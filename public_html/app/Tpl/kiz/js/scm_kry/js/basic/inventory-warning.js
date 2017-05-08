/**
 * 库存预警
 * Created by anq on 2016/9/22.
 */

var inventoryWarning = {
    //默认参数
    opts : {
        urlRoot : "/inventoryWarning",
        querySingleUrl : '/selectSingleSku', //单仓库设定
        queryMultiUrl : '/selectMultiSku', //多仓库设定
        saveSingleUrl: '/saveSingle',
        saveMultiUrl: '/saveMulti',
        batchSetUrl: '/batchSet',
        commandType : '',
        singleShowed : false,
        multiShowed : false,
        warehouseId:'',
        warehouseIds:[],
        gridData:[],
        editable : false,
        type:'',
        skuDetailsGridOpts: {
            data: [],
            datatype: 'local',
            multiselect: $('#editable').val()=='true'?true:false,
            showEmptyGrid: true,
            height: 600,
            width: $('.panel-body').width() - 18,
            rowNum: 9999,
            rownumWidth: 40,
            rownumbers: true,
            shrinkToFit: false,
            colNames: ['商品id', /*'库存类型',*/ '商品分类','商品状态', '商品编码', '商品名称(规格)', '单位'],
            colModel: [
                {name: 'skuId', index: 'skuId', width: 100, align: 'left',frozen : true,hidden:true,key:true},
                // {name: 'wmTypeName', index: 'wmTypeName', width: 100, align: 'left',frozen : true,sortable: !($('#editable').val()=='true'?true:false)},
                {name: 'skuTypeName', index: 'skuTypeName', width: 100, align: 'left',frozen : true,sortable: !($('#editable').val()=='true'?true:false)},
                {name: 'isDisable', index: 'isDisable', width: 100, align: 'left',frozen : true,hidden:true},
                {name: 'skuCode', index: 'skuCode', width: 100, align: "left",frozen : true,sortable: !($('#editable').val()=='true'?true:false)},
                {name: 'skuName', index: 'skuName', width: 100, align: 'left',frozen : true,sortable: !($('#editable').val()=='true'?true:false),
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 1) {
                            return "<span>" + cellvalue + "<span style='color:red'>(已停用)</span></span>";
                        } else {
                            return cellvalue;
                        }
                    }},
                {name: 'uom', index: 'uom', width: 80, align: "left",frozen : true,sortable: !($('#editable').val()=='true'?true:false)}
            ]
        },
        lowerInventory:'lowerInventory',
        safetyInventory:'safetyInventory',
        upperInventory:'upperInventory',
        exportConditions: [], //导出的查询条件
        exportConditions2: [], //导出的查询条件
        warehouses : [],
        tooltipText1: '&nbsp;<span class="iconfont question color-g" data-content="在不影响经营的情况下，最少应该存放的库存数量"></span>',
        tooltipText2: '&nbsp;<span class="iconfont question color-g" data-content="为了防止出现临时用量增减，或者交货延期的情况从而预计的保险存储量。"></span>',
        tooltipText3: '&nbsp;<span class="iconfont question color-g" data-offset="left" data-content="在库存允许的前提下，不发生呆料库存的基础上，最大可以存放的库存数量"></span>',
        errorMsg: '设置项必须满足库存上限>安全库存>库存下限>=0',
        excelNum:{}
    },

    //初始化
    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        _this.initWarehouseSelect();

        //tab标签切换
        $('.tab-nav .tab-btn').on('click', function (e) {
            if ($(this).hasClass("btn-active")) {
                return;
            }

            $(this).removeClass('btn-default').addClass('btn-active');
            $(this).siblings('button').removeClass('btn-active').addClass('btn-default');
            //显示区域
            $('.tab-box').hide();
            $('#' + $(this).attr("data-box-id")).show();

            //显示导出按钮
            $('.freeDownExcel').hide();
            $('#' + $(this).attr("down-excel-id")).show();

            $('.save').hide();
            $('#' + $(this).attr("save-id")).show();

            $('.edit').hide();
            $('#' + $(this).attr("edit-id")).show();
            if(!_this.opts.singleShowed && e.target.id == 'tabNav1'){
                var html = '<table id="grid" ></table>';
                var $skuDetailsTab = $('#skuDetailsTab');
                $skuDetailsTab.empty().html(html);
                _this.buildTable();
            }
            if(!_this.opts.multiShowed && e.target.id == 'tabNav2'){
                _this.buildTable_2();
            }

        });

        if(_this.opts.commandType=='2') {
            if(_this.opts.type=='1'){
                $('#tabNav1').click();
            }
            if(_this.opts.type=='2'){
                $('#tabNav2').click();
            }
        }else{
            _this.buildTable();
        }

        $.setSearchFocus();

        bindBatchSet();
    },

    //返回查询条件
    getExportConditions : function() {
        return this.opts.exportConditions;
    },

    //返回查询条件
    getExportConditions2 : function() {
        return this.opts.exportConditions2;
    },

    //初始化仓库选择事件
    initWarehouseSelect : function() {
        var _this = this;
        var $warehouseId = $("#warehouseId");

        var isCancel = false,lastChoice=$warehouseId.val();

        $(document).delegate("#warehouseId", "change", function () {
            if ($warehouseId.val() == '') {
                return;
            }
            //重置触发控制
            if(isCancel){
                isCancel = false;
                return false;
            }
            if($warehouseId.val()==lastChoice){
                return false;
            }
            if (inventoryWarning.opts.commandType=='2'){
                if(lastChoice==''){
                    _this.opts.warehouseId = $warehouseId.val();
                    var html = '<table id="grid" ></table>';
                    var $skuDetailsTab = $('#skuDetailsTab');
                    $skuDetailsTab.empty().html(html);
                    _this.buildTable();
                    lastChoice=$warehouseId.val();
                }else {
                    layer.confirm('切换仓库，将会清空当前页面的预警设定信息，确定执行？', {icon: 3, offset: '30%'}, function (index) {
                        _this.opts.warehouseId = $warehouseId.val();
                        var html = '<table id="grid" ></table>';
                        var $skuDetailsTab = $('#skuDetailsTab');
                        $skuDetailsTab.empty().html(html);
                        _this.buildTable();
                        lastChoice=$warehouseId.val();
                        layer.close(index);
                    }, function (index) {
                        isCancel = true;
                        restWarehouse(lastChoice);//重置为上一个
                        layer.close(index);
                        return false;
                    });
                }
            }else{
                _this.opts.warehouseId = $warehouseId.val();
                lastChoice=$warehouseId.val();
                var html = '<table id="grid" ></table>';
                var $skuDetailsTab = $('#skuDetailsTab');
                $skuDetailsTab.empty().html(html);
                _this.buildTable();
            }
        });
    },

    //单仓库设定
    buildTable : function () {
        var _this = this;

        var $gridDiv =  $("#gridDiv");
        var $grid = $("#grid");
        var warehouseId=_this.opts.warehouseId;

        var exportConditions = [];
        var warehouseName = $("#warehouseId").find("option:selected").text().trim();
        var commercialName=$("#commercialName").val();
        exportConditions.push({key : "商户", value : commercialName});
        exportConditions.push({key : "设定仓库", value : warehouseName});
        exportConditions.push({key : "导出人", type : 'user'});
        exportConditions.push({key : "导出时间", type : 'date'});

        $.ajax({
            url: ctxPath + inventoryWarning.opts.urlRoot + _this.opts.querySingleUrl,
            type: 'post',
            data: {warehouseId : warehouseId},
            dataType: 'json',
            async: false,
            success: function(data){
                _this.opts.exportConditions = exportConditions;
                _this.opts.gridData=data;
            }
        });

        $grid.dataGrid({
            data: _this.opts.gridData,
            showEmptyGrid: true,
            rowNum : 9999,
            multiselect: _this.opts.editable,
            datatype: 'local',
            colNames: ['商品id',  '库存类型', '商品中类', '商品编码', '商品名称(规格)', '单位',
                '库存下限' + _this.opts.tooltipText1, '安全库存' + _this.opts.tooltipText2, '库存上限' + _this.opts.tooltipText3],
            colModel: [
                {name: 'skuId', index: 'skuId', width: 100, align: 'left',hidden:true,sortable: !_this.opts.editable,key:true},
                {name: 'wmTypeName', index: 'wmTypeName', width: 100, align: 'left',sortable: !_this.opts.editable},
                {name: 'skuTypeName', index: 'skuTypeName', width: 100, align: 'left',sortable: !_this.opts.editable},
                {name: 'skuCode', index: 'skuCode', width: 100, align: "left",sortable: !_this.opts.editable},
                {name: 'skuName', index: 'skuName', width: 100, align: 'left',sortable: !_this.opts.editable,
                    formatter: function (cellvalue, options, rowObject) {
                        if (rowObject.isDisable == 1) {
                            return "<span>" + cellvalue + "<span style='color:red'>(已停用)</span></span>";
                        } else {
                            return cellvalue;
                        }
                    }},
                {name: 'uom', index: 'uom', width: 80, align: "left",sortable: !_this.opts.editable},
                {name: 'lowerInventory', index: 'lowerInventory', width: 100, align: "right",sorttype:'number',
                    formatter: _this.opts.editable ? formatInputNumber : qtyFormatter,unformat : unformatInput,
                    sortable: !_this.opts.editable},
                {name: 'safetyInventory', index: 'safetyInventory', width: 100, align: "right",sorttype:'number',
                    formatter: _this.opts.editable ? formatInputNumber : qtyFormatter,unformat : unformatInput,
                    sortable: !_this.opts.editable},
                {name: 'upperInventory', index: 'upperInventory', width: 100, align: "right",sorttype:'number',
                    formatter: _this.opts.editable ? formatInputNumber : qtyFormatter,unformat : unformatInput,
                    sortable: !_this.opts.editable}
            ]
        });

        $.filterGrid.init('#filterGridDiv');
        $.filterGrid.initSelect();
        inventoryWarning.opts.singleShowed=true;
    },

    //多仓库设定
    buildTable_2 : function () {

        var $skuDetailsTab = $('#skuDetailsTab2');

        //每次仅获取前7列，即是商品基本信息所在的列
        var colNames = inventoryWarning.opts.skuDetailsGridOpts.colNames.slice(0, 7);
        var colModel = inventoryWarning.opts.skuDetailsGridOpts.colModel.slice(0, 7);

        var finalColNames = [];
        var finalColModels = [];
        var finalData = [];
        var skuIds = [];

        var warehouseIds=inventoryWarning.opts.warehouseIds;

        $.ajax({
            url: ctxPath + inventoryWarning.opts.urlRoot + inventoryWarning.opts.queryMultiUrl,
            type: 'post',
            data: {warehouseIds : warehouseIds.join(',')},
            dataType: 'json',
            async: false,
            success: function(data){

                var warehouses = inventoryWarning.opts.warehouses;
                var warehouseNames = [];
                var warningNames = [];
                var warehouseModels = [];
                var warehouseIds = [];
                var lowerInventoryIds = [];

                var warehouseNo = 0;

                $.each(warehouses, function(i, d){
                    var lowerInventoryId = inventoryWarning.opts.lowerInventory + d.id;
                    var lowerInventoryColModel = {
                        name: lowerInventoryId,
                        index: lowerInventoryId,
                        align: 'right',
                        width: 100,
                        sorttype:'number',
                        sortable: !inventoryWarning.opts.editable
                    };

                    var safetyInventoryId = inventoryWarning.opts.safetyInventory + d.id;
                    var safetyInventoryColModel = {
                        name: safetyInventoryId,
                        index: safetyInventoryId,
                        align: 'right',
                        width: 100,
                        sorttype:'number',
                        sortable: !inventoryWarning.opts.editable
                    };

                    var upperInventoryId = inventoryWarning.opts.upperInventory + d.id;
                    var upperInventoryColModel = {
                        name: upperInventoryId,
                        index: upperInventoryId,
                        align: 'right',
                        width: 100,
                        sorttype:'number',
                        sortable: !inventoryWarning.opts.editable
                    };

                    var editColModel = {
                        editable: true,
                        formatter: formatInputNumber,
                        unformat: unformatInput
                    };
                    if (inventoryWarning.opts.commandType == 2) {
                        lowerInventoryColModel = $.extend(true, lowerInventoryColModel, editColModel || {});
                        safetyInventoryColModel = $.extend(true, safetyInventoryColModel, editColModel || {});
                        upperInventoryColModel = $.extend(true, upperInventoryColModel, editColModel || {});
                    }

                    warehouseNames[warehouseNo] = d.warehouseName;
                    warningNames[warehouseNo*3]='库存下限';
                    warningNames[warehouseNo*3+1]='安全库存';
                    warningNames[warehouseNo*3+2]='库存上限';
                    warehouseModels.push(lowerInventoryColModel);
                    warehouseModels.push(safetyInventoryColModel);
                    warehouseModels.push(upperInventoryColModel);
                    warehouseNo += 1;
                    warehouseIds.push(lowerInventoryId);
                    lowerInventoryIds.push(lowerInventoryId);
                    warehouseIds.push(safetyInventoryId);
                    warehouseIds.push(upperInventoryId);
                });

                $.each(data, function(i, d){
                    var index = warehouseNames.indexOf(d.warehouseName);
                    var lowerInventoryId = inventoryWarning.opts.lowerInventory + d.warehouseId;
                    var safetyInventoryId = inventoryWarning.opts.safetyInventory + d.warehouseId;
                    var upperInventoryId = inventoryWarning.opts.upperInventory + d.warehouseId;
                    if(index >= 0){
                        var lowerEx = d[lowerInventoryId];
                        var safetyEx = d[safetyInventoryId];
                        var upperEx = d[upperInventoryId];
                        if(lowerEx == undefined || lowerEx == null || lowerEx.length == 0){
                            lowerEx = '';
                        }
                        if(safetyEx == undefined || safetyEx == null || safetyEx.length == 0){
                            safetyEx = '';
                        }
                        if(upperEx == undefined || upperEx == null || upperEx.length == 0){
                            upperEx = '';
                        }
                        d[lowerInventoryId] = lowerEx + d.lowerInventory;
                        d[safetyInventoryId] = safetyEx + d.safetyInventory;
                        d[upperInventoryId] = upperEx + d.upperInventory;

                        var skuId=d.skuId;
                        if(skuIds.indexOf(d.skuId) < 0){
                            finalData.push(d);
                            skuIds.push(d.skuId);
                        }else{
                            for(var j=0;j<finalData.length;j++){
                                if(finalData[j].skuId==skuId){
                                    finalData[j][lowerInventoryId] = lowerEx + d.lowerInventory;
                                    finalData[j][safetyInventoryId] = safetyEx + d.safetyInventory;
                                    finalData[j][upperInventoryId] = upperEx + d.upperInventory;
                                    break;
                                }
                            }
                        }
                    }
                });

                //商品基本信息所在的colName
                Array.prototype.push.apply(finalColNames, colNames);
                //仓库所在的colName
                Array.prototype.push.apply(finalColNames, warningNames);
                //商品基本信息的colModel
                Array.prototype.push.apply(finalColModels, colModel);
                //仓库所在的colModel
                Array.prototype.push.apply(finalColModels, warehouseModels);

                inventoryWarning.opts.skuDetailsGridOpts.colNames = finalColNames;
                inventoryWarning.opts.skuDetailsGridOpts.colModel = finalColModels;

                var html = '<table id="grid_2" ></table>';
                $skuDetailsTab.empty().html(html);

                //计算grid宽度是否超过页面宽度
                var gridWidth = $('.panel-body').eq(1).width() - 18;
                var width = 580 + warehouseNames.length * 300;
                if(width < gridWidth){
                    inventoryWarning.opts.skuDetailsGridOpts.shrinkToFit = true;
                }

                $('#grid_2').jqGrid(inventoryWarning.opts.skuDetailsGridOpts);
                if(warehouseNames.length>0) {
                    var groupHeaders=[];
                    var obj = {};
                    for (var i = 0; i < warehouseNames.length; i++) {
                        var header={startColumnName: lowerInventoryIds[i], numberOfColumns: 3, titleText: warehouseNames[i]};
                        groupHeaders.push(header);
                        var num=i+6;
                        obj["F"+num]=1;
                    }
                    inventoryWarning.opts.excelNum=obj;
                    jQuery("#grid_2").jqGrid('setGroupHeaders', {
                        useColSpanStyle: !inventoryWarning.opts.editable, //修复BUG 20841 【库存预警设定】多仓库表头名称错误
                        groupHeaders: groupHeaders
                    });

                    //设定冻结列 修复BUG 20841 【库存预警设定】多仓库表头名称错误
                    $('#grid_2').jqGrid('setFrozenColumns');
                    if(inventoryWarning.opts.editable) {
                        $('.frozen-div').find('tr.jqg-second-row-header').hide();
                        $('.frozen-div').find('tr.jqg-third-row-header').css('height', '86px');
                    }
                }

                for(var i = 0; i < finalData.length; i++){
                    var rowData = finalData[i];
                    if(warehouseIds.length > 0){
                        for(var j = 0; j < warehouseIds.length; j++){
                            if(rowData[warehouseIds[j]]!=null&&rowData[warehouseIds[j]]!=undefined&&rowData[warehouseIds[j]]!='null'){
                                //do nothing
                            } else{
                                if (inventoryWarning.opts.commandType == 2) {
                                    rowData[warehouseIds[j]] = '';
                                }else {
                                    rowData[warehouseIds[j]] = '-'; // 若某仓库对一个商品没有设置预警数量，则赋值为'-'
                                }
                            }
                        }

                    }
                }
                $('#grid_2').jqGrid("setGridParam", {data:finalData});
                $('#grid_2').trigger("reloadGrid");

                inventoryWarning.opts.multiShowed=true;


                $.filterGrid.init('#filterGridDiv1');
                $.filterGrid.initSelect();

                var exportConditions2 = [];
                var commercialName=$("#commercialName").val();
                exportConditions2.push({key : "商户", value : commercialName});
                exportConditions2.push({key : "设定仓库", value : warehouseNames.join(',')});
                exportConditions2.push({key : "导出人", type : 'user'});
                exportConditions2.push({key : "导出时间", type : 'date'});

                inventoryWarning.opts.exportConditions2 = exportConditions2;

            }
        });
    }
};

/**
 * 编辑页面跳转
 * @param args 参数
 * url：跳转地址
 * postData：传递的参数
 * @returns {boolean}
 */
$.gotoEdit = function (type) {
    var url = basicPath + '&act=basic_inventoryWarning_edit';
    $.doForward({url:url,postData:{warehouseId:inventoryWarning.opts.warehouseId,type:type}});
};

$.doSaveMulti = function () {
    //修复 BUG 20766 【库存预警设定】当输入不正确时，输入框变红
    setTimeout(saveMulti,100);
};

//保存多仓库
function saveMulti() {
    var opts = inventoryWarning.opts;
    var warehouseIds = $('#warehouseIds').val();
    var gridData = $('#grid_2').jqGrid('getRowData');
    //验证
    if($('#grid_2').find('input.err').length > 0){
        $.layerMsg(inventoryWarning.opts.errorMsg,false);
        $('#grid_2').find('input.err').eq(0).focus();
        return;
    }

    //组装数据
    warehouseIds = warehouseIds.split(',');
    var saveData = {
        warehouseIds: warehouseIds,
        details: []
    };

    for(var i in warehouseIds){
        var id = warehouseIds[i];

        for(var j in gridData){
            var rowData = gridData[j];
            //库存对象
            var skuObj = {
                skuId: "",
                skuCode: "",
                skuName: "",
                skuTypeName: "",
                uom: "",
                wmTypeName: "",
                warehouseId: '',
                lowerInventory: '',
                safetyInventory: '',
                upperInventory: ''
            };

            skuObj.skuId = rowData.skuId;
            skuObj.skuCode = rowData.skuCode;
            skuObj.skuName = rowData.skuName;
            skuObj.skuTypeName = rowData.skuTypeName;
            skuObj.uom = rowData.uom;
            skuObj.wmTypeName = rowData.wmTypeName;
            skuObj.warehouseId = id;
            skuObj.lowerInventory = rowData[opts.lowerInventory + id];
            skuObj.safetyInventory = rowData[opts.safetyInventory + id];
            skuObj.upperInventory = rowData[opts.upperInventory + id];

            saveData.details.push(skuObj);
        }

    }

    if (gridData.length == 0) {
        layer.confirm('列表中没有商品，确定要保存？', {icon: 3, offset: '30%'}, function (index) {

            $.ajax({
                type: "POST",
                url: ctxPath + inventoryWarning.opts.urlRoot + inventoryWarning.opts.saveMultiUrl,
                data: JSON.stringify(saveData),
                dataType: "json",
                contentType: "application/json;charset=UTF-8",
                async: false,
                success: function (data) {
                    if(data.success){
                        $.layerMsg("保存成功", true);
                        inventoryWarning.opts.singleShowed=false;
                    }else{
                        $.layerMsg(data.message, false);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $.layerMsg("保存失败", false);
                }
            });
            layer.close(index);
        });
    }else{


        $.ajax({
            type: "POST",
            url: ctxPath + inventoryWarning.opts.urlRoot + inventoryWarning.opts.saveMultiUrl,
            data: JSON.stringify(saveData),
            dataType: "json",
            contentType: "application/json;charset=UTF-8",
            async: false,
            success: function (data) {
                if(data.success){
                    $.layerMsg("保存成功", true);
                    inventoryWarning.opts.singleShowed=false;
                }else{
                    $.layerMsg(data.message, false);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $.layerMsg("保存失败", false);
            }
        });
    }
};
//输入验证
$('#skuDetailsTab').on('blur','input:text', function () {
    var err = false;
    var $tr = $(this).parents('tr');
    var $lowerLimit = $tr.find('[name="lowerInventory"]');
    var $safeLimit = $tr.find('[name="safetyInventory"]');
    var $upperLimit = $tr.find('[name="upperInventory"]');
    var lowerLimit = parseFloat($lowerLimit.val());
    var safeLimit = parseFloat($safeLimit.val());
    var upperLimit = parseFloat($upperLimit.val());

    //修复 BUG 20766 【库存预警设定】当输入不正确时，输入框变红
    setTimeout(function () {
        if(safeLimit <= lowerLimit && $safeLimit.val() != ''){
            $tr.find('[name="safetyInventory"]').addClass('err');
        }else{
            $tr.find('[name="safetyInventory"]').removeClass('err');
        }

        if((upperLimit <= safeLimit || upperLimit <= lowerLimit) && $upperLimit.val() != ''){
            $tr.find('[name="upperInventory"]').addClass('err');
        }else{
            $tr.find('[name="upperInventory"]').removeClass('err');
        }
    },50);
});

//批量设定模态框事件绑定
function bindBatchSet(){
    //自定义设置、根据出库总数计算的切换
    $('#choseTypeHead>label').on('click', function () {
        var _this = $(this);
        $('#choseTypeHead').siblings('div').hide();
        var divId = '#' + _this.data('id');
        $(divId).show();
    });
    //下限、安全、上限输入框输入验证
    $('.modal-row input').on('keyup', function () {
        var type = $('.modal :radio:checked').val();
        if(type == '1'){
            var err = false;
            var $lowerLimit = $('#lowerLimit');
            var $safeLimit = $('#safeLimit');
            var $upperLimit = $('#upperLimit');
            var lowerLimit = parseFloat($lowerLimit.val());
            var safeLimit = parseFloat($safeLimit.val());
            var upperLimit = parseFloat($upperLimit.val());

            if(safeLimit <= lowerLimit && $safeLimit.val() != ''){
                $safeLimit.addClass('err');
            }else{
                $safeLimit.removeClass('err');
            }

            if((upperLimit <= safeLimit || upperLimit <= lowerLimit) && $upperLimit.val() != ''){
                $upperLimit.addClass('err');
            }else{
                $upperLimit.removeClass('err');
            }

            if($('#hand input.err').length > 0){
                $('#handMsg').show();
            }else{
                $('#handMsg').hide();
            }
        }else{
            var $lowerLimitPercent = $('#lowerLimitPercent');
            var $upperLimitPercent = $('#upperLimitPercent');
            var lowerLimitPercent = parseFloat($lowerLimitPercent.val());
            var upperLimitPercent = parseFloat($upperLimitPercent.val());

            if(upperLimitPercent <= lowerLimitPercent && $upperLimitPercent.val() != ''){
                $upperLimitPercent.addClass('err');
            }else{
                $upperLimitPercent.removeClass('err');
            }

            if($('#auto input.err').length > 0){
                $('#autoMsg').show();
            }else{
                $('#autoMsg').hide();
            }
        }
    });
    //确认按钮
    $('#batchSetOk').on('click', function () {
        var type = $('.modal :radio:checked').val();
        var ids = $('#grid').jqGrid("getGridParam", "selarrrow");
        var warehouseId = $('#warehouseId').val();
        var lowerLimit = '';
        var safeLimit = '';
        var upperLimit = '';

        if(type == '1'){
            if($('#hand input.err').length > 0){
                $('#hand input.err')[0].focus();
                return;
            }

            var $lowerLimit = $('#lowerLimit');
            var $safeLimit = $('#safeLimit');
            var $upperLimit = $('#upperLimit');
            lowerLimit = $lowerLimit.val();
            safeLimit = $safeLimit.val();
            upperLimit = $upperLimit.val();

            //设置
            for(var i in ids){
                $('#grid').jqGrid('setRowData',ids[i],{
                    lowerInventory: lowerLimit,
                    safetyInventory: safeLimit,
                    upperInventory: upperLimit
                });
            }
        }else{
            if($('#auto input.err').length > 0){
                $('#auto input.err')[0].focus();
                return;
            }

            //日期验证
            var arriveDateStart =  $('#arriveDateStart').val();
            var arriveDateEnd =  $('#arriveDateEnd').val();
            if(arriveDateStart == '' || arriveDateEnd == ''){
                $.layerMsg('开始日期和结束日期不能为空！',false);
                return;
            }
            arriveDateStart = new Date(arriveDateStart);
            arriveDateEnd = new Date(arriveDateEnd);

            var diffDay = (arriveDateEnd - arriveDateStart) / 24 / 60 / 60 /1000;
            if(diffDay > 90){
                $.layerMsg('开始日期和结束日期间隔不超过90天！',false);
                return;
            }

            //下限上限比例验证
            var upperLimitPercent = $('#upperLimitPercent').val();
            if(parseFloat(upperLimitPercent) <= 100){
                $.layerMsg('库存上限比例不能小于100%！',false);
                $('#upperLimitPercent').focus();
                return;
            }

            $.ajax({
                url: ctxPath + inventoryWarning.opts.urlRoot + inventoryWarning.opts.batchSetUrl,
                contentType: "application/json;charset=UTF-8",
                type: 'post',
                data: JSON.stringify({
                    warehouseId : warehouseId,
                    dateStart : arriveDateStart,
                    dateEnd : arriveDateEnd,
                    skuIds : ids,
                    lowerRate : parseFloat($('#lowerLimitPercent').val()),
                    upperRate : parseFloat($('#upperLimitPercent').val())
                }),
                dataType: 'json',
                async: false,
                success: function(data){
                    //设置
                    var setIds = [];
                    var setSkuNames = [];
                    for(var i in data){
                        var rowData = data[i];
                        $('#grid').jqGrid('setRowData',rowData.skuId,{
                            lowerInventory: rowData.lowerInventory,
                            safetyInventory: rowData.safetyInventory,
                            upperInventory: rowData.upperInventory
                        });
                        setIds.push(rowData.skuId);
                    }

                    for(var i in ids){
                        var id = ids[i];
                        if(setIds.indexOf(parseInt(id)) < 0){
                            var sku = $('#grid').jqGrid('getRowData',id);
                            setSkuNames.push(sku.skuName);
                        }
                    }

                    if(setSkuNames.length > 0){
                        $.layerMsg(setSkuNames.join(',') + '总出库数为0，请手动设置。',false);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $.layerMsg("批量设置失败", false);
                }
            });
        }

        //确认后操作
        $('#batchSetModal').modal('hide');
    });
    //模态框显示
    $.batchSet = function(){
        var ids = $('#grid').jqGrid("getGridParam", "selarrrow");
        if (ids == undefined || ids.length == 0) {
            $.layerMsg('请先选择商品！', false);
            return false;
        }
        var warehouseId = $('#warehouseId').val();
        if (warehouseId == undefined || warehouseId == '') {
            $.layerMsg('请先选择仓库！', false);
            return false;
        }

        //显示模态框
        $('#batchSetModal').modal({
            backdrop: 'static'

        });
        //初始化
        $('#batchSetModal').on('shown.bs.modal', function () {
            $('input[name="lowerLimit"]').focus();
        });
        $('#batchSetModal').on('hidden.bs.modal', function () {
            //初始化
            $('#batchSetModal :text:not(":disabled")').val('');
            $('#batchSetModal :text').removeClass('err');
            $('#handMsg').hide();
            $('#autoMsg').hide();

            $('#arriveDateStart').val($('#defaultDateStart').val());
            $('#arriveDateEnd').val($('#defaultDateEnd').val());

            $('#choseTypeHead :radio').eq(0).click();
        });
    };
}
//输入验证
$('#skuDetailsTab2').on('blur','input:text', function () {
    var $this = $(this);
    var err = false;
    var $tr = $this.parents('tr');
    var name = $this.attr('name');
    var houseId = name.substr(name.indexOf('tory')+4);
    var $lowerLimit = $tr.find('[name="lowerInventory' + houseId + '"]');
    var $safeLimit = $tr.find('[name="safetyInventory' + houseId + '"]');
    var $upperLimit = $tr.find('[name="upperInventory' + houseId + '"]');
    var lowerLimit = parseFloat($lowerLimit.val());
    var safeLimit = parseFloat($safeLimit.val());
    var upperLimit = parseFloat($upperLimit.val());

    //修复 BUG 20766 【库存预警设定】当输入不正确时，输入框变红
    setTimeout(function () {
        if(safeLimit <= lowerLimit && $safeLimit.val() != ''){
            $tr.find('[name="safetyInventory' + houseId + '"]').addClass('err');
        }else{
            $tr.find('[name="safetyInventory' + houseId + '"]').removeClass('err');
        }

        if ((upperLimit <= safeLimit || upperLimit <= lowerLimit) && $upperLimit.val() != '') {
            $tr.find('[name="upperInventory' + houseId + '"]').addClass('err');
        } else {
            $tr.find('[name="upperInventory' + houseId + '"]').removeClass('err');
        }
    },50);
});

//数量格式化
function qtyFormatter(cellvalue, options, rowObject) {
    if (!!cellvalue || cellvalue == 0) {
        return cellvalue;
    }

    return '-';
};

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
    str += 'data-format=\'float\'';
    str += 'id=\'' + options.rowId + '_' + options.colModel.name + '\' ';
    str += 'name=\'' + colName + '\' ';
    if(cellvalue=='null'){
        cellvalue='';
    }
    if (cellvalue != undefined) {
        str += 'value=\'' + cellvalue + '\' ';
    }
    //设置gridId和rowId
    str += 'row-id=\'' + options.rowId + '\' grid-id=\'' + options.gid + '\'';
    str += '>';
    return str;
}
//绑定多选下拉框事件 start
function delegateMultiSelect(){
    var _this = this;

    // 交互
    $(".multi-select > .select-control").on("click",function(){
        var showList = $(this).next(".multi-select-con");
        if(showList.is(":hidden")){
            $(".multi-select-con").hide();
            showList.show();
        }else{
            showList.hide();
        }
    });


    //任意点击隐藏下拉层
    $(document).bind("click",function(e){
        var target = $(e.target);
        //当target不在popover/coupons-set 内是 隐藏
        if(target.closest(".multi-select-con").length == 0 && target.closest(".select-control").length == 0){
            $(".multi-select-con").hide();
        }
    });


    _this.delegateCheckbox('skuTypes', '#sku-type-all');
    _this.delegateCheckbox('wmTypes', '#wm-type-all');
}
/**
 * 监听下拉选框的checkbox
 * @param name
 */
function delegateCheckbox(name, id){

    var _this = this;

    $(document).delegate(":checkbox[name='"+ name + "']","change",function(){
        _this.associatedCheckAll(this, $(id));
        _this.filterConditions(name,
            $(this).parents(".multi-select-con").prev(".select-control").find("em"),
            $(this).parents(".multi-select-con").next(":hidden"));
    });

    $(document).delegate(id,"change",function(){
        _this.checkAll(this,name);
        _this.filterConditions(name,
            $(this).parents(".multi-select-con").prev(".select-control").find("em"),
            $(this).parents(".multi-select-con").next(":hidden"));
    });
}
/**
 *    associatedCheckAll     //关联全选
 *    @param  object         e           需要操作对象
 *    @param  jqueryObj      $obj        全选对象
 **/
function associatedCheckAll(e, $obj){
    var _this = this;
    var flag = true;
    var $name = $(e).attr("name");
    _this.checkboxChange(e,'checkbox-check');
    $("[name='"+ $name +"']:checkbox").not(":disabled").each(function(){
        if(!this.checked){
            flag = false;
        }
    });
    $obj.get(0).checked = flag;
    _this.checkboxChange($obj.get(0),'checkbox-check');
}

/**
 *    checkbox               //模拟checkbox功能
 *    @param  object         element     需要操作对象
 *    @param  className      class       切换的样式
 **/
function checkboxChang(element,className){
    if(element.readOnly){return false;}
    if(element.checked){
        $(element).parent().addClass(className);
    }else{
        $(element).parent().removeClass(className);
    }
}
/**
 * 条件选择
 * @param checkboxName      string                  checkbox name
 * @param $textObj          jquery object           要改变字符串的元素
 * @param $hiddenObj        jquery object           要改变的隐藏域
 */
function filterConditions(checkboxName, $textObj, $hiddenObj){
    var checkboxs = $(":checkbox[name='" + checkboxName + "']");
    var checkboxsChecked = $(":checkbox[name='" + checkboxName + "']:checked");
    var len = checkboxs.length;
    var lenChecked = checkboxsChecked.length;
    var str = '';
    var value1 = '';

    for(var i=0;i<lenChecked;i++){
        if(i==0){
            str += checkboxsChecked.eq(i).attr("data-text");
            value1 += checkboxsChecked.eq(i).attr("value");
        }else{
            str += ',' + checkboxsChecked.eq(i).attr("data-text");
            value1 += "," + checkboxsChecked.eq(i).attr("value");
        }
    }
    $textObj.text(str);
    $hiddenObj.val(value1);

    if(lenChecked == len){
        $textObj.text("全部");
    }

}


/**
 *    checked all            //全选
 *    @param  object         e           需要操作对象
 *    @param  nameGroup      string      checkbox name
 **/
function checkAll(e,nameGroup){

    var _this = this;

    if(e.checked){
        //alert($("[name='"+ nameGroup+"']:checkbox"));
        $("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
            this.checked = true;
            _this.checkboxChange(this,'checkbox-check');
        });
    }else{
        $("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
            this.checked = false;
            _this.checkboxChange(this,'checkbox-check');
        });
    }
    _this.checkboxChange(e,'checkbox-check');
}

/**
 *    checkbox               //模拟checkbox功能
 *    @param  object         element     需要操作对象
 *    @param  className      class       切换的样式
 **/
function checkboxChange(element,className){
    if(element.readOnly){return false;}
    if(element.checked){
        $(element).parent().addClass(className);
    }else{
        $(element).parent().removeClass(className);
    }
}
//绑定多选下拉框事件 end

/**
 * 返回单元格input的值
 * @param cellvalue
 * @param options
 * @param cell
 * @returns {*}
 */
function unformatInput(cellvalue, options, cell) {
    var value = $(cell).children('input')[0].value;
    return value;
}

function restWarehouse(lastChoice){
    var target = $("#warehouseId"),sArray = target.find("option"),sLi = target.parent().find("ul li");
    var a=0;
    if(sArray.length<sLi.length){
        a=sLi.length-sArray.length;
    }
    for(var i = 0;i<sArray.length;i++){
        if($(sArray[i]).val()==lastChoice){
            //由于添加了过滤框i需要+1
            $(sLi[i+a]).click();
            return false;
        }
    }
}

//保存回调
$.saveCallback = function (args) {
    var rs = args.result;
    if (rs.success) {
        $.layerMsg("保存成功", true);
        inventoryWarning.opts.multiShowed = false;
    }else{
        $.layerMsg("操作失败:" + rs.message, false);
    }
}

$.doSaveSingle = function (args) {
    //修复 BUG 20766 【库存预警设定】当输入不正确时，输入框变红
    setTimeout(saveSingle,100);
};
function saveSingle(args){
    var warehouseId=$('#warehouseId').val();
    if(warehouseId==''){
        $.layerMsg("请先选择仓库", false);
        return;
    }
    var gridData = $('#grid').jqGrid('getRowData');
    var url=ctxPath + inventoryWarning.opts.urlRoot + inventoryWarning.opts.saveSingleUrl;
    if (gridData.length == 0) {
        layer.confirm('列表中没有商品，确定要保存？', {icon: 3, offset: '30%'}, function (index) {
            $.doSave({formId:'baseInfoForm',gridId:'grid',url:url,
                submitCallback:'$.saveCallback'});
            layer.close(index);
        });
    }else{
        //验证
        if($('#grid').find('input.err').length > 0){
            $.layerMsg(inventoryWarning.opts.errorMsg,false);
            $('#grid').find('input.err').eq(0).focus();
            return;
        }

        $.doSave({formId:'baseInfoForm',gridId:'grid',url:url,
            submitCallback:'$.saveCallback'});
    }
}