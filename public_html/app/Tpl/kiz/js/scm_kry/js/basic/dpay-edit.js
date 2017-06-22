//Create by LiXing On 2015/8/11
function getContextPath() {
    var pathName = document.location.pathname;
    var index = pathName.substr(1).indexOf("/");
    var result = pathName.substr(0, index + 1);
    return result;
}

$("#btncancle").on("click", function () {
    //Message.confirm({title: '取消提示', describe: '是否放弃当前操作'}, function () {
    //    window.location = getContextPath() + "/scm/warehouse/index";
    //}, function () {
    //    Message.display();
    //});

    layer.confirm("是否放弃当前操作？", {icon: 3, title:'提示', offset: '30%'}, function(index){
        window.location.href = dishPath + "&act=dish_tag";
        layer.close(index);
    });
});

$(function () {
    //保存
    $("#btn-save,#btnSave-bak").on("click", function () {
    	var isBtnBak = $(this).attr("id")=="btnSave-bak";
    	
        $("#warehouseForm").submit();
        //检查是否验证通过
        var flag = $("#warehouseForm").valid();
        if (flag) {
            var params = $("#warehouseForm").serialize();
            $.ajax({
                type: "POST",
                url: ctx2Path + "&act=dish_pay_add_ajax",
                data: params,
                dataType: "json",
                contentType: "application/x-www-form-urlencoded;charset=UTF-8",
                async: false,
                cache: false,
                success: function (data) {
                    $("#btn-save").bind("click");
                    if (data.success) {
                        $.layerMsg(data.message, true, {
                            end:function(){
                                window.location.href = dishPath + "&act=dish_pay";
                            },shade: 0.3});
                    }
                    // else {
                    //     if (data.message.indexOf("编码") != -1) {
                    //     	var lab = '<label for="wareshouseCode" generated="true" class="error">'+data.message+'</label>';
                    //     	$("#wareshouseCode").parent().find(".wrong").html(lab);
                    //     } else if (data.message.indexOf("名称") != -1) {
                    //     	var lab = '<label for="warehouseName" generated="true" class="error">'+data.message+'</label>';
                    //     	$("#warehouseName").parent().find(".wrong").html(lab);
                    //     } else if (data.message.indexOf("过期") != -1) {
                    //     	$.layerMsg(data.message, false, {
                    //             end:function(){
                    //                 window.location.href = getContextPath() + "/scm/warehouse/index";
                    //         }});
                    //     }
                    // }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $("#btn-save").bind("click");
                    $.layerMsg("保存失败，请刷新页面或重新登录！", false);
                }
            });
        }
    });
    
    
    $("#warehouseForm").validate({
        errorPlacement: function (error, element) {
            error.appendTo(element.parents(".positionRelative")
                    .find(".wrong"));
        },
        //只验证不提交 需要提交时注销这段代码
        debug: true
    });
});