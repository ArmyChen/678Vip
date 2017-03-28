/**
 * @description 返回 yyyy-MM-dd 格式的日期
 * @example new Date().Format("yyyy-MM-dd")返回今天 || new Date(毫秒数).Format("yyyy-MM-dd") 返回指定日期
 * @param fmt {string} 'yyyy-MM-dd'
 */
Date.prototype.Format = function (fmt) { //author: meizz
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};
var bkeruyun = {
    /**
     * @description: 动态添加页脚
     * @example: bkeruyun.addFooter();
     */
    addFooter: function () {
        if ($("#footer").length < 1) {
            $("body").append('<div id="footer" class="footerPs">©2012 keruyun.com 京ICP备12039470号</div>');
        }
    },
    /**
     * @description: 隐藏元素
     * @example: bkeruyun.hideElement($("#id"));
     * @param: $objs {jquery object}
     */
    hideElement: function ($objs) {
        $objs.hide();
    },
    /**
     * @description: 显示遮罩层
     * @example: bkeruyun.showLayer();
     */
    showLayer: function () {
        if ($("#layer").length > 0) {
            $("#layer").show();
        } else {
            $(document.body).append('<div id="layer"></div>');
            $("#layer").show();
        }
        $('html').css({"overflow-y": "hidden"});
    },
    /**
     * @description: 隐藏遮罩层
     * @example: bkeruyun.hideLayer();
     */
    hideLayer: function () {
        $("#layer").hide();
        $('html').css({"overflow-y": "auto"});
    },
    /**
     * @description: 滚动条效果
     * @param: $obj {jquery object}
     * @example: bkeruyun.rolling($("#nav-fixed"));
     */
    rolling: function ($obj) {
        var top = 0;
        var h = $obj.outerHeight();
        var titleObj = $(".article-header").eq(0);
        $(document).on('scroll', function () {
            top = $(document).scrollTop();
            //			console.log("titleObjtop=="+titleObj.offset().top);
            if (top > h) {
                titleObj.addClass("article-header-fixed");
            } else {
                titleObj.removeClass("article-header-fixed");
            }
        });

    },
    /**
     * @description: 移除元素
     * @example: bkeruyun.removeElement($("#id"),event);
     * @param: $obj {jquery object}
     * @param: e {object}
     */
    removeElement: function ($obj, e) {
        $obj.remove();
        if (e && e.preventDefault) {
            //阻止默认浏览器动作(W3C)
            e.preventDefault();
        } else {
            //IE中阻止函数器默认动作的方式
            window.event.returnValue = false;
        }

    },
    /**
     * @description: 单选
     * @example: bkeruyun.selectOne($(this),$(this).siblings(),'current');
     * @param: $item     {jquery object}
     * @param: $items    {jquery object}
     * @param: className {string}
     */
    selectOne: function ($item, $items, className) {
        $items.removeClass(className);
        $item.addClass(className);
    },
    /**
     * @description: 没有查询到数据时调用,返回一个自定义信息的元素，需要通过append添加到指定位置
     * @example: var notData = bkeruyun.notQueryData('么有查询到数据，换个条件试试吧！');
     * @param: msg {string} 自定义信息
     */
    notQueryData: function (msg) {
        window.scroll(0, 0);//将滚动条设置到顶部
        var notData = '<div class="notSearchContent">' + msg + '</div>';
        return notData;
    },
    /**
     * @description: 检测浏览器版本 如果是ie8及以下提示信息
     * @example: bkeruyun.detectionBrowser();
     */
    detectionBrowser: function () {
        // $.layerMsg("detectionBrowser");
        var browserVersion = window.navigator.userAgent.toUpperCase();
        //隐藏提示信息
        //$("#browser-prompt").hide();
        if (browserVersion.indexOf("MSIE") > -1) {
            //如果是ie浏览器检测是否是ie6 ie7 ie8
            if (browserVersion.indexOf("MSIE 6") > -1 || browserVersion.indexOf("MSIE 7") > -1 || browserVersion.indexOf("MSIE 8") > -1 || browserVersion.indexOf("MSIE 9") > -1) {
                //判断提示信息元素是否已经存在
                if ($("#browser-prompt").length < 1) {
                    //不存在 添加
                    $(document.body).append('<div id="browser-prompt"><div></div><p class="center">您当前的浏览器版本过低，有些功能不被支持，为了不影响您的正常使用，建议升级浏览器版本，或者下载<a href="http://chrome.360.cn/" target="_blank">360极速浏览器</a></p><span class="cancel">×</span></div>');
                }
                //显示提示信息
                $("#browser-prompt").show();
                $("#browser-prompt > .cancel").on("click", function () {
                    $("#browser-prompt").slideUp();
                });
                /*
                 setTimeout(function(){
                 $("#browser-prompt").hide();
                 },10000);
                 */
            }
        }
    },
    /**
     * @description: 显示隐藏菜单
     * @example: bkeruyun.showMenu(this,'.showObj') || bkeruyun.showMenu($("#set-add > li"),$("#set-add > li > ul"))
     * @param: objs {object} 鼠标移上去的元素
     * @param: showStr {string} 要显示的元素
     */
    showMenu: function (objs, showStr) {
        var objs = $(objs);
        //当鼠标移到每一个的时候执行
        objs.each(function () {
            var showObj = $(this).find(showStr);
            var flag = false;
            $(this).mouseover(function () {
                flag = true;
                showObj.fadeIn("slow");
            });
            $(this).mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        showObj.fadeOut("slow");
                    }
                }, 300);

                flag = false;
            });
            showObj.mouseover(function () {
                flag = true;
            });
            showObj.mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        showObj.fadeOut("slow");
                    }
                }, 300);
                flag = false;
            });
        });

    },
    /**
     * @description: 显示导航
     * @example: bkeruyun.style.showNav($(".nav > li,.dropdown-submenu"), ".dropdown-menu", "open");
     * @param: overObjs {object} 鼠标移上去的元素
     * @param: showObj {object} 要显示的元素
     * @param: className {string} 样式名
     */
    //显示导航 鼠标移上去的元素 要显示的元素 要添加样式的元素 样式名
    showNav: function (overObjs, showObj, className) {
        var overObjs = $(overObjs);
        //当鼠标移到每一个的时候执行
        overObjs.each(function () {
            var flag = false;
            var that = $(this);
            var showObj = that.find(showObj);
            that.mouseover(function () {
                flag = true;
                that.addClass(className);
            }).mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        that.removeClass(className);
                    }
                }, 300);

                flag = false;
            });
            showObj.mouseover(function () {
                flag = true;
            }).mouseout(function () {
                setTimeout(function () {
                    if (flag == false) {
                        that.removeClass(className);
                    }
                }, 300);
                flag = false;
            });
        });

    },
    /**
     * @description: 关闭当前窗口
     * @example: bkeruyun.closeWindow();
     */
    closeWindow: function () {
        var browserName = navigator.appName;
        if (browserName == "Netscape") {
            window.open('', '_parent', '');
            window.close();
        }
        else {
            if (browserName == "Microsoft Internet Explorer") {
                window.opener = "whocares";
                window.close();
            }
        }
    },

    //创建菜单
    createMenu: function(data,rootUrl){
        var dataLength=data.length;
        for(var i=0;i<dataLength;i++){
            var dataItem=data[i];
            var role=dataItem.role;
            var name=dataItem.name;
            var url=dataItem.url;
            var children=dataItem.children;
            var li = $('<li class="nav_group" role="'+role+'"></li>');
            if(children && children.length>0){
                children.sort(function(x,y){return x.sort - y.sort});
                li.html('<a class="dropdown-toggle '+role+'" href="'+url+'"> '+name+' <span class="caret"></span></a>');
                if(children.length > 0){
                    var childrenUl=$('<ul class="dropdown-menu"></ul>');
                    for(var j=0;j<children.length;j++){
                        var childrenItem = children[j];
                        var childrenChildren=childrenItem.children;

                        var grandsonLi=$('<li class="dropdown-submenu"><a href="'+rootUrl+childrenItem.url+'">'+childrenItem.name+'</a>');
                        childrenUl.append(grandsonLi);
                        if(childrenChildren &&childrenChildren.length){
                            childrenChildren.sort(function(x,y){return x.sort - y.sort});
                            var grandsonUl=$('<ul class="dropdown-menu multi-level"></ul>');
                            for(var z=0;z<childrenChildren.length;z++){
                                grandsonUl.append($('<li><a href="'+rootUrl+childrenChildren[z].url+'">'+childrenChildren[z].name+'</a></li>'));
                            }
                            grandsonLi.append(grandsonUl);
                            childrenUl.append(grandsonLi);

                        }
                    }
                    li.append(childrenUl);
                }else{
                    li.append('<li class="dropdown-submenu"><a href="'+rootUrl+url+'">'+name+'</a>');
                }
                $('.head-new-nav').append(li);
            }else{
                li.append('<a class="dropdown-toggle '+role+'" href="'+rootUrl+url+'"> '+name+'</a>');
                $('.head-new-nav').append(li);
            }
        }
        $('.head-new-nav').find("a").each(function(){if(this.href.charAt(this.href.length-1)=="#"){this.href = "javascript:;";}else{
            try{
                if(this.href.indexOf("javascript:") > -1){
                    var href = this.href;
                    var token = "javascript:window.open";
                    href = href.substring(href.indexOf(token)+token.length) ;
                    href = "var href = " + href;
                    eval(href);
                    $(this).click(function(){
                        sessionStorage.setItem('v4Url',$(this).data("href"));
                        sessionStorage.setItem('v4Title',$(this).text());
                        window.location.href = "/mind/proxy";
                    }).data("href",href).attr("href","javascript:;");
                }
            }catch(e){$.layerMsg("erp中配置的url有问题\n"+e+"\n"+this.href)}
        }});
    },
    //菜单数据转换
    convertMenuData: function(rows){
        var platform = function(p,u){
            if(u && u.indexOf("javascript:") > -1){
                if(u.indexOf("javascript:window.open2") == -1){
                    return u;
                }else{
                    var href = u;
                    try{
                        var len = "javascript:window.open2".length;
                        href = href.substring(href.indexOf("javascript:window.open2")+len) ;
                        href = "var href = " + href;
                        eval(href);
                        href = href +'" target="_blank';
                    }catch(e){$.layerMsg("erp中配置的url有问题"+e+"\n"+u)}
                    return href;
                }
            }else{
                if(p == 1){
                    return "/mind" + u;
                }else if(p == 3){
                    return "/kiz.php?ctl=inventory&"+ u;
                }else if(p == 4){
                    return "/kiz.php?ctl=basic&"+ u;
                }else if(p == 5){
                    return "/kiz.php?ctl=report&"+ u;
                }else if(p == 6){
                    return "/portalbiz" + u;
                }else{
                    //return "/ww"
                    throw new Error("菜单未知参数p="+p);
                }
            }
        }
        var nodes = [];
        for(var i=0; i<rows.length; i++){
            var row = rows[i];
            if (row.parentId == 0){
                var role=0;
                switch (row.name){
                    case '顾客':
                        role='crm';
                        break;
                    case '订单':
                        role='booking';
                        break;
                    case '营销':
                        role='marketing';
                        break;
                    case '报表':
                        role='report';
                        break;
                    case '设置':
                        role='set';
                        break;
                    case '库存':
                        role='scm';
                        break;
                    case '帮助':
                        role='faq';
                        break;
                };
                nodes.push({
                    id:row.id,
                    role:role,
                    name:row.name,
                    sort:row.sort,
                    parentId:row.parentId,
                    url:platform(row.platform,row.url)
                });
            }
        }
        var toDo = [];
        for(var i=0; i<nodes.length; i++){
            toDo.push(nodes[i]);
        };
        nodes.sort(function(x,y){return x.sort - y.sort});
        while(toDo.length){
            var node = toDo.shift();    // 父节点
            // 得到子节点
            for(var i=0; i<rows.length; i++){
                var row = rows[i];
                if (row.parentId == node.id){
                    var child = {
                        id:row.id,
                        name:row.name,
                        parentId:row.parentId,
                        sort:row.sort,
                        url: platform(row.platform,row.url)
                    };
                    if (node.children){
                        node.children.push(child);
                        if(!node.url || node.url.indexOf("#") >-1){
                            node.url='#';
                        }
                    } else {
                        node.children = [child];
                    }
                    toDo.push(child);
                }
            }
            toDo.sort(function(x,y){return x.sort - y.sort});
        }
        return nodes;
    },

    currentLink: function () {
        var currentUrl=window.location.href;
        currentUrl = currentUrl.substring(currentUrl.indexOf("://")+3);
        currentUrl = currentUrl.substring(currentUrl.indexOf("/"));
        var fun = function(url){
            var flag = false;
            $('.head-new-nav li a').each(function(){
                var href=$(this).attr('href');
                if(href&&href!='#'){
                    if(url==href){
                        var currentLi=$(this).closest('.nav_group')[0];
                        $(currentLi).addClass('current');
                        sessionStorage.setItem('lastUrl',url);
                        flag = true;
                        return false;
                    }
                }
            });
            return flag;
        }
        var flag =  fun(currentUrl);
        if(!flag){
            fun(sessionStorage.getItem('lastUrl'));
        }
    },


    /**
     * @description: 添加最大高度
     * @example: bkeruyun.maxHeight();
     * ? 需要扩张为外部可设置 或通过自定义属性
     */
    maxHeight: function () {
        $("div[data-max-height]").each(function () {
            var defaultHight = window.innerHeight - $("#header").outerHeight() - $("#footer").outerHeight() - 160;
            var maxHeight = $(this).attr("data-max-height") != "" ? $(this).attr("data-max-height") : defaultHight;

            $(this).css({"max-height": maxHeight + 'px', "overflow-x": "hidden", "overflow-y": "auto"});
        });
    },
    showLoading: function () {
        if ($("#loading").length > 0) {
            $("#loading").show();
        }
        else {
            var loading = '<div id="loading"><img src="/app/Tpl/kiz/js/scm_kry/img/loading.gif" /></div>';
            $(document.body).append(loading);
        }
        bkeruyun.showLayer();
    },
    hideLoading: function () {
        $("#loading").hide();
        bkeruyun.hideLayer();
    },
    closePlanPopover: function () {
        //关闭弹框 x号
        $(document).delegate(".close", "click", function (e) {
            var popoverObj = $(e.target).closest(".panel-popover");
            if (popoverObj) {
                bkeruyun.hideLayer();
                popoverObj.hide();
                bkeruyun.clearData(popoverObj);
            }
        });
        //关闭弹框 按钮
        $(document).delegate(".btn-shut-down", "click", function (e) {
            var popoverObj = $(e.target).closest(".panel-popover");
            if (popoverObj) {
                bkeruyun.hideLayer();
                popoverObj.hide();
            }
        });
        //关闭弹框 取消按钮
        $(document).delegate(".btn-cancel", "click", function (e) {
            var popoverObj;
            if ($(e.target).closest(".panel-popover").length > 0) {
                popoverObj = $(e.target).closest(".panel-popover");
            } else if ($(e.target).closest(".popover").length > 0) {
                popoverObj = $(e.target).closest(".popover");
            } else {
                return;
            }
            bkeruyun.hideLayer();
            popoverObj.hide();
            bkeruyun.clearData(popoverObj);
        });
    },
    //form
    checkboxChange: function (element, className) {
        if (element.checked) {
            $(element).parent().addClass(className);
        } else {
            $(element).parent().removeClass(className);
        }
    },
    checkAll: function (e, nameGroup) {
        if (e.checked) {
            //$.layerMsg($("[name='"+ nameGroup+"']:checkbox"));
            $("[name='" + nameGroup + "']:checkbox").not(":disabled").each(function () {
                this.checked = true;
                bkeruyun.checkboxChange(this, 'checkbox-check');
            });
        } else {
            $("[name='" + nameGroup + "']:checkbox").not(":disabled").each(function () {
                this.checked = false;
                bkeruyun.checkboxChange(this, 'checkbox-check');
            });
        }
        bkeruyun.checkboxChange(e, 'checkbox-check');
    },
    associatedCheckAll: function (e, $obj) {
        var flag = true;
        var $name = $(e).attr("name");
        bkeruyun.checkboxChange(e, 'checkbox-check');
        $("[name='" + $name + "']:checkbox").not(":disabled").each(function () {
            if (!this.checked) {
                flag = false;
            }
        });
        $obj.get(0).checked = flag;
        bkeruyun.checkboxChange($obj.get(0), 'checkbox-check');
    },
    isCheckAll: function (e, nameGroup) {
        var flag = true;
        $(":checkbox[name='" + nameGroup + "']").not(":disabled").each(function () {
            if (!this.checked) {
                flag = false;
            }
        });
        if (flag) {
            e.checked = true;
            $(e).parent().addClass("checkbox-check");
        }
    },
    checkboxEvt: function () {
        //关联全选
        $(document).delegate(":checkbox:not([name^='switch'])", "change", function () {
            if ($(this).attr("data-checked-all")) {
                var $obj = $("#" + $(this).attr("data-checked-all"));
                //关联全选操作
                bkeruyun.associatedCheckAll(this, $obj);
            } else {
                //复选框操作
                bkeruyun.checkboxChange(this, 'checkbox-check');
            }

        });
        //全选
        $(document).delegate(":checkbox[data-all]", "change", function () {
            var nameGroup = $(this).attr("data-all");
            //$.layerMsg(nameGroup);
            bkeruyun.checkAll(this, nameGroup);
        });
    },
    radioChange: function (element, className) {
        if (element.checked) {
            var $name = $(element).attr("name");
            $(":radio[name='" + $name + "']").each(function () {
                $(this).parent().removeClass(className);
            });
            $(element).parent().addClass(className);

        }
    },
    radioEvt: function () {
        //radio
        $(document).delegate(".radio > :radio", "change", function () {
            bkeruyun.radioChange(this, 'radio-check');
        });

    },
    //模拟select控件 @param $selects:select元素
    selectControl: function ($selects) {
        var len = $selects.length;
        for (var i = 0; i < len; i++) {

            var $ele = $selects.eq(i);//当前select
            //判断是否有默认值，如果有，设置选中
            if ($ele.attr("data-value")) {
                $ele.val($ele.attr("data-value"));
            }
            var txt = $ele.find("option:selected").text();//选中项文本
            //$.layerMsg(txt);
            if (!$ele.is(".select-style")) {//如果不存在这个class
                var optionObjs = $ele.find("option");
                var html = '<div class="select-control">';
                var ul = '<ul>';
                for (var j = 0, oLen = optionObjs.length; j < oLen; j++) {
                    ul += '<li>' + optionObjs.eq(j).text() + '</li>';
                }

                ul += '</ul>';
                html += '<em>' + txt + '</em></div>' + ul;
                $ele.addClass("select-style").wrap('<div class="select-group"></div>').before(html);
            } else {//如果存在class select-style 重置当前选中的文本
                $ele.parent().find(".select-control > em").text(txt);
            }

        }

    },
    selectControlEvt: function () {
        //select control 效果 点击显示下拉列表
        $(document.body).delegate(".select-control:not('.disabled')", "click", function () {
            // $.layerMsg(0);

            var ulObj = $(this).next("ul");
            //判断ul是否是隐藏的，如果是就显示，否则隐藏
            if (ulObj.is(":hidden")) {
                $(".select-group > ul").hide();
                $(".select-control").removeClass("select-control-arrowtop");
                ulObj.show();
                $(this).addClass("select-control-arrowtop");
            } else {
                ulObj.hide();
                $(this).removeClass("select-control-arrowtop");
            }

        });
        //select control 效果 点击下拉列表选项选中
        $(document.body).delegate(".select-group > ul > li", "click", function () {
            var txt = $(this).text();
            var index = $(this).index();
            var groupObj = $(this).parent().parent();
            var ulObj = $(this).parent();
            // $.layerMsg(0);
            ulObj.prev(".select-control").removeClass("select-control-arrowtop");
            ulObj.prev(".select-control").find("em").text(txt);
            ulObj.hide();//hide ul
            if(groupObj.find('select').length > 0){
                //下拉列表带筛选框时index偏差1
                if(groupObj.find('select').is('.select-filter')){
                    index -= 1;
                }
                groupObj.find('select')[0].selectedIndex = index;//关联select选中
                groupObj.find('select').trigger("change");//触发change事件
            }

        });
        //任意点击隐藏下拉列表
        $(document).delegate("body", "click", function (e) {
            var target = $(e.target);
            if (target.closest(".select-group").length == 0) {
                $(".select-group > ul").hide();
                $(".select-group > .select-control").removeClass("select-control-arrowtop");
            }
        });
    },
    creatSwitch: function ($checkElements) {
        var len = $checkElements.length;
        var browser = navigator.appName;
        //如果是ie浏览器并且小于ie9不加载
        if (browser == "Microsoft Internet Explorer") {
            var b_version = navigator.appVersion;
            var version = b_version.split(";");
            var trim_Version = version[1].replace(/[ ]/g, "");
            if (trim_Version == "MSIE8.0" || trim_Version == "MSIE7.0" || trim_Version == "MSIE6.0") {
                return false;
            }

        }
        for (var i = 0; i < len; i++) {

            var $ele = $checkElements.eq(i);
            var $id = $ele.attr("id");
            if (!$ele.is(".check-ios")) {
                $ele.addClass("check-ios").wrap('<div class="switch-holder"></div>').after('<label for="' + $id + '"></label><span></span>');
            }
            //$ele.after('<label for="' + $id + '"></label><span></span>');
        }
        //$.layerMsg(len);
    },
    //限制字符串的长度 @param obj;@param Ilength number 限制字符长度;@param isCharacter boolean true为按字符串计算 false按字算
    CutStrLength: function (obj, Ilength, isCharacter) {
        var str = obj.value;  //字符串
        var len = 0;            //累计字符长度
        var okLen = 0;         //实际字符长度 中文为1个字符
        //计算字符长度sss

        if (isCharacter != null || isCharacter == true) {
            for (var i = 0, len1 = str.length; i < len1; i++) {
                if (str.charCodeAt(i) > 255) {
                    len += 2;
                } else {
                    len += 1;
                }

                okLen += 1;
                if (len >= Ilength) {
                    break;
                }
            }
            obj.value = str.substring(0, okLen);//超过长度禁止输入
        } else {
            if (obj.value.length > Ilength) {
                obj.value = str.substring(0, Ilength);//超过长度禁止输入
            }
        }
    },
    maxlength: (function () {
        //限制textarea的字符串长度
        $(document).delegate("textarea[maxlength],input[maxlength]", "keyup change", function (e) {
            var len = $(this).attr("maxlength"),
                isCharacter = ($(this).attr("data-character")) ? $(this).attr("data-character") : null;
            bkeruyun.CutStrLength(this, len, isCharacter);
        });
    })(),
    limitInputFormat: function (o, reg) {
        var str = o.value;
        //替换不符合格式的字符为空
        o.value = str.replace(reg, '');
    },
    /**
     * 限制文本框只能输入数字，含小数
     */
    number: (function () {
        $(document).delegate("input[data-type='number']", "keydown", function (event) {
            var value = this.value,
                pos = this.selectionStart,//ie8也支持这个属性
                leftMax = $(this).attr('data-left-max') ? $(this).attr('data-left-max') : 0,
                rightMax = $(this).attr('data-right-max') ? $(this).attr('data-right-max') : 0;
            var index = value.indexOf(".");
            if (event.ctrlKey || event.shiftKey || event.altKey) {
                return false;
            }
            // if(!(event.keyCode==46)&&!(event.keyCode==8)&&!(event.keyCode==37)&&!(event.keyCode==39))
            if ((!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode >= 37 && event.keyCode <= 40)) && (!((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 190))) {
                // event.returnValue=false;
                return false;
            }
            if (event.keyCode === 190 && this.value.indexOf(".") != -1) {
                // event.returnValue=false;
                return false;
            }
            //左侧限制
            if (!!leftMax && index == -1) {
                if (value.length >= leftMax && (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39) && !(event.keyCode == 190))) {
                    return false;
                }
            } else if (!!leftMax && index != -1 && pos < index) {
                var indexLeftNum = (index == 0) ? '' : value.substring(0, index);
                indexLeftNum = (index == 0) ? '' : value.substring(0, index);
                if (indexLeftNum.length >= leftMax && (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39) && !(event.keyCode == 190))) {
                    return false;
                }
            }
            //右侧限制
            if (!!rightMax && index != -1 && pos > index) {
                //            	console.log("right pos=="+pos+"  index=="+index);
                var indexRightNum = (index == value.length - 1) ? '' : value.substring(index + 1);
                //检查小数点右边
                if (indexRightNum.length >= rightMax && (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39) && !(event.keyCode == 190))) {
                    return false;
                }
            }
        });
    })(),
    /**
     * 限制文本框只能输入整数
     */
    digits: (function () {
        $(document).delegate("input[data-type='digits']", "keypress keydown keyup", function (event) {
            if (event.ctrlKey || event.shiftKey || event.altKey) {
                return false;
            }
            if ((!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39)) && (!((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105)))) {
                // event.returnValue=false;
                return false;
            }
        });
    })(),
    word: (function () {
        $(document).delegate("input[data-type='word']", "keyup change", function (e) {
            var reg = /\W/g;
            bkeruyun.limitInputFormat(this, reg);
        });
    })(),
    /* scm不同模块特殊字符验证  */
    //TODO===================
    /*title:scm 名称特殊字符验证
     *doc: 只输入字母、中文、数字和特殊字符（）+-_/*&#$【】
     *desc:添加或移除特殊字符以"\字符"的形式
     *     这里的正则是允许输入的字符集合
     * */
    checkName: (function () {
        $(document).delegate("input[data-format='name']", "input propertychange", function (e) {
            //不允许输入的特殊字符集合
            var reg = /[\~\`\·\!\！\@\￥\^\…\——\=\|\{\}\[\]\:\：\;\；\‘\’\"\”\“\<\>\《\》\?\、\，\.\。\？\、\%\\]/g,
                check = this.value.match(reg);
            if (check) {
                var index = this.value.indexOf(check[0]);
                this.value = this.value.replace(reg, "");
                $(this).textFocus(index);
            }
        });
        $(document).delegate("input[data-format='name']", "change", function (e) {
            //输入完成清除为输入法预留的特殊字符
            var reg = /[\'\,]/g;
            if (reg.test(this.value)) this.value = this.value.replace(reg, "");
        });
    })(),
    /*title: scm 商品名称定制的特殊字符验证
     *doc:
     *desc:
     * */
    checkSkuName: (function () {
        $(document).delegate("input[data-format='skuName']", "input propertychange", function (e) {
            //不允许输入的特殊字符集合
            var reg = /[\<\>\"]/g,
                check = this.value.match(reg);
            if (check) {
                var index = this.value.indexOf(check[0]);
                this.value = this.value.replace(reg, "");
                $(this).textFocus(index);
            }
        });
        $(document).delegate("input[data-format='skuName']", "change", function (e) {
            //输入完成清除为输入法预留的特殊字符
            var reg = /[\'\,]/g;
            if (reg.test(this.value)) this.value = this.value.replace(reg, "");
        });
    })(),
    /*title:scm 编码特殊字符验证
     *doc: 只输入字母、数字和特殊字符（）+-_/*&#$【】
     *desc:添加或移除特殊字符以"\字符"的形式
     *     这里的正则是允许输入的字符集合
     * */
    checkCode: (function () {
        $(document).delegate("input[data-format='code']", "input propertychange", function (e) {
            var reg = /[^\a-\z\A-\Z0-9\【\】\$\#\&\*\//\_\-\+\(\)\（\）\']/g, check = this.value.match(reg);
            if (check) {
                var index = this.value.indexOf(check[0]);
                this.value = this.value.replace(reg, "");
                $(this).textFocus(index);
            }
        });
        $(document).delegate("input[data-format='code']", "change", function (e) {
            var reg = /[\'\,]/g;
            if (reg.test(this.value)) this.value = this.value.replace(reg, "");
        });
    })(),
    /*title:scm 自定义规则
     *doc: 自定义检查规则
     *desc:使用data-rule=/[^\@]/g形式指定规则
     * */
    checkRules: (function () {
        $(document).delegate("input[data-format='rule']", "input propertychange", function (e) {
            var data_rule = this.getAttribute('data-rule');
            if (data_rule) {
                var reg = eval(data_rule), check = this.value.match(reg);
                if (check) {
                    var index = this.value.indexOf(check[0]);
                    this.value = this.value.replace(reg, "");
                    $(this).textFocus(index);
                }
            }
        });
    })(),
    /*title:scm 整形数字验证
     *doc: 只允许整形数字输入
     *desc:使用时，在对应的input增加 data-format='int'即可，
     *          增加data-range='{10,100}'可追加数字大小只能为指定范围内数字效果，包括边界值
     * */
    checkInt: (function () {
        $(document).delegate("input[data-format='int']", "input propertychange", function (e) {
            //整数检测,光标不移动
            var reg = /[^\d]/g, temp = this.value, indexPoint = -11, check = this.value.match(reg);
            if (check) {
                indexPoint = this.value.indexOf(check[0]);
                temp = this.value.replace(/[^\d]/g, '');
            }

            //多余且不符合逻辑的0检测,光标直接移到最后
            if (temp.indexOf("0") == 0 && temp.length >= 2) {
                var firstNum = temp.substring(0, 1), nextNum = temp.substring(1, 2);
                if (firstNum == nextNum) temp = "0";
                else temp = temp.substring(1);
            }

            //数字大小限制超过最大值,光标直接移到最后
            var data_range = this.getAttribute('data-range');
            if (data_range) {
                var rangeNum = data_range.replace(/[\{\}\[\]\(\)\（\）]/g, "").split(",");
                if (rangeNum.length == 2) {
                    var leftNum = parseFloat(rangeNum[0]), rightNum = parseFloat(rangeNum[1]);
                    if (parseFloat(temp) < leftNum) temp = leftNum;
                    if (parseFloat(temp) > rightNum) temp = rightNum;
                }
            }
            if (this.value != temp) this.value = temp;
            if (indexPoint != -11) $(this).textFocus(indexPoint);
        });
    })(),
    /*title:scm 浮点型数字验证
     *doc: 允许带小数点的数字，可验证小数点前后的位数，也可验证数字的大小
     *desc:使用时，在对应的input增加 data-format='float'即可，
     *          增加data-limit='{2,3}'可追加小数点前只允许2位数，小数点后只允许3位效果
     *          增加data-range='{10,100}'可追加数字大小只能为指定范围内数字效果，包括边界值
     * */
    checkFloat: (function () {
        $(document).delegate("input[data-format='float']", "input propertychange", function (e) {
            //数字及点号检测,支持。号时不移动光标
            var reg = /[^\d\.\。]/g, temp = this.value, check = this.value.match(reg), indexPoint = -11;
            //特殊字符过滤验证,光标不移动
            if (check) {
                indexPoint = this.value.indexOf(check[0]);
                temp = this.value.replace(reg, "");
            } else {
                var index2 = temp.indexOf("。");
                if (index2 >= 0) temp = temp.replace(/[\。]/g, ".");
            }

            //检查.号是否符合，光标不移动
            var index = temp.indexOf(".");
            if (index == 0) {
                indexPoint = -11;
                temp = temp.length > 1 ? temp.substring(1) : "0.";
            } else if (index > 0) {
                var nextIndex = temp.replace(".", "").indexOf(".");
                if (nextIndex > 0) {
                    indexPoint = -11;
                    var firstStr = temp.substring(0, index + 1), nextStr = temp.substring(index + 1);
                    temp = firstStr + nextStr.replace(/[\.]/g, "");
                }
            }

            //多余且不符合逻辑的0检测,移动光标到最后
            var indexZero1 = temp.indexOf("0"), tempLen = temp.length;
            if (indexZero1 == 0 && temp.length >= 2) {
                var firstNum = temp.substring(0, 1), nextNum = temp.substring(1, 2);
                if (firstNum == nextNum) temp = "0.";
                else if (nextNum != ".") temp = temp.substring(1);

            }

            //左右边界检测,不移动光标
            var data_limit = this.getAttribute('data-limit');
            if (data_limit) {
                var limitNum = data_limit.replace(/[\{\}\[\]\(\)\（\）]/g, "").split(",");
                if (limitNum.length == 2) {
                    var leftNum = parseInt(limitNum[0]), rightNum = parseInt(limitNum[1]);
                    var tempCache = temp.split(".");
                    if (tempCache.length == 2) {
                        temp = tempCache[0].substring(0, leftNum) + ".";//左侧验证
                        temp += tempCache[1].substring(0, rightNum);//右侧验证
                    } else {
                        temp = tempCache[0].substring(0, leftNum);//左侧验证
                    }
                }
            }

            //数字大小限制,移动光标到最后
            var data_range = this.getAttribute('data-range');
            if (data_range) {
                var rangeNum = data_range.replace(/[\{\}\[\]\(\)\（\）]/g, "").split(",");
                if (rangeNum.length == 2) {
                    var leftNum = parseFloat(rangeNum[0]), rightNum = parseFloat(rangeNum[1]);
                    if (parseFloat(temp) < leftNum) temp = leftNum;
                    if (parseFloat(temp) > rightNum) temp = rightNum;
                }
            }
            if (this.value != temp) this.value = temp;
            if (indexPoint != -11) $(this).textFocus(indexPoint);
        });
    })(),
    /*OrderSn:scm 单据编号检查
     *doc: 只输入大写字母、数字和,输入小写字母自动转为大写
     * */
    checkOrderSn: (function () {
        $(document).delegate("input[data-format='sn']", "input propertychange", function (e) {
            var reg = /[^\a-\zA-\Z0-9]/g, check = this.value.match(reg);
            if (check) {
                var index = this.value.indexOf(check[0]);
                this.value = this.value.replace(reg, "");
                $(this).textFocus(index);
            } else {
                var regUpper = /[\a-z]/g, checkUpper = this.value.match(regUpper);
                if (checkUpper) this.value = this.value.toUpperCase();
            }
        });
    })(),
    /*Number:scm 单据编号检查
     *doc: 只输入大写字母、数字和,输入小写字母自动转为大写
     * */
    checkNumber: (function () {
        $(document).delegate("input[data-format='number']", "input propertychange", function (e) {
            var reg = /[^\d]/g, check = this.value.match(reg);
            if (check) {
                var index = this.value.indexOf(check[0]);
                this.value = this.value.replace(reg, "");
                $(this).textFocus(index);
            }
        });
    })(),
    /*checkOther:scm 禁止<和>
     *doc: 不能输入<和>
     * */
    checkOther: (function () {
        $(document).delegate("input,textarea", "input propertychange", function (e) {
            var reg = /[\<\>]/g, check = this.value.match(reg);
            if (check) {
                var index = this.value.indexOf(check[0]);
                this.value = this.value.replace(reg, "");
                $(this).textFocus(index);
            }
        });
    })(),

    //TODO===================
    minInput: (function () {
        $(document).delegate("input[min]", "keyup change", function (e) {
            var min = $(this).attr("min") * 1;
            var num = $(this).val() * 1;
            if (num < min) {
                $(this).val("");
            }
        });
    })(),
    maxInput: (function () {
        $(document).delegate("input[max]", "keyup change", function (e) {
            var max = $(this).attr("max") * 1;
            var num = $(this).val() * 1;
            if (num > max) {
                $(this).val("");
            }
        });
    })(),
    clearData: function (formObj) {
        if ($(formObj).find("form").length > 0) {
            $(formObj).find("form").each(function () {
                this.reset();
            });
        } else {
            return;
        }
        $(formObj).find(":checkbox:not([name^='switch-checkbox'])").each(function () {
            if (this.checked) {
                $(this).parent().addClass("checkbox-check");
            } else {
                $(this).parent().removeClass("checkbox-check");
            }
        });
        $(formObj).find(":radio").each(function () {
            if (this.checked) {
                $(this).parent().addClass("radio-check");
            } else {
                $(this).parent().removeClass("radio-check");
            }
        });
        $(formObj).find("select").each(function () {
            $(this).parent().find("ul > li").eq(0).trigger("click");
        });
        //移除错误提示
        $(formObj).find("label").each(function () {
            if ($(this).is(".wrong")) {
                $(this).removeClass("wrong");
            }
        });
        //移除名称重复的错误提示
        $(formObj).find("div.wrong").each(function () {
            $(formObj).find("div.wrong").remove();
        });
        //重置编辑器内容
        $(formObj).find("div[id^='myEditor']").each(function () {
            var editorId = $(this).attr("id");
            var um = UM.getEditor(editorId);
            um.ready(function () {
                //设置编辑器的内容
                um.setContent('');
            });
        });
        //图片预览 重置图片
        $(formObj).find(".img-responsive").each(function (i) {
            $(this).attr("src", "themes/style/img/default.jpg");
        });
    },
    clearEditor: function (editorObj) {
        var um = UM.getEditor(editorObj);
        //对编辑器的操作最好在编辑器ready之后再做
        um.ready(function () {
            //设置编辑器的内容
            um.setContent('');
        });
    },
    clearValue: (function () {
        //清空搜索框里的数据
        $(document).delegate(".search-box .close", "click", function () {
            var inputObj = $(this).parents(".search-box").find(":text");
            var setVal = $(this).data('val'); //设定特殊值 BUG #13276
            if(setVal == undefined){
                setVal = '';
            }
            inputObj.val(setVal);
            inputObj.blur(); //点击清除以后，输入框验证未执行 BUG #13255
            if (inputObj.is(".datepicker-start") && inputObj.attr('data-for-element')) {
                var endInput = $("#" + inputObj.attr('data-for-element'));
                endInput.val("");
            }
        });
    })(),
    //回调函数 是否为空 返回true或false
    isEmpty: function ($obj) {
        var thisValue = $.trim($obj.val());//输入的信息
        var thisPlaceholder = ($obj.attr("placeholder")) ? $.trim($obj.attr("placeholder")) : "";//占位符文本
        if (!thisValue || thisValue == thisPlaceholder) {
            //如果为空或等于占位符文本
            return true;
        } else {
            //如果不为空
            return false;
        }
    },
    //添加取消错误提示信息
    emptyPrompt: function (flag, $objLabel, className) {
        //判断是否有未填项
        //$.layerMsg($objLabel.html());
        if (flag) {
            if (!$objLabel.hasClass(className)) {//判断是否已存在错误提示信息 如不存在
                $objLabel.addClass(className);//添加错误提示信息
            }
        } else {
            if ($objLabel.hasClass(className)) {//检查是否存在错误提示信息 如存在
                $objLabel.removeClass(className);//移除错误提示信息
            }
        }
    },
    //如果模块有未填项 提示错误信息
    promptWrong: function (flag, $obj, str) {
        var wrongStr = '<span class="wrong">' + str + '</span>';//大标题提示信息
        if (flag) {//有未填项
            //给模块标题栏添加错误提示信息
            if ($obj.find('.wrong').length < 1) {//判断是否已含有错误提示信息
                //添加模块错误提示信息
                $obj.append(wrongStr);
            }

        } else {//填写正确
            //如果已含有错误提示信息 去掉错误提示信息
            if ($obj.find('.wrong').length > 0) {//判断是否已含有错误提示信息
                //如果已含有 将错误提示信息 移除
                $obj.find('.wrong').remove();
            }
        }
    },
    //显示提示信息 3秒后隐藏
    promptMessage: function (msg, data) {
        if ($("#promptMessage").length < 1) {
            var html = '<div id="promptMessage" style="display:none;"></div>';
            $("body").append(html);
        }
        var $obj = $("#promptMessage");
        $obj.html(msg);
        if (data == null) {
            data = {
                left: '50%',
                marginLeft: '-' + $("#promptMessage").outerWidth() / 2 + 'px',
                top: '70%',
                marginTop: '143px'
            }
        }
        $obj.show().css({
            "left": data.left,
            "margin-left": data.marginLeft,
            "top": data.top,
            "margin-top": data.marginTop
        });
        setTimeout(function () {
            $obj.fadeOut();
        }, 3000);
    },
    //日历控件
    datepickerInitialize: (function () {
        // $.layerMsg(new Date()*1 + 1000*60*60*24);
        /**
         data-date-format="yyyy-mm-dd"  //yyyy-mm-dd hh:ii:ss
         data-date-startView="2" //0:'hour' || 1:'day' || 2:'month' || 3:'year' || 4:'decade'
         data-date-minView="2" //默认0
         data-date-maxView="4" //默认4
         data-date-startDate="yyyy-mm-dd" //默认null
         data-date-endDate="yyyy-mm-dd" //默认null
         **/

        $(document).delegate(".datepicker-start", "focus", function () {
            var $this = $(this),
                _format = ($this.attr("data-date-format")) ? $this.attr("data-date-format") : "yyyy-mm-dd",
                _startView = ($this.attr("data-date-startView")) ? parseInt($this.attr("data-date-startView")) : 2,
                _minView = ($this.attr("data-date-minView")) ? parseInt($this.attr("data-date-minView")) : 2,
                _maxView = ($this.attr("data-date-maxView")) ? parseInt($this.attr("data-date-maxView")) : 4,
                _startDate = ($this.attr("data-date-startDate")) ? $this.attr("data-date-startDate") : null,
                _endDate = ($this.attr("data-date-endDate")) ? $this.attr("data-date-endDate") : null;

            $this.datetimepicker({
                format: _format,
                language: 'zh-CN',
                weekStart: 1,
                todayBtn: false,
                autoclose: true,
                todayHighlight: true,
                startView: _startView,
                minView: _minView,
                maxView: _maxView,
                startDate: _startDate,
                endDate: _endDate,
                forceParse: true
            }).on("changeDate", function (ev) {
                var endDayObjId = $this.attr("data-for-element"),
                    _value = $this.val();

                //清除上一次操作;
                if (endDayObjId) {
                    var endValue = $('#' + endDayObjId).val();
                    $('#' + endDayObjId).datetimepicker("remove");
                    //当结束日期为空或者结束日期小于开始日期时才赋值
                    if(endValue == '' || endValue < _value){
                        $('#' + endDayObjId).val(_value);
                    }
                    //$('#' + endDayObjId).attr("data-date-startDate",_value);
                    // console.log(0);
                }
            });
        });

        $(document).delegate(".datepicker-end", "focus", function () {
            var $this = $(this),
                _startValue = $("#" + $this.attr("data-for-element")).val(),
                _format = ($this.attr("data-date-format")) ? $this.attr("data-date-format") : "yyyy-mm-dd",
                _startView = ($this.attr("data-date-startView")) ? parseInt($this.attr("data-date-startView")) : 2,
                _minView = ($this.attr("data-date-minView")) ? parseInt($this.attr("data-date-minView")) : 2,
                _maxView = ($this.attr("data-date-maxView")) ? parseInt($this.attr("data-date-maxView")) : 4,
                _startDate = ($this.attr("data-date-startDate")) ? $this.attr("data-date-startDate") : _startValue,
                _endDate = ($this.attr("data-date-endDate")) ? $this.attr("data-date-endDate") : null;

            if (_startValue) {
                //_startDate = _startValue;
                //console.log(_startDate);
                $this.datetimepicker({
                    format: _format,
                    language: 'zh-CN',
                    weekStart: 1,
                    todayBtn: false,
                    autoclose: true,
                    todayHighlight: true,
                    startView: _startView,
                    minView: _minView,
                    maxView: _maxView,
                    startDate: _startDate,
                    endDate: _endDate,
                    forceParse: true
                });
            }
        });

        //清空开始日期 结束日期为空 不可选
        $(document).delegate(".datepicker-start ~ .close", "click", function () {
            var endDayId = $(this).parents(".search-box").find(".datepicker-start").attr("data-for-element");
            if (endDayId != "") {
                $("#" + endDayId).datetimepicker("remove");
            }
        });

        //清空结束日期 开始日期为空 结束日期不可选
        $(document).delegate(".datepicker-end ~ .close", "click", function () {
            var $endDayObj = $(this).parents(".search-box").find(".datepicker-end");
            var starrDayId = $endDayObj.attr("data-for-element");
            if (starrDayId != "") {
                $("#" + starrDayId).val("");
            }
            $endDayObj.datetimepicker("remove");
        });
    })(),
    //加操作
    plusFun: function ($obj, step, maxNum, evt) {
        var oldNumber = $obj.val() * 1;
        var newNumber;

        //如果当前值是最大值 返回
        if (oldNumber == maxNum) {
            return;
        }
        newNumber = (oldNumber + step) > maxNum ? maxNum : oldNumber + step;

        $obj.val(newNumber);
    },
    //减操作
    minusFun: function ($obj, step, minNum) {
        var oldNumber = $obj.val() * 1;
        var newNumber;

        //如果当前值是最小值 返回
        if (oldNumber == minNum) {
            return;
        }
        newNumber = (oldNumber - step) < minNum ? minNum : oldNumber - step;

        $obj.val(newNumber);
    },
    undoAll: function () {
        $("#undo-all").on("click", function (e) {
            e.preventDefault();
            bkeruyun.clearData($(this).parents('.aside'));

            if (!bkeruyun.isPlaceholder()) {
                JPlaceHolder.init();
            }

        });
    },
    //boolean
    isPlaceholder: function () {
        return 'placeholder' in document.createElement('input');
    },
    /**
     * @description 更多搜索
     * @param e {object}
     * @param moreObjs {jq object}
     * @param txt1 {string}
     * @param txt2 {string}
     */
    searchMore: function (e, moreObjs, txt1, txt2) {
        var txt = $(e).text();
        if (txt == txt1) {
            moreObjs.show();
            $(e).text(txt2);
        } else if (txt == txt2) {
            moreObjs.hide();
            $(e).text(txt1);
        }
    },
    //单选框和单据日期的按钮组合交互
    initCheckBoxDateGroup: function(){
        //初始化
        if($('#orderDay').val() != ''){
            var $input = $('#orderDay');
            var $btn = $input.next();
            var $strong = $input.parents('dd').prev().find('strong');
            $input.prop('disabled',false).removeClass('disabled');
            $btn.prop('disabled',false);
            $('#hasOrderDay').prop('checked',true);
            $strong.removeClass('white').addClass('red');
        }

        $('#hasOrderDay').on('change', function () {
            var $this = $(this);
            var $input = $('#orderDay');
            var $btn = $input.next();
            var $strong = $this.parents('dd').prev().find('strong');
            if($this.prop('checked')){
                $strong.removeClass('white').addClass('red');
                $input.prop('disabled',false).removeClass('disabled');
                $btn.prop('disabled',false);
            }else{
                $strong.removeClass('red').addClass('white');
                $input.val('');
                $input.prop('disabled',true).addClass('disabled');
                $btn.prop('disabled',true);
            }
        });
    }

};
$.ajaxSetup({
    contentType: "application/x-www-form-urlencoded;charset=utf-8",
    complete: function (xhr, textStatus) {
        //session timeout
        if (xhr.status == 911) {
            window.location = _loginUrl;//返回应用首页
            return;
        }
    }
});
var EnumUtil = {};
EnumUtil.getViewValue = function (enumArray, backValue) {
    var e = null;
    for (var i = 0; i < enumArray.length; i++) {
        e = enumArray[i];
        if (e.backValue == backValue) {
            return e.viewValue;
        }
    }
    return backValue;
};

/**
 * 序列化form
 * @param formId
 * @returns
 */
function serializeFormById(formId) {
    beforeSerialize(formId);
    var ret = $("#" + formId).serialize();
    afterSerialize(formId);
    return ret;
}

/**
 * 调用$("#"+formId).serialize()方法前调用，用于兼容查询时ie8,9不支持html5 placeholder问题
 * @param formId
 */
function beforeSerialize(formId) {
    $("#" + formId + " input[placeholder]").each(function (i, e) {
        $this = $(this);
        var placeHolder = $this.attr("placeholder");
        var value = $this.val();
        if (placeHolder == value) {
            $this.attr("isChange", true);
            $this.val("");
        }
    });
}

/**
 * 调用$("#"+formId).serialize()方法后调用，用于兼容查询时ie8,9不支持html5 placeholder问题
 * @param formId
 */
function afterSerialize(formId) {
    $("#" + formId + " input[placeholder]").each(function (i, e) {
        $this = $(this);
        var placeHolder = $this.attr("placeholder");
        var value = $this.val();
        var isChange = $this.attr("isChange");
        if (value == "" && isChange) {
            $this.removeAttr("isChange");
            $this.val(placeHolder);
        }
    });
}

function EnumWrapper(enumJsonString) {
    this.EnumObj = jQuery.parseJSON(enumJsonString);
    for (var name in this.EnumObj) {
        this[name] = this.EnumObj[name];
    }
    if (typeof EnumWrapper._init == "undefined") {
        EnumWrapper.prototype.getViewValue = function (backValue) {
            var enumObj = this.EnumObj;
            for (var propName in enumObj) {
                var obj = enumObj[propName];
                if (obj && obj.backValue == backValue) {
                    return obj.viewValue;
                }
            }
            return backValue;
        };
        EnumWrapper.prototype.getBackValue = function (viewValue) {
            var enumObj = this.EnumObj;
            for (var propName in enumObj) {
                var obj = enumObj[propName];
                if (obj && obj.viewValue == viewValue) {
                    return obj.backValue;
                }
            }
            return backValue;
        };
        EnumWrapper.prototype.getName = function (backValue) {
            var enumObj = this.EnumObj;
            for (var propName in enumObj) {
                var obj = enumObj[propName];
                if (obj && obj.backValue == backValue) {
                    return propName;
                }
            }
            return backValue;
        };
        EnumWrapper.prototype.getViewValueByName = function (name) {
            var enumObj = this.EnumObj;
            for (var propName in enumObj) {
                var obj = enumObj[propName];
                if (propName == name) {
                    return obj.viewValue;
                }
            }
            return name;
        };
        EnumWrapper._init = true;
    }
}
/**
 * 关闭当前窗口
 */
function closeWindow() {
    var browserName = navigator.appName;
    if (browserName == "Netscape") {
        window.open('', '_parent', '');
        window.close();
    }
    else {
        if (browserName == "Microsoft Internet Explorer") {
            window.opener = "whocares";
            window.close();
        }
    }
};
$(function () {
    $(document).delegate("a[href='#'],a[href=''],a[href='javascript:void(0)']", "click", function (e) {
        e.preventDefault();
    });
    //初始化 滚动条事件
    //	$(document).scroll(function(){
    bkeruyun.rolling($("#nav-fixed"));
    //	});
    $('.article-header').scrollspy();

    //初始化链接当前状态
    bkeruyun.currentLink($(".nav > li"));
    //初始化导航
    bkeruyun.showNav($(".nav > li,.dropdown-submenu"), ".dropdown-menu", "open");
    //添加底栏
    bkeruyun.addFooter();
    //初始化select控件
    bkeruyun.selectControl($('select'));
    //初始化select控件事件
    bkeruyun.selectControlEvt();
    //初始化select 筛选控件
    $('select.select-filter').selectFilter();
    //检测浏览器版本 如果是ie8及以下提示信息
    bkeruyun.detectionBrowser();
    //关闭弹框
    bkeruyun.closePlanPopover();
    //checkbox事件
    bkeruyun.checkboxEvt();
    //radio事件
    bkeruyun.radioEvt();
    //开关
    bkeruyun.creatSwitch($(":checkbox[name^='switch-checkbox'],:checkbox[class='switch']"));
    //全部撤销
    bkeruyun.undoAll();
    //单选框和单据日期的按钮组合交互
    bkeruyun.initCheckBoxDateGroup();
    //设置前台计算支持浮点数
    math.config({
        number: 'bignumber', // number| bignumber
        precision: 64        // 保留数字位数
    });
});
//js本地图片预览，兼容ie[6-9]、火狐、Chrome17+、Opera11+、Maxthon3
///*
function PreviewImage(fileObj, fileUrlObj, imgPreviewId, divPreviewId, btnDelete, namegroup) {
    var allowExtention = ".jpg,.bmp,.gif,.png";//允许上传文件的后缀名document.getElementById("hfAllowPicSuffix").value;
    var extention = fileObj.value.substring(fileObj.value.lastIndexOf(".") + 1).toLowerCase();
    var browserVersion = window.navigator.userAgent.toUpperCase();
    if (allowExtention.indexOf(extention) > -1) {
        //$.layerMsg(fileObj.value.substring(fileObj.value.lastIndexOf("\\")+1).toLowerCase());
        if (fileObj.files) {//HTML5实现预览，兼容chrome、火狐7+等
            if (window.FileReader) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById(imgPreviewId).setAttribute("src", e.target.result);
                }
                reader.readAsDataURL(fileObj.files[0]);
                //$.layerMsg(1);
            } else if (browserVersion.indexOf("SAFARI") > -1) {
                $.layerMsg("不支持Safari6.0以下浏览器的图片预览!");
                //document.getElementById(imgPreviewId).style.display = "none";
                //document.getElementById(divPreviewId).innerHTML = "不支持Safari6.0以下浏览器的图片预览!";
            }
            //$.layerMsg(fileObj.files[0]);
            //$("#" + fileUrlObj).val(fileObj.value);
        } else if (browserVersion.indexOf("MSIE") > -1) {
            if (browserVersion.indexOf("MSIE 6") > -1) {//ie6
                document.getElementById(imgPreviewId).setAttribute("src", fileObj.value);
            } else {//ie[7-9]
                fileObj.select();
                if (browserVersion.indexOf("MSIE 9") > -1)
                //fileObj.blur();//不加上这句 document.selection.createRange().text在ie9会拒绝访问
                    fileUrlObj.focus();
                var newPreview = document.getElementById(divPreviewId + "New");
                if (newPreview == null) {
                    newPreview = document.createElement("div");
                    newPreview.setAttribute("id", divPreviewId + "New");
                    newPreview.style.width = document.getElementById(imgPreviewId).width + "px";
                    newPreview.style.height = document.getElementById(imgPreviewId).height + "px";
                    newPreview.style.border = "solid 1px #d2e2e2";
                    //newPreview.style.marginLeft = "140px";
                }
                //$.layerMsg(document.selection.createRange().text);
                newPreview.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale',src='" + document.selection.createRange().text + "')";
                var tempDivPreview = document.getElementById(divPreviewId);
                tempDivPreview.parentNode.insertBefore(newPreview, tempDivPreview);
                tempDivPreview.style.display = "none";
            }
            //$.layerMsg(fileObj.value);
        } else if (browserVersion.indexOf("FIREFOX") > -1) {//firefox
            var firefoxVersion = parseFloat(browserVersion.toLowerCase().match(/firefox\/([\d.]+)/)[1]);
            if (firefoxVersion < 7) {//firefox7以下版本
                document.getElementById(imgPreviewId).setAttribute("src", fileObj.files[0].getAsDataURL());
            } else {//firefox7.0+
                document.getElementById(imgPreviewId).setAttribute("src", window.URL.createObjectURL(fileObj.files[0]));
            }
        } else {
            document.getElementById(imgPreviewId).setAttribute("src", fileObj.value);
        }
        //截取字符串并输出url
        var inputValue = fileObj.value.substring(fileObj.value.lastIndexOf("\\") + 1).toLowerCase();
        fileUrlObj.val(inputValue);
        //如果不为null，执行以下代码
        if (namegroup != null) {
            $(":checkbox[name='" + namegroup + "']").each(function (i) {
                $(this).removeAttr("disabled");
                //$.layerMsg(i);
            });
        }

    } else {
        $.layerMsg("仅支持" + allowExtention + "为后缀名的文件!");
        fileObj.value = "";//清空选中文件
        if (browserVersion.indexOf("MSIE") > -1) {
            fileObj.select();
            document.selection.clear();
        }
        fileObj.outerHTML = fileObj.outerHTML;
    }
    //$("#" + fileUrlObj).val(fileObj.value);
    //如果清空按钮隐藏 显示清空
    if ($(btnDelete).is(":hidden")) {
        btnDelete.show();
    }
}
/**
 * clearPreviewImg      清空预览图片
 * @param               string        urlInput id
 * @param               string        fileInput id
 * @param               string        previewImg id
 * @param               string        defaultUrl url
 * ? 遗留问题 ie滤镜清空
 */
function clearPreviewImg(urlInput, fileInput, previewImg, defaultUrl, imageId) {
    $("#" + urlInput).val("");
    $("#" + fileInput).val("");
    $("#" + previewImg).attr("src", defaultUrl);
    $("#" + fileInput).prev().hide();
    if (imageId) {
        $("#" + imageId).val("");
    }

    //如果是ie 清空滤镜  删除原来的滤镜div再添加默认div
}

$(function () {
        /**
         * 编辑界面离开时提示用户是否离开
         * @param url 返回地址
         */
        $.notifyLeaving = function (url) {
            if (!url) {
                //bkeruyun.promptMessage("返回地址不能为空");
                $.layerMsg("返回地址不能为空", false);
                return false;
            }

            layer.confirm('当前信息已变更，是否退出？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
                window.location.href = url;

                layer.close(index);
            });

            //Message.confirm({title: "提示", describe: "当前信息已变更，是否退出？"}, function () {
            //    window.location.href = url;
            //});
        };

        /**
         * 执行查询
         * @param args 查询参数
         * queryFormId 查询条件表单id
         * dataGridId 数据表格id
         */
        $.doSearch = function (args) {
            var gridId = args.dataGridId.id, toPage = args.toPage, formId = 'queryConditions';
            var arg_form = args.formId;
            if(arg_form && arg_form.id){ //针对一个页面有多个form 可以自定传参
                formId = arg_form.id;
            }
            //fromId = args.queryFormId.id;
            if (toPage == undefined || toPage == '') {
                $('#' + gridId).refresh();
            } else {
                $('#' + gridId).refresh(toPage);
            }

            var query = {};
            query.data = $('#' + formId).serializeArray();
            query.formId = formId;

            sessionStorage.setItem('query',JSON.stringify(query));
        };

        $.setQueryData = function(arrUrl){
            var query = sessionStorage.getItem('query');

            if(query){
                query = JSON.parse(query);

                if($.isInUrl(arrUrl)){
                    var $form = $('#' + query.formId);
                    var data = query.data;

                    $form.find('label.checkbox').removeClass('checkbox-check');
                    $form.find('input:checkbox').prop('checked',false);

                    data.forEach(function(dom){
                        var name = dom.name;
                        var value = dom.value;
                        var $input = $form.find('[name="' + name + '"]');
                        var inputType = $input.attr('type');
                        if($input.length == 0){
                            return;
                        }
                        if(inputType == 'text'){
                            $input.val(value);
                        }else if(inputType == 'checkbox' || inputType == 'radiobox'){
                            $checkbox = $('input[name="' + name + '"][value="' + value + '"]');
                            $checkbox.click();
                        }else if($input[0].tagName == 'SELECT'){
                            var $select = $('[name="' + name + '"]');
                            var $option = $select.find('option[value="' + value + '"]');
                            $option.prop('selected',true);
                            $select.siblings('div.select-control').children().text($option.text());
                        }
                    });
                }else{
                    sessionStorage.removeItem('query');
                }
            }

        };
        $.isInUrl = function(arrUrl){
            var history = sessionStorage.getItem('urlHistory');
            history = JSON.parse(history);
            var lastUrl = history[0];
            var isInUrl = false;
            arrUrl.forEach(function(url){
                if(lastUrl.indexOf(url) > -1){
                    isInUrl = true;
                    return true;
                }
            });
            return isInUrl;
        };
        var urlHistory = sessionStorage.getItem('urlHistory');
        if(!urlHistory){
            urlHistory = [window.location.href];
            sessionStorage.setItem('urlHistory',JSON.stringify(urlHistory));
        }else{
            urlHistory = JSON.parse(urlHistory);
            urlHistory.push(window.location.href);
            if(urlHistory.length > 2){
                urlHistory.shift(0);
            }
            sessionStorage.setItem('urlHistory',JSON.stringify(urlHistory));
        }

        $.doDelete = function (args) {
            //Message.confirm({title: "提示", describe: "是否删除?"}, function () {
            //    args.callback = '$.showMsgAndRefresh';
            //    $.submitWithAjax(args);
            //});

            layer.confirm('是否删除？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
                args.callback = '$.showMsgAndRefresh';
                $.submitWithAjax(args);

                layer.close(index);
            });
        };

        $.doConfirm = function (args) {
            layer.confirm('确认后，单据无法编辑，是否确认？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
                args.callback = '$.showMsgAndRefresh';
                $.submitWithAjax(args);

                layer.close(index);
            });
            //Message.confirm({title: "提示", describe: "确认后，单据无法编辑，是否确认?"}, function () {
            //    args.callback = '$.showMsgAndRefresh';
            //    $.submitWithAjax(args);
            //});
        };



        //列表的反确认函数
        $.doWithdraw = function (args) {
            layer.confirm('反确认后，单据将会变更为已保存状态，是否继续？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
                if(!args.redirectUrl) {
                    args.callback = '$.showMsgAndRefresh';
                } else {
                    args.callback = '$.defaultWithDrawCallback';
                }

                $.submitWithAjax(args);

                layer.close(index);
            });
        };

        //查看页面反确认函数(参考报废的单的按钮参数)
        $.doViewWithdraw = function(args){
            layer.confirm('反确认后，单据将会变更为已保存状态，是否继续？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
                if(!args.callback) args.callback = '$.defaultWithDrawCallback';
                $.submitWithAjax(args);
                layer.close(index);
            });
        }

        $.doClock = function (args) {
            var desc = $.beforeCallback(args);
            if (desc == '') {
                desc = '是否停用？';
            }
            //Message.confirm({title: "提示", describe: desc}, function () {
            //    args.callback = '$.showMsgAndRefresh';
            //    $.submitWithAjax(args);
            //});

            layer.confirm(desc, {icon: 3, title: '提示', offset: '30%'}, function (index) {
                args.callback = '$.showMsgAndRefresh';
                $.submitWithAjax(args);

                layer.close(index);
            });
        };
        $.doUnlock = function (args) {
            var desc = $.beforeCallback(args);
            if (desc == '') {
                desc = '是否启用？';
            }
            //Message.confirm({title: "提示", describe: desc}, function () {
            //    args.callback = '$.showMsgAndRefresh';
            //    $.submitWithAjax(args);
            //});

            layer.confirm(desc, {icon: 3, title: '提示', offset: '30%'}, function (index) {
                args.callback = '$.showMsgAndRefresh';
                $.submitWithAjax(args);

                layer.close(index);
            });
        };

        $.doCopy = function (args) {

            $.doForward(args);
        };

        $.doCopyOrder = function (args) {
            //获取单据id
            var orderIdSelector = args.orderIdSelector || '#id';
            args.postData = {id: $(orderIdSelector).val()};

            $.doForward(args);
        };

        //执行操作前回调
        $.beforeCallback = function (args) {
            var beforeCallback = args.beforeCallback, msg = '';
            if (beforeCallback && $.isFunction(beforeCallback.toFunction())) {
                msg = beforeCallback.toDo(args);
            }
            return msg;
        };

        /**
         * ajax提交数据
         * @param args
         */
        $.submitWithAjax = function (args) {
            var url = args.url, postData = args.postData;
            $.urlAndPostDataCheck(url, postData);

            var callback = args.callback;
            if (!callback) {
                callback = '$.defaultAjaxCallback';
            }
            $.ajax({
                url: url,
                type: args.type || "post",
                data: $.param(postData),
                async: (args.async != false),
                dataType: args.dataType || "json",
                beforeSend: bkeruyun.showLoading,
                success: function (result) {
                    if ($.isFunction(callback.toFunction())) {
                        args.result = result;
                        callback.toDo(args);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    //$.showMsgBar('error', '发生系统错误，请联系管理员！');
                    $.layerMsg('页面好像超时了，刷新试试。', false);
                },
                complete: function (jqXHR, textStatus) {
                    //取消遮罩
                    bkeruyun.hideLoading();
                }
            });
        };

        /**
         * 单据保存
         * @param args
         */
        $.doSave = function (args) {
            $.formSubmitWithAjax(args);
        };

        /**
         * 单据确认
         * @param args
         */
        $.doConfirmOrder = function (args) {
            args.confirm = true;
            args.confirmMsg = {title: "提示", describe: '确认后，单据无法编辑，是否确认？'};
            args.submitCallback = args.submitCallback || '$.msgAndForward';
            $.formSubmitWithAjax(args);
        };

        /**
         * 表单提交
         * @param args
         * @returns {boolean}
         */
        $.formSubmitWithAjax = function (args) {
            var url = args.url;
            if (!url) {
                //bkeruyun.promptMessage("请求地址不能为空");
                $.layerMsg("请求地址不能为空", false);
                return false;
            }

            var formId = args.formId;
            if (!formId) {
                //bkeruyun.promptMessage("表单id不能为空");
                $.layerMsg("表单id不能为空", false);
                return false;
            }

            //执行表单检查前回调
            var status = true;
            var beforeValidateCallback = args.beforeValidate;
            if (beforeValidateCallback && $.isFunction(beforeValidateCallback.toFunction())) {
                status = beforeValidateCallback.toDo();
                if (!status) {
                    return false;
                }
            }

            //args.messages = $.getFormValidateMessages(formId);

            /*
             $form.find('[validateRules]').each(function (index, object) {

             });
             */
            //执行表单检查
            var $form = $('#' + formId);
            var validator = $form.validate({
                debug: true, //提交表单
                messages: args.messages,
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents(".positionRelative").find(".wrong"));
                }
            });

            if (!validator.form()) {
                return false;
            }

            //执行表单检查后回调
            var afterBalidateCaccback = args.afterValidate;
            if (afterBalidateCaccback && $.isFunction(afterBalidateCaccback.toFunction())) {
                status = afterBalidateCaccback.toDo();
                if (!status) {
                    return false;
                }
            }

            //将表格还原为未编辑状态
            var gridId = args.gridId;
            if (gridId) {
                var $grid = $("#" + gridId);

                var ids = $grid.jqGrid('getDataIDs');
                for (var i = 0; i < ids.length; i++) {
                    $grid.jqGrid('restoreRow', ids[i]);
                }
            }

            //执行自定义检查
            var customValidator = args.customValidator;
            if (customValidator && $.isFunction(customValidator.toFunction())) {
                if (!customValidator.toDo(args)) {
                    return false;
                }
            }

            //序列化表格数据
            var gridData = [];
            if (gridId) {
                gridData = $grid.jqGrid('getRowData');
            }

            //生成最后提交数据
            var formData, postData, contentType;

            if (gridId) {
                contentType = "application/json;charset=UTF-8";
                formData = $form.getFormData();
                postData = formData;
                var gridDataId = 'details';
                if (args.gridDataId != undefined && args.gridDataId != '') {
                    gridDataId = args.gridDataId;
                }
                postData[gridDataId] = gridData;
                // postData = JSON.stringify(postData);
            } else {
                contentType = 'application/x-www-form-urlencoded;charset=UTF-8';
                postData = $form.serialize();
            }

            //合并args中的postData
            if (args.postData && typeof args.postData == "object") {
                for (var key in args.postData) {
                    postData[key] = args.postData[key];
                }
            }

            var callback = '$.defaultAjaxCallback';
            if (args.submitCallback) {
                callback = args.submitCallback;
            }
            //确认提示
            if (args.confirm) {
                layer.confirm(args.confirmMsg.describe, {icon: 3, title: args.confirmMsg.title, offset: '30%'},
                    function (index) {
                        layer.close(index);
                        if (args.confirmAfter) {
                            args.confirmAfter.toDo(args);
                        }
                        $.ajax({
                            url: url,
                            type: args.type || "post",
                            data: postData,
                            async: (args.async != false),
                            dataType: args.dataType || "json",
                            contentType: contentType,
                            beforeSend: bkeruyun.showLoading,
                            success: function (result, status, jqXHR) {
                                if ($.isFunction(callback.toFunction())) {
                                    args.result = result;
                                    args.token = jqXHR.getResponseHeader("t"); // 回调函数在$(document).ajaxComplete调用之前就需要token了，故在此取出
                                    callback.toDo(args);
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                //bkeruyun.promptMessage('发生系统错误，请联系管理员！');
                                errorMessage();
                            },
                            complete: function (jqXHR, textStatus) {
                                //取消遮罩
                                bkeruyun.hideLoading();
                            }
                        });
                    }
                );
                //Message.confirm(args.confirmMsg, function () {
                //    $.ajax({
                //        url: url,
                //        type: args.type || "post",
                //        data: postData,
                //        async: (args.async != false),
                //        dataType: args.dataType || "json",
                //        contentType: contentType,
                //        beforeSend: bkeruyun.showLoading,
                //        success: function (result) {
                //            if ($.isFunction(callback.toFunction())) {
                //                args.result = result;
                //                callback.toDo(args);
                //            }
                //        },
                //        error: function (jqXHR, textStatus, errorThrown) {
                //            bkeruyun.promptMessage('发生系统错误，请联系管理员！');
                //        },
                //        complete: function (jqXHR, textStatus) {
                //            //取消遮罩
                //            bkeruyun.hideLoading();
                //        }
                //    });
                //});
            } else {
                var showLoading = bkeruyun.showLoading;
                if (args.isAuto) {
                    showLoading = null;
                }
                $.ajax({
                    url: url,
                    type: args.type || "get",
                    data: postData,
                    async: (args.async != false),
                    dataType: args.dataType || "json",
                    contentType: contentType,
                    beforeSend: showLoading,
                    success: function (result) {
                        if ($.isFunction(callback.toFunction())) {
                            args.result = result;
                            callback.toDo(args);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        //bkeruyun.promptMessage('发生系统错误，请联系管理员！');
                        errorMessage();
                    },
                    complete: function (jqXHR, textStatus) {
                        //取消遮罩
                        bkeruyun.hideLoading();
                    }
                });
            }


        };

        /**
         * 从form中查找有message属性的集合,并返回一个对象
         * @param formId
         * @returns {Object}
         */
        $.getFormValidateMessages = function (formId) {
            var messageQueue = [];
            //根据当前form获取所有有message的标签,并处理验证消息
            $("#" + formId).find("[message]").each(function () {
                var message = $(this).attr("message");
                var property = $(this).attr("name") || $(this).attr("id");

                var entry = {};
                entry[property] = message;
                //var entry = new $.validateMessageObject(property, message);
                messageQueue.push(entry);
            });
            var messageSource = '{' + messageQueue.join(",").toString() + '}';
            return JSON.parse(messageSource);
        };

        $.validateMessageObject = function (property, message) {
            this.property = property;
            this.message = message;
            this.toString = function () {
                return this.property + ":" + this.message;
            };
        };


        /**
         * 执行编辑操作
         * @param args
         */
        $.doEditor = function (args) {
            $.doForward(args);
        };
        /**
         * 执行查看操作
         * @param args
         */
        $.doView = function (args) {
            $.doForward(args);
        };

        /**
         * 页面跳转
         * @param args 参数
         * url：跳转地址
         * postData：传递的参数
         * @returns {boolean}
         */
        $.doForward = function (args) {
            var url = args.url, postData = args.postData;
            if (!$.urlAndPostDataCheck(url, postData)) {
                return false;
            }
            //var token = window.parent.document.getElementById("t").value;
            var token = getToken();
            if (postData) {
                window.location.href = url + '?' + $.param(postData) ;
            } else {
                window.location.href = url ;
            }
        };

        /**
         * 打印
         * @param args 参数
         * url：跳转地址
         * postData：传递的参数
         *
         * */
        $.doPrint = function(args){
            var opt = {
                urlRoot: args.url,
                query: args.postData
            };
            $.print.showPrintDialog(opt);
        };

        /**
         * 导出Excel
         * @param args 参数
         * url：跳转地址
         * postData：传递的参数
         *
         * */
        $.doExport = function(args){
            var url = args.url + '?id=' + args.postData.id;
            window.open(url);
        };

        /**
         * 执行成功并跳转也列表，执行失败只提示
         * @param args
         */
        $.msgAndForward = function (args) {
            var result = args.result;
            if (result.success) {
                //bkeruyun.promptMessage(result.message);
                $.layerMsg(result.message, true);
                args.url = args.forwardUrl;
                $.doForward(args);
            } else {
                if (result.data != '' && result.data != undefined) {
                    //bkeruyun.promptMessage(result.message, result.data + "<br>");
                    $.layerOpen(result.message, result.data);
                } else {
                    //bkeruyun.promptMessage(result.message);
                    $.layerMsg(result.message, false);
                }
            }
        };

        /**
         * 执行成功提示并刷新表格，执行失败只提示
         * @param args
         */
        $.showMsgAndRefresh = function (args) {
            var result = args.result, dataGridId = args.dataGridId;
            var time = (result.message.length / 3) * 1000;
            if (result.success) {
                //$.showMsgBar('success', result.message);
                $.layerMsg(result.message, true);
                $("#" + dataGridId).refresh();
            } else {
                if (result.data != '' && result.data != undefined) {
                    //$.showMsgBar('error', result.message, result.data);
                    $.layerOpen(result.message, result.data);
                } else {
                    //$.showMsgBar('error', result.message);
                    $.layerMsg(result.message, false);
                }
                // 刷新
                if (result.refresh) {
                    $("#" + dataGridId).refresh();
                }
            }
        };

        /**
         * 默认的ajax成功回调，提示操作结果
         * @param rs
         */
        $.defaultAjaxCallback = function (args) {
            var rs = args.result;
            if (rs.success) {
                //bkeruyun.promptMessage("操作成功!");
                //$.showMsgBar('success', "操作成功!");
                $.layerMsg('操作成功', true);
            } else {
                if (rs.data != '' && rs.data != null) {
                    //bkeruyun.promptMessage("操作失败:" + rs.message, rs.data + "<br>");
                    //$.showMsgBar('error', "操作失败: " + rs.message, rs.data);
                    $.layerOpen(rs.message, rs.data);
                } else {
                    //bkeruyun.promptMessage("操作失败:" + rs.message);
                    //$.showMsgBar('error', "操作失败: " + rs.message);
                    $.layerMsg('操作失败：' + rs.message, false);
                }
            }
        };

        /**
         * 默认的反确认成功回调，提示操作结果
         * @param rs
         */
        $.defaultWithDrawCallback = function (args) {
            var rs = args.result;
            if (rs.success) {
                if(args.redirectUrl){
                    $("#withdraw,#btnprint").remove();
                    $.layerMsg('操作成功', true, {end:function(){$.doForward({url:args.redirectUrl,postData:args.postData});},shade: 0.3});
                }else{
                    $("#withdraw,#btnprint").remove();
                    $.layerMsg('操作成功', true);
                }
            } else {
                if (rs.data != '' && rs.data != null) {
                    $.layerOpen(rs.message, rs.data);
                } else {
                    $.layerMsg('操作失败：' + rs.message, false);
                }
            }
        };


        $.urlAndPostDataCheck = function (url, postData) {
            if (!url) {
                //bkeruyun.promptMessage("url地址不能为空");
                $.layerMsg("url地址不能为空", false);
                return false;
            }
            /*if (postData == undefined || postData == '') {
             bkeruyun.promptMessage("提交数据不能为空");
             return false;
             }*/
            return true;
        };

        // 提示信息
        $.showMsgBar = function (type, msg, detail, callBack, removeTime) {
            //removeTime默认值为2000，如果设置为'stop',则不会自动消失。
            if ($('.headerObj', window.parent.document).height() != null) {
                var inIframe = null;
            } else {
                var inIframe = window.parent.document;
            }
            if (!msg) return;
            if (!removeTime) removeTime = 6000;//'stop';
            if (!detail) {
                $("body", inIframe).append("<div class='msgBar'><div class='msgContent " + type + "'><span class='msgContentObj'>" + msg + "</span></div><div class='closeBtn closeBtnObj'></div></div>");
            } else {
                var detailMsg = '';

                if ($.isArray(detail)) {
                    for (var i = 0; i < detail.length; i++) {
                        if (typeof detail[i] === 'string') {
                            detailMsg += ((i + 1) + ': ' + detail[i] + '<br/>');
                        } else if (typeof detail[i] === 'object') {
                            for (key in detail[i]) {
                                detailMsg += (key + ': ' + detail[i][key] + '<br/>');
                            }
                        }
                    }
                } else if (typeof detail === 'string') {
                    detailMsg = detail;
                } else if (typeof detail === 'object') {
                    for (key in detail) {
                        detailMsg += (key + '. ' + detail[key] + '<br/>');
                    }
                }
                $("body", inIframe).append("<div class='msgBar'><div class='msgContent " + type + "'><span class='msgContentObj'>" + msg + "</span><a href='javascript:void(0)' class='detailObj underLine detail'>详细信息</a><div class='msgDetail'>" + detailMsg + "</div></div><div class='closeBtn closeBtnObj'></div></div>");
            }
            var temp = null;
            var msgBar = $('.msgBar:last', inIframe);

            function removeObj() {
                msgBar.fadeOut(500, function () {
                    $(this).remove();
                    if (callBack != '' && callBack != null && callBack != undefined) callBack();
                    clearTimeout(temp);
                });
            }

            msgBar.fadeIn(500);
            if (removeTime != 'stop') {
                temp = setTimeout(removeObj, removeTime + 500);
                msgBar.hover(function () {
                    clearTimeout(temp);
                }, function () {
                    clearTimeout(temp);
                    temp = setTimeout(removeObj, 1500);
                });
            }
            msgBar.find('.closeBtnObj').click(function () {
                removeObj();
            });
            if (detail != '' && detail != undefined) {
                var msgDetail = msgBar.find('.msgDetail');
                msgBar.find('.detailObj').click(function () {
                    if (msgDetail.css('display') == 'none') {
                        msgDetail.slideDown('fast');
                        $(this).addClass('pull');
                    } else {
                        msgDetail.slideUp('fast');
                        $(this).removeClass('pull');
                    }
                });
            }
        };

        $.layerMsg = function (msg, isSuccess, opts) {
            var time;
            try {
                time = ($('<div>' + msg + '</div>').text().length / 4 + 1) * 1000;
                if (!$.isNumeric(time)) {
                    time = (msg.length / 4 + 1) * 1000;
                }
            } catch (err) {
                time = (msg.length / 4 + 1) * 1000;
            }

            var options = {time: time, offset: '40%'};
            if (isSuccess) {
                options = $.extend(true, options, {icon: 1, closeBtn: 1}, opts || {});
            } else {
                options = $.extend(true, options, {icon: 0, /*shade: 0.3, shadeClose: true, */closeBtn: 1}, opts || {});
            }
            layer.msg(msg, options);
        };

        //用于有详情的信息展示
        $.layerOpen = function (title, detail, options) {
            //默认设置
            var opts = {
                type: 1, //page层
                area: ['500px', '300px'],
                title: '提示'
            };

            if (title) {
                opts.title = title;
            }

            //可以扩展或覆盖默认设置
            opts = $.extend(true, opts, options || {});

            var detailMsg = '';

            if ($.isArray(detail)) {
                for (var i = 0; i < detail.length; i++) {
                    if (typeof detail[i] === 'string') {
                        detailMsg += ((i + 1) + ': ' + detail[i] + '<br/>');
                    } else if (typeof detail[i] === 'object') {
                        for (key in detail[i]) {
                            detailMsg += (key + ': ' + detail[i][key] + '<br/>');
                        }
                    }
                }
            } else if (typeof detail === 'string') {
                detailMsg = detail;
            } else if (typeof detail === 'object') {
                for (key in detail) {
                    detailMsg += (key + '. ' + detail[key] + '<br/>');
                }
            }

            var content = '<div style="padding:10px;">' + detailMsg + '</div>';
            opts.content = content;

            layer.closeAll();
            return layer.open(opts);
        };

        //清除错误提示
        $.clearWrong = function (id) {
            $(document).delegate('#' + id, 'input propertychange change', function () {
                var value = $(this).val();
                var text = $(this).text();
                var wrong = $(this).parents('.positionRelative').find('.wrong');
                if (wrong) {
                    var oldHtml = wrong.html();
                    if (value || text) {
                        wrong.html('');
                    } else {
                        wrong.html(oldHtml);
                    }
                }
            });
        }
    }
);

/*tags for auto post, by skiny at 2015.05.12, start*/
$.fn.getFormData = function () {
    var result = {};
    $(this).serializeArray().map(function (v) {
        if (result[v.name] != undefined) {
            if (typeof result[v.name] == "string") result[v.name] = new Array(result[v.name]);
            result[v.name][result[v.name].length] = v.value;
        }
        else result[v.name] = v.value;
    });
    return result;
};

/**
 * 将对象转化为数组，特别处理对象中嵌套的数组
 * @param data
 * @returns {Array}
 */
function objectToArray(data) {
    var result = [];
    $.map(data, function (value, key) {
        var object = {};
        if ($.isArray(value)) {
            var name = key;
            $.map(value, function (value, key) {
                var object = {};
                object.name = name;
                object.value = value;
                result.push(object);
            });
        } else {
            object.name = key;
            object.value = value;
            result.push(object);
        }
    });
    return result;
}

/**
 * 首字母转大写
 * @param str
 * @returns {XML|void|string}
 * @constructor
 */
function UpperFirstLetter(str) {
    return str.replace(/\b\w+\b/g, function (word) {
        return word.substring(0, 1).toUpperCase() + word.substring(1);
    });
}


/**
 * 移除小数点后的0或.0，如“123,456,789.0000”变成“123,456,789”、 “123,000”不变还是“123,000”
 * @param obj
 * @returns {*}
 */
function returnWithoutDecimalZero(obj) {

    var number = (typeof obj == 'string' ? obj : obj.toString());

    if (number.indexOf('.') < 0) {
        return number;
    }

    while (true) {
        if (number.lastIndexOf('.0') == number.length - 2) {
            return number.substring(0, number.length - 2);
        } else if (number.lastIndexOf('0') == number.length - 1) {
            number = number.substring(0, number.length - 1);
        } else {
            return number;
        }
    }
}

/**
 * 自定义的金额表示格式：货币符号￥为前缀，千分位分隔符，删除多余的小数位0（或.0）。如“￥ 123,456,789,000”、“￥ 13.2”
 * @param cellvalue
 * @param options
 * @param rowObject
 * @returns {string}
 */
function customCurrencyFormatter(cellvalue, options, rowObject) {

    if (!cellvalue && cellvalue != 0) {
        cellvalue = '';
    }

    if (typeof cellvalue === 'string' && cellvalue.indexOf('合计') >= 0) {
        return cellvalue;
    }

    var numberstr = (typeof cellvalue == 'string' ? cellvalue : cellvalue.toString());

    //处理负数（影响千分位的计算）
    var minus = numberstr.indexOf('-') == 0 ? '-' : '';
    if (minus === '-') {
        numberstr = numberstr.substring(1);
    }

    numberstr = returnWithoutDecimalZero(numberstr);

    var index = numberstr.lastIndexOf('.');

    var left = index > 0 ? numberstr.substring(0, index) : numberstr;
    var right = index > 0 ? numberstr.substring(index + 1) : '';

    var count = 1;
    for (var pointer = left.length - 1; pointer > 0; pointer--) {
        if (count % 3 == 0) {
            var replace_left = left.substring(0, pointer);
            //var replace = left.substring(pointer, pointer + 1);
            var replace_right = left.substring(pointer + 1);
            var withstr = ',' + left.charAt(pointer);
            left = replace_left + withstr + replace_right;
        }
        count++;
    }

    return "￥" + minus + left + (index > 0 ? '.' : "") + right;
}


/**
 * 自定义的金额反格式化，去除“￥”和“,”
 * @param cellvalue
 * @param options
 * @param rowObject
 * @returns {string}
 */
function customCurrencyUnformatter(cellvalue, options, rowObject) {

    var v = cellvalue;
    while (v && v.indexOf("￥") >= 0) {
        v = v.replace("￥", "");
    }
    while (v && v.indexOf(",") >= 0) {
        v = v.replace(",", "");
    }
    return v;
}

/**
 * xxxx（已停用）
 * @param cellValue
 * @param options
 * @param rowObject
 * @returns {*}
 */
function customDisabledFormatter(cellValue, options, rowObject) {
    if (rowObject.isDisabled == 1) {
        return cellValue + '<span style="color: red">（已停用）</span>';
    }
    return cellValue;
}

/**
 * xxxx（已关闭权限）
 * @param cellValue
 * @param options
 * @param rowObject
 * @returns {*}
 */
function customNoSupplyFormatter(cellValue, options, rowObject) {
    if (rowObject.isSupply == 0) {
        return cellValue + '<span style="color: red">（未开通供应链）</span>';
    } else {
        if (rowObject.isDisabled == 1) {
            return cellValue + '<span style="color: red">（已停用）</span>';
        }
    }
    return cellValue;
}

/**
 * 生成一个输入浮点数的input
 * @param cellvalue
 * @param options
 * @param rowObject
 * @returns {string}
 */
function formatInputNumber(cellvalue, options, rowObject) {
    var colName = options.colModel.name;
    var str = '<input type=\'text\'  style=\'width:100%;height:34px\' autocomplete=\'off\' data-limit=\'{8,5}\'';
    str += 'class=\'text-right number gridInput ' + colName + '\'';
    str += 'data-format=\'float\' placeholder=\'0\' ';
    str += 'id=\'' + options.rowId + '_' + options.colModel.name + '\' ';
    str += 'name=\'' + colName + '\' ';
    if (cellvalue != '' && cellvalue != undefined) {
        str += 'value=\'' + cellvalue + '\' ';
    }
    //设置gridId和rowId
    str += 'row-id=\'' + options.rowId + '\' grid-id=\'' + options.gid + '\'';
    str += '>';
    return str;
}

function formatInputNumberPurchase(cellvalue, options, rowObject) {
    var colName = options.colModel.name;
    var str = '<input type=\'text\'  style=\'width:100%;height:100%\' autocomplete=\'off\' data-limit=\'{8,5}\'';
    str += 'class=\'text-right number gridInput ' + colName + '\'';
    str += 'data-format=\'float\' placeholder=\'0\' ';
    str += 'id=\'' + options.rowId + '_' + options.colModel.name + '\' ';
    str += 'name=\'' + colName + '\' ';
    if (cellvalue != '' && cellvalue != undefined) {
        str += 'value=\'' + cellvalue + '\' ';
    }
    //设置gridId和rowId
    str += 'row-id=\'' + options.rowId + '\' grid-id=\'' + options.gid + '\'';
    str += 'onfocus="inputFocus(this)">';
    return str;
}

/**
 * 返回单元格input的值
 * @param cellvalue
 * @param options
 * @param cell
 * @returns {*}
 */
function unformatInput(cellvalue, options, cell) {
    var value = $(cell).children('input')[0].value;
    return value == '' ? 0 : value;
}

/**
 * 返回单元格select的值
 * @param cellvalue
 * @param options
 * @param cell
 * @returns {*}
 */
function unformatSelect(cellvalue, options, cell) {
    var value = cellvalue;
    if($(cell).find('em').length == 1){
        value = $(cell).find('em').text();
    }
    return value;
}

/**
 * 数量为负数时红色显示
 * @param cellValue
 * @param options
 * @param rowObject
 * @returns {*}
 */
function customMinusToRedFormatter(cellValue, options, rowObject) {
    if(parseFloat(cellValue) < 0){
        return '<span style="color: red">' + cellValue + '</span>';
    }else{
        return cellValue == null ? '' : cellValue;//fix bug 13337
    }
}
/**
 * 返回单元格span里的值
 * @param cellvalue
 * @param options
 * @param cell
 * @returns {*}
 */
function unformatSpan(cellvalue, options, cell) {
    var value = $(cell).text();
    return value == '' ? 0 : value;
}
/**
 * 计算当前单位对应的当前库存，数量为负数时红色显示
 * @param cellValue
 * @param options
 * @param rowObject
 * @returns {*}
 */
function customMinusToRedFormatterWithUnit(cellvalue, options, rowObject) {
    if(rowObject.standardInventoryQty && rowObject.skuConvertOfStandard && rowObject.skuConvert){
        var variables = [];
        variables.push(rowObject.standardInventoryQty, rowObject.skuConvertOfStandard, rowObject.skuConvert);

        var formula = ['standardInventoryQty*skuConvertOfStandard/skuConvert=inventoryQty'];
        cellvalue = scmSkuSelect.opts.dataGridCal.normalCalculate(formula, variables);
    }

    if(parseFloat(cellvalue) < 0){
        return '<span style="color: red">' + cellvalue + '</span>';
    }else{
        return cellvalue == null ? '' : cellvalue;//fix bug 13337
    }
}

String.prototype.toFunction = function () {
    var temp = String(this);
    if (temp && typeof $[temp.replace("$.", "")] == "function") return $[temp.replace("$.", "")];
};
String.prototype.toDo = function (args) {
    var temp = String(this);
    if (temp && typeof $[temp.replace("$.", "")] == "function") {
        var result = $[temp.replace("$.", "")](args);
    }
    //返回回调的值
    if (result) {
        return result;
    }
};


$(function () {
    //event
    var $body = $('body');
    $body.on("click", "[function],[args]", function () {
        var args = $(this).attr("args"),
            perms = $(this).attr("perms"),
            functionDo = $(this).attr("function");
        if (functionDo || args) {
            args.replace(/'/g, "\"");
            try {
                eval("window.tempVal=" + args + ";");
                args = window.tempVal;
            }
            catch (ex) {
                args = {};
            }

            /*if (args.beforeSubmit) {
             if (typeof args.beforeSubmit.toFunction() == "function" && args.beforeSubmit.toDo()) {
             if (functionDo && (typeof functionDo.toFunction() == "function")) {
             functionDo(args);
             }
             //else autoSubmit(args);
             }

             }*/
            if ($.isFunction(functionDo.toFunction())) {
                //eval("(" + functionDo.toFunction(args) + ")");
                functionDo.toDo(args);
            }
            //else autoSubmit(args);
        }
    });

    //防止点击Backspace时页面回退
    $(window).keydown(function (event) {
        var keyCode = event.keyCode;
        var tagName = event.target.tagName.toLowerCase();
        var readonly = $(event.target).attr('readonly');

        if (keyCode == 8) {
            if (tagName === 'body') {
                return false;
            } else if (readonly == 'true' || readonly == 'readonly') {
                return false;
            }
        }
        return true;
    });

    //grid中的输入框在拾取焦点时，将输入值保存到grid的缓存中
    $body.on("blur", ".gridInput", function () {
        var $inputObj = $(this);
        var $gridObj = $('#' + $inputObj.attr('grid-id'));
        var rowId = $inputObj.attr('row-id');
        var data = {};
        data[$inputObj.attr('name')] = $inputObj.val();
        $gridObj.jqGrid('restoreRow', rowId);
        $gridObj.jqGrid('setRowData', rowId, data);
    });

    //初始化token
    initToken();
});
/*tags for auto post, by skiny at 2015.05.12, end*/

/**
 * scm 自动回复光标位置
 */
$.fn.textFocus = function (v) {
    var range, len, v = v === undefined ? 0 : parseInt(v);
    this.each(function () {
        if ($.browser.msie) {
            range = this.createTextRange();
            v === 0 ? range.collapse(false) : range.move("character", v);
            range.select();
        } else {
            len = this.value.length;
            v === 0 ? this.setSelectionRange(len, len) : this.setSelectionRange(v, v);
        }
        this.focus();
    });
    return this;
}
//指定预处理参数选项的函数
$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
    // options对象 包括accepts、crossDomain、contentType、url、async、type、headers、error、dataType等许多参数选项
    // originalOptions对象 就是你为$.ajax()方法传递的参数对象，也就是 { url: "/index.php" }
    // jqXHR对象 就是经过jQuery封装的XMLHttpRequest对象(保留了其本身的属性和方法)
    //var token = window.parent.document.getElementById("t").value;
    var token = getToken();
    //	if(options.contentType.indexOf("json")>0){
    //		var data=options.data;
    //		data = eval('('+data+')');
    //		data.t=token;
    //		options.data=JSON.stringify(data);
    //	}
    options.headers = {"t": token};
});
$(document).ajaxComplete(function (event, request, settings) {
    if(request.getResponseHeader) { // fix bug 17237 【商品原料】进行批量导入报错
        var token_new = request.getResponseHeader('t');
        if (token_new == "-1") {
            $.layerMsg('页面过期，返回到首页！', false);
            setTimeout(function () {
                window.location.href = ctxPath + "/index";
            }, 2000);
        }
        var token = window.parent.document.getElementById("t");
        var token_2 = window.parent.document.getElementById("t_2");
        if (token_new != null) {
            token.value = token_new;
            token_2.value = token_new;

            //更新浏览器token
            updateUrlToken(token_new);
        }
        var unauthorizedException = request.getResponseHeader('unauthorizedException');
        if (unauthorizedException != null && unauthorizedException == '1') {
            $.layerMsg('很抱歉，您没有该页面的访问权限！', false);
        }
    }
});

/**
 * 页面初始化完成后初始化token值
 */
function initToken() {
    var token = window.parent.document.getElementById("t").value;

    if (!token || token.length == 0) {
        $.ajax({
            type: 'get',
            url:  ctxPath + '/token/get?r=' + new Date().getTime(),//fix bug 【12991】 : 点浏览器回退后，再操作可能跳到首页。---由于请求没有真正发出，取的浏览器的缓存造成的
            async: false,
            dataType: 'text',
            success: function (t) {
                token = t;
                window.parent.document.getElementById("t").value = token;
                window.parent.document.getElementById("t_2").value = token;
            },
            error: function (data) {
                $.layerMsg('网络错误', false);
            }
        });
    }

    updateUrlToken(token);
}

/**
 * 获取页面token值
 */
function getToken() {
    var token = window.parent.document.getElementById("t").value;
    if (!token || token.length == 0) {
        token = window.parent.document.getElementById("t_2").value;
    } else {
        window.parent.document.getElementById("t").value = '';
    }

    return token;
}

/**
 * 更新浏览器地址栏的token
 * @param token 新的token
 */
function updateUrlToken(token) {
    var href = location.href;
    var index = href.indexOf("?");

    if (index == -1) {
        return;
    }

    var url = href.substring(0, index + 1);
    var para = href.substring(index + 1);
    para = para.split("&");
    var hasToken = false,
        paraArray = [];

    $.each(para, function(i, n){
        var parameter = n.split("=");
        if (parameter[0] == 't') {
            parameter[1] = token;
            hasToken = true;
        }

        paraArray.push(parameter);
    });

    if (!hasToken) {
        return;
    }

    $.each(paraArray, function(i, n){
        if (i != 0) {
            url += '&';
        }

        url += n[0] + '=' +n[1];
    });

    history.replaceState({}, "更新token", url);
}

/*
 * 批量修改gird的下拉选框
 * tableId: table的ID
 * fCallback: 选中后执行的回调函数
 * -----fCallback的注入参数-------
 * $row: grid行对象
 * selectIndex: 选中的下标
 * selectTxt: 选中的文本
 *
 * create by zhulf 2015-11-19
 * */
$.fn.selectForGridWithCheck = function (tableId, fCallBack, msg) {
    var $selectGroup = this.parent(); //下拉选框组件
    var $selectControl = $selectGroup.children('.select-control');
    //$selectControl.addClass("disabled"); //初期组件禁用
    //gird绑定行点击事件，有checkbox选中时启动
    /*$('body').delegate('#gview_'+ tableId + ' tr', 'click', function(){
     var hasChecked = false;
     var $grid = $('#' + tableId);
     $grid.find('tbody input[type="checkbox"]').each(function(){
     if($(this).prop('checked')){
     hasChecked = true;
     }
     });
     if(hasChecked){
     // 选框启用
     $selectControl.removeClass("disabled");
     }else{
     // 选框禁用
     $selectControl.addClass("disabled");
     }
     });*/

    $selectGroup.delegate('ul > li', 'click', function () {
        var selectIndex = $(this).index();
        var selectTxt = $(this).text();
        var $grid = $('#' + tableId);
        var $gridRows = $grid.find('tbody>tr[role="row"]:not(".jqgfirstrow")'); //取得行对象

        var checkedBox = $grid.find('tbody input[type="checkbox"]:checked');
        if (checkedBox == undefined || checkedBox.length == 0) {
            // 没有勾选的时候提示信息
            $.layerMsg('请选择' + msg + '！', false);
        }

        if ($gridRows && $gridRows.length > 0) {
            $gridRows.each(function () {
                var $row = $(this);
                if (typeof fCallBack == 'function') {
                    fCallBack($row, selectIndex, selectTxt);
                }
            });
        }
    });
};

/*
 * 为下拉组件添加筛选行 $('xxx').selectFilter()
 *create by zhulf 2016/8/9
 * */
$.fn.selectFilter = function () {
    $(this).each(function () {
        var $this = $(this);
        $this.prev().prepend('<li><div class="select-search-box"><input class="form-control" type="text" autocomplete="off"><span class="glyphicon glyphicon-search search-icon"></span></div></li>');

        var $ul = $this.prev(); //ul
        var $selectFilter = $this.prev().find(':text'); //selectFilter
        $ul.find('li:first').on('click', function (e) {
            e.stopPropagation();
        });
        $selectFilter.on('click', function (e) {
            e.stopPropagation();
        });
        $selectFilter.on('keyup', function () {
            $ul.find('li:first').siblings("li").each(function () {
                var $li = $(this);
                if($li.text().indexOf($selectFilter.val()) > -1){
                    $li.show();
                }else{
                    $li.hide();
                }
            });
        });
        $this.siblings('.select-control').on('click', function () {
            var $this = $(this);
            var $ul = $this.next();
            if($ul.is(':hidden')){
                $ul.find(':text').val('');
                $ul.find('li').show();
                setTimeout(function () {
                    $ul.find(':text').focus();
                },10);
            }
        });
    });
};

/*
 * 打印页面弹出框
 * $.print.showPrintDialog(opt)：显示弹出框
 *
 * opt: 参数格式{}
 * 输入:{urlRoot:'print',query: {id: '123',name: 'zhulf'}}
 * 输出:http://localhost:8080/scm_kry/print/print/A4?id=123&name=zhulf
 *
 * create by zhulf 2015-12-29
 * */
(function($){



    var printBox = '<div class="print_toolbar">'
        +'    <div class="print_button" rel="btn-a4">'
        +'        <span class="img-a4"></span>'
        +'        <span>A4</span>'
        +'    </div>'
        +'    <div class="print_button" rel="btn-halve">'
        +'        <span class="img-halve"></span>'
        +'        <span>针式（二等分）</span>'
        +'    </div>'
        +'</div>';

    //命名空间
    $.print = {};
    var _this = $.print;
    //打印方法
    _this.printA4 = function(opt){
        opt.printType = 'A4';
        _this.formatObjToUrl(opt);
    };
    _this.printHalve = function(opt){
        opt.printType = 'stylus';
        _this.formatObjToUrl(opt);
    };
    _this.formatObjToUrl = function(opt){
        var printJsp = opt.urlRoot + '/printPage';
        var url = '';
        var query = opt.query;
        var queryUrl = '';
        var queryBefor = '?';
        for(key in query){
            queryUrl += queryBefor + key + '=' + query[key];
            queryBefor = '&';
        }

        var orderType = $('#orderType').text();
        var date = new Date();
        //yyyy年MM月dd日HH时mm分ss秒
        var time = date.getFullYear() + '年'
            + (date.getMonth() + 1) + '月'
            //+ date.getDay() + '日' //从 Date 对象返回一周中的某一天 (0 ~ 6)。
            + date.getDate() + '日' //从 Date 对象返回一个月中的某一天 (1 ~ 31)。
            + date.getHours() + '时'
            + date.getMinutes() + '分'
            + date.getSeconds() + '秒';

        var title = encodeURIComponent(encodeURIComponent(orderType + time));
        url = opt.urlRoot + '/print/' + opt.printType + '/' + title + '.pdf' + queryUrl;
        printJsp += "?title=" + encodeURIComponent(encodeURIComponent(orderType)) + "&printUrl=" + url;
        //printJsp = encodeURI(printJsp); //转码
        window.open(printJsp);
    };

    //显示打印框
    _this.showPrintDialog = function(opt){
        var index = layer.open({
            title: '请选择打印尺寸',
            type: 1,
            area: ['530px','388px'],
            content: printBox
        });

        $('div[rel="btn-a4"]').on("click", function(){
            _this.printA4(opt);
            layer.close(index);
        });
        $('div[rel="btn-halve"]').on("click", function(){
            _this.printHalve(opt);
            layer.close(index);
        });
    };
})(jQuery);

/**
 * 修改替换地址栏的内容
 * @param newUrl 新的地址，从application context后开始，如：/asn/si/edit
 * @param paraStr 参数字符串，不用包含“?”，如：id=1234&isDisable=true
 */
function replaceUrl(newUrl, paraStr) {
    var search = location.search.replace('?', ''),
        href = location.href,
        oldUrl = '';

    var index = href.indexOf("?");
    if (index == -1) {
        oldUrl = href;
    } else {
        oldUrl = href.substring(0, index);
    }

    var tokenPara = '';
    if (!!search) {
        $.each(search.split("&"), function (i, n) {
            var parameter = n.split("=");
            if (parameter[0] == 't') {
                tokenPara = 't=' + parameter[1];
            }
        });
    }

    var hasToken = false;
    if (!!paraStr) {
        $.each(paraStr.split("&"), function(i, n){
            var parameter = n.split("=");
            if (parameter[0] == 't') {
                hasToken = true;
            }
        });
    }

    if (!!tokenPara && !hasToken) {
        paraStr = !!paraStr ? paraStr + '&' + tokenPara : tokenPara;
    }

    var url = !!newUrl ? ctxPath + newUrl : oldUrl;

    if (!!paraStr) {
        url += '?' + paraStr;
    }

    history.replaceState({}, "更新浏览器地址", url);
}

/**
 * 生成一个input
 * @param cellvalue
 * @param options
 * @param rowObject
 * @returns {string}
 */
function formatInput(cellvalue, options, rowObject) {
    var colName = options.colModel.name;
    var str = '<input type=\'text\'  ';
    str += 'class=\'form-control w185 required gridInput  ' + colName + '\'';
    str += ' data-character=\'true\' maxlength=\'48\' ';
    str += 'id=\'' + options.rowId + '_' + options.colModel.name + '\' ';
    str += 'name=\'' + colName + '\' ';
    if (cellvalue != '' && cellvalue != undefined) {
        str += 'value=\'' + cellvalue + '\' ';
    }
    //设置gridId和rowId
    str += 'row-id=\'' + options.rowId + '\' grid-id=\'' + options.gid + '\'';
    str += '>';
    return str;
}

/**
 * 返回单元格input的值
 * @param cellvalue
 * @param options
 * @param cell
 * @returns {*}
 */
function unformatInputStr(cellvalue, options, cell) {
    var value = $(cell).children('input')[0].value;
    return value;
}

/**
 * 查询页面第一个输入框获得光标，输入框绑定enter事件
 * */
$.setSearchFocus = function(){
    var focusDom = $('.form-control:visible:first');
    if(!focusDom.prop('readonly')){
        focusDom.focus();
    }
    $(window).on('keydown',function(e){
        if(e.keyCode == 13){
            if($('.layui-layer-shade').length == 1 || $(e.target).hasClass("ui-pg-input") || $("body").hasClass('modal-open')){//要排除掉在表格页码框内回车的情况，这时是要跳到指定页;还有modal弹框显示的情况也要排除
                return;
            }
            var searchBtn = $('.aside').find('.btn-search:visible').first();
            searchBtn.click();
        }
    });
};

//显示加载图标
function showLoading(domId){
    var $this = $(domId);
    $loading = $this.children('.iconfont.loading');

    $this.css({color: $this.css('background-color')});
    if($loading.length == 1){
        $loading.css({visibility:'visible'});
    }
}
//隐藏加载图标
function hideLoading(domId){
    var $this = $(domId);
    $this.css({color:'#ffffff'});
    $this.children('.iconfont.loading').css({visibility:'hidden'});
}

/**
 * 按钮权限控制
 */
function bindButtonPermissions() {
    var codes  = $("#button_group_code").val().split(',');
    //测试代码
    // codes = ",scm:button:purchase:asn:quote,scm:button:purchase:asn:add,scm:button:purchase:return:quote,scm:button:purchase:return:add,"
    $("div.article-header").find(".btn-wrap a").each(function (i,v) {
        var code = $(this).attr("code");
        //用逗号分隔，防止含有子编码的情况
        if (code && codes.indexOf(code) < 0) {
            $(this).removeAttrs("function args onclick href");
            $(this).addClass("button-disable");
            $(this).attr('title','沒有'+$(this).text()+'权限')
        }
    })

    $("div.pull-left").find(".panel-item a").each(function (i,v) {
        var code = $(this).attr("code");
        //用逗号分隔，防止含有子编码的情况
        if (code && codes.indexOf(code) < 0) {
            $(this).removeAttrs("function args onclick href");
            $(this).addClass("button-disable");
            $(this).attr('title','沒有'+$(this).text()+'权限')
        }
    })
}

function errorMessage(message) {
    if(!message) {
        $.layerMsg("小on悄悄说道：网络可能出错了或者退出重新登录下！", false);
    }
    else {
        $.layerMsg(message, false);
    }
}