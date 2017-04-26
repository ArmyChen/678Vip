/**
 * Created by mayi on 2015/6/1.
 */

var skuTemplate = {
    //默认参数
    opts : {
        urlRoot : '/scm/template/stockcount',
        brandId : 1031,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '/query',
        editUrl : '/update',
        viewUrl : '/view',
        lockUrl : '/lock',
        unlockUrl : '/unlock',
        sortName : 'code',
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
                _this.initSkuDetailGrid(true);
                _this.initShopDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                $('#name').focus();
                break;
            case 2 ://编辑
                _this.checkCodeAndName();
                _this.initSkuDetailGrid(true);
                _this.initShopDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                break;
            default ://查看
                _this.initSkuDetailGrid(false);
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
                    $.layerMsg('网络错误', false);
                }
            });

            if (!flag) {
                return false;
            }
            return true;
        }, '此栏位重复');

        //检查模板名称是否重复
        jQuery.validator.addMethod('checkName', function (value, element) {
            if (value == null || value.length < 1) {
                return false;
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
                    $.layerMsg('网络错误', false);
                }
            });

            if (!flag) {
                return false;
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

    //初始化查询页面
    initQueryList : function() {
        ShopData.init();

        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/scm/template/stockcount/update',
            'scm_kry/scm/template/stockcount/view',
            'scm_kry/scm/template/stockcount/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;
        $.beforeSend = function (formData) {
            if (typeof formData.status == "object" || formData.status == undefined) {
                formData["isDisable"] = "";
            } else {
                formData["isDisable"] = formData.status;
            }
            return formData;
        };

        $.showEdit = function(rowData){
        	var flag = renderEnum.disabled;
        	if(!rowData.isDisable) flag = renderEnum.normal; //如果是ture则正常显示
        	return flag;
        };

        $.show = function (rowData) {
        	var flag = renderEnum.hidden;
        	if(!rowData.isDisable) flag = renderEnum.normal; //如果是ture则正常显示
        	return flag;
        };

        $.showLock = function (rowData) {
        	var flag = renderEnum.hidden;
        	if(rowData.isDisable) flag = renderEnum.normal; //如果是ture则正常显示
        	return flag;
        };

        $('#' + _this.opts.listGridId).dataGrid({
            formId: _this.opts.queryConditionsId,
            serializeGridDataCallback: $.beforeSend,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '模板编码', '模板名称', '编辑人', '最后修改时间', '状态', '操作'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'code', index: 'code', width: 160, align: 'left'},
                {name: 'name', index: 'name', width: 160, align: 'left'},
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
                    code: "scm:button:stockcount:template:edit",
                    url: _this.opts.urlRoot + _this.opts.editUrl
                },
                clock: {
                    url: _this.opts.urlRoot + _this.opts.lockUrl,
                    code: "scm:button:stockcount:template:lock",
                    render: $.show
                },
                unlock: {
                    url: _this.opts.urlRoot + _this.opts.unlockUrl,
                    code: "scm:button:stockcount:template:unlock",
                    render: $.showLock
                }
            }
        });

        function disableFormatter(cellValue, options, rowObject) {
            return rowObject.isDisable ? '<span style="color: red">停用</span>' : '启用';
        }
    },

    //初始化单据作业明细表格--商品信息
    initSkuDetailGrid : function(editable) {
        var _this = this;
        var $gridObj = $('#' + _this.opts.detailGridId);

        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            multiselect: editable,
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['skuId', '所属分类', '商品编码', '商品名称(规格)', '单位', '价格','非授权商户', '当前换算率', '标准单位换算率', '定价', '单位ID'],
            colModel: [
                {name: 'id', index: 'id', hidden: true},
                {name: 'skuTypeName', index: 'skuTypeName', sortable: false,width: 70, formatter: $.skuIsDisableFormat,unformat: unformatSpan},
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
                {name: 'uom', index: 'uom', width: 50, sortable: !editable,align: 'center', formatter: $.skuIsDisableFormat},
                //{name: 'uom', index: 'uom', width: 50, sortable: !editable, align: 'center'},
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
                //初始化本行非授权商户
                //var thisInput = $("#"+rowid).find(".checkbox-selected");
                //thisInput.selectMulti();//完成首次加载数据后初始化非授权商户下拉列表
                //thisInput.parents("td").css({"overflow" : "visible"}).removeAttr("title");
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
                {name: 'name',index: 'name',hidden: true,
                	formatter:function(data,opt,cell){
                		return data?data:(cell.id==-1?"品牌":cell.commercialName);
                	}
                },
                {name: 'commercialId', index: 'commercialId',  sortable: false,align: 'center',formatter: $.shopIdFormat},
                {name: 'commercialName', index: 'commercialName',  sortable: false,formatter: $.shopNameFormat},
                {name: 'commercialAddress', index: 'commercialAddress',  sortable: false,formatter: $.shopIdFormat},
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
    },

    //初始化商品类型
    initSkuTypeNames : function(details){
        var _this = this;
        var $skuTypeNameDiv = $("#skuTypeNameDiv");
        var skuTypeNames = [];
        var option = '';
        var select = '<select class="form-control" name="skuTypeName" id="skuTypeName"><option value="">请选择商品分类</option></select>';
        $skuTypeNameDiv.html(select);
        $.each(details, function(index, detail){
            if(skuTypeNames.indexOf(detail.skuTypeName) < 0){
                skuTypeNames.push(detail.skuTypeName);
                option += '<option value=' + detail.skuTypeName + '>' + detail.skuTypeName + '</option>';
            }
        });
        $('#skuTypeName').append(option);
        bkeruyun.selectControl($('#skuTypeName'));
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
        return data+"(<span style='color:red;'>品牌</span>)";
    } else if(typeof(cell.status) != "undefined" && cell.isSupply == 0) {
        return "<span style='color:#9D9D9D;'>" + data + '<span style="color:red;">(未开通供应链)</span></span>';
    }
	return data;
};

$.shopIdFormat = function(data,opt,cell){
	if(typeof(cell.status) != "undefined"&&cell.status!=0&&cell.id!=-1) return "<span style='color:#9D9D9D;'>"+data+'</span>';
	return data;
};
$.resetExceptSelect = function(){
	var currentShop = {},newData = $("#shop_grid").jqGrid('getRowData');
	for(var i=0;i<newData.length;i++){
		var name=newData[i].commercialName,notUsing = (name.indexOf("</span>")!=-1&&name.indexOf('<span style="color:red;">(未开通供应链)</span>')!=-1);
		currentShop[newData[i].id] = newData[i].name+(notUsing?"(未开通供应链)":"");
	}
	$.setSelectMultiData(currentShop);
};
//-------------------------格式化 end-----------------------

//单据明细校验
$.detailsValidator = function (args) {
	var pageShopTempateData = $("#shop_grid").getCol('id');
	if(pageShopTempateData.length==0){
		$.layerMsg('请勾选授权商户！若不勾选，则在商户下选择不到该盘点模板！', false);
        return false;
	}else{
		var allShop = new Array();
		for(var i=0;i<pageShopTempateData.length;i++) allShop.push({shopId:pageShopTempateData[i]});
		$("#shopTemplateDetails").val(JSON.stringify(allShop));
	}

    var rowData = $('#' + skuTemplate.opts.detailGridId).jqGrid('getRowData');
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
    		$.layerMsg("操作成功，"+$("#name").val()+"编码是：<span style='color:red;'>"+rs.data+"</span>", true, {end:function(){window.location.href = skuTemplate.opts.urlRoot + '/index';},shade: 0.3});
    	}else{
    		$.layerMsg("操作成功！", true, {end:function(){window.location.href = skuTemplate.opts.urlRoot + '/index';},shade: 0.3});
    	}
    } else {
        if (rs.data != '' && rs.data != null) {
            $.layerOpen("操作失败：" + rs.message, rs.data);
        } else {
            $.layerMsg("操作失败：" + rs.message, false);
        }
    }
};

//保存回调
$.submitCallbackBak = function (args) {
    var rs = args.result;
    if (rs.success) {
    	var showMsg = $("#code").val().length==0;
    	if(showMsg){
    		$.layerMsg("操作成功，"+$("#name").val()+"编码是：<span style='color:red;'>"+rs.data+"</span>",true,{shade: 0.3});
    	}else{
    		$.layerMsg("操作成功！",true,{shade: 0.3});
    	}
    } else {
        if (rs.data != '' && rs.data != null) {
            $.layerOpen("操作失败：" + rs.message, rs.data);
        } else {
            $.layerMsg("操作失败：" + rs.message, false);
        }
    }
};

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

$.afterAdd = function(){
    //初始化本行非授权商户
    var thisInput = $("input[name='multiSelectIpt']");
    thisInput.selectMulti();//完成首次加载数据后初始化非授权商户下拉列表
    thisInput.parents("td").css({"overflow" : "visible"}).removeAttr("title");
    $.filterGrid.initSkuTypeNames();
};
