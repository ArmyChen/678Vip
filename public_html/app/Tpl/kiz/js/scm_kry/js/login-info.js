/**
 * 登录信息管理
 * Created by mayi on 2015/9/15.
 */

var loginInfo = {
    //默认参数
    opts: {
        brandObj: {},
        loginBrand: false,//是否有品牌权限
        brand: [],//品牌信息，若当前用户无品牌权限则为空数组
        isLoginAsBrand: false,//当前是否是以品牌登录
        brandSearchInfo: '',//品牌搜索信息（品牌名称 + 地址）
        currentCommercialId: '',//当前的登录的门店id（若以门店登录）
        commercialList: [],//当前用户有权限的门店列表
        urlRoot: '',
        webRoot : '/kiz.php?',
        getCommercialListUrl: '/·',
        changeCommercialUrl: '',
        modifyPasswordUrl: '/mind/auth/userBrand/toModifyPassword',
        longoutUrl: 'ctl=user&act=logout',
        isMapInit: false,
        mapDiv: 'map_container',
        map: {},
        pointArray: [],
        markerArray: [],
        labelArray: [],
        infoWindowArray: [],
        searchArray: [],
        labelOpts: {offset: new BMap.Size(20, -10)},
        currentOpenInfoWindowIndex: 0,//当前打开的信息窗口下标
        infoWindowOpts: {
            title: "门店信息", //标题
            width: 250, //宽度
            height: 83, //高度
            enableAutoPan: true, //自动平移
            enableSendToPhone: false,//隐藏发送到手机按钮
            offset: new BMap.Size(0, 20), //偏移量
            searchTypes: []
        }
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        //判断当前是门店还是品牌登录
        _this.opts.isLoginAsBrand = _this.opts.currentCommercialId == "" || _this.opts.currentCommercialId == -1;

        loginInfo.showMenuByClick(".user-info", ".list-group");

        if (_this.opts.loginBrand) {
            _this.opts.brand.push(_this.opts.brandObj);
        }

        if ((_this.opts.brand.length + _this.opts.commercialList.length) > 1) {
            $("#changeCommercial").click(function (e) {
                $(".list-group").hide();
                $(".user-info").removeClass("user-info-evolving");
                var $modal = $("#changeCommercialModal");
                $modal.modal({
                    backdrop: 'static'
                });

                $modal.on('shown.bs.modal', function () {
                    if (!loginInfo.opts.isMapInit) {
                        loginInfo.initMap();
                        loginInfo.opts.isMapInit = true;
                    }
                })
            });
        } else {
            $("#changeCommercial").remove();
        }

        //修改密码
        $("#modifyPassword").click(function () {
            window.location.href = loginInfo.opts.modifyPasswordUrl;
        });

        //退出登录
        $("#logout").click(function () {
            layer.confirm('确定要退出登录吗？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
                if(window.sessionStorage){sessionStorage.clear();}
                top.location.href = loginInfo.opts.webRoot + loginInfo.opts.longoutUrl;
                layer.close(index);
            });
        });
    },

    //初始化地图
    initMap: function () {
        var _this = this, points = [], markers = [], labels = [], infoWindows = [], searchArray = [];
        var commercialsTemp = _this.opts.commercialList;
        var commercials = [];
        for (var i = 0; i < commercialsTemp.length; i++) {
            if (commercialsTemp[i].latlong != null && commercials.indexOf(commercialsTemp[i]) < 0) {
                commercials.push(commercialsTemp[i]);
            }
        }
        var commercialList = [];
        var length = commercials.length;

        var isLoginAsBrand = _this.opts.isLoginAsBrand;

        var map = new BMap.Map(_this.opts.mapDiv, {enableMapClick: false});

        if (!isLoginAsBrand) {
            for (var index = 0; index < length; index++) {
                if (commercials[index].id == _this.opts.currentCommercialId) {
                    commercialList.unshift(commercials[index]);
                } else {
                    commercialList.push(commercials[index]);
                }
            }
        } else {
            commercialList = commercials;
        }

        if (_this.opts.brand.length > 0) {
            var brandInfo = _this.opts.brand[0];
            var brandNameTitle = brandInfo.name,
                brandNameHtml = brandInfo.name,
                brandAddress = brandInfo.address || '-';
            if (isLoginAsBrand) {
                brandNameTitle += "(当前品牌)";
                brandNameHtml += '<span class="orange">(当前品牌)</span>';

            }
            var brandHtml = '<div class="row brandCol"><div class="col-md-9"><p title=" ' + brandNameTitle + '"><strong>';
            brandHtml += brandNameHtml;
            brandHtml += '</strong></p><p title=" ' + brandAddress + '">';
            brandHtml += brandAddress;
            brandHtml += '</p><input class="hide commercialId" index="-1" value="-1" /></div><div class="col-md-1 btn-box">';
            if (!isLoginAsBrand) {
                brandHtml += '<a href="#" class="btn btn-primary btn-switch" style="display: none;">切换</a>';
            }
            brandHtml += '</div>';

            $("#shop_list").append(brandHtml);

            _this.opts.brandSearchInfo = brandInfo.name + brandInfo.address || '-';
        }

        for (var i = 0; i < length; i++) {
            var latlongArray = commercialList[i].latlong.split(","),
                commercialName = commercialList[i].name,
                commercialNameTitle = commercialList[i].name,
                commercialNameHtml = commercialList[i].name,
                commercialAdress = commercialList[i].address || '-',
                commercialId = commercialList[i].id;

            if (!isLoginAsBrand && i == 0) {
                commercialNameTitle += '(当前门店)';
                commercialNameHtml += '<span class="orange">(当前门店)</span>';
            }

            //创建点
            var point = new BMap.Point(latlongArray[1], latlongArray[0]);

            //创建标记点，并添加到地图上
            var marker = new BMap.Marker(point);
            map.addOverlay(marker);

            //创建文字标签
            var label = new BMap.Label(commercialName, _this.opts.labelOpts);
            marker.setLabel(label);

            //创建信息弹框
            var btn = "</p>";
            if (isLoginAsBrand || (!isLoginAsBrand && i != 0)) {
                var href = location.href + "&commercialName="+commercialName+"&commercialId=" + commercialId;
                btn += '<p><a href="' + href + '" class="btn btn-primary btn-switch">切换</a></p>';
            }
            var content = "<p title='" + commercialName + "'><strong style='font-size: 14px;'>" + commercialName + "</strong></p><p title='" + commercialAdress + "'>" + commercialAdress + btn;
            var infoWindow = new BMapLib.SearchInfoWindow(map, content, _this.opts.infoWindowOpts);

            //创建门店列表html代码
            var htmlStart = '<div class="row commercialCol"><div class="col-md-9">';
            var htmlEnd1 = '</div><div class="btn-box"> <a href="#" class="btn btn-primary btn-switch" style="display: none;">切换</a> </div>';
            var htmlEnd2 = '</div><div class="btn-box"> </div>';
            var html = htmlStart;
            html += "<p title='" + commercialNameTitle + "'><strong>";
            html += commercialNameHtml;
            html += "</strong></p><p title='" + commercialAdress + "'>";
            html += commercialAdress;
            html += "</p>";
            html += "<input class='hide commercialId' index='" + i + "' value='" + commercialId + "'/>";
            html += "<input class='hide commercialName' index='" + i + "' value='" + commercialName + "'/>";
            if (!isLoginAsBrand && i == 0) {
                html += htmlEnd2;
            } else {
                html += htmlEnd1;
            }

            points.push(point);
            markers.push(marker);
            labels.push(label);
            infoWindows.push(infoWindow);
            searchArray.push(commercialName + commercialAdress);

            $("#shop_list").append(html);

            (function (i) {
                markers[i].addEventListener("click", function () {
                    infoWindows[i].open(points[i]);
                    _this.opts.currentOpenInfoWindowIndex = i;
                    $("#shop_list").find(".commercialCol").eq(i).click();
                });
            })(i);
        }

        map.centerAndZoom(points[0], 14);
        map.enableScrollWheelZoom();
        map.enableContinuousZoom();
        if (!isLoginAsBrand) {
            infoWindows[0].open(points[0]);
        }

        _this.opts.map = map;
        _this.opts.pointArray = points;
        _this.opts.markerArray = markers;
        _this.opts.labelArray = labels;
        _this.opts.infoWindowArray = infoWindows;
        _this.opts.searchArray = searchArray;

        _this.initEventBind();
    },

    //初始化事件绑定
    initEventBind: function () {
        var _this = this;
        _this.showMenu(".row", ".btn-switch", "current");

        //门店列表切换按钮点击事件
        $(document).delegate("#shop_list .btn-switch", "click", function () {
            var commercialId = $(this).parents(".row").find(".commercialId").val();
            var commercialName = $(this).parents(".row").find(".commercialName").val();
            //window.location.href = ctxPath + _this.opts.urlRoot + _this.opts.changeCommercialUrl + "?commercialId=" + commercialId + "&commercialName=" + encodeURIComponent(commercialName);
            window.location.href = location.href + "&commercialName=" + commercialName+ "&commercialId=" + commercialId;
        });

        //门店列表行点击事件
        $(document).delegate("#shop_list .row", "click", function () {
            var index = $(this).find(".commercialId").attr("index");
            //_this.opts.map.panTo(_this.opts.pointArray[index]);

            $(this).siblings().removeClass("current").find(".btn-switch").hide();
            $(this).addClass("current").find(".btn-switch").show();
            if (index == -1) {
                _this.opts.infoWindowArray[_this.opts.currentOpenInfoWindowIndex].close();
            } else {
                _this.opts.infoWindowArray[index].open(_this.opts.pointArray[index]);
                _this.opts.currentOpenInfoWindowIndex = index;
            }
        });

        //门店搜索框回车事件
        $(document).delegate("#changeCommercialModal #searchCommercial", "keydown", function (event) {
            if (event.keyCode == 13) {
                _this.searchCommercial();
            }
        });

        //门店搜索框搜索按钮点击事件
        $(document).delegate("#changeCommercialModal #js-type-search-button", "click", function () {
            _this.searchCommercial();
        });
    },

    //搜索门店
    searchCommercial: function () {
        var _this = this;

        var val = $.trim($("#searchCommercial").val());
        var rows = $("#shop_list").find(".commercialCol");
        var markers = _this.opts.markerArray;
        if (val != "") {
            if (_this.opts.brandSearchInfo.indexOf(val) != -1) {
                $("#shop_list").find(".brandCol").show();
            } else {
                $("#shop_list").find(".brandCol").hide();
            }

            var searchArray = _this.opts.searchArray;
            for (var i = 0; i < searchArray.length; i++) {
                if (searchArray[i].indexOf(val) != -1) {
                    rows.eq(i).show();
                    markers[i].show();
                } else {
                    rows.eq(i).hide();
                    markers[i].hide();
                    _this.opts.infoWindowArray[i].close();
                }
            }
        } else {
            $("#shop_list").find(".brandCol").show();
            rows.show();
            for (var i = 0; i < markers.length; i++) {
                markers[i].show();
            }
        }
    },

    showMenu: function (objStr, showStr, notHideClass) {
        var objs = $(objStr);
        //当鼠标移到每一个的时候执行
        objs.each(function () {
            var _this = this;
            var showObj = $(this).find(showStr);
            var flag = false;
            $(this).mouseover(function () {
                flag = true;
                showObj.show();
            });
            $(this).mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        showObj.hide();
                    }
                }, 300);

                if ($(this).hasClass(notHideClass)) {
                    flag = true;
                } else {
                    flag = false;
                }
            });
            showObj.mouseover(function () {
                flag = true;
            });
            showObj.mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        showObj.hide();
                    }
                }, 300);

                if ($(_this).hasClass(notHideClass)) {
                    flag = true;
                } else {
                    flag = false;
                }
            });
        });
    },

    //点击显示
    showMenuByClick: function (objStr, showStr, notHideClass) {
        var objs = $(objStr);
        //当鼠标移到每一个的时候执行
        objs.each(function () {
            var _this = this;
            var showObj = $(this).find(showStr);
            var flag = false;
            $(this).click(function (e) {
                if ($(e.target).closest(".list-group").length == 0) {
                    flag = true;
                    showObj.show();
                    $(this).addClass("user-info-evolving");
                }
            });
            $(this).mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        showObj.hide();
                        $(".user-info").removeClass("user-info-evolving");
                    }
                }, 300);

                if ($(this).hasClass(notHideClass)) {
                    flag = true;
                } else {
                    flag = false;
                }
            });
            $(this).mouseover(function () {
                flag = true;
            });
            showObj.mouseover(function () {
                flag = true;
            });
            showObj.mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        showObj.hide();
                        $(".user-info").removeClass("user-info-evolving");
                    }
                }, 300);

                if ($(_this).hasClass(notHideClass)) {
                    flag = true;
                } else {
                    flag = false;
                }
            });
        });
    }
};