//Create by LiXing On 2015/8/11
var wareHouseList = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctxPath,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '"&act=dc_cangku_ajax"',
        editUrl : '&act=basic_setting_edit',
        viewUrl : '/view',
        lockUrl : '/disableWarehouse',
        saveUrl : '/save',
        unlockUrl : '/enableWarehouse',
        deleteUrl : '&act=ajax_setting_del',
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
        	var wareHouseName = $("#wareHouseName").val().trim(),isDisable = 1,
        	    checkbox0 = $("#checkbox-0").hasClass("checkbox-check"),checkbox1 = $("#checkbox-1").hasClass("checkbox-check");
        	
        	delete formData.wareshouseCode;delete formData.warehouseName;
        	if(wareHouseName!="") formData["warehouseName"] = wareHouseName;
        	if(checkbox1) isDisable = 0;
        	formData["isDisable"] = isDisable;
        	if(checkbox0&&checkbox1) delete formData.isDisable;
        	if(!checkbox0&&!checkbox1) delete formData.isDisable;
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
            //formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id',  '仓库名称', '创建时间', '最后修改时间','扣减设定', '状态', '操作'],
            colModel: [
                {name: 'id', index: 'ids', width: 50, hidden: true},
                {name: 'warehouseName', index: 'warehouseName', align: "left", width: 120},
                {name: 'createTime', index: 'createTime', align: "center", width: 180, hidden: true},
                {name: 'updateTime', index: 'updateTime', align: "center", width: 180, hidden: true},
                {name: 'deductionName', index: 'deductionname', align: "center", width: 120, hidden: true},
                {
                    name: 'isDisable',
                    index: 'isDisable',
                    align: "center",
                    width: 100,
                    formatter: function (cellvalue, options, rowObject) {
                        if (!rowObject.isDisable) {
                            return "<span style='color:red'>停用 </span>";
                        } else {
                            return "启用";
                        }
                    }
                },
                {name: 'isDisable', index: 'isDisable', width: 150, hidden: true}
            ],
            sortname: 'wareshouseCode',
            sortorder:'asc',
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
                    url: basicPath + _this.opts.editUrl,
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