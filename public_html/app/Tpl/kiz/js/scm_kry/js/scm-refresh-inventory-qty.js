


var refreshInventoryQty = {

    opts: {
        gridId: '',
        warehouseSelectId:'',
        skuIdColName: 'skuId'
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        _this.delegateWarehouseChange(_this.opts.warehouseSelectId);
    },




    delegateWarehouseChange : function(warehouseSelectId) {

        var _this = this;

        if(warehouseSelectId && warehouseSelectId.length > 0 && warehouseSelectId != 'null'){
            $(document).delegate('#' + warehouseSelectId, 'change', function () {
                var warehouseId = $('#' + warehouseSelectId).val();
                if(warehouseId && warehouseId.length > 0){
                    _this.refreshInventoryQty(warehouseId);
                }
            });
        };

    },


    refreshInventoryQty : function(warehouseId){

        var _this = this;

        var $gridObj = $('#' + _this.opts.gridId);

        var skuIdColName = _this.opts.skuIdColName; // default : skuId

        if(skuIdColName){

            var skuIds = $gridObj.getCol(skuIdColName); // 外部商品的grid中已有的商品id
            if(skuIds && skuIds.length > 0){
                $.ajax({
                    url: ctxPath + "/common/getInventoryQty",
                    data: {warehouseId: warehouseId, skuIds: skuIds.join(',')},
                    type: "post",
                    async: false,
                    dataType: "json",
                    success: function (data) {
                        var ids = $gridObj.getDataIDs();
                        for(var i = 0; i < ids.length; i++){
                            var rowData = $gridObj.getRowData(ids[i]);
                            for(var j = 0; j < data.length; j++){
                                var skuVo = data[j];
                                if(skuVo.id == rowData.skuId){
                                    // fix bug 26177 【配送签收单】选择仓库后，当前库存未显示
                                    // 配送签收单 rowData.standardInventoryQty 是undefined
                                    var newRowData = {};
                                    if(rowData.inventoryQty) {
                                        newRowData.inventoryQty = data[j].inventoryQty;
                                    }
                                    if(rowData.standardInventoryQty) {
                                        newRowData.standardInventoryQty = data[j].inventoryQty;
                                    }
                                    if(rowData.skuConvertOfStandard) {
                                        newRowData.skuConvertOfStandard = rowData.skuConvertOfStandard;
                                    }
                                    if(rowData.skuConvert) {
                                        newRowData.skuConvert = rowData.skuConvert;
                                    }
                                    $gridObj.jqGrid('setRowData', ids[i], newRowData);
                                    /*$gridObj.jqGrid('setRowData', ids[i], {
                                     inventoryQty: data[j].inventoryQty,
                                     standardInventoryQty: data[j].inventoryQty,
                                     skuConvertOfStandard: rowData.skuConvertOfStandard,
                                     skuConvert: rowData.skuConvert
                                     });*/
                                }
                            }
                        }
                        $gridObj.find(':text').first().trigger('propertychange');
                    }
                });
            }
        }
    }
};
