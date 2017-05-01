var aggregateList = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : '',
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '/query/list',
        editUrl : '/edit',
        viewUrl : '/view',
        saveUrl : '/save',
        deleteUrl : '/delete?cmd=true',
        exportUrl : '/export',
        confimUrl: '/doconfirm?cmd=false',
        withdrawUrl: '/withdraw',
        sortName : 'parentSkuTypeCode',
        pager : '#gridPager'
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
                //$.setSearchFocus();
                $('#orderNo').focus();
                break;
        }
    },

    //初始化查询页面
    initQueryList : function() {
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/supply/aggregate/edit',
            'scm_kry/supply/aggregate/view',
            'scm_kry/supply/aggregate/add',
            'scm_kry/supply/aggregate/withdraw'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        //序列化查询条件
        $.serializeGridDataCallback = function (formData) {
        	var orderNo = $("#orderNo").val().trim(),dlIdStr = $("#dlId").val().trim(),
        	    dateStart = $("#dateStart").val().trim(),dateEnd = $("#dateEnd").val().trim(),
        	    skuNameOrCode = $("#skuNameOrCode").val().trim(),editName = $("#editName").val().trim();
        	    
        	delete formData.orderNo;delete formData.dlIdStr;delete formData.dateStart;
        	delete formData.dateEnd;delete formData.skuNameOrCode;delete formData.editName;
        	
        	
        	if(orderNo!="") formData["orderNo"] = orderNo;
        	if(dlIdStr!="") formData["dlIdStr"] = dlIdStr;
        	if(dateStart!="") formData["dateStart"] = dateStart;
        	if(dateEnd!="") formData["dateEnd"] = dateEnd;
        	if(skuNameOrCode!="") formData["skuNameOrCode"] = skuNameOrCode;
        	if(editName!="") formData["editName"] = editName;
        	
        	var statusArray = [],changeArray=[];
        	$("input[name='status']:checked").each(function(){statusArray.push($(this).val());});
        	$("input[name='isChange']:checked").each(function(){changeArray.push($(this).val());});
        	
        	formData["status"] = statusArray.toString();
        	formData["isChange"] = changeArray.toString();
        	if(statusArray.length==0) delete formData.status;
        	if(changeArray.length==0) delete formData.isChange;
            return formData;
        };

        //编辑按钮
        $.showEdit = function (rowData) {
        	var flag = renderEnum.normal;
        	if(rowData.status!=0) flag = renderEnum.hidden;
            return flag;
        };
        //查看按钮
        $.showView = function (rowData) {
        	var flag = renderEnum.normal;
        	if(rowData.status==0) flag = renderEnum.hidden;
            return flag;
        };
        //删除按钮
        $.showDelete = function (rowData) {
        	var flag = renderEnum.normal;
        	if(rowData.status!=0) flag = renderEnum.hidden;
            return flag;
        };
        //导出按钮
        $.showExport = function(rowData){
        	var flag = renderEnum.normal;
        	if(rowData.status==0) flag = renderEnum.disabled;
            return flag;
        };
        //采购
        $.showPurchase = function(rowData) {
        	var flag = renderEnum.normal;
        	if(rowData.isChange==1) flag = renderEnum.disabled;
        	if(rowData.status==0) flag = renderEnum.hidden;
            return flag;
        };
        //反确认
        $.showWithdraw = function(rowData) {
            var flag = renderEnum.normal;
            if(rowData.isChange==1) flag = renderEnum.disabled;
            if(rowData.status==0) flag = renderEnum.hidden;
            return flag;
        };

        var $gridObj = $("#" + _this.opts.listGridId);
        $gridObj.dataGrid({
            rownumbers: true,
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '单据号', '线路名称','到货日期','编辑人', '最后修改时间', '状态', '操作'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'orderNo', index: 'orderNo', align: "center", width: 120},
                {name: 'dlName', index: 'dlName', align: "center", width: 120},
                {name: 'arriveDate', index: 'arriveDate', align: "center", width: 180},
                {name: 'updaterName',index: 'updaterName',align: "center",width: 70},
                {name: 'updateTime', index: 'updateTime', align: "center", width: 180},
                {
                    name: 'status',
                    index: 'status',
                    align: "center",
                    width: 100,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.status==0) {
                            return "已保存";
                        } else if (rowObject.status==1){
                            return "已确认";
                        } else if (rowObject.status==2){
                            return "已出单";
                        }
                    }
                },
                {name: 'status', index: 'status', width: 150, hidden: true}
            ],
            sortname: 'id',
            pager: "#gridPager",
            showOperate: true,
            actionParam: {
            	view: {
            		render : $.showView,
                    url: _this.opts.urlRoot + _this.opts.viewUrl
                },
                editor: {
                	render : $.showEdit,
                    code: "scm:button:delivery:aggregate:edit",
                    url: _this.opts.urlRoot + _this.opts.editUrl
                },
                confirm: {
                	render : $.showEdit,
                    code: "scm:button:delivery:aggregate:confirm",
                    url: _this.opts.urlRoot + _this.opts.confimUrl
                },
                withdraw: {
                    render: $.showWithdraw,
                    code: "scm:button:delivery:aggregate:withdraw",
                    url: _this.opts.urlRoot + _this.opts.withdrawUrl,
                    redirectUrl: _this.opts.urlRoot + _this.opts.editUrl //重定向跳转到编辑页面
                },
                purchase: {
                	render : $.showPurchase,
                    code: "scm:button:delivery:aggregate:confirmApply",
                    url: _this.opts.urlRoot + _this.opts.editUrl
                },
                delete:{
                    render : $.showDelete,
                    code: "scm:button:delivery:aggregate:delete",
                    url: _this.opts.urlRoot + _this.opts.deleteUrl
                },
                export:{
                	render : $.showExport,
                    url: _this.opts.urlRoot + _this.opts.exportUrl, 
                    title: '导出'
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
                $.layerMsg(result.message, false);
                // 刷新
                $("#" + dataGridId).refresh();
            }
        };
        
        //显示弹出层
        $.doPurchase = function(args){
        	var msg = $("#showLayerMsg").html();
        	layer.confirm(msg, {title: '提示', offset: '30%',maxWidth:500}, function (index) {
        		var checkMsg = doConfimApply(args.postData.id,true);
        		if(checkMsg){
        			layer.confirm("配送申请单号"+checkMsg.msg+"已被拒绝，生成采购申请单将会自动过滤掉！", {icon: 3, title: '提示', offset: '30%'}, 
           				function (index) {
           					doConfimApply(args.postData.id,false,checkMsg.type);
                     		layer.close(index);
                        }
           			);
        		}
            });
        }
        
        //生成采购申请单
        function doConfimApply(id,check,tp){
    	  var checkMsg = undefined,type = $('input[name="type"]:checked').val(),
          jumpUrl = "$.doForward({url:'/scm_kry/purchase/apply/edit',postData:{id:'";
    	  type = type?type:tp;
    	  
          $.ajax({
              url: ctxPath + "/supply/aggregate/confirmApply",
              type: 'post',
              async: false,
              data: {id:id,type:type,check:check},
              success: function(rest){
                  	if(rest.status==200){
                  		jumpUrl+=rest.orderId+"'}})";
                  		var newMsg = '采购申请单创建成功！单据号为：'+rest.orderNo+'，<a id="newJump" class="btn-link" onclick="'+jumpUrl+'">点击查看</a>';
                  		layer.confirm(newMsg, {icon: 1,btn: [],title: '提示', offset: '30%',maxWidth:500});
                  		$("#doApply").remove();
                  		$("#grid").refresh();
                  	}else if(rest.status==305){
                  		checkMsg = rest;
                		checkMsg["type"] = type;
                  	}else{
                  		$.layerMsg(rest.message, false);
                  	}
              },
              error: function () {
                  $.layerMsg("网络错误", false);
              },
              complete: function(XMLHttpRequest, textStatus){
                  bkeruyun.hideLoading();
              }
           });
          return checkMsg;
        }
        
        //回车支持
        $(document).on("keypress",function(event){
        	if(event.keyCode == "13"){
                $("#btnQuery").trigger("click");
             }
        });
    }
};