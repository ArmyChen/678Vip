//Create by LiXing On 2015/8/11
var saleDetailReport = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctxPath,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=report_sale_detail_ajax',
        editUrl : '&act=bumen_edit',
        viewUrl : '&act=bumen_view',
        lockUrl : '&act=bumen_lock',
        saveUrl : '&act=bumen_add',
        unlockUrl : '&act=bumen_lock',
        deleteUrl : '&act=bumen_del_ajax',
        sortName : 'parentSkuTypeCode',
        pager : '#gridPager',
        formId : 'baseInfoForm',
        type : 0
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
                $('#skuNameOrCode').focus();
                break;

            case 1 ://新增
                _this.initSaveBtn();
                _this.changeType(true);
                // _this.checkCodeAndName();
                break;

            case 2 ://编辑
                _this.initSaveBtn();
                _this.changeType(false);
                // _this.checkCodeAndName();
                break;

            default ://查看
                $("#parentId").siblings(".select-control").addClass("disabled");
                break;
        }
    },

    //初始化查询页面
    initQueryList : function() {
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/scm/bumen/edit',
            'scm_kry/scm/bumen/view',
            'scm_kry/scm/bumen/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        $.serializeGridDataCallback = function (formData) {
            return formData;
        };
        $.showView = function (rowData) {
            return renderEnum.hidden;

        };

        $.showEdit = function (rowData) {
            return renderEnum.hidden;

        };

        //先假设可用判断置灰，再判断是否可用
        $.showlock = function (rowData) {
            return renderEnum.hidden;

        };

        $.showUnlock = function (rowData) {
            return renderEnum.hidden;

        };

        $.showDelete = function (rowData) {
            return renderEnum.hidden;

        };

        var $gridObj = $("#" + _this.opts.listGridId);
        $gridObj.dataGrid({
            rownumbers: true,
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            // colNames: ['单据号','创建时间','销售门店', '库存类型', '商品分类', '商品编码', '商品名称（规格）', '单位', '价格', '销售数量', '出库数量','收货数量','销售金额'],
            // colModel: [
            //     {name: 'orderNo', index: 'orderNo', /*width: 130,*/align: 'center',sortable: false},
            //     {name: 'updateTime', index: 'updateTime', align: 'center',sortable: false},
            //     {name: 'commercialName', index: 'commercialName', /*width: 100,*/ align: 'center',sortable: false},
            //     {name: 'wmTypeStr', index: 'wmTypeStr', /*width: 100,*/ align: 'center',sortable: false},
            //     {name: 'skuTypeName', index: 'skuTypeName',/* width: 110,*/ align: 'center',sortable: false,
            //     	formatter:function(cellvalue, options, rowObject) {
            //             if (rowObject.skuTypeIsDisable == 1) {
            //                 return cellvalue + '<span style="color:red;">(已停用)</span>';
            //             }
            //             return cellvalue;
            //         }
            //     },
            //     {name: 'skuCode', index: 'skuCode', /*width: 120,*/ align: 'center',sortable: false},
            //     {name: 'skuName', index: 'skuName', /*width: 150,*/sortable: false,
            //     	formatter:function(cellvalue, options, rowObject){
            //     		if (rowObject.skuIsDelete == 1) {
            //                 return cellvalue + "<span style='color:red'>(已删除)</span>";
            //             } else if (rowObject.skuIsDisable == 1) {
            //                 return cellvalue + "<span style='color:red'>(已停用)</span>";
            //             } else {
            //                 return cellvalue;
            //             }
            //     	}
            //     },
            //     {name: 'uom', index: 'uom', /*width: 90,*/ align: "center",sortable: false},
            //     {name: 'price', index: 'price',/*width: 140,*/ align: "right", formatter:_this.amountFormatter,sortable: false},
            //     {name: 'saleQty', index: 'saleQty',/*width: 100,*/ align: "right", formatter:_this.qtyFormatter,sortable: false},
            //     {name: 'outBoundQty', index: 'outBoundQty',/*width: 140,*/ align: "right", formatter:_this.qtyFormatter,sortable: false},
            //     {name: 'receiveQty', index: 'receiveQty',/*width: 140,*/ align: "right", formatter:_this.qtyFormatter,sortable: false},
            //     {name: 'amount', index: 'amount',/*width: 165,*/ align: "right",formatter:_this.amountFormatter,sortable: false},
            // ],
            colNames: ['商品分类id','商品分类', '商品编码', '商品名称（规格）', '商品单价', '销售数量', '商品总价','实收金额'],
            colModel: [
                {name: 'cate_id', index: 'cate_id', /*width: 130,*/align: 'center',sortable: false, hidden:true},
                {name: 'cname', index: 'cname', /*width: 130,*/align: 'center',sortable: false},
                {name: 'id', index: 'id', align: 'center',sortable: false},
                {name: 'name', index: 'name', /*width: 100,*/ align: 'center',sortable: false},
                {name: 'price', index: 'price', /*width: 100,*/ align: 'center',sortable: false,formatter:_this.amountFormatter},
                {name: 'pnum', index: 'pnum',/* width: 110,*/ align: 'center',sortable: false},
                {name: 'goodszong', index: 'goodszong', /*width: 120,*/ align: 'center',sortable: false,formatter:_this.amountFormatter},
                {name: 'profit', index: 'profit', /*width: 150,*/sortable: false,formatter:_this.amountFormatter}
            ],
            sortname: 'id',
            pager: "#gridPager",
            showOperate: false,
            actionParam: {
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl,
                    render : $.showView,
                },
                editor: {
                    render : $.showEdit,
                    code: "scm:button:purchase:supplier:edit",
                    url: basicPath + _this.opts.editUrl,
                },
                clock: {
                    render : $.showlock,
                    code: "scm:button:purchase:supplier:disablebumen",
                    url: _this.opts.urlRoot + _this.opts.lockUrl

                },
                unlock: {
                    render :  $.showUnlock,
                    code: "scm:button:purchase:supplier:enablebumen",
                    url: _this.opts.urlRoot + _this.opts.unlockUrl
                },
                delete:{
                    render : $.showDelete,
                    code: "scm:button:purchase:supplier:delete",
                    url: _this.opts.urlRoot + _this.opts.deleteUrl
                }
            }
        });

        /**
         * 执行成功提示并刷新表格，执行失败也刷新
         * @param args
         */
        $.showMsgAndRefresh = function (args) {
            var result = args.result, dataGridId = args.dataGridId;
            var time = (result.message.length / 3) * 1000;
            if (result.success) {
                $.layerMsg(result.message, true);
                $("#" + dataGridId).refresh();
            } else {
                if (result.billReferenceVOs != '' && result.billReferenceVOs != undefined) {
                    var opt = {
                        confirm : false,
                        hint : result.message, //提示信息
                        dataHint : '引用信息列表', //详情提示信息
                        dataList : eval(result.billReferenceVOs) //详情数据
                    };
                    $.message.showDialog(opt);
                } else {
                    $.layerMsg(result.message, false);
                    // 刷新
                    $("#" + dataGridId).refresh();
                }
            }
        };


    }

};
function load() {
    var _gridTable = '#grid';
    cachedQueryConditions = serializeFormById('queryConditions');
    $(_gridTable).refresh(-1);
}