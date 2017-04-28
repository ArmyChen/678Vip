//自动保存标识
var flag = true;

var cctask = {

    opts : {
        urlRoot : ctxPath,
        commandType : '',
        queryConditionsId : 'queryConditions',
        listGridId : '#grid',
        queryUrl : '&act=count_task_ajax',
        editUrl : '/edit',
        viewUrl : '/view',
        confirmUrl : '&act=count_task_doconfirm',
        deleteUrl : '/delete',
        sortName : 'ccTaskNo',
        pager : '#gridPager',
        detailGridId : '#grid',
        details : [],
        editable : false,
        warehouseId : '#warehouseId',
        urlWebSocket : '',
        exportUrl:'/export',
        tooltipText1: '&nbsp;<span class="iconfont question color-g" data-content="创建盘点单时的库存数"></span>',
        tooltipText2: '&nbsp;<span class="iconfont question color-g" data-content="在盘点过程中，产生库存变化后，仓库当前的库存数（当单据确认后，实时库存不再变化）"></span>',
        ws: null,
        wsCloseType: 1,
        connectNum: 1,
        ccModel: 1
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        setInterval('activate()', 1000 * 60 * 5); // 每5分钟执行一次

        switch (_this.opts.commandType)
        {
            case 0 ://列表查询
                _this.initQueryList();
                $.setSearchFocus();
                break;
            case 1 ://新增
                _this.initDetailGrid();
                _this.delegateWarehouse();
                _this.initPrintButton();
                break;
            case 2 ://编辑
                 _this.initDetailGrid();
                _this.initPrintButton();
                _this.updateRealTimeQty();
                 break;
            case 3 ://查看
                _this.initDetailGrid();
                _this.initPrintButton();
                break;
            default :
                //_this.initDetailGrid();
                break;
        }
    },

    //初始化查询列表grid
    initQueryList : function(){
        //需要保存查询条件的页面
        var arrUrl = [
            'scm_kry/cc/task/edit',
            'scm_kry/cc/task/view',
            'scm_kry/cc/task/add'
        ];
        //保存查询条件
        $.setQueryData(arrUrl);

        var _this = this;

        var $listGrid = $(_this.opts.listGridId);

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
            return renderEnum.hidden;

        };

        $.showConfirm = function (rowData) {
            if(rowData.status == 1){
                return renderEnum.normal;
            }
        };

        $.showDelete = function (rowData) {
            return renderEnum.hidden;

        };

        $.showPrint = function (rowData) {
            return renderEnum.hidden;

        };

        $.showExport = function () {
            return renderEnum.hidden;
        };

        $listGrid.dataGrid({
            formId: _this.opts.queryConditionsId,
            serializeGridDataCallback: $.serializeGridDataCallback,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            colNames: ['id','单据号', '盘点仓库','模板名称', '盘盈金额', '盘亏金额','编辑人','最后修改时间', '状态', '状态','提示'],
            colModel: [
                {name: 'id', index: 'id', width: 120, align: "center",hidden:true},
                {name: 'ccTaskNo', index: 'ccTaskNo', width: 120, align: "center"},
                {name: 'warehouseName', index: 'warehouseName', width: 80},
                {name: 'templateName', index: 'templateName', width: 80,align: "center"},
                {
                    name: 'profitAmount',
                    index: 'profitAmount',
                    width: 90,
                    align: "right",
                    formatter: amountFormatterByCcModel
                },
                {
                    name: 'lossAmount',
                    index: 'lossAmount',
                    width: 90,
                    align: "right",
                    formatter: amountFormatterByCcModel
                },
                {name: 'updaterName', index: 'updaterName', width: 60, align: "center"},
                {name: 'updateTime', index: 'updateTime', width: 80, align: "center"},
                {name: 'statusName', index: 'status', width: 50, align: "center"},
                {name: 'status', index: 'status', width: 100, align: "center", hidden:true},
                {name: 'showNote', index: 'showNote', width: 100, align: "center", hidden:true}
            ],
            sortname: _this.opts.sortName,
            pager: _this.opts.pager,
            showOperate:true,
            actionParam: {
                editor: {
                    url: _this.opts.urlRoot + _this.opts.editUrl,
                    code: "scm:button:cc:cc:edit",
                    render: $.showEditor
                },
                view: {
                    url: _this.opts.urlRoot + _this.opts.viewUrl,
                    render: $.showView
                },
                confirm: {
                    url: _this.opts.urlRoot + _this.opts.confirmUrl,
                    code: "scm:button:cc:cc:confirm",
                    render: $.showConfirm
                },
                delete: {
                    url: _this.opts.urlRoot + _this.opts.deleteUrl,
                    code: "scm:button:cc:cc:delete",
                    render: $.showDelete
                },
                print: {
                    url: _this.opts.urlRoot,
                    render: $.showPrint
                }
                ,
                export: {
                    url: _this.opts.urlRoot+ _this.opts.exportUrl,
                    render: $.showExport
                }
            },
            gridComplete: function() {
                if( _this.opts.commandType=='0') {
                    var $gridObj = $(this);

                    var rowids = $gridObj.getDataIDs();
                    if (rowids.length > 0) {
                        for (var i = 0; i < rowids.length; i++) {
                            var rowData = $gridObj.getRowData(rowids[i]);
                            if (rowData.showNote == '1') {
                                $.layerMsg('请注意：销售、生产扣减仓库正在盘点，请确保盘点期间，销售端（POS）不进行操作！', false);
                                break;
                            }
                        }

                    }
                }
                _this.opts.commandType="";
            }
        });
    },


    /**
     * 商品列表JqGrid的初始化
     */
    initDetailGrid : function() {

        var _this = this;

        var sortable = true;

        var $detailGrid = $(_this.opts.detailGridId);

        //initSkuTypeNames(_this.opts.details);

        var colNames, colModel, gridCal;
        if (_this.opts.ccModel == 1) {//明盘

            colNames = ['skuId', '所属分类', '商品编码', '商品名称(规格)', '单位', '价格',
                '盘点初始库存'+cctask.opts.tooltipText1,
                '实时库存'+cctask.opts.tooltipText2,
                '盘点数', '盘点差异<br/><span style="font-size:12px; ">(盘点数-实时库存)</span>', '盘点差异金额', '备注', '盘点金额', '实时库存金额', '是否原数据'];

            colModel = [
                {name: 'skuId', index: 'skuId', width: 80, hidden: true, key: true},
                {name: 'skuTypeName', index: 'skuTypeName', width: 120, sortable: sortable},
                {name: 'skuCode', index: 'skuCode', width: 120, sortable: sortable},
                {name: 'skuName', index: 'skuName', width: 160,sortable: sortable},
                {name: 'uom', index: 'uom', width: 50, sortable: sortable, align: 'center'},
                {
                    name: 'price',
                    index: 'price',
                    width: 120,
                    align: "right",
                    sortable: sortable,
                    sorttype: 'float'
                },
                {
                    name: 'inventoryQty',
                    index: 'inventoryQty',
                    width: 120,
                    align: "right",
                    sortable: sortable,
                    formatter: customMinusToRedFormatter,
                    unformat: unformatSpan,
                    sorttype: 'float'
                },
                {
                    name: 'realTimeInventory',
                    index: 'realTimeInventory',
                    width: 100,
                    align: "right",
                    sortable: sortable,
                    formatter: function(cellValue, options, rowObject){
                        var icon = '';
                        if(cctask.opts.commandType == 3){
                            return cellValue;
                        }
                        if(parseFloat(cellValue) > parseFloat(rowObject['inventoryQty'])){
                            icon = '<i data-flag="trueFlag" class="glyphicon glyphicon-arrow-up red"></i>';
                        }else if(parseFloat(cellValue) < parseFloat(rowObject['inventoryQty'])){
                            icon = '<i data-flag="trueFlag" class="glyphicon glyphicon-arrow-down green"></i>';
                        }
                        return cellValue + icon;
                    },
                    unformat: unformatSpan,
                    sorttype: 'float'
                },
                {
                    name: 'ccQty',
                    index: 'ccQty',
                    align: 'right',
                    width: 170,
                    formatter: _this.opts.editable ? formatInputCcQty : null,
                    unformat: _this.opts.editable ? unformatInput : null,
                    editable: _this.opts.editable,
                    sortable: sortable,
                    sorttype: 'float'
                },
                {name: 'qtyDiff', index: 'qtyDiff', width: 120, align: 'right', sortable: sortable, sorttype: 'float'},
                {name: 'amountDiff', index: 'amountDiff', width: 120, align: 'right', sortable: sortable, sorttype: 'float'},
                {
                    name: 'remarks',
                    index: 'remarks',
                    width: 130,
                    formatter: _this.opts.editable ? formatInput : null,
                    unformat: _this.opts.editable ? unformatInputStr : null,
                    editable: _this.opts.editable,
                    sortable:false
                },
                {name: 'ccAmount', index: 'ccAmount', hidden: true},
                {
                    name: 'relTimeAmount',
                    index: 'relTimeAmount',
                    hidden: true,
                    formatter: function(cellValue, options, rowObject){
                        if(rowObject.relTimeAmount || rowObject.relTimeAmount == 0){
                            return rowObject.relTimeAmount;
                        }
                        return $.toFixed(parseFloat(rowObject.price) * parseFloat(rowObject.realTimeInventory));
                    }
                },
                {name: 'alreadyData', index: 'alreadData', hidden: true}
            ];

            gridCal = {
                formula: ['ccQty-realTimeInventory=qtyDiff', 'price*qtyDiff=amountDiff', 'price*ccQty=ccAmount', 'price*realTimeInventory=relTimeAmount'],
                summary: [
                    {colModel: 'amountDiff', objectId: 'profitAmountSum', requirement: 'amountDiff>0', showCurrencySymbol: true},
                    {colModel: 'amountDiff', objectId: 'lossAmountSum', requirement: 'amountDiff<0', showCurrencySymbol: true},
                    {colModel: 'ccAmount', objectId: 'ccAmountSum', showCurrencySymbol: true},
                    {colModel: 'relTimeAmount', objectId: 'inventoryAmountSum', showCurrencySymbol: true}
                ]
            };

        } else {//暗盘

            colNames = ['skuId', '所属分类', '商品编码', '商品名称(规格)', '单位', '价格', '盘点初始库存', '盘点数', '备注', '盘点金额', '是否原数据'];

            colModel = [
                {name: 'skuId', index: 'skuId', width: 80, hidden: true, key: true},
                {name: 'skuTypeName', index: 'skuTypeName', width: 120, sortable: sortable},
                {name: 'skuCode', index: 'skuCode', width: 120, sortable: sortable},
                {name: 'skuName', index: 'skuName', width: 160,sortable: sortable},
                {name: 'uom', index: 'uom', width: 50, sortable: sortable, align: 'center'},
                {
                    name: 'price',
                    index: 'price',
                    width: 120,
                    align: "right",
                    sortable: sortable,
                    sorttype: 'float'
                },
                {
                    name: 'inventoryQty',
                    index: 'inventoryQty',
                    width: 120,
                    align: "right",
                    hidden: true
                },
                {
                    name: 'ccQty',
                    index: 'ccQty',
                    align: 'right',
                    width: 170,
                    formatter: _this.opts.editable ? formatInputCcQty : null,
                    unformat: _this.opts.editable ? unformatInput : null,
                    editable: _this.opts.editable,
                    sortable: sortable,
                    sorttype: 'float'
                },
                {
                    name: 'remarks',
                    index: 'remarks',
                    width: 130,
                    formatter: _this.opts.editable ? formatInput : null,
                    unformat: _this.opts.editable ? unformatInputStr : null,
                    editable: _this.opts.editable,
                    sortable:false
                },
                {name: 'ccAmount', index: 'ccAmount', hidden: true},
                {name: 'alreadyData', index: 'alreadData', hidden: true}
            ];

            gridCal = {
                formula: ['price*ccQty=ccAmount'],
                summary: [
                    {colModel: 'ccAmount', objectId: 'ccAmountSum', showCurrencySymbol: true}
                ]
            };
        }

        _this.opts.details.forEach(function (detail,i) {
            detail.alreadyData = '1';
        });

        $detailGrid.dataGrid({
            data: _this.opts.details,
            datatype: 'local',
            showEmptyGrid: true,
            multiselect: (_this.opts.commandType != 3),
            //height: 500,
            rownumbers: true,
            rowNum: 9999,
            colNames: colNames,
            colModel: colModel,
            gridview:false,
            onSortCol: function (index,iCol,sortorder) {
                var $grid = $(this);
                var rowDatas = $grid.jqGrid('getRowData');
                this.p.data = rowDatas;
            },
            beforeSelectRow : function (rowid, e){
                if($('#jqg_grid_' + rowid).prop('disabled')){
                    return false;
                }else{
                    return true;
                }
            },
            onSelectAll : function (aRowids, status){
                if(status){
                    // uncheck "protected" rows
                    var cbs = $("tr.jqgrow > td > input.cbox:disabled", $detailGrid[0]);
                    cbs.removeAttr("checked");

                    //modify the selarrrow parameter
                    $detailGrid[0].p.selarrrow = $detailGrid.find("tr.jqgrow:has(td > input.cbox:checked)")
                        .map(function() { return this.id; }) // convert to set of ids
                        .get(); // convert to instance of Array

                    //deselect disabled rows
                    $detailGrid.find("tr.jqgrow:has(td > input.cbox:disabled)")
                        .attr('aria-selected', 'false')
                        .removeClass('ui-state-highlight');
                }
            },
            afterInsertRow: function (rowid, rowData) {
                if(rowData.alreadyData) {
                    $('#jqg_grid_' +  rowid).prop('disabled',true).css('opacity','0.3');
                }
            }
            //BUG 13576 【盘点单】反复移除商品，添加商品，导致添加商品显示不出名称信息，导致保存失败
            /*afterInsertRow: function (rowid, aData) {
                if(!aData.realTimeInventory){
                    aData.realTimeInventory = 0;
                }
                //设置盘点数量和合计金额为0 ,商品id等于行id
                var ccQty = aData.realTimeInventory < 0.0 ? 0 : aData.realTimeInventory;
                var qtyDiff = math.subtract(math.bignumber(ccQty), math.bignumber(aData.realTimeInventory));
                var amountDiff = math.multiply(math.bignumber(aData.price), math.bignumber(qtyDiff));
                $detailGrid.jqGrid('setRowData', rowid, {ccQty: ccQty, qtyDiff: qtyDiff, amountDiff: amountDiff});
            }*/
        });

        gridCal.customerFunc = function () {
            var currencySymbol = '￥';
            var profitAmountSum = $('#profitAmountSum').text().replace(currencySymbol,'').replace(/,/g,'');
            var lossAmountSum = $('#lossAmountSum').text().replace(currencySymbol,'').replace(/,/g,'');
            var inventoryAmountSum = $('#inventoryAmountSum').text().replace(currencySymbol,'').replace(/,/g,'');

            $('#profitRate').text(percent(profitAmountSum,inventoryAmountSum));
            $('#lossRate').text(percent(lossAmountSum,inventoryAmountSum));
        };

        //表格计算
        scmSkuSelect.opts.dataGridCal = $(_this.opts.detailGridId).dataGridCal(gridCal);

        scmSkuSelect.opts.dataGridCal.summaryCalculate(scmSkuSelect.opts.dataGridCal.opts, $(cctask.opts.detailGridId));
        scmSkuSelect.opts.dataGridCal.customerFunc();

        $.filterGrid.initSkuTypeNames();
    },

    //绑定仓库的change事件
    delegateWarehouse : function(){

        var _this = this;

        /** 监听仓库的改变 **/
        $(document).delegate(_this.opts.warehouseId, 'change', function(){

            var warehouseId = $(this).val();

            var deductionName=$('#deductionName'+warehouseId).val();

            if(warehouseId != undefined && warehouseId != ''){
                selectWarehouse(warehouseId,deductionName);
            }
        });
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

    //建立webSocket接受实时推送信息
    updateRealTimeQty : function(){
        if (cctask.opts.ccModel == 2) {//暗盘
            return;
        }

        if(cctask.opts.connectNum <=0){
            $.layerMsg('实时库存更新中断，请保存单据后刷新页面重试', false);
            return;
        }

        cctask.opts.wsCloseType = 1;
        var wareHouseId = $('#warehouseId').val();

        cctask.opts.ws = new WebSocket(cctask.opts.urlWebSocket + wareHouseId);
        cctask.opts.ws.onmessage = function(evt){
            var list = JSON.parse(evt.data);
            list.forEach(function(object,index){
                var row = $('#grid').jqGrid('getRowData', object.id);
                $('#grid').jqGrid('setRowData', object.id, {
                    realTimeInventory : object.inventoryQty,
                    qtyDiff : $.toFixed(parseFloat(row.ccQty) - parseFloat(object.inventoryQty)),
                    amountDiff : $.toFixed((parseFloat(row.ccQty) - parseFloat(object.inventoryQty)) * parseFloat(row.price)),
                    relTimeAmount : $.toFixed(parseFloat(object.inventoryQty) * parseFloat(row.price)),
                    inventoryQty : row.inventoryQty,
                    price : row.price
                });
            });
            //$('#grid').trigger('reloadGrid'); //注释原因:盘点数去除了gridinput类，修改后没有加入缓存中，reload会显示上次保存的缓存
            scmSkuSelect.opts.dataGridCal.summaryCalculate(scmSkuSelect.opts.dataGridCal.opts, $(cctask.opts.detailGridId));
            scmSkuSelect.opts.dataGridCal.customerFunc();
        };
        cctask.opts.ws.onclose = function(evt){
            if(cctask.opts.wsCloseType == 1){
                cctask.opts.connectNum--;
                cctask.updateRealTimeQty();
                // console.log('onclose...reconnect websocket......' + new Date());
            }
        };
        cctask.opts.ws.onerror = function(evt){
            if(cctask.opts.wsCloseType == 1){
                cctask.opts.connectNum--;
                cctask.updateRealTimeQty();
                // console.log('onerror...reconnect websocket......' + new Date());
            }
        };
    }
};

//单据明细校验
$.saveOrUpdateValidator = function (args) {

    var rowData = $(cctask.opts.detailGridId).jqGrid('getRowData');

    if (rowData.length == 0) {
        //$.showMsgBar('error', '商品列表为空，无法保存盘点单！请尝试选择要盘点的仓库，系统将自动加载商品列表！');
        //$.layerMsg('商品列表为空，无法保存盘点单！请尝试选择要盘点的仓库，系统将自动加载商品列表！', false);
        $.layerMsg('商品列表为空，无法保存盘点单！', false);
        return false;
    }
    return true;
};

$.saveCallback = function(args){
	if(args.result.success) if($("#templateId").val()!=-1) hideTemplate(true);
    $.defaultAjaxCallback(args);
};

$.confirmAfter = function (args) {
    cctask.opts.wsCloseType = 2;
    if (cctask.opts.ws) {
        cctask.opts.ws.close();
    }
};

/**
 * 选择仓库行为，将包括如下业务：
 *      1、检查仓库加锁与否，是-恢复仓库下拉框，否-第2步；
 *      2、让用户确认是否对该仓库盘点，是-第3步，否-恢复仓库下拉框；
 *      3、创建盘点单；
 * @param warehouseId
 */
selectWarehouse = function(warehouseId,deductionName){

    $.ajax({
        url: ctxPath + '&act=count_task_isLocked',
        type: "post",
        data: {warehouseId: warehouseId},
        dataType: 'text',
        async: false,
        success: function(data){
            if(data === 'true'){
                //$.showMsgBar('error', '该仓库正在盘点中，不能重复盘点！');
                $.layerMsg('该仓库正在盘点中，不能重复盘点！', false);
                undoSelectWarehouse();
                return false;
            } else{
                var msg='确定对该仓库进行盘点？';
                var commercialId=$('#commercialId').val();
                if(commercialId!=-1&&deductionName!=null&&deductionName!=''){
                    msg='《销售扣减》或《生产扣减》的仓库在盘点期间，为保证盘点数据的准确性，请确保盘点期间，销售端（POS）不进行操作!';
                }
                layer.confirm(msg, {icon:3, offset: '30%'} , function(index){
                    var status = createCcTask(warehouseId); //创建盘点单
                    // hideTemplate(false);
                    hideTemplate(!status);  // fix bug 15306 【盘点单】所选盘点仓库无库存，选择模版提示信息优化
                    layer.close(index);
                }, function(){
                    undoSelectWarehouse(); // 重置仓库选择
                });
            }
        },
        error:function(xhr, status, error){

        }
    });
};




/**
 * 当用户取消仓库的选择（或该仓库已处于盘点状态）时，恢复到未选择状态
 * @returns {boolean}
 */
undoSelectWarehouse = function(){

    $('#warehouseId').parent().find('ul li:first').click();
    return false;
};

/**
 * 根据仓库id，加载商品及其库存，创建盘点单。
 * 具体包括：
 *          （1）后台创建盘点单及其明细；
 *          （2）前台加载商品至列表；
 *          （3）前台显示盘点单号，同时仓库和盘点单号置灰、只读；
 *          （4）前台给各种“金额合计”赋值；
 * @param warehouseId
 */
createCcTask = function(warehouseId){
    var status = false;

    $.ajax({
        url: cctask.opts.urlRoot + '&act=count_task_saving_ajax',
        type: "post",
        data: {warehouseId: warehouseId},
        dataType: 'json',
        async: false,
        success: function(result){

            if(result.success == false){
                //$.showMsgBar('error', result.message);
                $.layerMsg(result.message, false);
                undoSelectWarehouse();
                return false;
            }

            status = true;

            reloadGrid(result);
            enableCcTaskNo(result.ccTaskNo); // 展示盘点单号
            enableWarehouseName(); //锁定仓库
            enableRemarks(); // 展示盘点单号
            $('input[name=id]').val(result.id); // id将传回后台

            $('#inventoryAmountSum').text("￥" + result.inventoryAmount);
            $('#ccAmountSum').text("￥" + result.ccAmount);
            $('#profitAmountSum').text("￥" + result.profitAmount);
            $('#lossAmountSum').text("￥" + result.lossAmount);
            $("#btnPrint2").show();//显示打印按钮
            $("#btnExport").show();//显示导出按钮

            //更改url新增地址为编辑地址，改变页面title等
            replaceUrl(ctxPath+'&act=count_task_edit', 'id=' + result.id);
            $("#command-type-name").text("编辑");
            document.title = '编辑盘点单';

            cctask.updateRealTimeQty();
        },
        error:function(xhr, status, error){

        }
    });

    return status;
};

/**
 * 重新加载JqGrid数据
 */
reloadGrid = function(ccTaskVo){
    if (ccTaskVo.ccModel) {
        cctask.opts.ccModel = ccTaskVo.ccModel;
        $("#ccModel").val(ccTaskVo.ccModel);
    }

    cctask.opts.details = ccTaskVo.details;

    if (cctask.opts.ccModel == 1) {
        $("#ccAmoutSpan").siblings().show();
    } else {
        $("#ccAmoutSpan").siblings().hide();
        $("#ccByShadeTitel").show();
    }

    $("#gridDiv").empty().append('<table id="grid"></table>');

    cctask.initDetailGrid();
};

/**
 * 用户选定仓库后，应对仓库进行锁定，使其为disabled，用户无法选择其他仓库
 */
enableWarehouseName = function(){

    $('#warehouseId').parents('.pull-left').hide();

    $('#warehouseName').val($('#warehouseId').find('option:selected').text());
    $('#warehouseName').parents('.pull-left').show();
    $('#warehouseName').css('background-color', '#dfdfdf');
}

/**
 * 用户选定仓库后，应对仓库进行锁定，使其为disabled，用户无法选择其他仓库
 */
enableRemarks = function(){

    $('#remarks').parents('.pull-left').show();
}

/**
 * 此时（用户成功选定了仓库），盘点单号已生成，应展示给用户
 * @param ccTaskNo
 */
enableCcTaskNo = function(ccTaskNo){

    $('#ccTaskNo').val(ccTaskNo);
    $('#ccTaskNo').parents('.pull-left').show();
    $('#ccTaskNo').css('background-color', '#dfdfdf');
}

//确认回调
$.confirmCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        var id = rs.data.id;
        var url = cctask.opts.urlRoot + '/view';
        var token_new = args.token;
        if(token_new) {
            $('#t').val(token_new);
        }
        $.doForward({"url":url, "postData":{"id":id}});
    } else {
        cctask.updateRealTimeQty();
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
 * 每隔一段时间发一次请求，使得session不过期
 */
activate = function() {
    /**
     * 根据市场反馈临时将业务改成自动保存
     */

    // var url = cctask.opts.urlRoot + '/activate?t=' + new Date().getTime();
    //
    // $.post(url, function(msg){
    //     console.log('activate page ...' + msg + ',' + new Date());
    // });

    var commandType = cctask.opts.commandType;
    var isEditable =  commandType != 1 && commandType != 2;

    //判断在是否开始盘点，如否则不用定时自动保存
    if(isEditable || !$('input[name=id]').val() || flag == false)
        return false;

    //自动保存
    var args = {
            formId:'saveOrUpdateForm',
            gridId:'grid',
            url: cctask.opts.urlRoot + '/update',
            customValidator:'$.saveOrUpdateValidator',
            submitCallback:'$.autoSaveCallback',
            isAuto : true  //是否是自动保存
    }

    //回调函数
    $.autoSaveCallback = function (args) {
        var rs = args.result;
        if(!rs.success) {
            $.layerMsg("自动保存失败，" + rs.message, false);
            flag = false;
        }
    }

    layer.msg('自动保存中', {
            icon: 16,
            shade: 0.01,
        },
        $.formSubmitWithAjax(args)
    );
   };


/**
 * 生成一个输入浮点数的input
 * @param cellvalue
 * @param options
 * @param rowObject
 * @returns {string}
 */
function formatInputCcQty(cellvalue, options, rowObject) {
    if(!rowObject['skuId']){
        rowObject = $(cctask.opts.detailGridId).jqGrid('getRowData',options.rowId);
    }
    var colName = options.colModel.name;
    var skuId=rowObject['skuId'];
    var skuName=rowObject['skuName'];
    var str = '<input type=\'text\'  style=\'width:75%;height:100%;float:left;\' autocomplete=\'off\' data-limit=\'{8,5}\'';
    str += 'class=\'text-right number ' + colName + '\'';
    str += 'data-format=\'float\' placeholder=\'0\' ';
    str += 'id=\'' + options.rowId + '_' + options.colModel.name + '\' ';
    str += 'name=\'' + colName + '\' ';
    if (cellvalue != '' && cellvalue != undefined) {
        str += 'value=\'' + cellvalue + '\' ';
    }
    //设置gridId和rowId
    str += 'row-id=\'' + options.rowId + '\' grid-id=\'' + options.gid + '\'';
    str += '>';
    var parentId=options.rowId + '_' + options.colModel.name;
    str += '<a href=\'javascript:void(0);\' onfocus=\'this.blur();\' title=\'计算器\' onclick=\'getUnit('+skuId+',"'+parentId+'","'+skuName+'")\' id=\'calculator\' class=\'icon-counter\'></a> ';
    return str;
}

function getUnit(skuId,parentId,skuName){
    $.ajax({
        type: "POST",
        url: cctask.opts.urlRoot +"/getUnit",
        data: {skuId:skuId},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        async: false,
        cache: false,
        success: function (data) {
            var dataJson=JSON.parse(data);
            if (dataJson.length > 0) {
                var str='<div>';
                str += '<div style="text-align: center;padding: 20px;line-height:40px;">';
                var unitStander;
                var unitStanderConvert;
                for (var i = 0; i < dataJson.length; i++) {
                    var each = dataJson[i];
                    str += '<input type=\'text\'  style=\'width:120px;height:34px\' autocomplete=\'off\' data-limit=\'{8,5}\'';
                    str += 'class=\'text-right number gridInput ' + '\'';
                    str += 'data-format=\'float\' placeholder=\'0\' ';
                    str += 'id=\'' + 'unit_' + i + '\' ';
                    str += 'name=\'' + 'unit_' + i + '\' ';
                    str += '>' + each.unitName;

                    str += '<input type=\'hidden\' ';
                    str += 'id=\'' + 'skuConvert_' + i + '\' ';
                    str += 'name=\'' + 'skuConvert_' + i + '\' ';
                    str += 'value=\'' + each.skuConvert+ '\' ';
                    str += '>';

                    if (i + 1 < dataJson.length) {
                        str += '<span style="font-size:18px;font-weight: bold;">&nbsp;+&nbsp;</span>';
                    } else {
                        str += '<span style="font-size:18px;font-weight: bold;">&nbsp;=&nbsp;</span>';
                    }
                    if (each.unitStander == 1) {
                        unitStander = each.unitName;
                        unitStanderConvert = each.skuConvert;
                    }
                }
                str += '<input type=\'text\' disabled  style=\'width:120px;height:34px;\' autocomplete=\'off\' data-limit=\'{8,5}\'';
                str += 'class=\'text-right number gridInput ' + '\'';
                str += 'data-format=\'float\' placeholder=\'0\' ';
                str += 'id=\'' + 'unitStander' + '\' ';
                str += 'name=\'' + 'unitStander' + '\' ';
                str += '>' + unitStander;
                str += '</div>';
                str += '<div id="wrong" style="text-align: center;width: 100%;color: red;display: none;margin-bottom:10px;">';
                str += '盘点数不能超过99999999.99999！';
                str += '</div>';
                str += '<div class=\'layui-layer-btn\'>';
                str += '<a id=\'confirm\' class=\'layui-layer-btn0\'>确定</a>';
                str += '<a id=\'cancel\' class=\'layui-layer-btn1\'>取消</a>';
                str += '</div>';
                str += '</div>';
                var index=layer.open({
                    title: '多单位换算（' + skuName + '）',
                    type: 1,
                    area: 'auto',
                    content: str,
                    maxWidth: 800,
                    yes: function(index, layero){
                        //输入值为0时不回显
                        if(parseFloat(parentValue) != 0){
                            if(parseFloat(parentValue) > 99999999.99999){
                                $('#wrong').show();
                                return;
                            }

                            var parentCC=$("#"+parentId , parent.document) ;
                            parentCC.val(parentValue);
                            parentCC.trigger('propertychange');
                        }
                        layer.close(index);
                    },
                    cancel: function(index, layero){
                        layer.close(index);
                    }
                });
                var parentValue=0;
                $("input").keyup(function () {
                    var unitStanderVol = 0;
                    for (var i = 0; i < dataJson.length; i++) {
                        if($("#unit_" + i).val()!='') {
                            var value = parseFloat($("#unit_" + i).val()) * parseFloat($("#skuConvert_" + i).val()) / parseFloat(unitStanderConvert);
                            unitStanderVol = unitStanderVol + value;
                        }
                    }
                    $("#unitStander").val($.toFixed(unitStanderVol));
                    parentValue=$("#unitStander").val();
                });
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $.layerMsg("获取单位失败，请联系管理员！", false);
        }
    });
}

//保存-导出回调
$.saveCallbackExport = function(args){
	if(args.result.success){
		if($("#templateId").val()!=-1) hideTemplate(true);

		var id = $("#id").val();
		window.open('/scm_kry/cc/task/export?id='+id,"_self");
	}else{
		 $.layerMsg("操作失败:" + rs.message, false);
	}
};

//保存-打印回调
$.saveCallbackPrint = function(args){
	if(args.result.success){
		if($("#templateId").val()!=-1) hideTemplate(true);

		 var id = $('#id').val();
		 $.print.showPrintDialog({
            urlRoot: cctask.opts.urlRoot,
            query: {
                id: id
            }
        });
	}else{
		 $.layerMsg("操作失败:" + rs.message, false);
	}
};

//查看页面的导出
$("#btnExport_view").on("click",function(){
	var id = $("#id").val();
	window.open('/scm_kry/cc/task/export?id='+id,"_self");
});
/**
 * 根据盘点方式格式化盘盈盘亏金额
 * @param cellvalue
 * @param options
 * @param rowObject
 */
function amountFormatterByCcModel(cellvalue, options, rowObject) {
    if (rowObject.ccModel == 2 && rowObject.status == 0) {//未确认的暗盘
        return '-';
    }

    return customCurrencyFormatter(cellvalue, options, rowObject);
}

function percent(member,denominator){
    var member = parseFloat(member);
    var denominator = parseFloat(denominator);
    var rs = 0;

    if(denominator <= 0){
        return '-';
    }
    rs = member / denominator * 100;
    return rs.toFixed(2) + '%';
}