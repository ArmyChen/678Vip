/*create by lixing 2015-09-30*/
var _gridTable = '#grid';
var wareHouseJson = JSON.parse($("#houseCache").val());//house缓存
var cacheCondition = {isClick:true};//缓存页面条件和点击查询状态
    
//列表渲染
$(function () { 
	initDefaultDate();//初始化日期
	cacheCondition.comId = $("#commericalId").val();
	reLoadList(cacheCondition.comId);//初始化
    $(_gridTable).dataGrid({
        formId: "queryConditions",
        url: 'report/allocationDifference/query',
        colNames: ['商户名称', '商品大类','商品中类', '商品编码', '商品名称（规格）', '单位', '调拨', '调拨数','调拨金额',
                   '收货(调入方)','收货数','收货金额','收货差异','差异数','差异金额','未收货数','未收货数','未收货金额'],
        colModel: [
            {name: 'commercialName', index: 'commercialName', width: 180, align: 'left'},
            {name: 'typeName', index: 'typeName', width: 160, align: 'left'},
            {name: 'skuTypeName', index: 'skuTypeName', width: 160, align: 'left'},
            {name: 'skuCode', index: 'skuCode', width: 180, align: 'left'},
            {name: 'skuName', index: 'skuName', width: 240, align: 'left',
                formatter: function (cellvalue, options, rowObject) {
                    if (rowObject.isDelete == 1) {
                        return cellvalue + "<span style='color:red'>(已删除)</span>";
                    } else if (rowObject.isDisable == 1) {
                        return cellvalue + "<span style='color:red'>(已停用)</span>";
                    } else {
                        return cellvalue;
                    }
                }
            },
            {name: 'uom', index: 'uom', width: 120, align: 'center'},
            /*{
                name: 'price',
                index: 'price',
                width: 120,
                align: "right",
                formatter: customCurrencyFormatter
            },*/
            //=================================
            {name: 'qtyAmountAvg',align: "right",hidden:true},
            {name: 'qtyNum', index: 'qtyNum', width: 150, align: "right"},
            {name: 'qtyAmount', index: 'qtyAmount', width: 150, align: "right",formatter: customCurrencyFormatter},
            //=================================
            {name: 'actualQtyAmountAvg',align: "right",hidden:true},
            {name: 'actualQty', index: 'actualQty', width: 150, align: "right"},
            {name: 'actualQtyAmount', index: 'actualQtyAmount', width: 150, align: "right",formatter: customCurrencyFormatter},
            //=================================
            {name: 'diffAmountAvg',align: "right",hidden:true},
            {name: 'diffNum', index: 'diffNum', width: 150, align: "right"},
            {name: 'diffAmount', index: 'diffAmount', width: 150, align: "right",formatter: customCurrencyFormatter},
            //=================================
            {name: 'actualQtyNotAmountAvg',align: "right",hidden:true},
            {name: 'actualQtyNot', index: 'actualQtyNot', width: 150, align: "right"},
            {name: 'actualQtyNotAmount', index: 'actualQtyNotAmount', width: 150, align: "right",formatter: customCurrencyFormatter}
        ],
        sortname: ' type_name,sku_type_name,sku_code_',//手动转换：1.开头一个空格是防止首字母被大写后，第一个单词后面被重复添加“_”；2.最后一个“_”供工具类截取
        sortorder:'asc',
        footerrow: true,
        rowNum:9999,
        gridComplete: function() {
             var rowNum = parseInt($(this).getGridParam('records'),10);
	         if(rowNum > 0){
	          $(".ui-jqgrid-sdiv").show();
	             	var qtyNum  = jQuery(this).getCol('qtyNum',false,'sum'),
		             	qtyAmount  = jQuery(this).getCol('qtyAmountAvg',false,'sum'),
		             	diffNum  = jQuery(this).getCol('diffNum',false,'sum'),
		             	diffAmount  = jQuery(this).getCol('diffAmountAvg',false,'sum'),
	             	    actualQty  = jQuery(this).getCol('actualQty',false,'sum'),
	             	    actualQtyAmount  = jQuery(this).getCol('actualQtyAmountAvg',false,'sum'),
	             	    actualQtyNot  = jQuery(this).getCol('actualQtyNot',false,'sum');
	             		actualQtyNotAmount  = jQuery(this).getCol('actualQtyNotAmountAvg',false,'sum');
	               $(this).footerData("set",{uom:"合计:",
	            	   qtyNum:qtyNum,
	            	   qtyAmount:qtyAmount,
	            	   actualQty:actualQty,
	            	   actualQtyAmount:actualQtyAmount,
	            	   diffNum:diffNum,
	            	   diffAmount:diffAmount,
	            	   actualQtyNot:actualQtyNot,
	            	   actualQtyNotAmount:actualQtyNotAmount
	               });
	         }else{
	            $(".ui-jqgrid-sdiv").hide();
	         }
        },
        showOperate: false,
        dataType: 'local'
    });
    
    jQuery("#grid").jqGrid('setGroupHeaders', {
       	  useColSpanStyle: true,
       	  groupHeaders:[
       	    {startColumnName: 'qtyAmountAvg', numberOfColumns: 3, titleText: '调拨'},
       	    {startColumnName: 'actualQtyAmountAvg', numberOfColumns: 3, titleText: '收货(调入方)'},
       	    {startColumnName: 'diffAmountAvg', numberOfColumns: 3, titleText: '收货差异'},
       	    {startColumnName: 'actualQtyNotAmountAvg', numberOfColumns: 3, titleText: '未收货数'}
       	  ]
   	});

    $.setSearchFocus();
});

/* 监听下拉选框  */
$("#commericalId").on('change',function(){
	reLoadList($(this).val());
});

/*监听条件变更事件*/
$("#commericalId,#skuCodeOrName,#warehouseId,#startDate,#endDate").on('change',function(){
	var	page_endDate = $("#endDate").val(),
		page_startDate = $("#startDate").val(), 
		page_warehouseId = $("#warehouseId").val(), 
		page_commericalId = $("#commericalId").val(),
	    page_skuCodeOrName = $("#skuCodeOrName").val(); 
	if(page_commericalId!=cacheCondition.comId){
		cacheCondition.comId=page_commericalId;
		cacheCondition.isClick = false;
	}
	if(page_skuCodeOrName!=cacheCondition.skuCodeOrName){
		cacheCondition.skuCodeOrName=page_skuCodeOrName;
		cacheCondition.isClick = false;
	}
	if(page_warehouseId!=cacheCondition.warehouseId){
		cacheCondition.warehouseId=page_warehouseId;
		cacheCondition.isClick = false;
	}
	if(page_startDate!=cacheCondition.startDate){
		cacheCondition.startDate=page_startDate;
		cacheCondition.isClick = false;
	}
	if(page_endDate!=cacheCondition.endDate){
		cacheCondition.endDate=page_endDate;
		cacheCondition.isClick = false;
	}
});

/*监听全部撤销按钮*/
//重置表单
$("#undo-all1").on("click", function (e) {
    e.preventDefault();
    bkeruyun.clearData($(this).parents('.aside'));
    if (!bkeruyun.isPlaceholder()) {
        JPlaceHolder.init();
    }
    initDefaultDate();
});

/*设置初始时间*/
function initDefaultDate() {
    var currentDate = new Date().Format('yyyy-MM-dd');
    $('#startDate').val(currentDate);
    $('#endDate').val(currentDate);
}

/*重新加载下拉框*/
function reLoadList(commericalId){
	var myHouse = $("#warehouseId"),
	    newList='<li>请选择调出仓库</li>',
	    newOption = '<option value="">请选择调出仓库</option>';
	for(var i = 0;i<wareHouseJson.length;i++){
		var each = wareHouseJson[i];
		if(commericalId==each.commercialId){
			var endStr = each.isDisable?"(已停用)":"";
			newList+='<li>'+each.warehouseName+endStr+'</li>';
			newOption+='<option value="'+each.id+'">'+each.warehousrName+endStr+'</option>';
		}
	}
	myHouse.html(newOption);
	myHouse.parent().find("ul").html(newList);
	myHouse.parent().find("em").html("请选择调出仓库");
}

/*下载报表*/
function exportResult(){
    var commercialId = $("#commericalId").val();
	if (commercialId == null || commercialId == undefined || commercialId == '-999') {
        $.layerMsg('请选择商户!', false);
        return;
  	}

    var $gridObj = $(_gridTable);
    var totalSize = $gridObj.jqGrid('getGridParam','records');
  
    //数据一致性检查
    if(!cacheCondition.isClick){
    	$.layerMsg('条件已改变，请先点击查询按钮！', false);
    	return false;
    }
    
    if(totalSize > 0){
        var sidx = $gridObj.jqGrid('getGridParam','sortname'),
            sord = $gridObj.jqGrid('getGridParam','sortorder'),
        	comName = $("#commericalId").parent().find('em').html(),
        	houseName = $("#warehouseId").val()==""?"":$("#warehouseId").parent().find('em').html(),
        	exportUrl = "report/allocationDifference/export?rows=0&sidx="+sidx+"&sord="+sord;
        $("#comName").val(comName);
        $("#houseName").val(houseName);
        $("#queryConditions").attr("action", exportUrl);
        $("#queryConditions").submit();
    } else{
        $.layerMsg('导出记录为空！', false);
    }
}

/*查询新的数据*/
function queryData() {
    var commercialId = $("#commericalId").val();
    if (commercialId == null || commercialId == undefined || commercialId == '-999') {
        $.layerMsg('请选择商户!', false);
        return;
    }
    cacheCondition.isClick = true;
    $(_gridTable).refresh(-1);
}

/*格式化价格*/
function customCurrencyFormatter(cellvalue, options, rowObject) {
    if (!cellvalue && cellvalue != 0) {
        cellvalue = '';
    }
    var numberstr = (typeof cellvalue == 'string' ? cellvalue : cellvalue.toString());
    if(numberstr.indexOf('合计')==0){
    	return numberstr;
    }
    //处理负数（影响千分位的计算）
    var minus = numberstr.indexOf('-') == 0 ? '-' : '';
    if (minus === '-') {
        numberstr = numberstr.substring(1);
    }
    numberstr = returnWithoutDecimalZero(numberstr);
    var index = numberstr.lastIndexOf('.');
    var left = index > 0 ? numberstr.substring(0, index) : numberstr;
    var right = index > 0 ? numberstr.substring(index + 1) : '';
    var count = 1;
    for (var pointer = left.length - 1; pointer > 0; pointer--) {
        if (count % 3 == 0) {
            var replace_left = left.substring(0, pointer);
            //var replace = left.substring(pointer, pointer + 1);
            var replace_right = left.substring(pointer + 1);
            var withstr = ',' + left.charAt(pointer);
            left = replace_left + withstr + replace_right;
        }
        count++;
    }
    return "￥" + minus + left + (index > 0 ? '.' : "") + right;
}