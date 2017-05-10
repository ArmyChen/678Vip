/**
 * Created by mayi on 2015/6/3.
 */

var skuBom = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        id: -1,
        skuBomName: '',
        baseNum: 1,
        urlRoot : ctxPath,
        templateId : -1,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        cachedQueryConditions : '',
        listGridId : 'grid',
        queryUrl : '&act=ajax_skuBom_index',
        editUrl : '&act=basic_skuBom_edit',
        deleteUrl : '/delete',
        viewUrl : '/view',
        confirmUrl : '/doconfirm',
        exportUrl : '/export',
        queryForCopy : '/queryforcopy',
        switchSku: '/switchsku',
        sortName : 'code',
        pager : '#gridPager',
        formId : 'baseInfoForm',
        detailGridId : 'grid',
        gridData : [],
        warehouseId :'#fromWmId',
        tipQty: '&nbsp;<span class="iconfont question color-g" data-content="原物料/外购商品未加工时可使用的数量"></span>',
        tipNetQty: '&nbsp;<span class="iconfont question color-g" data-content="原物料/外购商品初加工后可使用的数量"></span>',
        tipYieldRate: '&nbsp;<span class="iconfont question color-g" data-content="原物料/外购商品经过初加工后，净料重量和毛料重量的百分比"></span>',
        reckonPrice:'&nbsp;<span class="iconfont question color-g" data-content="半成品和预制商品的估算成本，原物料和外购商品的采购价/结算价"></span>',
        reckonAmount:'&nbsp;<span class="iconfont question color-g" data-offset="left" data-content="由估算单价和毛料数量相乘计算而来"></span>',
        _now : new Date()
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType)
        {
            case 0 ://列表查询
                _this.$listGrid = $('#' + _this.opts.listGridId);
                _this.opts.cachedQueryConditions = serializeFormById(skuBom.opts.queryConditionsId);
                _this.initQueryList();
                _this.initExportButton();
                $.setSearchFocus();
                break;
            case 1 ://新增
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                $(document).delegate("#baseNum", "blur", function (e) {
                    if (!$(this).val() || parseFloat($(this).val()) <= 0) {
                        $(this).val(1);
                    }
                });
                _this.initDetailGrid(true);
                break;
            case 2 ://编辑
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                $(document).delegate("#baseNum", "blur", function (e) {
                    if (!$(this).val() || parseFloat($(this).val()) <= 0) {
                        $(this).val(1);
                    }
                });
                _this.bindCopyBtn();
                _this.initDetailGrid(true);

                $("#id").val(_this.opts.id);
                $("#id").trigger("chosen:updated");
                _this.bindSwitchSku();
                break;

            default ://查看
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(false);

                $("#id").val(_this.opts.id);
                $("#id").trigger("chosen:updated");
                _this.bindSwitchSku();
                break;
        }
    },

    //初始化查询页面
    initQueryList : function() {
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/scm/skubom/edit',
            'scm_kry/scm/skubom/view',
            'scm_kry/scm/skubom/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        $.serializeGridDataCallback = function (formData) {
            if (typeof formData.status == "object" || formData.status == undefined) {
                formData["status"] = -2;
            }
            return formData;
        };

        $.showEdit = function (rowData) {
        	// var flag = renderEnum.disabled;
        	// if(rowData.status == 0) flag = renderEnum.normal; //如果是ture则正常显示
        	return renderEnum.normal;
        };

        $.showView = function (rowData) {
        	var flag = renderEnum.hidden;
        	//if(rowData.isDisable == 1) flag = renderEnum.normal; //如果是ture则正常显示
        	return flag;
        };

        var $gridObj = $("#" + _this.opts.listGridId);
        $gridObj.dataGrid({
            rownumbers:true,
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url:  _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '所属分类', '库存类型', '商品编码', '商品名称(规格)', '单位', '设定日期', '配方状态', '配方状态'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'skuTypeName', index: 'skuTypeName', align: "left", width: 150},
                {name: 'wmTypeName', index: 'wmType', align: "left", width: 150},
                {name: 'skuCode', index: 'skuCode', align: "left", width: 150},
                {
                    name: 'skuName',
                    index: 'skuName',
                    align: "left",
                    width: 200,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 0) {
                            return cellvalue + "<span style='color:red'>(已停用)</span>";
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'uom', index: 'uom', align: "center", width: 80},
                {
                    name: 'updateTime',
                    index: 'updateTime',
                    align: "center",
                    width: 180,
                    formatter: function (cellvalue, options, rowObject) {
                        if (!cellvalue) {
                            return '-';
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'statusName', index: 'status', align: "center", width: 100},
                {name: 'status', index: 'status', width: 150, hidden: true}
            ],
            sortname: 'skuCode',
            pager: "#gridPager",
            showOperate: true,
            actionParam: {
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl,
                    render: $.showView
                },
                editor: {
                    url: basicPath + _this.opts.editUrl,
                    code: "scm:button:masterdata:skuBom:edit",
                    render: $.showEdit
                }
            }
        });
    },

    //初始化批量导出
    initExportButton : function() {
        var _this = this;

        $(document).delegate("#export", "click", function () {
            var currentQueryConditions = serializeFormById(_this.opts.queryConditionsId);

            if(currentQueryConditions != _this.opts.cachedQueryConditions){
                $.layerMsg('条件已改变，请先点击查询按钮！', false);
                return;
            }

            var $gridObj = $("#" + _this.opts.listGridId);

            var totalSize = $gridObj.jqGrid('getGridParam','records');

            if(totalSize > 0){

                var sidx = $gridObj.jqGrid('getGridParam','sortname');
                var sord = $gridObj.jqGrid('getGridParam','sortorder');

                var conditions = $.extend(true, {}, $("#queryConditions").getFormData() || {});

                conditions["sidx"] = sidx;
                conditions["sord"] = sord;

                if (typeof conditions.status == "object" || conditions.status == undefined) {
                    conditions["status"] = -2;
                }

                conditions = '?' + $.param(conditions, true);

                window.open(_this.opts.urlRoot + _this.opts.exportUrl + conditions, '_blank');
            } else{
                $.layerMsg('导出记录为空！', false);
            }
        });
    },

    //绑定复制按钮点击时间
    bindCopyBtn : function () {
        var _this = this;

        $("#btnCopy").click(function () {
            bkeruyun.showLoading();
            $.ajax({
                url: _this.opts.urlRoot + _this.opts.queryForCopy + "?r=" + new Date().getTime(),
                type: "post",
                async: false,
                data: JSON.stringify({id: $("#id").val()}),
                dataType: "json",
                contentType: "application/json",
                success: function (result) {
                    if (!!result && result.length > 0) {
                        $.skubom.show(result);
                    } else {
                        $.layerMsg('没有可供复制的商品', false);
                    }
                    bkeruyun.hideLoading();
                },
                error: function () {
                    bkeruyun.hideLoading();
                    $.layerMsg("网络错误", false);
                }
            });
        });
    },

    //绑定商品切换事件
    bindSwitchSku: function () {
        var _this = this;

        $(document).delegate("#id", 'change', function () {

            var id = $(this).val();

            if (id == _this.opts.id) {
                return;
            }

            if (_this.opts.commandType != 2) {//查看
                _this.handleSwitchSku(id);
                return
            }

            var result = _this.checkWhetherChange();
            if (result) {
                //注意：原项目中的layer(v1.9.3)的取消按钮(即第二个按钮)和关闭均执行cancel方法，无法区分，因而在配方编辑页面引入了layer(v3.0.1),并将源码中绑定到window的layer改名为layer3，避免覆盖原来的layer
                layer3.open(
                    {
                        icon: 3,
                        title: '提示',
                        offset: '30%',
                        content: '当前配方有变动，切换前是否先保存？',
                        btn: ['保存并切换', '直接切换'],
                        yes: function (index) {
                            //执行保存并切换按钮点击事件

                            layer3.close(index);

                            //现还原，保存后再切换
                            $("#id").val(_this.opts.id);
                            $("#id").trigger("chosen:updated");

                            var args = $("#btnSave").attr("args");
                            try {
                                eval("window.tempVal=" + args + ";");
                                args = window.tempVal;
                            } catch (ex) {
                                args = {};
                            }

                            args.confirm = false;
                            args.skuBomId = id;
                            args.mode = 2;
                            $.saveSkuBom(args);
                        },
                        btn2: function (index) {
                            //执行直接切换按钮点击事件

                            layer3.close(index);
                            _this.handleSwitchSku(id);
                        }
                        ,
                        cancel: function (index) {
                            //执行弹框关闭图标点击事件

                            //还原之前选择的商品
                            $("#id").val(_this.opts.id);
                            $("#id").trigger("chosen:updated");
                            layer3.close(index);
                        }
                    }
                );
            } else {
                _this.handleSwitchSku(id);
            }
        });
    },

    //检查配方是否有变动（基数、子商品），页面变动，不涉及后台检查
    checkWhetherChange: function () {
        var _this = this;

        //检查配方基数
        var baseNum = $('#baseNum').val();
        if (baseNum != _this.opts.baseNum) {
            return true;
        }

        //检查配方明细
        var savedDetails  = _this.opts.gridData;
        //只需比较毛料数量，原因：1，半成品只有毛料；2，净料和毛料是根据出成率相互换算的，在本页面出成率不可改，因此比较毛料料即相当于也比较了净料，PS:不考虑换算程序计算错误的情况
        var currDetails = $("#grid").jqGrid('getRowData');
        if (savedDetails.length != currDetails.length) {
            return true;
        } else if (savedDetails.length == 0) {
            //没有配方，不用比较
            return false;
        }

        var currDetailsMap = {};
        $.each(currDetails, function (i, n) {
            currDetailsMap[n.skuId] = n.qty;
        });

        for (var i = 0; i < savedDetails.length; i++) {
            var sku = savedDetails[i];
            if (sku.qty != currDetailsMap[sku.skuId]) {
                return true;
            }
        }

        return false;
    },

    //向后台请求切换商品
    handleSwitchSku: function (id) {
        var _this = this;
        var commandType = _this.opts.commandType,
            editable = _this.opts.commandType == 2;

        bkeruyun.showLoading();
        $.ajax({
            url: _this.opts.urlRoot + _this.opts.switchSku + "?id=" + id + "&commandType=" + commandType + "&r=" + new Date().getTime(),
            type: "post",
            async: false,
            data: JSON.stringify({id: $("#id").val()}),
            dataType: "json",
            contentType: "application/json",
            success: function (result) {
                if (result.success) {
                    var skuBomVo = result.data;
                    _this.opts.gridData = skuBomVo.details;
                    $("#gridDiv").empty().append('<table id="grid"></table>');
                    _this.$detailGrid = $('#' + _this.opts.detailGridId);
                    _this.initDetailGrid(editable);

                    $('.uom').html(skuBomVo.uom);
                    $('#baseNum').val(skuBomVo.baseNum);
                    _this.opts.id = skuBomVo.id;
                    updateIdInUrl(id);

                    if (_this.opts.commandType == 2) {
                        $('#skuId').val(skuBomVo.skuId);
                        $('#skuBomId').val(skuBomVo.id);
                        _this.opts.baseNum = skuBomVo.baseNum;
                        _this.opts.skuBomName = skuBomVo.skuName;
                        if ($("#id").val() != id) {
                            $("#id").val(_this.opts.id);
                            $("#id").trigger("chosen:updated");
                        }
                    } else {
                        $('#baseNumView').html(skuBomVo.baseNum);
                        if (skuBomVo.isDisable) {
                            $("#btnEdit").hide();
                        } else {
                            $("#btnEdit").show();
                        }
                    }
                } else {
                    if (result.flag == 1) {
                        var msg = "该商品已被" + (_this.opts.commandType == 2 ? "停用或" : "") + "删除";
                        $.layerMsg(msg, false, {
                            end: function () {
                                location.reload(true);
                            }
                        });
                    } else {
                        $.layerMsg("商品切换失败，请重试", false);
                        $("#id").val(_this.opts.id);
                        $("#id").trigger("chosen:updated");
                    }
                }
                bkeruyun.hideLoading();
            },
            error: function () {
                bkeruyun.hideLoading();
                $.layerMsg("网络错误", false);
                $("#id").val(_this.opts.id);
                $("#id").trigger("chosen:updated");
            }
        });
    },

    //初始化单据作业明细表格
    initDetailGrid : function(editable) {
        var _this = this;
        var $gridObj = _this.$detailGrid;
        var netQtyColModel = {
    		name: 'netQtyStr',
            index: 'netQtyStr',
            align: 'right',
            width: 100,
            sortable: !editable,
            formatter:function(cellvalue, options, rowObject){
            	return cellvalue==null?"-":cellvalue;
            }
        };
        var qtyColModel = {
            name: 'qty',
            index: 'qty',
            align: 'right',
            width: 100,
            sortable: !editable
        };

        var editColModel = {
            editable: true,
            formatter: formatInputForQty,
            unformat: unformatInput
        };
        
        var editNetQtyColModel = {
    		editable : true,
    		formatter: formatInputForNetQty,
    		unformat: unformatInputForNetQty
        }

        if (editable) {
            qtyColModel = $.extend(true, qtyColModel, editColModel || {});
            netQtyColModel = $.extend(true, netQtyColModel, editNetQtyColModel || {});
        }

        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            //height: 390,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['商品编码','price','reckonPrice','库存类型', '商品分类', '原料条码', '原料名称(规格)','估算单价'+skuBom.opts.reckonPrice, '净料数量'+skuBom.opts.tipNetQty,'出成率'+skuBom.opts.tipYieldRate,'毛料数量'+skuBom.opts.tipQty,'单位','yieldRateNone','估算金额'+skuBom.opts.reckonAmount],
            colModel: [
                {name: 'skuId', index: 'skuId', width: 80, sortable: !editable},
                {hidden:true,name: 'price', index: 'reckonPrice', width: 80, sortable: !editable},
                {hidden:true,name: 'reckonPrice', index: 'reckonPrice', width: 100,formatter:function(cellvalue, options, rowObject){
	                	return rowObject.reckonPrice!=null&&rowObject.reckonPrice!="null"?rowObject.reckonPrice:0;
                }},
                {hidden:true,name: 'wmTypeStr', index: 'wmTypeStr', width: 100, sortable: !editable,
                	formatter:function(cellvalue, options, rowObject){
                		if (rowObject.isDisable == 0) {
                            return ("<span style='color:#9D9D9D;'>"+cellvalue+"</span>");
                        } else {
                            return cellvalue;
                        }
                	}
                },
                {name: 'skuTypeName', index: 'skuTypeName', width: 100, sortable: !editable, formatter: $.skuIsDisableFormat},
                {name: 'skuCode', index: 'skuCode', width: 100, sortable: !editable, formatter: $.skuIsDisableFormat},
                {name: 'skuName', index: 'skuName', width: 170, sortable: !editable,
                    formatter:function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 0) {
                            return "<span style='color:#9D9D9D;'>" + cellvalue + "<span style='color:red'>(已停用)</span></span>";
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'reckonPriceStr', index: 'reckonPriceStr', width: 100, align: "center",sortable: !editable,formatter:function(cellvalue, options, rowObject){
                	var value = 0;
                	if(rowObject.reckonPrice!=null&&rowObject.reckonPrice!="null") value=rowObject.reckonPrice;
                	return rowObject.isDisable==0?('<span style="color:#9D9D9D;">'+value+'</span>'):value;
                }},
                netQtyColModel,
                {name: 'yieldRateStr', index: 'yieldRateStr', width: 100, sortable: !editable,align: "center",formatter:function(cellvalue, options, rowObject){
                	var value = "-";
                	if(rateStr!=undefined){
                        var rateStr = rowObject.yieldRateStr;
                        value = rateStr.indexOf("%")!=-1?rateStr:(rateStr+"%");
                    }
                	return rowObject.isDisable==0?('<span style="color:#9D9D9D;">'+value+'</span>'):value;
                }},
                qtyColModel,
                {name: 'uom', index: 'uom', width: 100, sortable: !editable,align: "center", formatter: $.skuIsDisableFormat},
                {name: 'yieldRateNone', index: 'yieldRateNone',hidden: true,formatter:function(cellvalue, options, rowObject){
                	if (rowObject.wmType==3||rowObject.wmType==4) {
                		var rateStr = rowObject.yieldRateStr;
                		if(rateStr.indexOf("%")!=-1) rateStr = rateStr.replace("%","");
                        return rateStr;
                    } else {
                        return 100;
                    }
                }},
                {name: 'reckonAmount', index: 'reckonAmount', width: 130, align: "center",sortable: !editable,
                	formatter:function(cellvalue, options, rowObject){
                		var value = 0;
	                	if(rowObject.qty&&rowObject.reckonPrice!=null){
	                		value = math.chain(parseFloat(rowObject.reckonPrice)).multiply(parseFloat(rowObject.qty)).round(5).value;
	                	}
	                	return rowObject.isDisable==0?('<span style="color:#9D9D9D;">'+value+'</span>'):value;
                	}
                }
            ],
            afterInsertRow: function (rowid, aData) {
                //$gridObj.jqGrid('setRowData', rowid, {skuId: rowid});
            },
            loadComplete: function(data){
            	/**只读配方明细表有效**/
            	test: (function () {
            		$(document).delegate(".netQtyStr,.qty", "input propertychange", function (e) {
                		var _this = $(this),id = _this.attr("id"),real = id.split("_")[0],
	                		value = parseFloat(_this.val()==""?0:_this.val()),
	                		reckonAmount = _this.parent().parent().find('td[aria-describedby="grid_reckonAmount"]'),
                			rate = parseFloat(_this.parent().parent().find('td[aria-describedby="grid_yieldRateNone"]').html()),
                			reckonPrice = parseFloat(_this.parent().parent().find('td[aria-describedby="grid_reckonPrice"]').html());
                		
                		if(id.indexOf("netQtyStr")>0){//当前是净料
                			var qtyInput = $("#"+real+"_qty");
                			qtyInput.val(math.chain(value).multiply(100).divide(rate).round(5).value);
                			if(!isNaN(reckonPrice))
                			reckonAmount.html(math.chain(value).multiply(reckonPrice).multiply(100).divide(rate).round(5).value);
                			$.changeBomReckonAmount();
                		}else{//当前是毛料
                			var netInput = $("#"+real+"_netQtyStr");
                			if(netInput){
                				netInput.val(math.chain(value).multiply(rate).divide(100).round(5).value);
                				if(!isNaN(reckonPrice))
                				reckonAmount.html(math.chain(value).multiply(reckonPrice).round(5).value);
                				$.changeBomReckonAmount();
                			}
                		}
                	});
            		$.changeBomReckonAmount();
                })();

            }
        });
    }
};


$("#baseNum").on("blur",function(){$.changeBomReckonAmount();});
$.changeBomReckonAmount = function(){
	var list = $("#grid").jqGrid('getRowData'),amount = math.chain(0),num = parseFloat($("#baseNum").val());
	list.forEach(function(v){
		var each = parseFloat(v.reckonAmount.replace('<span style="color:#9D9D9D;">','').replace('</span>',''));
		if(!isNaN(each)) amount = amount.add(each);
	});
	if(num>0){
		$("#bomReckonPrice").val(amount.divide(num).round(5).value);
	}else{
		$("#bomReckonPrice").val("-");
	}
};

$.saveSkuBom = function (args) {
	var bool = check(args);
	if(bool){
		// args.confirm = true;
		args.confirmMsg = {title: "提示", describe: '是否保存' + skuBom.opts.skuBomName + '的配方？'};
		$.doSave(args);
	}
};

//编辑页面的返回
$.goBack = function (url) {
    var result = skuBom.checkWhetherChange();
    if (result) {
        //注意：原项目中的layer(v1.9.3)的取消按钮(即第二个按钮)和关闭均执行cancel方法，无法区分，因而在配方编辑页面引入了layer(v3.0.1),并将源码中绑定到window的layer改名为layer3，避免覆盖原来的layer
        layer3.open(
             {
                icon: 3,
                title: '提示',
                offset: '30%',
                content: '当前配方有变动，返回前是否先保存？',
                btn: ['保存并返回', '直接返回'],
                yes: function (index) {
                    //执行保存并返回按钮点击事件

                    layer3.close(index);

                    var args = $("#btnSave").attr("args");
                    try {
                        eval("window.tempVal=" + args + ";");
                        args = window.tempVal;
                    } catch (ex) {
                        args = {};
                    }

                    args.confirm = false;
                    args.redirectUrl = url;
                    args.mode = 3;
                    $.saveSkuBom(args);
                },
                btn2: function (index) {
                    //执行直接返回按钮点击事件

                    window.location.href = url;

                    layer3.close(index);
                }
                ,
                cancel: function (index) {
                    //执行弹框关闭图标点击事件

                    layer3.close(index);
                }
            }
        );
    } else {
        window.location.href = url;
    }
};

//查看页面的跳转
$.toEditInViewPage = function (args) {
    args.postData.id = skuBom.opts.id;
    $.doForward(args);
};

//保存前单据明细检测
function check(args){
	var gridData = $('#' + skuBom.opts.detailGridId).jqGrid('getRowData');
    if (gridData.length == 0) {
        layer.confirm('当前商品未添加配方，是否保存？', {icon: 3, title: '提示', offset: '30%'},
            function (index) {
                layer.close(index);
                
                args.customValidator = false;
                args.confirm = false;
                args.confirmMsg = {};
                $.doSave(args);
            },
            function (index) {
                layer.close(index);
            }
        );
    } else {
    	var bool = true;
        for (var i = 0; i < gridData.length; i++) {
            try {
                if (parseFloat(gridData[i].qty) <= 0) {
                    $.layerMsg('配方内商品数量必须大于0', false);
                    bool = false;
                }
            } catch (err) {
                $.layerMsg('配方内商品数量必须大于0', false);
                bool = false;
            }
        }
        
        if(bool){
        	$.ajax({
        		type: "post",
        		async: false,
        		url : "scm/skubom/check/yieldRate",
        		contentType : 'application/json',
        		dataType : 'json',
        		data : JSON.stringify(gridData),
        		success: function (data) {
        			var msg = ""
    				if(data&&data.length>0){
    					
    					//着色
    					for(var i=0;i<data.length;i++) 
    						$("#"+data[i].skuId).find('td[aria-describedby="grid_yieldRateStr"]').addClass("bg_red");
    					
    					bool = false;
    					var conf = {
								confirm:true,
								hint:'以下商品的出成率，已经修改，确定保存？',
								dataHint:'修改出成率后，会依据净料数量，重新计算毛料数量。',
								dataList:data,
								callBack:function(){
									args.customValidator = false;
					                args.confirm = false;
					                args.confirmMsg = {};
					                $.doSave(args);
								},
								callCancelBack:function(){
									for(var i=0;i<data.length;i++){
	        							var eh = data[i];
	        							var id = eh.skuId,netQty = eh.netQtyStr,rate = eh.yieldRate;
	        							$("#"+id).find('td[aria-describedby="grid_yieldRateStr"]').html(rate+"%");
	        							$("#"+id).find('td[aria-describedby="grid_yieldRateNone"]').html(rate);
	        							$("#"+id+"_qty").val(math.chain(parseFloat(netQty)).multiply(100).divide(parseFloat(rate)).round(5).value);
	        						}
								}
						};
						$.message.showDialog(conf);
    				}
        		}
        	});
        }
        return bool;
    }
}

//保存回调
$.saveCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        if(confirm(rs.message + ",保存成功是否继续？")){
            location.reload();
        }else{
            location.href=basicPath+"&act=basic_skuBom_index";
        }

        return;
    } else {
        if (rs.data != '' && rs.data != null) {
            $.layerOpen("操作失败:" + rs.message, rs.data);
        } else {
            $.layerMsg("操作失败:" + rs.message, false);
        }
    }
};

$.skuIsDisableFormat = function (cellvalue, options, rowObject) {
    if (rowObject.isDisable == 0) {
        return "<span style='color:#9D9D9D;'>" + cellvalue + "</span>";
    } else {
        return cellvalue;
    }
};

function load() {
    skuBom.opts.cachedQueryConditions = serializeFormById(skuBom.opts.queryConditionsId);
    $("#" + skuBom.opts.listGridId).refresh();

    //添加查询条件缓存
    var query = {};
    query.data = $('#' + skuBom.opts.queryConditionsId).serializeArray();
    query.formId = skuBom.opts.queryConditionsId;

    sessionStorage.setItem('query',JSON.stringify(query));
}

function formatInputForQty(cellvalue, options, rowObject) {
    var colName = options.colModel.name;
    var str = '<input type=\'text\'  style=\'width:100%;height:34px\' autocomplete=\'off\' data-limit=\'{6,5}\' data-range=\'{0,100000}\'';
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

/**
 * 生成一个输入浮点数的input
 */
function formatInputForNetQty(cellvalue, options, rowObject) {
    var colName = options.colModel.name;
    var str = '<input type=\'text\' style=\'width:100%;height:34px;\' autocomplete=\'off\' data-limit=\'{8,5}\' data-range=\'{0,10000000}\'';
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
    /**
     * 1.正常情况每次都会带wmType，根据wmType判断是否有净料数量
     * 2.编辑净料数量时，会触发格式化，但不会带wmType，这种情况需要直接放行
     **/
    return (!rowObject.wmType||rowObject.wmType==3||rowObject.wmType==4)?str:"-";
}

function unformatInputForNetQty(cellvalue, options, cell) {
    var value = $(cell).children('input')[0].value;
    return value == '' ? '-' : value;
}

/**
 * 更新浏览器地址栏的token
 * @param token 新的token
 */
function updateIdInUrl(id) {
    var href = location.href;
    var index = href.indexOf("?");

    if (index == -1) {
        return;
    }

    var url = href.substring(0, index + 1);
    var para = href.substring(index + 1);
    para = para.split("&");
    var hasId = false,
        paraArray = [];

    $.each(para, function(i, n){
        var parameter = n.split("=");
        if (parameter[0] == 'id') {
            parameter[1] = id;
            hasId = true;
        }

        paraArray.push(parameter);
    });

    if (!hasId) {
        return;
    }

    $.each(paraArray, function(i, n){
        if (i != 0) {
            url += '&';
        }

        url += n[0] + '=' +n[1];
    });

    history.replaceState({}, "更新id", url);
}