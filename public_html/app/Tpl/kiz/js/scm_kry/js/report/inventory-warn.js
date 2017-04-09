/**
 * 库存预警报表
 *
 * Created by xia.zl on 2016/11/3.
 */

var cachedQueryConditions = ''; //缓存页面条件

var baseUrl,shortageUrl,warmingUrl,overStockUrl;

var initCommercialId = $('select[name=commercialId]').val();

var inventoryWarn = {
    opts: {
        urlRoot:'',
        shortageId: '#grid_shortage',
        shortageFootId: '#gridPager_shortage',
        warnId: '#grid_warn',
        warnFootId: '#gridPager_warn',
        overstockId: '#grid_overstock',
        overstockFootId: '#gridPager_overstock',
        cachedQueryConditions: '', //页面查询条件
        queryConditionsId: 'queryConditions',
        shortNumerMsg: '&nbsp;<span class="iconfont question color-g" data-content="短缺数=库存下限-当前库存"></span>',
        shortPercentMsg: '&nbsp;<span class="iconfont question color-g" data-offset="left" data-content="短缺比例=短缺数/库存下限"></span>',
        warnNumerMsg: '&nbsp;<span class="iconfont question color-g" data-content="预警数=安全库存-当前库存"></span>',
        warnPercentMsg: '&nbsp;<span class="iconfont question color-g" data-offset="left" data-content="预警比例=预警数/安全库存"></span>',
        overstockNumerMsg: '&nbsp;<span class="iconfont question color-g" data-content="积压数=当前库存-库存上限"></span>',
        overstockPercentMsg: '&nbsp;<span class="iconfont question color-g" data-offset="left" data-content="积压占比=积压数/库存上限"></span>'
    }
};

 function initUrl(args){
     baseUrl = args.urlRoot+"/report/inventoryWarn/";
     shortageUrl = args.urlRoot+'/report/inventoryWarn/queryLow';
     warmingUrl = args.urlRoot+'/report/inventoryWarn/query';
     overStockUrl = args.urlRoot+ '/report/inventoryWarn/queryUp';
     inventoryWarn.opts.urlRoot = args.urlRoot;
}

$(function () {
    refreshWms($('select[name=commercialId]').val());
    delegateSelect('#commercialId');


//-------------绑定下拉 start-----------
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
        _this.delegateCheckbox('wmTypes', '#wm-type-all');
    }

    delegateMultiSelect();

    //tab标签切换
    $('.tab-nav .tab-btn').on('click', function () {
        var $this = $(this);

        if ($this.hasClass("btn-active")) {
            return;
        }

        $this.removeClass('btn-default').addClass('btn-active');
        $this.siblings('button').removeClass('btn-active').addClass('btn-default');
        //显示区域
        $('.tab-box').hide();
        $('#' + $this.attr("data-box-id")).show();

        var isInitialized = $this.hasClass("is-initialized");//是否已初始化过了
        var needToRefresh = $this.hasClass("need-to-refresh");//是否需要刷新

        if (needToRefresh) {
            if ($this.attr("data-box-id") == 'tabBox_1') {
                if (isInitialized) {
                    $(inventoryWarn.opts.shortageId).refresh(-1);
                } else {
                    initTable(inventoryWarn.opts.shortageId, inventoryWarn.opts.shortageFootId, 1, shortageUrl);
                    $this.addClass("is-initialized");
                }
            } else if ($this.attr("data-box-id") == 'tabBox_2') {
                if (isInitialized) {
                    $(inventoryWarn.opts.warnId).refresh(-1);
                } else {
                    initTable(inventoryWarn.opts.warnId, inventoryWarn.opts.warnFootId, 2, warmingUrl);
                    $this.addClass("is-initialized");
                }
            } else if ($this.attr("data-box-id") == 'tabBox_3') {
                if (isInitialized) {
                    $(inventoryWarn.opts.overstockId).refresh(-1);
                } else {
                    initTable(inventoryWarn.opts.overstockId, inventoryWarn.opts.overstockFootId, 3, overStockUrl);
                    $this.addClass("is-initialized");
                }
            }

            $this.removeClass("need-to-refresh");
        }

        transformBtnShow();

        //显示导出按钮
        $('.freeDownExcel').hide();
        $('#' + $this.attr("down-excel-id")).show();
    });

    //供应商-商品查询条件重置
    $("#undo-all").on("click", function (e) {
        e.preventDefault();
        bkeruyun.clearData($(this).parents('.aside'));

        if (!bkeruyun.isPlaceholder()) {
            JPlaceHolder.init();
        }

        $('.multi-select > .select-control').each(function () {
            var $this = $(this);
            //显示默认文字
            $this.find('em').text($this.data('default'));
            $this.find('input:hidden').val('');
        });
    });
    cachedQueryConditions = serializeFormById('queryConditions');

    $.setSearchFocus();

    //跳转到相应的tab页
    function getParam(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var index = window.location.search.substr(1).match(reg);
        if(index!=null)return  unescape(index[2]); return null;
    }
    var index = getParam("index");

    switch (index){
        case '2' :
            $('#tabNav2').click();
            break;
        case '3' :
            $('#tabNav3').click();
            break;
        default :
            $('#tabNav1').click();
    }
//        initTable(inventoryWarn.opts.shortageId,inventoryWarn.opts.shortageFootId,1,shortageUrl);

    // 交互
    $(".multi-select > .select-control").on("click",function(e){
        e.stopPropagation();
        var showList = $(this).next(".multi-select-con");
        if(showList.is(":hidden")){
            $(".multi-select-con").hide();
            $(".select-control").removeClass("select-control-arrowtop");
            showList.show();
            $(this).addClass("select-control-arrowtop");
        }else{
            showList.hide();
            $(this).removeClass("select-control-arrowtop");
        }
    });

    /**
     * 刷新仓库的下拉选框
     * @param commercialIds 门店id，多个值时以逗号分隔
     */
    function refreshWms(commercialIds){

        $('#wmId-all-em').html('全部');// 如果门店id为空，则清空仓库的文本显示

        if(commercialIds === ''){
            $('#wmId-all-ul').html('');   // 如果门店id为空，则清空仓库选项
            return;
        }

        var arr = JSON.parse(wareHouseJson);
        var wareHousesWanted = [];

        for(var i = 0; i < arr.length; i++){
            var wm = arr[i];
            var wmPrefIndex = wm.warehouseName.indexOf('_');
            if(commercialIds.indexOf(wm.warehouseName.substr(0, wmPrefIndex)) >= 0){
                wm.warehouseName = wm.warehouseName.substr(wmPrefIndex + 1);
                wareHousesWanted.push(wm);
            }
        }

        buildWmSelect(wareHousesWanted);

    }

    function buildWmSelect(data){

        var wmIdAllLi =
            '<li>' +
            '<label class="checkbox" for="warehouseId-all">' +
            '<span></span>' +
            '<input type="checkbox" id="warehouseId-all">全部' +
            '</label>' +
            '</li>';

        $('#wmId-all-ul').html('');
        $('#wmId-all-ul').html(wmIdAllLi); // 默认一开始添加“全部”

        var count = 0; // 一共添加的仓库的数量

        /** 一次添加一个仓库 **/
        $(data).each(function (i, v) {

            var isDisable = v.isDisable ? '（已停用）' : '';

            var wmTypeItem =

                '<li>' +
                '<label class="checkbox" for="warehouseId-' + count + '">' +
                '<span></span>' +
                '<input type="checkbox" name="warehouseIds" id="warehouseId-' + count + '" value="' + v.id + '" data-text="' + v.warehouseName + isDisable + '" >' + v.warehouseName + isDisable +
                '</label>' +
                '</li>';
            $('#wmId-all-ul').append(wmTypeItem);
            count++;
        });

        if(count == 0){
            $('#wmId-all-ul').html(''); // 如果没有仓库，应把“全部”删除
        }

        delegateCheckbox('warehouseIds', '#warehouseId-all');  // 开始监听仓库的checkbox
        $('#warehouseId-all').click();
    }

    /**
     * 监听品牌/商户下拉选框
     * @param name
     * @param id
     */
    function delegateSelect(id){
        //业务类型 条件选择
        $(document).delegate(id, "change", function(){
            refreshWms($('select[name=commercialId]').val());
            transformBtnShow();
        });
    };

    //导出按钮点击
    $(document).delegate("#export", "click", function () {
        formDateFormat('export');
    });

    /**
     * 表单数据封装
     */
    function formDateFormat(operate) {
        var url = '';
        var currentQueryConditions = serializeFormById('queryConditions');

        if (currentQueryConditions != cachedQueryConditions) {
            $.layerMsg('条件已改变，请先点击查询按钮！', false);
            return false;
        }

        var conditions = $.extend(true, {}, $('#' + inventoryWarn.opts.queryConditionsId).getFormData() || {});

        conditions = '?' + $.param(conditions, true);
        var isEnable = '', isDisable = '';
        var skuNameOrCode = $('#skuNameOrCode').val();
        var wmTypeNames = $('#wmTypeId-all-em').text();
        if (wmTypeNames == $('#wmTypeId-all-em').parent().attr('data-default'))
            wmTypeNames = '';
        var skuTypeNames = $('#skuTypeId-all-em').text();
        if (skuTypeNames == $('#skuTypeId-all-em').parent().attr('data-default'))
            skuTypeNames = '';
        var commercialNames = $('#commercialId').siblings('.select-control').children().text();
        var warehouseNames = $('#wmId-all-em').text();
        if ($("input[name='isEnable']").attr('checked'))
            isEnable = $("input[name='isEnable']").val();
        if ($("input[name='isDisable']").attr('checked'))
            isDisable = $("input[name='isDisable']").val();
        conditions = conditions.replace('skuNameOrCode', 'skuNameOrCodeNotWanted');
        conditions += ('&skuNameOrCode=' + skuNameOrCode);
        conditions += ('&wmTypeNames=' + wmTypeNames);
        conditions += ('&skuTypeNames=' + skuTypeNames);
        conditions += ('&commercialNames=' + commercialNames);
        conditions += ('&warehouseNames=' + warehouseNames);
        conditions += ('&isEnable=' + isEnable);
        conditions += ('&isDisable=' + isDisable);

        conditions = encodeURI(encodeURI(conditions));

        var $gridObj, part;
        var activeTab = $(" .btn-group").find(" .btn-active").attr("data-box-id");
        if (activeTab == 'tabBox_1') {
            $gridObj = $("#grid_shortage");
            part = "shortageExport";
        }
        else if (activeTab == 'tabBox_2') {
            $gridObj = $("#grid_warn");
            part = "warnExport";
        }
        else {
            $gridObj = $("#grid_overstock");
            part = "overStockExport";
        }

        var totalSize = $gridObj.jqGrid('getGridParam', 'records');
        var sidx = $gridObj.jqGrid('getGridParam', 'sortname');
        var sord = $gridObj.jqGrid('getGridParam', 'sortorder');
        //rows=0将获取所有记录，不分页

        // if(totalSize <= 0 && operate == 'export'){
        //     $.layerMsg('导出记录为空！', false);
        //     return;
        // }
        // else if(totalSize <= 0 && operate == 'transform') {
        //     $.layerMsg('转单记录为空！', false);
        //     return;
        // }

        if (totalSize > 0 && operate == 'export') {
            url = baseUrl + part + conditions + "&rows=0&sidx=" + sidx + "&sord=" + sord;
            window.open(url, '_blank');
        } else if (totalSize == 0 && operate == 'export') {
            $.layerMsg('导出记录为空！', false);
        }
        else if (totalSize > 0 && operate == 'transform') {
            url = inventoryWarn.opts.urlRoot + '/purchase/apply/transform' + conditions;
            if(part == 'shortageExport')
                window.location.href = url + "&flag=1";
            else
                window.location.href = url + "&flag=2";
        }
        else
            $.layerMsg('转单记录为空！', false);;
    }

    //转单
    $('#transform').click(function () {
        formDateFormat('transform');
    })
});

function initTable(tableId,footId,type,url){
    var opts = inventoryWarn.opts;
    var gridOpts =  {
        formId: opts.queryConditionsId,
        url: url,
        dataType: 'local',
        colNames: ['', '仓库名称', '库存类型', '商品中类', '商品编码', '商品名称(规格)', '单位'],
        colModel: [
            {name: 'id', index: 'id', hidden: true},
            {name: 'warehouseName', index: 'warehouseName', width: 100, align: 'left'},
            {name: 'wmTypeName', index: 'wmType', width: 100, align: 'left'},
            {name: 'skuTypeName', index: 'typeName', width: 100, align: 'left'},
            {name: 'skuCode', index: 'skuCode', width: 100, align: 'left'},
            {name: 'skuName', index: 'skuName', width: 100, align: 'left',
                formatter: function (cellvalue, options, rowObject) {
                    if (rowObject.isDelete == 1) {
                        return cellvalue + "<span style='color:red'>(已删除)</span>";
                    } else if (rowObject.isDisable == 1) {
                        return cellvalue + "<span style='color:red'>(已停用)</span>";
                    } else {
                        return cellvalue;
                    }
                }
            },
            {name: 'uom', index: 'uom', width: 100, align: 'center'}
        ],
        sortname: 'skuCode',
        sortorder: "asc",
        showOperate: false,
        pager: footId
    };

    var shortage = {
        colNames: ['库存下限', '当前库存', '短缺数' + opts.shortNumerMsg, '短缺比例' + opts.shortPercentMsg],
        colModel: [
            {name: 'settingQty', index: 'lowerInventory', width: 100, align: 'left'},
            {name: 'qty', index: 'qty', width: 100, align: 'left'},
            {name: 'limitQty', index: 'limitQty', width: 100, align: 'left',
                formatter: function(cellValue, options, rowObject) {
                    if(cellValue != null){
                        var icon = '<i data-flag="trueFlag" class="glyphicon glyphicon-arrow-down"></i>';
                        return '<span style="color: #FF634D">' + cellValue + icon + '</span>';
                    }else{
                        return  '';
                    }
                }
            },
            {name: 'proportionStr', index: 'proportionStr', width: 100, align: 'left',
                formatter: function(cellValue, options, rowObject) {
                    if(cellValue != null){
                        return '<span style="color: #FF634D">' + cellValue + '</span>';
                    }else{
                        return '' ;
                    }
                }
            }
        ]
    };

    var warn = {
        colNames: ['安全库存', '当前库存', '预警数' + opts.warnNumerMsg, '预警比例'  + opts.warnPercentMsg],
        colModel: [
            {name: 'settingQty', index: 'safetyInventory', width: 100, align: 'left'},
            {name: 'qty', index: 'qty', width: 100, align: 'left'},
            {name: 'limitQty', index: 'limitQty', width: 100, align: 'left',
                formatter: function(cellValue, options, rowObject) {
                    if(cellValue != null){
                        var icon = '<i data-flag="trueFlag" class="glyphicon glyphicon-arrow-down"></i>';
                        return '<span style="color: #FF9257">' + cellValue + icon + '</span>';
                    }else{
                        return  '';
                    }
                }
            },
            {name: 'proportionStr', index: 'proportionStr', width: 100, align: 'left',
                formatter: function(cellValue, options, rowObject) {
                    if(cellValue != null){
                        return '<span style="color: #FF9257">' + cellValue + '</span>';
                    }else{
                        return '';
                    }
                }
            }
        ]
    };

    var overstock = {
        colNames: ['库存上限', '当前库存', '积压数' + opts.overstockNumerMsg, '积压比例' + opts.overstockPercentMsg],
        colModel: [
            {name: 'settingQty', index: 'upperInventory', width: 100, align: 'left'},
            {name: 'qty', index: 'qty', width: 100, align: 'left'},
            {name: 'limitQty', index: null, width: 100, align: 'left',
                formatter: function(cellValue, options, rowObject) {
                    if(cellValue != null){
                        var icon = '<i data-flag="trueFlag" class="glyphicon glyphicon-arrow-up"></i>';
                        return '<span style="color: #FFBA00">' + cellValue + icon + '</span>';
                    }else{
                        return '';
                    }
                }
            },
            {name: 'proportionStr', index: null, width: 100, align: 'left',
                formatter: function(cellValue, options, rowObject) {
                    if(cellValue != null){
                        return '<span style="color: #FFBA00">' + cellValue + '</span>';
                    }else{
                        return '';
                    }
                }
            }
        ]
    };

    var colNames = gridOpts.colNames;
    var colModel = gridOpts.colModel;
    switch (type){
        case 1:
            gridOpts.colNames = colNames.concat(shortage.colNames);
            gridOpts.colModel = colModel.concat(shortage.colModel);
            break;
        case 2:
            gridOpts.colNames = colNames.concat(warn.colNames);
            gridOpts.colModel = colModel.concat(warn.colModel);
            break;
        case 3:
            gridOpts.colNames = colNames.concat(overstock.colNames);
            gridOpts.colModel = colModel.concat(overstock.colModel);
            break;
    }

    $(tableId).dataGrid(gridOpts);
}

/**
 * 监听下拉选框的checkbox
 * @param namepom
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
    }else if(lenChecked == 0){
        //显示默认文字
        $textObj.text($textObj.parent().data('default'));
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

function load() {
    //验证
    var commercialId = $('select[name=commercialId]').val();
    if (!commercialId || commercialId.length < 1) {
        $.layerMsg('请选择品牌/商户!', false);
        return;
    }

    var activeTab = $(".btn-group").find(".btn-active").attr("data-box-id");
    var refreshTableId ;
    if(activeTab == 'tabBox_1' )
        refreshTableId = inventoryWarn.opts.shortageId;
    if(activeTab == 'tabBox_2' )
        refreshTableId =inventoryWarn.opts.warnId;
    if(activeTab == 'tabBox_3' )
        refreshTableId = inventoryWarn.opts.overstockId;
    cachedQueryConditions = serializeFormById('queryConditions');
    $(refreshTableId).refresh(-1);
    $(".btn-group").find(".btn-active").siblings().addClass("need-to-refresh");
}

//转单按钮显示
function transformBtnShow() {
    var commercialId = $('select[name=commercialId]').val();
    var tab = $('.btn-group .btn-active').attr("data-box-id");
    var flag;
    if(commercialId != initCommercialId || tab == 'tabBox_3')
        flag = false;
    else
        flag = true;
    if(flag == false) {
        $('#transformBtn').attr('disabled',true);
    }
    else
        $('#transformBtn').removeAttr('disabled');
}
