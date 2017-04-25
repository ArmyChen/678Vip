/**
 * 库存分布明细表
 * @auth linc, Jun 6 2016
 */

var inventorydistribution = {
    //默认参数
    opts : {
        urlRoot : ctxPath,
        queryUrl : "&act=report_stock_dubbo_ajax",
        exportUrl : "&act=report_stock_dubbo_export",
        queryData : "",
        footerData : {},
        oldCommercialId : "",
        listGridId: '#grid',
        queryConditionId: 'queryConditions',
        cachedQueryConditions: '', //缓存页面条件
        sorting : false,//是否正在排序，防止死循环
        gridOpts: {
            data: [],
            datatype: 'local',
            showEmptyGrid: true,
            autowidth: true,
            //rowNum: 9999,
            shrinkToFit: false,
            rowNum: 50,
            scroll: 1, // virtual scroll
            colNames:['商品大类', '商品中类', '库存类型', '商品编码', '商品名称（规格）', '单位', '库存合计'],
            colModel:[
                {name: 'skuParentTypeName', index: 'skuParentTypeName', width: 80, align: 'center'},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, align: 'center'},
                {name: 'wmTypeName', index: 'wmTypeName', width: 80, align: 'center'},
                {name: 'skuCode', index: 'skuCode', width: 100, align: 'left'},
                {name: 'skuName', index: 'skuName', width: 180, align: 'left',
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDelete == 1) {
                            return cellvalue + "<span style='color:red'>(已删除)</span>";
                        } else if (rowObject.isDisable == 1) {
                            return cellvalue + "<span style='color:red'>(已停用)</span>";
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'uom', index: 'uom', width: 60, align: 'center'},
                {name: 'qtySum', index: 'qtySum', width: 100, align: 'right', formatter: customMinusToRedFormatter}
            ],
            footerrow: true,
            gridComplete: function() {
                var $gridObj = $(this);

                var rowData = $gridObj.getDataIDs();
                var $gridDom = $('#gbox_' + $gridObj.jqGrid('getGridParam', 'id'));
                var $gridDivDom = $gridDom.parent();
                var $notSearch = $gridDom.parent().find(".notSearchContent");

                if (rowData.length > 0) {
                    //表格有数据，显示表格元素，并隐藏无数据提示元素
                    $gridDivDom.show();
                    $gridDom.show();
                    if ($notSearch.length > 0) {
                        $notSearch.hide();
                    }

                    $(".ui-jqgrid-sdiv").show();
                    $(this).footerData("set", inventorydistribution.opts.footerData);
                } else {
                    //表格无数据，隐藏表格自身元素，显示无数据提示元素
                    $gridDom.hide();
                    $gridDivDom.show();
                    if ($notSearch.length > 0) {
                        $notSearch.show();
                    } else {
                        var notData = bkeruyun.notQueryData("没有查到数据，试试其他查询条件吧！");
                        $gridDivDom.append(notData);
                    }

                    $(".ui-jqgrid-sdiv").hide();
                }
            },
            onSortCol: function (index, iCol ,sortorder) {

                if(!inventorydistribution.opts.sorting){
                    //排序开始
                    inventorydistribution.opts.sorting = true;

                    $("#search").click();
                    $("#grid").sortGrid(index, false, sortorder);

                    //排序结束
                    inventorydistribution.opts.sorting = false;
                    return 'stop';
                }
            }
        }
    },

    //初始化
    _init : function (args) {

        var _this = this;

        _this.opts = $.extend(true, this.opts, args || {});

        delegateCheckbox('wmTypeIds', '#wm-type-all', false);
        delegateCheckbox('skuTypeIds', '#sku-type-all', false);

        //默认勾选第一个商户（品牌或商户）
        if($('#commercialId-1')) {
            $('#commercialId-1').click();
        }

        $(_this.opts.listGridId).dataGrid(_this.opts.gridOpts);

        _this.opts.cachedQueryConditions = serializeFormById(_this.opts.queryConditionsId);//缓存查询条件

        _this.delegateQuery();

        _this.delegateExport();

        _this.delegateUndoAll();

        $('#search').click();

        $.setSearchFocus();
        
        if(commercialId!="-1"){
	     	refreshWms(commercialId);
	    }
    },

    delegateQuery : function(){

        var _this = this;

        //查询按钮点击
        $(document).delegate("#search", "click", function () {
            _this.query();
        });
    },

    delegateExport : function(){

        var _this = this;

        //导出按钮点击
        $(document).delegate("#export", "click", function () {

            var currentQueryConditions = serializeFormById(_this.opts.queryConditionId);

            if(currentQueryConditions != _this.opts.cachedQueryConditions){
                $.layerMsg('条件已改变，请先点击查询按钮！', false);
                return;
            }

            if (_this.opts.gridOpts.data.length == 0) {
                $.layerMsg('导出记录为空！', false);
                return;
            }

            var conditions = $.extend(true, {}, $('#' + _this.opts.queryConditionId).getFormData() || {});

            conditions = '?' + $.param(conditions, true);

            var skuNameOrCode = $('#skuNameOrCode').val();
            var wmTypeNames = $('#wmTypeId-all-em').text();
            var skuTypeNames = $('#skuTypeId-all-em').text();
            var commercialNames = $('#commercialId-all-em').text();
            var warehouseNames = $('#wmId-all-em').text();

            conditions = conditions.replace('skuNameOrCode', 'skuNameOrCodeNotWanted');
            conditions += ('&skuNameOrCode=' + skuNameOrCode);
            conditions += ('&wmTypeNames=' + wmTypeNames);
            conditions += ('&skuTypeNames=' + skuTypeNames);
            conditions += ('&commercialNames=' + commercialNames);
            conditions += ('&warehouseNames=' + warehouseNames);

            conditions = encodeURI(encodeURI(conditions));

            window.open(_this.opts.urlRoot + _this.opts.exportUrl + conditions, '_blank');
        });
    },

    delegateUndoAll : function(){
        $("#undo-all").on("click",function(){
            //默认勾选第一个商户（品牌或商户）
        	 if(commercialId!="-1"){
     	     	refreshWms(commercialId);
     	     	$("#oneCommercial").find("li").click();
     	     	$("#oneCommercial").find("input").val(commercialId);
     	    }else{
     	    	if($('#commercialId-1')) {
     	    		$('#commercialId-1').click();
     	    	}
     	    }
        });
    },

    //查询
    query : function () {

        var _this = this;

        var conditions = $.extend(true, {}, $('#' + _this.opts.queryConditionId).getFormData() || {});

        //若只选择一个仓库时转换成数组，便于后台接收
        if (typeof conditions.wmTypeIds == "string") {
            var wmTypeIds = [];
            wmTypeIds.push(conditions.wmTypeIds);
            conditions["wmTypeIds"] = wmTypeIds;
        }
        if (typeof conditions.wmIds == "string") {
            var wmIds = [];
            wmIds.push(conditions.wmIds);
            conditions["wmIds"] = wmIds;
        }
        if (typeof conditions.skuTypeIds == "string") {
            var skuTypeIds = [];
            skuTypeIds.push(conditions.skuTypeIds);
            conditions["skuTypeIds"] = skuTypeIds;
        }
        if (typeof conditions.commercialIds == "string") {
            var commercialIds = [];
            commercialIds.push(conditions.commercialIds);
            conditions["commercialIds"] = commercialIds;
        }


         // conditions = JSON.stringify(conditions);
        conditions = serializeFormById("queryConditions");

        $("#load_grid").show();
        $.ajax({
            type: "post",
            async: true,
            url : _this.opts.urlRoot + _this.opts.queryUrl+'&'+conditions,
            contentType : 'application/json;charset=UTF-8',
            data : {},
            dataType : 'json',
            success: function (data) {
                //var data = $.parseJSON(json);
                if (data.skuVOs) {
                    _this.buildTable(data.skuVOs);
                    _this.opts.cachedQueryConditions = serializeFormById(_this.opts.queryConditionId);//缓存查询条件
                } else {
                    $.layerMsg('查询失败！');
                }
                $("#load_grid").hide();
            }
        });
    },

    //构建表格
    buildTable : function (skuVOs) {

        var _this = this;


        if (skuVOs) {

            var finalColNames = [];
            var finalColModels = [];

            //每次仅获取前7列，即是商品基本信息所在的列
            var fixedColNames = inventorydistribution.opts.gridOpts.colNames.slice(0, 7);
            var fixedColModel = inventorydistribution.opts.gridOpts.colModel.slice(0, 7);

            var dynamicColNames = [],
                dynamicColModel = [],
                groupHeaders1 = [],
                groupHeaders2 = [],
                footerData = {qtySum : "合计"};

            var pushedGroupHeaders1 = [];
            var pushedGroupHeaders2 = [];
            var pushedWarehouses = [];

            $.each(skuVOs, function(i, sku) {

                $.each(sku.titleVOs, function(j, warehouse) {

                    if(warehouse.qty != undefined && warehouse.qty != null) {
                        sku['qty_' + warehouse.warehouseId] = warehouse.qty;
                        sku['amount_' + warehouse.warehouseId] = warehouse.amount;
                    }

                    if(footerData['qty_' + warehouse.warehouseId] != undefined && footerData['qty_' + warehouse.warehouseId] != null){
                        footerData['qty_' + warehouse.warehouseId] = $.toFixed(parseInt(footerData['qty_' + warehouse.warehouseId]) + parseInt(warehouse.qty));
                    } else{
                        footerData['qty_' + warehouse.warehouseId] = warehouse.qty;
                    }

                    if(footerData['amount_' + warehouse.warehouseId] != undefined && footerData['amount_' + warehouse.warehouseId] != null){
                        footerData['amount_' + warehouse.warehouseId] = $.toFixed(parseFloat(footerData['amount_' + warehouse.warehouseId]) + parseFloat(warehouse.amount));
                    } else{
                        footerData['amount_' + warehouse.warehouseId] = warehouse.amount;
                    }

                    if(pushedWarehouses.indexOf(warehouse.warehouseId) < 0){

                        pushedWarehouses.push(warehouse.warehouseId);

                        dynamicColNames.push('库存');
                        dynamicColNames.push('库存金额');

                        dynamicColModel.push({
                            name: 'qty_' + warehouse.warehouseId,
                            index: 'qty_' + warehouse.warehouseId,
                            width: 60,
                            align: "right",
                            sorttype: "number",
                            formatter: _this.qtyFormatter
                        });


                        dynamicColModel.push({
                            name: 'amount_' + warehouse.warehouseId,
                            index: 'amount_' + warehouse.warehouseId,
                            width: 75,
                            align: "right",
                            sorttype: "number",
                            formatter: _this.amountFormatter
                        });

                        if(pushedGroupHeaders2.indexOf(warehouse.warehouseId) < 0){

                            groupHeaders2.push({
                                startColumnName: 'qty_' + warehouse.warehouseId,
                                numberOfColumns: 2,
                                titleText: warehouse.warehouseName
                            });

                            pushedGroupHeaders2.push(warehouse.warehouseId);

                            if(pushedGroupHeaders1.indexOf(warehouse.commercialId) < 0){

                                groupHeaders1.push({
                                    startColumnName: 'qty_' + warehouse.warehouseId,
                                    numberOfColumns: 2 ,
                                    titleText: warehouse.commercialName
                                });

                                pushedGroupHeaders1.push(warehouse.commercialId);

                            }else {
                                if(groupHeaders1.length > 0) {
                                    var preGroupHeader1 = groupHeaders1.pop();
                                    if (preGroupHeader1 && preGroupHeader1.titleText === warehouse.commercialName) {
                                        preGroupHeader1.numberOfColumns += 2 ;
                                    }
                                    groupHeaders1.push(preGroupHeader1);
                                }
                            }
                        }
                    }
                });
            });


            //商品基本信息所在的colName/colModel
            Array.prototype.push.apply(finalColNames, fixedColNames);
            Array.prototype.push.apply(finalColModels, fixedColModel);
            //库存所在的colName/colModel
            Array.prototype.push.apply(finalColNames, dynamicColNames);
            Array.prototype.push.apply(finalColModels, dynamicColModel);

            _this.opts.gridOpts.colNames = finalColNames;
            _this.opts.gridOpts.colModel = finalColModels;
            _this.opts.gridOpts.data = skuVOs;
            _this.opts.footerData = footerData;

            var $gridDiv =  $("#gridDiv");
            _this.opts.gridOpts.shrinkToFit = (680 + 135 * groupHeaders2.length) < $gridDiv.width();

            $gridDiv.empty().html('<table id="grid"></table>');
            var $grid = $(_this.opts.listGridId);
            $grid.dataGrid(_this.opts.gridOpts);

            $grid.jqGrid('setGroupHeaders', {
                useColSpanStyle: true,
                groupHeaders: groupHeaders1
            });

            $grid.jqGrid('setGroupHeaders', {
                useColSpanStyle: true,
                groupHeaders: groupHeaders2
            });

            //修改商品信息的rowspan值，使其支持三行合并
            $("th[role='columnheader'][rowspan='2']").attr("rowspan", "3");
        } else {
            _this.opts.gridOpts.data = [];

            $("#gridDiv").empty().html('<table id="grid"></table>');
            $("#grid").dataGrid(_this.opts.gridOpts);
        }
    },

    //数量格式化
    qtyFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            return customMinusToRedFormatter(cellvalue);
        }

        return '-';
    },

    //金额格式换
    amountFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            return '￥' + cellvalue;
        }

        return '-';
    }
};