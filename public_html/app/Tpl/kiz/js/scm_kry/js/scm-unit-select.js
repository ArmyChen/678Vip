var urlUomList = '/scm_kry/common/query/uomList';
$('head').append('<style>.select-group{white-space: normal;}</style>');

$.removeSelectTitle = function(rowId){
    $('#'+rowId).find('td[aria-describedby="grid_uom"]').removeAttr("title");
};

//单位下拉 created by zhulf
$.unitSelectFormatter = function (cellvalue, options, rowObject) {
    cellvalue = cellvalue || cellvalue == 0 ? cellvalue : '';//null，undefined等值转为（''）
    var redBorderClass = cellvalue === '' ? ' red-border' : '';
    var selectHtml = '<div class="select-group select-unit" data-loaded="1" data-rowid="' + options.rowId + '"' + '>'
        +'    <div class="select-control' + redBorderClass + '">'
        +'        <em>' + cellvalue + '</em>'
        +'    </div>'
        +'    <ul style="display: none;">'
        +'        <li>' + cellvalue + '</li>'
        +'    </ul>'
        +'    <select name="uomSelect" class="select-style">'
        +'        <option value="' + rowObject.standardUnitId + '">' + rowObject.standardUnitName + '</option>'
        +'    </select>'
        +'</div>';

    return selectHtml;
};


$.delegateClickSelectGroup = function ($detailGrid){
    $(".select-group").parents("td").css({"overflow" : "visible"}).removeAttr("title");
    //数据量大时多次触发，页面易卡死
    //$detailGrid.find('tr').each(function(){
    //    $(this).find(':text').first().trigger('propertychange');
    //});
    $detailGrid.find(':text').first().trigger('propertychange');

    //单位下拉框加载数据
    $(document).delegate('.select-group.select-unit','click',function(e){
        var _this = $(this);
        var loaded = _this.data('loaded'); //是否已经加载
        var rowId = _this.data('rowid'); //行ID
        var rowData = $($detailGrid.selector).jqGrid('getRowData',rowId); //行数据
        var selectValue = rowData.standardUnitId; //选择的单位id
        if(loaded == '1'){
            //加载数据
            $.ajax({
                type: 'get',
                url: urlUomList,
                data: {
                    skuId: rowData.skuId || rowData.id
                },
                async: false,
                dataType: 'json',
                success: function (result) {
                    if (result == null || result.success) {
                        alert('单位获取失败，请重试');
                    } else {
                        var listData = result;
                        //加载成功
                        var objectData = {};
                        listData.forEach(function(v,i){
                            objectData[v.id] = v;
                        });
                        _this.data('objectdata',JSON.stringify(objectData));

                        var lis = '';
                        var opts = '';
                        for(var i=0;i<listData.length;i++){
                            var data = listData[i];
                            var selected = '';
                            lis += '<li>' + data.name + '</li>';
                            if(selectValue == data.id){
                                selected = 'selected';
                            }
                            opts += '<option value="' + data.id + '" ' + selected + '>' + data.name + '</option>';
                        }

                        //填充下拉列表
                        _this.find('ul').empty().html(lis);
                        _this.find('select').empty().html(opts);

                        _this.parents("td").css({"overflow" : "visible"}).removeAttr("title");

                        _this.data('loaded','2'); //已加载
                    }
                },
                error: function (data) {
                }
            });
        }

        e.stopPropagation();
    });

    //单位下拉框变更事件
    $(document).delegate('select[name="uomSelect"]','change',function(e){
        var _this = $(this);
        var selectId = _this.val(); //选择的单位id

        var $selectGroup = _this.parent('.select-group');
        var objData = $selectGroup.data('objectdata'); //所有单位数据
        objData = JSON.parse(objData);
        var rowId = $selectGroup.data('rowid'); //行ID
        var rowData = $detailGrid.jqGrid('getRowData',rowId); //行数据

        var unitObj =  objData[selectId];
        //计算单价、合计金额与当前库存

        rowData.price = $.toFixed(rowData.standardPrice / unitObj.standerConvert * unitObj.skuConvert); //最小价格 * 当前单位换算率
        //rowData.inventoryQty = $.toFixed(rowData.standardInventoryQty * rowData.skuConvertOfStandard / unitObj.skuConvert); //库存

        $detailGrid.jqGrid('setCell',rowId,'price',rowData.price);
        //$detailGrid.jqGrid('setCell',rowId,'inventoryQty',rowData.inventoryQty);

        $detailGrid.jqGrid('setCell',rowId,'skuConvert',unitObj.skuConvert); //保存当前单位换算率
        $detailGrid.jqGrid('setCell',rowId,'skuConvertOfStandard',unitObj.standerConvert); //保存标准单位换算率
        $detailGrid.jqGrid('setCell',rowId,'uomId',unitObj.id); //保存当前单位ID
        $('tr#' + rowId).find(':text').first().trigger('propertychange'); //触发propertychange事件，算出合计金额与采购合计金额

        $selectGroup.find('.select-control').removeClass('red-border');//移除没有单位的提醒
    });
};