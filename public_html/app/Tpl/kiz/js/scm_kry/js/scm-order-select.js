//gridId : 商户列表JqGrid table的id
//showBrand : 是否显示品牌
var orderSelect = {
    opts: {
        gridTableId : '#orderSelectGrid',
        orderSelectModalId : '#orderSelectModal',
        provCityDistInitialized : false,
        outterGridId: '',
        gridObj: {},
        templateRequired: '',
        initUrl: '/supply/aggregate/getAggregateOrderQuery',
        dataUrl: '/supply/aggregate/query/applyOrder',
        searchCondition: '',
        searchFormId: 'orderSelectConditions',
        addBtnId: 'addApplyOrder',
        searchBtnId: 'btnOrderSearch',
        isSortCol: false
    },

    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        _this.opts.outterGridId = $('#outterOrderGridId').val();
        _this.opts.gridObj = $('#' + _this.opts.outterGridId);

        _this.delegateBtnAdd();
        _this.delegateBtnRemove();
    },

    /**
     * 监听添加商户按钮
     */
    delegateBtnAdd: function(){
        var _this = this;
        //添加商户绑定
        $(document).delegate('#btnSelectOrder', 'click', function () {
            $(_this.opts.orderSelectModalId).modal({
                backdrop: 'static'
            });
            _this.clearConditions(); // 清空查询条件
            $(_this.opts.gridTableId).jqGrid('setGridParam', {datatype: 'local'}).trigger('reloadGrid');
            _this.getOrderInfo();

            //需求汇总单页面初始化时显示查询结果
            if(!orderSelect.opts.templateRequired){
                $("#btnOrderSearch").trigger("click");
            }
        });
    },

    /**
     * 监听移除商户按钮
     */
    delegateBtnRemove: function(){
        var _this = this;
        //删除商户绑定
        $(document).delegate('#btnDeleteOrder', 'click', function () {
            var ids = _this.opts.gridObj.jqGrid("getGridParam", "selarrrow");
            if (ids == undefined || ids.length == 0) {
                $.layerMsg('请选择单据!', false);
                return false;
            }
            var len = ids.length;
            layer.confirm("是否移除单据？", {icon: 3, title:'提示', offset: '30%'}, function(index){
                for(var i = 0; i < len; i++){
                    _this.opts.gridObj.jqGrid('delRowData', ids[0]);
                }
                $('#cb_' + _this.opts.outterGridId).attr('checked', false); // 重置checkbox为未选中
                layer.close(index);
              
                var callAfterFunc = $("#btnDeleteOrder").attr("callAfterSuccessFunc");
                if(callAfterFunc) eval(callAfterFunc);
            });
        });
    },

    /**
     * 清除查询条件
     */
    clearConditions: function() {
        var _this = this;
        //初始化条件
        $("#arriveDateStart,#arriveDateEnd").val('');
        $("#templateIdDiv").html('<select name="templateId" id="modalTemplateId" class="{required:true}"><option value="">请选择配送模板</option></select>');
        $("#dlIdDiv").html('<select name="dlId" id="modalDlId"><option value="">请选择配送线路</option></select>');
        
        _this.initQueryData();
        if($('#lockTemplateId').val() == ''){
            $('#modalTemplateId').prev().find('li:first').click();
        }
        $('#modalDlId').prev().find('li:first').click();
        $('#orderSelectModal').find('input:not(:hidden)').val('');
    },

    /**
     * 初始化查询条件
     * @returns {boolean}
     */
    initQueryData: function() {
	   var _this = this;
	   var orderId = $("#orderId").val();

        var firstRowID = $('#' + _this.opts.outterGridId).getDataIDs()[0];
        var addedTemplateId = firstRowID ? $('#' + _this.opts.outterGridId).getCell(firstRowID, 'templateId') : false;

       $.ajax({
           url:  ctxPath + _this.opts.initUrl,
           type: 'post',
           data: {orderId:orderId},
           dataType: 'json',
           async: false,
           success: function(details){
               if(details == undefined || details == null || details.success == false) return false;
               var templateNames = [],
                   optionTemplate = '',
                   dlNames = [],
                   optionLine = '';

               //初始化模板
               $.each(details.template, function(index, detail){
                   if(detail != undefined || detail != null){
                       if(templateNames.indexOf(detail.name) < 0){
                           templateNames.push(detail.name);
                           var selected = addedTemplateId == detail.id ? 'selected' : '';
                           var isDisable = detail.isDisable == true ? '（已停用）' : '';
                           optionTemplate += '<option delivery-cycle="' + detail.deliveryCycle +'" value=' + detail.id + ' ' + selected + ' >' + detail.name + isDisable + '</option>';
                       }
                   }
               });
               $('#modalTemplateId').append(optionTemplate);


               //停用模版后依然显示
               $.each(details.dlInfo, function(index, detail){
                   if(detail != undefined || detail != null){
                       if(dlNames.indexOf(detail.dlName) < 0){
                           dlNames.push(detail.dlName);
                           optionLine += '<option value=' + detail.id + '>' + detail.dlName + '</option>';
                       }
                   }
               });
               $('#modalDlId').append(optionLine);

           }
       });
        bkeruyun.selectControl($('#' + orderSelect.opts.searchFormId).find('select'));

        //单据列表中有单据时，配送模板不可变更
        if($('#lockTemplateId').val() == '1' && addedTemplateId){
            $('#modalTemplateId').siblings('.select-control').addClass('disabled');
        }
    },

    /**
     * 查询商户
     */
    getOrderInfo: function() {
        var _this = this;
        var $gridObj = $(_this.opts.gridTableId);
        $gridObj.dataGrid({
            formId: "orderSelectConditions",
            url: ctxPath + orderSelect.opts.dataUrl,
            datatype: 'local',
            showEmptyGrid: true,
            rownumbers: true,
            multiselect: true,
            multiselectWidth: 50,
            height: 340,
            colNames: ['id','orderId','templateId','commercialId','单据号', '申请商户', '配送模板','要货日期','到货日期','线路名称'],
            colModel: [
				{
					name: 'id',
					index: 'id',
					hidden: true,
                    key: true
				},
                {name: 'orderId',index: 'orderId',hidden: true},
                {name: 'templateId',index: 'templateId',hidden: true},
                {name: 'commercialId',index: 'commercialId',hidden: true},
                {
                	name: 'orderNo',
                	index: 'orderNo',
                	width: 200
                },
                {
                	name: 'commercialName', 
                	index: 'commercialName', 
                	width: 200
                },
                {
                	name: 'templateName', 
                	index: 'templateName',
                	width: 200
                },
                {
                	name: 'applyDate', 
                	index: 'applyDate'
                },
                {
                	name: 'arriveDate', 
                	index: 'arriveDate'
                },
                {
                    name: 'dlNames',
                    index: 'dlNames',
                    width: 185
                }
            ],
            sortname: 'arriveDate',
            sortorder: "asc",
            pager: "",
            rowNum: "0",
            onSortCol: function () {
                orderSelect.opts.isSortCol = true;
            },
            beforeRequest: function(){
                if(orderSelect.opts.isSortCol){
                    return;
                }
                showLoading('#btnOrderSearch');
            },
            loadComplete: function(data){
                hideLoading('#btnOrderSearch');
                if(data.dataList && data.dataList.length == 1){
                    var selectId = data.dataList[0].id;
                    $(orderSelect.opts.gridTableId).jqGrid("setSelection", selectId);
                    $("#jqgh_orderSelectGrid_cb").trigger("click");
                }
            },
            loadError: function(xhr, status, error){
                hideLoading('#btnOrderSearch');
                $('#addOrderCount').html(error);
            }
        });
    }
    
};

/**
 * 刷新JqGrid表格
 */
refreshApplyOrders = function(target) {
    orderSelect.opts.isSortCol = false;
    //检查配送模板
    var templateId = $('#modalTemplateId').val();
    var condition = $('#' + orderSelect.opts.searchFormId).serialize();
    if(orderSelect.opts.templateRequired && (templateId == undefined || templateId === '')){
        $.layerMsg('请选择配送模板！', false);
        return false;
    }

    if(!target && orderSelect.opts.searchCondition == condition && $(orderSelect.opts.gridTableId).jqGrid('getRowData').length>0){
        $('#' + orderSelect.opts.addBtnId).click();
        return false;
    }

    $(orderSelect.opts.gridTableId).refresh(); // 第二次及之后的查询
    //保存查询条件
    orderSelect.opts.searchCondition = condition;
};

function addApplyOrderBefore(){
    showLoading('#addApplyOrder');

    setTimeout(function() {
        addApplyOrder();
        hideLoading('#addApplyOrder');
    }, 50);
}

/**
 * 添加商户到外部商户列表
 * @returns {boolean}
 */
addApplyOrder = function() {
    var addCount = 0; //添加个数
    var notAddCount = 0; //未添加的重复个数
    orderSelect.opts.gridObj = $('#' + orderSelect.opts.outterGridId); //取得最新节点

    if (orderSelect.opts.outterGridId == undefined || orderSelect.opts.outterGridId == '') {
        alert('表格id不能为空');
        return false;
    }
    var commercialIds = orderSelect.opts.gridObj.jqGrid('getDataIDs');
    var selectedRowIds = $(orderSelect.opts.gridTableId).jqGrid("getGridParam", "selarrrow");
    if (selectedRowIds == undefined || selectedRowIds == null || selectedRowIds.length == 0) {
        $.layerMsg('未选择任何单据，请勾选单据后点击添加！', false);
        return false;
    }
    //表格数据有变动后再重载表格
    for (var i = 0; i < selectedRowIds.length; i++) {
        var rowIdToInsert = selectedRowIds[i];
        var rowData = $(orderSelect.opts.gridTableId).getRowData(rowIdToInsert);
        if (commercialIds.indexOf(rowData.id) == -1) {
            orderSelect.opts.gridObj.jqGrid("addRowData", rowData.id, rowData);
            addCount++;
        }else{
            notAddCount++;
        }
    }
    
    //after call func
    var callAfterFunc = $("#btnSelectOrder").attr("callAfterSuccessFunc");
    if(callAfterFunc) eval(callAfterFunc);

    //显示添加成功商品与添加失败商品
    var msg = '';
    if(addCount > 0){
        msg += addCount + '个单据添加成功';
    }
    if(notAddCount > 0){
        if(msg){
            msg += ',';
        }
        msg += notAddCount+ '个单据已存在'
    }

    $('#addOrderCount').html(msg);
    if($('#lockTemplateId').val()){
        $('#modalTemplateId').siblings('.select-control').addClass('disabled');
    }
};

$(function () {
    var tmplCommericalSelect = '\
    <div id="orderSelectModal" class="modal fade in" aria-hidden="true">\
        <div class="modal-dialog"  style="width: 1188px; margin-top: 60px;">\
            <div class="modal-content">\
                <div class="modal-header">\
                    <a class="close" data-dismiss="modal">&times;</a>\
                    <h3 style="font-size: 17px">添加单据<span style="margin-left: 15px;font-size: 13px;color: #999;"></span></h3>\
                </div>\
                <div style="padding:0px;" class="modal-body">\
                    <form id="orderSelectConditions" action="#" method="post" style="margin: 0">\
                           <input type="hidden" id="orderId" name="orderId" value="{{(orderId)}}">\
                           <input type="hidden" name="dpId" value="{{dpId}}">\
                        <div class="panel-body">\
                            <div class="pull-left">\
                               <dl class="panel-item positionRelative">\
                                  <dt>\
                                       <label for="modalTemplateId">\
                                       配送模板\
                                       {{if templateRequired}}\
                                       <strong class="red vam"> *</strong>\
                                       {{/if}}\
                                       </label>\
                                       <div class="wrong"></div>\
                                  </dt>\
                                  <dd>\
                                       <div class="pull-left w150" id="templateIdDiv">\
                                           <select name="templateId" id="modalTemplateId" class="{required:true}">\
                                               <option value="">请选择配送模板</option>\
                                           </select>\
                                       </div>\
                                   </dd>\
                               </dl>\
                            </div>\
                            <div class="pull-left">\
                                <dl class="panel-item">\
                                    <dt>到货日期</dt>\
                                    <dd>\
                                        <div class="search-box">\
                                            <input type="text" name="arriveDateStart" id="arriveDateStart" class="form-control datepicker-start" data-for-element="arriveDateEnd" placeholder="请选择开始日期" readonly>\
                                            <button type="button" class="close" aria-hidden="true">&times;</button>\
                                        </div>\
                                    </dd>\
                                </dl>\
                            </div>\
                            <div class="pull-left">\
                                <dl class="panel-item" style="margin-left:-70px">\
                                    <dt>~</dt>\
                                    <dd>\
                                        <div class="search-box">\
                                            <input type="text" name="arriveDateEnd" id="arriveDateEnd" class="form-control datepicker-end" data-for-element="arriveDateStart" placeholder="请选择结束日期" readonly>\
                                            <button type="button" class="close" aria-hidden="true">&times;</button>\
                                        </div>\
                                    </dd>\
                                </dl>\
                            </div>\
                            <div class="pull-left">\
                                <dl class="panel-item">\
                                    <dt>配送线路</dt>\
                                    <dd>\
                                        <div class="pull-left w150" id="dlIdDiv">\
                                            <select name="dlId" id="modalDlId">\
                                                <option value="">请选择配送线路</option>\
                                            </select>\
                                        </div>\
                                    </dd>\
                                </dl>\
                            </div>\
                            <div class="pull-left">\
                                <div class="panel-item">\
                                    <a id="btnOrderSearch" class="btn-blue btn-search" role="button" onclick="refreshApplyOrders(this)" style="position:relative;" >查 询<span class="iconfont loading icon-b"></span></a>\
                                </div>\
                            </div>\
                        </div>\
                        <input type="text" class="hidden"/>\
                    </form>\
                </div>\
                <div style="width: 1180px; height: 385px" >\
                    <table id="orderSelectGrid"></table>\
                </div>\
                <div class="modal-footer" style="margin-top: 0;padding: 7px 17px;position:relative;">\
                    <span id="addOrderCount" style="position: absolute;display: inline-block;left: 40%;top: 50%;margin-top: -10px;color: #5cb85c;"></span>\
                    <a id="addApplyOrder" onfocus="this.blur()" href="#" class="btn btn-success positionRelative" onclick="addApplyOrderBefore()">添 加<span class="iconfont loading icon-b" style="visibility: hidden;"></span></a>\
                    <a href="#" class="btn btn-primary" data-dismiss="modal">关 闭</a>\
                </div>\
            </div>\
        </div>\
    </div>\
    <input type="hidden" id="ctxPath" value="{{ctxPath}}" />\
    <input type="hidden" id="lockTemplateId" value="{{lockTemplateId}}" />\
    <input type="hidden" id="outterOrderGridId" value="{{gridId}}" />';

    var render = template.compile(tmplCommericalSelect);
    var $orderSelectDiv = $('#orderSelectDiv');
    orderSelect.opts.templateRequired = $orderSelectDiv.attr('templateRequired');
    if($orderSelectDiv.attr('initUrl')) orderSelect.opts.initUrl = $orderSelectDiv.attr('initUrl');
    if($orderSelectDiv.attr('dataUrl')) orderSelect.opts.dataUrl = $orderSelectDiv.attr('dataUrl');

    var htmlOrderSelectDiv = render({
        ctxPath: ctxPath,
        gridId: $orderSelectDiv.attr('gridId'),
        orderId: $orderSelectDiv.attr('orderId') || '',
        dpId: $orderSelectDiv.attr('dpId') || '',
        templateRequired: $orderSelectDiv.attr('templateRequired') || '',
        lockTemplateId: $orderSelectDiv.attr('lockTemplateId') || ''
    });
    $('#orderSelectDiv').html(htmlOrderSelectDiv);

    var modal = $('#orderSelectModal');
    if(modal){
        var opts = {};
        orderSelect._init(opts);
    }

    //模态框可见时执行
    $(orderSelect.opts.orderSelectModalId).on('shown.bs.modal', function () {
        $(window).on('keydown.modal', function (e) {
            if($('#orderSelectModal').css('display') == 'block'){
                var keyCode = e.keyCode;
                switch(keyCode){
                    case 13:
                        //Enter
                        refreshApplyOrders();
                        break;
                    case 27:
                        //Escape
                        $(orderSelect.opts.orderSelectModalId).modal('hide');
                        break;
                }
            }
        });
    });
    //模态框隐藏时间执行
    $(orderSelect.opts.orderSelectModalId).on('hidden.bs.modal', function () {
        orderSelect.opts.searchCondition = '';
        $('div.layui-layer').hide();
        $('#orderSelectModal').off('keydown.modal');
        $(window).off('keydown.modal');
        $('#addOrderCount').html('');
    });
});

