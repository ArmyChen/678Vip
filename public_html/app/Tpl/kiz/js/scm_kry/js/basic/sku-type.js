/**
 * Created by mayi on 2015/7/28.
 */

var skuType = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : '/scm/skutype',
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '/query',
        editUrl : '/edit',
        viewUrl : '/view',
        lockUrl : '/lock',
        saveUrl : '/save',
        deleteUrl:'/delete',
        unlockUrl : '/unlock',
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
                break;

            case 1 ://新增
                _this.initSaveBtn();
                _this.changeType(true);
                _this.checkCodeAndName();
                $('#typeName').focus();
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
            'scm_kry/scm/skutype/edit',
            'scm_kry/scm/skutype/view',
            'scm_kry/scm/skutype/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        $.serializeGridDataCallback = function (formData) {
            if (typeof formData.isDisable == "object" || formData.isDisable == undefined) {
                formData["isDisable"] = -1;
            }
            return formData;
        };

        $.showEditor = function (rowData) {
            if (!rowData.dishTypeId && rowData.isDisable == 0) {
                return renderEnum.normal;
            }
            return renderEnum.disabled;
        };

        $.showLock = function (rowData) {
            if (!rowData.dishTypeId && rowData.isDisable == 0) {
                return renderEnum.normal;
            } else if (!!rowData.dishTypeId && rowData.isDisable == 0) {
                return renderEnum.disabled;
            } else {
                return renderEnum.hidden;
            }
        };

        $.showUnlock = function (rowData) {
            if (!rowData.dishTypeId && rowData.isDisable == 1) {
                return renderEnum.normal;
            } else if (!!rowData.dishTypeId && rowData.isDisable == 1) {
                return renderEnum.disabled;
            } else {
                return renderEnum.hidden;
            }
        };
        $.showDelete = function(rowData){
        	if (!rowData.dishTypeId) {
                return renderEnum.normal;
            } else {
                return renderEnum.disabled;
            } 
        }
        
        //处理删除或停用任务
	    $.doDeleteOrLockTask = function(data){
	       $.ajax({
               url: data.url,
               type: "post",
               async: false,
               data: data.isDelete?{dishTypeIds:data.postData.id}:{id:data.postData.id},
               success: function (data) {
            	  if(data.success==true){
            		  $("#btn-query").trigger("click");
            		  $.layerMsg(data.message, true);
            	  }else{
            		  $.layerMsg(data.message, false);
            	  }
               },
               error: function () {
                   $.layerMsg("网络错误", false);
               }
           });
	    }
	   
	   //处理停用事件
	   $.doClock = function(data){$.doDelete(data);}
	    
	   //处理删除事件
       $.doDelete = function(dataArgs){
    	   var typeCode = $(dataArgs.domId).parent().parent().find('td[aria-describedby="grid_typeCode"]').attr("title");
    	   var isDelete = dataArgs.domId.indexOf("#delete")!=-1;
    	   dataArgs.isDelete=isDelete;
    	   var opts = {
    		   callBack: $.doDeleteOrLockTask,
    		   callBackArgs:dataArgs,
			   hint:isDelete?((typeCode==""?'商品大类':'商品中类')+'删除后不可恢复,确认删除？'):'确定停用？'
    	    };
    	   $.message.showDialog(opts);
    	   $("#typeCodeOrName").focus().blur();
        }
       
        var $gridObj = $("#" + _this.opts.listGridId);
        $gridObj.dataGrid({
            rownumbers: true,
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '大类编码', '上级分类名称', '中类编码', '分类名称', '最后编辑时间', '状态', '状态', 'mind类型ID'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'parentTypeCode', index: 'parentTypeCode', align: "left", width: 120,hidden:true},
                {name: 'parentTypeName', index: 'parentTypeName', align: "left", width: 170},
                {name: 'typeCode', index: 'typeCode', align: "left", width: 120,hidden:true},
                {name: 'typeName', index: 'typeName', align: "left", width: 170},
                {name: 'updateTime', index: 'updateTime', align: "center", width: 160},
                {
                    name: 'isDisableName',
                    index: 'isDisable',
                    align: "center",
                    width: 60,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 1) {
                            return "<span style='color:red'>" + cellvalue + "</span>";
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'isDisable', index: 'isDisable', width: 150, hidden: true},
                {name: 'dishTypeId', index: 'dishTypeId', width: 150, hidden: true}
            ],
            sortname: 'id',
            pager: "#gridPager",
            showOperate: false,
            actionParam: {
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl
                },
                editor: {
                    render: $.showEditor,
                    code: "scm:button:masterdata:skuType:edit",
                    url: _this.opts.urlRoot + _this.opts.editUrl
                },
                clock: {
                    url: _this.opts.urlRoot + _this.opts.lockUrl,
                    code: "scm:button:masterdata:skuType:lock",
                    render: $.showLock
                },
                unlock: {
                    url: _this.opts.urlRoot + _this.opts.unlockUrl,
                    code: "scm:button:masterdata:skuType:unlock",
                    render: $.showUnlock
                },
                delete:{
                	url: _this.opts.urlRoot + _this.opts.deleteUrl,
                    code: "scm:button:masterdata:skuType:delete",
                    render: $.showDelete,
                    beforeCallback:'$.doDelete'
                }
            }
        });
    },

    initSaveBtn : function() {
        var _this = this;

        $("#btnSave,#btnSave-bak").click(function() {
        	var isBtnBak = $(this).attr("id")=="btnSave-bak";
        	
            //执行表单检查
            var $form = $("#baseInfoForm");
            var validator = $form.validate({
                debug: true, //不提交表单
                //messages: args.messages,
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents(".positionRelative").find(".wrong"));
                }
            });

            if (!validator.form()) {
                return false;
            }

            var type = {};
            type.id = $("#id").val();
            type.typeCode = $("#typeCode").val();
            type.typeName = $("#typeName").val();
            type.aliasName = $("#aliasName").val();
            if (_this.opts.type == 1) {
                type.parentId = $("#parentId").val();
            }
            type.comment = $("#comment").val();
            type.version = $("#version").val();

            bkeruyun.showLoading();
            $.ajax({
                url: _this.opts.urlRoot + _this.opts.saveUrl + "?r=" + new Date().getTime(),
                type: "post",
                async: false,
                data: JSON.stringify(type),
                dataType: "json",
                contentType: "application/json",
                success: function (result) {
                	bkeruyun.hideLoading();
                    if (result.success) {
                    	var showMsg = $("#typeCode").val().length==0;
                        if(isBtnBak){
                        	if(showMsg){
                        		$.layerMsg("操作成功，"+result.data.typeName+"编码是：<span style='color:red;'>"+result.data.typeCode+"</span>", result.success,{shade: 0.3});
                        	}else{
                        		$.layerMsg("操作成功！", result.success,{shade: 0.3});
                        	}
                        	skuType.doResetType();
                        	return false;
                        }else{
                        	skuType.changeType(false);
                        	$("#id").val(result.data.id);
                        	if(showMsg){
	                        	$.layerMsg("操作成功，"+result.data.typeName+"编码是：<span style='color:red;'>"+result.data.typeCode+"</span>", true,{
	                        		end:function(){
	                        			window.location.href = _this.opts.urlRoot + "/index";
	                        			},shade: 0.3});
                        	}else{
                        		$.layerMsg("操作成功！", true,{
	                        		end:function(){
	                        			window.location.href = _this.opts.urlRoot + "/index";
	                        			},shade: 0.3});
                        	}
                        }
                        return false;
                    }
                    $.layerMsg(result.message, result.success);
                },
                error: function () {
                    bkeruyun.hideLoading();
                    $.layerMsg("网络错误", false);
                }
            });
        });
    },
    
    //重置大类列表
    doResetType:function(){
		  $.ajax({
	          url: skuType.opts.urlRoot+'/real/type',
	          type: "post",
	          async: false,
	          data: {},
	          success: function (res) {
	             if(res.length>0){
	            	 var taget = $("#parentId"),isChange = true,
	            	 oldVal = taget.parent().find("em").html(),
	            	 realList='<li>请选择所属大类</li>',
	            	 realOpt='';
	            	 
	            	 for(var i=0;i<res.length;i++){
	            		 if(isChange&&res[i].typeName==oldVal) isChange = false;
	            		 realList+='<li>'+res[i].typeName+'</li>';
	            		 realOpt+='<option '+(res[i].typeName==oldVal?"selected":"")+' value="'+res[i].id+'">'+res[i].typeName+'</option>';
	            	 }
	            	 
	            	taget.html("").append('<option '+(isChange?"selected":"")+' value="">请选择所属大类</option>'+realOpt);
	             	taget.parent().find("ul").html("").append(realList);
	             	if(isChange) taget.parent().find("em").html("请选择所属大类");
	             }
	          },
	          error: function () {
	             //DO ...
	          }
	      });
    },

    //大类、中类选择切换
    changeType : function(flag) {
        var _this = this;

        if(flag) {//新增
            _this.parentIdSetting();
            $("#type :radio").click(function() {
                _this.opts.type = $(this).val();
                _this.parentIdSetting();
            });
        } else {//修改
            $("#type input").prop("disabled", true);
            _this.parentIdSetting();
        }
    },

    parentIdSetting : function () {
        var _this = this;
        var $parentId = $("#parentId");
        if(_this.opts.type == 0) {
            $parentId.removeClass("required");
            $parentId.siblings("ul").children().first().click();
            $parentId.val("");
            $parentId.siblings(".select-control").addClass("disabled");
        } else {
            $parentId.siblings(".select-control").removeClass("disabled");
            $parentId.addClass("required");
        }
    },

    //自定义输入框验证
    checkCodeAndName : function() {
        var _this = this;
        //检查类别代码是否重复
        jQuery.validator.addMethod('checkTypeCode', function (value, element) {
            if (value == null || value.length < 1) {
                return true;
            }
            var flag = true;
            $.ajax({
                type: 'post',
                url: _this.opts.urlRoot + '/checkTypeCode',
                data: {
                    id: parseInt($("#id").val()) || -1,
                    typeCode: value
                },
                async: false,
                dataType: 'json',
                success: function (result) {
                    if (result == null || result.success) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                },
                error: function (data) {
                    //bkeruyun.promptMessage('网络错误');
                    $.layerMsg("网络错误", false);
                }
            });

            if (!flag) {
                //bkeruyun.promptMessage('类别编码填写重复，请检核！');
                //$.layerMsg("类别编码填写重复，请检核！", false);
                return false;
            }
            return true;
        }, '此栏位重复');

        //检查类别名称是否重复
        jQuery.validator.addMethod('checkTypeName', function (value, element) {
            if (value == null || value.length < 1) {
                return false;
            }

            var flag = true;
            $.ajax({
                type: 'post',
                url: _this.opts.urlRoot + '/checkTypeName',
                data: {
                    id: parseInt($("#id").val()) || -1,
                    typeName: value
                },
                async: false,
                dataType: 'json',
                success: function (result) {
                    if (result == null || result.success) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                },
                error: function (data) {
                    //bkeruyun.promptMessage('网络错误');
                    $.layerMsg("网络错误", false);
                }
            });

            if (!flag) {
                //bkeruyun.promptMessage('类别名称填写重复，请检核！');
                //$.layerMsg("类别名称填写重复，请检核！", false);
                return false;
            }
            return true;
        }, '此栏位重复');

        //检查类别别名是否重复
        jQuery.validator.addMethod('checkAliasName', function (value, element) {
            if (value == null || value.length < 1) {
                return true;
            }

            var flag = true;
            $.ajax({
                type: 'post',
                url: _this.opts.urlRoot + '/checkAliasName',
                data: {
                    id: parseInt($("#id").val()) || -1,
                    aliasName: value
                },
                async: false,
                dataType: 'json',
                success: function (result) {
                    if (result == null || result.success) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                },
                error: function (data) {
                    //bkeruyun.promptMessage('网络错误');
                    $.layerMsg("网络错误", false);
                }
            });

            if (!flag) {
                //bkeruyun.promptMessage('类别别名填写重复，请检核！');
                //$.layerMsg("类别别名填写重复，请检核！", false);
                return false;
            }
            return true;
        }, '此栏位重复');

        if (_this.opts.commandType == 2) {
            $('#' + _this.opts.formId).validate({
                rules: {
                    typeName: {
                        checkTypeName: true
                    },
                    aliasName : {
                        checkAliasName : true
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents('.positionRelative').find('.wrong'));
                },
                debug: true
            });
        } else {
            $('#' + _this.opts.formId).validate({
                rules: {
                    typeCode: {
                        checkTypeCode: true
                    },
                    typeName: {
                        checkTypeName: true
                    },
                    aliasName : {
                        checkAliasName : true
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents('.positionRelative').find('.wrong'));
                },
                debug: true
            });
        }
    }
};