/**
 *
 * 显示符合筛选条件的行，一般情况下，调用该方法即可。
 * 当出现以下情况时，可通过多次调用filterGridRowIds，再调用filterGridRows方法，达到筛选目的：
 *      一个input框的条件接受2种值，商品名称、商品编码。
 *
 * @param gridId
 * @param conditions json格式
 *          {
                skuCode: '1',
                skuName: '泸州老窖500ml',
                skuTypeName: '酒水'
            }
 * @param matchPatten 可不传 匹配模式的数组(1:模糊匹配,2:精确匹配)
 *          例 [1,1,2]
 */
filterGridData = function(gridId, conditions, matchPatten){

    var rowIdsToShow = filterGridRowIds(gridId, conditions, matchPatten);

    filterGridRows(gridId, rowIdsToShow);

};

/**
 * 显示id在rowIds中的row
 * @param gridId
 * @param rowIds
 * @returns {boolean}
 */
filterGridRows = function(gridId, rowIds){

    var $gridObj = $('#' + gridId);
    var $gridObjFrozen = $('#' + gridId + '_frozen');

    if($gridObj == undefined){
        return false;
    }

    var array = $gridObj.jqGrid("getDataIDs");
    $.each(array, function(index, rowId){
        if(rowIds.indexOf(rowId) == -1){
            $gridObj.find('#' + rowId).hide(); // 隐藏每一行
            $gridObjFrozen.find('#' + rowId).hide(); // 隐藏每一行
        }else{
            $gridObj.find('#' + rowId).show(); // 显示符合条件的行
            $gridObjFrozen.find('#' + rowId).show(); // 显示符合条件的行
        }
    });
};

/**
 * 过滤出符合条件的row id
 * @param gridId
 * @param conditions
 * @returns {Array}
 */
filterGridRowIds = function(gridId, conditions, matchPatten){
    matchPatten = matchPatten ? matchPatten : [];//默认为空既都为模糊匹配
    var $gridObj = $('#' + gridId);
    if($gridObj == undefined){
        return [];
    }

    var array = $gridObj.jqGrid("getDataIDs");
    if(array == undefined || array.length == 0){
        return [];
    }

    if($.isEmptyObject(conditions)){
        return array; // 筛选条件为空，则显示所有行
    }


    var rowIdsToShow = [];

    $.each(array, function(index, id){

        var allConditionsHit = true;
        var matchPattenIndex = -1;

        for(key in conditions){
            matchPattenIndex += 1;
            var cell = $gridObj.getCell(id, key);
            if(conditions[key] == undefined || conditions[key] === ''){
                continue;
            }
            //根据匹配模式匹配
            allConditionsHit = filterByPatten(cell, conditions[key], matchPatten[matchPattenIndex]);
            if(allConditionsHit == false){
                break;
            }
        }
        if(allConditionsHit){
            rowIdsToShow.push(id);
        }
    });

    return rowIdsToShow;
};

//匹配条件
function filterByPatten(form, to, patten){
    if(patten == 2){
        if(form != to){
            return false;
        }
    }else{
        //默认为1:模糊匹配
        if(form.indexOf(to) < 0){
            return false;
        }
    }
    return true;
}

$(function () {
    //填充筛选组件 start
    var tmlFilterGrid = '\
    <form method="get" action="#">\
        <input name="tableid" type="text" class="hidden" value=""/>\
        <div class="form-container ml10">\
            {{if showwarehouse}}\
            <div class="form-item filter">\
                <div class="control-label-con1" name="warehouseDiv" style="width: 185px">\
                    <div class="multi-select">\
                        <div class="select-control"><em></em></div>\
                        <div class="multi-select-con" style="display:none;top: 30px;">\
                            <ul class="multi-select-items">\
                                <li>\
                                    <label class="checkbox" for="wm-type-all">\
                                        <span></span>\
                                        <input type="checkbox" id="wm-type-all">全部\
                                    </label>\
                                </li>\
                            </ul>\
                        </div>\
                        <input type="hidden" value="" />\
                    </div>\
                </div>\
            </div>\
            {{/if}}\
            {{if showinventory}}\
            <div class="form-item filter">\
                <div class="control-label-con1" name="inventoryStypeDiv" style="width: 185px"></div>\
            </div>\
            {{/if}}\
            {{if showreason}}\
            <div class="form-item filter">\
                <div class="control-label-con1" name="reasonDiv" style="width: 185px"></div>\
            </div>\
            {{/if}}\
            <div class="form-item filter">\
                <div class="control-label-con1" name="skuTypeNameDiv" style="width: 185px;"></div>\
            </div>\
            <div class="form-item filter">\
                <div class="control-label-con1 search-box" style="width: 185px;">\
                    <input type="text" name="skuCodeOrName" class="form-control" placeholder="请输入商品条码/名称" data-format="skuName" maxlength="14">\
                    <button type="button" class="close" aria-hidden="true">&times;</button>\
                </div>\
            </div>\
            <div class="form-item" style="width: 80px;">\
                <a name="filterSearch" class="btn-blue btn-search" role="button" onclick="$.filterGrid.filterSku(this);">查 询</a>\
            </div>\
        </div>\
    </form>';

    $.filterGrid = {
        selectSkuType: '',
        filterGridDiv: '#filterGridDiv'
    };

    $.filterGrid.init = function (parentDiv) {
        var _this = $.filterGrid;
        if(parentDiv){
            _this.filterGridDiv = parentDiv;
        }
        var $filterGridDiv = $(_this.filterGridDiv);

        var render = template.compile(tmlFilterGrid);
        $filterGridDiv.html(render({
            showreason: $filterGridDiv.data('showreason') || false,
            showinventory: $filterGridDiv.data('showinventory') || false,
            showwarehouse: $filterGridDiv.data('showwarehouse') || false
        }));

        $filterGridDiv.find('[name="tableid"]').val($filterGridDiv.data('tableid') || 'grid');

        //商品编码名称输入框，添加enter查询事件
        $filterGridDiv.find('[name="skuCodeOrName"]').on('keydown', function (e) {
            if(e.keyCode == 13){
                $filterGridDiv.find('[name="filterSearch"]').click();
            }
        });
    };
    $.filterGrid.init();
    //填充筛选组件 end

    //商品分类，商品编码、名称筛选
    $.filterGrid.filterSku = function(e) {
        var $filterGridDiv = $(e).parents('.form-container').parents('div').eq(0);

        //需要筛选的表格id
        var gridId = $filterGridDiv.find('[name="tableid"]').val();
        var showreason = $filterGridDiv.data('showreason'); //是否显示退回原因
        var showinventory = $filterGridDiv.data('showinventory'); //是否显示退回原因
        var showwarehouse = $filterGridDiv.data('showwarehouse'); //是否显示退回原因

        var skuTypeName = $filterGridDiv.find('[name="skuTypeName"]').find('option:selected').val();
        var reason = '';
        if(showreason){
            reason = $filterGridDiv.find('[name="reason"]').find('option:selected').val();
        }
        var wmTypeName = '';
        if(showinventory){
            wmTypeName = $filterGridDiv.find('[name="inventoryStype"]').find('option:selected').val();
        }

        var conditions1 = {
            skuCode: $filterGridDiv.find('[name="skuCodeOrName"]').val(),
            skuTypeName: skuTypeName,
            reason: reason,
            wmTypeName: wmTypeName
        };
        var conditions2 = {
            skuName: $filterGridDiv.find('[name="skuCodeOrName"]').val(),
            skuTypeName: skuTypeName,
            reason: reason,
            wmTypeName: wmTypeName
        };

        var rowIds1 = filterGridRowIds(gridId, conditions1);
        var rowIds2 = filterGridRowIds(gridId, conditions2);
        Array.prototype.push.apply(rowIds1, rowIds2);
        filterGridRows(gridId, rowIds1);

        if(showwarehouse){
            var $warehouseDiv = $filterGridDiv.find('[name="warehouseDiv"]');
            if($warehouseDiv.find('em').text() == '全部' || $warehouseDiv.find('em').text() == ''){
                $('[role="columnheader"]').show();
                $('[id^="grid_2_lowerInventory"]').show();
                $('[id^="grid_2_safetyInventory"]').show();
                $('[id^="grid_2_upperInventory"]').show();
                $('[aria-describedby^="grid_2_lowerInventory"]').show();
                $('[aria-describedby^="grid_2_safetyInventory"]').show();
                $('[aria-describedby^="grid_2_upperInventory"]').show();
                return;
            }

            //隐藏列
            var ids = $warehouseDiv.find('[type="hidden"]').val().split(',');
            var names = $warehouseDiv.find('em').text().split(',');

            $('[colspan="3"]').hide();
            $('[colspan="3"]').each(function () {
                var has = names.indexOf($(this).text());
                if(has > -1){
                    $(this).show();
                }
            });

            $('[id^="grid_2_lowerInventory"]').hide();
            $('[id^="grid_2_safetyInventory"]').hide();
            $('[id^="grid_2_upperInventory"]').hide();
            $('[aria-describedby^="grid_2_lowerInventory"]').hide();
            $('[aria-describedby^="grid_2_safetyInventory"]').hide();
            $('[aria-describedby^="grid_2_upperInventory"]').hide();
            for(var i in ids){
                var id = ids[i];
                $('[id="grid_2_lowerInventory' + id + '"]').show();
                $('[id="grid_2_safetyInventory' + id + '"]').show();
                $('[id="grid_2_upperInventory' + id + '"]').show();
                $('[aria-describedby="grid_2_lowerInventory' + id + '"]').show();
                $('[aria-describedby="grid_2_safetyInventory' + id + '"]').show();
                $('[aria-describedby="grid_2_upperInventory' + id + '"]').show();
            }
        }
    };
    //刷新商品分类选项
    $.filterGrid.initSkuTypeNames = function(){
        var _this = $.filterGrid;
        var $filterGridDiv = $(_this.filterGridDiv);

        var gridId = $filterGridDiv.find('[name="tableid"]').val();
        var details = $('#' + gridId).jqGrid('getRowData');

        var $skuTypeNameDiv = $filterGridDiv.find('[name="skuTypeNameDiv"]');
        var $skuCodeOrName = $filterGridDiv.find('[name="skuCodeOrName"]');

        var skuTypeNames = [];
        var option = '';

        var select = '<select class="form-control" name="skuTypeName" id="skuTypeName">' +
            '<option value="">请选择商品分类</option>' +
            '</select>';

        $skuTypeNameDiv.html(select);

        $.each(details, function(index, detail){
            if(detail.skuTypeName && skuTypeNames.indexOf(detail.skuTypeName) < 0){
                skuTypeNames.push(detail.skuTypeName);
                option += '<option value=' + detail.skuTypeName + '>' + detail.skuTypeName + '</option>';
            }
        });

        var $skuTypeName = $filterGridDiv.find('[name="skuTypeName"]');
        $skuTypeName.append(option);

        bkeruyun.selectControl($skuTypeName);

        //保留已经设置过的商品分类
        var selectSkuTypeOption = $skuTypeName.children('[value="' + _this.selectSkuType + '"]');
        if(_this.selectSkuType != '' && selectSkuTypeOption.length == 1){
            $skuTypeNameDiv.find('em').text(selectSkuTypeOption.text());
            selectSkuTypeOption.prop('selected','selected');
        }

        //选择商品分类后光标定位到商品编码中
        $skuTypeName.on('change', function (e) {
            _this.selectSkuType = $skuTypeName.val(); //设置商品分类
            $skuCodeOrName.focus();
        });
    };
    //初始化原因
    $.filterGrid.initReason = function(){
        var _this = $.filterGrid;
        var $filterGridDiv = $(_this.filterGridDiv);

        var gridId = $filterGridDiv.find('[name="tableid"]').val();
        var details = $('#' + gridId).jqGrid('getRowData');

        var $reasonDiv = $filterGridDiv.find('[name="reasonDiv"]');
        var $skuCodeOrName = $filterGridDiv.find('[name="skuCodeOrName"]');

        var reasons = [];
        var option = '';

        var select = '<select class="form-control" name="reason">' +
            '<option value="">请选择退回原因</option>' +
            '</select>';

        $reasonDiv.html(select);

        $.each(details, function(index, detail){
            if(reasons.indexOf(detail.reason) < 0){
                reasons.push(detail.reason);
                option += '<option value=' + detail.reason + '>' + detail.reason + '</option>';
            }
        });

        var $reason = $filterGridDiv.find('[name="reason"]');
        $reason.append(option);

        bkeruyun.selectControl($reason);

        //选择商品分类后光标定位到商品编码中
        $reason.on('change', function () {
            $skuCodeOrName.focus();
        });
    };
    //初始化库存类型
    $.filterGrid.initInventoryStype = function(){
        var _this = $.filterGrid;
        var $filterGridDiv = $(_this.filterGridDiv);

        var gridId = $filterGridDiv.find('[name="tableid"]').val();
        var details = $('#' + gridId).jqGrid('getRowData');

        var $inventoryStypeDiv = $filterGridDiv.find('[name="inventoryStypeDiv"]');
        var $skuCodeOrName = $filterGridDiv.find('[name="skuCodeOrName"]');

        var inventoryStype = [];
        var option = '';

        var select = '<select class="form-control" name="inventoryStype">' +
            '<option value="">请选择库存类型</option>' +
            '</select>';

        $inventoryStypeDiv.html(select);

        $.each(details, function(index, detail){
            if(inventoryStype.indexOf(detail.wmTypeName) < 0){
                inventoryStype.push(detail.wmTypeName);
                option += '<option value=' + detail.wmTypeName + '>' + detail.wmTypeName + '</option>';
            }
        });

        var $inventoryStype = $filterGridDiv.find('[name="inventoryStype"]');
        $inventoryStype.append(option);

        bkeruyun.selectControl($inventoryStype);

        //选择商品分类后光标定位到商品编码中
        $inventoryStype.on('change', function () {
            $skuCodeOrName.focus();
        });
    };
    //初始化仓库
    $.filterGrid.initWarehouse = function(warehouseData){
        var _this = $.filterGrid;
        var $filterGridDiv = $(_this.filterGridDiv);

        var gridId = $filterGridDiv.find('[name="tableid"]').val();
        var details = $('#' + gridId).jqGrid('getRowData');

        var $warehouseDiv = $filterGridDiv.find('[name="warehouseDiv"]');
        var $skuCodeOrName = $filterGridDiv.find('[name="skuCodeOrName"]');

        var warehouse = [];

        var select = '\
        <li>\
            <label class="checkbox" for="wm-type-all">\
            <span></span>\
            <input type="checkbox" id="wm-type-all">全部\
            </label>\
        </li>';
        var li = '\
        <li>\
            <label class="checkbox" for="wm-type-{i}">\
            <span></span>\
            <input type="checkbox" name="wmTypes" id="wm-type-{i}" value="{id}" data-text="{v}">{v}\
            </label>\
        </li>';

        for(var i in warehouseData){
            if(warehouse.indexOf(warehouseData[i].id) < 0){
                warehouse.push(warehouseData[i].id);
                select += li.replace(/{i}/g,i).replace('{id}',warehouseData[i].id).replace(/{v}/g,warehouseData[i].warehouseName);
            }
        }

        $warehouseDiv.find('ul').html(select);

        //选择商品分类后光标定位到商品编码中
        $warehouseDiv.find('[type="hidden"]').on('change', function () {
            $skuCodeOrName.focus();
        });
    };

    //初始化所有选择框
    $.filterGrid.initSelect = function () {
        var _this = $.filterGrid;
        var $filterGridDiv = $(_this.filterGridDiv);
        var showreason= $filterGridDiv.data('showreason');
        var showinventory = $filterGridDiv.data('showinventory');
        var showwarehouse = $filterGridDiv.data('showwarehouse');

        _this.initSkuTypeNames();
        if(showinventory) _this.initInventoryStype();
        if(showwarehouse) _this.initWarehouse();
        if(showreason) _this.initReason();
    };
});

