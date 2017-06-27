
$.refreshDishType = function(ctxPath, codeOrName, dishId) {
	var args = {};
	args.url = ctx2Path + "&act=queryDishTypes";
	args.postData = {codeOrName:codeOrName, dishId:dishId};
	args.callback = "$.reloadDishType";
	$.submitWithAjax(args);
};	

$.reloadDishType = function(args) {
	var topDishTypes = args.result,
		topDishTypeSize = topDishTypes.length;
	$(".tree").html("");
	for (var i = 0; i < topDishTypeSize; i++) {
		var topDishType = topDishTypes[i],
			middleDishTypes = topDishType.middleDishBrandTypes,
			middleDishTypeSize = middleDishTypes.length;
		if(topDishType.isCure==1){
			continue;
		}


        // var html = "<li class='shutDown";
        var html = "<li class=' shutDown";
        if (topDishType.isChecked || topDishTypeSize == 1) {
			html += " open";
        }
        var isTopCheck = "";
        var inputChecked = "";
        if(topDishType.isChecked){
            isTopCheck = "checkbox-check";
            inputChecked = "checked";
        }else{
            isTopCheck = "";
            inputChecked = "";

        }
        // var html = ""
        html += "'>";
		html += "<label class='checkbox "+isTopCheck+"'><span></span><input type='checkbox' "+inputChecked+"  name='dishTypeId' data-value='"+topDishType.id+"' ><em>" + topDishType.name + "/" + topDishType.typeCode ;
		if (topDishType.enabledFlag == 2) {
			html += "<span class='red'>（已停用）</span>";
        }
        html += "</em></label>";
		html += "<ul class='highlighted-list' style='margin-left: 20px;'>";
		for (var j = 0; j < middleDishTypeSize; j++) {
			var middleDishType = middleDishTypes[j],
				labelCheckedClass = "",
				inputChecked = "",
				stoped = "",
				disabled = "";
			if (middleDishType.isChecked) {
				labelCheckedClass = "checkbox-check";
				inputChecked = "checked";
			}
			if (middleDishType.enabledFlag == 2) {
				labelCheckedClass = "";
				inputChecked = "";
				stoped = "<span class='red'>（已停用）</span>";
				disabled="disabled";
			}
			html += "<li title="+ middleDishType.name+"/"+middleDishType.typeCode+";><label class='checkbox "+labelCheckedClass+"' for='types-"+i+'_'+j+"'><span></span>";
			html += "<input type='checkbox'  name='dishTypeId' id='types-"+i+'_'+j+"' data-value='"+middleDishType.id+"' "+inputChecked+" "+disabled+">";
			html += middleDishType.name+"/"+middleDishType.typeCode;
			html += "</label>"+stoped+"</li>";
		}
		html += "</ul>";
		html += "<span class='icon-folding'></span>";
		html += "</li>";
		$(".tree").append(html);
	}
};

function validateData(obj,msg){
	if($(obj).parent(".checkDiv").length>0){
		$(obj).parent(".checkDiv").find("span").remove();
		$(obj).unwrap();
	}
	
	var productCode = obj.value;
	if(isEmpty(productCode)){
		$(obj).wrap("<div border='red' class='checkDiv'></div>").append("<span>"+msg+"</span>");
		$(obj).val("");
		return false;
	}else{
		if(!isNumOrWord(obj)){
			$(obj).wrap("<div border='red' class='checkDiv'></div>").after("<span color='red'>"+msg+"</span>");
			$(obj).val("");
			return false;
		}
		
		return true;
	}
}



/**
 *检查输入的商品编码是否唯一
 * @param inputId
 */
function validateProducCode(obj){
	var productCode = obj.value;
	if(!validateData(obj,'商品编码只能为数字字母且不能为空')){
		return;
	}
	
	$.ajax({ //第二步，执行保存套餐信息
		type:"GET",
		url:rootPath + "/dish/setMeal/validateProducCode",
		data:{productCode:productCode},
		dataType:"json",
		contentType: "application/json", 
		async:false,
		cache:false,
		success:function(data){
			if(data.count>0){
				$(obj).wrap("<div border='red' class='checkDiv'></div>").append(data.msg);
//				$(obj).val("");
				return false;
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
	        alert(XMLHttpRequest.status);
	    },
	});
	return true;
}


/**
 * 大于零的整数
 * @param obj
 * @returns {Boolean}
 */
function ischeckNumGtZero(obj) {
	if (obj) {
		if (!isNaN(obj.value)) {
			if(obj.value.length>1){
				var fisrtWord = obj.value.substring(0,1);
				var secondtWord = obj.value.substring(1,2);
				if(fisrtWord==0&&secondtWord!="."){
					obj.value=0;
					Message.alert({title:"提示",describe:"只能输入大于零的整数"},Message.display);
					return false;
				}
			}
			return true;
		} else {
			alert("只能输入大于零的整数");
			obj.value=0;
			return false;
		}
	} 
}

function isNumTwoPoint(obj,temp){
	var regu =/(^((?:-?\d{1,2})(?:\.\d{1,2})?)$)/;
	if(!regu.test(obj.value)){
		Message.alert({'title':'提示','describe':'只能输入数字,并且只能是两位整数，两位小数 '});
		var nums = obj.value.split("\.");
		if(!isNaN(nums[0])){
			obj.value=nums[0].substring(0,2);
		}else{
			obj.value=temp;
		}
		return false;
	}
	return true;
}



function ischeckNum(obj) 
{ 
	var patrn=/^([1-9]\d*|0)(\.\d*[1-9])?$/; 
	if (!patrn.exec(obj.value)) 
	{
		return false;
	} else {
		return true;
	}
}  

/**
 * 大于等于0的数，两位小数
 * @param obj
 * @returns {Boolean}
 */
function eqZoreTwoPointOrInt(obj) 
{ 
	var patrn = /(^\d{1,6}$)|(^\d{1,6}\.\d{1,2}$)/;
 
	if (!patrn.exec(obj.value)) 
	{
		return false;
	} else {
		return true;
	}
}  

/**
 * 大于0的数，最大能写两位小数
 * @returns {Boolean}
 */
function gtZoreTwoPoint(obj){
	var re = /(^\d{1,6}$)|(^\d{1,6}\.\d{1,2}$)/;
	 if (!re.test(obj.value)){ 
	        return false; 
	 } 
	 return true;
}

/**
 * 0--100 的数  并且只能是两位小数
 * @param obj
 * @returns
 */
function twoPointZeroToHundred(obj){
	var regu = /^\d+(\.\d{2})?$/;
	var flag = false;
	if(ischeckNum(obj)){
		flag=true;
		return;
	}
	if(!flag){
		if(!((obj.value>=0.01)&&(obj.value<100))){
			Message.alert({'title':'提示','describe':'只能输入0~100之间的数字,并且只能是两位小数 '});
			return false;
		}
	}
	if(!regu.test(obj.value)){
		Message.alert({'title':'提示','describe':'只能输入0~100之间的数字,并且只能是两位小数 '});
		obj.value="";
		return false;
	}
	
}

/**
 * 正整数，正数，负数，小数
 * @param obj
 * @returns {Boolean}
 */
function checkNumAll(obj) {
	var re = /^-?[1-9]*(\.\d*)?$|^-?0(\.\d*)?$/;
    if (!re.test(obj.value))
   {
    	Message.alert({'title':'提示','describe':'能输入正整数，正数，负数，小数'});
        obj.value="";
       return false;
    }
	return true;
} 


/**
 * 只能 是数字 字母
 */
function isNumOrWord(obj) {
	var reg = /^[0-9a-zA-Z]*$/g;
	if(!reg.test(obj.value)){
		return false;
	}
	return true;
}

/**
 * 不否是整数
 * @param str
 * @returns
 */
function isInteger(obj) {
	var regu = /^[-]{0,1}[0-9]{1,}$/;
	if(!regu.test(obj.value)){
		obj.value="";
		return false;
	}
	if(obj.value.length>1){
		var startNum = obj.value.substring(1, 0);
		if(startNum==0){
			obj.value="";
			return false;
	   }
	}
	return true;

}

/**
 * 正整数，正数，负数，小数
 * @param obj
 * @returns {Boolean}
 */
function checkNumAll(obj) {
	var re = /^-?[1-9]*(\.\d*)?$|^-?0(\.\d*)?$/;
    if (!re.test(obj.value))
   {
       obj.value=0;
       return false;
    }
	return true;
} 
