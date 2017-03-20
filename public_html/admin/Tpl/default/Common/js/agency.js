$(document).ready(function(){
	var id=$("input[name='id']").val();
	$("select[name='province_id']").live("change",function(){
		init_city($(this).val(),id);
	});

	init_city($("select[name='province_id']").val(),id);
	
	
	$("select[name='city_id']").live("change",function(){
		init_region($(this).val(),id);
	});

	//init_region($("select[name='city_id']").val(),id);
	
	$("input[name='ref_mobile']").live("blur",function(){
		var mobile=$(this).val();
		init_ref_user(mobile);
	});


});

function init_city($province_id,id)
{

	var action=ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=get_city_list&province_id="+$province_id; 
	var query = new Object();
	query.id = id;
	$.ajax({
		url:action,
		type:"POST",
		data:query,
		dataType:"json",
		success:function(obj){
			$("#city_box").html(obj);
			init_region($("select[name='city_id']").val(),id);
		}
	})

	
}

function init_region($city_id,id)
{
	var action=ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=get_region_list&city_id="+$city_id; 
	var query = new Object();
	query.id = id;
	$.ajax({
		url:action,
		type:"POST",
		data:query,
		dataType:"json",
		success:function(obj){
			$("#region_box").html(obj);
			
		}
	})


}


function init_ref_user(mobile){
	var action=ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=get_ref_user&mobile="+mobile; 
	var query = new Object();
	$.ajax({
		url:action,
		type:"POST",
		data:query,
		dataType:"json",
		success:function(obj){
			$("#ref_user_box").html(obj);
			
		}
	})
	
}