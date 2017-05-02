//Create by LiXing On 2015/8/11
var bumen_edit = {
	//默认参数
    opts : {
        commandType : 1,
        skuDetailGrid : 'grid',
        shopDetailGrid: 'shop_grid',
        gridSkuData : [], 
        gridShopData : []
    },
    
	//公共初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        switch (_this.opts.commandType)
        {
            case 1 ://新增
              _this.$skuDetailGrid = $('#' + _this.opts.skuDetailGrid);
              _this.$shopDetailGrid = $('#' + _this.opts.shopDetailGrid);
              
              _this.initSkuDetailGrid(true);
              _this.initShopDetailGrid(true);
              
              $.filterGrid.initSkuTypeNames();
              break;
            case 2 ://查看
              _this.$skuDetailGrid = $('#' + _this.opts.skuDetailGrid);
              _this.$shopDetailGrid = $('#' + _this.opts.shopDetailGrid);
              
              _this.initSkuDetailGrid(false);
              _this.initShopDetailGrid(false);
              
              $.filterGrid.initSkuTypeNames();
              break;
        }
    },
	    
	//初始化商品授权表格
    initSkuDetailGrid : function(editable) {
        var _this = this,$gridObj1 = _this.$skuDetailGrid;
        $gridObj1.dataGrid({
            data: _this.opts.gridSkuData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['skuId', '所属分类', '商品编码', '商品名称(规格)', '单位', '价格','价格-hidden','非授权商户', '当前换算率', '标准单位换算率', '定价', '单位ID'],
            colModel: [
                {name: 'id', index: 'id', hidden: true},
                {name: 'skuTypeName', index: 'skuTypeName', sortable: false,width: 70, formatter: $.skuIsDisableFormat},
                {name: 'skuCode', index: 'skuCode', sortable: false,width: 80, formatter: $.skuIsDisableFormat},
                {
                    name: 'skuName',
                    index: 'skuName',
                    width: 100,
                    sortable: false,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 1) {
                            return "<span style='color:#9D9D9D;'>" + cellvalue + "<span style='color:red'>(已停用)</span></span>";
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'uom', index: 'uom', width: 50, sortable: false, align: 'center',formatter: $.skuIsDisableFormat},
                {
                    name: 'price',
                    index: 'price',
                    width: 70,
                    sortable: false,
                    align: "right",
                    formatter: $.skuIsDisableFormat
                },
                //用于当前取值，以后干掉
                {
                    name: 'priceHidden',
                    index: 'priceHidden',
                    width: 70,
                    sortable: false,
                    align: "right",
                    formatter: function (cellvalue, options, rowObject){
                    	return rowObject.price;
                    },
                    hidden: true
                },
                {
                	name: 'exceptShopStr',
                	index: 'exceptShopStr',
                	width: 80,
                	sortable: false,
                	/**暂时屏蔽非授权商户**/
                	hidden: true,
                	formatter: function (cellvalue, options, rowObject){
                		return '<input name="multiSelectIpt" type="hidden" class="checkbox-selected" value="'+rowObject.exceptShopStr+'" />';
                	}
                },
                {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
                {name: 'uomId', index: 'uomId', align: 'right', hidden: true}
            ],
            afterInsertRow: function (rowid, aData) {
                $.removeSelectTitle(rowid); //移除下拉框列的表格title
            }
        });

        //模拟select选择，并去除所属td原有的overflow：hidden，让下拉框可见
        $("input[name='multiSelectIpt']").parents("td").css({"overflow" : "visible"}).removeAttr("title");
        $gridObj1.setGridWidth($('.panel').width() - 29);

        if(editable) $.delegateClickSelectGroup($gridObj1);
    },
     
    //初始化商户授权表格
    initShopDetailGrid : function(editable) {
        var _this = this,$gridObj2 = _this.$shopDetailGrid;
        $gridObj2.dataGrid({
            data: _this.opts.gridShopData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['id','name','商户编码', '商户名称', '商户地址','创建人','创建时间'],
            colModel: [
                {name: 'id',index: 'id',hidden: true},
                {name: 'name',index: 'name',hidden: true,
                	formatter:function(data,opt,cell){
                		return data?data:(cell.id==-1?"品牌":cell.commercialName);
                	}
                },
                {name: 'commercialId', index: 'commercialId', width: 50, sortable: false,align: 'center',formatter: $.shopIdFormat},
                {name: 'commercialName', index: 'commercialName', width: 70, sortable: false,formatter: $.shopNameFormat},
                {name: 'commercialAddress', index: 'commercialAddress',width: 60,sortable: false,formatter: $.shopIdFormat},
                {name: 'creatorId', index: 'creatorId', hidden: true,sortable: !editable},
                {name: 'createTime', index: 'createTime', hidden: true,sortable: !editable}
            ],
            afterInsertRow: function (rowid, aData) {

            }
        });
        
        $.resetExceptSelect();//数据重置
        $("input[name='multiSelectIpt']").selectMulti();//完成首次加载数据后初始化非授权商户下拉列表
        //预览时修改下拉为一般的input
        if(!editable){
    	   var taget = $("input[name='multiSelectIpt']").parents("td");
    	   for(var i=0;i<taget.length;i++){
    		   var eh = $(taget[i]),txt=eh.find("em").html();
    		   eh.attr("title",txt);
               //商品停用时非授权商户置灰
               if(eh.prev().children('span').length == 1){
                   eh.html('<span style="color:#9D9D9D;">' + txt + '</span>');
               }else{
                   eh.html(txt);
               }
    	   }
    	   taget.css({"overflow" : "hidden"});
        }
        $gridObj2.setGridWidth($('.panel').width() - 35);
    },
    
    //初始化商品类型
    initSkuTypeNames : function(details){
        var _this = this;
        var $skuTypeNameDiv = $("#skuTypeNameDiv");
        var skuTypeNames = [];
        var option = '';
        var select = '<select class="form-control" name="skuTypeName" id="skuTypeName"><option value="">请选择商品分类</option></select>';
        $skuTypeNameDiv.html(select);
        $.each(details, function(index, detail){
            if(skuTypeNames.indexOf(detail.skuTypeName) < 0){
                skuTypeNames.push(detail.skuTypeName);
                option += '<option value=' + detail.skuTypeName + '>' + detail.skuTypeName + '</option>';
            }
        });
        $('#skuTypeName').append(option);
        bkeruyun.selectControl($('#skuTypeName'));
    }
};

//------------------------格式化 start---------------------
$.skuIsDisableFormat = function (cellvalue, options, rowObject) {
    if (rowObject.isDisable == 1) {
        return "<span style='color:#9D9D9D;'>" + cellvalue + "</span>";
    } else {
        return cellvalue;
    }
};

$.shopNameFormat = function(data,opt,cell){
	if (typeof(cell.status) != "undefined" && cell.id == -1) {
        return data+"(<span style='color:red;'>品牌</span>)";
    } else if(typeof(cell.status) != "undefined" && cell.isSupply == 0) {
        return "<span style='color:#9D9D9D;'>" + data + '<span style="color:red;">(未开通供应链)</span></span>';
    }
	return data;
};

$.shopIdFormat = function(data,opt,cell){
	if(typeof(cell.status) != "undefined"&&cell.status!=0&&cell.id!=-1) return "<span style='color:#9D9D9D;'>"+data+'</span>';
	return data;
};
$.resetExceptSelect = function(){
	var currentShop = {},newData = $("#shop_grid").jqGrid('getRowData');
	for(var i=0;i<newData.length;i++){
		var name=newData[i].commercialName,notUsing = (name.indexOf("</span>")!=-1&&name.indexOf('<span style="color:red;">(未开通供应链)</span>')!=-1);
		currentShop[newData[i].id] = newData[i].name+(notUsing?"(未开通供应链)":"");
	}
	$.setSelectMultiData(currentShop);
};
//-------------------------格式化 end-----------------------

/* tab切换 */
$("#reservation-tab").on("click","li",function(){
	$("#"+$(this).attr("tabId")).show().siblings().hide()
	$(this).addClass('current').siblings().removeClass('current');
});

//保存或修改
$("#btnNext,#btnSave-bak").on("click", function () {
	var isBtnBak = $(this).attr("id")=="btnSave-bak";
	
	var loadData = loadFormData();//将数据回写到form
	
    $("#bumenForm").submit();
    //检查是否验证通过
    // var flag = $("#bumenForm").valid();
    var flag = true;

    if (flag&&loadData) {
        var params = $("#bumenForm").serialize();
        $.ajax({
            type: "POST",
            url: ctxPath+"&act=bumen_add_ajax",
            data: params,
            dataType: "json",
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            async: false,
            cache: false,
            success: function (data) {
                $("#btn-save").bind("click");
                if (data.success) {
                    $.layerMsg("操作成功！", true, {
                        end:function(){
                            window.location.href = basicPath + "&act=bumen_index";
                        },shade: 0.3});
                	// var showMsg = $("#bumenCode").val().length==0;
                	// if(isBtnBak){
                	// 	$("#btnMore").text("隐藏更多......");
                	// 	$("#setMore").show();
                	// 	if(showMsg){
                	// 		$.layerMsg("操作成功，"+data.scmbumen.bumenName+"编码是：<span style='color:red;'>"+data.scmbumen.bumenCode+"</span>", true,{shade: 0.3});
                	// 	}else{
                	// 		$.layerMsg("操作成功！", true,{shade: 0.3});
                	// 	}
                	// 	doResetType();
                	// }else{
                	// 	if(showMsg){
                	// 		$.layerMsg("操作成功，"+data.scmbumen.bumenName+"编码是：<span style='color:red;'>"+data.scmbumen.bumenCode+"</span>", true, {
                	// 			end:function(){
                	// 				window.location.href = $("#root_url").val() + "/scm/bumen/index";
                	// 			},shade: 0.3});
                	// 	}else{
                	// 		$.layerMsg("操作成功！", true, {
                	// 			end:function(){
                	// 				window.location.href = $("#root_url").val() + "/scm/bumen/index";
                	// 			},shade: 0.3});
                	// 	}
                	// }
                } else {
                    $.layerMsg("操作失败！", true,{shade: 0.3});
                     // if (data.message.indexOf("编码") != -1) {
                     // 	var lab = '<label for="bumenCode" generated="true" class="error">'+data.message+'</label>';
                     // 	$("#bumenCode1").parent().find(".wrong").html(lab);
                     // } else if (data.message.indexOf("名称") != -1) {
                     // 	var lab = '<label for="bumenName" generated="true" class="error">'+data.message+'</label>';
                     // 	$("#bumenName1").parent().find(".wrong").html(lab);
                     // } else if (data.message.indexOf("类别") != -1) {
                     //     var lab = '<label for="bumenCateId" generated="true" class="error">'+data.message+'</label>';
                     //     $("#bumenCateId1").parents('.panel-item').find('.wrong').html(lab);
                     // } else if (data.message.indexOf("过期") != -1) {
                     // 	$.layerMsg(data.message, false, {
                     //         end:function(){
                     //             //window.location.href = getContextPath() + "/scm/bumen/index";
                     //             window.location.reload(true);//fix bugId 12547
                     //     }});
                     // }
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $("#btn-save").bind("click");
                $.layerMsg("保存失败，请刷新页面或重新登录！", false);
            }
        });
    }
});

//验证格式
$("#bumenForm").validate({
    errorPlacement: function (error, element) {
        error.appendTo(element.parents(".positionRelative").find(".wrong"));
    },
    focusInvalid: true,
    //只验证不提交 需要提交时注销这段代码
    debug: true
});

//取消操作
$("#btncancle").on("click", function () {
    layer.confirm("是否放弃当前操作？", {icon: 3, title:'提示', offset: '30%'}, function(index){
        window.location = basicPath+"&act=bumen_index";
        layer.close(index);
    });
});

//将数据放入表单内部
function loadFormData(){
	var allSkuData = new Array(),
	    allShopData = new Array(),
	    allUnLicensed = new Array(),
	    taxRate = $("#taxRate1").val(),
	    bumenCode = $("#bumenCode1").val(),
	    bumenName = $("#bumenName1").val(),
	    bumenCateId = $("#bumenCateId1").val(),
        allSkuGrid = $("#grid").getRowData(),
        allShopGrid = $("#shop_grid").getRowData();
	
	$("#taxRate").val(taxRate);
	$("#bumenCode").val(bumenCode);
	$("#bumenName").val(bumenName);
	$("#bumenCateId").val(bumenCateId);
	$("#bond_sku").val($("#checkbox-0").hasClass("checkbox-check")?true:false);
	$("#bond_price").val($("#checkbox-1").hasClass("checkbox-check")?true:false);
	
	if(bumenName=="") {$("#bumenName1").parent().find(".wrong").html("此栏位为必填");return false;}
	if(bumenCateId=="") {$("#bumenCateId1").parent().parent().parent().find(".wrong").html("此栏位为必填");return false;}
	if(taxRate=="") {$("#taxRate1").parent().parent().find(".wrong").html("此栏位为必填");return false}
	
	/*if($("#checkbox-0").hasClass("checkbox-check")){
		if($("#sku_grid").getRowData().length<=0){
			$.layerMsg("商品约束被勾选时,必须保证商品授权列表至少有一个商品!", false);
			return false;
		}
	}*/
	
	//获取商品数据并验证
	for(var i=0;i<allSkuGrid.length;i++){
		var each = allSkuGrid[i],skuId = each.id,exceptShopStr = $(each.exceptShopStr).find("input[name='multiSelectIpt']").val();
		if(exceptShopStr!=""){
			var exceptShop = exceptShopStr.split(",");
			for(j=0;j<exceptShop.length;j++){
				allUnLicensed.push({skuId:skuId,commercialId:exceptShop[j]});
			}
		}
		allSkuData.push({skuId:skuId,price:each.priceHidden});
	}
	
	//获取商户数据
	for(var i=0;i<allShopGrid.length;i++){
		allShopData.push({commercialId:allShopGrid[i].commercialId});
	}
	
	$("#allSkuData").val(JSON.stringify(allSkuData));
	$("#allShopData").val(JSON.stringify(allShopData));
	$("#allUnLicensed").val(JSON.stringify(allUnLicensed));
	return true;
}

/**
 * 重新刷新类型
 * */
function doResetType(){
	 $.ajax({
          url: 'scm/bumen/real/type',
          type: "post",
          async: false,
          data: {},
          success: function (res) {
             if(res.length>0){
            	 var taget = $("#bumenCateId"),isChange = true,
            	 oldVal = taget.parent().find("em").html(),
            	 realList='<li>请选择部门类别</li>',
            	 realOpt='';
            	 
            	 for(var i=0;i<res.length;i++){
            		 if(isChange&&res[i].bumenCateName==oldVal) isChange = false;
            		 realList+='<li>'+res[i].bumenCateName+'</li>';
            		 realOpt+='<option '+(res[i].bumenCateName==oldVal?"selected":"")+' value="'+res[i].id+'">'+res[i].bumenCateName+'</option>';
            	 }
            	 
            	taget.html("").append('<option '+(isChange?"selected":"")+' value="">请选择部门类别</option>'+realOpt);
             	taget.parent().find("ul").html("").append(realList);
             	if(isChange) taget.parent().find("em").html("请选择部门类别");
             }
          },
          error: function () {
             //DO ...
          }
      });
}

//商品过滤
function filterSku() {
    var skuTypeName = $('#skuTypeName').find('option:selected').text();
    if($('#skuTypeName').val() === '') skuTypeName = '';
    var conditions1 = {skuCode: $('#skuCodeOrName').val(),skuTypeName: skuTypeName};
    var conditions2 = {skuName: $('#skuCodeOrName').val(),skuTypeName: skuTypeName}
    var rowIds1 = filterGridRowIds('grid', conditions1);
    var rowIds2 = filterGridRowIds('grid', conditions2);
    Array.prototype.push.apply(rowIds1, rowIds2);
    filterGridRows('grid', rowIds1);
}

$.afterAdd = function(){
    //初始化本行非授权商户
    var thisInput = $("input[name='multiSelectIpt']");
    thisInput.selectMulti();//完成首次加载数据后初始化非授权商户下拉列表
    thisInput.parents("td").css({"overflow" : "visible"}).removeAttr("title");
    $.filterGrid.initSkuTypeNames();
};