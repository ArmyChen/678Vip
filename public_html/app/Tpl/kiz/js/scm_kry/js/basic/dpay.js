//Create by LiXing On 2015/8/11
var type = "&type=" + $("#type").val();
var dPayList = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctx2Path,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=dish_pay_ajax'+type,
        editUrl : '&act=dish_pay_edit'+type,
        viewUrl : '/view',
        lockUrl : '&act=dish_pay_lock'+type,
        saveUrl : '/save',
        unlockUrl : '&act=dish_pay_lock'+type,
        deleteUrl : '&act=dish_pay_checkUsed'+type,
        sortName : 'parentSkuTypeCode'+type,
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
        var page_title = $("#page_title").val();
        $gridObj.dataGrid({
            rownumbers: true,
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id','编号', page_title , '状态'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'dpid', index: 'dpid', width: 50, hidden: true},
                {name: 'ptname', index: 'ptname', align: "center", width: 120},
                // {name: 'isDisable', index: 'isDisable', width: 150, hidden: true},
                {
                    name: 'isDisable',
                    index: 'isDisable',
                    align: "center",
                    hidden:true,
                    width: 100,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.is_effect == 0) {
                            return "<span style='color:red'>停用 </span>";
                        } else {
                            return "启用";
                        }
                    }
                }
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