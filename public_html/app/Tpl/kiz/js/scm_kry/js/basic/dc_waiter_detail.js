//Create by LiXing On 2015/8/11
function getParam(name) {
    return location.href.match(new RegExp('[?#&]' + name + '=([^?#&]+)', 'i')) ? RegExp.$1 : '';

}

var dc_waiter_detail = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctx2Path,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=dish_dc_waiter_detail_ajax&sno='+ getParam("sno"),
        editUrl : '&act=dish_dc_waiter_detail_edit',
        viewUrl : '/view',
        lockUrl : '&act=dish_dc_waiter_detail_lock',
        saveUrl : '/save',
        unlockUrl : '&act=dish_dc_waiter_detail_lock',
        deleteUrl : '&act=dish_dc_waiter_detail_checkUsed',
        sortName : 'parentSkuTypeCode',
        pager : '#gridPager',
        formId : 'baseInfoForm',
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
                $('#wareHouseName').focus();
                break;

            case 1 ://新增
                _this.initSaveBtn();
                _this.changeType(true);
                _this.checkCodeAndName();
                break;

            case 2 ://编辑
                _this.initSaveBtn();
                _this.changeType(false);
                _this.checkCodeAndName();
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
            'scm_kry/scm/warehouse/edit',
            'scm_kry/scm/warehouse/view',
            'scm_kry/scm/warehouse/add',
            'scm_kry/scm/warehouse/deduction'
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
            return renderEnum.normal;
        };

        //先假设可用判断置灰，再判断是否可用
        $.showlock = function (rowData) {
            return renderEnum.hidden;
        };

        $.showUnlock = function (rowData) {
            return renderEnum.hidden;
        };

        $.showDelete = function (rowData) {
            return renderEnum.normal;
        };

        var $gridObj = $("#" + _this.opts.listGridId);
        $gridObj.dataGrid({
            rownumbers: true,
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['序号','订单号', '订单总价' , '菜品名称','当前商品价格','实收金额','数量','营销佣金','支付方式'],
            colModel: [
                {name: 'id', index: 'id',align: "center", width: 50},
                {name: 'onum', index: 'onum',align: "center", width: 180},
                {name: 'money_ys', index: 'money_ys', align: "center", width: 250},
                {name: 'name', index: 'name', align: "center", width: 120},
                {name: 'pprice', index: 'pprice', align: "center",width: 120},
                {name: 'pmoney', index: 'pmoney', align: "center", width: 120},
                {name: 'pnum', index: 'pnum', align: "center",width: 120},
                {name: 'tichengmoney', index: 'tichengmoney', align: "center", width: 120},
                {name: 'zffs', index: 'zffs', align: "center",width: 120},
            ],
            sortname: 'dpid',
            sortorder:'desc',
            pager: "#gridPager",
            showOperate: true,
            actionParam: {
            	view: {
                    render : $.showView,
                    url: _this.opts.urlRoot + _this.opts.viewUrl
                },
                editor: {
                	render : $.showEdit,
                    code: "scm:button:masterdata:warehouse:edit",
                    url: dishPath + _this.opts.editUrl,
                },
                clock: {
                	render : $.showlock,
                    url: _this.opts.urlRoot + _this.opts.lockUrl,
                    code: "scm:button:masterdata:warehouse:disableWarehouse",
                    disabledTitle :"扣减仓库不能停用"

                },
                unlock: {
                	render :  $.showUnlock,
                    code: "scm:button:masterdata:warehouse:enableWarehouse",
                    url: _this.opts.urlRoot + _this.opts.unlockUrl
                },
                delete: {
                    url: _this.opts.urlRoot + _this.opts.deleteUrl,
                    code: "scm:button:masterdata:warehouse:deleteWareHouse",
                    render: $.showDelete,
                    disabledTitle :"指定扣减仓库或存在库存信息，无法删除"
                }
            }
        });
        /**
         * 执行成功提示并刷新表格，执行失败只提示
         * @param args
         */
        $.showMsgAndRefresh = function (args) {
            var result = args.result, dataGridId = args.dataGridId;
            var time = (result.message.length / 3) * 1000;
            if (result.success) {
                //$.showMsgBar('success', result.message);
                $.layerMsg(result.message, true);
                $("#" + dataGridId).refresh();
            } else {
                if (result.wareHouseBillReferenceVos != '' && result.wareHouseBillReferenceVos != undefined) {
                    var opt = {
                        confirm : false,
                        hint : result.message, //提示信息
                        dataHint : '引用信息列表', //详情提示信息
                        dataList : eval(result.wareHouseBillReferenceVos) //详情数据
                    };
                    $.message.showDialog(opt);
                } else {
                    //$.showMsgBar('error', result.message);
                    $.layerMsg(result.message, false);
                    // 刷新
                    $("#" + dataGridId).refresh();
                }
            }
        };
    }

};