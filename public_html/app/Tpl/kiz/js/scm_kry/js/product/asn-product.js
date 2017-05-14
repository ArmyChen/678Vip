/**
 * Created by mayi on 2015/6/3.
 */

var asnProduct = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctxPath,
        templateId : -1,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=basic_product_index_ajax',
        editUrl : '&act=go_down_index_view',
        deleteUrl :'&act=go_down_delete_ajax',
        viewUrl : '&act=go_down_index_view',
        printUrl : '&act=go_down_print_view',
        confirmUrl : '/doconfirm',
        withdrawUrl : '/withdraw',
        sortName : 'code',
        pager : '#gridPager',
        _now : new Date(),
        formId : 'baseInfoForm',
        applyDateId : 'applyDate',
        arriveDateId : 'arriveDate',
        detailGridId : 'grid',
        gridData : [],
        currentSavedTempId : -1,//当前保存的模板id
        isTempEnable : true,//进入页面时的模板是否可用
        disableTempId : -1,//不可用的模板id
        disableTempGridData : [],//不可用模板的商品明细
        savedGridData : [],
        skuTypeNameDivId : 'skuTypeNameDiv',
        dataGridCal: new Object(),
        loginAsBrand: false
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType)
        {
            case 0 ://列表查询
                _this.$listGrid = $('#' + _this.opts.listGridId);
                _this.initQueryList();
                $.setSearchFocus();
                break;
            case 1 ://新增
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initTempSelect();
                _this.initDetailGrid(true);
                $.filterGrid.initSkuTypeNames();

                if ($("#senderId").val() != '') {
                    $("#senderId").change();
                }
                break;
            case 2 ://编辑
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initTempSelect();
                _this.initDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                 if($("#warehouseId").val()==''&&$("#warehouseName").val()!=''){
                 	$.layerMsg('仓库：'+$("#warehouseName").val()+'已被停用，请重新选择', false);
                 }
                break;

            default ://查看
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                $("select").siblings(".select-control").addClass("disabled");
                _this.initPrintButton();
                _this.initDetailGrid(false);
                $.filterGrid.initSkuTypeNames();
                break;
        }
    },

    //初始化模板选择方法
    initTempSelect : function() {
        var _this = this;
        $("#senderId").on("change", function() {
            var templateId = $(this).val();
            if (templateId == '') {
                return;
            }

            var isSkuVo = false;//true表示是商品vo对象，此时id即为skuId；为false表示是单据明细对象，此时skuId就是真实的skuId
            if (!_this.opts.isTempEnable && templateId == _this.opts.disableTempId) {
                _this.opts.gridData = _this.opts.disableTempGridData;
            } else if (templateId == _this.opts.currentSavedTempId) {
                _this.opts.gridData = _this.opts.savedGridData;
            } else {
                $.ajax({
                    type: "POST",
                    async: false,
                    url : _this.opts.urlRoot + "/getSkuOfTemplate?r=" + new Date().getTime(),
                    data : {
                        id: templateId,
                        whId: parseInt($("#warehouseId").val()) || -1
                    },
                    dataType : 'json',
                    success: function (data) {
                        _this.opts.gridData = data.skuVos;
                        isSkuVo = true;
                    }
                });
            }
            _this.reloadGrid(_this.opts.gridData, isSkuVo);

            var gridCal = _this.opts.dataGridCal;
            gridCal.summaryCalculate(gridCal.opts, _this.$detailGrid);
        });
    },

    //重新加载表格数据
    reloadGrid : function(data, isSkuVo) {
        var _this = this;
        _this.$detailGrid.clearGridData();
        for(var i = 0; i < data.length; i++){
            if (isSkuVo) {//true表示是商品vo对象，此时id即为skuId；为false表示是单据明细对象，此时skuId就是真实的skuId
                data[i].skuId = data[i].id;
            }
            _this.$detailGrid.jqGrid("addRowData", i, data[i]);
        }
        $.filterGrid.initSkuTypeNames();
    },

    /**
     * 初始打印按钮
     */
    initPrintButton : function() {

        var _this = this;

        $(document).delegate("#btnPrint", "click", function () {
            var id = $('#id').val();
            $.print.showPrintDialog({
                urlRoot: _this.opts.urlRoot,
                query: {
                    id: id
                }
            });
        });
    },

    //初始化单据作业明细表格
    initDetailGrid : function(editable) {
        var _this = this;
        var $gridObj = _this.$detailGrid;
        var qtyColModel = {
            name: 'actualQty',
            index: 'actualQty',
            align: 'right',
            width: 120,
            sorttype:'number',
            sortable: !editable
        };

        var editColModel = {
            editable: true,
            formatter: formatInputNumber,
            unformat: unformatInput
        };

        if (editable) {
            qtyColModel = $.extend(true, qtyColModel, editColModel || {});
        }

        //构造数据 添加字段:当前库存(隐藏) = 当前库存
        _this.opts.gridData.forEach(function(v,i){
            if(v.standardInventoryQty == 0 || v.standardInventoryQty == undefined ){
                v.standardInventoryQty = v.inventoryQty;
            }else{
                v.inventoryQty = parseInt(v.actualQty) + parseInt(v.standardInventoryQty);
            }
        });

        scmSkuSelect.opts.dataGridCal = $gridObj.dataGridCal({
            formula: ['price*actualQty=amount','actualQty+standardInventoryQty=inventoryQty'],
            summary: [
                {colModel: 'inventoryQty', objectId: 'inventoryQtySum'},
                {colModel: 'actualQty', objectId: 'qtySum'},
                {colModel: 'amount', objectId: 'amountSum', showCurrencySymbol: true}
            ]
        });

        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            //multiselect: true,
            showEmptyGrid: true,
            //height: 300,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['id','skuId', '所属分类', '商品编码', '商品名称(规格)', '单位', '价格', '入库数', '入库金额', '当前库存', '换算率', '标准单位换算率', '标准价格', '标准单位ID', '标准单位','当前库存（隐藏）'],
            colModel: [
                {name: 'id', index: 'id', width: 80, hidden: true, sortable: !editable},
                {name: 'skuId', index: 'skuId', width: 80, hidden: true, sortable: !editable},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, sortable: !editable},
                {name: 'skuCode', index: 'skuCode', width: 100, sortable: !editable},
                {name: 'skuName', index: 'skuName', width: 200, sortable: !editable},
                {name: 'uom', index: 'uom', width: 50, sortable: !editable, align: 'center'},
                {
                    name: 'price',
                    index: 'price',
                    width: 120,
                    align: "right",
                    sorttype:'number',
                    sortable: !editable
                    //formatter: customCurrencyFormatter
                },
                qtyColModel,
                {name: 'amount', index: 'amount', width: 150, align: 'right',sorttype:'number', sortable: !editable},
                {name: 'inventoryQty', index: 'inventoryQty', align: 'right', hidden: !editable, width: 150, sorttype:'number',sortable: !editable, formatter: customMinusToRedFormatter},
                {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
                {name: 'standardUnitId', index: 'standardUnitId', align: "center", hidden: true},
                {name: 'standardUnitName', index: 'standardUnitName', align: "center", hidden: true},
                {name: 'standardInventoryQty', index: 'standardInventoryQty', align: "center", hidden: true}
            ],
            afterInsertRow: function (rowid, aData) {
                //若没有金额或金额为0，则设置金额为0，fix bug 19905
                if (!aData.amount) {
                    $gridObj.jqGrid('setRowData', rowid, {amount: '0'});
                }
            }
        });

        _this.opts.dataGridCal = $gridObj.dataGridCal({
            formula: ['price*actualQty=amount'],
            summary: [
                {colModel: 'actualQty', objectId: 'qtySum'},
                {colModel: 'amount', objectId: 'amountSum', showCurrencySymbol: true}
            ]
        });
    },

    //商品过滤
    filterSku : function() {

        var skuTypeName = $('#skuTypeName').find('option:selected').text();
        if($('#skuTypeName').val() === ''){
            skuTypeName = '';
        }

        var conditions1 = {
            skuCode: $('#skuCodeOrName').val(),
            skuTypeName: skuTypeName
        };
        var conditions2 = {
            skuName: $('#skuCodeOrName').val(),
            skuTypeName: skuTypeName
        }


        var rowIds1 = filterGridRowIds('grid', conditions1);
        var rowIds2 = filterGridRowIds('grid', conditions2);
        Array.prototype.push.apply(rowIds1, rowIds2);
        filterGridRows('grid', rowIds1);
    },

    //初始化查询页面
    initQueryList : function() {
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/asn/product/edit',
            'scm_kry/asn/product/view',
            'scm_kry/asn/product/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;
        $.serializeGridDataCallback = function (formData) {
            if (typeof formData.status == "object" || formData.status == undefined) {
                formData["status"] = "-2";
            }
            return formData;
        };

        $.showEditor = function (rowData) {
            return renderEnum.hidden;
        };

        $.showView = function (rowData) {
            return renderEnum.normal;
        };

        $.showConfirm = function (rowData) {
            return renderEnum.hidden;
        };

        $.showWithDraw = function(rowData){
            return renderEnum.hidden;
        };

        $.showDelete = function (rowData) {
            return renderEnum.hidden;
        };

        $.showPrint = function (rowData) {
            return renderEnum.normal;
        };

        $.showCopy = function (rowData) {
            return renderEnum.hidden;
        };

        var $gridObj = $("#grid");
        $gridObj.dataGrid({
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: [
                'id',
                '单据号',
                // '来源单据号',
                '入库原因',
                '入库仓库',
                '入库金额',
                '制单人',
                // '部门',
                '保存日期'
                // '供货',
                // '理货员',
                // '金额',
                // '数量',
                // '体积',
                // '重量',
                // '物流',
                // '运费'
            ],
            colModel: [
                {name: 'id', index: 'id',  align: "center", width: 50},
                {name: 'danjuhao', index: 'danjuhao', align: "center", width: 120},
                // {name: '', index: '', align: "center", width: 120},
                {name: 'ywsort', index: 'ywsort', width: 120, align: "center"},
                {name: 'cname', index: 'cname', width: 120, align: "center"},
                {name: 'zmoney', index: 'zmoney', align: "right", width: 70},
                {name: 'lihuo_user', index: 'lihuo_user', align: "center", width: 120},
                // {name: 'gonghuo', index: 'gonghuo', align: "center", width: 120},
                {name: 'ctime', index: 'ctime', align: "center", width: 120},
                // {name: 'cname', index: 'cname', align: "center", width: 90},
                // {name: 'ywsort', index: 'ywsort', align: "center", width: 90},
                // {name: 'danjuhao', index: 'danjuhao', align: "center", width: 90},
                // {name: 'memo', index: 'memo', align: "center", width: 90},
                // {name: 'gonghuo', index: 'gonghuo', align: "center", width: 120},
                // {name: 'lihuo_user', index: 'lihuo_user', align: "center", width: 90},
                // {name: 'zmoney', index: 'zmoney', align: "center", width: 70},
                // {name: 'znum', index: 'znum', align: "center", width: 130},
                // {name: 'ztiji', index: 'ztiji', align: "center", width: 70},
                // {name: 'zweight', index: 'zweight', align: "center", width: 70},
                // {name: 'wuliu_company', index: 'wuliu_company', align: "center", width: 70},
                // {name: 'wuliu_yunfei', index: 'wuliu_yunfei', align: "center", width: 70},
            ],
            sortname: 'asnNo',
            pager: "#gridPager",
            showOperate: true,
            actionParam: {
                editor: {
                    url: inventoryPath + _this.opts.editUrl,
                    // code: "scm:button:inventory:si:edit",
                    render: $.showEditor
                },
                view: {
                    url: inventoryPath + _this.opts.viewUrl,
                    render: $.showView
                },
                confirm: {
                    url: inventoryPath + _this.opts.confirmUrl,
                    // code: "scm:button:inventory:si:confirm",
                    render: $.showConfirm
                },
                withdraw: {
                    url:inventoryPath + _this.opts.withdrawUrl,
                    // code: "scm:button:inventory:si:withdraw",
                    render: $.showWithDraw,
                    redirectUrl: _this.opts.urlRoot + _this.opts.editUrl
                },
                delete: {
                    url: inventoryPath + _this.opts.deleteUrl,
                    // code: "scm:button:inventory:si:delete",
                    render: $.showDelete
                },
                print: {
                    url: inventoryPath + _this.opts.printUrl,
                    render: $.showPrint
                },
                copy: {
                    url: inventoryPath + _this.opts.copyUrl,
                    // code: "scm:button:inventory:si:add",
                    render: $.showCopy
                }
            }
        });
    }
};

//单据明细校验
$.detailsValidator = function (args) {
    var qtySum = $('#' + asnProduct.opts.detailGridId).jqGrid('getCol', 'actualQty', false, 'sum');
    if (qtySum == 0) {
        //bkeruyun.promptMessage('申请数不能全部为0');
        $.layerMsg('入库数不能全部为0', false);
        return false;
    }

    return true;
};

//保存回调
$.saveCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        $.layerMsg("操作成功！", true, {
            end:function(){
                window.location.href = productPath + "&act=product_inventory_index";
            },shade: 0.3});
    } else {
        $.layerMsg("操作失败！", true,{shade: 0.3});
    }
};

//确认回调
$.confirmCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        var id = rs.data.id;
        var url = asnProduct.opts.urlRoot + '/view';
        var token_new = args.token;
        if(token_new) {
            $('#t').val(token_new);
        }
        $.doForward({"url":url, "postData":{"id":id}});
    } else {
        if (rs.data != '' && rs.data != null) {
            //bkeruyun.promptMessage("操作失败:" + rs.message, rs.data + "<br>");
            $.layerOpen("操作失败:" + rs.message, rs.data);
        } else {
            //bkeruyun.promptMessage("操作失败:" + rs.message);
            $.layerMsg("操作失败:" + rs.message, false);
        }
    }
};

/**
 * 编辑人格式化
 * @param cellvalue
 * @param options
 * @param rowObject
 * @returns {*}
 */
function updaterNameFormatter(cellvalue, options, rowObject) {

    if (!cellvalue) {
        return rowObject.creatorName;
    } else {
        return cellvalue;
    }
}