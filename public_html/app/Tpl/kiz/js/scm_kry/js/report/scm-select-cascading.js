
$(function(){

	startSelectCascading();

	bindCancelButton();
});



function startSelectCascading(){

	delegateSelects('#firstSelectCascading', '#secondSelectCascading');

	$('#firstSelectCascading').parent().find('ul').find('li:eq(0)').click();
}


function delegateSelects(selectId, subSelectId){

	$(document).delegate(selectId, "change", function(){
		cascadeSubSelect(subSelectId, $(this).val());
	});

}

function cascadeSubSelect(subSelectId, selectedValue){

	$(subSelectId).find('option').hide();

	var optionsOfSelected = $(subSelectId).find('option[name=options-of-' + selectedValue + ']');

	if(optionsOfSelected != undefined && optionsOfSelected.length > 0) {

		optionsOfSelected.show();

		var hit = [];
		$.each($(subSelectId).find('option'), function (index, option) {
			if ($(option).attr('name') === 'options-of-' + selectedValue) {
				hit.push(index); // 根据第一个select找出第二个select可展示的options
			}
		});

		$(subSelectId).parent().find('ul').find('li').hide(); // 隐藏所有的li
		$.each($(subSelectId).parent().find('ul').find('li'), function (index, li) {
			$.each(hit, function (i, h) {
				if (index == h) {
					$(li).show(); // 显示所有可展示的li
				}
			});
		});

		var firstLi = $(subSelectId).parent().find('ul').find('li:eq(' + hit[0] + ')');
		if(firstLi != undefined){
			firstLi.click(); // 默认选中第一个
		}
	} else{

		$(subSelectId).append('<option value=""></option>');
		$(subSelectId).parent().find('ul').append('<li></li>');
		$(subSelectId).parent().find('ul').find('li').hide(); // 隐藏所有的li
		$(subSelectId).parent().find('ul').find('li:last').click();

		//var a = $('#firstSelectCascading').val();
		//var b = $('#secondSelectCascading').val();
		//var c = '';
	}
}

function bindCancelButton(){

	$("#undo-all").on("click", function() {
		$('#firstSelectCascading').parent().find('ul').find('li:eq(0)').click();
	});
}