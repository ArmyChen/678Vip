/**
 * 商品查询
 */
var skulist = {

    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : '',
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '/query',
        editUrl : '/edit',
        viewUrl : '/view',
        lockUrl : '/disable?isEnable=false',
        saveUrl : '/save',
        unlockUrl : '/enable?isEnable=true',
        deleteUrl:'/delete',
        referencesUrl : '/references/get',
        sortName : 'parentSkuTypeCode',
        pager : '#gridPager',
        formId : 'baseInfoForm',
        type : 0,
        cachedQueryConditions : '',
        priceMsg: '&nbsp;<span class="iconfont question" data-content="商品的销售价格"></span>',
        purchasePriceMsg: '&nbsp;<span class="iconfont question" data-content="商品的采购价格"></span>',
        costPriceMsg: '&nbsp;<span class="iconfont question" data-content="加工生产的成本价格"></span>',
        balancePriceMsg: '&nbsp;<span class="iconfont question" data-content="品牌配送给门店的价格,或门店调拨给其他门店的价格"></span>'
    },

    //初始化
    _init : function (args,isBrand) {

        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType)
        {
            case 0 ://列表查询
                _this.$listGrid = $('#' + _this.opts.listGridId);
                _this.delegateMultiSelect();
                _this.initQueryList(isBrand);
                _this.delegateQueryBtn();
                $.setSearchFocus();
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

    delegateQueryBtn : function(){

        var _this = this;

        $(document).delegate('#btn-query', 'click', function(){

            _this.opts.cachedQueryConditions = serializeFormById(_this.opts.queryConditionsId);

            $("#" + _this.opts.listGridId).refresh(-1);

            //添加查询条件缓存
            var query = {};
            query.data = $('#' + skulist.opts.queryConditionsId).serializeArray();
            query.formId = skulist.opts.queryConditionsId;

            sessionStorage.setItem('query',JSON.stringify(query));
        });
    },

    //初始化查询页面
    initQueryList : function(isBrand) {
        var opts = skulist.opts;
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/sku/edit',
            'scm_kry/sku/view',
            'scm_kry/sku/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        $.serializeGridDataCallback = function (formData) {
            var skuCode = $("#skuCode").val().trim(),skuType = $("#skuType").val(),skuWm = $("#skuWm").val(),isDisable = 0,
                checkbox0 = $("#checkbox-0").hasClass("checkbox-check"),checkbox1 = $("#checkbox-1").hasClass("checkbox-check");

            delete formData.wmType;delete formData.skuCode;delete formData.skuTypeId;
            if(skuWm!=-1) formData["wmType"] = skuWm;
            if(skuCode!="") formData["skuCode"] = skuCode;
            if(skuType!=-1) formData["skuTypeId"] = skuType;
            if(checkbox1) isDisable = 1;
            if(checkbox0&&checkbox1) isDisable = 2;
            if(!checkbox0&&!checkbox1) isDisable = 2;
            formData["isDisable"] = isDisable;
            return formData;
        };

        $.showEdit = function (rowData) {
            var flag = renderEnum.normal;
            if(rowData.isDisable == 1) flag = renderEnum.disabled;
            return flag;
        };

        //先假设可用判断置灰，再判断是否可用
        $.showlock = function (rowData) {
            var flag = renderEnum.normal;
            if(rowData.wmType!=4&&rowData.wmType!=5) flag = renderEnum.disabled;
            if(rowData.isDisable == 1) flag = renderEnum.hidden;
            if(!isBrand) flag = renderEnum.hidden;
            return flag;
        };

        $.showUnlock = function (rowData) {
            var flag = renderEnum.normal;
            if(rowData.wmType!=4&&rowData.wmType!=5) flag = renderEnum.disabled;
            if(rowData.isDisable == 0) flag = renderEnum.hidden;
            if(!isBrand) flag = renderEnum.hidden;
            return flag;
        };
        
        $.showDelete = function(rowData){
        	 var flag = renderEnum.normal;
             if(rowData.wmType!=4&&rowData.wmType!=5) flag = renderEnum.disabled;
            if(!isBrand) flag = renderEnum.hidden;
             return flag;
        };

        //删除前检测数据是否需要提示
	    $.doDeleteOrLockTask = function(data){
	    	$.ajax({
               url: skulist.opts.urlRoot+(data.isDelete?skulist.opts.deleteUrl:skulist.opts.lockUrl),
               type: "post",
               async: false,
               data: data.isDelete?{skuIds:data.postData.id}:{id:data.postData.id},
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
	    };
	   
	   //处理停用事件
	   $.doClock = function(data){$.doDelete(data);};
	    
	   //处理删除事件
       $.doDelete = function(dataArgs){
    	   var isDelete = dataArgs.domId.indexOf("#delete")!=-1,
    	       opts = {
	    		   callBack: $.doDeleteOrLockTask,
	    		   callBackArgs:dataArgs,
				   hint:isDelete?'删除后不可恢复，确认删除？':'商品已被引用，确定停用？',
				   dataHint:isDelete?'删除后会清除对应配方、模板、供货关系、库存预警等信息：':'以下为引用配方、模板、供货关系、库存预警等信息：'
    	    }
    	   $.ajax({
               url: dataArgs.url,
               type: "post",
               async: false,
               data: {skuIds:dataArgs.postData.id},
               success: function (data) {
            	   if(data.count>0){
            		   opts.showBills = true;
            		   opts.dataList = data.skuReferenceVO;
            	   }else{
            		   if(!isDelete) opts.hint='确定停用？'; 
            	   }
            	   dataArgs.isDelete = isDelete;
             	   opts.callBackArgs=dataArgs;
                   $.message.showDialog(opts);
                   $("#skuCodeOrName").focus().blur();
               },
               error: function () {
                   $.layerMsg("网络错误", false);
               }
           });
        }

        var $gridObj = $("#" + _this.opts.listGridId);


        $gridObj.dataGrid({
            rownumbers: true,
            multiselect: isBrand,
            gridview:false,

            formId: _this.opts.queryConditionsId,
            //serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', 'wmType','商品分类', '库存类型', '商品编码', '商品名称(规格)', '单位(标准)','售卖价'+opts.priceMsg,'采购价'+opts.purchasePriceMsg,'成本价'+opts.costPriceMsg,'结算价'+opts.balancePriceMsg,'状态', '操作'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'wmType', index: 'wmType', width: 50, hidden: true},
                {name: 'skuTypeName', index: 'typeName', align: "left", width: 120},
                {name: 'wmTypeName', index: 'wmType', align: "left", width: 120},
                {name: 'skuCode', index: 'skuCode', align: "left", width: 180},
                {name: 'standerStr', index: 'skuName', align: "left", width: 180},
                {name: 'unitName', index: 'unitName', align: "center", width: 120},
                {

                    name: 'price',
                    index: 'price',
                    align: "center",
                    width: 120,
                    formatter:function (cellvalue,options,rowObject) {
                        if (rowObject.price == null){
                            return "<span>-</span>";
                        }else {
                            return rowObject.price;
                        }
                    }

                },
                {
                    name: 'purchasePrice',
                    index: 'purchasePrice',
                    align: "center",
                    width: 120,
                    formatter:function (cellvalue,options,rowObject) {
                        if ( rowObject.wmType == 1 || rowObject.wmType == 2 || rowObject.wmType == 5 ||  rowObject.purchasePrice == null){
                            return "<span>-</span>";
                        }else {
                            return rowObject.purchasePrice;
                        }
                    }
                },
                {
                    name: 'costPrice',
                    index: 'costPrice',
                    align: "center",
                    width: 120,
                    formatter:function (cellvalue,options,rowObject) {
                        if (rowObject.wmType == 3 || rowObject.wmType == 4  || rowObject.costPrice == null  ){
                            return "<span>-</span>";
                        }
                        else {
                            return rowObject.costPrice;
                        }
                    }
                },
                {
                    name: 'balancePrice',
                    index: 'balancePrice',
                    align: "center",
                    width: 120,
                    formatter:function (cellvalue,options,rowObject) {
                        if (rowObject.wmType == 2 || rowObject.balancePrice == null){
                            return "<span>-</span>";
                        }else {
                            return rowObject.balancePrice;
                        }
                    }
                },
                {
                    name: 'status',
                    index: 'isDisable',
                    align: "center",
                    width: 100,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 1) {
                            return "<span style='color:red'>停用 </span>";
                        } else {
                            return "启用";
                        }
                    }
                },
                {name: 'isDisable', index: 'isDisable', width: 150, hidden: true}
            ],
            sortname: 'skuCode',
            sortorder: "asc",
            pager: _this.opts.pager,
            showOperate: false,
            actionParam: {
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl
                },
                editor: {
                    render : $.showEdit,
                    code: "scm:button:masterdata:sku:edit",
                    url: _this.opts.urlRoot + _this.opts.editUrl
                },
                clock: {
                    render : $.showlock,
                    code: "scm:button:masterdata:sku:disable",
                    beforeCallback:'$.doClock',
                    url: _this.opts.urlRoot + _this.opts.referencesUrl

                },
                unlock: {
                    render :  $.showUnlock,
                    code: "scm:button:masterdata:sku:enable",
                    url: _this.opts.urlRoot + _this.opts.unlockUrl
                },
                delete:{
                	 render : $.showDelete,
                     code: "scm:button:masterdata:sku:delete",
                	 beforeCallback:'$.doDelete',
                     url: _this.opts.urlRoot + _this.opts.referencesUrl
                }
            },

            afterInsertRow: function (rowid, rowData) {
            	 if(rowData.wmType!=4&&rowData.wmType!=5) {
                     $('#jqg_grid_' +  rowid).prop('disabled',true).css('opacity','0.3');
                }
            },
           beforeSelectRow : function (rowid, e){
            	var rowData =$gridObj.getRowData(rowid);
                if(rowData.wmType!=4&&rowData.wmType!=5){
                    return false;
                }else{
                    return true;
                }
            },
            onSelectAll : function (aRowids, status){
                if(status){
                    // uncheck "protected" rows
                    var cbs = $("tr.jqgrow > td > input.cbox:disabled", $gridObj[0]);
                    cbs.removeAttr("checked");

                    //modify the selarrrow parameter
                    $gridObj[0].p.selarrrow = $gridObj.find("tr.jqgrow:has(td > input.cbox:checked)")
                        .map(function() { return this.id; }) // convert to set of ids
                        .get(); // convert to instance of Array

                    //deselect disabled rows
                    $gridObj.find("tr.jqgrow:has(td > input.cbox:disabled)")
                        .attr('aria-selected', 'false')
                        .removeClass('ui-state-highlight');
                }
            }
        });

        _this.opts.cachedQueryConditions = serializeFormById(_this.opts.queryConditionsId);
    },


    /**
     * 监听多选下拉框
     */
    delegateMultiSelect: function(){

        var _this = this;

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


        //任意点击隐藏下拉层
        $(document).bind("click",function(e){
            var target = $(e.target);
            //当target不在popover/coupons-set 内是 隐藏
            if(target.closest(".multi-select-con").length == 0 && target.closest(".select-control").length == 0){
                $(".multi-select-con").hide();
            }
        });


        _this.delegateCheckbox('skuTypes', '#sku-type-all');
        _this.delegateCheckbox('wmTypes', '#wm-type-all');
    },

    /**
     * 监听下拉选框的checkbox
     * @param name
     */
    delegateCheckbox: function(name, id){

        var _this = this;

        $(document).delegate(":checkbox[name='"+ name + "']","change",function(){
            _this.associatedCheckAll(this, $(id));
            _this.filterConditions(name,
                $(this).parents(".multi-select-con").prev(".select-control").find("em"),
                $(this).parents(".multi-select-con").next(":hidden"));
        });

        $(document).delegate(id,"change",function(){
            _this.checkAll(this,name);
            _this.filterConditions(name,
                $(this).parents(".multi-select-con").prev(".select-control").find("em"),
                $(this).parents(".multi-select-con").next(":hidden"));
        });
    },

    /**
     *    associatedCheckAll     //关联全选
     *    @param  object         e           需要操作对象
     *    @param  jqueryObj      $obj        全选对象
     **/
    associatedCheckAll: function(e, $obj){
        var _this = this;
        var flag = true;
        var $name = $(e).attr("name");
        _this.checkboxChange(e,'checkbox-check');
        $("[name='"+ $name +"']:checkbox").not(":disabled").each(function(){
            if(!this.checked){
                flag = false;
            }
        });
        $obj.get(0).checked = flag;
        _this.checkboxChange($obj.get(0),'checkbox-check');
    },

    /**
     *    checkbox               //模拟checkbox功能
     *    @param  object         element     需要操作对象
     *    @param  className      class       切换的样式
     **/
     checkboxChang: function(element,className){
        if(element.readOnly){return false;}
        if(element.checked){
            $(element).parent().addClass(className);
        }else{
            $(element).parent().removeClass(className);
        }
    },

    /**
     * 条件选择
     * @param checkboxName      string                  checkbox name
     * @param $textObj          jquery object           要改变字符串的元素
     * @param $hiddenObj        jquery object           要改变的隐藏域
     */
    filterConditions: function(checkboxName, $textObj, $hiddenObj){
        var checkboxs = $(":checkbox[name='" + checkboxName + "']");
        var checkboxsChecked = $(":checkbox[name='" + checkboxName + "']:checked");
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

    },


    /**
     *    checked all            //全选
     *    @param  object         e           需要操作对象
     *    @param  nameGroup      string      checkbox name
     **/
    checkAll : function(e,nameGroup){

        var _this = this;

        if(e.checked){
            //alert($("[name='"+ nameGroup+"']:checkbox"));
            $("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
                this.checked = true;
                _this.checkboxChange(this,'checkbox-check');
            });
        }else{
            $("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
                this.checked = false;
                _this.checkboxChange(this,'checkbox-check');
            });
        }
        _this.checkboxChange(e,'checkbox-check');
    },

    /**
     *    checkbox               //模拟checkbox功能
     *    @param  object         element     需要操作对象
     *    @param  className      class       切换的样式
     **/
    checkboxChange : function(element,className){
        if(element.readOnly){return false;}
        if(element.checked){
            $(element).parent().addClass(className);
        }else{
            $(element).parent().removeClass(className);
        }
    },
   //批量删除事件
    batchDelete : function(){
    	var skuIdsStr = "",skuIds = $("#grid").jqGrid("getGridParam", "selarrrow");
    	for(var i=0;i<skuIds.length;i++) skuIdsStr+=","+skuIds[i];
    	
    	if(skuIdsStr.length<=0){
    		$.message.showDialog({confirm:false,hint:'请至少选择一个商品！'});
    	}else{
    		var args = {
    			url:'/scm_kry/sku/references/get',
    			dataGridId:"grid",
    			beforeCallback:"$.doDelete",
    			domId:"#delete_batch",
    			postData:{
    				id:skuIdsStr.substring(1)
    			}
    		};
    		$.doDelete(args);
    	}
    }
};





