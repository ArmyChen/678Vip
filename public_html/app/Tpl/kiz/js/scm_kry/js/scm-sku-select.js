//gridId                 :商品列表JqGrid table的id
//skuIdColName = "skuId" :商品列表JqGrid中商品ID的colName
//skuScene               :商品选择：属性、单位、价格取值场景
//excludedSkuId = -1L    :场景：BOM设定，商品选择需排除自己
//warehouseId            :仓库所在select的ID，用于显示实时库存
//sku = "商品"           :“商品”的默认显示，如：商品选择，可覆盖后显示为，原料选择
//toCommercialId         :开启调拨单过滤条件（不传调入门店Id则不开启）
//ccTask                 :TODO
//singleSelect           :1:开启单选模式，0:默认多选

var scmSkuSelect = {

    opts: {

        gridTableId: '#skuSelectGrid',
        skuSelectModalId: '#skuSelectModal',
        skuTypesInitialized: false,
        outterGridId: '',
        gridObj: new Object(),
        hideInventoryQty: true,
        warehouseSelectId: '',
        supplierSelectId: '',
        commercialSelectId: '',
        toCommercialId:'',//对调拨单过滤
        skuScene: '',
        sku: '商品',
        dataGridCal: new Object(),
        searchCondition: '',
        searchFormId : 'skuselectConditions',
        addBtnId: 'addsku',
        searchBtnId: 'btnSearch',
        isSortCol: false,
        addRowWithRealod: false
    },

    _init: function (args) {

        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        _this.opts.outterGridId = $('#outterGridId').val();

        if(_this.opts.outterGridId && _this.opts.outterGridId.length > 0){

            _this.opts.gridObj = $('#' + _this.opts.outterGridId);

            _this.opts.warehouseSelectId = $('input[name=warehouseSelectId]').val();
            _this.opts.supplierSelectId = $('input[name=supplierSelectId]').val();
            _this.opts.commercialSelectId = $('input[name=commercialSelectId]').val();
            _this.opts.skuScene = $('input[name=skuScene]').val();

            _this.opts.toCommercialId = $('input[name=toCommercialId]').val();
            _this.opts.qtyColName = $('#qtyColName').val();

            _this.resetCss();

            _this.delegateAddBtn();

            _this.delegateBtnRemove();

            _this.delegateWarehouseChange();

            _this.delegateMultiSelect();

            _this.addSkuByBarcode();
        }

    },

    /**
     * 调整弹出框样式
     */
    resetCss: function(){

        var _this = this;

        $('#skuSelectModal .form-item').width(280);
        $('#skuSelectModal .form-item :last').css({"width":"120px", "margin-left":"60px"});
        $('#skuSelectModal .form-item .multi-select-con').css({"width":"152px", "top":"33px"});
        $('#skuSelectModal .form-item .multi-select-con ul').width(140);
        $('#skuSelectModal .form-item .select-control').width(150);
        //$('#skuSelectModal .form-item .select-control ul').width(150);
        $('#skuSelectModal .form-item .select-group ul').width(150);
        $('#skuSelectModal .form-item input[name=skuCodeOrName]').width(150);
        $('#skuSelectModal .form-item .close').hide();
        //$('#skuSelectModal .form-item .control-label').css("text-align", "left");
        $('#skuSelectModal .form-item .control-label-con').css("margin-left", "80");
        $('#skuSelectModal .form-item .control-label-con :last').css("margin-left", "110");
        $('#skuSelectModal .form-container').css("margin-left","-50px");
        $('#skuSelectModal .control-label-con').css("margin-left","120px");
        $('#skuSelectModal .modal-footer').css("padding","10px 20px 10px");

        if(_this.opts.skuScene != 11){
            $('#skuSelectModal .form-item :first').hide();
        }
    },


    /**
     * 监听商品添加按钮
     */
    delegateAddBtn: function(){

        var _this = this;

        //添加商品
        $(document).delegate('#btnSelectSku', 'click', function () {
            if (!_this.checkBeforeAddSku($(this))) {
                return false;
            }

            //多个添加删除按钮时
            if($(this).data('gridid')){
                _this.opts.outterGridId = $(this).data('gridid');
            }
            //是否采用表格重新加载组装数据的方式
            if($(this).data('addrowwithrealod')){
                _this.opts.addRowWithRealod = true;
            }else{
                _this.opts.addRowWithRealod = false;

            }

            $(_this.opts.skuSelectModalId).modal({
                backdrop: 'static'
            });
            $('.control-label-con.search-box').css({width:'175px'});
            $('.close').show();

            _this.clearConditions(); // 清空查询条件
            $(_this.opts.gridTableId).jqGrid('setGridParam', {datatype: 'local'}).trigger('reloadGrid');
            _this.getSkuInfo();
        });
    },

    /**
     * 添加商品前检查仓库、供应商、调入门店
     */
    checkBeforeAddSku: function ($btnSelectSku) {
        var _this = this;

        if(_this.opts.warehouseSelectId && _this.opts.warehouseSelectId.length > 0 && _this.opts.warehouseSelectId != 'null'){

            _this.opts.hideInventoryQty = false;

            var warehouseId = $('#' + _this.opts.warehouseSelectId).val();

            //存在多个添加商品按钮时，根据添加按钮的属性data-nowarehouse判断仓库是否必须
            if($btnSelectSku.data('nowarehouse') != '1'){
                if(!(warehouseId && warehouseId.length > 0)){
                    $.layerMsg('请先选择仓库！', false);
                    return false;
                }else{
                    //如果开启调拨过滤则做相应检查
                    if(_this.opts.toCommercialId&&_this.opts.toCommercialId.length>0&&_this.opts.toCommercialId!='null'){
                        var currentToCommercialId = $("#"+_this.opts.toCommercialId).val();
                        if(!(currentToCommercialId && currentToCommercialId.length > 0)){
                            $.layerMsg('请选择调入门店！', false);
                            return false;
                        }
                    }

                }
            }

        }

        if(_this.opts.supplierSelectId && _this.opts.supplierSelectId.length > 0 && _this.opts.supplierSelectId != 'null'){

            _this.opts.hideInventoryQty = false;

            var supplierId = $('#' + _this.opts.supplierSelectId).val();

            if(!(supplierId && supplierId.length > 0)){
                $.layerMsg('请先选择供应商！', false);
                return false;
            }
        }

        //销售订单特殊处理
        if(scmSkuSelect.opts.skuScene == 91){

            var commercialId = $('#' + _this.opts.commercialSelectId).val();
            if(!(commercialId && commercialId.length > 0)){
                $.layerMsg('请先选择销售商户！', false);
                return false;
            }
        }

        return true;
    },

    /**
     * 监听商品移除按钮
     */
    delegateBtnRemove: function(){

        var _this = this;

        //删除商品 ,并重新计算合计 TODO
        $(document).delegate('#btnDeleteSku', 'click', function () {
            //多个添加删除按钮时
            if($(this).data('gridid')){
                _this.opts.outterGridId = $(this).data('gridid');
            }

            var _btn_this = this;
            _this.opts.gridObj = $('#' + _this.opts.outterGridId); //取得最新节点

            var ids = _this.opts.gridObj.jqGrid("getGridParam", "selarrrow");
            if (ids == undefined || ids.length == 0) {
                $.layerMsg('请选择' + _this.opts.sku + '！', false);
                return false;
            }

            var len = ids.length;

            layer.confirm("是否移除" + _this.opts.sku + "？", {icon: 3, title:'提示', offset: '30%'}, function(index){
                for(var i = 0; i < len; i++){
                    _this.opts.gridObj.jqGrid('delRowData', ids[0]);
                }
                $('#cb_' + _this.opts.outterGridId).attr('checked', false); // 重置checkbox为未选中

                layer.close(index);


                //回调
                var afterRemoved = $(_btn_this).attr('afterRemoved');
                if(afterRemoved){
                    if (afterRemoved && $.isFunction(afterRemoved.toFunction())) {
                        afterRemoved.toDo();
                    }
                }

                //移入、移除重新载入分类
                var reloadType = $(_btn_this).attr('reloadType');
                if(reloadType){
                    if (reloadType && $.isFunction(reloadType.toFunction())) {
                        reloadType.toDo();
                    }
                }
            });

        });
    },

    /**
     * 监听仓库切换事件，仓库切换后，应刷新库存
     */
    delegateWarehouseChange: function(){

        var _this = this;

        //仓库切换时，刷新库存
        if(_this.opts.warehouseSelectId && _this.opts.warehouseSelectId.length > 0 && _this.opts.warehouseSelectId != 'null'){
            var opts = {
                gridId: _this.opts.outterGridId,
                warehouseSelectId: _this.opts.warehouseSelectId,
                skuIdColName: $('#skuIdColName').val() // default: skuId，在ScmSkuSelectTag.java中定义
            };
            refreshInventoryQty._init(opts);
        }
    },


    /**
     * 监听“库存类型”的下拉框多选事件
     */
    delegateMultiSelect: function(){

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

        _this.delegateCheckbox('wmType');

        //任意点击隐藏下拉层
        $(document).bind("click",function(e){
            var target = $(e.target);
            //当target不在popover/coupons-set 内是 隐藏
            if(target.closest(".multi-select-con").length == 0 && target.closest(".select-control").length == 0){
                $(".multi-select-con").hide();
            }
        });
    },

    /**
     * 监听下拉选框
     * @param name
     */
    delegateCheckbox: function(name){

        var _this = this;

        //业务类型 条件选择
        $(document).delegate(":checkbox[name='"+ name + "']","change",function(){
            _this.filterConditions(name,
                $(this).parents(".multi-select-con").prev(".select-control").find("em"),
                $(this).parents(".multi-select-con").next(":hidden"));
        });
    },



    /**
     * 条件选择
     * @param checkboxName      string                  checkbox name
     * @param $textObj          jquery object           要改变字符串的元素
     * @param $hiddenObj        jquery object           要改变的隐藏域
     */
    filterConditions: function(checkboxName, $textObj, $hiddenObj){
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
        if(str == '') str = '请选择库存类型';
        $textObj.text(str);
        $hiddenObj.val(value1);

        if(lenChecked == len){
            //$textObj.text("全部");
        }

    },


    /**
     * 清除查询条件
     */
    clearConditions: function() {

        var _this = this;

        _this.initSkuTypes();
        $('#skuSelectModal').find('select').parent().find('ul li:first').click();
        $('#skuSelectModal').find('#keyWord').val('');
        
        $("#wmType .checkbox-check").each(function(){
        	$(this).click();
        });
    },

    /**
     * 初始化商品类型
     * @returns {boolean}
     */
    initSkuTypes: function() {

        var _this = this;

        if (_this.opts.skuTypesInitialized) {
            return false;
        }

        var $skuTypes = $('#modalSkuTypeId');
        //选择商品分类后guan光标定位到商品编码中
        $skuTypes.on('change', function (e) {
            $('#keyWord').focus();
        });

        $.ajax({
            url: ctxPath + "/common/getSkuTypes",
            data: {skuScene: _this.opts.skuScene},
            type: "post",
            async: false,
            dataType: "json",
            success: function (data) {
                var option = '';
                var li = '';
                jQuery.each(data, function (index, sku) {
                    option += '<option value="' + sku.skuTypeId + '">' + sku.skuTypeName + '</option>';
                    li += '<li>' + sku.skuTypeName + '</li>';
                });
                $skuTypes.append(option);
                $skuTypes.parent().find('ul').append(li);
                _this.opts.skuTypesInitialized = true;
            }
        });
    },



    /**
     * 查询商品
     */
    getSkuInfo: function() {

        var _this = this;

        var $gridObj = $(_this.opts.gridTableId);
        var width = $('#skuSelectModal .modal-dialog').width() - 3;

        var url = '/common/querySkuByScene';
        //BUG #13577 【盘点单】全部移除商品后，不能添加商品，不能保存，查询所有的商品数据
        if(scmSkuSelect.opts.skuScene == 61){
            url = '/common/querySkuForCc'
        }

        var priceName = '价格';
        if(_this.opts.skuScene == 11) priceName = '估算单价';

        $gridObj.dataGrid({
            formId: "skuselectConditions",
            serializeGridDataCallback: _this.serializeGridDataCallback,
            url: ctxPath + url,
            datatype: 'local',
            showEmptyGrid: true,
            rownumbers: true,
            multiselect: true,
            multiselectWidth: 49,
            height: 350,
            width: width,
            colNames: ['id', 'wmType','yieldRateStr','reckonPrice','库存类型','所属分类', _this.opts.sku + '编码',
                _this.opts.sku + '名称（规格）', '单位', '单位ID', priceName, '盘点初始库存','当前库存', '换算率', '标准单位换算率', '标准单位ID', '标准单位', '定价','库存类型'],
            colModel: [
                {name: 'id', index: 'id', hidden: true},
                {name: 'wmType', index: 'wmType', hidden: true},
                {name: 'yieldRateStr', index: 'yieldRateStr', hidden: true},
                {name: 'reckonPrice',index: 'reckonPrice',hidden:true,formatter: function (cellvalue, options, rowObject) {
                   return rowObject.price;
                }},
                {name: 'wmTypeStr', index: 'wmType', align: "center"},
                {name: 'skuTypeName', index: 'skuTypeName', align: "center"},
                {name: 'skuCode', index: 'skuCode', align: "center"},
                {name: 'skuName', index: 'skuName', align: "center"},
                {name: 'uom', index: 'uom', align: "center"},
                {name: 'uomIdForTable', index: 'uomIdForTable', align: "center",hidden: true},
                {name: 'price', index: 'price', align: "center"},
                {name: 'originalCcInventoryQty', index: 'originalCcInventoryQty', align: "center", hidden: true},
                {name: 'inventoryQty', index: 'inventoryQty', align: "center", hidden: true/*hideInventoryQty*/},
                {name: 'skuConvert', index: 'skuConvert', align: "center", hidden: true},
                {name: 'skuConvertOfStandard', index: 'skuConvertOfStandard', align: "center", hidden: true},
                {name: 'standardUnitId', index: 'standardUnitId', align: "center", hidden: true},
                {name: 'standardUnitName', index: 'standardUnitName', align: "center", hidden: true},
                {name: 'standardPrice', index: 'standardPrice', align: "center", hidden: true},
                {name: 'wmTypeName', index: 'wmTypeName', align: "center", hidden: true}
            ],
            sortname: 'skuCode',
            pager: "",
            rowNum: "0",
            beforeSelectRow: function (rowid, e) {
                if($('#singleSelect').val() == '1'){
                    $gridObj.jqGrid('resetSelection');
                }
                return true;
            },
            onSelectAll: function (aRowids, status) {
                if($('#singleSelect').val() == '1'){
                    $gridObj.jqGrid('resetSelection');
                }
            },
            onSortCol: function () {
                scmSkuSelect.opts.isSortCol = true;
            },
            beforeRequest: function(){
                if(scmSkuSelect.opts.isSortCol){
                    return;
                }
                showLoading('#btnSearch');
            },
            loadComplete: function(data){
                hideLoading('#btnSearch');
                if(data.dataList && data.dataList.length == 1){
                    var selectId = data.dataList[0].id;
                    $(scmSkuSelect.opts.gridTableId).jqGrid("setSelection", selectId);
                }
            },
            loadError: function(xhr, status, error){
                hideLoading('#btnSearch');
                $('#addCount').html(error);
            }
        });

        $gridObj.setGridWidth(width);
    },
    serializeGridDataCallback: function (formData) {

        if (!scmSkuSelect.opts.hideInventoryQty) {
            formData["warehouseId"] = $('#' + scmSkuSelect.opts.warehouseSelectId).val();
        }

        formData["supplierId"] = $('#' + scmSkuSelect.opts.supplierSelectId).val();

        formData["commercialId"] = $('#' + scmSkuSelect.opts.commercialSelectId).val();

        var isCcTask = $('#ccTask').val();
        if(isCcTask == '1'){
            formData["ccTaskId"] = $('#id').val();
        }

        //是否开启了调拨的过滤条件,开启后添加目标门店Id
        if(scmSkuSelect.opts.toCommercialId&&scmSkuSelect.opts.toCommercialId.length>0&&scmSkuSelect.opts.toCommercialId!='null'){
            var toshopId = $("#"+scmSkuSelect.opts.toCommercialId).val();
            formData["toshopId"] = toshopId;
        }
        return formData;
    },

    /**
     * 扫码添加商品
     */
    addSkuByBarcode: function () {
        var _this = this;

        $(document).delegate("#barcode", "keydown", function(e) {
            if (e.keyCode == 13) {
                if (!_this.checkBeforeAddSku($("#btnSelectSku"))) {
                    $("#barcode").val('');
                    return;
                }

                var barcode = $.trim($("#barcode").val());
                if (barcode == '') {
                    return;
                }

                var data = {
                    barcode: barcode,
                    skuScene: _this.opts.skuScene,
                    excludedSkuId: $('#skuSelectModal input[name="excludedSkuId"]').val()
                };

                data = _this.serializeGridDataCallback(data);

                $.ajax({
                    url: ctxPath + "/common/querySkuByBarcode",
                    data: data,
                    type: "post",
                    async: false,
                    dataType: "json",
                    success: function (result) {
                        if (result.success) {
                            var rowData = result.data;
                            scmSkuSelect.opts.gridObj = $('#' + scmSkuSelect.opts.outterGridId); //取得最新节点

                            if (scmSkuSelect.opts.outterGridId == undefined || scmSkuSelect.opts.outterGridId == '') {
                                alert('表格id不能为空');
                                return false;
                            }

                            var skuIdColName = $('#skuIdColName').val(); // default : skuId
                            var skuIds = scmSkuSelect.opts.gridObj.getCol(skuIdColName); // 外部商品的grid中已有的商品id
                            var skuIdAndRowIds = scmSkuSelect.opts.gridObj.getCol(skuIdColName, true); // 外部商品的grid中已有的商品id及其行id

                            var rowIdToInsert = rowData.id;
                            var qtyColName = _this.opts.qtyColName;

                            var index_1 = skuIds.indexOf(rowIdToInsert + ""),
                                index_2 = skuIds.indexOf((0 - rowIdToInsert) + "");
                            if (index_1 == -1 && index_2 == -1) { // 判断外部商品的grid中是否已有选中的商品
                                rowData[skuIdColName] = rowIdToInsert;

                                //盘点单特殊处理
                                if(scmSkuSelect.opts.skuScene == 61){
                                    rowData['realTimeInventory'] = rowData['inventoryQty']; //盘点单实时库存
                                    rowData['ccQty'] = rowData['inventoryQty']; //盘点数
                                    //盘点单添加商品时，查询的inventoryQty为实时库存，originalCcInventoryQty为库存固定
                                    rowData['inventoryQty'] = rowData['originalCcInventoryQty'];
                                    if(parseFloat(rowData['ccQty']) < 0){
                                        rowData['ccQty'] = 0;
                                    }
                                    rowData['ccAmount'] = $.toFixed(parseFloat(rowData['ccQty']) * parseFloat(rowData['price']));
                                    rowData['qtyDiff'] = $.toFixed(parseFloat(rowData['ccQty']) - parseFloat(rowData['realTimeInventory']));
                                    rowData['amountDiff'] = $.toFixed(parseFloat(rowData['qtyDiff']) * parseFloat(rowData['price']));
                                }

                                rowData[qtyColName] = 1;
                                //收货单特殊处理
                                if(scmSkuSelect.opts.skuScene == '81'){
                                    rowData['applyQty'] = 0;
                                    rowData['planQty'] = 1;
                                }
                                //V2.4 添加隐藏列 当前库存
                                rowData['standardInventoryQty'] = rowData['inventoryQty'];

                                //自产模版 单位自增ID
                                rowData['uomId'] = rowData['uomIdForTable'];

                                rowData['amount'] = 0; // 和 bug 3966 相关，该bug有时不能重现，此处也许能解决该bug

                                scmSkuSelect.opts.gridObj.jqGrid("addRowData", rowIdToInsert, rowData);

                                $('#cb_' + scmSkuSelect.opts.outterGridId).attr('checked', false); // 重置checkbox为未选中

                                $('#' + rowIdToInsert + '_' + qtyColName).trigger('propertychange');

                                var jqgridBdiv = scmSkuSelect.opts.gridObj.parents('.ui-jqgrid-bdiv');
                                if (jqgridBdiv.length > 0) {
                                    jqgridBdiv[0].scrollTop = jqgridBdiv[0].scrollHeight;
                                }

                                //回调
                                var afterAdd = $('#btnSelectSku').attr('afterAdd');
                                if(afterAdd){
                                    if (afterAdd && $.isFunction(afterAdd.toFunction())) {
                                        afterAdd.toDo();
                                    }
                                }

                                //移入、移除重新载入分类
                                var reloadType = $('#btnSelectSku').attr('reloadType');
                                if(reloadType){
                                    if (reloadType && $.isFunction(reloadType.toFunction())) {
                                        reloadType.toDo();
                                    }
                                }
                            } else {
                                if (qtyColName == 'null') {
                                    $.layerMsg("此商品已存在", true, {icon: 6});
                                    return;
                                }
                                //获取行id,新增时行id一般为skuId，编辑时行id可能为数据库的主键id；skuIdAndRowIds和skuIds的顺序一般一致
                                var index = index_1 == -1 ? index_2 : index_1,
                                    rowId = skuIdAndRowIds[index].id,
                                    skuId = skuIdAndRowIds[index].value;

                                //校验根据下标得出的商品是否就是该商品，若不是则循环重新查找，一般不用
                                if (rowIdToInsert != skuId && (0 - rowIdToInsert) != skuId) {
                                    for (var i = 0; i < skuIdAndRowIds.length; i++) {
                                        if (rowIdToInsert == skuIdAndRowIds[i].value || (0 - rowIdToInsert) == skuIdAndRowIds[i].value) {
                                            rowId = skuIdAndRowIds[i].id;
                                            break;
                                        }
                                    }
                                }

                                var $qtyInput = $('#' + rowId + '_' + qtyColName);
                                var oldValue = $qtyInput.val() || 0;
                                $qtyInput.val(parseFloat(oldValue) + 1);
                                $qtyInput.trigger('propertychange');
                            }

                            $.layerMsg("添加成功", true);
                        } else {
                            $.layerMsg("小on提醒您：<span style='color: red'>" + barcode + "</span>未识别", false, {time: 0});
                        }
                    },
                    error: function () {
                        $.layerMsg("请求失败！", false, {time: 0});
                    },
                    complete: function () {
                        $("#barcode").val('');
                    }
                });
            }
        });
    }
};

/**
 * 刷新JqGrid表格
 */
function refreshSkuInfo(target){
    scmSkuSelect.opts.isSortCol = false;
    //验证查询条件是否改变
    var condition = $('#' + scmSkuSelect.opts.searchFormId).serialize();
    if(!target && scmSkuSelect.opts.searchCondition == condition && $(scmSkuSelect.opts.gridTableId).jqGrid('getRowData').length>0){
        $('#'+scmSkuSelect.opts.addBtnId).click();
        return false;
    }
    //查询条件改变后刷新表格
    $(scmSkuSelect.opts.gridTableId).refresh();
    //保存查询条件
    scmSkuSelect.opts.searchCondition = condition;
};

function addSkubefore(){
    showLoading('#addsku');

    setTimeout(function() {
        addSku();
        hideLoading('#addsku');
    }, 50);
}

/**
 * 添加商品到外部商品列表
 * @returns {boolean}
 */
function addSku() {
    var addCount = 0; //添加个数
    var notAddCount = 0; //未添加的重复个数
    scmSkuSelect.opts.gridObj = $('#' + scmSkuSelect.opts.outterGridId); //取得最新节点
    $('#keyWord').focus();

    if (scmSkuSelect.opts.outterGridId == undefined || scmSkuSelect.opts.outterGridId == '') {
        alert('表格id不能为空');
        return false;
    }

    var skuIdColName = $('#skuIdColName').val(); // default : skuId
    var skuIds = scmSkuSelect.opts.gridObj.getCol(skuIdColName); // 外部商品的grid中已有的商品id

    var selectedRowIds = $(scmSkuSelect.opts.gridTableId).jqGrid("getGridParam", "selarrrow");
    if (selectedRowIds == undefined || selectedRowIds == null || selectedRowIds.length == 0) {
        $.layerMsg('未选择任何' + scmSkuSelect.opts.sku + '，请勾选' + scmSkuSelect.opts.sku + '后点击添加！', false);
        return false;
    }
    var gridDatas = [];
    //表格数据有变动后再重载表格
    for (var i = 0; i < selectedRowIds.length; i++) {
        var rowIdToInsert = selectedRowIds[i];
        var resetCheckAll = false;
        if (skuIds.indexOf(rowIdToInsert) == -1 && skuIds.indexOf((0 - rowIdToInsert) + "") == -1) { // 判断外部商品的grid中是否已有选中的商品
            var rowData = $(scmSkuSelect.opts.gridTableId).getRowData(rowIdToInsert);
            rowData[skuIdColName] = rowIdToInsert;

            //盘点单特殊处理
            if(scmSkuSelect.opts.skuScene == 61){
                rowData['realTimeInventory'] = rowData['inventoryQty']; //盘点单实时库存
                rowData['ccQty'] = rowData['inventoryQty']; //盘点数
                //盘点单添加商品时，查询的inventoryQty为实时库存，originalCcInventoryQty为库存固定
                rowData['inventoryQty'] = rowData['originalCcInventoryQty'];
                if(parseFloat(rowData['ccQty']) < 0){
                    rowData['ccQty'] = 0;
                }
                rowData['ccAmount'] = $.toFixed(parseFloat(rowData['ccQty']) * parseFloat(rowData['price']));
                rowData['qtyDiff'] = $.toFixed(parseFloat(rowData['ccQty']) - parseFloat(rowData['realTimeInventory']));
                rowData['amountDiff'] = $.toFixed(parseFloat(rowData['qtyDiff']) * parseFloat(rowData['price']));
            }
            //收货单特殊处理
            if(scmSkuSelect.opts.skuScene == '81'){
                rowData['applyQty'] = 0;
                rowData['planQty'] = 0;
            }
            //V2.4 添加隐藏列 当前库存
            rowData['standardInventoryQty'] = rowData['inventoryQty'];

            //自产模版 单位自增ID
            rowData['uomId'] = rowData['uomIdForTable'];

            rowData['amount'] = 0; // 和 bug 3966 相关，该bug有时不能重现，此处也许能解决该bug
            if(scmSkuSelect.opts.addRowWithRealod){
                gridDatas.push(rowData); //20980 【库存预警设定】多仓库中添加大概200个商品，界面卡死了
            }else{
                scmSkuSelect.opts.gridObj.jqGrid("addRowData", rowIdToInsert, rowData);
            }
            resetCheckAll = true;
            addCount++;
        }else{
            notAddCount++;
        }
        if(resetCheckAll){
            $('#cb_' + scmSkuSelect.opts.outterGridId).attr('checked', false); // 重置checkbox为未选中
        }
    }

    //数据拼接好后统一生成节点(//20980 【库存预警设定】多仓库中添加大概200个商品，界面卡死了
    if(scmSkuSelect.opts.addRowWithRealod){
        var rowData = scmSkuSelect.opts.gridObj.jqGrid('getRowData');
        for (var i = 0; i < rowData.length; i++) {
            if(rowData[i]['skuName']){
                var index=rowData[i]['skuName'].indexOf('<span style="color:red">');
                if(index>0){
                    rowData[i]['skuName']=rowData[i]['skuName'].substring(6,index);
                }
            }
        }
        scmSkuSelect.opts.gridObj.jqGrid('setGridParam',{data: rowData.concat(gridDatas)});
        scmSkuSelect.opts.gridObj.trigger('reloadGrid');
    }

    //回调
    var afterAdd = $('#btnSelectSku').attr('afterAdd');
    if(afterAdd){
        if (afterAdd && $.isFunction(afterAdd.toFunction())) {
            afterAdd.toDo();
        }
    }

    //移入、移除重新载入分类
    var reloadType = $('#btnSelectSku').attr('reloadType');
    if(reloadType){
        if (reloadType && $.isFunction(reloadType.toFunction())) {
            reloadType.toDo();
        }
    }

    //显示添加成功商品与添加失败商品
    var msg = '';
    if(addCount > 0){
        msg += addCount + '个商品添加成功';
    }
    if(notAddCount > 0){
        if(msg){
            msg += ',';
        }
        msg += notAddCount+ '个商品已存在'
    }

    $('#addCount').html(msg);
};


$.afterRemoved = function(){
    if(scmSkuSelect.opts.dataGridCal.summaryCalculate){
        scmSkuSelect.opts.dataGridCal.summaryCalculate(scmSkuSelect.opts.dataGridCal.opts, $('#' + scmSkuSelect.opts.outterGridId));
    }
    if($.filterGrid){
        //如果有商品筛选组件 则刷新商品分类
        $.filterGrid.initSkuTypeNames();
        $.filterGrid.initInventoryStype();
        $('#filterSearch').click();
    }
};

$.afterAdd = function(){
    //$('#' + scmSkuSelect.opts.outterGridId).trigger('reloadGrid');
    if(scmSkuSelect.opts.dataGridCal.summaryCalculate){
        scmSkuSelect.opts.dataGridCal.summaryCalculate(scmSkuSelect.opts.dataGridCal.opts, $('#' + scmSkuSelect.opts.outterGridId));
    }
    if(scmSkuSelect.opts.dataGridCal.customerFunc){
        scmSkuSelect.opts.dataGridCal.customerFunc();
    }
    //$('input[name="ccQty"]').first().trigger('propertychange');
    if($.filterGrid){
        //如果有商品筛选组件 则刷新商品分类
        $.filterGrid.initSkuTypeNames();
        $.filterGrid.initInventoryStype();
        $('#filterSearch').click();
    }
};

$(function () {
    //填充商品选择组件 start
    var tmplSkuSelect = '	<div id="skuSelectModal" class="modal fade in" aria-hidden="true">\
            <div class="modal-dialog"  style="width: 1000px; margin-top: 90px;">\
                <div class="modal-content">\
                    <div class="modal-header">\
                        <a class="close" data-dismiss="modal">&times;</a>\
                        <h3 style="font-size: 17px">{{sku}}选择<span style="margin-left: 15px;font-size: 13px;color: #999;">注：【查询】【添加】可使用回车(Enter)键，【关闭】可使用Esc键</span></h3>\
                    </div>\
                    <div class="modal-body modal-body-skuselect">\
                        <form id="skuselectConditions" action="#" method="post" style="margin: 0">\
                            <div>\
                                <div class="form-container clearfix">\
                                    <div class="form-item">\
                                        <label class="control-label">库存类型</label>\
                                        <div class="control-label-con">\
                                            <div class="multi-select" >\
                                                 <div class="select-control"><em>请选择库存类型</em></div>\
    												<div class="multi-select-con" style="display:none;">\
    													<ul class="multi-select-items" id="wmType">\
    														<li>\
    															<label class="checkbox" for="wmType-1">\
    																<span></span>\
    																<input type="checkbox" name="wmType" id="wmType-1" value="1" data-text="预制商品" >预制商品\
    															</label>\
    														</li>\
    														<li>\
    															<label class="checkbox" for="wmType-3">\
    																<span></span>\
    																<input type="checkbox" name="wmType" id="wmType-3" value="3" data-text="外购商品" >外购商品\
    															</label>\
    														</li>\
    														<li>\
    															<label class="checkbox" for="wmType-4">\
    																<span></span>\
    																<input type="checkbox" name="wmType" id="wmType-4" value="4" data-text="原物料" >原物料\
    															</label>\
    														</li>\
    														<li>\
    															<label class="checkbox" for="wmType-5">\
    																<span></span>\
    																<input type="checkbox" name="wmType" id="wmType-5" value="5" data-text="半成品" >半成品\
    															</label>\
    														</li>\
                                                    	</ul>\
                                                 </div>\
                                                 <input type="hidden" value="" />\
                                            </div>\
                                        </div>\
                                    </div>\
                                    <div class="form-item">\
                                        <label class="control-label">{{sku}}分类</label>\
                                        <div class="control-label-con">\
                                            <div>\
                                                <select class="form-control" name="skuTypeId" id="modalSkuTypeId">\
                                                    <option value="">请选择{{sku}}分类</option>\
                                                </select>\
                                            </div>\
                                        </div>\
                                    </div>\
                                    <div class="form-item">\
                                        <label class="control-label">{{sku}}编码/名称</label>\
                                        <div class="control-label-con search-box">\
                                            <input style="padding-right:25px;" data-format="skuName" type="text" name="skuCodeOrName" id="keyWord" class="form-control"\
                                                   placeholder="请输入{{sku}}编码/名称" maxlength="48" />\
                                            <button style="right:0;" id="btnClose" type="button" class="close" aria-hidden="true">&times;</button>\
                                        </div>\
                                    </div>\
                                    <div class="form-item" style="float:right;">\
                                			<a class="btn-blue btn-search" id="btnSearch" role="button" onclick="refreshSkuInfo(this)" style="margin-left: 30px;position:relative">查 询<span class="iconfont loading icon-b"></span></a>\
                                    </div>\
                                </div>\
                            </div>\
                        	   <input type="text" style="display:none;" name="skuScene" value="{{skuScene}}" />\
                        	   <input type="text" style="display:none;" name="excludedSkuId" value="{{excludedSkuId}}" />\
                        	   <input type="text" style="display:none;" name="warehouseSelectId" value="{{warehouseId}}" />\
                        	   <input type="text" style="display:none;" name="supplierSelectId" value="{{supplierId}}" />\
                        	   <input type="text" style="display:none;" name="commercialSelectId" value="{{commercialId}}" />\
                        	   <input type="text" style="display:none;" name="toCommercialId" value="{{toCommercialId}}" />\
                        	   <input type="text" style="display:none;" id="ccTask" name="ccTask" value="{{ccTask}}" />\
                        </form>\
                    </div>\
                    <div style="width: 999px; height: 395px" >\
                        <table id="skuSelectGrid"></table>\
                    </div>\
                    <div class="modal-footer" style="margin-top: 0;position:relative;">\
                         <span id="addCount" style="position: absolute;display: inline-block;left: 40%;top: 50%;margin-top: -10px;color: #5cb85c;"></span>\
                        <a href="#" id="addsku" class="btn btn-success" onclick="addSkubefore()" style="position:relative;">添 加<span class="iconfont loading icon-b"></span></a>\
    						 <a href="#" class="btn btn-primary" data-dismiss="modal" style="position:relative;">关 闭<span class="iconfont loading icon-b"></span></a>\
                    </div>\
                </div>\
            </div>\
        </div>\
    	  <input type="hidden" id="ctxPath" value="{{ctxPath}}" />\
    	  <input type="hidden" id="outterGridId" value="{{gridId}}" />\
    	  <input type="hidden" id="skuIdColName" value="{{skuIdColName}}" />\
    	  <input type="hidden" id="singleSelect" value="{{singleSelect}}" />\
    	  <input type="hidden" id="sku" value="{{sku}}" />\
          <input type="hidden" id="qtyColName" value="{{qtyColName}}" />';

    var $scmSkuSelectDiv = $('#scmSkuSelectDiv');
    var render = template.compile(tmplSkuSelect);
    var html = render({
        ctxPath: $scmSkuSelectDiv.attr('ctxPath'),
        gridId: $scmSkuSelectDiv.attr('gridId'),
        skuIdColName: $scmSkuSelectDiv.attr('skuIdColName') || 'skuId',
        skuScene: $scmSkuSelectDiv.attr('skuScene'),
        excludedSkuId: $scmSkuSelectDiv.attr('excludedSkuId') || -1,
        warehouseId: $scmSkuSelectDiv.attr('warehouseId'),
        supplierId: $scmSkuSelectDiv.attr('supplierId'),
        commercialId: $scmSkuSelectDiv.attr('commercialId'),
        sku: $scmSkuSelectDiv.attr('sku') || '商品',
        toCommercialId: $scmSkuSelectDiv.attr('toCommercialId'),
        ccTask: $scmSkuSelectDiv.attr('ccTask'),
        singleSelect: $scmSkuSelectDiv.attr('singleSelect') || '0',
        qtyColName: $scmSkuSelectDiv.attr('qtyColName') || 'planQty'
    });
    $scmSkuSelectDiv.html(html);
    bkeruyun.selectControl($('#' + scmSkuSelect.opts.searchFormId).find('select'));

    //填充商品选择组件 end

    var modal = $('#skuSelectModal');

    if(modal){

        var opts = {
            sku : $("#sku").val() || '商品'
        };

        scmSkuSelect._init(opts);
    }

    //模态框可见时执行
    modal.on('shown.bs.modal', function () {
        $('#keyWord').focus();
        // fix bug 17043 【原料配方设定】选择库存类型，点击enter，响应的是添加按钮
        $('.multi-select').on('click',function(e){
            $('#keyWord').focus();
        });
        $(window).on('keydown.modal', function (e) {
            if($('#skuSelectModal').css('display') == 'block'){
                var keyCode = e.keyCode;
                switch(keyCode){
                    case 13:
                        //Enter
                        refreshSkuInfo();
                        break;
                    case 27:
                        //Escape
                        $(scmSkuSelect.opts.skuSelectModalId).modal('hide');
                        break;
                }
            }
        });
    });
    //模态框隐藏时间执行
    modal.on('hidden.bs.modal', function () {
        scmSkuSelect.opts.searchCondition = '';
        $('div.layui-layer').hide();
        $('#keyWord').off('keydown.modal');
        $(window).off('keydown.modal');
        $('#addCount').html('');
    });


});


