/**
 * 需求汇总单JS
 **/
var supplyAggregate = {
	//默认参数
    opts : {
        commandType : 1,
        orderGrid : 'order_grid',
        skuDetailGrid: 'sku_detail_grid',
        orderDetailGrid: 'order_detail_grid',
        lastCacheId : [],
        currentCacheId : [],
        gridOrderData : [], 
        gridSkuDetailData : []
    },
    
    //公共初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        switch (_this.opts.commandType)
        {
            case 1 ://新增
              _this.$orderGrid = $('#' + _this.opts.orderGrid);
              _this.$skuDetailGrid = $('#' + _this.opts.skuDetailGrid);
              _this.$orderDetailGrid = $('#' + _this.opts.orderDetailGrid);
              
              _this.initOrderDetailGrid(true);
              _this.initSkuDetailGrid(false);
              _this.initOrderDetailDataGrid(false);
              
              break;
            case 2 ://编辑
        	  _this.$orderGrid = $('#' + _this.opts.orderGrid);
              _this.$skuDetailGrid = $('#' + _this.opts.skuDetailGrid);
              _this.$orderDetailGrid = $('#' + _this.opts.orderDetailGrid);
             
              _this.initOrderDetailGrid(true);
              _this.initSkuDetailGrid(false);
              _this.initOrderDetailDataGrid(false);
              
              $.reloadOrderData();
              break;
            case 3 ://查看
          	  _this.$orderGrid = $('#' + _this.opts.orderGrid);
              _this.$skuDetailGrid = $('#' + _this.opts.skuDetailGrid);
              _this.$orderDetailGrid = $('#' + _this.opts.orderDetailGrid);
              
              _this.initSkuDetailGrid(false);
              _this.initOrderDetailDataGrid(false);
              initSkuQuery(supplyAggregate.opts.gridSkuDetailData);

              $.reloadOrderDataForView();
              break;
        }
    },
    
    //初始化单据明细列表
    initOrderDetailGrid : function(editable) {
        var _this = this,$orderBodyGrid = _this.$orderGrid;
        $orderBodyGrid.dataGrid({
            data: _this.opts.gridOrderData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['id','orderId','templateId','commercialId', '单据号', '申请商户','线路名称','配送模板','要货日期','到货日期'],
            colModel: [
                {name: 'id', index: 'id', hidden: true},
                {name: 'orderId',index: 'orderId',hidden: true},
                {name: 'templateId',index: 'templateId',hidden: true},
                {name: 'commercialId',index: 'commercialId',hidden: true},
                {name: 'orderNo', index: 'orderNo', sortable: true,width: 70,align: "center"},
                {name: 'commercialName', index: 'commercialName', sortable: true,width: 80,align: "center"},
                {name: 'dlNames', index: 'dlNames', sortable: true,width: 80,align: "center"},
                {name: 'templateName', index: 'templateName', sortable: true,width: 80,align: "center"},
                {name: 'applyDate', index: 'applyDate', sortable: true,width: 80,align: "center", sorttype:'date'},
                {name: 'arriveDate', index: 'arriveDate', sortable: true,width: 80,align: "center", sorttype:'date'}
            ],
            afterInsertRow: function (rowid, aData) {
                
            }
        });
        $orderBodyGrid.setGridWidth($('.panel').width() - 28);
    },
    
    //初始化商品明细列表
    initSkuDetailGrid : function(editable) {
        var _this = this,$skuBodyGrid = _this.$skuDetailGrid;
        $skuBodyGrid.dataGrid({
            data: _this.opts.gridSkuDetailData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['orderId','skuId','price','amount','库存类型', '所属分类', '商品编码','商品名称(规格)','单位','数量合计'],
            colModel: [
                {name: 'orderId', index: 'orderId', hidden: true},
                {name: 'skuId', index: 'skuId', hidden: true},
                {name: 'price', index: 'price', hidden: true},
                {name: 'amount', index: 'amount', hidden: true},
                {name: 'wmTypeName', index: 'wmTypeName', sortable: true,width: 70,align: "center"},
                {name: 'skuTypeName', index: 'skuTypeName', sortable: true,width: 80,align: "center"},
                {name: 'skuCode', index: 'skuCode', sortable: true,width: 80,align: "center"},
                {name: 'skuName', index: 'skuName', sortable: true,width: 80,align: "center"},
                {name: 'uom', index: 'uom', sortable: true,width: 80,align: "center"},
                {name: 'qty', index: 'qty', sortable: true,width: 80,align: "center", sorttype: 'number'}
            ],
            afterInsertRow: function (rowid, aData) {
                
            }
        });
        $skuBodyGrid.setGridWidth($('.panel').width() - 28);
    },
    
    //初始化单据查看明细列表
    initOrderDetailDataGrid : function(editable) {
        var _this = this,$orderDetailBodyGrid = _this.$orderDetailGrid;
        $orderDetailBodyGrid.dataGrid({
            data: _this.opts.gridOrderData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['id','orderId', '单据号', '申请商户','线路名称','配送模板','要货日期','到货日期'],
            colModel: [
                {name: 'id', index: 'id', hidden: true},
                {name: 'orderId',index: 'orderId',hidden: true},
                {name: 'orderNo', index: 'orderNo', sortable: true,width: 70,align: "center"},
                {name: 'commercialName', index: 'commercialName', sortable: true,width: 80,align: "center"},
                {name: 'dlNames', index: 'dlNames', sortable: true,width: 80,align: "center"},
                {name: 'templateName', index: 'templateName', sortable: true,width: 80,align: "center"},
                {name: 'applyDate', index: 'applyDate', sortable: true,width: 80,align: "center"},
                {name: 'arriveDate', index: 'arriveDate', sortable: true,width: 80,align: "center"}
            ],
            afterInsertRow: function (rowid, aData) {
                
            }
        });
        $orderDetailBodyGrid.setGridWidth($('.panel').width() - 28);
    }
}

//保存和确认
doSaveOrConfim = function(type){
	//判断按钮
	if(!type||(type!="1"&&type!="2")) {$.layerMsg("请勿修改页面按钮！", false);return false;}
	//判断商品
	var sum = 0,qtyList = $("#sku_detail_grid").getCol('qty');
	if(qtyList.length==0) {$.layerMsg("商品明细不能为空！", false);return false;}
	for(var i=0;i<qtyList.length;i++) sum+=parseFloat(qtyList[i]);
	if(sum<=0) {$.layerMsg("商品的数量合计不能全部为0！", false);return false;}
	
	var id = $("#id").val(),
		orderNo = $("#orderNo").val();
	var data = {id:id,orderNo:orderNo,type:type};
	data.orderDetails = supplyAggregate.opts.gridOrderData;
	var url="";
    if(type=="1"){
        url="/supply/aggregate/save";
    }else{
        url="/supply/aggregate/confirm";
    }
	 $.ajax({
         url: ctxPath + url,
         type: 'post',
         dataType: 'json',
         contentType: 'application/json',
         async: false,
         data: JSON.stringify(data),
         success: function(rest){
        	 if(rest.status==200){
        		 if(type==1){//保存
        			 $("#id").val(rest.id);
        	         replaceUrl('/supply/aggregate/edit', 'id=' + rest.id);
        	         $("#command-type-name").text("编辑");
        	         document.title = '编辑商品需求汇总单';
        		 }else{//confim
        			 $("#id").val(rest.id);
        			 setTimeout(jumpView,1000);
        		 }
        		 $.layerMsg(rest.msg, true);
        	 }else if(rest.status==305){
        		 
        		 layer.confirm("配送申请单号"+rest.msg+"已被拒绝或反确认，系统将自动清除这些配送申请单，是否执行？汇总时将自动过滤掉！",
        				 {icon: 3, title: '提示', offset: '30%'}, function (index) {
        					var order_grid = $("#order_grid"),array = rest.applyId.split(",");
                  			for(var i=0;i<array.length;i++) order_grid.jqGrid('delRowData', array[i]);
                  			$("#tab1_li").trigger("click");
                  			$.reloadOrderData();	 
                  			layer.close(index);
                 });
        		
        	 } else{
        		 $.layerMsg(rest.msg, false);
        	 }
         },
         error: function () {
             $.layerMsg("网络错误，请检查网络或刷新页面试试", false);
         }
     });
}

function jumpView(){
	var id = $("#id").val();
	$.doForward({url:'supply/aggregate/view',postData:{id:id}});
}

//重载单据数据
$.reloadOrderData = function(){
	var dataArray = $("#order_grid").getRowData(),allId = $("#order_grid").getCol('id');
	supplyAggregate.opts.gridOrderData = dataArray;
	supplyAggregate.opts.currentCacheId = allId;
}

//重载单据数据(ForView)
$.reloadOrderDataForView = function(){
	var ids = [];
	var array = supplyAggregate.opts.gridOrderData;
	for(var i=0;i<array.length;i++) ids.push(array[i].id);
	supplyAggregate.opts.currentCacheId = ids;
}

//明细级联操作
$("#reservation-tab").on("click","li",function(){
	var cut = $(this),tabNo = cut.attr("data-id");
	$("#"+tabNo).show().siblings().hide();
	cut.addClass("current").siblings().removeClass("current");
});

//外层级联操作
$("#scmBreadCrumbs>li,#btnPrev,#btnNext").on("click",function(){
	var cut = $(this),tabNo = cut.attr("tabNo");
	if(tabNo=="tab1"){
		$("#btnPrev").hide();$("#btnNext").show();
		$("#btnSave").hide();$("#btnConfirm").hide();
	}else{
		var nextStatus = doNext();
		if(!nextStatus) {
			$.layerMsg('请先添加单据!', false);
			return nextStatus;
		}
		$("#btnPrev").show();$("#btnNext").hide();
		$("#btnSave").show();$("#btnConfirm").show();
	}
	$("#reservation-tab-li1").addClass('current').siblings().removeClass('current');
	$("#skuDetailsTab").show().siblings().hide();
	$("#"+tabNo).show().siblings().hide();
	$("#"+tabNo+"_li").addClass('current').siblings().removeClass('current');
});

//下一步操作，刷新明细数据
function doNext(){
	var last = supplyAggregate.opts.lastCacheId,current = supplyAggregate.opts.currentCacheId;
	
	if(current.length==0){
		return false;
	}else{
		if(last.sort().toString()==current.sort().toString()){
			return true;//单据未改变，不刷新商品明细
		}else{
			supplyAggregate.opts.lastCacheId = current;
		}
		
		loadOrderDetailData();//加载单据查看明细
		loadSkuDetailData(current.sort().toString());
	}
	return true;
}

//载入单据明细预览数据
function loadOrderDetailData(){
	var array = supplyAggregate.opts.gridOrderData;
	var grid = $('#order_detail_grid');
	grid.clearGridData();
	for(var i=0;i<array.length;i++){
		grid.jqGrid("addRowData", i, array[i]);
	}
}

//载入商品明细预览数据
function loadSkuDetailData(ids){
	 $.ajax({
         url: ctxPath + "/supply/aggregate/getSkuBySupply",
         type: 'post',
         async: false,
         data: {supplyIds:ids},
         success: function(array){
        	 var grid = $('#sku_detail_grid');
        	 grid.clearGridData();
        	 for(var i=0;i<array.length;i++) grid.jqGrid("addRowData", i, array[i]);
             initSkuQuery(array);//初始化商品查询条件
         },
         error: function () {
             $.layerMsg("网络错误，请检查网络或刷新页面试试", false);
         }
     });
}

//初始化商品过滤
function initSkuQuery(array){
	var skuTypeArray = [],wmTypeArray = [],typeOption = '',wmOption='';
  
	$("#skuTypeNameDiv").html('<select class="form-control" name="skuTypeName" id="skuTypeName"><option value="">请选择商品分类</option></select>');
	$("#wmTypeNameDiv").html('<select class="form-control" name="wmTypeName" id="wmTypeName"><option value="">请选择库存类型</option></select>');
	
	for(var i=0;i<array.length;i++){
		var each = array[i],skuTypeEh = each.skuTypeName,wmTypeEh = each.wmTypeName;
		if(skuTypeArray.indexOf(skuTypeEh)<0){
			skuTypeArray.push(skuTypeEh);
			typeOption+='<option value=' + skuTypeEh + '>' + skuTypeEh + '</option>';
		}
		if(wmTypeArray.indexOf(wmTypeEh)<0){
			wmTypeArray.push(wmTypeEh);
			wmOption+='<option value=' + wmTypeEh + '>' + wmTypeEh + '</option>';
		}
	}
	
	$('#skuTypeName').append(typeOption);
	bkeruyun.selectControl($('#skuTypeName'));
	
	$('#wmTypeName').append(wmOption);
	bkeruyun.selectControl($('#wmTypeName'));
}

//商品过滤
function filterSku() {
    var skuTypeName = $('#skuTypeName').find('option:selected').text();
    var wmTypeName = $('#wmTypeName').find('option:selected').text();
    if($('#skuTypeName').val() === '') skuTypeName = '';
    if($('#wmTypeName').val() === '') wmTypeName = '';
    
    var conditions1 = {skuCode: $('#skuCodeOrName').val(),skuTypeName: skuTypeName,wmTypeName:wmTypeName};
    var conditions2 = {skuName: $('#skuCodeOrName').val(),skuTypeName: skuTypeName,wmTypeName:wmTypeName};
    var rowIds1 = filterGridRowIds('sku_detail_grid', conditions1);
    var rowIds2 = filterGridRowIds('sku_detail_grid', conditions2);
    Array.prototype.push.apply(rowIds1, rowIds2);
    filterGridRows('sku_detail_grid', rowIds1);
}

//confimApply,使用onclick触发事件
function doApply(){
	var msg = $("#showLayerMsg").html();
	layer.confirm(msg, {title: '提示', offset: '30%',maxWidth:500}, function (index) {
		var checkMsg = doConfimApply(true);
		if(checkMsg){
			layer.confirm("配送申请单号"+checkMsg.msg+"已被拒绝，生成采购申请单将会自动过滤掉！", {icon: 3, title: '提示', offset: '30%'}, 
   				function (index) {
   					doConfimApply(false,checkMsg.type);
             		layer.close(index);
                }
   			);
		}
    });
}

/**
 * 生成采购申请单
 * */
function doConfimApply(check,tp){
	var checkMsg = undefined,id = $("#id").val(),
    type = $('input[name="type"]:checked').val(),
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
            		$("#doApply").remove();$("#showPic").show();
            	}else if(rest.status==305){
            		checkMsg = rest;
            		checkMsg["type"] = type;
            	}else{
            		$.layerMsg(rest.message, false);
            	}
        },
        error: function () {
            $.layerMsg("网络错误，请检查网络或刷新页面试试", false);
        },
        complete: function(XMLHttpRequest, textStatus){
            bkeruyun.hideLoading();
        }
    });
    return checkMsg;
}

//export
$("#export").on("click",function(){
	window.open(ctxPath + "/supply/aggregate/export?id="+$("#id").val(),"_self");
});

$("#wmTypeNameDiv,#skuTypeNameDiv").on("change",function(){
	$("#skuCodeOrName").focus();
});

//回车支持
$(document).on("keypress",function(event){
	if(event.keyCode == "13"){
        $("#filterSearch").trigger("click");
     }
});

