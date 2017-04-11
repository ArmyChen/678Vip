/**
 * Created by zhu.lf on 2016/9/20.
 */
//默认设定数据
var defaultData = {data :
    [

    ]
};
//已保存的设定数据
var selectData = {
    data : [

    ]
};

var templateData = {data:[
]};
var pageInitFirst = true;
var month = moment().month();
var FORMAT = 'YYYY-MM-DD';

var setting = {
    saveUrl: ctxPath + '/scm/cost/save',
    prompt: {
        2: '商品属性中的结算价栏位值', //结算价栏位值
        3: '商品属性中的成本价栏位值', //成本价栏位值
        4: '根据配方明细，各商品进行完全拆解，成本相加得出，当无配方明细时配方估算成本为0。', //预估计算
        11: '截止到当前时间30天内的该原物料的采购加权平均价', //加权平均值
        12: '最新采购单据中的采购价格', //最新采购单据值
        13: '商品属性中的采购价栏位值' //采购价栏位值
    }
};
var month = moment().month();

$(function () {
    var data = splitData(defaultData.data);
    var brandData = data.brandData;
    var commercialData = data.commercialData;

    fillTable('brandTable',brandData);
    fillTable('commercialTable',commercialData);

    if(templateData.data.length > 0){
        fillAccountingPeriodTemplate('accountingPeriod',templateData);
    }

    bindEvent();
    initSelectValue();

    $('select').trigger('change');
});

//分割出品牌数据与商户数据
function splitData(data){
    var brandData = {data : []};
    var commercialData = {data : []};

    data.forEach(function(v,i){
        if(parseInt(v.commercialId) == '-1'){
            brandData.data.push(v);
        }else{
            commercialData.data.push(v);
        }
    });

    return {brandData : brandData,commercialData : commercialData}
}

//绑定事件
function bindEvent(){
    $('table').on('change', 'select', function () {
        var $this = $(this);
        var linkdata = $this.find('option:checked').data('linkdata');
        if(typeof  linkdata == 'string'){
            linkdata = JSON.parse(linkdata);
        }

        $this.parents('td').next().find('[name="costRule"]').val(getFirst(linkdata,1)).trigger('change');
        var $spanLink = $this.parents('td').next().find('.span-link');
        if(linkdata.length > 1){
            $spanLink.addClass('active');
        }else{
            $spanLink.removeClass('active');
        }
        $spanLink.html(getFirst(linkdata));
    });

    $('table').on('click', '.span-link.active', function () {
        var $this = $(this);
        var linkdata = $this.parents('td').prev().find('select option:checked').data('linkdata');
        if(typeof  linkdata == 'string') {
            linkdata = JSON.parse(linkdata);
        }

        $('#costRuleModal').modal({
            backdrop: 'static'
        });

        $('#costRuleModal').on('shown.bs.modal', function () {
            var inputs = '';

            linkdata.sort(function(a,b){return a.priority - b.priority});
            linkdata.forEach(function (v,i) {
                var dataString = JSON.stringify(v);
                inputs += '<li class="ui-state-default" data-costdata=' + dataString + '><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' + v.costRuleName + '</li>';
            });

            $( "#sortable" ).html(inputs).sortable();
        });

        $('#costRuleOk').on('click', function () {
            var orderVos = [];
            $('#sortable').children().each(function(i,e){
                var costdata = $(e).data('costdata');
                if(typeof  linkdata == 'string') {
                    costdata = JSON.parse(costdata);
                }

                costdata.priority = i+1;
                orderVos.push(costdata);
            });

            //优先级变更后设置计算规则
            $this.parents('td').prev().find('select option:checked').data('linkdata',JSON.stringify(orderVos));
            $this.text(orderVos[0].costRuleName);
            $this.prev().val(orderVos[0].costRule).trigger('change');

            $('#costRuleModal').modal('hide');
        });
    });

    //设置?提示信息
    $('table').on('change', '[name="costRule"]', function () {
        var $this = $(this);
        var costCaseName = setting.prompt[$this.val()];
        $this.siblings('.iconfont.question').data('content',costCaseName);
    });
    $('[name="costRule"]').trigger('change');

    $("#explanation").click(function () {
        $('#explanationModal').modal({
            backdrop: 'static'
        });
    });

    $("#ruleExplain").click(function () {
        $('#ruleExplainModal').modal({
            backdrop: 'static'
        });
    });

    $('input[name="dateEnd"]').change(function(){
        changeDay(this);
    });
    $("#costExplain").click(function () {
        $('#costExplainModal').modal({
            backdrop: 'static'
        });
    });
}

$('#costRuleModal').on('hidden.bs.modal', function () {
    $('#costRuleOk').off('click');
});

//将json数据保存为string格式
template.helper('toString', function (data,format) {
    return JSON.stringify(data);
});

//取得优先级为1的数据,format:1 取值；format:2 取名
template.helper('firstData', function (data,format) {
    return getFirst(data,format);
});

function changeDay(element){
    pageInitFirst = false;
    var value = $(element).val();
    var index = $(element).data('index');
    var data = templateData.data;

    //设置当前会计期
    var start = data[index].dateStart;
    var end = data[index].dateEnd = value;
    data[index].dateCnt = moment(end).diff(start,'days') + 1;

    //设置以后会计期
    for(var i = index + 1;i<data.length;i++){
        var preRow = data[i-1];
        var row = data[i];
        var dateStart = moment(preRow.dateEnd).add(1,'d');
        var dateEnd = moment(dateStart).add(1,'M').subtract(1,'d');
        var maxDay = moment().set('month',i).endOf('month');
        if(dateEnd.isAfter(maxDay)){
            dateEnd = maxDay;
        }

        row.dateStart = dateStart.format(FORMAT);
        row.dateEnd = dateEnd.format(FORMAT);
        row.dateCnt = dateEnd.diff(dateStart,'days') + 1;
    }

    $('#accountingPeriod').empty();
    fillAccountingPeriodTemplate('accountingPeriod',templateData);
    $('input[name="dateEnd"]').change(function(){
        changeDay(this);
    });
}

//绑定选择的数据，并初始化下拉选框
function initSelectValue(){
    if(selectData.data.length > 0){
        $('tbody>tr').each(function (i,e) {
            var $this = $(e);
            var brandId = $this.find('[name="brandId"]').val();
            var commercialId = $this.find('[name="commercialId"]').val();
            var wmType = $this.find('[name="wmType"]').val();

            var data = {};
            for(var i in selectData.data){
                var item = selectData.data[i];
                if(parseInt(brandId) == item.brandId && (parseInt(commercialId) != -1 || parseInt(commercialId) == item.commercialId) && parseInt(wmType) == item.wmType){
                    data = item;
                    break;
                }
            }

            if(!data.priceTypeVos){
                return;
            }

            var linkdata = data.priceTypeVos[0].orderVos;
            linkdata.sort(function(a,b){return a.priority - b.priority});

            $this.find('[name="costCase"]').val(data.priceTypeVos[0].costCase);
            $this.find('[name="costCase"]').find('option:checked').data('linkdata',JSON.stringify(linkdata));
        });
    }

    bkeruyun.selectControl($('select'));
}

//取得优先级为1的数据
function getFirst(data,option){
    var rs = '';
    data.forEach(function (v,i) {
        if(v.priority == 1 || v.priority == '1'){
            rs = v;
            return;
        }
    });
    if(option == 1){
        return rs.costRule;
    }else{
        return rs.costRuleName;
    }
}

//填充表格
function fillTable(containerId, data){
    var html = template('settingTemplate', data);
    $('#' + containerId).html(html);
}

function fillAccountingPeriodTemplate(containerId,data){
    var datas = data.data;
    if(moment(datas[month].dateEnd).isBefore(moment()) && pageInitFirst){
        month = month + 1;
    }
    for(var i =0;i<datas.length;i++){
        var row = datas[i];
        var minDay = moment().set('month',i).startOf('month');

        if(minDay.isBefore(moment(row.dateStart))){
            minDay = moment(row.dateStart);
        }
        if(minDay.isBefore(moment())){
            minDay = moment();
        }
        row.minDay = minDay.format(FORMAT);
        row.maxDay = moment().set('month',i).endOf('month').format(FORMAT);
        row.isNow = i == month;
        row.disable = i < month;
        row.periouds = row.fiscalYear + '.' + row.fiscalPeriod;
    }
    var html = template('accountingPeriodTemplate',data);
    $('#' + containerId).html(html);
}

//恢复默认选择
function reseTable(key){
    layer.confirm('选择恢复默认选择，将覆盖当前价格类型的取值和计算规则的优先级设定。是否确认？', {icon: 3, title: '提示', offset: '30%'}, function (index) {
        var data = splitData(defaultData.data);
        switch(key){
            case 0:
                fillTable('brandTable',data.brandData);
                break;
            case 1:
                fillTable('commercialTable',data.commercialData);
                break;
        }
        bkeruyun.selectControl($('select'));
        $('[name="costCase"]').trigger('change');
        $('[name="costRule"]').trigger('change');

        layer.close(index);
    });
}

//保存配方估算成本设定
$.saveSkuBom = function(args){
    //取得数据
    var dataList = [];
    $('tbody>tr').each(function(i,e){
        var $this = $(e);
        var data = {};
        var $optionChecked = $this.find('[name="costCase"]').find('option:checked');

        data.brandId = $this.find('[name="brandId"]').val();
        data.commercialId = $this.find('[name="commercialId"]').val();
        data.wmType = $this.find('[name="wmType"]').val();
        data.wmTypeName = $this.find('[name="wmTypeName"]').val();
        data.costCase = $this.find('[name="costCase"]').val();
        data.costCaseName = $optionChecked.text();
        if(typeof $optionChecked.data('linkdata') == 'string'){
            data.orderVos = JSON.parse($optionChecked.data('linkdata'));
        }else{
            data.orderVos = $optionChecked.data('linkdata');
        }

        dataList.push(data);
    });

    bkeruyun.showLoading();
    $.ajax({
        type: "post",
        async: false,
        url : setting.saveUrl,
        contentType : 'application/json',
        dataType : 'json',
        data : JSON.stringify(dataList),
        success: function (data) {
            if(!data.success){
                $.layerMsg(data.message);
            }else{
                $.layerMsg('保存成功',true);
            }
            bkeruyun.hideLoading();
        },
        error: function () {
            bkeruyun.hideLoading();
            $.layerMsg("网络错误", false);
        }
    });
};

//保存库存参数设定
$.saveSettings = function(args){
    //取得销售成本数据
    var dataList = [];
    var elements = $('tbody[name="saleCostSettings"]>tr');
    elements.each(function(i,e){
        var $this = $(e);
        var data = {};
        var $optionChecked = $this.find('[name="costCase"]').find('option:checked');

        data.brandId = $this.find('[name="brandId"]').val();
        data.commercialId = $this.find('[name="commercialId"]').val();
        data.wmType = $this.find('[name="wmType"]').val();
        data.wmTypeName = $this.find('[name="wmTypeName"]').val();
        data.costCase = $this.find('[name="costCase"]').val();
        data.costCaseName = $optionChecked.text();
        if(typeof $optionChecked.data('linkdata') == 'string'){
            data.orderVos = JSON.parse($optionChecked.data('linkdata'));
        }else{
            data.orderVos = $optionChecked.data('linkdata');
        }

        dataList.push(data);
    });

    //获取价格同步设定数据
    var priceSyncSettings = [];
    var priceSyncSettingCheckboxIds = ["type_31", "type_32", "type_41", "type_42"];
    for (var i = 0; i < priceSyncSettingCheckboxIds.length; i++) {
        var $setting = $("#" + priceSyncSettingCheckboxIds[i]);
        var value = $setting.is(':checked') ? 1 : 0;
        priceSyncSettings.push(
            {
                id: $setting.attr("data-id"),
                settingItemType: $setting.attr("data-type"),
                settingItemValue: value,
                version: $setting.attr("data-version")
            }
        );
    }

    var fiscalPeriods = templateData.data;
    for(var i in fiscalPeriods){
        var row = fiscalPeriods[i];
        delete row.minDay;
        delete row.maxDay;
        delete row.disable;
        delete row.periouds;
        delete row.isNow;
    }
    var settings = {
        saleCostSettings: dataList,
        priceSyncSettings: priceSyncSettings,
        fiscalPeriods: templateData.data,
    };

    $.ajax({
        type: "post",
        async: false,
        url : setting.saveUrl,
        contentType : 'application/json;charset=UTF-8',
        dataType : 'json',
        data : JSON.stringify(settings),
        beforeSend: bkeruyun.showLoading,
        success: function (result) {
            if (result.success) {
                var data = result.data;
                $.each(data, function(i, n) {
                    $("#type_" + n.settingItemType).attr({"data-id": n.id, "data-version": n.version});
                });

                $.layerMsg("保存成功", true);
            } else {
                if (result.flag == 1) {
                    $.layerMsg("数据已过期", false, {
                        end: function() {
                            window.location.reload(true);
                        }
                    });
                } else {
                    $.layerMsg("保存失败", false);
                }
            }
        },
        error: function () {
            $.layerMsg("网络错误", false);
        },
        complete: function (jqXHR, textStatus) {
            //取消遮罩
            bkeruyun.hideLoading();
        }
    });
};