$(function(){



	$(".is_effect_btn").bind("click",function(){

		var obj = $(this);

		var id = $(this).attr("data-id");

		var query = new Object();

		query.act = "dc_menu_status";

		query.id = id;

		$.ajax({

			url:ajax_url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(data){

				if(data.status){

					if(data.is_effect ==1){

						$(obj).html("&#xe612;");

					}else{

						$(obj).html("&#xe60b");

					}

				}else{

					$.showErr(data.info);

				}

			}

		});

	});









	/*导入菜单*/

	$("button.btnImport").bind("click",function(){

		var location_id = $(this).attr("data-id");

		var query = new Object();

		query.act = "load_btnImport_weebox";

		query.location_id = location_id;

		$.ajax({

			url:ajax_url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(result){

				$.weeboxs.open(result.html, {boxid:'btnImport_weebox',contentType:'text',showButton:false,title:"导入菜单",width:570,type:'wee',onopen:function(){

						init_ui_button();

						init_ui_textbox();

						init_ui_select();

						init_ui_checkbox();







						//提交数据

						$("form[name='add_menu_form']").submit(function(){



						});



					}

				});

			}

		});

	});





		/*导出菜单*/

	$("button.btnExport").bind("click",function(){

		var location_id = $(this).attr("data-id");

		var query = new Object();

		query.act = "load_btnExport_weebox";

		query.location_id = location_id;

		$.ajax({

			url:ajax_url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(result){

				$.weeboxs.open(result.html, {boxid:'btnExport_weebox',contentType:'text',showButton:false,title:"导出菜单",width:570,type:'wee',onopen:function(){

						init_ui_button();

						init_ui_textbox();

						init_ui_select();

						init_ui_checkbox();







						//提交数据

						$("form[name='add_menu_form']").submit(function(){



						});



					}

				});

			}

		});

	});





	/*新增菜单*/

	$("button.add_menu_btn").bind("click",function(){

		var location_id = $(this).attr("data-id");

		var query = new Object();

		query.act = "load_add_menu_weebox";

		query.location_id = location_id;

		$.ajax({

			url:ajax_url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(result){

				$.weeboxs.open(result.html, {boxid:'add_menu_weebox',contentType:'text',showButton:false,title:"添加菜单",width:800,height:500,type:'wee',onopen:function(){

						init_ui_button();

						init_ui_textbox();

						init_ui_select();

						init_ui_checkbox();

                    	$("input[name=isImagePost]").attr("value",1);


                    //上传控件

						$(".img_image_upbtn div.img_image_btn").ui_upload({multi:false,FilesAdded:function(files){

							//选择文件后判断

							if($(".img_image_upload_box").find("span").length+files.length>1)

							{

								$.showErr("最多只能传1张图片");

								return false;

							}

							else

							{

								for(i=0;i<files.length;i++)

								{

									var html = '<span><div class="loader"></div></span>';

									var dom = $(html);

									$(".img_image_upload_box").append(dom);

								}

								uploading = true;

								return true;

							}

						},FileUploaded:function(responseObject){

							if(responseObject.error==0)

							{
                                $("input[name=isImagePost]").attr("value",0);

                                $("#cropper").show();
								var first_loader = $(".img_image_upload_box").find("span div.loader:first");

								var box = first_loader.parent();

								first_loader.remove();

                                var copper = $(".img_image_upload_box").parent().find("#cropper");
                                $(copper).attr("src",APP_ROOT+'/'+responseObject.url);

								var html = '<a href="javascript:void(0);"></a>'+

								'<div id="preview-pane"><img id="uploadimage" src="'+APP_ROOT+'/'+responseObject.url+'" /></div>'+

								'<input type="hidden" name="image" value="'+responseObject.web_40+'" />';

								$(box).html(html);

								$(box).find("a").bind("click",function(){

									$(this).parent().remove();

								});
                                var jcrop_api,
                                    boundx,
                                    boundy,

                                    // Grab some information about the preview pane
                                    $pimg = $("#uploadimage"),

                                    xsize = 100,
                                    ysize = 100;

                                $('#cropper').Jcrop({
									onSelect: preView,
                                    allowMove:true,
									bgFade: true, // use fade effect
                                    boxWidth:600,
                                    aspectRatio: xsize / ysize
                                },function(){
                                    var bounds = this.getBounds();
                                    boundx = bounds[0];
                                    boundy = bounds[1];
                                    jcrop_api = this;
                                });

                                function preView(c) {
                                    if (parseInt(c.w) > 0)
                                    {
                                        $("input[name=x]").attr("value",c.x);
                                        $("input[name=y]").attr("value",c.y);
                                        $("input[name=w]").attr("value",c.w);
                                        $("input[name=h]").attr("value",c.h);
                                        $("input[name=isImagePost]").attr("value",1);
                                        var rx = xsize / c.w;
                                        var ry = ysize / c.h;
                                        $pimg.css({
                                            width: Math.round(rx * boundx) + 'px',
                                            height: Math.round(ry * boundy) + 'px',
                                            marginLeft: '-' + Math.round(rx * c.x) + 'px',
                                            marginTop: '-' + Math.round(ry * c.y) + 'px'
                                        });



                                    }
                                }

                                // $("#img_image2").bind("click",function(){
									//
                                // });


							}

							else

							{

								$.showErr(responseObject.message);

							}

						},UploadComplete:function(files){
							//全部上传完成

							uploading = false;

						},Error:function(errObject){

							$.showErr(errObject.message);

						}});





						$(".time_input").datetimepicker();

						//提交数据

						$("form[name='add_menu_form']").submit(function(){
                            var isImage = $("input[name=isImagePost]").attr("value");
                            if(isImage == 0){
                                $.showErr("请上传图片进行裁剪，才能提交");
                                return false;
                            }
							var form = $("form[name='add_menu_form']");



							if($.trim($("input[name='menu_name']").val()) == ''){

								$.showErr("请输入新增菜单名称");

								return false;

							}
							//上传原图成功，保存裁剪图片
							var a = new Object();
							a.x = $("input[name=x]").attr("value");
							a.y = $("input[name=y]").attr("value");
							a.w = $("input[name=w]").attr("value");
							a.h = $("input[name=h]").attr("value");
							a.src =   $("#cropper").attr("src");
							var ajaxUrl = "crop.php?x="+a.x+"&y="+a.y+"&w="+a.w+"&h="+a.h+"&src="+a.src;
							$.get(ajaxUrl,function (e) {
								$("input[name=image]").removeAttr("value");
								$("input[name=image]").attr("value",e);
								$("input[type=hidden][name=image]").attr("value",e);


								var query = $(form).serialize();

								var url = $(form).attr("action");

								$.ajax({

									url:url,

									data:query,

									type:"post",

									dataType:"json",

									success:function(data){

										if(data.status==1){

											$.showSuccess(data.info,function(){window.location=data.jump;});

										}else{

											$.showErr(data.info);



										}

									}

								});
							});




							return false;

						});



					}

				});

			}

		});

	});





	/*编辑菜单*/

	$(".edit_menu_btn").bind("click",function(){

		var id = $(this).attr("data-id");
		var page = $(this).attr("data-page");

		var query = new Object();

		query.act = "load_edit_menu_weebox";

		query.id = id;
		query.page = page;

		$.ajax({

			url:ajax_url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(result){

				if(result.status){

				$.weeboxs.open(result.html, {boxid:'edit_menu_weebox',contentType:'text',showButton:false,title:"编辑菜单",width:800,height:600,type:'wee',onopen:function(){

						init_ui_button();

						init_ui_textbox();

						init_ui_select();

						init_ui_checkbox();

						init_img_del();
                    	$("input[name=isImagePost]").attr("value",1);
						//上传控件

						$(".img_image_upbtn div.img_image_btn").ui_upload({multi:false,FilesAdded:function(files){

							//选择文件后判断

							if($(".img_image_upload_box").find("span").length+files.length>1)

							{

								$.showErr("最多只能传1张图片");

								return false;

							}

							else

							{

								for(i=0;i<files.length;i++)

								{

									var html = '<span><div class="loader"></div></span>';

									var dom = $(html);

									$(".img_image_upload_box").append(dom);

								}

								uploading = true;

								return true;

							}

						},FileUploaded:function(responseObject){

							if(responseObject.error==0)

							{
                                $("input[name=isImagePost]").attr("value",0);

                                $("#cropper").show();

								var first_loader = $(".img_image_upload_box").find("span div.loader:first");

								var box = first_loader.parent();

								first_loader.remove();

                                var copper = $(".img_image_upload_box").parent().find("#cropper");
                                $(copper).attr("src",APP_ROOT+'/'+responseObject.url);

                                var html = '<a href="javascript:void(0);"></a>'+

                                    '<div id="preview-pane"><img id="uploadimage" src="'+APP_ROOT+'/'+responseObject.url+'" /></div>'+

								'<input type="hidden" name="image" value="'+responseObject.web_40+'" />';

								$(box).html(html);

								$(box).find("a").bind("click",function(){

									$(this).parent().remove();

								});

                                var jcrop_api,
                                    boundx,
                                    boundy,

                                    // Grab some information about the preview pane
                                    $pimg = $("#uploadimage"),

                                    xsize = 100,
                                    ysize = 100;

                                $('#cropper').Jcrop({
                                    onSelect: preView,
                                    allowMove:true,
									bgFade: true,
                                    boxWidth:600,// use fade effect
                                    aspectRatio: xsize / ysize
                                },function(){
                                    var bounds = this.getBounds();
                                    boundx = bounds[0];
                                    boundy = bounds[1];
                                    jcrop_api = this;
                                });

                                function preView(c) {
                                    if (parseInt(c.w) > 0)
                                    {
                                        $("input[name=x]").attr("value",c.x);
                                        $("input[name=y]").attr("value",c.y);
                                        $("input[name=w]").attr("value",c.w);
                                        $("input[name=h]").attr("value",c.h);
                                        $("input[name=isImagePost]").attr("value",1);
                                        var rx = xsize / c.w;
                                        var ry = ysize / c.h;
                                        $pimg.css({
                                            width: Math.round(rx * boundx) + 'px',
                                            height: Math.round(ry * boundy) + 'px',
                                            marginLeft: '-' + Math.round(rx * c.x) + 'px',
                                            marginTop: '-' + Math.round(ry * c.y) + 'px'
                                        });

                                    }
                                }
							}

							else

							{

								$.showErr(responseObject.message);

							}

						},UploadComplete:function(files){

							//全部上传完成

							uploading = false;

						},Error:function(errObject){

							$.showErr(errObject.message);

						}});



							$(".time_input").datetimepicker();



						//提交数据

						$("form[name='edit_menu_form']").submit(function(){
							if($('input[name=image]').val() == $('input[name=image]').parent().find('img').attr('src')){
								var form = $("form[name='edit_menu_form']");



								if($.trim($("input[name='menu_name']").val()) == ''){

									$.showErr("请输入新增菜单名称");

									return false;

								}
								var page=$('#current_page').val();
								debugger;
								var query = $(form).serialize();

								var url = $(form).attr("action");

								$.ajax({

									url:url,

									data:query,

									type:"post",

									dataType:"json",

									async:false,

									success:function(data){
                                        var name = getQueryString("name")==null?'':getQueryString("name");
                                        var p = getQueryString("name")==null?'':getQueryString("name");
										if(data.status==1){

											$.showSuccess(data.info,function(){window.location=data.jump+"&name="+name+"&p="+page;});

										}else{

											$.showErr(data.info);



										}

									}

								});
								return false;
							}else{

								var isImage = $("input[name=isImagePost]").attr("value");
								if(isImage == 0){
									$.showErr("请上传图片进行裁剪，才能提交");
									return false;
								}
								var form = $("form[name='edit_menu_form']");



								if($.trim($("input[name='menu_name']").val()) == ''){

									$.showErr("请输入新增菜单名称");

									return false;

								}

								//上传原图成功，保存裁剪图片
								var a = new Object();
								a.x = $("input[name=x]").attr("value");
								a.y = $("input[name=y]").attr("value");
								a.w = $("input[name=w]").attr("value");
								a.h = $("input[name=h]").attr("value");
								a.src =   $("#cropper").attr("src");
								var ajaxUrl = "crop.php?x="+a.x+"&y="+a.y+"&w="+a.w+"&h="+a.h+"&src="+a.src;
								$.get(ajaxUrl,function (e) {
									$("input[name=image]").removeAttr("value");
									$("input[name=image]").attr("value",e);
									$("input[type=hidden][name=image]").attr("value",e);


									var query = $(form).serialize();

									var url = $(form).attr("action");

									$.ajax({

										url:url,

										data:query,

										type:"post",

										dataType:"json",

										async:false,

										success:function(data){

											if(data.status==1){

												$.showSuccess(data.info,function(){window.location=data.jump;});

											}else{

												$.showErr(data.info);



											}

										}

									});
								});




								return false;							}


						});



					}

				});

			 }else{

				 $.showErr(result.info);

			 }

			}

		});

	});



	$("button.batch_del").bind("click",function(){

		$.showConfirm("确定要批量删除菜单吗?",function(){

			$("form[name='menu_manage_form']").submit();

		});

		return false;



	});



	$("form[name='menu_manage_form']").submit(function(){

		var form = $("form[name='menu_manage_form']");

		var query = $(form).serialize();

		var url = $(form).attr("action");

		$.ajax({

			url:url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(data){

				if(data.status==1){

					$.showSuccess(data.info,function(){window.location=data.jump;});

				}else{

					$.showErr(data.info);



				}

				return false;

			}

		});

		return false;

	});



});





function del_menu(obj){

	var id = $(obj).attr("data-id");

	$.showConfirm("确认删除吗？",function(){

		var query = new Object();

		query.act = "dc_menu_del";



		query.id = id;

		$.ajax({

			url:ajax_url,

			data:query,

			type:"post",

			dataType:"json",

			success:function(data){

				if(data.status){

					window.location = data.jump;

				}else{

					$.showErr(data.info);

				}

			}

		});



	});



}

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

function init_img_del(){

	$(".pub_upload_img_box").find("a").bind("click",function(){

		$(this).parent().remove();

	});

}



$(function(){
	$(".cate_name_box").bind("click",function(){
		$(this).hide();
		$(this).parent().parent().find(".edit_item_txt").show();
		$(this).parent().parent().find(".edit_item_txt input").focus();
	});

	$(".edit_save").bind("click",function(){
		edit_menu_name($(this));
	});

	$(".edit_item_txt input").bind("blur",function(){
		edit_menu_name($(this));
	});
});
function edit_menu_name(obj){
	var stock =$.trim($(obj).parent().find("input").val());
	var id = $(obj).parent().find("input").attr("data-id");
	if(stock.length == 0){
		$.showErr("库存不能为空！");
		return false;
	}
	var hide_div_obj = $(obj).parent();
	var show_div_obj = $(obj).parent().parent().find(".cate_name_box");
	var txt_obj = $(obj).parent().parent().find(".name_edit_btn");
	var query = new Object();
	query.act = "do_edit_menu_stock";
	query.stock = stock;
	query.id = id;
	$.ajax({
		url:ajax_url,
		data:query,
		type:"post",
		dataType:"json",
		success:function(data){
			if(data.status){
				$(txt_obj).html(stock);
				$(hide_div_obj).hide();
				$(show_div_obj).show();
			}else{
				$.showErr(data.info);
			}
		}
	});
}