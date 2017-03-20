function add_deal_box(deal_id)
{
	var url = ROOT+'?m=FxSalary&a=add_deal';
	if(deal_id>0){
		url+='&deal_id='+deal_id;
	}
	var box = $.weeboxs.open(url, {
		contentType:'ajax',
		showButton:true,
		title:'添加分销商品',
		width:750,
		height:400,
		position: 'center',
		onopen:function(){
			$("form[name='seach_form']").submit(function(e){
				var query =  $("form[name='seach_form']").serialize();
				$.ajax({ 
					url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_seach_deal", 
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
			var is_err = 0;
			$("input[name='fx_salary[]']").each(function(i){
				if($(this).val()=='' || $(this).val()<=0){
					is_err=1;
					return false;
				}
			});
			if(check_ids.length == 0){
		    	alert("请选择一个商品");
		    	return false;
		    }
			
			if(is_err == 1){
				if(confirm("定额/比率佣金不设置或为0，则为系统默认值")){
					
				}else{
					return false;
				}
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
						window.location = obj.jump;
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
}

function del_deal_fx(id){
	
	if(!id)
	{
		idBox = $(".key:checked");
		if(idBox.length == 0)
		{
			alert(LANG['DELETE_EMPTY_WARNING']);
			return;
		}
		idArray = new Array();
		$.each( idBox, function(i, n){
			idArray.push($(n).val());
		});
		id = idArray.join(",");
	}
	if(confirm(LANG['CONFIRM_DELETE']))
	$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=del_deal_fx&id="+id, 
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				$("#info").html(obj.info);
				if(obj.status==1)
				location.href=location.href;
			}
	});
}
