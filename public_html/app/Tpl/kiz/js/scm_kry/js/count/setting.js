;
/**
 * 库存设置
 * @type {{}}
 */
var setting = {
    opts : {
        urlRoot:ctxPath+ '/cc/setting',
        saveUrl: '/save'
    },

    /**
     * 统一的初始化入口
     */
    init: function(args) {
        this.opts = $.extend(true, this.opts, args || {});

        this.initCcModel();
        this.handleSave();
    },

    /**
     * 初始化盘点方式相关功能
     */
    initCcModel: function() {
        var titleId = $("#cc-mode .td-to-holder .radio-on").attr("data-title-id");
        $("#" + titleId).show();

        $("#cc-mode .td-to-holder span").on("click", function(){
            var _this = this;
            if ($(this).hasClass("radio-on")) {
                return;
            }

            $(_this).addClass("radio-on").siblings().removeClass("radio-on");
            var titleId = $(_this).attr("data-title-id");
            $("#" + titleId).show().siblings().hide();
        });
    },

    handleSave: function() {
        var _this = this;

        $("#btnSave").on("click", function() {
            var $ccMpdel= $("#cc-mode .td-to-holder .radio-on");
            var ccModelId = $ccMpdel.attr("data-id");
            var ccModelType = $ccMpdel.attr("data-type");
            var ccModelValue = $ccMpdel.attr("data-value");
            var ccModelVersion = $ccMpdel.attr("data-version");

            var ccMode = {
                id: ccModelId,
                settingItemType: ccModelType,
                settingItemValue: ccModelValue,
                version: ccModelVersion
            };

            var settings = [];
            settings.push(ccMode);

            bkeruyun.showLoading();
            $.ajax({
                type: "POST",
                url: _this.opts.urlRoot + _this.opts.saveUrl + "?r=" + new Date().getTime(),
                async: false,
                data: JSON.stringify(settings),
                dataType: "json",
                contentType: "application/json",
                success: function (result) {
                    if (result.success) {
                        var data = result.data;
                        $.each(data, function(i, n) {
                            $("span[data-type='" + n.settingItemType +"']").attr({"data-id": n.id, "data-version": n.version});
                        });

                        $.layerMsg("保存成功", true);
                    } else {
                        if (result.flag == 1) {
                            $.layerMsg("数据已过期", false, {
                                end: function() {
                                    window.location.reload(true);
                                }
                            });
                        } else {
                            $.layerMsg("保存失败", false);
                        }
                    }
                    bkeruyun.hideLoading();
                },
                error: function () {
                    bkeruyun.hideLoading();
                    $.layerMsg("网络错误", false);
                }
            })
        });
    }
};