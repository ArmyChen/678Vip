var transferOrder = {
    $gridObj: '',
    //default opts
    opts: {
        urlRoot: ctxPath,
        queryUrl: '&act=diaobo_list_ajax',
        editUrl: '/edit',
        deleteUrl: '/delete',
        viewUrl: '&act=go_transfer_index_view',
        printUrl: '&act=go_transfer_print_view',
        confirmUrl: '/doconfirm',
        withdrawUrl: '/withdraw',
        //查询条件表单id
        queryFormId: 'queryConditions',
        //列表id
        listGridId: 'grid',
        //列表分页id
        pager: '#gridPager',
        //列表默认排序字段
        sortName: 'transferOrderNo',
        //列表默认排序方式
        sortOrder: 'desc',
        //编辑界面表单id
        editFormId: 'editForm',
        //表格数据
        gridData: [],
        //表格编辑标识
        editable: true
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        $gridObj = $('#' + _this.opts.listGridId);
        switch (_this.opts.commandType) {
            case 0 ://列表查询
                _this.initQueryList();
                $.setSearchFocus();
                break;
            case 1 ://新增
                //_this.checkCodeAndName();
                //_this.initDateSelect();
                _this.initDetailGrid(true);
                $.filterGrid.initSkuTypeNames();
                break;
            case 2 ://编辑
                //_this.checkCodeAndName();
                //_this.initDateSelect();
                _this.initDetailGrid(true);
                if ($("#removeWarehouse").val() == '' && $("#fromWmName").val() != '' && $("#moveWarehouse").val() == '' && $("#toWmName").val() != '') {
                    $.layerMsg('移出仓库：' + $("#fromWmName").val() + '已被停用，请重新选择。' + '移入仓库：' + $("#toWmName").val() + '已被停用，请重新选择。', false);
                } else {
                    if ($("#removeWarehouse").val() == '' && $("#fromWmName").val() != '') {
                        $.layerMsg('移出仓库：' + $("#fromWmName").val() + '已被停用，请重新选择', false);
                    }
                    if ($("#moveWarehouse").val() == '' && $("#toWmName").val() != '') {
                        $.layerMsg('移入仓库：' + $("#toWmName").val() + '已被停用，请重新选择', false);
                    }
                }
                $.filterGrid.initSkuTypeNames();
                break;
            default ://查看
                _this.initDetailGrid(false);
                $.filterGrid.initSkuTypeNames();
                break;
        }
    },

    //初始化查询界面
    initQueryList: function () {
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/transferorder/edit',
            'scm_kry/transferorder/view',
            'scm_kry/transferorder/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        //查询列表序列化回调
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

        $gridObj.dataGrid({
            formId: _this.opts.queryFormId,
            serializeGridDataCallback: $.serializeGridDataCallback,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id', '单据号', '保存日期', '移出仓库', '移入仓库', '移库金额', '状态', '状态'],
            colModel: [
                {name: 'id', index: 'id', width: 50, hidden: true},
                {name: 'transferOrderNo', index: 'transferOrderNo', width: 150, align: 'center'},
                {name: 'updateTime', index: 'updateTime', width: 160, align: 'center'},
                {name: 'fromWmName', index: 'fromWmName', width: 150, align: 'center'},
                {name: 'toWmName', index: 'toWmName', width: 150, align: 'center'},
                {
                    name: 'amount',
                    index: 'amount',
                    width: 120,
                    align: "right",
                    formatter: customCurrencyFormatter
                },
                {name: 'statusName', index: 'status', width: 70, align: 'center', hidden: true},
                {name: 'status', index: 'status', width: 100, hidden: true}
            ],
            sortname: _this.opts.sortName,
            sortorder: _this.opts.sortOrder,
            pager: _this.opts.pager,
            showOperate: true,
            actionParam: {
                editor: {
                    url: _this.opts.urlRoot + _this.opts.editUrl,
                    code: "scm:button:inventory:transfer:edit",
                    render: $.showEditor
                },
                view: {
                    url: inventoryPath + _this.opts.viewUrl,
                    render: $.showView
                },
                confirm: {
                    url: _this.opts.urlRoot + _this.opts.confirmUrl,
                    code: "scm:button:inventory:transfer:confirm",
                    render: $.showConfirm
                },
                withdraw: {
                	url: _this.opts.urlRoot + _this.opts.withdrawUrl,
                	code: "scm:button:inventory:transfer:withdraw",
                    render: $.showWithDraw,
                    redirectUrl: _this.opts.urlRoot + _this.opts.editUrl
                },
                delete: {
                    url: _this.opts.urlRoot + _this.opts.deleteUrl,
                    code: "scm:button:inventory:transfer:delete",
                    render: $.showDelete
                },
                print: {
                    url: inventoryPath +  _this.opts.printUrl,
                    render: $.showPrint
                }
            }
        });
    },

    //初始化编辑界面表格
    initDetailGrid: function (editable) {
        var _this = this;
        var planMoveQtyColModel = {
            name: 'planMoveQty',
            index: 'planMoveQty',
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

        if (_this.opts.editable) {
            planMoveQtyColModel = $.extend(true, planMoveQtyColModel, editColModel || {});
        }

        //构造数据 添加字段:当前库存(隐藏) = 当前库存
        _this.opts.gridData.forEach(function(v,i){
            if(v.standardInventoryQty == 0 || v.standardInventoryQty == undefined ){
                v.standardInventoryQty = v.inventoryQty;
            }
        });

        //绑定表格计算
        scmSkuSelect.opts.dataGridCal = $gridObj.dataGridCal({//表格计算
            formula: ['price*planMoveQty=amount','standardInventoryQty-planMoveQty=inventoryQty'],
            summary: [
                {colModel: 'planMoveQty', objectId: 'qtySum'},
                {colModel: 'amount', objectId: 'amountSum', showCurrencySymbol: true}
            ]
        });

        //var gridData = eval(_this.opts.gridData);
        $gridObj.dataGrid({
            data: _this.opts.gridData,
            datatype: 'local',
            multiselect: (_this.opts.editable && true),
            showEmptyGrid: true,
            rownumbers: true,
            rowNum : 10000,
            //height: 300,
            colNames: ['商品编码', '所属分类ID','所属分类', '商品条码', '商品名称(规格)', '单位', '单位', '价格', '移库数', '合计金额', '状态', '当前库存', '当前库存(隐藏)', '换算率', '标准单位换算率', '定价', '标准单位ID', '标准单位'],
            colModel: [
                {name: 'skuId', index: 'skuId', width: 80, hidden: false},
                {name: 'skuTypeId', index: 'skuTypeId', width: 80, hidden: true},
                {name: 'skuTypeName', index: 'skuTypeName', width: 80, sortable: !editable},
                {name: 'skuCode', index: 'skuCode', width: 100, sortable: !editable},
                {name: 'skuName', index: 'skuName', width: 200, sortable: !editable},
                {name: 'uom', index: 'uom', width: 50, align: 'center', sortable: !editable, hidden: editable},
                {name: 'uom', index: 'uom', width: 70, sortable: !editable, align: 'center', hidden: !editable,
                    formatter: $.unitSelectFormatter,
                    unformat : unformatSelect
                },
                {
                    name: 'price', index: 'price', width: 120, align: "right", sorttype:'number',sortable: !editable//formatter: customCurrencyFormatter
                },
                planMoveQtyColModel,
                {name: 'amount', index: 'amount', width: 150, align: 'right', sorttype:'number',sortable: !editable},
                {name: 'status', index: 'status', width: 150, hidden: true},
                {
                    name: 'inventoryQty',
                    index: 'inventoryQty',
                    align: 'right',
                    hidden: !editable,
                    width: 100,
                    sorttype:'number',
                    sortable: !editable,
                    formatter: customMinusToRedFormatterWithUnit,
                    unformat : unformatSpan
                },
                {name: 'standardInventoryQty', index: 'standardInventoryQty', align: 'right', hidden: true},
                {name: 'skuConvert', index: 'skuConvert', align: 'right', hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: 'right', hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
                {name: 'standardUnitId', index: 'standardUnitId', align: "center", hidden: true},
                {name: 'standardUnitName', index: 'standardUnitName', align: "center", hidden: true}
            ],

            afterInsertRow: function (rowid, aData) {
                //设置商品id等于行id
                //$gridObj.jqGrid('setRowData', rowid, {skuId: rowid});

                $.removeSelectTitle(rowid); //移除下拉框列的表格title
            }
        });

        if(editable) $.delegateClickSelectGroup($gridObj);
    }
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

//保存后回调函数
$.showOrderNo = function (args) {
    if (args.result.success) {
        var $id = $("input[name=id]");
        if (!$id.val()) {
            $id.val(args.result.data.id);//确保再次保存是更新操作，不是再次创建新的单据
            replaceUrl('', 'id=' + args.result.data.id);
            $("#command-type-name").text("编辑");
            document.title = '编辑移库单';
        }
    }
    $.defaultAjaxCallback(args);
};

//单据明细校验
$.transferOrderValidator = function (args) {
    var rowData = $gridObj.jqGrid('getRowData');
    if (rowData.length == 0) {
        $.layerMsg('请添加商品', false);
        return false;
    }
    var qtySum = $('#qtySum').html();
    if (qtySum == 0) {
        $.layerMsg('移库数量不能全部为0', false);
        return false;
    }
    return true;
};

//移入/移出仓库，移出仓库被选中项不在移入仓库显示
function warehouseInOff(inObjId, offObjId) {
    var inOptions = $(inObjId).find('option');
    var inLis = $(inObjId).parents(".select-group").find("ul").find("li");
    $(offObjId).change(function () {
        var value = $.trim($(this).val());
        //console.log(value);
        inOptions.each(function (i, option) {
            var inLi = inLis.eq(i);
            if (value != '' && value === $(this).val()) {
                inLi.hide();
            } else {
                inLi.show();
            }
        });
    });

    var offOptions = $(offObjId).find('option');
    var offLis = $(offObjId).parents(".select-group").find("ul").find("li");

    offOptions.each(function (i, option) {
        var inSelectedOptionVal = $(inObjId).find('option:selected').val();
        if (inSelectedOptionVal != '' && inSelectedOptionVal == $(option).val()) {
            offLis.eq(i).hide();
        }
    });
}

//查看页面打印方法
function print(id){
    $.print.showPrintDialog({
        urlRoot: transferOrder.opts.urlRoot,
        query: {
            id: id
        }
    });
};




//确认回调
$.confirmCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        var id = rs.data.id;
        var url = transferOrder.opts.urlRoot + '/view';
        var token_new = args.token;
        if(token_new) {
            $('#t').val(token_new);
        }
        $.doForward({"url":url, "postData":{"id":id}});
        return;
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