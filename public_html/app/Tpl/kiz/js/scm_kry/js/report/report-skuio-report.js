/**
 * 商品月出入库明细表
 * @auth LiXing
 */

var reportSkuIOReport = {
    //默认参数
    opts : {
        urlRoot : "",
        queryUrl : "&act=report_stock_detail_skuio_ajax",
        exportUrl : "&",
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
            colNames:['库存类型', '商品分类', '商品编码', '商品名称（规格）', '单位'],
            colModel:[
                {name: 'wmTypeName', index: 'wmTypeName', width: 80, align: 'center',sortable: false},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, align: 'center',sortable: false},
                {name: 'skuCode', index: 'skuCode', width: 100, align: 'left',sortable: false},
                {name: 'skuName', index: 'skuName', width: 180, align: 'left',sortable: false},
                {name: 'uom', index: 'uom', width: 60, align: 'center',sortable: false}
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
                    $(this).footerData("set", reportSkuIOReport.opts.footerData);
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

                if(!reportSkuIOReport.opts.sorting){
                    //排序开始
                    reportSkuIOReport.opts.sorting = true;

                    $("#search").click();
                    $("#grid").sortGrid(index, false, sortorder);

                    //排序结束
                    reportSkuIOReport.opts.sorting = false;
                    return 'stop';
                }
            }
        }
    },

    //初始化
    _init : function (args) {

        var _this = this;

        _this.opts = $.extend(true, this.opts, args || {});
        // refreshWms($('select[name=commercialId]').val());
        // delegateSelect('#commercialId');

        delegateCheckbox('wmTypeIds', '#wm-type-all', false);
        delegateCheckbox('skuTypeIds', '#sku-type-all', false);

        $(_this.opts.listGridId).dataGrid(_this.opts.gridOpts);

        _this.delegateQuery();

        _this.delegateExport();

        _this.delegateUndoAll();

        // var commercialArray = $("#commercialId").parent().find("li");
        // if(commercialArray.length>1){
        //     $(commercialArray[1]).click();
        // }

        $('#search').click();

        $.setSearchFocus();
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
            var queryCondition = reportSkuIOReport.queryCondition();

            $("#export").blur();

            if(_this.opts.cachedQueryConditions!=""&&JSON.stringify(queryCondition) != _this.opts.cachedQueryConditions){
                $.layerMsg('条件已改变，请先点击查询按钮！', false);
                return;
            }
            if (_this.opts.gridOpts.data.length == 0) {
                $.layerMsg('导出记录为空！', false);
                return;
            }

            //序列化文案信息
            queryCondition["wmTypeName"] = $("#wmTypeId-all-em").text();
            queryCondition["wherehouseName"] = $("#wmId-all-em").text();
            queryCondition["skuTypeName"] = $("#skuTypeId-all-em").text();
            // queryCondition["commercialIdName"] = $("#commercialId").parent().find("em").text();

            queryCondition = $.param(queryCondition, true);
            window.open(_this.opts.urlRoot + _this.opts.exportUrl + queryCondition, '_self');
        });
    },

    /**统一查询条件**/
    queryCondition:function(){
        var queryType = $('input[name="queryType"]:checked').val();
        var isEnable = $("#isEnable").is(":checked");
        var isDisable = $("#isDisable").is(":checked");
        var isDelete = $("#isDelete").is(":checked");
        var skuStatusName = "";

        if(isEnable) skuStatusName = "启用";
        if(isDisable) skuStatusName = skuStatusName==""?"停用":skuStatusName+",停用";
        if(isDelete) skuStatusName = skuStatusName==""?"删除":skuStatusName+",删除";

        var conditions = {
            sidx:"sku_id",
            sord:"ASC",
            queryType:queryType,
            wmIds:$("#warehouseIds").val(),
            skuTypeIds:$("#skuTypeIds").val(),
            wmTypeIds:$("#wmTypeIds").val(),
            // commercialId:$("#commercialId").val(),
            skuNameOrCode:$("#skuNameOrCode").val(),
            confirmDateStart:queryType=="1"?$("#confirmDateStart").val():$("#dateStart").val(),
            confirmDateEnd:queryType=="1"?$("#confirmDateEnd").val():$("#dateEnd").val(),
            isEnable:isEnable?1:0,
            isDisable:isDisable?1:0,
            isDelete:isDelete?1:0,
            skuStatusName:skuStatusName,
            fiscalPeriod:queryType=="1"?"":$("#fiscalPeriodStr").html()
        }

        if(conditions.isDelete==0) delete conditions["isDelete"];
        if(conditions.isEnable==0) delete conditions["isEnable"];
        if(conditions.isDisable==0) delete conditions["isDisable"];
        if(conditions.fiscalPeriod) conditions.fiscalPeriod = conditions.fiscalPeriod.trim();

        return conditions;
    },

    delegateUndoAll : function(){
        $("#undo-all").on("click",function(){
            $('#confirmDateColumn').show();
            $("#fiscalPeriodColumn").find("li").first().click();
            $('#fiscalPeriodColumn').hide();

            // var commercialArray = $("#commercialId").parent().find("li");
            if(commercialArray.length>1){
                $(commercialArray[1]).click();
            }else{
                $(commercialArray[0]).click();
            }
        });
    },

    //查询
    query : function () {
        var _this = this;
        var queryCondition = _this.queryCondition();
        _this.opts.cachedQueryConditions = JSON.stringify(queryCondition);//缓存查询条件

        // if(queryCondition.commercialId==""){
        //     $.layerMsg('请选择品牌/商户！');
        //     return false;
        // }

        $("#load_grid").show();
        $.ajax({
            type: "post",
            async: true,
            data : queryCondition,
            url : _this.opts.urlRoot + _this.opts.queryUrl,
            success: function (data) {
                if (data) {
                    _this.buildTable(data);
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

            //每次仅获取前5列，即是商品基本信息所在的列
            var fixedColNames = reportSkuIOReport.opts.gridOpts.colNames.slice(0, 5);
            var fixedColModel = reportSkuIOReport.opts.gridOpts.colModel.slice(0, 5);

            var dynamicColNames = [],
                dynamicColModel = [],
                groupHeaders1 = [],
                groupHeaders2 = [],
                footerData = {qtySum : "合计"};

            var pushedGroupHeaders1 = [];
            var pushedGroupHeaders2 = [];
            var pushedWarehouses = [];

            $.each(skuVOs, function(i, sku) {

                $.each(sku.skuIoDetailVOs, function(j, detail) {
                    if(detail.qty != undefined && detail.qty != null) {
                        sku['qty_' + detail.modelNumber] = detail.qty;
                        sku['amount_' + detail.modelNumber] = detail.amount;
                    }

                    if(footerData['qty_' + detail.modelNumber] != undefined && footerData['qty_' + detail.modelNumber] != null){
                        footerData['qty_' + detail.modelNumber] = $.toFixed(footerData['qty_' + detail.modelNumber] + detail.qty);
                    } else{
                        footerData['qty_' + detail.modelNumber] = detail.qty;
                    }

                    if(footerData['amount_' + detail.modelNumber] != undefined && footerData['amount_' + detail.modelNumber] != null){
                        footerData['amount_' + detail.modelNumber] = $.toFixed(footerData['amount_' + detail.modelNumber] + detail.amount);
                    } else{
                        footerData['amount_' + detail.modelNumber] = detail.amount;
                    }

                    if(pushedWarehouses.indexOf(detail.modelNumber) < 0){

                        pushedWarehouses.push(detail.modelNumber);

                        dynamicColNames.push('数量');
                        dynamicColNames.push('金额');

                        dynamicColModel.push({
                            name: 'qty_' + detail.modelNumber,
                            index: 'qty_' + detail.modelNumber,
                            width: 60,
                            align: "right",
                            sortable: false,
                            sorttype: "number",
                            formatter: _this.qtyFormatter
                        });


                        dynamicColModel.push({
                            name: 'amount_' + detail.modelNumber,
                            index: 'amount_' + detail.modelNumber,
                            width: 75,
                            align: "right",
                            sortable: false,
                            sorttype: "number",
                            formatter: _this.amountFormatter
                        });

                        if(pushedGroupHeaders2.indexOf(detail.modelNumber) < 0){
                            groupHeaders2.push({
                                startColumnName: 'qty_' + detail.modelNumber,
                                numberOfColumns: 2,
                                titleText: detail.thisLabel
                            });
                            pushedGroupHeaders2.push(detail.modelNumber);

                            if(pushedGroupHeaders1.indexOf(detail.parentLabel) < 0){
                                groupHeaders1.push({
                                    startColumnName: 'qty_' + detail.modelNumber,
                                    numberOfColumns: 2,
                                    titleText: detail.parentLabel
                                });
                                pushedGroupHeaders1.push(detail.parentLabel);
                            }else {
                                if(groupHeaders1.length > 0) {
                                    var preGroupHeader1 = groupHeaders1.pop();
                                    if (preGroupHeader1 && preGroupHeader1.titleText === detail.parentLabel) {
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

            //如果包含期初期末，则合并期初期末
            if(pushedGroupHeaders1.indexOf("期末库存")>0){
                var allParent = $("#grid_rn").parent().find("th");
                for(var i = 0;i<allParent.length;i++){
                    var eh = $(allParent[i]);
                    if(eh.html()=="期初库存"||eh.html()=="期末库存")
                        eh.attr("rowspan","2");
                }
                $(".jqg-third-row-header").find("th").first().hide();
            }
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

// /**
//  * 监听下拉选框
//  * @param name
//  * @param id
//  */
// function delegateSelect(id){
//     //业务类型 条件选择
//     $(document).delegate(id, "change", function(){
//         refreshWms($('select[name=commercialId]').val());
//     });
// };