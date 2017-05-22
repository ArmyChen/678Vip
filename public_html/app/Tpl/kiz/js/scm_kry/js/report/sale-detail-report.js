/**
 * 销售明细表
 * Created by LiXing on 2016/12/12.
 */

var saleDetailReport = {
    //默认参数
    opts : {
        urlRoot : "/report/saledetail",
        queryUrl : "/query",
        exportUrl : "/export",
        queryData : "",
        footerData : {},
        oldCommercialId : "",
        cachedQuery: '' //缓存页面条件
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        //初始化库存类型
        // delegateCheckbox('wmTypes', '#wm-type-all', false);
        
        //供应商-商品查询按钮点击
        $(document).delegate("#search", "click", function () {
            _this.query();
        });

        //页面初始化完成后查询供应商-商品采购
        $("#search").click();

        //供应商-商品查询条件重置
        $("#undo-all_1").on("click", function (e) {
            e.preventDefault();
            bkeruyun.clearData($(this).parents('.aside'));

            if (!bkeruyun.isPlaceholder()) {
                JPlaceHolder.init();
            }

            $("#commercialId-all,#wm-type-all").click().click();
            
            $("#skuTypeId").val("");
            $("#skuTypeId").trigger("chosen:updated");
        });

        $.setSearchFocus();
        
        $("#skuName").focus();
    },

    //获取当前的查询条件
    getCondition : function(){
        var condition = $.extend(true, {}, $("#queryConditions").getFormData() || {});

        var billDateStart = condition.billDateStart;
        var billDateEnd = condition.billDateEnd;

       if(billDateStart.replace('-','') > billDateEnd.replace('-','')){
            $.layerMsg('开始日期不能大于结束日期！', false);
            return false;
        }

        var iDays = saleDetailReport.getDays(billDateStart, billDateEnd);
        if (iDays > 365) {
            $.layerMsg('查询日期不应超过一年！', false);
            return false;
        }

         var supplier = $("#supplierId").find("option:selected").text().trim();
         var skuTypeName = $("#skuTypeId").find("option:selected").text().trim();
         var statusStr = "";
         if (!!condition.isEnable) statusStr += "启用，";
         if (!!condition.isDisable)statusStr += "停用，";
         if (!!condition.isDelete) statusStr += "删除，";
         if (statusStr.length>0) statusStr = statusStr.substring(0, statusStr.length - 1);
        
         var commercialStr = "",wareHouseStr="",wmTypeStr="";
         $('input[name="wmTypes"]:checked').each(function(){wmTypeStr+=$(this).attr("data-text")+"，"});
         $('input[name="wmIds"]:checked').each(function(){wareHouseStr+=$(this).attr("data-text")+"，"});
         $('input[name="commercialIds"]:checked').each(function(){commercialStr+=$(this).attr("data-text")+"，"});
       
         condition.skuTypeStr = condition.skuTypeId==""?"":$("#skuTypeIds").find("span").html();
         condition.wmTypeStr = wmTypeStr.length>0?wmTypeStr.substring(0,wmTypeStr.length-1):"";
         condition.wareHouseStr = wareHouseStr.length>0?wareHouseStr.substring(0,wareHouseStr.length-1):"";
         condition.commercialStr = commercialStr.length>0?commercialStr.substring(0,commercialStr.length-1):"";
         condition.statusStr = statusStr;
      
         //删除无用的key
         delete condition.wmIds;
         delete condition.wmTypes;
         delete condition.commercialIds;
         
         return condition;
    },
    
    //供应商-商品分析查询
    query : function () {
        var _this = this;
        var condition = saleDetailReport.getCondition();
        if(!condition){
        	return false;
        }else{
        	//缓存上次的条件
        	saleDetailReport.cacheQuery = JSON.stringify(condition);
        }
        
        bkeruyun.showLoading();
        $.ajax({
            type: "post",
            async: false,
            url : ctxPath + _this.opts.urlRoot + _this.opts.queryUrl,
            data : condition,
            success: function (data) {
                if (data && data.length > 0) {
                    $("#noData").hide();
                    $("#showData").show();
                    _this.buildTable(data);
                } else {
                    $("#showData").hide();
                    $("#noData").show();
                    $("#gridDiv").empty().html('<table id="grid"></table>');
                }
            },
            complete: function() {
                setTimeout(function() {
                    bkeruyun.hideLoading();
                }, 500);
            }
        });
    },

    //构建供应商-商品分析表格
    buildTable : function (data) {
        var _this = this;

        var $gridDiv =  $("#gridDiv");
        $gridDiv.empty().html('<table id="grid"></table>');
        var $grid = $("#grid");

        $grid.dataGrid({
            data: data,
            datatype: 'local',
            showEmptyGrid: true,
            autowidth: true,
            rownumbers: false,
            shrinkToFit:false,
            autoScroll: true,
            rowNum: 9999,
            height: 520,
            colNames: ['单据号','创建时间','销售门店', '库存类型', '商品分类', '商品编码', '商品名称（规格）', '单位', '价格', '销售数量', '出库数量','收货数量','销售金额'],
            colModel: [
                {name: 'orderNo', index: 'orderNo', /*width: 130,*/align: 'center',sortable: false},
                {name: 'updateTime', index: 'updateTime', align: 'center',sortable: false},
                {name: 'commercialName', index: 'commercialName', /*width: 100,*/ align: 'center',sortable: false,hidden:true},
                {name: 'wmTypeStr', index: 'wmTypeStr', /*width: 100,*/ align: 'center',sortable: false,hidden:true},
                {name: 'skuTypeName', index: 'skuTypeName',/* width: 110,*/ align: 'center',sortable: false,
                	formatter:function(cellvalue, options, rowObject) {
                        if (rowObject.skuTypeIsDisable == 1) {
                            return cellvalue + '<span style="color:red;">(已停用)</span>';
                        }
                        return cellvalue;
                    }
                },
                {name: 'skuCode', index: 'skuCode', /*width: 120,*/ align: 'center',sortable: false},
                {name: 'skuName', index: 'skuName', /*width: 150,*/sortable: false,
                	formatter:function(cellvalue, options, rowObject){
                		if (rowObject.skuIsDelete == 1) {
                            return cellvalue + "<span style='color:red'>(已删除)</span>";
                        } else if (rowObject.skuIsDisable == 1) {
                            return cellvalue + "<span style='color:red'>(已停用)</span>";
                        } else {
                            return cellvalue;
                        }
                	}
                },
                {name: 'uom', index: 'uom', /*width: 90,*/ align: "center",sortable: false},
                {name: 'price', index: 'price',/*width: 140,*/ align: "right", formatter:_this.amountFormatter,sortable: false},
                {name: 'saleQty', index: 'saleQty',/*width: 100,*/ align: "right", formatter:_this.qtyFormatter,sortable: false},
                {name: 'outBoundQty', index: 'outBoundQty',/*width: 140,*/ align: "right", formatter:_this.qtyFormatter,sortable: false},
                {name: 'receiveQty', index: 'receiveQty',/*width: 140,*/ align: "right", formatter:_this.qtyFormatter,sortable: false},
                {name: 'amount', index: 'amount',/*width: 165,*/ align: "right",formatter:_this.amountFormatter,sortable: false},
            ],
            sortname: 'orderNo',
            sortorder:'asc',
            gridComplete: function() {
            	merge(["orderNo","updateTime","commercialName"]);
            	
            	//让单据类型使用单据号样式
            	$("#grid>tbody>tr:gt(0)").each(function(indx,eh){
            		var eh = $(eh).find("td"),td0 = $(eh[0]);
            		var style = td0.attr("style")+"background: #ffffff !important;";
            		td0.attr("style",style);
            		$(eh[1]).attr("style",style).attr("rowspan",td0.attr("rowspan"));
                    $(eh[2]).attr("style",style).attr("rowspan",td0.attr("rowspan"));
            	});
            }
        });
    },

    //数量格式化
    qtyFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            return cellvalue;
        }
        return '-';
    },
    //金额格式换
    amountFormatter : function(cellvalue, options, rowObject) {
        if (!!cellvalue || cellvalue == 0) {
            return '￥' + cellvalue;
        }
        return '-';
    },

    //商品名称格式化
    skuFormatter : function(cellvalue, options, rowObject) {
        if (rowObject.skuIsDelete == 1) {
            return cellvalue + "<span style='color:red'>(已删除)</span>";
        } else if (rowObject.skuIsDisable == 1) {
            return cellvalue + "<span style='color:red'>(已停用)</span>";
        } else {
            return cellvalue;
        }
    },

    //商品类型停用格式化
    skuTypeFormatter : function(cellvalue, options, rowObject) {
        if (rowObject.skuTypeIsDisable == 1) {
            return cellvalue + '<span style="color:red;">(已停用)</span>';
        }
        return cellvalue;
    },

    //供应商停用格式化
    supplierFormatter : function(cellvalue, options, rowObject) {
        if (rowObject.supplierIsDisable == '1') {
            return cellvalue + '<span style="color:red;">(已停用)</span>';
        }
        return cellvalue;
    },

    /**
     * 计算相隔天数
     * @param strDateStart
     * @param strDateEnd
     * @returns {Number|*}
     */
    getDays: function (strDateStart, strDateEnd) {
        var strSeparator = "-"; //日期分隔符
        var oDate1;
        var oDate2;
        var iDays;
        oDate1 = strDateStart.split(strSeparator);
        oDate2 = strDateEnd.split(strSeparator);
        var strDateS = new Date(oDate1[0] + "-" + oDate1[1] + "-" + oDate1[2]);
        var strDateE = new Date(oDate2[0] + "-" + oDate2[1] + "-" + oDate2[2]);
        iDays = parseInt(Math.abs(strDateS - strDateE) / 1000 / 60 / 60 / 24);//把相差的毫秒数转换为天数
        return iDays;
    }
};

Date.prototype.yyyymmdd = function () {
    var yyyy = this.getFullYear().toString();
    var mm = (this.getMonth() + 1).toString(); // getMonth() is zero-based
    var dd = this.getDate().toString();
    return yyyy + '-' + (mm[1] ? mm : "0" + mm[0]) + '-' + (dd[1] ? dd : "0" + dd[0]); // padding
};

function merge(names) {
    var trs = $("#grid>tbody>tr:gt(0)");
    $.each(names, function (ind, name) {
      var bg = trs.eq(0).children("[aria-describedby='grid_" + name + "']"),
        index = bg.index(),
        rowsp = 1;
      trs.slice(1).each(function (ind2, tr) {
        var me = $(tr).children("td").eq(index);
        if (bg.text() === me.text()) {
          rowsp++;
          me.hide();
        } else {
          bg.attr("rowspan", rowsp);
          bg = me; 
          rowsp = 1;
        }
        bg.attr("rowspan", rowsp);
      });
    });
 }

//导出
$("#export").on("click",function(){
	 var condition = saleDetailReport.getCondition();
     if(!condition){
     	return false;
     }else{
     	//缓存上次的条件
    	 if(JSON.stringify(condition)!=saleDetailReport.cacheQuery){
    		 $.layerMsg('条件已改变，请先点击查询按钮！', false);
    		 return false;
    	 }
     }
     var gridData = $('#grid').jqGrid('getRowData');
     if (gridData.length == 0) {
    	 $.layerMsg('导出记录为空！', false);
		 return false;
     }
     
     condition.wmType = "["+condition.wmType+"]";
     condition.commercial = "["+condition.commercial+"]";
     
     var  data = JSON.stringify(condition).replace(new RegExp('"\\[',"gm"),'[').replace(new RegExp('\\]"',"gm"),']'),
     	  url = ctxPath + saleDetailReport.opts.urlRoot + saleDetailReport.opts.exportUrl+"?data="+data;
     window.open(url,"_self");
});