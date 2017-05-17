

var skuioreport = {

    opts: {
        urlRoot: '',
        queryConditionsId: 'queryConditions',
        listGridId: '#grid',
        cachedQueryConditions: '' //缓存页面条件
    },

    //初始化
    _init: function (args) {

        var _this = this;

        _this.opts = $.extend(true, this.opts, args || {});

        // _this.initDates();

        _this.delegateReset();

       // refreshWms($('select[name=commercialId]').val());

        _this.initQueryList();

        delegateSelect('#commercialId');

        delegateCheckbox('skuTypeIds', '#sku-type-all', false);

        _this.opts.cachedQueryConditions = serializeFormById(_this.opts.queryConditionsId);

        $.setSearchFocus();

    },

    // 初始化查询结果列表
    initQueryList: function () {

        var _this = this;

        $(_this.opts.listGridId).dataGrid({
            formId: _this.opts.queryConditionsId,
            url: ctxPath + '&act=report_stock_detail_ajax',
            shrinkToFit:false,
            autoScroll: true,
            colNames: ['商品一级分类', '商品分类', '商品编码', '商品名称（规格）', '单位', /*'价格',*/
                '采购入库数', '验收退货数', '销售出库数', '销售退货数', '盘盈数', '盘亏数', '移库数（出）', '移库数（入）',
                '调拨数', '收货数', '在途数', '生产入库数', '生产用料数<br/><span style="font-size:12px; ">(净料)</span>',
                '生产用料数<br/><span style="font-size:12px; ">(毛料)</span>', '退回数', '报废数', '出库数（配送）', '配送数',
                '收货数','出库单（销售）','销售收货数', '出库数', '入库数'],
            colModel: [
                {name: 'skuParentTypeName', index: 'skuParentTypeName', width: 120, align: 'left'},
                {name: 'skuTypeName', index: 'skuTypeName', width: 120, align: 'left'},
                {name: 'skuCode', index: 'skuCode', width: 180, align: 'left'},
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
                {name: 'uom', index: 'uom', width: 80, align: 'center'},
                /*{name: 'price', index: 'price', width: 80, align: "right", formatter: customCurrencyFormatter},*/
                {name: 'purchaseSi', index: 'purchaseSi', width: 120, align: "right", sortable: false},
                { name: 'purchaseReturnSo', index: 'purchaseReturnSo', width: 120, align: "right", sortable: false},
                { name: 'saleSo', index: 'saleSo',width: 120, align: "right", sortable: false},
                {name: 'saleReturnSi', index: 'saleReturnSi', width: 120, align: "right", sortable: false},
                {name: 'ccProfitSi', index: 'ccProfitSi', width: 120, align: "right", sortable: false},
                {name: 'ccLossSo', index: 'ccLossSo', width: 120, align: "right", sortable: false},
                {name: 'transferSo', index: 'transferSo', width: 120, align: "right", sortable: false},
                {name: 'transferSi', index: 'transferSi', width: 120, align: "right", sortable: false},
                {name: 'allocationSo', index: 'allocationSo', width: 120, align: "right", sortable: false},
                {name: 'allocationReceiveSi', index: 'allocationReceiveSi', width: 120, align: "right", sortable: false},
                {name: 'allocationOnTheWay', index: 'allocationOnTheWay', width: 120, align: "right", sortable: false},
                {name: 'asnProductSi', index: 'asnProductSi', width: 120, align: "right", sortable: false},
                {name: 'asnProductBomNetSo', index: 'asnProductBomNetSo', width: 120, align: "right", sortable: false},
                {name: 'asnProductBomSo', index: 'asnProductBomSo', width: 120, align: "right", sortable: false},
                {name: 'commercialReturnSo', index: 'commercialReturnSo', width: 120, align: "right", sortable: false},
                {name: 'scrapSo', index: 'scrapSo', width: 120, align: "right", sortable: false},
                {name: 'deliverySo', index: 'deliverySo', width: 120, align: "right", sortable: false},
                {name: 'deliveryOnTheWay', index: 'deliveryOnTheWay', width: 120, align: "right", sortable: false},
                {name: 'deliveryReceiveSi', index: 'deliveryReceiveSi', width: 120, align: "right", sortable: false},
                {name: 'saleOrderOut', index: 'otherSo', width: 120, align: "right", sortable: false},
                {name: 'saleOrderIn', index: 'otherSi', width: 120, align: "right", sortable: false},
                {name: 'otherSo', index: 'otherSo', width: 120, align: "right", sortable: false},
                {name: 'otherSi', index: 'otherSi', width: 120, align: "right", sortable: false}
            ],
            sortname: 'skuCode',
            sortorder:'asc',
            //pager: "#gridPager",
            footerrow: true,
            rowNum:9999,
            gridComplete: function() {
                var rowNum = parseInt($(this).getGridParam('records'),10);
                if(rowNum > 0){
                    $(".ui-jqgrid-sdiv").show();
                    var purchaseSi  = jQuery(this).getCol('purchaseSi',false,'sum');
                    var purchaseReturnSo  = jQuery(this).getCol('purchaseReturnSo',false,'sum');
                    var saleSo  = jQuery(this).getCol('saleSo',false,'sum');
                    var saleReturnSi  = jQuery(this).getCol('saleReturnSi',false,'sum');
                    var ccProfitSi  = jQuery(this).getCol('ccProfitSi',false,'sum');
                    var ccLossSo  = jQuery(this).getCol('ccLossSo',false,'sum');
                    var transferSo  = jQuery(this).getCol('transferSo',false,'sum');
                    var transferSi  = jQuery(this).getCol('transferSi',false,'sum');
                    var allocationSo  = jQuery(this).getCol('allocationSo',false,'sum');
                    var allocationReceiveSi  = jQuery(this).getCol('allocationReceiveSi',false,'sum');
                    var allocationOnTheWay  = jQuery(this).getCol('allocationOnTheWay',false,'sum');
                    var asnProductSi  = jQuery(this).getCol('asnProductSi',false,'sum');
                    var asnProductBomNetSo  = jQuery(this).getCol('asnProductBomNetSo',false,'sum');
                    var asnProductBomSo  = jQuery(this).getCol('asnProductBomSo',false,'sum');
                    var commercialReturnSo  = jQuery(this).getCol('commercialReturnSo',false,'sum');
                    var scrapSo  = jQuery(this).getCol('scrapSo',false,'sum');
                    var deliverySo  = jQuery(this).getCol('deliverySo',false,'sum');
                    var deliveryOnTheWay  = jQuery(this).getCol('deliveryOnTheWay',false,'sum');
                    var deliveryReceiveSi  = jQuery(this).getCol('deliveryReceiveSi',false,'sum');
                    var saleOrderOut  = jQuery(this).getCol('saleOrderOut',false,'sum');
                    var saleOrderIn  = jQuery(this).getCol('saleOrderIn',false,'sum');
                    var otherSo  = jQuery(this).getCol('otherSo',false,'sum');
                    var otherSi  = jQuery(this).getCol('otherSi',false,'sum');
                    $(this).footerData("set",
                        {price:"合计:",
                            purchaseSi:purchaseSi,purchaseReturnSo:purchaseReturnSo,
                            saleSo:saleSo,saleReturnSi:saleReturnSi,
                            ccProfitSi:ccProfitSi,ccLossSo:ccLossSo,
                            transferSo:transferSo,transferSi:transferSi,
                            allocationSo:allocationSo,allocationReceiveSi:allocationReceiveSi,allocationOnTheWay:allocationOnTheWay,
                            asnProductSi:asnProductSi,asnProductBomNetSo:asnProductBomNetSo,asnProductBomSo:asnProductBomSo,
                            commercialReturnSo:commercialReturnSo,scrapSo:scrapSo,
                            deliverySo:deliverySo,deliveryOnTheWay:deliveryOnTheWay,deliveryReceiveSi:deliveryReceiveSi,
                            saleOrderOut:saleOrderOut,saleOrderIn:saleOrderIn,
                            otherSo:otherSo,otherSi:otherSi
                        }
                    );
                }else{
                    $(".ui-jqgrid-sdiv").hide();
                }
            },
            showOperate: false,
            dataType: 'local',
            jsonReader:{
              id: 'skuId'
            },
            localReader:{
                id: 'skuId'
            }
        });

        jQuery(_this.opts.listGridId).jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                {startColumnName: 'purchaseSi', numberOfColumns: 2, titleText: '采购'},
                {startColumnName: 'saleSo', numberOfColumns: 2, titleText: '销售'},
                {startColumnName: 'ccProfitSi', numberOfColumns: 2, titleText: '盘点'},
                {startColumnName: 'transferSo', numberOfColumns: 2, titleText: '移库'},
                {startColumnName: 'allocationSo', numberOfColumns: 3, titleText: '调拨'},
                {startColumnName: 'asnProductSi', numberOfColumns: 3, titleText: '生产'},
                {startColumnName: 'commercialReturnSo', numberOfColumns: 2, titleText: '退回、报废'},
                {startColumnName: 'deliverySo', numberOfColumns: 3, titleText: '配送'},
                {startColumnName: 'saleOrderOut', numberOfColumns: 2, titleText: '品牌销售'},
                {startColumnName: 'otherSo', numberOfColumns: 2, titleText: '其他出入库'}
            ]
        });
    },


    /**
     * 重置所有查询条件的监听事件
     */
    delegateReset: function(){

        var _this = this;

        //重置表单
        $("#undo-all2").on("click", function (e) {
            e.preventDefault();
            var formObj = $(this).parents('.aside');
            bkeruyun.clearData(formObj);
            $(formObj).find(":checkbox:not([name^='switch-checkbox'],[name^='is'])").each(function () {//[name^='is']是为了修复bugId [14462] 商品状态，全部撤销，启用，停用应为默认勾选
                if($(this).parent().attr('class').indexOf('checkbox-check') >= 0){ // 若已选中，则再点击一次，即不选中
                    $(this).click();
                } else{ // 若未选中，则再点击2次，即先选中，再取消选中，确保<em>中的内容被清空
                    $(this).click();
                    $(this).click();
                }
            });

            if (!bkeruyun.isPlaceholder()) {
                JPlaceHolder.init();
            }
            // _this.initDates();
        });
    },

    /**
     * 初始化日期查询条件
     */
    initDates: function(){

        var currentDate = new Date().Format('yyyy-MM-dd');
        $('#confirmDateStart').val(currentDate);
        $('#confirmDateEnd').val(currentDate);
    }
};


/**
 * 导出
 */
function exportResult(){

    var currentQueryConditions = serializeFormById(skuioreport.opts.queryConditionsId);

    if(currentQueryConditions != skuioreport.opts.cachedQueryConditions){
        $.layerMsg('条件已改变，请先点击查询按钮！', false);
        return false;
    }

    var commercialId = $('select[name=commercialId]').val();

    // if (commercialId == null || commercialId == undefined || commercialId.length < 1) {
    //     $.layerMsg('请选择品牌/商户!', false);
    //     return;
    // }

    var $gridObj = $(skuioreport.opts.listGridId);

    var totalSize = $gridObj.jqGrid('getGridParam','records');

    if(totalSize > 0){

        var sidx = $gridObj.jqGrid('getGridParam','sortname');
        var sord = $gridObj.jqGrid('getGridParam','sortorder');

        //rows=0将获取所有记录，不分页
        var exportUrl = skuioreport.opts.urlRoot + "/export?rows=0&sidx=" + sidx + "&sord=" + sord;

        $('#' + skuioreport.opts.queryConditionsId).attr("action", exportUrl).attr("target", "_blank");
        $('#' + skuioreport.opts.queryConditionsId).submit();
    } else{
        $.layerMsg('导出记录为空！', false);
    }
};

function load() {
    //验证
    var commercialId = $('select[name=commercialId]').val();
    // if (commercialId == null || commercialId == undefined || commercialId.length < 1) {
    //     $.layerMsg('请选择品牌/商户!', false);
    //     return;
    // }
    skuioreport.opts.cachedQueryConditions = serializeFormById(skuioreport.opts.queryConditionsId);
    $(skuioreport.opts.listGridId).refresh(-1);
};


/**
 * 监听下拉选框
 * @param name
 * @param id
 */
function delegateSelect(id){
    //业务类型 条件选择
    $(document).delegate(id, "change", function(){
    //    refreshWms($('select[name=commercialId]').val());
    });
};
