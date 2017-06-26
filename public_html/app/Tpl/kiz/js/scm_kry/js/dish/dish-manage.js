$.addCurrencySymbol = function(data) {
	var _data = data + "", symbolData;
	if (_data.split("")[0] === "-") {
		symbolData = "-$" + _data.substr(1);
	} else {
		symbolData = "$" + _data;
	}
	if (symbolData == "$") {
		symbolData = "$0";
	}
	return symbolData;
};

$.initDishAttribute = function() {
	var dishId = $("#dishId").val();
	var args = {};
	args.url = queryDishAndAttributeUrl;
	args.postData = {
		dishId : dishId
	};
	args.callback = "$.reloadDishAttribute";
	$.submitWithAjax(args);
};

$.initRelevanceCount = function() {
	var dishId = $("#dishId").val();
	var args = {};
	args.url = queryRevelanceSettingUrl;
	args.postData = {
		dishId : dishId
	};
	args.callback = "$.reloadRevelanceSetting";
	$.submitWithAjax(args);};

$.initMedia = function() {
	var fileName = $.trim($("#trigger-file-2").val());
	if (fileName != null && fileName != "") {
		$("#labelText").show();
	} else {
		$("#labelText").hide();
	}
};

var attributeArray = [];
$.reloadDishAttribute = function(args) {
	var resutl = args.result, dishAttributes = resutl.dishAndAttributes, dishAttributeSize = dishAttributes.length, dishPropertyTypes = resutl.dishPropertyTypes, dishPropertyTypeSize = dishPropertyTypes.length;

	attributeArray = dishPropertyTypes;
	if (dishPropertyTypes == 0) {
		return;
	}
	if (dishAttributeSize > 0) {
		var selectedType = {};
		for (var i = 0; i < dishAttributeSize; i++) {
			var dishAttribute = dishAttributes[i];
			for (var j = 0; j < dishPropertyTypeSize; j++) {
				var dishPropertyType = dishPropertyTypes[j];
				if (dishPropertyType.id == 1) {
					selectedType = dishPropertyType;
					break;
				}
			}
			var html = '<div class="additem">';
			html += '<input type="text" class="form-control select-group js-attribute-type" value="'
					+ selectedType.name + '" disabled>';
			html += "<select class='property-attribute' data-type-value='"
					+ selectedType.name + "' data-kind-value='4'>";
			html += "<option>/</option>";
			var dishProperties = selectedType.dishProperties, dishPropertySize = dishProperties.length;
			for (var k = 0; k < dishPropertySize; k++) {
				var dishProperty = dishProperties[k];
				if (dishProperty.id == dishAttribute.propertyId) {
					html += "<option value='" + dishProperty.id + "' selected>"
							+ dishProperty.name + "</option>";
				} else {
					html += "<option value='" + dishProperty.id + "'>"
							+ dishProperty.name + "</option>";
				}
			}
			html += "</select>";
			html += '<a href="#" class="close-1 ml20" style="display:none;">移除此项</a>';
			html += '</div>';
			$("#goodsAttribute").append(html);
			setGoodsAttributeHeight(false);
		}
	} else {
		var firstType = dishPropertyTypes[0];
		var html = '<div class="additem">';
		html += '<input type="text" class="form-control select-group js-attribute-type" value="'
				+ firstType.name + '" disabled>';
		html += "<select class='property-attribute' data-type-value='"
				+ firstType.name + "' data-kind-value='4'>";
		html += "<option>/</option>";
		var dishProperties = firstType.dishProperties, dishPropertySize = dishProperties.length;
		for (var k = 0; k < dishPropertySize; k++) {
			var dishProperty = dishProperties[k];
			html += "<option value='" + dishProperty.id + "'>"
					+ dishProperty.name + "</option>";
		}
		html += "</select>";
		html += '<a href="#" class="close-1 ml20" style="display:none;">移除此项</a>';
		html += '</div>';
		$("#goodsAttribute").append(html);
		setGoodsAttributeHeight(false);
	}
	bkeruyun.selectControl($("#goodsAttribute select"));
	bkeruyun.showMenu($("#goodsAttribute .additem"),
			$("#goodsAttribute .additem .close-1"));

	$("#goodsAttribute property-attribute").parent().find(
			".select-control > em").text($("property-attribute").val());
};

function attrDetection(array) {
	var newArray = [];
	var existingObjs = $("#goodsAttribute .js-attribute-type");
	for (var i = 0, len = array.length; i < len; i++) {
		var flag = false;
		for (var j = 0, len1 = existingObjs.length; j < len1; j++) {
			var existingAttr = existingObjs.eq(j).val();
			if (array[i].name === existingAttr) {
				flag = true;
				break;
			}
		}
		if (!flag) {
			newArray.push(array[i]);
		}
	}
	return newArray;
}
function setGoodsAttributeHeight(bol) {
	var $bol = bol;
    var len = $("#goodsAttribute .additem").length;
    var h = $("#goodsAttribute .additem").height();
    $("#goodsAttributeTd").css("height",len*(h+20)+"px");
    $("#goodsAttribute").css("height",len*(h+20)+"px");
}

function addAttribute(newType){
	var html = '<div class="additem">';
	html += '<input type="text" class="form-control select-group js-attribute-type" value="'
			+ newType.name + '" disabled>';
	html += "<select class='property-attribute'  data-type-value='"
			+ newType.name + "' data-kind-value='4'>";
	html += "<option>/</option>";
	var dishProperties = newType.dishProperties, dishPropertySize = dishProperties.length;
	for (var k = 0; k < dishPropertySize; k++) {
		var dishProperty = dishProperties[k];
		html += "<option value='" + dishProperty.id + "'>" + dishProperty.name
				+ "</option>";
	}
	html += "</select>";
	html += '<a href="#" class="close-1 ml20" style="display:none;">移除此项</a>';
	html += '</div>';
	$("#goodsAttribute").append(html);
	bkeruyun.selectControl($("#goodsAttribute select"));
	bkeruyun.showMenu($("#goodsAttribute .additem"),
			$("#goodsAttribute .additem .close-1"));
}
var cookingWayTypeSize = 0;
$.reloadRevelanceSetting = function(args) {
	var countMap = args.result, labelCount = countMap.labelCount, memoCount = countMap.memoCount, condimentCount = countMap.condimentCount, cookingWayTypesAndCount = countMap.cookingWayTypesAndCount;

	cookingWayTypeSize = cookingWayTypesAndCount.length;

	$("#labelCount").html("");
	$("#labelCount").html(
			"(" + labelCount.checkedCount + "/" + labelCount.count + ")");
	var labelHtml = "", labels = labelCount.dishProperties, labelSize = labels.length, labelCheckedAllClass = "";
	labelHtml += "<div class='goodsConfig-con' id='goodsLabelCon'>";
	if (labelSize > 0) {
		if (labelCount.isCheckedAll) {
			labelCheckedAllClass = "checkbox-check";
		}
		labelHtml += "<h3 class='goodsListAll'><label class='checkbox "
				+ labelCheckedAllClass
				+ "'><span></span><input type='checkbox' name='goodsLabelsAll' id='goodsLabelsAll' ";
		labelHtml += "data-all='goodsLabels'>全部</label></h3>";
		labelHtml += "<ul class='goodsList defaultGoodsList'>";
		for (var i = 0; i < labelSize; i++) {
			var label = labels[i], labelCheckedClass = "", inputChecked = "", stoped = "", disabled = "";
			if (label.isChecked) {
				labelCheckedClass = "checkbox-check";
				inputChecked = "checked";
			}
			if (label.enabledFlag == 2) {
				labelCheckedClass = "";
				inputChecked = "";
				stoped = "<span class='red'>（已停用）</span>";
				disabled = "disabled";
			}
			labelHtml += "<li><label class='checkbox " + labelCheckedClass
					+ "'><span></span>";
			labelHtml += "<input type='checkbox' name='goodsLabels' id='goodsLabels-"
					+ i + "' data-checked-all='goodsLabelsAll' ";
			labelHtml += "data-value='" + label.id + "' data-kind-value='2' "
					+ inputChecked + " " + disabled + ">" + label.name
					+ "</label>" + stoped + "</li>";
		}
		labelHtml += "</ul>";
	}
	labelHtml += "</div>";
	$(".js-revelance-setting").append(labelHtml);

	$("#memoCount").html("");
	$("#memoCount").html(
			"(" + memoCount.checkedCount + "/" + memoCount.count + ")");
	var memoHtml = "", memos = memoCount.dishProperties, memoSize = memos.length, labelCheckedAllClass = "";
	memoHtml += "<div class='goodsConfig-con' id='goodsNoteCon' style='display:none;'>";
	if (memoSize > 0) {
		if (memoCount.isCheckedAll) {
			labelCheckedAllClass = "checkbox-check";
		}
		memoHtml += "<h3 class='goodsListAll'><label class='checkbox "
				+ labelCheckedAllClass
				+ "'><span></span><input type='checkbox' name='goodsNotesAll' id='goodsNotesAll' ";
		memoHtml += "data-all='goodsNotes'>全部</label></h3>";
		memoHtml += "<ul class='goodsList defaultGoodsList'>";
		for (var i = 0; i < memoSize; i++) {
			var memo = memos[i], labelCheckedClass = "", inputChecked = "", stoped = "", disabled = "";
			if (memo.isChecked) {
				labelCheckedClass = "checkbox-check";
				inputChecked = "checked";
			}
			if (memo.enabledFlag == 2) {
				labelCheckedClass = "";
				inputChecked = "";
				stoped = "<span class='red'>（已停用）</span>";
				disabled = "disabled";
			}
			memoHtml += "<li><label class='checkbox " + labelCheckedClass
					+ "'><span></span>";
			memoHtml += "<input type='checkbox' name='goodsNotes' id='goodsNotes-"
					+ i + "' data-checked-all='goodsNotesAll' ";
			memoHtml += "data-value='" + memo.id + "' data-kind-value='3' "
					+ inputChecked + " " + disabled + ">" + memo.name
					+ "</label>" + stoped + "</li>";
		}
		memoHtml += "</ul>";
	}
	memoHtml += "</div>";
	$(".js-revelance-setting").append(memoHtml);

	$("#condimentCount").html("");
	$("#condimentCount").html(
			"(" + condimentCount.checkedCount + "/" + condimentCount.count
					+ ")");
	var condimentHtml = "", condiments = condimentCount.condiments, condimentSize = condiments.length, labelCheckedAllClass = "";
	condimentHtml += "<div class='goodsConfig-con' id='goodsBurdeningCon' style='display:none;'>";
	if (condimentSize > 0) {
		if (condimentCount.isCheckedAll) {
			labelCheckedAllClass = "checkbox-check";
		}
		condimentHtml += "<h3 class='goodsListAll'><label class='checkbox "
				+ labelCheckedAllClass
				+ "'><span></span><input type='checkbox' name='goodsBurdeningsAll' id='goodsBurdeningsAll' ";
		condimentHtml += "data-all='goodsBurdenings'>全部</label></h3>";
		condimentHtml += "<ul class='goodsList defaultGoodsList'>";
		for (var i = 0; i < condimentSize; i++) {
			var condiment = condiments[i], labelCheckedClass = "", inputChecked = "", stoped = "", disabled = "";
			if (condiment.isChecked) {
				labelCheckedClass = "checkbox-check";
				inputChecked = "checked";
			}
			if (condiment.enabledFlag != 1) {
				labelCheckedClass = "";
				inputChecked = "";
				stoped = "<span class='red'>（已停用）</span>";
				disabled = "disabled";
			}
			condimentHtml += "<li><label class='checkbox " + labelCheckedClass
					+ "'><span></span>";
			condimentHtml += "<input type='checkbox' name='goodsBurdenings' id='goodsBurdenings-"
					+ i + "' data-checked-all='goodsBurdeningsAll' ";
			condimentHtml += "data-value='" + condiment.id + "' "
					+ inputChecked + " " + disabled + ">" + condiment.name + "($" + condiment.marketPrice +")"
					+ "</label>" + stoped + "</li>";
		}
		condimentHtml += "</ul>";
	}
	condimentHtml += "</div>";
	$(".js-revelance-setting").append(condimentHtml);

	var cookingWayHtml = "";
	cookingWayHtml += "<div class='goodsConfig-con' id='goodsPracticeCon' style='display:none;'>";
	if (cookingWayTypeSize > 0) {
		cookingWayHtml += "<div id='category-tab' style='width:838px;height:35px;'>";
		cookingWayHtml += "<ul class='tab-white-gray'>";
		for (var i = 0; i < cookingWayTypeSize; i++) {
			var cookingWayType = cookingWayTypesAndCount[i];
			cookingWayHtml += "<li ";
			if (i == 0) {
				cookingWayHtml += "class='current' ";
			}
			if (cookingWayType.enabledFlag == 2) {
				labelCheckedClass = "";
				inputChecked = "";
				cookingWayHtml += "data-status='disabled' ";
			}
			cookingWayHtml += "data-show='goodsPracticeCon-" + i + "' title='"
					+ cookingWayType.name + "'><em>" + cookingWayType.name
					+ "</em>&nbsp;";
			cookingWayHtml += "<span>(" + cookingWayType.checkedCount + "/"
					+ cookingWayType.count + ")</span></li>";
		}
		cookingWayHtml += "</ul>";
		cookingWayHtml += "</div>";
		for (var i = 0; i < cookingWayTypeSize; i++) {
			var cookingWayType = cookingWayTypesAndCount[i], cookingWays = cookingWayType.dishProperties, cookingWaySize = cookingWays.length;
			if (cookingWaySize > 0) {
				var labelCheckedAllClass = "", disabledAll = "";
				cookingWayHtml += "<div class='goodsPracticeCon' id='goodsPracticeCon-"
						+ i + "'";
				if (i != 0) {
					cookingWayHtml += " style='display:none;'";
				}
				cookingWayHtml += ">";
				if (cookingWayType.isCheckedAll) {
					labelCheckedAllClass = "checkbox-check";
				}
				if (cookingWayType.enabledFlag == 2) {
					labelCheckedAllClass = "checkbox-disable";
					disabledAll = "disabled";
				}
				cookingWayHtml += "<h3 class='goodsListAll'><label class='checkbox "
						+ labelCheckedAllClass + "'><span></span>";
				cookingWayHtml += "<input type='checkbox' name='goodsPractice"
						+ i + "All' id='goodsPractice" + i
						+ "All' data-all='goodsPractice" + i + "' "
						+ disabledAll + ">全部</label></h3>";
				cookingWayHtml += "<ul class='goodsList defaultGoodsList'>";
				for (var j = 0; j < cookingWaySize; j++) {
					var cookingWay = cookingWays[j], labelCheckedClass = "", inputChecked = "", defaultClass = "", stopedOrDefault = "<span class='setDefaultBtn'>(默认)</span>", disabled = "";
					if (cookingWay.isChecked) {
						labelCheckedClass = "checkbox-check";
						inputChecked = "checked";
					}
					if (cookingWay.enabledFlag == 2) {
						labelCheckedClass = "checkbox-disable";
						inputChecked = "";
						stopedOrDefault = "<span class='red'>（已停用）</span>";
						disabled = "disabled";
					}
					if (cookingWay.isDefault == 1) {
						defaultClass = "class='default'";
					}
					cookingWayHtml += "<li " + defaultClass
							+ "><label class='checkbox " + labelCheckedClass
							+ "'><span></span>";
					cookingWayHtml += "<input type='checkbox' name='goodsPractice"
							+ i
							+ "' id='goodsPractice1-"
							+ i
							+ "' data-checked-all='goodsPractice"
							+ i
							+ "All' data-value='" + cookingWay.id + "' ";
					cookingWayHtml += "data-type-value='" + cookingWay.name
							+ "' data-type-reprice='" + cookingWay.reprice
							+ "' data-kind-value='"+cookingWay.kindId+"' data-is-default='"
							+ cookingWay.isDefault + "' " + inputChecked + " "
							+ disabled + ">";
					cookingWayHtml += "<i>"+cookingWay.name+"</i>("
							+ $.addCurrencySymbol(cookingWay.reprice) + ")"
							+ "</label>" + stopedOrDefault + "</li>";
				}
				cookingWayHtml += "</ul>";
				cookingWayHtml += "</div>";
			} else {
				cookingWayHtml += "<div class='goodsPracticeCon' id='goodsPracticeCon-"
						+ i + "'";
				if (i != 0) {
					cookingWayHtml += " style='display:none;'";
				}
				cookingWayHtml += "></div>";
			}
		}
	}
	cookingWayHtml += "</div>";
	$(".js-revelance-setting").append(cookingWayHtml);
	bkeruyun.showMenu($(".defaultGoodsList > li"), ".setDefaultBtn");
};

function saveDishInfo() {
	// if (!$("#stepOneForm").valid()) {
	// 	window._isAlreadySaving = false;return;
	// }
	var dishVo = {};
	dishVo.id = $("#dishId").val();
	var dishTypeId = "";
	$(":checkbox[name='dishTypeId']:checked").each(function() {
		dishTypeId = $(this).attr("data-value");
	});
	if (!dishTypeId) {
		Message.alert({
			title : "提示",
			describe : "请选择商品类别"
		}, Message.display);
		window._isAlreadySaving = false;return;
	}
	dishVo.dishTypeId = dishTypeId;
	var dishCode = $("#dishCode").val();
	// if (!dishCode) {
	// 	Message.alert({
	// 		title : "提示",
	// 		describe : "商品编码不能为空"
	// 	}, Message.display);
	// 	window._isAlreadySaving = false;return;
	// }
	dishVo.dishCode = dishCode;
    var name = $("#name").val();
    var sname = $("#sname").val();
	if (!name) {
		Message.alert({
			title : "提示",
			describe : "商品名称不能为空"
		}, Message.display);
		window._isAlreadySaving = false;return;
	}
    dishVo.name = name;
    dishVo.sname = sname;
	dishVo.aliasName = $("#aliasName").val();
	dishVo.shortName = $("#shortName").val();
	dishVo.aliasShortName = $("#aliasShortName").val();
	dishVo.barcode = $("#barcode").val();
	dishVo.dishNameIndex = $("#dishNameIndex").val();
	var marketPrice = $("#marketPrice").val();
	if (!marketPrice) {
		Message.alert({
			title : "提示",
			describe : "商品定价不能为空"
		}, Message.display);
		window._isAlreadySaving = false;return;
	}
	dishVo.marketPrice = marketPrice;
	var unitId = $("#unitId").val();
	if (!unitId) {
		Message.alert({
			title : "提示",
			describe : "销售单位不能为空"
		}, Message.display);
		window._isAlreadySaving = false;return;
	}
	dishVo.unitId = unitId;
	dishVo.wmType = $("#wmType").val();
	var saleType = $(":checkbox[id='saleType']:checked").attr("data-value");
	if (!saleType) {
		saleType = 2;
	}
	dishVo.saleType = saleType;
	//当为称重商品时,增加换算参数
	if(saleType ==1){
		dishVo.weight = $('#weight').val();
		if(!checkUnitWeight()){
			Message.alert({
				title : "提示",
				describe : "请正确填写称重商品单位换算"
			}, function(){
				window._isAlreadySaving = false;
			});
			return;
		}
	}

	var attributeObjs = [];
	$(".property-attribute").each(function() {
		if ($(this).val() == "/") {
			return;
		}
		attributeObjs.push({
            id : $(this).val(),
            propertyKindId : $(this).attr("data-kind-value"),
			propertyTypeId : $(this).attr("data-type-value")
		});
	});
	dishVo.attributes = attributeObjs;

	dishVo.sort = $("#sort").val();
	dishVo.dishIncreaseUnit = $("#dishIncreaseUnit").val();
	var isSingle = $(":checkbox[id='isSingle']:checked").attr("data-value");
	if (!isSingle) {
		isSingle = 2;
	}
	dishVo.isSingle = isSingle;
	var isDiscountAll = $(":checkbox[name='isDiscountAll']:checked").attr(
			"data-value");
	if (!isDiscountAll) {
		isDiscountAll = 2;
	}
	dishVo.isDiscountAll = isDiscountAll;
	var isSendOutside = $(":checkbox[name='isSendOutside']:checked").attr(
			"data-value");
	if (!isSendOutside) {
		isSendOutside = 2;
	}

    var isChangePrice = $(":checkbox[name='isChangePrice']:checked").attr("data-value");
    if (!isChangePrice||isChangePrice==undefined) {
        isChangePrice = 2;
    }
    dishVo.isChangePrice = isChangePrice;
    var isHalf = $(":checkbox[name='isHalf']:checked").attr("data-value");
    if (!isHalf||isHalf==undefined) {
        isHalf = 2;
    }
    dishVo.isHalf = isHalf;
    dishVo.isSendOutside = isSendOutside;

	var isOrder = $(":checkbox[name='isOrder']:checked").attr("data-value");
	if (!isOrder) {
		isOrder = 2;
	}
	dishVo.isOrder = isOrder;
	dishVo.stepNum = $("#stepNum").val();
	dishVo.videoUrl = $("#videoUrl").val();
    dishVo.dishDesc = $("#dishDesc").val();
    dishVo.chuan = $("#chuan").val();

	var labelObjs = [];
	$(":checkbox[name='goodsLabels']:checked").each(function() {
		labelObjs.push({
			id : $(this).attr("data-value"),
			propertyKindId : $(this).attr("data-kind-value")
		});
	});
	dishVo.labels = labelObjs;

	var memoObjs = [];
	$(":checkbox[name='goodsNotes']:checked").each(function() {
		memoObjs.push({
			id : $(this).attr("data-value"),
			propertyKindId : $(this).attr("data-kind-value")
		});
	});
	dishVo.memos = memoObjs;

	var condimentObjs = [];
	$(":checkbox[name='goodsBurdenings']:checked").each(function() {
		condimentObjs.push({
			id : $(this).attr("data-value")
		});
	});
	dishVo.condiments = condimentObjs;

	var cookingWayObjs = [];
	for (var i = 0; i < cookingWayTypeSize; i++) {
		$(":checkbox[name='goodsPractice" + i + "']:checked").each(function() {
			cookingWayObjs.push({
				id : $(this).attr("data-value"),
				propertyKindId : $(this).attr("data-kind-value"),
                propertyTypeId : $(this).attr("data-type-value"),
                propertyTypeRePrice : $(this).attr("data-type-reprice"),
				isDefault : $(this).attr("data-is-default")
			});
		});
	}
	dishVo.cookingWays = cookingWayObjs;

	var sampleObjs = [];
	$(":checkbox[name='sample']:checked").each(function() {
		sampleObjs.push({
			id : $(this).val()
		});
	});
	dishVo.templates = sampleObjs;
	// image part
	var $imageUrl =$('#imageUrl');
	dishVo.imageUrl = $imageUrl.val();
	if(dishVo.imageUrl){
		dishVo.imageSize = $imageUrl.attr('data-imageSize');
		dishVo.imageSuffixes = $imageUrl.attr('data-imageSuffixes');
		dishVo.imageName = $imageUrl.attr('data-imageName');
	}
	var deleteImage = 0;
	if ('yes' == $imageUrl.attr('data-uploaded')) {
		deleteImage = 1;
	}
	dishVo.deleteImage = deleteImage;
//	zww
	dishVo.richDesc = UE.getEditor('myEditor').getContent();
//
	//商品和餐盒数量设置
	var dishQty = $("#dishQty").val();
	var boxQty = $("#boxQty").val();
	if(dishQty != null && dishQty != ""){
	}else{
		dishQty = "1";
	}
	if(boxQty != null && boxQty != ""){
	}else{
		boxQty = "1";
	}
	dishVo.dishQty = dishQty;
	dishVo.boxQty = boxQty;

	bkeruyun.showLayer();
	$.ajax({
		type : "POST",
		url : saveOrUpdateUrl,
        // data :  {"posts" : "1231"},
        data : JSON.stringify(dishVo),
		dataType : "json",
		contentType : "application/json",
		async : false,
		cache : false,
		success : function(result) {
            Message.alert({
                title : "提示",
                describe : result.message
            }, function(){
                if (result.success) {
                    location.href = dishPath + "&act=dish_list";
                }else{
                    bkeruyun.hideLoading();
                }
            });


		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
		//	alert(XMLHttpRequest.status);
			bkeruyun.promptMessage('发生系统错误，请联系管理员！');
			window._isAlreadySaving = false;
		},
		complete: function (jqXHR, textStatus) {
		}
	});

}
