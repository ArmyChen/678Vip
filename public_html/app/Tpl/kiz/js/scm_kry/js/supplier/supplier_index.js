//Create by LiXing On 2015/8/11
var supplierList = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctxPath,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=supplier_ajax',
        editUrl : '&act=supplier_edit',
        viewUrl : '&act=supplier_view',
        lockUrl : '&act=supplier_lock',
        saveUrl : '&act=supplier_add',
        unlockUrl : '&act=supplier_lock',
        deleteUrl : '&act=supplier_del',
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
                $('#supplierName').focus();
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
            'scm_kry/scm/supplier/edit',
            'scm_kry/scm/supplier/view',
            'scm_kry/scm/supplier/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        $.serializeGridDataCallback = function (formData) {
        	var supplierName = $("#supplierName").val().trim(),isDisable = 0,supplierCateId = $("#supplierCateId").val(),
        	    checkbox0 = $("#checkbox-0").hasClass("checkbox-check"),checkbox1 = $("#checkbox-1").hasClass("checkbox-check");
        	
        	delete formData.supplierName;delete formData.supplierCateId;
        	if(supplierName!="") formData["supplierName"] = supplierName;
        	if(supplierCateId!="") formData["supplierCateId"] = supplierCateId;
        	if(checkbox1) isDisable = 1;
        	formData["isDisable"] = isDisable;
        	if(checkbox0&&checkbox1) delete formData.isDisable;
        	if(!checkbox0&&!checkbox1) delete formData.isDisable;
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
            colNames: ['id', '供应商类别', '供应商编码','供应商名称','税率', '编辑人','最后编辑时间', '状态', '操作'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'supplierCateName', index: 'supplierCateName', align: "left", width: 120},
                {name: 'supplierCode', index: 'supplierCode', align: "left", width: 120},
                {name: 'supplierName', index: 'supplierName', align: "left", width: 180},
                {
                    name: 'taxRate',
                    index: 'taxRate',
                    align: "center",
                    width: 70,
                    formatter: function (cellvalue, options, rowObject) {
                    	return rowObject.taxRate+"%";
                    }
                },
                {name: 'updaterName', index: 'updaterName', align: "center", width: 100},
                {name: 'updateTime', index: 'updateTime', align: "center", width: 180},
                {
                    name: 'isDisable',
                    index: 'isDisable',
                    align: "center",
                    width: 100,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable==0) {
                            return "<span style='color:red'>停用 </span>";
                        } else {
                            return "启用";
                        }
                    }
                },
                {name: 'isDisable', index: 'isDisable', width: 150, hidden: true}
            ],
            sortname: 'id',
            pager: "#gridPager",
            showOperate: true,
            actionParam: {
            	view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl,
                    render : $.showView,
                },
                editor: {
                	render : $.showEdit,
                    code: "scm:button:purchase:supplier:edit",
                    url: _this.opts.urlRoot + _this.opts.editUrl,
                },
                clock: {
                	render : $.showlock,
                    code: "scm:button:purchase:supplier:disableSupplier",
                    url: _this.opts.urlRoot + _this.opts.lockUrl
                    
                },
                unlock: {
                	render :  $.showUnlock,
                    code: "scm:button:purchase:supplier:enableSupplier",
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