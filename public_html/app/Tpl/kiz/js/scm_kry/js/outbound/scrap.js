/**
 * Created by mayi on 2015/6/3.
 */

var scrap = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : '/outbound/scrap',
        templateId : -1,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '/query',
        editUrl : '/edit',
        deleteUrl : '/delete',
        viewUrl : '/view',
        confirmUrl : '/doconfirm',
        sortName : 'code',
        pager : '#gridPager',
        formId : 'baseInfoForm',
        detailGridId : 'grid',
        gridData : [],
        warehouseId :'#fromWmId',
        taxRate : '',
        reasonTip: '请选择报废原因',
        reasons: []
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType)
        {
            case 0 ://列表查询
                _this.$listGrid = $('#' + _this.opts.listGridId);
                _this.initQueryList();
                $.setSearchFocus();
                break;
            case 1 ://新增
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(true);
                _this.initChangAllReason();
                _this.ready2ClearWrong();
                $.filterGrid.initSkuTypeNames();
                break;
            case 2 ://编辑
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(true);
                _this.initChangAllReason();
                _this.ready2ClearWrong();
                if($("#warehouseId").val()==''&&$("#warehouseName").val()!=''){
                	$.layerMsg('仓库：'+$("#warehouseName").val()+'已被停用，请重新选择', false);
                }
                validateReasons(_this);
                $.filterGrid.initSkuTypeNames();
                break;
            case 7 : //复制
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(true);
                _this.initChangAllReason();
                _this.ready2ClearWrong();
                validateReasons(_this);
                $.filterGrid.initSkuTypeNames();
                break;

            default ://查看
                $("select").siblings(".select-control").addClass("disabled");
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(false);
                $.filterGrid.initSkuTypeNames();
                break;
        }
    },

    ready2ClearWrong : function(){

        $.clearWrong('warehouseId');
    },

    //初始化查询页面
    initQueryList : function() {
        var _this = this;

        $.serializeGridDataCallback = function (formData) {
            if (typeof formData.status == "object" || formData.status == undefined) {
                formData["status"] = "-2";
            }
            return formData;
        };

        $.showEditor = function (rowData) {
            if (rowData.status == 0) {
                return renderEnum.normal;
            }
            return renderEnum.hidden;
        };

        $.showView = function (rowData) {
            if (rowData.status == 1) {
                return renderEnum.normal;
            }
            return renderEnum.hidden;
        };

        $.showConfirm = function (rowData) {
            if (rowData.status == 0) {
                return renderEnum.normal;
            }
            return renderEnum.disabled;
        };

        $.showDelete = function (rowData) {
            if (rowData.status == 0) {
                return renderEnum.normal;
            }
            return renderEnum.disabled;
        };

        var $gridObj = $("#" + _this.opts.listGridId);
        $gridObj.dataGrid({
            formId: _this.opts.queryConditionsId,
            serializeGridDataCallback: $.serializeGridDataCallback,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['单据号', '保存 / 确认日期', '供应商名称', '退货仓库', '退回金额', '状态', '状态'],
            colModel: [
                {name: 'outboundNo', index: 'outboundNo', width: 160, align: "center"},
                {name: 'updateTime', index: 'updateTime', width: 160, align: "center"},
                {name: 'receiverName', index: 'receiverName', width: 280},
                {name: 'warehouseName', index: 'warehouseName', width: 160},
                {
                    name: 'amount',
                    index: 'amount',
                    width: 120,
                    align: "right",
                    formatter: customCurrencyFormatter
                },
                {name: 'statusName', index: 'status', width: 100, align: "center"},
                {name: 'status', index: 'status', width: 100, align: "center", hidden:true}
            ],
            sortname: 'outboundNo',
            pager: "#gridPager",
            showOperate:true,
            actionParam: {
                editor: {
                    url: _this.opts.urlRoot + _this.opts.editUrl,
                    render: $.showEditor
                },
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl,
                    render: $.showView
                },
                confirm: {
                    url: _this.opts.urlRoot + _this.opts.confirmUrl,
                    render: $.showConfirm
                },
                delete: {
                    url: _this.opts.urlRoot + _this.opts.deleteUrl,
                    render: $.showDelete
                }
            }
        });
    },

    //初始化单据作业明细表格
    initDetailGrid : function(editable) {
        var _this = this;
        var $gridObj = _this.$detailGrid;
        var priceColModel = {
            name: 'price',
            index: 'price',
            align: 'right',
            width: 120,
            sortable: !editable
        };

        var qtyColModel = {
            name: 'planQty',
            index: 'planQty',
            align: 'right',
            width: 120,
            sorttype:'number',
            sortable: !editable
        };

        var editColModel = {
            editable: true,
            formatter: formatInputNumber,
            unformat: unformatInput
        };

        if (editable) {
            priceColModel = $.extend(true, priceColModel, editColModel || {});
            qtyColModel = $.extend(true, qtyColModel, editColModel || {});
        }

        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            //height: 300,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['skuId', '所属分类', '商品编码', '商品名称(规格)', '单位', '单位', '价格', '报废数', '报废金额', '报废原因', '报废原因', '换算率', '标准单位换算率', '标准单位ID', '标准单位', '定价'],
            colModel: [
                {name: 'skuId', index: 'skuId', width: 80, hidden: true, sortable: !editable},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, sortable: !editable},
                {name: 'skuCode', index: 'skuCode', width: 100, sortable: !editable},
                {name: 'skuName', index: 'skuName', width: 200, sortable: !editable},
                {name: 'uom', index: 'uom', width: 50, sortable: !editable, align: 'center', hidden: editable},
                {name: 'uom', index: 'uom', width: 70, sortable: !editable, align: 'center', hidden: !editable,
                    formatter: $.unitSelectFormatter,
                    unformat : unformatSelect
                },
                {name: 'price', index: 'price', align: 'right', width: 50, sorttype:'number',sortable: !editable},
                qtyColModel,
                {name: 'amount', index: 'amount', width: 100, align: 'right', sorttype:'number',sortable: !editable},
                {
                    name: 'reasonId',
                    index: 'reasonId',
                    width: 100,
                    sortable: !editable,
                    editable: true,
                    hidden : !editable,
                    formatter : selectFormatter,
                    unformat:selectUnFormat
                },
                {
                    name: 'reason',
                    index: 'reason',
                    width: 100,
                    sortable: !editable,
                    hidden: editable,
                    unformat: function (cellvalue, options, cell) {
                        var $reasonId = $("#reason_" + options.rowId);
                        if ($reasonId.val() == "") {
                            return "";
                        }
                        return $reasonId.find("option:selected").text();
                    }
                },
                {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
                {name: 'standardUnitId', index: 'standardUnitId', align: "center", hidden: true},
                {name: 'standardUnitName', index: 'standardUnitName', align: "center", hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true}
            ],
            afterInsertRow: function (rowid, aData) {
                //$gridObj.jqGrid('setRowData', rowid, {skuId: rowid});
                var $select = $("#reason_" + rowid);
                //$select.chosen({disable_search_threshold: 10});

                //模拟select选择，并去除所属td原有的overflow：hidden，让下拉框可见，并移除提示title
                bkeruyun.selectControl($select);
                $select.parents("td").css({"overflow" : "visible"}).removeAttr("title");

                $.removeSelectTitle(rowid); //移除下拉框列的表格title
            }
        });

        //模拟select选择，并去除所属td原有的overflow：hidden，让下拉框可见
        var $reason = $(".reason");
        bkeruyun.selectControl($reason);
        $reason.parents("td").css({"overflow" : "visible"}).removeAttr("title");

        scmSkuSelect.opts.dataGridCal = $gridObj.dataGridCal({
            formula: ['price*planQty=amount'],
            summary: [
                {colModel: 'planQty', objectId: 'qtySum'},
                {colModel: 'amount', objectId: 'amountSum', showCurrencySymbol: true}
            ]
        });

        if(editable) $.delegateClickSelectGroup($gridObj);

        function selectFormatter (cellvalue, options, rowObject) {

            var str = '<select class="reason" id="reason_' + options.rowId + '">';
            str += '<option value="">' + _this.opts.reasonTip + '</option>';
            var reasons = _this.opts.reasons;
            for (var i = 0; i < reasons.length; i++) {
                if (reasons[i].id == cellvalue) {
                    str += '<option value="' + reasons[i].id + '" selected>' + reasons[i].content + '</option>';
                } else {
                    str += '<option value="' + reasons[i].id + '">' + reasons[i].content + '</option>';
                }
            }
            str += '</select>';
            return str;
        }

        function selectUnFormat (cellvalue, options, cell){
            var value = $("#reason_" + options.rowId).val();
            return value;
        }
    },

    initChangAllReason : function () {
        var $changAllReason = $("#changAllReason");
        $changAllReason.selectForGridWithCheck('grid',selectForGridWithCheckCallBack,'商品');
    }


};

/*
 * 下拉框选中后执行的方法
 * $row: grid行对象
 * selectIndex: 选中的下标
 * selectTxt: 选中的文本
 * */
function selectForGridWithCheckCallBack($row,selectIndex,selectTxt){
    var $checkbox = $row.find('input[type="checkbox"]');
    var $reasons = $row.find(".reason");
    if($checkbox.prop('checked')){
        if(selectIndex == 0){
            selectTxt = $reasons.children(':first').text(); //选中第一个值
        }
        $reasons.siblings(".select-control").find("em").text(selectTxt);
        $reasons[0].selectedIndex = selectIndex;
        $reasons.trigger('change');
    }
}

//单据明细校验
$.detailsValidator = function (args) {
    var $grid = $('#'+ scrap.opts.detailGridId);
    var gridData = $('#' + scrap.opts.detailGridId).jqGrid('getRowData');
    if (gridData.length == 0) {
        //bkeruyun.promptMessage('请先添加商品');
        $.layerMsg('请先添加商品', false);
        return false;
    }

    var qtySum = $('#' + scrap.opts.detailGridId).jqGrid('getCol', 'planQty', false, 'sum');
    if (qtySum == 0) {
        //bkeruyun.promptMessage('报废数不能全部为0');
        $.layerMsg('报废数不能全部为0', false);
        return false;
    }
    var qtyofNoUom = $grid.find('.select-unit > .red-border').length;
    if(qtyofNoUom > 0){
        $.layerMsg('还有商品未选择单位',false);
        return false;
    }

    for (var i = 0; i < gridData.length; i++) {
        if ((gridData[i].planQty > 0 && gridData[i].reasonId == "") || (gridData[i].planQty == 0 && gridData[i].reasonId != "")) {
            //bkeruyun.promptMessage('报废数量与原因的栏位需同时有数据或同时为空，请检核！');
            $.layerMsg('报废数量与原因的栏位需同时有数据或同时为空，请检核！', false);
            return false;
        }
    }

    return true;
};

//保存回调
$.saveCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        var $id = $("#id");
        var $btnCopy = $("#btnCopy");
        if (!$id.val()) {
            $id.val(rs.data.id);
            $btnCopy.removeClass("hidden")
            replaceUrl('/outbound/scrap/edit', 'id=' + rs.data.id);
            $("#command-type-name").text("编辑");
            document.title = '编辑报废单';
        }
        $.layerMsg(rs.message, true);
        return;
    } else {
        if (rs.data != '' && rs.data != null) {
            //bkeruyun.promptMessage("操作失败:" + rs.message, rs.data + "<br>");
            $.layerOpen("操作失败:" + rs.message, rs.data);
        } else {
            //bkeruyun.promptMessage("操作失败:" + rs.message);
            $.layerMsg("操作失败:" + rs.message, false);
        }
    }
};

//确认回调
$.confirmCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        var id = rs.data.id;
        var url = scrap.opts.urlRoot + '/view';
        var token_new = args.token;
        if(token_new) {
            $('#t').val(token_new);
        }
        $.doForward({"url":url, "postData":{"id":id}});
        return;
    } else {
        if (rs.data != '' && rs.data != null) {
            //bkeruyun.promptMessage("操作失败:" + rs.message, rs.data + "<br>");
            $.layerOpen("操作失败:" + rs.message, rs.data);
        } else {
            //bkeruyun.promptMessage("操作失败:" + rs.message);
            $.layerMsg("操作失败:" + rs.message, false);
        }
    }
};
/**
*
* 在编辑页面，切换到“单据作业”时，先验证原因是否被删除
*
*/
function validateReasons(_this){
   var details = _this.$detailGrid;

   if(details.length > 0 && checkReasonsDeleted(_this)){
       // do nothing
   }
}
/**
*
* 检查原因是否被删除
*
* @param details
* @returns {boolean}
*/
function checkReasonsDeleted(_this){

   var deleted = false;
   var isConfirmOperation=false;
   var id=$("#id").val();
   $.ajax({
       url: _this.opts.urlRoot + '/checkReasonsDeletedForEdit?id=' + id,
       type: "post",
       async: false,
       contentType: 'application/json',
       success: function (result) {
           deleted = !result.success;
           if (deleted) {
               //bkeruyun.promptMessage(result.message);
               $.layerMsg(result.message, false);
           }
       },
       error: function (data, status, e) {
           //bkeruyun.promptMessage("网络错误");
           $.layerMsg("网络错误", false);
       }
   });
   return deleted;

}


//查看页面打印方法
function print(id){
    $.print.showPrintDialog({
        urlRoot: scrap.opts.urlRoot,
        query: {
            id: id
        }
    });
}