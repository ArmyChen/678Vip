
$(function(){

	/**
	 * 点击“全部撤销”
	 */
	$("#undo-all").on("click",function(){

		$($('.multi-select em')).each(function(){
			$(this).text('');
		});

		$($('.multi-select input[type=hidden]')).each(function(){
			$(this).val(''); // 顺便清空input type=hidden id=hiddenCommercialIds的值
		});

		clearInputsValueAndCheckboxStatus('input[name=commercialIds]', '#commercialId-all', true);

	});



	// 交互
	$(".multi-select > .select-control").on("click",function(e){
		e.stopPropagation();
		var showList = $(this).next(".multi-select-con");
		if(showList.is(":hidden")){
			$(".multi-select-con").hide();
			$(".select-control").removeClass("select-control-arrowtop");
			showList.show();
			$(this).addClass("select-control-arrowtop");
		}else{
			showList.hide();
			$(this).removeClass("select-control-arrowtop");
		}
	});

	delegateCheckbox('commercialIds', '#commercialId-all', true);

	//任意点击隐藏下拉层
	$(document).bind("click",function(e){
		var target = $(e.target);
		//当target不在popover/coupons-set 内是 隐藏
		if(target.closest(".multi-select-con").length == 0 && target.closest(".multi-select").length == 0){
			$(".multi-select-con").hide();
			$(".multi-select > .select-control").removeClass("select-control-arrowtop");
		}
	});
});

function clearInputsValueAndCheckboxStatus(inputName, allId, clearWm){

	$($(inputName)).each(function(){
		//$(this).val(''); // 应注释掉，input值不能清除，否则clear之后，再次click就获取不到值了
	});
	if($(allId).length > 0 && $(allId).get(0).checked){ // 当“全部”为checked状态
		$(allId).parent().click();
	} else{
		//$(allId).parent().click();
		//$(allId).parent().click();// click两次，确保变为unchecked
	}
	if(clearWm){
		$('#wmId-all-ul').html(''); //清空仓库下拉选项框
	}
}

/**
 * 监听下拉选框
 * @param name
 * @param id
 * @param refreshWmsOrNot 是否刷新“仓库”的下拉选框
 */
function delegateCheckbox(name, id, refreshWmsOrNot){
	//业务类型 条件选择
	$(document).delegate(":checkbox[name='"+ name + "']","change",function(){
		associatedCheckAll(this,$(id));
		filterConditions(name,$(this).parents(".multi-select-con").prev(".select-control").find("em"),$(this).parents(".multi-select-con").next(":hidden"), refreshWmsOrNot);
	});
	//业务类型 条件选择 全选
	$(document).delegate(id,"change",function(){
		checkAll(this,name);
		filterConditions(name,$(this).parents(".multi-select-con").prev(".select-control").find("em"),$(this).parents(".multi-select-con").next(":hidden"), refreshWmsOrNot);
	});
}

/**
 * 刷新仓库的下拉选框
 * @param commercialIds 门店id，多个值时以逗号分隔
 */
function refreshWms(commercialIds){

	$('#wmId-all-em').html('');// 如果门店id为空，则清空仓库的文本显示

	if(commercialIds === ''){
		$('#wmId-all-ul').html('');   // 如果门店id为空，则清空仓库选项
		return;
	}

	var arr = JSON.parse(wareHouseJson);
	var wareHousesWanted = [];

	for(var i = 0; i < arr.length; i++){
		var wm = arr[i];
		var wmPrefIndex = wm.warehouseName.indexOf('_');
		if(commercialIds.indexOf(wm.warehouseName.substr(0, wmPrefIndex)) >= 0){
			wm.warehouseName = wm.warehouseName.substr(wmPrefIndex + 1);
			wareHousesWanted.push(wm);
		}
	}

	buildWmSelect(wareHousesWanted);

}

function buildWmSelect(data){

	var wmIdAllLi =
		'<li>' +
		'<label class="checkbox" for="wmId-all">' +
		'<span></span>' +
		'<input type="checkbox" id="wmId-all">全部' +
		'</label>' +
		'</li>';

	$('#wmId-all-ul').html('');
	$('#wmId-all-ul').html(wmIdAllLi); // 默认一开始添加“全部”

	var count = 0; // 一共添加的仓库的数量

	/** 一次添加一个仓库 **/
	$(data).each(function (i, v) {

		var isDisable = v.isDisable ? '（已停用）' : '';

		var wmTypeItem =

			'<li>' +
			'<label class="checkbox" for="wmId-' + count + '">' +
			'<span></span>' +
			'<input type="checkbox" name="wmIds" id="wmId-' + count + '" value="' + v.id + '" data-text="' + v.warehouseName + isDisable + '" >' + v.warehouseName + isDisable +
			'</label>' +
			'</li>';
		$('#wmId-all-ul').append(wmTypeItem);
		count++;
	});

	if(count == 0){
		$('#wmId-all-ul').html(''); // 如果没有仓库，应把“全部”删除
	}

	delegateCheckbox('wmIds', '#wmId-all', false);  // 开始监听仓库的checkbox
}


/**
 * 条件选择
 * @param checkboxName      string                  checkbox name
 * @param $textObj          jquery object           要改变字符串的元素
 * @param $hiddenObj        jquery object           要改变的隐藏域
 */
function filterConditions(checkboxName,$textObj,$hiddenObj, refreshWmsOrNot){
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
	if(refreshWmsOrNot){
		refreshWms(value1);
	}
}

/**
 *    associatedCheckAll     //关联全选
 *    @param  object         e           需要操作对象
 *    @param  jqueryObj      $obj        全选对象
 **/
function associatedCheckAll(e,$obj){
	var flag = true;
	var $name = $(e).attr("name");
	checkboxChange(e,'checkbox-check');
	$("[name='"+ $name +"']:checkbox").not(":disabled").each(function(){
		if(!this.checked){
			flag = false;
		}
	});
	$obj.get(0).checked = flag;
	checkboxChange($obj.get(0),'checkbox-check');
}


/**
 *    checkbox               //模拟checkbox功能
 *    @param  object         element     需要操作对象
 *    @param  className      class       切换的样式
 **/
function checkboxChange(element,className){
	if(element.readOnly){return false;}
	if(element.checked){
		$(element).parent().addClass(className);
	}else{
		$(element).parent().removeClass(className);
	}
}

/**
 *    checked all            //全选
 *    @param  object         e           需要操作对象
 *    @param  nameGroup      string      checkbox name
 **/
function checkAll(e,nameGroup){
	if(e.checked){
		//alert($("[name='"+ nameGroup+"']:checkbox"));
		$("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
			this.checked = true;
			checkboxChange(this,'checkbox-check');
		});
	}else{
		$("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
			this.checked = false;
			checkboxChange(this,'checkbox-check');
		});
	}
	checkboxChange(e,'checkbox-check');
}