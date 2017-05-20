// var urlInit = ctxPath + "/auth/role/list";
// var urlCheckName = ctxPath + "/auth/role/checkRoleName";
// var urlSaveOrUpdate = ctxPath + "/auth/role/saveOrUpdateRole";
// var urldelete = ctxPath + "/auth/role/deleteRole";
// var urlBrandPermission = ctxPath + "/auth/role/loadBrandPermissions";
// var urlShopPermission = ctxPath + "/auth/role/loadShopPermissions";
// var urlPosPermission = ctxPath + "/auth/role/loadPosPermissions";
// var saveRolePermission = ctxPath + "/auth/role/saveRolePermission";

//权限内容的tab页，当前所在的第几页
var tabSort = 1;
$(function () {
    var validates;
    //权限内容tab切换
    bkeruyun.tab("#role-tab .tab-nav a", "click", '.tab-panel', "data-show", "active");
    //设置当前角色
    $(".role-list>ul>li").first().addClass('active');

    //点击角色名称加载对应权限内容
    $("#j_role-list").on('click','li>span', function () {
        var $this=$(this);
        $("#roleId").val($this.attr('mark'));
        $("#roleName").val($this.attr('data-type-name'));
        $("#typeFlag").val($this.attr('data-type-flag'));
        $("#tab1a").addClass("active");
        $("#tab2a").removeClass("active");
        $("#tab3a").removeClass("active");
        $("#tab4a").removeClass("active");
        $("#tab5a").removeClass("active");//onmobile
        $("#tab6a").removeClass("active");
        defaultDisplayTab();
    });

    //添加角色事件
    $("#role-add").on("click", function () {
        $("#id").val("");
        $("#name").val("");
        $("#roleAdd").show();
        $("#btnImportFile").css("border", "none");
        bkeruyun.showLayer();

    });

    //编辑角色事件
    $("#j_role-list .icon-editor").on("click", function () {
        bkeruyun.showLayer();
        $("#roleAdd").find('.panel-popover-title').html("修改角色");
        $('#nameTip').empty();
        var $this=$(this);
        $("#id").val($this.attr('data-role-id'));
        $("#name").val($this.attr('data-role-name'));
        $("#typeFlag").val($this.attr('data-type-flag'));

        var dealer=$this.attr('data-dealer');
        var $checkboxDealer=$('#switch-checkbox-dealer');
        var $allowShopBox=$('#allowShopBox');
        if(dealer==1){
            $checkboxDealer.attr('checked','checked');

            var shop=$this.attr('data-shop');
            var $checkboxShop=$('#switch-checkbox-shop');

            if(shop==1) {
                $checkboxShop.attr('checked', 'checked');
            }else{
                $checkboxShop.removeAttr('checked');
            }

            $allowShopBox.show();
        }else{
            $checkboxDealer.removeAttr('checked');
            $allowShopBox.hide();
        }

        //显示弹出层
        $("#roleAdd").show();
    });

    //设置允许经销商
    $("#switch-checkbox-dealer").on('click', function () {
        var $allowShopBox = $('#allowShopBox');
        if ("checked" == $(this).attr("checked")) {
            $allowShopBox.show();
        } else {
            $allowShopBox.hide();
        }
    });

    $(".role-list>ul>li").on('click', function () {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
    });

    //删除角色
    $("#j_role-list .icon-delete").on('click', function (event) {
        event.preventDefault();
        var args = {
            url: urldelete,
            postData: {id: $(this).parent("span").prev("span").attr("mark")},
            async: false
        }
        $.submitWithAjax(args);
        setTimeout(function () {
            window.location.href = urlInit + "?_ts=" + new Date().getTime();
        }, 500);
    });

    //点击编辑按钮
    $("#role-tab .icon-editor").on("click", function () {
        if ("2" == $("#typeFlag").val() || "3" == $("#typeFlag").val()) {
            bkeruyun.promptMessage("默认角色权限不可编辑");
            return;
        }
        $('#tabDetail input').each(function () {
            $(this).removeAttr("disabled");
        });
        $("#role-tab .icon-editor").css("display", "none");
        $("#role-tab .icon-stop").css("display", "");
        $("#role-tab .icon-confirm").css("display", "");
    });

    //验证角色名称
    $('#name').on('blur',checkRoleName);

    //保存角色修改/新增
    $('#btnImportFile').on('click', function () {
        if(!checkRoleName()){
            return false;
        }

        //设置允许经销商
        $("#switch-checkbox-dealer").on('click', function () {
            var $allowShopBox = $('#allowShopBox');
            if ("checked" == $(this).attr("checked")) {
                $allowShopBox.show();
            } else {
                $allowShopBox.hide();
            }
        });

        var checkDealer = 1;
        if ($("#switch-checkbox-dealer").attr('checked') == 'checked') {
            checkDealer = 1;
        } else {
            checkDealer = 2;
        }

        var checkShop = 1;
        if ($("#switch-checkbox-shop").attr('checked') == 'checked') {
            checkShop = 1;
        } else {
            checkShop = 2;
        }

        var condition = {
            name: $("#name").val().trim(),
            id: $("#id").val().trim(),
            typeFlag: $("#typeFlag").val().trim(),
            isCreateAccountByDealer: checkDealer,
            isCreateAccountByShop:checkShop
        };
        bkeruyun.showLoading();
        $.post(urlSaveOrUpdate,condition,function(data){
            bkeruyun.hideLoading();
            Message.alert({title:"提示信息",describe:"更新成功!"});
            setTimeout(function () {
                $("#roleAdd").hide();
                window.location.href = urlInit + "?_ts=" + new Date().getTime();
            }, 500);
        });
    });

    //点击取消按钮
    $("#role-tab .icon-stop").on("click", function () {
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        if (tabSort == 2) {
            renderShopPermissionDiv();
        } else if (tabSort == 3) {
            renderV4PosPermissionDiv();
        } else if (tabSort == 4) {
            // V5
            renderPosPermissionDiv();
        } else if (tabSort == 5) {
            renderOnMobilePermissionDiv();   //onmobile
        } else {
            renderBrandPermissionDiv();
        }
    });

    //点击保存按钮
    $("#role-tab .icon-confirm").on("click", function () {
        bkeruyun.showLoading();
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        var permissionIds = "";
        $('#tabDetail input').each(function () {
            if ($(this).parent().hasClass("checkbox-check")) {
                console.info($(this).parent().hasClass("checkbox-check"), $(this).attr("data-id"));
                permissionIds = permissionIds + $(this).attr("data-id") + ",";
            }
        });
        var discounts = [];
        $('.menu-discount').each(function () {
            var $this = $(this);
            var id = $this.attr('data-id');
            var name = $this.attr('data-name');
            var value = $this.val();
            var discount = $this.attr('data-discount');
            var item = 'id=' + id + ',name=' + name + ',value=' + value + ',discount=' + discount;
            discounts.push(item);
        });

        var groupFlag = 3;
        if (tabSort == 3 || tabSort == 4) {
            groupFlag = 3;
        } else if (tabSort == 5) {
            groupFlag = 4;
        } else if (tabSort == 6) {
            groupFlag = 5;
        } else {
            groupFlag = tabSort;
        }
        var menu = $.trim($("#tab" + tabSort + "a").text());
        var adminPermissionIds = $("#adminPermissionIds").val();
        bkeruyun.showLayer();

        var condition = {
            discounts: discounts.join('-'),
            adminPermissionIds: adminPermissionIds,
            permissionIds: permissionIds,
            menu: menu
        };

        // $.ajax({
        //     type: "POST",
        //     url: saveRolePermission + "?roleId=" + $("#roleId").val() + "&groupFlag=" + groupFlag + "" + "&random=" + Math.random(),
        //     data: condition,
        //     dataType: 'json',
        //     cache: false,
        //     async: false,
        //     success: function (data) {
        //         bkeruyun.hideLoading();
        //         Message.alert({title: '提示', describe: data.message}, function () {
        //             if (data.success) {
        //                 //操作成功,重新加载数据
        //                 if (tabSort == 2) {
        //                     renderShopPermissionDiv();
        //                 } else if (tabSort == 3) {
        //                     renderV4PosPermissionDiv();
        //                 } else if (tabSort == 4) {
        //                     // V5
        //                     renderPosPermissionDiv();
        //                 } else if (tabSort == 5) {
        //                     renderOnMobilePermissionDiv();
        //                 } else if (tabSort == 6) {
        //                     renderSelfServiceStoresPermissionDiv();
        //                 } else {
        //                     renderBrandPermissionDiv();
        //                 }
        //             } else {
        //                 //操作失败,不错数据加载
        //                 $("#role-tab .icon-editor").css("display", "none");
        //                 $("#role-tab .icon-stop").css("display", "");
        //                 $("#role-tab .icon-confirm").css("display", "");
        //
        //             }
        //         });
        //     },
        //     error: function (XMLHttpRequest, textStatus, errorThrown) {
        //         canSaveSubmit = true;
        //         bkeruyun.promptMessage("网络异常，请检查网络连接状态！");
        //         setTimeout(function () {
        //             window.location.href = urlInit + "?_ts=" + new Date().getTime();
        //         }, 500);
        //     }
        // });
        bkeruyun.hideLayer
    });


    //总部后台
    $('#tab1a').on('click',renderBrandPermissionDiv);

    //门店后台
    $('#tab2a').on('click',renderShopPermissionDiv);

    //V4/v5收银POS
    $('#tab3a').on('click',renderV4PosPermissionDiv);

    //V6收银POS
    $('#tab4a').on('click',renderPosPermissionDiv);

    //onMobile
    $('#tab5a').on('click',renderOnMobilePermissionDiv);

    //自助门店
    $('#tab6a').on('click',renderSelfServiceStoresPermissionDiv);

    // 进入页面时默认刷新权限表格
    function defaultDisplayTab() {
        if ($("#tab1a").length > 0) {
            renderBrandPermissionDiv();
        } else if ($("#tab3a").length > 0) {
            renderV4PosPermissionDiv();
        } else if ($("#tab4a").length > 0) {
            renderPosPermissionDiv();
        } else if ($("#tab5a").length > 0) {
            renderOnMobilePermissionDiv();//onmobile
        }

    };


    //验证角色名称
    function checkRoleName() {
        var $nameTip=$('#nameTip');
        var name=$('#name').val().trim();
        $nameTip.empty();
        if(name.length<1){
            $nameTip.text('* 角色名称不能为空!');
            return false
        }
        if(name.length>16){
            $nameTip.text('* 角色名称长度不能超过16个!');
            return false
        }

        var reg = new RegExp("[~`!@^_=……￥@！~|、\}{\\[\\]:;；：‘’“”\\'\",，.><?？、]");
        if (reg.test($('#name').val())) {
            $nameTip.text('* 包含非法字符,请检核!');
            return false;
        }

        var condition={
            id:$("#id").val(),
            name:name
        };
        //同步验证角色名称
        // $.ajax({
        //     type:"POST",
        //     url:urlCheckName,
        //     data:condition,
        //     dataType:"json",
        //     async:false,
        //     cache:false,
        //     success:function(data){
        //         if(!data){
        //             $nameTip.text('* 此角色已存在!');
        //             return false;
        //         }else {
        //             $nameTip.empty();
        //             return false;
        //         }
        //     }
        // });

        return true;
    };

    //加载总部后台tab
    function renderBrandPermissionDiv() {
        tabSort = 1;
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        bkeruyun.showLoading();
        $('#tabDetail').load(urlBrandPermission, {roleId: $("#roleId").val()}, function (response, status) {
            bkeruyun.hideLoading();
            $('#tabDetail').html(response);
            //初始化全选
            initChackAll();
            //设置全选功能
            setCheckAll();
        });
    }

    //加载门店后台tab
    function renderShopPermissionDiv() {
        tabSort = 2;
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        bkeruyun.showLoading();
        $('#tabDetail').load(urlShopPermission, {roleId: $("#roleId").val()}, function (response, status) {
            bkeruyun.hideLoading();
            $('#tabDetail').html(response);
            //初始化全选
            initChackAll();
            //设置全选功能
            setCheckAll();
        });
    }

    //加载收银V5POS后台tab
    function renderPosPermissionDiv() {
        tabSort = 4;
        // "V5收银POS" ->品牌下有6.2就显示,参数->group_flag = 3.2
        var groupFlag = 5;
        // var groupFlag=3;
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        bkeruyun.showLoading();
        $('#tabDetail').load(urlPosPermission, {
            roleId: $("#roleId").val(),
            groupFlag: groupFlag
        }, function (response, status) {
            bkeruyun.hideLoading();
            $('#tabDetail').html(response);
            //初始化全选
            initChackAll();
            //设置全选功能
            setCheckAll();
        });
    }


    //加载V4收银POS后台tab
    function renderV4PosPermissionDiv() {
        tabSort = 3;
        //   "V4收银POS" ->品牌下有<6.2就显示,参数->group_flag = 3.1
        var groupFlag = 4;
        // var groupFlag=3;
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        bkeruyun.showLoading();
        $('#tabDetail').load(urlPosPermission, {
            roleId: $("#roleId").val(),
            groupFlag: groupFlag
        }, function (response, status) {
            bkeruyun.hideLoading();
            $('#tabDetail').html(response);
            //初始化全选
            initChackAll();
            //设置全选功能
            setCheckAll();
        });
    }

    //加载onMobile后台tab
    function renderOnMobilePermissionDiv() {
        tabSort = 5;
        //   "V4收银POS" ->品牌下有<6.2就显示,参数->group_flag = 3.1
        var groupFlag = 6;
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        bkeruyun.showLoading();
        $('#tabDetail').load(urlPosPermission, {
            roleId: $("#roleId").val(),
            groupFlag: groupFlag
        }, function (response, status) {
            bkeruyun.hideLoading();
            $('#tabDetail').html(response);
            //初始化全选
            initChackAll();
            //设置全选功能
            setCheckAll();
        });
    }

    //加载自助门店后台tab
    function renderSelfServiceStoresPermissionDiv() {
        tabSort = 6;
        //   "V4收银POS" ->品牌下有<6.2就显示,参数->group_flag = 3.1
        var groupFlag = 7;
        $("#role-tab .icon-editor").css("display", "");
        $("#role-tab .icon-stop").css("display", "none");
        $("#role-tab .icon-confirm").css("display", "none");
        bkeruyun.showLoading();
        $('#tabDetail').load(urlPosPermission, {
            roleId: $("#roleId").val(),
            groupFlag: groupFlag
        }, function (response, status) {
            bkeruyun.hideLoading();
            $('#tabDetail').html(response);
            //初始化全选
            initChackAll();
            //设置全选功能
            setCheckAll();
        });
    }

    //设置全选功能
    function setCheckAll() {
        //1.设置全选  第一级
        $('tr.firstMenu').each(function (index, element) {
            $(element).find('label.checkbox').on('click', function () {
                var $this = $(this);
                if ($this.find(':checkbox').attr('disabled')) {
                    return false;
                }
                var firstIndex = $(element).data('first');
                if ($this.hasClass('checkbox-check')) {
                    $this.removeClass('checkbox-check');
                    $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox').removeClass('checkbox-check');
                    $('tr.triMenu[data-first=' + firstIndex + ']').find('label.checkbox').removeClass('checkbox-check');
                } else {
                    $this.addClass('checkbox-check');
                    $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox').addClass('checkbox-check');
                    $('tr.triMenu[data-first=' + firstIndex + ']').find('label.checkbox').addClass('checkbox-check');
                }
                return false;
            });
        });

        //二级全选
        $('tr.secMenu').each(function (index, element) {
            $(element).find('label.checkbox').on('click', function () {
                var $this = $(this);
                if ($this.find(':checkbox').attr('disabled')) {
                    return false;
                }
                var firstIndex = $(element).data('first');
                var secondIndex = $(element).data('second');
                if ($this.hasClass('checkbox-check')) {
                    $this.removeClass('checkbox-check');
                    $('tr.triMenu').each(function (indexThird, elementThird) {
                        if ($(elementThird).data('first') == firstIndex && $(elementThird).data('second') == secondIndex) {
                            $(elementThird).find('label.checkbox').removeClass('checkbox-check');
                        }
                    });
                } else {
                    $this.addClass('checkbox-check');
                    $('tr.triMenu').each(function (indexThird, elementThird) {
                        if ($(elementThird).data('first') == firstIndex && $(elementThird).data('second') == secondIndex) {
                            $(elementThird).find('label.checkbox').addClass('checkbox-check');
                        }
                    });
                }
                //设置 第一级全选
                var secMenu = $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox');
                var secMenuChecked = $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox-check');
                if (secMenu.length != secMenuChecked.length) {
                    $('tr.firstMenu[data-first=' + firstIndex + ']').find('label.checkbox').removeClass('checkbox-check');
                } else {
                    $('tr.firstMenu[data-first=' + firstIndex + ']').find('label.checkbox').addClass('checkbox-check');
                }
                return false;
            });
        });

        //第三级
        $('tr.triMenu').each(function (index, element) {
            $(element).find('label.checkbox').on('click', function () {
                var $this = $(this);
                if ($this.find(':checkbox').attr('disabled')) {
                    return false;
                }
                var firstIndex = $(element).data('first');
                var secondIndex = $(element).data('second');
                if ($this.hasClass('checkbox-check')) {
                    $this.removeClass('checkbox-check');
                } else {
                    $this.addClass('checkbox-check');
                }

                var triMenu = $('tr.triMenu[data-first=' + firstIndex + '][data-second=' + secondIndex + ']').find('label.checkbox');
                var triMenuChecked = $('tr.triMenu[data-first=' + firstIndex + '][data-second=' + secondIndex + ']').find('label.checkbox-check');
                //判断二级全选
                var $secMenuList = $('tr.secMenu[data-first=' + firstIndex + '][data-second=' + secondIndex + ']');
                if (triMenu.length != triMenuChecked.length) {
                    $secMenuList.find('label.checkbox').removeClass('checkbox-check');
                } else {
                    $secMenuList.find('label.checkbox').addClass('checkbox-check');
                }

                //判断一级全选
                var $firstMenuList = $('tr.firstMenu[data-first=' + firstIndex + ']');
                var secMenu = $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox');
                var secMenuChecked = $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox-check');
                if (secMenu.length != secMenuChecked.length) {
                    $firstMenuList.find('label.checkbox').removeClass('checkbox-check');
                } else {
                    $firstMenuList.find('label.checkbox').addClass('checkbox-check');
                }
                return false;
            });
        });
    };

    //初始化全选
    function initChackAll() {
        //1.线检查三级,二级,设置一级
        $('tr.secMenu').each(function (index, element) {
            var $this = $(this);
            var firstIndex = $(element).data('first');
            var secondIndex = $(element).data('second');

            //取得子级ck
            var triMenu = $('tr.triMenu[data-first=' + firstIndex + '][data-second=' + secondIndex + ']').find('label.checkbox');
            var triMenuChecked = $('tr.triMenu[data-first=' + firstIndex + '][data-second=' + secondIndex + ']').find('label.checkbox-check');

            //判断二级全选
            if (triMenu.length > 0) {
                var $secMenuList = $('tr.secMenu[data-first=' + firstIndex + '][data-second=' + secondIndex + ']');

                if (triMenu.length != triMenuChecked.length) {
                    $secMenuList.find('label.checkbox').removeClass('checkbox-check');
                } else {
                    $secMenuList.find('label.checkbox').addClass('checkbox-check');
                }
            }

            //判断一级全选
            var $firstMenuList = $('tr.firstMenu[data-first=' + firstIndex + ']');
            var secMenu = $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox');
            var secMenuChecked = $('tr.secMenu[data-first=' + firstIndex + ']').find('label.checkbox-check');
            if (secMenu.length > 0) {
                if (secMenu.length != secMenuChecked.length) {
                    $firstMenuList.find('label.checkbox').removeClass('checkbox-check');
                } else {
                    $firstMenuList.find('label.checkbox').addClass('checkbox-check');
                }
            }
            // return false;
        });
    };


    // 进入页面时默认刷新权限表格
    // defaultDisplayTab();

});
