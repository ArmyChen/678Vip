$(function(){
	

	
	
	
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
function add_deal_box(user_id)
{
	var url = ROOT+'?m=FxUser&a=add_deal&user_id='+user_id;

	var box = $.weeboxs.open(url, {
		contentType:'ajax',
		showButton:true,
		title:'添加会员分销商品',
		width:750,
		height:380,
		position: 'center',
		onopen:function(){
			$("form[name='seach_form']").submit(function(e){
				var query =  $("form[name='seach_form']").serialize();
				$.ajax({ 
					url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_seach_deal&user_id="+user_id, 
					data: query,
					success: function(obj){
						$(".list_table_box").html(obj);
					}
				});	
				return false;
			});
		},
		onok:function(){
			var check_ids = new Array();
			$(".key").each(function(i){
				if($(this).attr("checked")==true){
					check_ids.push($(this).val());
				}
			});

	
			
		    if(check_ids.length == 0){
		    	alert("请选择一个商品");
		    	return false;
		    }
		    
			$("input[name='check_ids']").val(check_ids.join(","));
			var sub_query = $("form[name='add_deal_form']").serialize();
			$.ajax({ 
				url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=save", 
				data: sub_query,
				dataType:"json",
				type:"post",
				success: function(obj){
					if(obj.status){
						alert(obj.info);
						box.close(); 
						location.reload();
						return false;
					}else{
						alert(obj.info);
						return false;
					}
					
				}
			});	
			return false;
			},
		}
	);
	load_seach_deal(user_id);
}
//全选
function WeeboxsCheckAll(tableID)
{
	$("#"+tableID).find(".key").attr("checked",$("#weeb_check").attr("checked"));
}


function load_seach_deal(user_id)
{

	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_seach_deal&user_id="+user_id, 
		data: "ajax=1",
		success: function(obj){
			$(".list_table_box").html(obj);
			
		}
	});	
}
