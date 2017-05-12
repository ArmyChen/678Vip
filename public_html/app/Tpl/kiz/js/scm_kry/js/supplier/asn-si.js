/**
 * Created by mayi on 2015/6/3.
 */

var asnSi = {
    $listGrid : '',
    $detailGrid : '',
    //默认参数
    opts : {
        urlRoot : ctxPath,
        templateId : -1,
        commandType : 0,
        queryConditionsId : 'queryConditions',
        listGridId : 'grid',
        queryUrl : '&act=go_down_index_ajax2&type=1',
        editUrl : '&act=go_down_index_view',
        deleteUrl :'&act=go_down_delete_ajax',
        viewUrl : '&act=go_down_index_view',
        printUrl : '&act=go_down_print_view',
        confirmUrl : '&act=go_down_doconfirm&type=1',
        withdrawUrl : '&act=go_down_withdraw&type=1',
        copyUrl: '/copy',
        sortName : 'code',
        pager : '#gridPager',
        formId : 'baseInfoForm',
        detailGridId : 'grid',
        gridData : [],
        skuTypeNameDivId : 'skuTypeNameDiv',
        warehouseId :'#fromWmId',
        taxRate : '',
        skuScene:'',
        skuTypes:[]
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
                _this.initDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                break;
            case 9 ://新增出库
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initChukuDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                break;
            case 2 ://编辑
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(true);
                _this.checkWarehouse();
                $.filterGrid.initSkuTypeNames();
                break;
            case 7://复制
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(true);
                _this.checkWarehouse();
                $.filterGrid.initSkuTypeNames();
                _this.updateInfoWhenCopy();
                break;

            default ://查看
                $("select").siblings(".select-control").addClass("disabled");
                _this.$detailGrid = $('#' + _this.opts.detailGridId);
                _this.initDetailGrid(false);
                $.filterGrid.initSkuTypeNames();
                break;
        }
    },

    //检查仓库是否停用
    checkWarehouse: function () {
        var warehouseId = $("#warehouseId").val(),
            warehouseName = $("#warehouseName").val();

        if(warehouseId == '' && warehouseName != ''){
            $.layerMsg('仓库：' + warehouseName + '已被停用，请重新选择', false);
        }
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
            'scm_kry/asn/si/edit',
            'scm_kry/asn/si/view',
            'scm_kry/asn/si/add',
            'scm_kry/asn/si/copy'
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
            if(rowData.isdisable == 1){
                return renderEnum.normal;
            }else{
                return renderEnum.hidden;
            }
        };

        $.showWithDraw = function(rowData){
            if(rowData.isdisable == 2){
                return renderEnum.normal;
            }else{
                return renderEnum.hidden;
            }
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
        //更多查询
        $("#searchMore").on("click",function(){
            bkeruyun.searchMore(this,$("#commercialCol,#updateCol"),"更多条件","隐藏更多");
        });

        var $gridObj = $("#grid");
        $gridObj.dataGrid({
            formId: "queryConditions",
            serializeGridDataCallback: $.serializeGridDataCallback,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: [
                'id',
                '单据号',
                // '来源单据号',
                '部门',
                '供应商',
                '入库仓库',
                '入库金额',
                '制单人',
                '保存日期',
                '状态'
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
                // {name: 'ywsort', index: 'ywsort', width: 120, align: "center"},
                {name: 'gonghuo', index: 'gonghuo', width: 120, align: "center"},
                {name: 'gys', index: 'gys', width: 120, align: "center"},
                {name: 'cname', index: 'cname', width: 120, align: "center"},
                {name: 'zmoney', index: 'zmoney', align: "right", width: 70},
                {name: 'lihuo_user', index: 'lihuo_user', align: "center", width: 120},
                {name: 'ctime', index: 'ctime', align: "center", width: 120},
                {name: 'isdisable', index: 'isdisable', align: "center", width: 120,
                    formatter:function(v){
                        if(v==1){
                            return "已保存";
                        }else {
                            return "已确认";
                        }
                    }
                },
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
            sortname: 'ctime',
            pager: "#gridPager",
            showOperate: true,
            actionParam: {
                editor: {
                    url: supplierPath + _this.opts.editUrl,
                    // code: "scm:button:inventory:si:edit",
                    render: $.showEditor
                },
                view: {
                    url: supplierPath + _this.opts.viewUrl,
                    render: $.showView
                },
                confirm: {
                    url: supplierPath + _this.opts.confirmUrl,
                    // code: "scm:button:inventory:si:confirm",
                    render: $.showConfirm
                },
                withdraw: {
                	url:supplierPath + _this.opts.withdrawUrl,
                	// code: "scm:button:inventory:si:withdraw",
                    render: $.showWithDraw,
                    redirectUrl: _this.opts.urlRoot + _this.opts.editUrl
                },
                delete: {
                    url: supplierPath + _this.opts.deleteUrl,
                    // code: "scm:button:inventory:si:delete",
                    render: $.showDelete
                },
                print: {
                    url: supplierPath + _this.opts.printUrl,
                    render: $.showPrint
                },
                copy: {
                    url: supplierPath + _this.opts.copyUrl,
                    // code: "scm:button:inventory:si:add",
                    render: $.showCopy
                }
            }
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
        var priceModel = {
            name: 'price',
            index: 'price',
            width: 100,
            align: 'right',
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
            priceModel = $.extend(true, priceModel, editColModel || {});
        }

        //构造数据 添加字段:当前库存(隐藏) = 当前库存
        _this.opts.gridData.forEach(function(v,i){
            v.standardInventoryQty = v.inventoryQty;
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
            multiselect: (editable && _this.opts.isSourceOrderIdNull),
            showEmptyGrid: true,
            //height: 300,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['商品编码','skuId', '所属分类id', '所属分类', '商品条码', '商品名称(规格)', '单位', '单位', '价格', '入库数', '合计金额', '计算后库存', '当前库存(隐藏)', '当前换算率', '标准单位换算率', '定价', '标准单位ID', '标准单位'],
            colModel: [
                {name: 'id', index: 'id', width: 80, hidden: false, sortable: !editable},
                {name: 'skuId', index: 'skuId', width: 80, hidden: true, sortable: !editable},
                {name: 'skuTypeId', index: 'skuTypeId', width: 80, hidden: true, sortable: !editable},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, sortable: !editable},
                {name: 'skuCode', index: 'skuCode', width: 100, sortable: !editable},
                {name: 'skuName', index: 'skuName', width: 200, sortable: !editable},
                {name: 'uom', index: 'uom', width: 100, sortable: !editable,align: "center", hidden: editable},
                {name: 'uom', index: 'uom', width: 100, sortable: !editable, align: 'center', hidden: !editable,
                    formatter: $.unitSelectFormatter,
                    unformat : unformatSelect
                },
                priceModel,
                qtyColModel,
                {name: 'amount', index: 'amount', width: 100, align: 'right', sorttype:'number',sortable: !editable},
                {name: 'inventoryQty', index: 'inventoryQty', align: 'right', hidden: !editable, width: 100, sorttype:'number',sortable: !editable,
                    formatter: customMinusToRedFormatterWithUnit,
                    unformat: unformatSpan
                },
                {name: 'standardInventoryQty', index: 'standardInventoryQty', align: 'right', hidden: true},
                {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
                {name: 'standardUnitId', index: 'standardUnitId', align: "center", hidden: true},
                {name: 'standardUnitName', index: 'standardUnitName', align: "center", hidden: true}
            ],
            afterInsertRow: function (rowid, aData) {
                $gridObj.jqGrid('setRowData', rowid, {/* skuId: rowid, */ taxRate: _this.opts.taxRate});
                $gridObj.find(":text").first().keyup();

                $.removeSelectTitle(rowid); //移除下拉框列的表格title
            }
        });

        if(editable) $.delegateClickSelectGroup($gridObj);
    },
    /**
     * 复制单据时，更新商品的税率，总金额和总数量（商品可能存在停用删除未授权等情况，这些商品是不显示的，所以原来的总金额和总数量可能不正确）
     */
    updateInfoWhenCopy: function () {
        var _this = this;

        var $gridObj = _this.$detailGrid;
        var gridDataIDs = $gridObj.jqGrid('getDataIDs');
        var $qtySum = $("#qtySum"),
            $amountSum = $("#amountSum");
        if (gridDataIDs.length > 0) {
            $gridObj.find('.gridInput').first().trigger('propertychange');
        } else {
            $qtySum.text('');
            $amountSum.text('');
        }
    },

    //初始化出库单据作业明细表格
    initChukuDetailGrid : function(editable) {
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
        var priceModel = {
            name: 'price',
            index: 'price',
            width: 100,
            align: 'right',
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
            priceModel = $.extend(true, priceModel, editColModel || {});
        }

        //构造数据 添加字段:当前库存(隐藏) = 当前库存
        _this.opts.gridData.forEach(function(v,i){
            v.standardInventoryQty = v.inventoryQty;
        });

        scmSkuSelect.opts.dataGridCal = $gridObj.dataGridCal({
            formula: ['price*actualQty=amount','standardInventoryQty-actualQty=inventoryQty'],
            summary: [
                {colModel: 'inventoryQty', objectId: 'inventoryQtySum'},
                {colModel: 'actualQty', objectId: 'qtySum'},
                {colModel: 'amount', objectId: 'amountSum', showCurrencySymbol: true}
            ]
        });

        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            multiselect: (editable && _this.opts.isSourceOrderIdNull),
            showEmptyGrid: true,
            //height: 300,
            rownumbers: true,
            rowNum : 10000,
            colNames: ['商品编码','skuId', '所属分类', '商品条码', '商品名称(规格)', '单位', '单位', '价格', '出库数', '合计金额', '当前库存', '当前库存(隐藏)', '当前换算率', '标准单位换算率', '定价', '标准单位ID', '标准单位'],
            colModel: [
            {name: 'id', index: 'id', width: 80, hidden: false, sortable: !editable},
            {name: 'skuId', index: 'skuId', width: 80, hidden: true, sortable: !editable},
            {name: 'skuTypeName', index: 'skuTypeName', width: 80, sortable: !editable},
            {name: 'skuCode', index: 'skuCode', width: 100, sortable: !editable},
            {name: 'skuName', index: 'skuName', width: 200, sortable: !editable},
            {name: 'uom', index: 'uom', width: 50, sortable: !editable,align: "center", hidden: editable},
            {name: 'uom', index: 'uom', width: 50, sortable: !editable, align: 'center', hidden: !editable,
                formatter: $.unitSelectFormatter,
                unformat : unformatSelect
            },
            priceModel,
            qtyColModel,
            {name: 'amount', index: 'amount', width: 100, align: 'right', sorttype:'number',sortable: !editable},
            {name: 'inventoryQty', index: 'inventoryQty', align: 'right', hidden: !editable, width: 100, sorttype:'number',sortable: !editable,
                formatter: customMinusToRedFormatterWithUnit,
                unformat: unformatSpan
            },
            {name: 'standardInventoryQty', index: 'standardInventoryQty', align: 'right', hidden: true},
            {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
            {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
            {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
            {name: 'standardUnitId', index: 'standardUnitId', align: "center", hidden: true},
            {name: 'standardUnitName', index: 'standardUnitName', align: "center", hidden: true}],
            afterInsertRow: function (rowid, aData) {
            $gridObj.jqGrid('setRowData', rowid, {/* skuId: rowid, */ taxRate: _this.opts.taxRate});
            $gridObj.find(":text").first().keyup();

            $.removeSelectTitle(rowid); //移除下拉框列的表格title
        }
    });

        if(editable) $.delegateClickSelectGroup($gridObj);
    },
};

//单据明细校验
$.detailsValidator = function (args) {
    var $grid = $('#' + asnSi.opts.detailGridId);

    var gridData = $grid.jqGrid('getRowData');
    if (gridData.length == 0) {
        $.layerMsg('请先添加商品', false);
        return false;
    }

    // var qtyOfNoUom = $grid.find('.select-unit > .red-border').length;
    // if (qtyOfNoUom > 0) {
    //     $.layerMsg('还有商品未选择单位', false);
    //     return false;
    // }

    var qtySum = $grid.jqGrid('getCol', 'actualQty', false, 'sum');
    if (qtySum == 0) {
        $.layerMsg('出库数不能全部为0', false);
        return false;
    }

    return true;
};

//保存回调
$.saveCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        // var $id = $("#id");
        // if (!$id.val()) {
        //     $id.val(rs.data.id);
        //    replaceUrl('/asn/si/edit', 'id=' + rs.data.id);
        //     $("#command-type-name").text("编辑");
        //     document.title = '编辑出库单';
        //
        //     $("#btnCopy").removeClass("hidden");
        // }
        // $.layerMsg(, true);
        if(confirm(rs.message + ",是否继续添加？")){
            location.reload();
        }else{
            location.href=rs.data.url;
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

//确认回调
$.confirmCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        var id = rs.data.id;
        var url = asnSi.opts.urlRoot + '/view';
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

//查看页面打印方法
function print(id){
    $.print.showPrintDialog({
        urlRoot: asnSi.opts.urlRoot,
        query: {
            id: id
        }
    });
}