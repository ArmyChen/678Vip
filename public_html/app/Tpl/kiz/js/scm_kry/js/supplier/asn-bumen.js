/**
 * 库存分布明细表
 * @auth linc, Jun 6 2016
 */

var asnSi = {
    //默认参数
    opts : {
        urlRoot : ctxPath,
        queryUrl : "&act=go_bumen_index_ajax2",
        exportUrl : "&act=go_bumen_index_export",
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
            colNames:['id','类别id','类别', '编号', '名称', '单位', '单价', '直拨', '退料', '合计', '直拨','退料','合计'],
            colModel:[
                {name: 'id', index: 'id', width: 80, align: 'center'},
                {name: 'skuTypeId', index: 'skuTypeId', width: 80, align: 'center'},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, align: 'center'},
                {name: 'skuCode', index: 'skuCode', width: 80, align: 'center'},
                {name: 'skuName', index: 'skuName', width: 100, align: 'left'},
                {name: 'uom', index: 'uom', width: 180, align: 'left'},
                {name: 'price', index: 'price', width: 60, align: 'center'},
                {name: 'zhinum', index: 'zhinum', width: 80, align: 'center'},
                {name: 'tuinum', index: 'tuinum', width: 80, align: 'center'},
                {name: 'sumnum', index: 'sumnum', width: 80, align: 'center'},
                {name: 'zhiprice', index: 'zhiprice', width: 80, align: 'center'},
                {name: 'tuiprice', index: 'tuiprice', width: 80, align: 'center'},
                {name: 'sumprice', index: 'sumprice', width: 100, align: 'left'},
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
                    $(this).footerData("set", asnSi.opts.footerData);
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

                if(!asnSi.opts.sorting){
                    //排序开始
                    asnSi.opts.sorting = true;

                    $("#search").click();
                    $("#grid").sortGrid(index, false, sortorder);

                    //排序结束
                    asnSi.opts.sorting = false;
                    return 'stop';
                }
            }
        }
    },

    //初始化
    _init : function (args) {

        var _this = this;

        _this.opts = $.extend(true, this.opts, args || {});

        // delegateCheckbox('wmTypeIds', '#wm-type-all', false);
        // delegateCheckbox('skuTypeIds', '#sku-type-all', false);
        //
        // //默认勾选第一个商户（品牌或商户）
        // if($('#commercialId-1')) {
        //     $('#commercialId-1').click();
        // }

        $(_this.opts.listGridId).dataGrid(_this.opts.gridOpts);

        _this.opts.cachedQueryConditions = serializeFormById(_this.opts.queryConditionsId);//缓存查询条件

        _this.delegateQuery();

        _this.delegateExport();

        _this.delegateUndoAll();

        $('#search').click();

        $.setSearchFocus();

        // if(commercialId!="-1"){
        //     refreshWms(commercialId);
        // }
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
            var vDataNames = $('#wmId-all-em').text();

            conditions = conditions.replace('skuNameOrCode', 'skuNameOrCodeNotWanted');
            conditions += ('&skuNameOrCode=' + skuNameOrCode);
            conditions += ('&wmTypeNames=' + wmTypeNames);
            conditions += ('&skuTypeNames=' + skuTypeNames);
            conditions += ('&commercialNames=' + commercialNames);
            conditions += ('&vDataNames=' + vDataNames);

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
        // if (typeof conditions.wmTypeIds == "string") {
        //     var wmTypeIds = [];
        //     wmTypeIds.push(conditions.wmTypeIds);
        //     conditions["wmTypeIds"] = wmTypeIds;
        // }
        // if (typeof conditions.wmIds == "string") {
        //     var wmIds = [];
        //     wmIds.push(conditions.wmIds);
        //     conditions["wmIds"] = wmIds;
        // }
        // if (typeof conditions.skuTypeIds == "string") {
        //     var skuTypeIds = [];
        //     skuTypeIds.push(conditions.skuTypeIds);
        //     conditions["skuTypeIds"] = skuTypeIds;
        // }
        // if (typeof conditions.commercialIds == "string") {
        //     var commercialIds = [];
        //     commercialIds.push(conditions.commercialIds);
        //     conditions["commercialIds"] = commercialIds;
        // }


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
                // console.log(data);
                //var data = $.parseJSON(json);
                if (data) {
                    _this.buildTable(data);
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

            //每次仅获取前6列，即是商品基本信息所在的列
            var fixedColNames = asnSi.opts.gridOpts.colNames;
            var fixedColModel = asnSi.opts.gridOpts.colModel;

            var dynamicColNames = [],
                dynamicColModel = [],
                groupHeaders1 = [],
                groupHeaders2 = [],
                groupHeaders3 = [],
                footerData = {price : "合计"};

            var data = skuVOs.goods;
            groupHeaders1.push({
                startColumnName: 'id',
                numberOfColumns: 7 ,
                titleText: '物品'
            });
            groupHeaders2.push({
                startColumnName: 'zhinum',
                numberOfColumns: 3 ,
                titleText: '数量'
            });
            groupHeaders3.push({
                startColumnName: 'zhiprice',
                numberOfColumns: 3 ,
                titleText: '金额'
            });
            $.each(data, function(j, e) {
                if(footerData['zhinum'] != undefined && footerData['zhinum'] != null){
                    footerData['zhinum'] = parseInt(parseInt(footerData['zhinum']) + parseInt(e.zhinum));
                } else{
                    footerData['zhinum'] = e.zhinum;
                }

                if(footerData['zhiprice'] != undefined && footerData['zhiprice'] != null){
                    footerData['zhiprice'] = parseFloat(parseFloat(footerData['zhiprice']) + parseFloat(e.zhiprice));
                } else{
                    footerData['zhiprice'] = e.zhiprice;
                }

                if(footerData['tuiprice'] != undefined && footerData['tuiprice'] != null){
                    footerData['tuiprice'] = parseFloat(parseFloat(footerData['tuiprice']) + parseFloat(e.tuiprice));
                } else{
                    footerData['tuiprice'] = e.tuiprice;
                }

                if(footerData['tuinum'] != undefined && footerData['tuinum'] != null){
                    footerData['tuinum'] = parseInt(parseInt(footerData['tuinum']) + parseInt(e.tuinum));
                } else{
                    footerData['tuinum'] = e.tuinum;
                }

                if(footerData['sumprice'] != undefined && footerData['sumprice'] != null){
                    footerData['sumprice'] = parseFloat(parseFloat(footerData['sumprice']) + parseFloat(e.sumprice));
                } else{
                    footerData['sumprice'] = e.sumprice;
                }

                if(footerData['sumnum'] != undefined && footerData['sumnum'] != null){
                    footerData['sumnum'] = parseInt(parseInt(footerData['sumnum']) + parseInt(e.sumnum));
                } else{
                    footerData['sumnum'] = e.sumnum;
                }
            });
            //商品基本信息所在的colName/colModel
            Array.prototype.push.apply(finalColNames, fixedColNames);
            Array.prototype.push.apply(finalColModels, fixedColModel);
            //库存所在的colName/colModel
            Array.prototype.push.apply(finalColNames, dynamicColNames);
            Array.prototype.push.apply(finalColModels, dynamicColModel);

            _this.opts.gridOpts.colNames = finalColNames;
            _this.opts.gridOpts.colModel = finalColModels;
            _this.opts.gridOpts.data = data;
            _this.opts.footerData = footerData;

            var $gridDiv =  $("#gridDiv");
            _this.opts.gridOpts.shrinkToFit = (680 + 135 * groupHeaders1.length*groupHeaders2.length*groupHeaders3.length) < $gridDiv.width();

            $gridDiv.empty().html('<table id="grid"></table>');
            $("#grid").dataGrid(_this.opts.gridOpts);

            $("#grid").jqGrid('setGroupHeaders', {
                useColSpanStyle: true,
                groupHeaders: [
                    {
                        startColumnName: 'id',
                        numberOfColumns: 7 ,
                        titleText: '物品'
                    },
                    {
                        startColumnName: 'zhinum',
                        numberOfColumns: 3 ,
                        titleText: '数量'
                    },
                    {
                        startColumnName: 'zhiprice',
                        numberOfColumns: 3 ,
                        titleText: '金额'
                    }
                ]
            });
            // //修改商品信息的rowspan值，使其支持三行合并
            // $("th[role='columnheader'][rowspan='1']").attr("rowspan", "2");
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