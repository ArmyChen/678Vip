$(function(){
	init_fx_level(1);
	//绑定事件
	$("select[name='fx_salary_type']").live("change",function(){init_fx_level();});
});