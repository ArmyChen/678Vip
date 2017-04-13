//$(function () {
//    var toCalculate = new ToCalculate();
//    //上一步按钮
//    var $btnPrev = $("#btnPrev");
//    //下一步按钮
//    var $btnNext = $("#btnNext");
//    //基本信息panel
//    var $inventoryForm = $("#inventoryForm");
//    //单据作业panel
//    var $documentsJob = $("#documentsJob");
//    //from wm
//    var $fromWm = $("#removeWarehouse");
//    //to wm
//    var $toWm = $("#moveWarehouse");
//    //基本信息，单据作业导航
//    var $basePanel = $("#basePanel");
//    var $detailsPanel = $("#detailsPanel");
//    //transferOrderId
//    var $transferOrderId = $("#transferOrderId");
//    var $transferOrderNo = $("#transferOrderNo");
//
//    //替换掉内容中 斜杠（/） 和空格
//    //var skuStr = $("#skuList").val().replace(/\//g, "-");
//    //skuStr = skuStr.replace(/\s+/g, '');
//
//    //下一步按钮点击
//    $btnNext.on("click", function () {
//        //$("#inventoryForm").submit();
//        //检查是否验证通过
//        var flag = $inventoryForm.valid();
//
//        if (flag) {
//            showDocumentsJob();
//            $(this).hide();
//            $btnPrev.show();
//            $basePanel.removeClass("current");
//            $detailsPanel.addClass("current");
//        } else {
//            bkeruyun.promptMessage("部分资料错误或必填栏位为空，请检核！");
//        }
//    });
//    //上一步按钮点击
//    $btnPrev.on("click", function () {
//        showBaseInfo();
//        $(this).hide();
//        $btnNext.show();
//        $detailsPanel.removeClass("current");
//        $basePanel.addClass("current");
//    });
//
//    $inventoryForm.validate({
//        errorPlacement: function (error, element) {
//            error.appendTo(element.parents(".positionRelative").find(".wrong"));
//        },
//        //只验证不提交 需要提交时注销这段代码
//        debug: true
//    });
//    //分类折叠
//    $(document).delegate("#classificationAll .icon-folding", "click", function () {
//        if ($(this).is(".open")) {
//            $(this).removeClass("open");
//            $(this).parent().removeClass("open");
//        } else {
//            $(this).addClass("open");
//            $(this).parent().addClass("open");
//        }
//    });
//    //全选操作
//    setCheckedAll();
//
//    //移除仓库不能和移入库相同
//    warehouseInOff("moveWarehouse", "removeWarehouse");
//    warehouseInOff("removeWarehouse", "moveWarehouse");
//
//    //基本信息 单据作业切换
//    $("#scmBreadCrumbs > li").on("click", function () {
//        var index = $(this).index();
//        switch (index) {
//            case 0:
//                showBaseInfo();
//                $(this).addClass("current").siblings().removeClass("current");
//                break;
//            case 1:
//                //检查是否验证通过
//                var flag = $inventoryForm.valid();
//                if (flag) {
//                    showDocumentsJob();
//                    $(this).addClass("current").siblings().removeClass("current");
//                } else {
//                    bkeruyun.promptMessage("部分资料错误或必填栏位为空，请检核！");
//                }
//                break;
//        }
//
//    });
//
//
//    //计算
////    toCalculate.unitCalculate();
//
//    var $tbody = $("#tbody-1");
//    //已添加商品id
//    var skuIds = [];
//    //添加商品
//    $("#btnAdd").click(function () {
//        var ids = [];
//        var $skuSelect = $('input[name^="dish"]:checked');
//        var classObjs = $(".classificationCol");//所有分类
//        var classLen = classObjs.length;
//        if ($skuSelect.length == 0) {
//            bkeruyun.promptMessage("请勾选商品，再添加！");
//            return;
//        }
//
//        $skuSelect.each(function (index) {
//            var existIndex = skuIds.indexOf($(this).val());
//            if (existIndex == -1) {
//                skuIds.push($(this).val());
//                ids.push($(this).val());
//            }
//            //设置勾选
//            $(this).attr("disabled", "disabled");
//            $(this).parent("label").addClass("checkbox-disable");
//        });
//        //关联分类 全选
//        for (var i = 0; i < classLen; i++) {
//            var classCheckbox = classObjs.eq(i).find(":checkbox").get(0);
//            if (classCheckbox.checked) {
//                $(classCheckbox).attr("disabled", "disabled").parent("label").addClass("checkbox-disable");
//            }
//        }
//        if ($("#checkboxsAll").get(0).checked) {
//            $("#checkboxsAll").attr("disabled", "disabled").parent("label").addClass("checkbox-disable");
//        }
//
//        if (ids.length == 0) {
//            return;
//        }
//
//        //load sku info
//        var row = '';
//        $.post(ctx + "/transferorder/getSkuInfo", {ids: ids.join(",")}, function (data) {
//            jQuery.each(data, function (i, value) {
//                row += '<tr id="' + value.id + '">';
//                row += '<td>' + value.skuTypeName + '</td>';
//                row += '<td>' + value.skuCode + '</td>';
//                row += '<td>' + value.skuName + '</td>';
//                row += '<td>' + value.uom + '</td>';
//                row += '<td><span class="unit">' + value.price + '</span></td>';
//                row += '<td><input id="qty-' + value.id + '" type="text" class="w80 text-center number" autocomplete="off" data-left-max="8" data-right-max="5" value="" data-type="number" placeholder="0"></td>';
//                row += '<td><span class="priceTotal">0</span></td>';
//                row += '<td><span class="intTotal">0</span></td>';
//                row += '<td><span class="icon-cancellation-coupons"></span></td>';
//                row += '</tr>';
//            });
//            $tbody.append(row);
//        }, "json")
//    });
//
//    //删除一条数据
//    $(document).delegate("#tbody-1 .icon-cancellation-coupons", "click", function () {
//        var trObj = $(this).parents("tr");
//        var trObjId = trObj.attr("id");
//        trObj.remove();
//        setClassAvailable(trObjId);
////        setCheckedAll();
//        toCalculate.totalCalculate();
//        //删除当前页面已存在商品id
//        var existIndex = skuIds.indexOf(trObjId);
//        skuIds.splice(existIndex, 1);
//    });
//
//    //删除全部商品
//    $("#cancelAll").click(function () {
//        var skuInfos = $tbody.find("> tr");
//        if (skuInfos.length > 0) {
//            Message.confirm({title: "提示", describe: "确定删除全部商品"}, function () {
//                $("#classificationAll :checkbox").each(function () {
//                    if (this.checked) {
//                        $(this).removeAttr("checked").parent("label").removeClass("checkbox-check");
//                    }
//                    if (this.disabled) {
//                        $(this).removeAttr("disabled").parent("label").removeClass("checkbox-disable");
//                    }
//                });
//                skuInfos.remove();
//                toCalculate.totalCalculate();
//                //TODO 重置商品分类可用
//                //setCheckedAll();
//            });
//        }
//        skuIds = [];
//    });
//
//    //保存
//    $("#btnSave").click(function () {
//        //检查移库数量是否有效
//        var qty = $.validateQty(true);
//        if (qty.length == 0) {
//            bkeruyun.promptMessage("移库数量不能全为0");
//            return;
//        }
//        $.commit(ctx + '/transferorder/save', qty);
//    });
//
//    //修改
//    $("#btnUpate").click(function () {
//        //检查移库数量是否有效
//        var qty = $.validateQty(true);
//        if (qty.length == 0) {
//            bkeruyun.promptMessage("移库数量不能全为0");
//            return;
//        }
//        $.commit(ctx + '/transferorder/update', qty);
//
//    });
//
//    //确认
//    $("#btnConfirm").click(function () {
//        var qty = $.validateQty(false);
//        if (qty.length == 0) {
//            bkeruyun.promptMessage("移库数量不能全为0");
//            return;
//        }
//        Message.confirm({title: "提示", describe: "确认后，单据无法编辑，是否确认"}, function () {
//            if ($.commit(ctx + '/transferorder/confirm', qty)) {
//                window.location.href = ctx + "/transferorder/index";
//            }
//        });
//    });
//
//    //检查移库单数量
//    $.validateQty = function (permitZero) {
//        var qty = [];
//        var pass = false;
//        $("input[id|='qty']").each(function (index, obj) {
//            if (obj.value > 0) {
//                pass = true;
//            }
//            if (permitZero) {
//                qty.push({skuId: obj.id.substr(4), planMoveQty: obj.value == '' ? 0 : obj.value});
//            } else if (obj.value > 0) {
//                qty.push({skuId: obj.id.substr(4), planMoveQty: obj.value});
//            }
//        });
//        if (!pass) {
//            qty = [];
//        }
//        return qty;
//    };
//
//    $.buildData = function () {
//        var order = {};
//        order.id = $transferOrderId.val();
//        order.transferOrderNo = $("#transferOrderNo").val();
//        order.fromWmId = $fromWm.val();
//        order.toWmId = $toWm.val();
//        order.details = $.validateQty(true);
//        return order;
//    };
//
//    $.successCallBack = function () {
//        $transferOrderId.val(result.data.id);
//        $transferOrderNo.val(result.data.transferOrderNo);
//    };
//
//
//    $.commit = function (url, detail) {
//        var order = {};
//        order.id = $transferOrderId.val();
//        order.transferOrderNo = $("#transferOrderNo").val();
//        order.fromWmId = $fromWm.val();
//        order.toWmId = $toWm.val();
//        order.details = detail;
//        var opResult = true;
//        $.ajax({
//            url: url,
//            type: "post",
//            async: false,
//            data: JSON.stringify(order),
//            dataType: "json",
//            contentType: "application/json",
//            success: function (result) {
//                if (result.success) {
//                    $transferOrderId.val(result.data.id);
//                    $transferOrderNo.val(result.data.transferOrderNo);
//                    $("#btncancle").text("返回");
//                } else {
//                    opResult = false;
//                }
//                bkeruyun.promptMessage(result.message);
//            },
//            error: function () {
//                bkeruyun.promptMessage("网络错误");
//            }
//        });
//        return opResult;
//    };
//});

/**
 * 分类全选
 * @Method setCheckedAll
 */
function setCheckedAll() {
    $("#checkboxsAll").change(function () {
        var that = this;
        if (that.checked) {
            $("#classificationAll :checkbox").not(":disabled").each(function () {
                this.checked = true;
                bkeruyun.checkboxChange(this, 'checkbox-check');
            });
        } else {
            $("#classificationAll :checkbox").not(":disabled").each(function () {
                this.checked = false;
                bkeruyun.checkboxChange(this, 'checkbox-check');
            });
        }
    });
    //关联全选
    $(document).delegate("#classificationAll :checkbox", "change", function () {
        var flag = true;
        var allCheckbox = $("#checkboxsAll").get(0);
        var checkboxs = $(":checkbox[name^='dish']").not(":disabled");
        checkboxs.each(function () {
            if (!this.checked) {
                flag = false;
            }
        });
        allCheckbox.checked = flag;
        bkeruyun.checkboxChange(allCheckbox, 'checkbox-check');
    });
}

//显示基本信息
function showBaseInfo() {
    $("#baseInfo,#btnNext").show();
    $("#documentsJob,#btnPrev,#btnSave,#btnConfirm").hide();
}
//显示单据作业
function showDocumentsJob() {
    $("#baseInfo,#btnNext").hide();
    $("#documentsJob,#btnPrev,#btnSave,#btnConfirm").show();
}

//分类搜索
function classSearch(event) {
    var keyObj = document.getElementById("keyWord");
    var keyWord = $.trim(keyObj.value);
    // alert(keyObj.value);
    if (event.keyCode == 13) {
        // var keyWord = $.trim($("#code").val());
        // var keyWord = '拿铁';

        var searchScope = $(".classificationCol :checkbox");
        var classObjs = $(".classificationCol");//所有分类
        var classLen = classObjs.length;
        var checkboxsAll = $("#checkboxsAll");
        //console.log("keyWord==" + keyWord);
        var flag = false;//是否包含关键字
        for (var i = 0; i < classLen; i++) {
            var count = 0;
            classObjs.eq(i).find("ul li").each(function (i) {
                var txt = $.trim($(this).find("label > em").text());
                var inputObj = $(this).find("input");
                if (i > 0) {
                    if (txt.indexOf(keyWord) != -1) {
                        $(this).removeClass("notScope");
                        inputObj.removeAttr("disabled");
                        flag = true;
                        count++;
                    } else {
                        //console.log("txt==" + txt);
                        $(this).addClass("notScope");
                        inputObj.attr("disabled", "disabled");
                    }
                }
                if (inputObj.get(0).checked) {
                    inputObj.removeAttr("checked").parent().removeClass("checkbox-check");
                }
            });
            if (count === 0) {
                classObjs.eq(i).addClass("notScope");
            } else {
                classObjs.eq(i).removeClass("notScope");
            }
        }
        //检查全选是否被选中
        if (checkboxsAll.get(0).checked) {
            checkboxsAll.removeAttr("checked").parent().removeClass("checkbox-check");
        }
        if (!flag) {
            $("#classificationAll").hide();
            //bkeruyun.promptMessage("未找到您要的资料");
            $.layerMsg("未找到您要的资料", false);
        } else {
            $("#classificationAll").show();
        }
        //alert(0);
    }
}

/**
 * 删除数据 重置数据删除后所在分类为可用
 * @Method setClassAvailable
 * @id {string} 被删除数据的id
 */
function setClassAvailable(id) {
    var classObjs = $(".classificationCol");//所有分类
    var classLen = classObjs.length;
    var checkboxsAll = $("#checkboxsAll");

    for (var i = 0; i < classLen; i++) {
        // var count = 0;
        classObjs.eq(i).find("ul li").each(function (i) {
            var inputObj = $(this).find("input");
            var classInputObj = $("#" + inputObj.attr("data-checked-all"));
            var dataId = $.trim(inputObj.attr("data-id"));
            if (i > 0) {
                if (id === dataId) {
                    inputObj.removeAttr("disabled").removeAttr("checked");
                    inputObj.parent("label").removeClass("checkbox-disable checkbox-check");
                    if (classInputObj.get(0).checked) {
                        classInputObj.removeAttr("checked").removeAttr("disabled").parent("label").removeClass("checkbox-disable checkbox-check");
                    }
                    if (checkboxsAll.get(0).checked) {
                        checkboxsAll.removeAttr("checked").removeAttr("disabled").parent("label").removeClass("checkbox-disable checkbox-check");
                    }
                }
            }
        });
    }
}

//计算
function ToCalculate(opts) {
    this.init(opts);

}
ToCalculate.prototype = {
    init: function (opts) {
        this.opts = $.extend({}, this.defaultOpts, opts || {});
        //调用需要初始化的业务逻辑方法
//        this.PublickFunc();
        //初始化所有的事件
        this._handleEvent();
    },
    /**
     * 事件管理，将所有的事件绑定都放在此处
     * @private
     */
    _handleEvent: function () {
        var _this = this;
        //keydown 时检查输入
//        $(document).delegate("#" + _this.opts.tableId + " :text" + _this.opts.unitEles.price + ",#" + _this.opts.tableId + " :text" + _this.opts.unitEles.number, "keydown", function (event) {
//            var value = this.value,
//                pos = this.selectionStart,//ie8也支持这个属性
//                leftMax = $(this).attr(_this.opts.leftMax) ? $(this).attr(_this.opts.leftMax) : 100,
//                rightMax = $(this).attr(_this.opts.rightMax) ? $(this).attr(_this.opts.rightMax) : 100;
//            if (!_this._checkInputFunc(event, value, pos, leftMax, rightMax)) {//alert(0);
//                return false;
//            }
//            ;
//
//        });
        //keyup 计算
        $(document).delegate("#" + _this.opts.tableId + " :text" + _this.opts.unitEles.price + ",#" + _this.opts.tableId + " :text" + _this.opts.unitEles.number, "keyup", function (event) {
            var obj = $(this).parents("tr");
            _this.unitCalculate(obj);//单条数据合计
            _this.totalCalculate();//合计计算
        });
    },
    /**
     * 默认参数，将所有默认参数都放在此处
     */
    defaultOpts: {
        tableId: "tbody-1",//操作表格的id
        unitEles: {price: ".unit", number: ".number", total: ".priceTotal"},//单价计算elements
        totalEles: [{total: "#numberAll", item: ".number"}, {total: "#priceTotalAll", item: ".priceTotal"}],//合计计算elements
//        leftMax: 'data-left-max',//HTML5 data格式前缀，用于获取小数点左侧最大位数
//        rightMax: 'data-right-max'//HTML5 data格式前缀，用于获取小数点右侧最大位数
    },
    /**
     * 检查输入
     * @Method _checkInputFunc
     * @event {event}
     * @value {number}
     * @return {boolean} true可以输入；false不可以输入
     */
//    _checkInputFunc: function (event, value, pos, leftMax, rightMax) {
//        var _this = this;
//        var flag = true;
//
//        if (value.indexOf(".") != -1) {
//            var index = value.indexOf(".");
//            var indexLeftNum = (index == 0) ? '' : value.substring(0, index);
//            var indexRightNum = (index == value.length - 1) ? '' : value.substring(index + 1);
//            //检查光标的位置
//            if (pos > index) {
//                //检查小数点右边
//                if (indexRightNum.length >= rightMax && (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39) && !(event.keyCode == 190))) {
//                	flag = false;
//                }
//            } else {
//                //检查小数点左边
//                if (indexLeftNum.length >= leftMax && (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39) && !(event.keyCode == 190))) {
//                	flag = false;
//                }
//
//            }
//        } else {
//            if (value.length >= leftMax && (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39) && !(event.keyCode == 190))) {
//                flag = false;
//            }
//        }
//        return flag;
//    },
    /**
     * 数字修复
     * @Method _mathRepair
     * @value {number/string}
     */
    _mathRepair: function (value) {
        var number = 0;
        if (value === '') {
            number = 0;
        } else {//如果以.开始默认为0.;
            if (value.indexOf(".") == 0) {
                number = Math.abs(0 + '' + value);
            } else {
                number = Math.abs(value * 1);
            }
        }
        return number;
    },
    /**
     * 相应的业务函数
     *
     */
    /**
     * 减法
     * @Method subtractionFun
     * @number {number}
     * @totalNum {number}
     * @return {number}
     */
    subtractionFun: function (number, totalNum) {
        return math.subtract(math.bignumber(totalNum), math.bignumber(number));
        //return math.subtracttotalNum,number);
        //return Math.round(parseFloat(totalNum - number));
    },
    /**
     * 乘法
     * @Method multiplicationFun
     * @number {number} unitEles.number
     * @unitPrice {number} unitEles.price
     * @return {number}
     */
    multiplicationFun: function (number, unitPrice) {
        return math.multiply(math.bignumber(number), math.bignumber(unitPrice));
        //return math.multiply(number, unitPrice);
        //return Math.round(parseFloat(number * unitPrice));
    },
    /**
     * 单价计算
     * @Method unitCalculate
     * @itemObj $obj 当前条目对象
     */
    unitCalculate: function (itemObj) {
        var _this = this;
        var priceObj = itemObj.find(_this.opts.unitEles.price),
            price = (priceObj.get(0).nodeName.toLowerCase() === "input") ? priceObj.val() : priceObj.text(),
            numberObj = itemObj.find(_this.opts.unitEles.number),
            number = (numberObj.get(0).nodeName.toLowerCase() === "input") ? numberObj.val() : numberObj.text();
            totalObj = itemObj.find(_this.opts.unitEles.total),
            total = _this.multiplicationFun(_this._mathRepair(number), _this._mathRepair(price));
        if (totalObj.get(0).nodeName.toLowerCase() === "input") {
            totalObj.val(total);
        } else {
            totalObj.text(total);
        }
    },
    /**
     * 合计
     * @Method totalCalculate
     */
    totalCalculate: function () {
        var _this = this;
        for (var i = 0, len = _this.opts.totalEles.length; i < len; i++) {
            var totalObj = $(_this.opts.totalEles[i].total);
            var items = $("#" + _this.opts.tableId).find(_this.opts.totalEles[i].item);
            var sum = 0;
            for (var j = 0, jLen = items.length; j < jLen; j++) {
                var value = (items.get(0).nodeName.toLowerCase() === "input") ? items.eq(j).val() : items.eq(j).text();
                if (value == '') {
                    value = 0;
                }
                sum = math.add(math.bignumber(sum), math.bignumber(value));
                //sum += _this._mathRepair(value);
            }
            if (totalObj.get(0).nodeName.toLowerCase() === "input") {
                totalObj.val(sum);
            } else {
                totalObj.text(sum);
            }
        }
    }
};
/**
 * 移入/移出仓库，移出仓库被选中项不在移入仓库显示
 * @Method warehouseInOff
 * @inObjId {string} 移入仓库ID
 * @offObjId {string} 移出仓库ID
 */
function warehouseInOff(inObjId, offObjId) {
    var inOptions = $("#" + inObjId + ' option');
    var inLis = $("#" + inObjId).parents(".select-group").find("ul").find("li");
    $('#' + offObjId).change(function () {
        var value = $.trim($(this).val());
        inOptions.each(function (i) {
            var inLi = inLis.eq(i);
            if (value === $(this).val()) {
                inLi.hide();
            } else {
                inLi.show();
            }
        });
    });
}
/**
 * 设置最大高度
 * @Method  setMaxHeight
 * @obj {object} 目标对象
 * @number {number} 除去头尾等固定高度之外的高度
 */
function setMaxHeight(obj, number) {
    var maxHeight = window.innerHeight - $("#nav-fixed").outerHeight() - $(".article-header").eq(0).outerHeight() - $("#footer").outerHeight() - number;
    $(obj).css({"max-height": maxHeight + "px", "overflow-y": "auto"});
}