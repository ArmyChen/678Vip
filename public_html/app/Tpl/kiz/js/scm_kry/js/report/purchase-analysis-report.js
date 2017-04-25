/**
 * 采购分析表
 * Created by mayi on 2016/1/5.
 */

var purchaseAnalysisReport = {
    //默认参数
    opts : {
        urlRoot : "/report/purchaseanalysis",
        querySupplierSkuUrl : "/querySupplierSkuData",
        querySkuSupplierUrl: "/querySkuSupplierData",
        exportUrl : "/export",
        queryData : "",
        footerData : {},
        oldCommercialId : "",
        cachedQueryConditions: '', //缓存页面条件
        exportConditions_1: [], //导出的查询条件（供应商-商品分析）
        exportConditions_2: [] //导出的查询条件（商品-供应商分析）
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        //初始化门店/商户多选
        _this.initCommercials();

        //供应商-商品查询按钮点击
        $(document).delegate("#search", "click", function () {
            _this.query();
        });

        //商品-供应商查询按钮点击
        $(document).delegate("#search_2", "click", function () {
            _this.query_2();
        });

        //tab标签切换
        $('.tab-nav .tab-btn').on('click', function () {
            if ($(this).hasClass("btn-active")) {
                return;
            }

            $(this).removeClass('btn-default').addClass('btn-active');
            $(this).siblings('button').removeClass('btn-active').addClass('btn-default');
            //显示区域
            $('.tab-box').hide();
            $('#' + $(this).attr("data-box-id")).show();

            //显示导出按钮
            $('.freeDownExcel').hide();
            $('#' + $(this).attr("down-excel-id")).show();
        });
        //_this.opts.cachedQueryConditions = serializeFormById("queryConditions");//缓存查询条件

        //页面初始化完成后查询供应商-商品采购
        $("#search").click();

        //供应商-商品查询条件重置
        $("#undo-all_1").on("click", function (e) {
            e.preventDefault();
            bkeruyun.clearData($(this).parents('.aside'));

            if (!bkeruyun.isPlaceholder()) {
                JPlaceHolder.init();
            }

            $("#commercialId-1").click();
            $("#skuTypeId").val("");
            $("#skuTypeId").trigger("chosen:updated");
        });

        //商品-供应商查询条件重置
        $("#undo-all_2").on("click", function (e) {
            e.preventDefault();
            bkeruyun.clearData($(this).parents('.aside'));

            if (!bkeruyun.isPlaceholder()) {
                JPlaceHolder.init();
            }

            $("#commercialId_2-1").click();
            $("#skuId").val("-1");
            $("#skuId").trigger("chosen:updated");
        });

        $.setSearchFocus();
    },

    //初始化门店选择
    initCommercials: function () {
        // 交互
        $(".multi-select > .select-control").on("click",function(){
            var showList = $(this).next(".multi-select-con");
            if(showList.is(":hidden")){
                $(".multi-select-con").hide();
                showList.show();
            }else{
                showList.hide();
            }
        });

        delegateCheckbox('commercialIds');

        //任意点击隐藏下拉层
        $(document).bind("click",function(e){
            var target = $(e.target);
            //当target不在popover/coupons-set 内是 隐藏
            if(target.closest(".multi-select-con").length == 0 && target.closest(".select-control").length == 0){
                $(".multi-select-con").hide();
            }
        });

        //选中品牌
        $("#commercialId-1").click();
        $("#commercialId_2-1").click();
    },

    //供应商-商品分析查询
    query : function () {
        var _this = this;

        var condition = $.extend(true, {}, $("#queryConditions").getFormData() || {});

        // if (condition.commercialIds == undefined || condition.commercialIds.length == 0) {
        //     $.layerMsg('请选择品牌/商户!', false);
        //     return false;
        // } else if (typeof condition.commercialIds != "object") {
        //     var commercialIds = [];
        //     commercialIds.push(condition.commercialIds);
        //     condition.commercialIds = commercialIds;
        // }

        // var dateStart = condition.dateStart;
        // var dateEnd = condition.dateEnd;
        //
        // if (!dateStart || dateStart.trim().length == 0) {
        //     $.layerMsg('请选择开始日期！', false);
        //     return false;
        // } else if (!dateEnd || dateEnd.trim().length == 0) {
        //     var date = new Date();
        //     dateEnd = date.yyyymmdd();
        //     $("#dateEnd").val(dateEnd);
        // } else if(dateStart.replace('-','') > dateEnd.replace('-','')){
        //     $.layerMsg('开始日期不能大于结束日期！', false);
        //     return false;
        // }
        //
        // var iDays = _this.getDays(dateStart, dateEnd);
        //
        // if (iDays > 365) {
        //     $.layerMsg('查询日期不应超过一年！', false);
        //     return false;
        // }

        var billDateStart = condition.billDateStart;
        var billDateEnd = condition.billDateEnd;

        if (!billDateStart || billDateStart.trim().length == 0) {
            $.layerMsg('请选择开始日期！', false);
            return false;
        } else if (!billDateEnd || billDateEnd.trim().length == 0) {
            var date = new Date();
            billDateEnd = date.yyyymmdd();
            $("#billDateEnd").val(billDateEnd);
        } else if(billDateStart.replace('-','') > billDateEnd.replace('-','')){
            $.layerMsg('开始日期不能大于结束日期！', false);
            return false;
        }

        var iDays = _this.getDays(billDateStart, billDateEnd);

        if (iDays > 365) {
            $.layerMsg('查询日期不应超过一年！', false);
            return false;
        }

        var commercialNames = [];
        var $commercials = $(".commercials");
        if ($commercials.hasClass("login-shop")) {
            var commercial = $(".commercials").find("option:selected").text().trim()
            commercialNames.push(commercial);
        } else {
            $commercials.find(".commercial.checkbox-check").each(function () {
                commercialNames.push($(this).text().trim());
            });
        }

        var supplier = $("#supplierId").find("option:selected").text().trim();
        var skuTypeName = $("#skuTypeId").find("option:selected").text().trim();
        var skuStatuses = "";
        if (!!condition.isEnable) {
            skuStatuses += "启用，";
        }
        if (!!condition.isDisable) {
            skuStatuses += "停用，";
        }
        if (!!condition.isDelete) {
            skuStatuses += "删除，";
        }

        var length = skuStatuses.length;
        if (length > 0) {
            skuStatuses = skuStatuses.substring(0, length - 1);
        }

        var exportConditions = [];
        exportConditions.push({key : "商品名称/编码", value : condition.keyword});
        exportConditions.push({key : "商品类别", value : skuTypeName});
        exportConditions.push({key : "品牌/商户", value : commercialNames.join("，")});
        exportConditions.push({key : "供应商", value : supplier});
        // exportConditions.push({key : "查询日期", value : condition.dateStart + '~' + condition.dateEnd});
        exportConditions.push({key : "单据日期", value : condition.billDateStart + '~' + condition.billDateEnd});
        exportConditions.push({key : "商品状态", value : skuStatuses});
        exportConditions.push({key : "导出人", type : 'user'});
        exportConditions.push({key : "导出日期", type : 'date'});

        condition = JSON.stringify(condition);
        bkeruyun.showLoading();
        $.ajax({
            type: "post",
            async: false,
            url : ctxPath + _this.opts.urlRoot + _this.opts.querySupplierSkuUrl,
            contentType : 'application/json',
            data : condition,
            dataType : 'json',
            success: function (data) {
                _this.opts.exportConditions_1 = exportConditions;
                var commecials = commercialNames.join("，");
                $("#commercials").text(commecials);
                $("#commercials").attr("title", commecials);
                $("#supplier").text(supplier);
                $("#supplier").attr("title", supplier);
                $("#dateStartSpan").text(billDateStart);
                $("#dateEndSpan").text(billDateEnd);
                $("#chart-head").show();

                if (data && data.skuData.length > 0) {
                    $("#noData").hide();
                    $("#showData").show();
                    _this.buildTable(data);
                } else {
                    $("#showData").hide();
                    $("#noData").show();
                }
            },
            complete: function() {
                setTimeout(function() {
                    bkeruyun.hideLoading();
                }, 500);
            }
        });
    },

    //商品-供应商分析查询
    query_2 : function () {
        var _this = this;

        var condition = $.extend(true, {}, $("#queryConditions_2").getFormData() || {});

        if (condition.skuId == undefined || condition.skuId == '') {
            $.layerMsg('请选择商品!', false);
            return false;
        }

        // if (condition.commercialIds == undefined || condition.commercialIds.length == 0) {
        //     $.layerMsg('请选择品牌/商户!', false);
        //     return false;
        // } else if (typeof condition.commercialIds != "object") {
        //     var commercialIds = [];
        //     commercialIds.push(condition.commercialIds);
        //     condition.commercialIds = commercialIds;
        // }

        // var dateStart = condition.dateStart;
        // var dateEnd = condition.dateEnd;
        //
        // if (!dateStart || dateStart.trim().length == 0) {
        //     $.layerMsg('请选择开始日期！', false);
        //     return false;
        // } else if (!dateEnd || dateEnd.trim().length == 0) {
        //     var date = new Date();
        //     dateEnd = date.yyyymmdd();
        //     $("#dateEnd").val(dateEnd);
        // } else if(dateStart.replace('-','') > dateEnd.replace('-','')){
        //     $.layerMsg('开始日期不能大于结束日期！', false);
        //     return false;
        // }
        //
        // var iDays = _this.getDays(dateStart, dateEnd);
        //
        // if (iDays > 365) {
        //     $.layerMsg('查询日期不应超过一年！', false);
        //     return false;
        // }

        var billDateStart = condition.billDateStart;
        var billDateEnd = condition.billDateEnd;

        if (!billDateStart || billDateStart.trim().length == 0) {
            $.layerMsg('请选择开始日期！', false);
            return false;
        } else if (!billDateEnd || billDateEnd.trim().length == 0) {
            var date = new Date();
            billDateEnd = date.yyyymmdd();
            $("#billDateEnd").val(billDateEnd);
        } else if(billDateStart.replace('-','') > billDateEnd.replace('-','')){
            $.layerMsg('开始日期不能大于结束日期！', false);
            return false;
        }

        var iDays = _this.getDays(billDateStart, billDateEnd);

        if (iDays > 365) {
            $.layerMsg('查询日期不应超过一年！', false);
            return false;
        }

        var commercialNames = [];
        var $commercials = $(".commercials_2");
        if ($commercials.hasClass("login-shop")) {
            var commercial = $commercials.find("option:selected").text().trim();
            commercialNames.push(commercial);
        } else {
            $commercials.find(".commercial.checkbox-check").each(function () {
                commercialNames.push($(this).text().trim());
            });
        }

        var sku = $("#skuId").find("option:selected").text().trim();

        var exportConditions = [];
        exportConditions.push({key : "商品名称", value : sku});
        exportConditions.push({key : "品牌/商户", value : commercialNames.join("，")});
        // exportConditions.push({key : "查询日期", value : condition.dateStart + '~' + condition.dateEnd});
        exportConditions.push({key : "单据日期", value : condition.billDateStart + '~' + condition.billDateEnd});
        exportConditions.push({key : "导出人", type : 'user'});
        exportConditions.push({key : "导出日期", type : 'date'});

        condition = JSON.stringify(condition);
        bkeruyun.showLoading();
        $.ajax({
            type: "post",
            async: false,
            url : ctxPath + _this.opts.urlRoot + _this.opts.querySkuSupplierUrl,
            contentType : 'application/json',
            data : condition,
            dataType : 'json',
            success: function (data) {
                _this.opts.exportConditions_2 = exportConditions;
                var commercials = commercialNames.join("，");
                $("#commercials_2").text(commercials);
                $("#commercials_2").attr("title", commercials);
                $("#sku").text(sku);
                $("#sku").attr("title", sku);
                $("#dateStartSpan_2").text(billDateStart);
                $("#dateEndSpan_2").text(billDateEnd);

                $("#chart-head_2").show();

                if (data && data.skuData.length > 0) {
                    $("#noData_2").hide();
                    $("#showData_2").show();
                    _this.initChart("line", data.priceData, data.dateTimeStart, data.dateTimeEnd, data.taxAvgPrice);
                    _this.buildTable_2(data);
                } else {
                    $("#showData_2").hide();
                    $("#noData_2").show();
                }
                $(document).scroll(); //修复火狐的bug，bugId:9006
            },
            complete: function() {
                setTimeout(function() {
                    bkeruyun.hideLoading();
                }, 500);
            }
        });
    },

    //构建供应商-商品分析表格
    buildTable : function (data) {
        var _this = this;

        var $gridDiv =  $("#gridDiv");
        $gridDiv.empty().html('<table id="grid"></table>');
        var $grid = $("#grid");

        $grid.dataGrid({
            data: data.skuData,
            datatype: 'local',
            showEmptyGrid: true,
            autowidth: true,
            rowNum: 9999,
            footerrow: true,
            colNames: ['所属分类', '商品名称（规格）','单位', '平均价格（含税）', '平均价格', '数量', '金额（含税）', '金额占比', '数量', '金额（含税）', '金额占比', '退货率'],
            colModel: [
                {name: 'skuTypeName', index: 'skuTypeName', width: 130, align: 'left', formatter: _this.skuTypeFormatter},
                {name: 'skuName', index: 'skuName', width: 190, align: 'left', formatter: _this.skuFormatter},
                {name: 'unitName', index: 'unitName', width: 80, align: 'center'},
                {name: 'taxAvgPrice', index: 'taxAvgPrice', width: 150, align: 'right', sorttype: "number", formatter: _this.amountFormatter},
                {name: 'avgPrice', index: 'avgPrice', width: 140, align: 'right', sorttype: "number", formatter: _this.amountFormatter},
                {name: 'purQty', index: 'purQty', width: 130, align: 'right', sorttype: "number", formatter: _this.qtyFormatter},
                {name: 'taxPurAmount', index: 'taxPurAmount', width: 140, align: 'right', sorttype: "number", formatter: _this.amountFormatter},
                {name: 'purAmountRatio', index: 'purAmountRatio', width: 90, align: "right", sorttype: "number", formatter: _this.ratioFormatter},
                {name: 'retQty', index: 'retQty', width: 130, align: "right", sorttype: "number", formatter: _this.qtyFormatter},
                {name: 'taxRetAmount', index: 'taxRetAmount',width: 140, align: "right", sorttype: "number", formatter: _this.amountFormatter},
                {name: 'retAmountRatio', index: 'retAmountRatio', width: 90, align: "right", sorttype: "number", formatter: _this.ratioFormatter},
                {name: 'retRatio', index: 'retRatio', width: 90, align: "right", sorttype: "number", formatter: _this.ratioFormatter}
            ],
            sortname: 'skuTypeName',
            sortorder:'asc',
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
                    $(this).footerData("set",
                        {
                            avgPrice: "合计:",
                            purQty: data.totalPurchaseQty,
                            taxPurAmount: data.totalPurchaseAmount,
                            //purAmountRatio: '100',
                            retQty: data.totalReturnQty,
                            taxRetAmount: data.totalReturnAmount
                            //retAmountRatio: '100'
                        }
                    );
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
            }
        });

        $grid.jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                {startColumnName: 'taxAvgPrice', numberOfColumns: 5, titleText: '采购'},
                {startColumnName: 'retQty', numberOfColumns: 4, titleText: '退货'}
            ]
        });
    },

    //构建商品-供应商分析表格
    buildTable_2 : function (data) {
        var _this = this;

        var $gridDiv =  $("#gridDiv_2");
        $gridDiv.empty().html('<table id="grid_2"></table>');
        var $grid = $("#grid_2");

        $grid.dataGrid({
            data: data.skuData,
            datatype: 'local',
            showEmptyGrid: true,
            autowidth: true,
            rowNum: 9999,
            height: 217,
            footerrow: true,
            colNames: ['供应商名称', '平均价格（含税）', '平均价格', '数量', '单位', '金额（含税）', '金额占比', '数量', '单位', '金额（含税）', '金额占比', '退货率'],
            colModel: [
                {name: 'supplierName', index: 'supplierName', width: 200, align: 'left', formatter: _this.supplierFormatter},
                {name: 'taxAvgPrice', index: 'taxAvgPrice', width: 150, align: 'right', sorttype: "number", formatter: _this.amountFormatter},
                {name: 'avgPrice', index: 'avgPrice', width: 140, align: 'right', sorttype: "number", formatter: _this.amountFormatter},
                {name: 'purQty', index: 'purQty', width: 130, align: 'right', sorttype: "number", formatter: _this.qtyFormatter},
                {name: 'unitName', index: 'unitName', width: 50, align: 'center', formatter: function(cellvalue, options, rowObject){
                    if (!!rowObject.purQty || rowObject.purQty == 0) {
                        return cellvalue;
                    }

                    return '-';
                }},
                {name: 'taxPurAmount', index: 'taxPurAmount', width: 140, align: 'right', sorttype: "number", formatter: _this.amountFormatter},
                {name: 'purAmountRatio', index: 'purAmountRatio', width: 90, align: "right", sorttype: "number", formatter: _this.ratioFormatter},
                {name: 'retQty', index: 'retQty', width: 130, align: "right", sorttype: "number", formatter: _this.qtyFormatter},
                {name: 'unitName', index: 'unitName', width: 50, align: 'center', formatter: function(cellvalue, options, rowObject){
                    if (!!rowObject.retQty || rowObject.retQty == 0) {
                        return cellvalue;
                    }

                    return '-';
                }},
                {name: 'taxRetAmount', index: 'taxRetAmount',width: 140, align: "right", sorttype: "number", formatter: _this.amountFormatter},
                {name: 'retAmountRatio', index: 'retAmountRatio', width: 90, align: "right", sorttype: "number", formatter: _this.ratioFormatter},
                {name: 'retRatio', index: 'retRatio', width: 90, align: "right", sorttype: "number", formatter: _this.ratioFormatter}
            ],
            sortname: 'skuTypeName',
            sortorder:'asc',
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
                    $(this).footerData("set",
                        {
                            supplierName: "合计:",
                            taxAvgPrice: data.taxAvgPrice,
                            avgPrice: data.avgPrice,
                            purQty: data.totalPurchaseQty,
                            taxPurAmount: data.totalTaxPurchaseAmount,
                            //purAmountRatio: '100',
                            retQty: data.totalReturnQty,
                            taxRetAmount: data.totalReturnAmount
                            //retAmountRatio: '100'
                        }
                    );
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
            }
        });

        $grid.jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                {startColumnName: 'taxAvgPrice', numberOfColumns: 6, titleText: '采购'},
                {startColumnName: 'retQty', numberOfColumns: 5, titleText: '退货'}
            ]
        });
    },

    //初始化EChart报表
    initChart : function (domId, data, dateTimeStart, dateTimeEnd, avgPrice) {
        var option = $.extend(true, {}, EChartOption);
        if (data && data.length > 0) {
            var series = [],
                legendData = [];
            $.each(data, function(i, n) {
                var obj = {
                    type:'line',
                    showAllSymbol: true
                };

                obj.name = n.supplierName;
                obj.data = n.purData;
                series.push(obj);

                legendData.push(n.supplierName);
            });

            var avgLine = $.extend(true, {}, avgPriceLine);
            avgLine.markLine.data[0][0].value = avgPrice;
            avgLine.markLine.data[0][0].yAxis = avgPrice;
            avgLine.markLine.data[0][1].yAxis = avgPrice;

            avgLine.markLine.data[0][0].xAxis = 0;
            avgLine.markLine.data[0][1].xAxis = dateTimeEnd;

            series.push(avgLine);
            legendData.push(avgLine.name);

            option.series = series;
            option.legend.data = legendData;
        }

        var charDom = document.getElementById(domId);
        // 基于准备好的dom，初始化echarts图表
        var myChart = echarts.init(charDom, macarons);

        // 为echarts对象加载数据
        myChart.setOption(option);
    },

    //返回供应商-商品分析的查询条件
    getExportConditions_1 : function() {
        return this.opts.exportConditions_1;
    },

    //返回商品-供应商分析的查询条件
    getExportConditions_2 : function() {
        return this.opts.exportConditions_2;
    },

    //数量格式化
    qtyFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            return cellvalue;
        }

        return '-';
    },

    //金额格式换
    amountFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            if (cellvalue == '合计:') {
                return cellvalue;
            }
            return '￥' + cellvalue;
        }

        return '-';
    },

    //占比格式化
    ratioFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            return cellvalue + '%';
        }

        return '-';
    },

    //商品名称格式化
    skuFormatter : function(cellvalue, options, rowObject) {
        if (rowObject.skuIsDelete == 1) {
            return cellvalue + "<span style='color:red'>(已删除)</span>";
        } else if (rowObject.skuIsDisable == 1) {
            return cellvalue + "<span style='color:red'>(已停用)</span>";
        } else {
            return cellvalue;
        }
    },

    //商品类型停用格式化
    skuTypeFormatter : function(cellvalue, options, rowObject) {
        if (rowObject.skuTypeIsDisable == 1) {
            return cellvalue + '<span style="color:red;">(已停用)</span>';
        }

        return cellvalue;
    },

    //供应商停用格式化
    supplierFormatter : function(cellvalue, options, rowObject) {
        if (rowObject.supplierIsDisable == '1') {
            return cellvalue + '<span style="color:red;">(已停用)</span>';
        }

        return cellvalue;
    },

    /**
     * 计算相隔天数
     * @param strDateStart
     * @param strDateEnd
     * @returns {Number|*}
     */
    getDays: function (strDateStart, strDateEnd) {
        var strSeparator = "-"; //日期分隔符
        var oDate1;
        var oDate2;
        var iDays;
        oDate1 = strDateStart.split(strSeparator);
        oDate2 = strDateEnd.split(strSeparator);
        var strDateS = new Date(oDate1[0] + "-" + oDate1[1] + "-" + oDate1[2]);
        var strDateE = new Date(oDate2[0] + "-" + oDate2[1] + "-" + oDate2[2]);
        iDays = parseInt(Math.abs(strDateS - strDateE) / 1000 / 60 / 60 / 24);//把相差的毫秒数转换为天数
        return iDays;
    }
};

Date.prototype.yyyymmdd = function () {
    var yyyy = this.getFullYear().toString();
    var mm = (this.getMonth() + 1).toString(); // getMonth() is zero-based
    var dd = this.getDate().toString();
    return yyyy + '-' + (mm[1] ? mm : "0" + mm[0]) + '-' + (dd[1] ? dd : "0" + dd[0]); // padding
};

/**
 * 监听下拉选框
 * @param name
 * @param id
 * @param refreshWmsOrNot 是否刷新“仓库”的下拉选框
 */
function delegateCheckbox(name){
    //业务类型 条件选择
    $(document).delegate(":checkbox[name='"+ name + "']","change",function(){
        var all = $(this).parents(".multi-select-items").find('.checkbox-all');
        associatedCheckAll(this, all);
        filterConditions(this, name, $(this).parents(".multi-select-con").prev(".select-control").find("em"),$(this).parents(".multi-select-con").next(":hidden"));
    });
    //业务类型 条件选择 全选
    $(document).delegate(".checkbox-all","change",function(){
        checkAll(this, name);
        filterConditions(this, name, $(this).parents(".multi-select-con").prev(".select-control").find("em"),$(this).parents(".multi-select-con").next(":hidden"));
    });
}

/**
 * 条件选择
 * @param checkboxName      string                  checkbox name
 * @param $textObj          jquery object           要改变字符串的元素
 * @param $hiddenObj        jquery object           要改变的隐藏域
 */
function filterConditions(target,checkboxName,$textObj,$hiddenObj){
    var checkboxs = $(target).parents(".multi-select-items").find(":checkbox[name='" + checkboxName + "']");
    var checkboxsChecked = $(target).parents(".multi-select-items").find(":checkbox[name='" + checkboxName + "']:checked");
    var len = checkboxs.length;
    var lenChecked = checkboxsChecked.length;
    var str = '';
    var value1 = '';

    for(var i=0;i<lenChecked;i++){
        if(i==0){
            str += checkboxsChecked.eq(i).attr("data-text");
            value1 += checkboxsChecked.eq(i).attr("value");
        }else{
            str += ',' + checkboxsChecked.eq(i).attr("data-text");
            value1 += "," + checkboxsChecked.eq(i).attr("value");
        }
    }
    $textObj.text(str);
    $hiddenObj.val(value1);

    if(lenChecked == len){
        $textObj.text("全部");
    }
}

/**
 *    associatedCheckAll     //关联全选
 *    @param  object         e           需要操作对象
 *    @param  jqueryObj      $obj        全选对象
 **/
function associatedCheckAll(e,$obj){
    var flag = true;
    var $name = $(e).attr("name");
    checkboxChange(e,'checkbox-check');
    $(e).parents(".multi-select-items").find("[name='"+ $name +"']:checkbox").not(":disabled").each(function(){
        if(!this.checked){
            flag = false;
        }
    });
    $obj.get(0).checked = flag;
    checkboxChange($obj.get(0),'checkbox-check');
}

/**
 *    checkbox               //模拟checkbox功能
 *    @param  object         element     需要操作对象
 *    @param  className      class       切换的样式
 **/
function checkboxChange(element,className){
    if(element.readOnly){return false;}
    if(element.checked){
        $(element).parent().addClass(className);
    }else{
        $(element).parent().removeClass(className);
    }
}

/**
 *    checked all            //全选
 *    @param  object         e           需要操作对象
 *    @param  nameGroup      string      checkbox name
 **/
function checkAll(e,nameGroup){
    if(e.checked){
        //alert($("[name='"+ nameGroup+"']:checkbox"));
        $(e).parents(".multi-select-items").find("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
            this.checked = true;
            checkboxChange(this,'checkbox-check');
        });
    }else{
        $(e).parents(".multi-select-items").find("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
            this.checked = false;
            checkboxChange(this,'checkbox-check');
        });
    }
    checkboxChange(e,'checkbox-check');
}

//报表默认参数
var EChartOption = {
    dataZoom: {
        show: true,
            start : 0,
            end : 100,
            showDetail: true
    },
    grid: {
        y2: 80
    },
    calculable: false,
    tooltip : {
        trigger: 'axis',
            axisPointer:{
            show: false,
                type : 'none',
                lineStyle: {
                type : 'dashed',
                width : 1
            }
        },
        formatter : function (params) {
            var date = new Date(params.value[0]);
            var month = date.getMonth() + 1;
            month = month < 10 ? '0' + month : month;
            var day = date.getDate();
            day = day < 10 ? '0' + day : day;
            return date.getFullYear() + '-'
                + month + '-'
                + day
                +  '<br/>'
                + params.seriesName
                + "<br/>当日均价：￥"
                + params.value[1];

            /*return params.value[2]
             + "<br/>"
             + params.seriesName
             + "<br/>当日均价：￥"
             + params.value[1];
             */
        }
    },
    legend: {
        data:[]
    },
    toolbox: {
        show : false,
            feature : {
            mark : {show: true},
            dataZoom : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']},
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    xAxis : [
        {
            type: 'time',
            name: '采购日期',
            splitNumber:10,
            axisLabel: {
                formatter: function(value, e, t) {
                    var date = new Date(value);
                    var month = date.getMonth() + 1;
                    month = month < 10 ? '0' + month : month;
                    var day = date.getDate();
                    day = day < 10 ? '0' + day : day;

                    var dateStr = month + ' - ' + day + '\n' + date.getFullYear();

                    if ($.inArray(dateStr, t._valueLabel) != -1) {
                        return '';
                    }

                    return dateStr;
                }
            }
        }
    ],
    yAxis : [
        {
            type: 'value',
            name: '平均价格(含税)',
            min: 0,
            axisLabel: {
                formatter: '￥{value}'
            }
        }
    ],
    series : [{data:[]}],
    noDataLoadingOption: {
        text: '暂无采购数据',
        effect: 'bubble',
        effectOption:{
            backgroundColor:"#fff"
        }
    }
};

//平均采购价标线默认参数
var avgPriceLine = {
    name: '平均采购价',
    type: 'line',
    data : [],
    markLine: {
        symbol:['none', 'none'],
        data : [
            [
                {name: '', value: 0, xAxis: 0, yAxis: 0},
                {name: '', xAxis: 0, yAxis: 0}
            ]
        ]
    }
};