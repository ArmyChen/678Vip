$(function () {
    //上一步  按钮切换
    $("#btnPrev").on("click", function () {
        step(-1);
    });
    //下一步 按钮切换
    $("#btnNext").on("click",function(){
    	//商品套餐 判断树的选择
		 var dishTypeId = "";    
		  $('input[name="dishTypeId"]:checked').each(function(){    
			  dishTypeId = $(this).attr("data-value");    
		  });    
		if(isEmpty(dishTypeId)){
			Message.alert({title:"提示",describe:"中类类型为必选项"},Message.display);
			return;
		}
    	if ($("#setMealForm").length){
    		if (!$("#setMealForm").valid()) {
    			return;
    		}
    		var minNum = $("#minNum").val();
    		var maxNum = $("#maxNum").val();
    		if(!isEmpty(minNum) && !isEmpty(maxNum)&&parseFloat(maxNum)<parseFloat(minNum)){
    			Message.alert({title:"提示",describe:"最小人数必须小于等于最大人数"},Message.display);
    			return;
    		}
    		
    	}
    	
    	if ($("#stepOneForm").length) {
    		// if (!$("#stepOneForm").valid()) {
    		// 	return;
    		// }
    		

    		var dishBrandVo = {},
    			attributeObjs = [];
    		dishBrandVo.id = $("#dishId").val();
    		dishBrandVo.name = $("#name").val();
    		$(".property-attribute").each(function() {
    			if ($(this).val() == "/") {
    				return;
    			}
    			attributeObjs.push({
    				id:$(this).val(),
    				propertyKindId:$(this).attr("data-kind-value"),
    				propertyTypeId:$(this).attr("data-type-value")
    			});
    		});
    		dishBrandVo.attributes = attributeObjs;
            step(1);
    		// $.ajax({
    		// 	type:"POST",
    		// 	url:rootPath + "/dish/manage/checkDishBrandSkuKey",
    		// 	data:JSON.stringify(dishBrandVo),
    		// 	dataType:"json",
    		// 	contentType: "application/json",
    		// 	async:false,
    		// 	cache:false,
    		// 	success:function(result){
             //        // step(1);
    		// 		if (result) {
    		// 			Message.alert({title:"提示",describe:"已存在该规格的商品"},Message.display);
            //
    		// 		} else {
    		// 			step(1);
    		// 		}
    		// 	},
    		// 	error: function(XMLHttpRequest, textStatus, errorThrown) {
    		//         alert(XMLHttpRequest.status);
    		//     },
    		// });
    	} else {
			var flag = true;//验证返回
			// if ($('#js_setepOneForm').length) {
			// 	flag=$('#js_setepOneForm').valid();
			// }
			var currentDiv = $("#scmBreadCrumbs > li.current").attr("data-show");
			////只有验证成功了才能进入下一步
			if (flag) {
				step(1);
			}
		}
    });
});

/*
 * 页面切换
 * @i        {number}    页签索引
 */
function step(i) {
    var breadCrumbsObjs = $("#scmBreadCrumbs > li");
    var index = $("#scmBreadCrumbs > li.current").index();
    var len = breadCrumbsObjs.length;
    var showObj = $("#" + breadCrumbsObjs.eq(index + i).attr("data-show"));
    if (index + i == 0) {
//    	alert('index-i=='+index);
        $("#btnPrev,#btnSave").hide();
        $("#btnNext,#btncancle").show();
    } else if (index + i == len - 1) {
//    	alert('index+i=='+index);
        $("#btnNext").hide();
        $("#btnPrev,#btncancle,#btnSave").show();
    } else {
        $("#btnSave").hide();
        $("#btnPrev,#btnNext,#btncancle").show();
    }
    breadCrumbsObjs.eq(index + i).addClass("current").siblings().removeClass("current");
    showObj.show().siblings(".panel-group").hide();
}

function isEmpty(str){
	if(str==null || $.trim(str)=="" || str=="undefined"){
		return true;
	}
}