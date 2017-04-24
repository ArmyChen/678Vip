var supplierCate = {
    $gridObj: '',
    //default opts
    opts: {
        urlRoot: ctxPath + '/scm/supplierCate',
        queryUrl: '/query',
        addUrl: '/add',
        editUrl: '/edit',
        viewUrl: '/view',
        enableUrl: '/enableSupplierCate',
        disableUrl: '/disableSupplierCate',
        deleteUrl: '/delete',
        //查询条件表单id
        queryFormId: 'queryConditions',
        //列表id
        listGridId: 'grid',
        //列表分页id
        pager: '#gridPager',
        //列表默认排序字段
        sortName: 'supplierCateCode',
        //列表默认排序方式
        sortOrder: 'asc',
        //编辑界面表单id
        editFormId: 'editForm',
        //表格数据
        gridData: [],
        //表格编辑标识
        editable: true
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        $gridObj = $('#' + _this.opts.listGridId);
         _this.initQueryList();

        $.setSearchFocus();
        $('#supplierCateName').focus();
    },

    //初始化查询界面
    initQueryList: function () {
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/scm/supplierCate/edit',
            'scm_kry/scm/supplierCate/view',
            'scm_kry/scm/supplierCate/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;
        
      //查询列表序列化回调
        $.serializeGridDataCallback = function (formData) {
            if (typeof formData.isDisable == "object" || formData.isDisable == undefined) {
                formData["isDisable"] = "";
            }
            return formData;
        };

        $.showEditor = function (rowData) {
        	var flag = renderEnum.normal;
         	if(rowData.isDisable) flag = renderEnum.disabled;
         	return flag;
        };

        $.showView = function (rowData) {
           return renderEnum.normal;
        };

        $.showEnable = function (rowData) {
            if (rowData.isDefault) {
                return renderEnum.disabled;
            }
            if (rowData.isDisable) {
                return renderEnum.hidden;
            }
            return renderEnum.normal;
        };

        $.showDisable = function (rowData) {
            if (rowData.isDisable) {
                return renderEnum.normal;
            }
            return renderEnum.hidden;
        };

        $.showDelete = function (rowData) {
            if (rowData.isDefault) {
                return renderEnum.disabled;
            }
            if (rowData.isDelete == 0) {
                return renderEnum.normal;
            }
            return renderEnum.hidden;
        };

        $gridObj.dataGrid({
            formId: _this.opts.queryFormId,
            serializeGridDataCallback: $.serializeGridDataCallback,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '类别编码', '类别名称', '创建时间', '最后修改时间', '状态', '状态'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'supplierCateCode', index: 'supplierCateCode', width: 160, align: 'center'},
                {name: 'supplierCateName', index: 'supplierCateName', width: 160, align: 'center'},
                {name: 'createTime', index: 'createTime', width: 160, align: 'center'},
                {name: 'updateTime', index: 'updateTime', width: 160, align: 'center'},
                {
                 	name: 'isDisable',
                 	index: 'isDisable',
                 	align: "center",
                 	width: 100,
                 	formatter: function (cellvalue, options, rowObject) {
	                 	if (rowObject.isDisable==1) {
		                 		return "<span style='color:red'>停用 </span>";
		                 	} else {
		                 	 return "启用";
		                 	}
                 	}
                 													},
                {name: 'isDisable', index: 'isDisable', width: 100, hidden: true}
            ],
            sortname: _this.opts.sortName,
            sortorder: _this.opts.sortOrder,
            pager: _this.opts.pager,
            showOperate: true,
            actionParam: {
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl,
                    render: $.showView
                },
                editor: {
                    url: _this.opts.urlRoot + _this.opts.editUrl,
                    code: "scm:button:purchase:supplierType:edit",
                    render: $.showEditor
                },
                clock: {
                    url: _this.opts.urlRoot + _this.opts.disableUrl,
                    code: "scm:button:purchase:supplierType:disableSupplierCate",
                    render: $.showEnable
                },
                unlock: {
                    url: _this.opts.urlRoot + _this.opts.enableUrl,
                    code: "scm:button:purchase:supplierType:enableSupplierCate",
                    render: $.showDisable
                },
                delete:{
                    render : $.showDelete,
                    code: "scm:button:purchase:supplierType:delete",
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
                if (result.suppliers != '' && result.suppliers != undefined) {
                    var opt = {
                        confirm : false,
                        hint : result.message, //提示信息
                        dataHint : '引用信息列表', //详情提示信息
                        dataList : eval(result.suppliers) //详情数据
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