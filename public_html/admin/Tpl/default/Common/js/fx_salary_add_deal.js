$(function(){
	init_fx_level(1);
	//绑定事件
	$("select[name='fx_salary_type']").live("change",function(){init_fx_level();});
	
	load_seach_deal(1);
	
	//分页委托事件
	$(".page a").live("click",function(){
		var ajax_url = $(this).attr("href");
		$.ajax({ 
			url:ajax_url, 
			data: "ajax=1",
			success: function(obj){
				$(".list_table_box").html(obj);
				return false;
			}
		});	
		return false;
	});
});
function load_seach_deal(init)
{
	var condition = '';
	if(init==1){
		var deal_id = $("input[name='check_ids']").val();
		if(deal_id>0){
			condition+="&deal_id="+deal_id;
		}
			
	}
	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_seach_deal"+condition, 
		data: "ajax=1",
		success: function(obj){
			$(".list_table_box").html(obj);
			
		}
	});	
}
//全选
function WeeboxsCheckAll(tableID)
{
	$("#"+tableID).find(".key").attr("checked",$("#weeb_check").attr("checked"));
}

