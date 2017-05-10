/**
 * Created by mayi on 2015/6/1.
 */

var productTemplate = {
    //默认参数
    opts : {
        urlRoot : ctxPath,
        brandId : 1031,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=product_moban_ajax',
        editUrl : '/update',
        viewUrl : '/view',
        lockUrl : '/lock',
        unlockUrl : '/unlock',
        sortName : 'code',
        sortOrder: 'asc',
        pager : '#gridPager',
        _now : new Date(),
        formId : 'baseInfoForm',
        startDayId : 'validityStartDate',
        endDayId : 'validityEndDate',
        detailGridId : 'grid',
		detailShopGrid : 'shop_grid',
		gridData : [],
		shopData : [],
		skuTypes : []
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType)
        {
            case 0 ://列表查询
                _this.initQueryList();
                $.setSearchFocus();
                break;
            case 1 ://新增
                _this.checkCodeAndName();
                _this.initDateSelect();
                _this.initDetailGrid(true);
                _this.initShopDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                $('#name').focus();
                break;
            case 2 ://编辑
                _this.checkCodeAndName();
                _this.initDateSelect();
                _this.initDetailGrid(true);
                _this.initShopDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                break;
            default ://查看
                _this.initDetailGrid(false);
            	_this.initShopDetailGrid(false);
                break;
        }
    },

    //自定义输入框验证
    checkCodeAndName : function() {
        var _this = this;
        //检查模板代码是否重复
        jQuery.validator.addMethod('checkCode', function (value, element) {
            if (value == null || value.length < 1) {
                return true;
            }
            var flag = true;
            $.ajax({
                type: 'post',
                url: _this.opts.urlRoot + '/checkCode',
                data: {
                    id: parseInt($("#id").val()) || -1,
                    code: value
                },
                async: false,
                dataType: 'json',
                success: function (result) {
                    if (result == null || result.success) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                },
                error: function (data) {
                    //bkeruyun.promptMessage('网络错误');
                    $.layerMsg('网络错误', false);
                }
            });

            if (!flag) {
                //bkeruyun.promptMessage('模板编码填写重复，请检核！');
                //$.layerMsg('模板编码填写重复，请检核！', false);
                return false;
            }else{
				$("#code").parent().parent().find(".wrong").html("");
			}
            return true;
        }, '此栏位重复');

        //检查模板名称是否重复
        jQuery.validator.addMethod('checkName', function (value, element) {
            if (value == null || value.length < 1) {
                return false;
            }

            var oldName = '${skuTemp.name}';
            if (oldName != null && oldName.length > 0 && oldName == value) {
                return true;
            }

            var flag = true;
            $.ajax({
                type: 'post',
                url: _this.opts.urlRoot + '/checkName',
                data: {
                    id: parseInt($("#id").val()) || -1,
                    name: value
                },
                async: false,
                dataType: 'json',
                success: function (result) {
                    if (result == null || result.success) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                },
                error: function (data) {
                    // bkeruyun.promptMessage('网络错误');
                    // $.layerMsg('网络错误', false);
                }
            });

            if (!flag) {
                //bkeruyun.promptMessage('模板名称填写重复，请检核！');
                //$.layerMsg('模板名称填写重复，请检核！', false);
                return false;
            }else{
				$("#name").parent().parent().find(".wrong").html("");
			}
            return true;
        }, '此栏位重复');

        if (_this.opts.commandType == 2) {
            $('#' + _this.opts.formId).validate({
                rules: {
                    name: {
                        checkName: true
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents('.positionRelative').find('.wrong'));
                },
                debug: true
            });
        } else {
            $('#' + _this.opts.formId).validate({
                rules: {
                    code: {
                        checkCode: true
                    },
                    name: {
                        checkName: true
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents('.positionRelative').find('.wrong'));
                },
                debug: true
            });
        }
    },

    //初始化日期选择器
    initDateSelect : function() {
        var _this = this;
        $(document).delegate("#" + _this.opts.startDayId, "focus", function () {
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
                //重置结束日期选择器
                var $endObj = $("#" + _this.opts.endDayId).datetimepicker("remove");
                var startValue = $this.val();
                var endValue = $endObj.val();

                //判断结束日期是否小于开始日期
                if (startValue != '' && endValue != '') {
                    var startDate = new Date(startValue);
                    var endDate = new Date(endValue);
                    if (startDate > endDate) {
                        $endObj.val('');
                    }
                }
            });

            //清空开始日期 重置结束日期选择器
            $this.parents(".search-box").find(".close").bind("click", function () {
                $("#" + _this.opts.endDayId).datetimepicker("remove");
            });
        });

        $(document).delegate("#" + _this.opts.endDayId, "focus", function () {
            var $this = $(this),
	            _dateStartDate = $this.attr("data-date-startDate"),
                _startValue = $("#" + $this.attr("data-for-element")).val(),
                _format = ($this.attr("data-date-format")) ? $this.attr("data-date-format") : "yyyy-mm-dd",
                _startView = ($this.attr("data-date-startView")) ? parseInt($this.attr("data-date-startView")) : 2,
                _minView = ($this.attr("data-date-minView")) ? parseInt($this.attr("data-date-minView")) : 2,
                _maxView = ($this.attr("data-date-maxView")) ? parseInt($this.attr("data-date-maxView")) : 4,
                _startDate =( _startValue>=_dateStartDate) ? _startValue : _dateStartDate,
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
            });
        });
    },

    //初始化查询页面
    initQueryList : function() {
        ShopData.init();
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/scm/template/product/update',
            'scm_kry/scm/template/product/view',
            'scm_kry/scm/template/product/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;
        $.beforeSend = function (formData) {
           return renderEnum.hidden;
        };

        $.showEdit = function(rowData){
            return renderEnum.hidden;

        };
        
        $.show = function (rowData) {
            return renderEnum.hidden;

        };

        $.showLock = function (rowData) {
            return renderEnum.hidden;

        };

        $('#' + _this.opts.listGridId).dataGrid({
            formId: _this.opts.queryConditionsId,
            serializeGridDataCallback: $.beforeSend,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '模板编码', '模板名称', /*'开始日期', '截止日期',*/ '编辑人','最后修改时间', '状态', '操作'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'code', index: 'code', width: 160, align: 'left'},
                {name: 'name', index: 'name', width: 160, align: 'left'},
               /* {name: 'validityStartDate', index: 'validityStartDate', width: 160, align: 'center'},
                {
                    name: 'validityEndDate',
                    index: 'validityEndDate',
                    width: 160,
                    align: 'center',
                    formatter: validityEndDateFormatter
                },*/
                {name: 'updaterName', index: 'updaterName', width: 160, align: 'center'},
                {name: 'updateTime', index: 'updateTime', width: 160, align: 'center'},
                {name: 'isDisable', index: 'isDisable', width: 100, formatter: disableFormatter, align: 'center'},
                {name: 'status', index: 'isDisable', width: 150, hidden: true}
            ],
            sortname: _this.opts.sortName,
            sortorder: "asc",
            pager: _this.opts.pager,
            showOperate: true,
            actionParam: {
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl
                },
                editor: {
                    render: $.showEdit,
                    code: "scm:button:production:template:edit",
                    url: _this.opts.urlRoot + _this.opts.editUrl
                },
                clock: {
                    url: _this.opts.urlRoot + _this.opts.lockUrl,
                    code: "scm:button:production:template:lock",
                    render: $.show
                },
                unlock: {
                    url: _this.opts.urlRoot + _this.opts.unlockUrl,
                    code: "scm:button:production:template:unlock",
                    render: $.showLock,
                    beforeCallback: 'unlockCallback'
                }
            }
        });

        $.unlockCallback = function (args) {
            var id = args.postData.id;
            var rowObj = $('#' + _this.opts.listGridId).jqGrid('getRowData', id);
            var startDate = new Date(rowObj.validityStartDate + ' 00:00:00');
            if (_this.opts._now < startDate) {
                return '该模板还未到开始日期，是否将开始日期改为当前日期？';
            }

            var endDate = rowObj.validityEndDate;
            if (endDate == null || endDate == '-') {
                return '是否启用？';
            }

            if (_this.opts._now >= new Date(endDate + ' 23:59:59')) {
                return '该模板的截止日期已过，是否将截止日期置空永久有效？';
            }
            return '是否启用？';
        };

        function validityEndDateFormatter(cellValue, options, rowObject) {
            if (rowObject.validityEndDate == null ||
                rowObject.validityEndDate == undefined ||
                rowObject.validityEndDate.length < 1) {
                return '-';
            }
            return rowObject.validityEndDate;
        }

        function disableFormatter(cellValue, options, rowObject) {
            return rowObject.isDisable == 0 ? '<span style="color: red">停用</span>' : '启用';
        }
    },
    
  //初始化单据作业明细表格
    initDetailGrid : function(editable) {
        var _this = this;
        var $gridObj = $('#' + _this.opts.detailGridId);

        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['skuId', '所属分类', '商品编码', '商品名称(规格)','单位', '单位', '价格','非授权商户', '当前换算率', '标准单位换算率', '定价', '单位ID'],
            colModel: [
                {name: 'id', index: 'id',hidden: true},
                {name: 'skuTypeName', index: 'skuTypeName', sortable: false,width: 70, formatter: $.skuIsDisableFormat},
                {name: 'skuCode', index: 'skuCode', sortable: false,width: 80, formatter: $.skuIsDisableFormat},
                {
                    name: 'skuName',
                    index: 'skuName',
                    width: 100,
                    sortable: false,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 1) {
                            return "<span style='color:#9D9D9D;'>" + cellvalue + "<span style='color:red'>(已停用)</span></span>";
                        } else {
                            return cellvalue;
                        }
                    }
                },
                {name: 'uom', index: 'uom', width: 50, sortable: !editable, align: 'center', hidden: editable},
                {name: 'uom', index: 'uom', width: 50, sortable: !editable, align: 'center', hidden: !editable,
                    formatter: $.unitSelectFormatter,
                    unformat : unformatSelect
                },
                {
                    name: 'price',
                    index: 'price',
                    width: 70,
                    sortable: false,
                    align: "right",
                    //formatter: customCurrencyFormatter
                    formatter: $.skuIsDisableFormat
                },
                {
                	name: 'exceptShopStr', 
                	index: 'exceptShopStr', 
                	width: 80, 
                	sortable: false,
                	formatter: function (cellvalue, options, rowObject){
                		return '<input name="multiSelectIpt" type="hidden" class="checkbox-selected" value="'+rowObject.exceptShopStr+'" />';
                	}
                },
                {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
                {name: 'uomId', index: 'uomId', align: 'right', hidden: true}
            ],
            afterInsertRow: function (rowid, aData) {
            	//初始化本行费商户授权
            	var thisInput = $("#"+rowid).find(".checkbox-selected");
            	thisInput.selectMulti();//完成首次加载数据后初始化非授权商户下拉列表
            	thisInput.parents("td").css({"overflow" : "visible"}).removeAttr("title");

                $.removeSelectTitle(rowid); //移除下拉框列的表格title
            }
        });
        
        //模拟select选择，并去除所属td原有的overflow：hidden，让下拉框可见
        $("input[name='multiSelectIpt']").parents("td").css({"overflow" : "visible"}).removeAttr("title");
        $gridObj.setGridWidth($('.panel').width() - 28);

        if(editable) $.delegateClickSelectGroup($gridObj);
    },
    
    //初始化单据作业表格--商户授权
    initShopDetailGrid : function(editable) {
        var _this = this,$gridObj2 = $("#"+ _this.opts.detailShopGrid);
        $gridObj2.dataGrid({
            data: _this.opts.shopData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['id','name','商户编码', '商户名称', '商户地址','创建人','创建时间'],
            colModel: [
                {name: 'id',index: 'id',hidden: true},
                {name: 'name',index: 'name',hidden: true, sortable: false,
                	formatter:function(data,opt,cell){
                		return data?data:(cell.id==-1?"品牌":cell.commercialName);
                	}
                },
                {name: 'commercialId', index: 'commercialId', align: 'center',sortable: false,formatter: $.shopIdFormat},
                {name: 'commercialName', index: 'commercialName', sortable: false,formatter: $.shopNameFormat},
                {name: 'commercialAddress', index: 'commercialAddress', sortable: false,formatter: $.shopIdFormat},
                {name: 'creatorId', index: 'creatorId', hidden: true,sortable: !editable},
                {name: 'createTime', index: 'createTime', hidden: true,sortable: !editable}
            ],
            afterInsertRow: function (rowid, aData) {
                
            }
        });
        $.resetExceptSelect();//数据重置
        $("input[name='multiSelectIpt']").selectMulti();//完成首次加载数据后初始化非授权商户下拉列表
        //预览时修改下拉为一般的input
        if(!editable){
    	   var taget = $("input[name='multiSelectIpt']").parents("td");
            for(var i=0;i<taget.length;i++){
                var eh = $(taget[i]),txt=eh.find("em").html();
                eh.attr("title",txt);
                //商品停用时非授权商户置灰
                if(eh.prev().children('span').length == 1){
                    eh.html('<span style="color:#9D9D9D;">' + txt + '</span>');
                }else{
                    eh.html(txt);
                }
            }
    	   taget.css({"overflow" : "hidden"});
        }
        $gridObj2.setGridWidth($('.panel').width() - 25);
    }
};

//------------------------格式化 start---------------------
$.skuIsDisableFormat = function (cellvalue, options, rowObject) {
    if (rowObject.isDisable == 1) {
        return "<span style='color:#9D9D9D;'>" + cellvalue + "</span>";
    } else {
        return cellvalue;
    }
};

$.shopNameFormat = function(data,opt,cell){
	if (typeof(cell.status) != "undefined" && cell.id == -1) {
        return data+"<span style='color:red;'>(品牌)</span>";
    } else if (typeof(cell.status) != "undefined" && (cell.status == -1 || cell.isSupply == 0)) {
        return "<span style='color:#9D9D9D;'>" + data + '<span style="color:red;">(未开通供应链)</span></span>';
    }
	return data;
};

$.shopIdFormat = function(data,opt,cell){
	if(typeof(cell.status) != "undefined" && (cell.status == -1 || cell.isSupply == 0) && cell.id != -1) {
	    return "<span style='color:#9D9D9D;'>"+data+'</span>';
    }
	return data;
};

$.resetExceptSelect = function(){
	var currentShop = {},newData = $("#shop_grid").jqGrid('getRowData');
	for(var i=0;i<newData.length;i++){
		var name=newData[i].commercialName,notUsing = (name.indexOf("</span>")!=-1&&name.indexOf('<span style="color:red;">(未开通供应链)</span>')!=-1);
		currentShop[newData[i].id] = newData[i].name+(notUsing?"(未开通供应链)":"");
	}
	$.setSelectMultiData(currentShop);
}
//-------------------------格式化 end-----------------------

//单据明细校验 
$.detailsValidator = function (args) {
	var pageShopTempateData = $("#shop_grid").getCol('id');
	if(pageShopTempateData.length==0){
		$.layerMsg('请勾选授权商户！若不勾选，则在商户下选择不到该自产模板！', false);
        return false;
	}else{
		var allShop = new Array();
		for(var i=0;i<pageShopTempateData.length;i++) allShop.push({shopId:pageShopTempateData[i]});
		$("#shopTemplateDetails").val(JSON.stringify(allShop));
	}

	var rowData = $('#' + productTemplate.opts.detailGridId).jqGrid('getRowData');
    if (rowData.length == 0) {
        $.layerMsg('请添加商品', false);
        return false;
    }
    
    var allExcShop = new Array();
    for(var i=0;i<rowData.length;i++){
    	var each = rowData[i],skuId=each.id,excShopStr=$(each.exceptShopStr).find(".checkbox-selected").val();
    	if(excShopStr&&excShopStr.length>0){
    		var allExcId = excShopStr.split(",");
    		for(var j=0;j<allExcId.length;j++) allExcShop.push({shopId:allExcId[j],skuId:skuId});
    	}   
    }
    $("#exceptShopDetails").val(JSON.stringify(allExcShop));
    return true;
}

//保存回调
$.submitCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
    	var showMsg = $("#code").val().length==0;
    	if(showMsg){
    		$.layerMsg("操作成功，"+$("#name").val()+"编码是：<span style='color:red;'>"+rs.data+"</span>", true, {end:function(){window.location.href = basicPath+"&act=product_index";},shade: 0.3});
    	}else{
    		$.layerMsg("操作成功！", true, {end:function(){window.location.href = productTemplate.opts.urlRoot + '/index';},shade: 0.3});
    	}
        return;
    } else {
        if (rs.data != '' && rs.data != null) {
            $.layerOpen("操作失败：" + rs.message, rs.data);
        } else {
            $.layerMsg("操作失败：" + rs.message, false);
        }
    }
}

//保存复制回调
$.submitCallbackBak = function (args) {
    var rs = args.result;
    if (rs.success) {
    	var showMsg = $("#code").val().length==0;
    	if(showMsg){
    		$.layerMsg("操作成功，"+$("#name").val()+"编码是：<span style='color:red;'>" + rs.data+"</span>", true,{shade: 0.3});
    	}else{
    		$.layerMsg("操作成功！", true,{shade: 0.3});
    	}
        return;
    } else {
        if (rs.data != '' && rs.data != null) {
            $.layerOpen("操作失败：" + rs.message, rs.data);
        } else {
            $.layerMsg("操作失败：" + rs.message, false);
        }
    }
}

/* tab select */
$("#tab-ggoup").on("click","button",function(){
	$(this).addClass("btn-active").siblings().removeClass("btn-active");
	$("#"+$(this).data("tid")).show().siblings().hide();
});

//商品过滤
function filterSku() {
    var skuTypeName = $('#skuTypeName').find('option:selected').text();
    if($('#skuTypeName').val() === '') skuTypeName = '';
    var conditions1 = {skuCode: $('#skuCodeOrName').val(),skuTypeName: skuTypeName};
    var conditions2 = {skuName: $('#skuCodeOrName').val(),skuTypeName: skuTypeName}
    var rowIds1 = filterGridRowIds('grid', conditions1);
    var rowIds2 = filterGridRowIds('grid', conditions2);
    Array.prototype.push.apply(rowIds1, rowIds2);
    filterGridRows('grid', rowIds1);
}