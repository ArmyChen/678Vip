//gridId : 商户列表JqGrid table的id
//showBrand : 是否显示品牌
var skuCommercialSelect = {
    opts: {
        gridTableId : '#skuCommercialSelectGrid',
        commercialSelectModalId : '#commercialSelectModal',
        provCityDistInitialized : false,
        outterGridId: '',
        gridObj: {},
        searchCondition: '',
        searchFormId : 'commercialselectConditions',
        addBtnId: 'addSkuCommercial',
        searchBtnId: 'btnCommercialSearch',
        isSortCol: false
    },

    _init : function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});
        _this.opts.outterGridId = $('#outterShopGridId').val();
        _this.opts.gridObj = $('#' + _this.opts.outterGridId);

        _this.delegateBtnAdd();
    },

    /**
     * 监听添加商户按钮
     */
    delegateBtnAdd: function(){

        if($('#btnSelectCommercial').attr('class')&&$('#btnSelectCommercial').attr('class').indexOf('button-disable') < 0) {
            var _this = this;
            //添加商户绑定
            $(document).delegate('#btnSelectCommercial', 'click', function () {
                $(_this.opts.commercialSelectModalId).modal({
                    backdrop: 'static'
                });
                _this.clearConditions(); // 清空查询条件
                $(_this.opts.gridTableId).jqGrid('setGridParam', {datatype: 'local'}).trigger('reloadGrid');
                _this.getCommercialInfo();
            });
        }
    },

    /**
     * 清除查询条件
     */
    clearConditions: function() {
        var _this = this;
        _this.initProvCityDistNew();
        $('#commercialSelectModal').find('select').parent().find('ul li:first').click();
        $('#commercialSelectModal').find('#commercialIdOrName').val('');
    },

    /**
     * 初始化“省市区”下拉选框
     * @returns {boolean}
     */
    initProvCityDistNew: function() {
        var _this = this;
        if (_this.opts.provCityDistInitialized) {
            return false;
        }
        var $province = $('#provinceCode');
        var $city = $('#cityCode');
        var $district = $('#districtCode');
        $.ajax({
            url: ctxPath + "/common/getProvCityDist",
            type: "post",
            async: false,
            dataType: "json",
            success: function (data) {
                var map_p = data.province;
                var map_c = data.city;
                var map_d = data.district;
                var option_province = '';
                var li_province = '';
                for(key in map_p){
                    option_province += '<option value="' + key + '">' + map_p[key] + '</option>';
                    li_province += '<li>' + map_p[key] + '</li>';
                };
                $province.append(option_province);
                $province.parent().find('ul').append(li_province);

                var option_city = '';
                var li_city = '';
                for(key in map_c){
                    var parent_val = key.substr(0, 2) + '0000';
                    option_city += '<option name="parent-val-' + parent_val + '" value="' + key + '">' + map_c[key] + '</option>';
                    li_city += '<li>' + map_c[key] + '</li>';
                };
                $city.append(option_city);
                $city.parent().find('ul').append(li_city);

                var option_district = '';
                var li_district = '';
                for(key in map_d){
                    var parent_val = key.substr(0, 4) + '00';
                    option_district += '<option name="parent-val-' + parent_val + '" value="' + key + '">' + map_d[key] + '</option>';
                    li_district += '<li>' + map_d[key] + '</li>';
                };
                $district.append(option_district);
                $district.parent().find('ul').append(li_district);
                _this.opts.provCityDistInitialized = true;
            }
        });
        _this.startSelectCascading();

        bkeruyun.selectControl($('#' + skuCommercialSelect.opts.searchFormId).find('select'));
    },


    /**
     * 初始化“省市区”下拉选框
     * @returns {boolean}
     */
    initProvCityDist: function() {
        var _this = this;
        if (_this.opts.provCityDistInitialized) {
            return false;
        }
        var $province = $('#provinceCode');
        var $city = $('#cityCode');
        var $district = $('#districtCode');
        $.ajax({
            url: ctxPath + "/common/getProvCityDistByBrandId",
            type: "post",
            async: false,
            dataType: "json",
            success: function (data) {
                var map_p = data.province;
                var map_c = data.city;
                var map_d = data.district;
                var option_province = '';
                var li_province = '';
                for(key in map_p){
                    option_province += '<option value="' + key + '">' + map_p[key] + '</option>';
                    li_province += '<li>' + map_p[key] + '</li>';
                };
                $province.append(option_province);
                $province.parent().find('ul').append(li_province);

                var option_city = '';
                var li_city = '';
                for(key in map_c){
                    var parent_val = key.substr(0, 2) + '0000';
                    option_city += '<option name="parent-val-' + parent_val + '" value="' + key + '">' + map_c[key] + '</option>';
                    li_city += '<li>' + map_c[key] + '</li>';
                };
                $city.append(option_city);
                $city.parent().find('ul').append(li_city);

                var option_district = '';
                var li_district = '';
                for(key in map_d){
                    var parent_val = key.substr(0, 4) + '00';
                    option_district += '<option name="parent-val-' + parent_val + '" value="' + key + '">' + map_d[key] + '</option>';
                    li_district += '<li>' + map_d[key] + '</li>';
                };
                $district.append(option_district);
                $district.parent().find('ul').append(li_district);
                _this.opts.provCityDistInitialized = true;
            }
        });
        _this.startSelectCascading();

        bkeruyun.selectControl($('#' + skuCommercialSelect.opts.searchFormId).find('select'));
    },





    /**
     * 查询商户
     */
    getCommercialInfo: function() {
        var _this = this;
        var $gridObj = $(_this.opts.gridTableId);
        $gridObj.dataGrid({
            formId: "commercialselectConditions",
            url: ctxPath + '/common/getTempletCommercialJqGridData',
            datatype: 'local',
            showEmptyGrid: true,
            rownumbers: true,
            //shrinkToFit: false,
            //autoScroll: true,
            multiselect: true,
            multiselectWidth: 50,
            height: 340,
            colNames: ['id','name','商户编码', '商户名称', '商户地址'],
            colModel: [
                {
                	name: 'id',
                	index: 'id', 
                	hidden: true,
                	formatter: function(data,opt,cell){
                		return cell.commercialId
                	}
                },
                {
                	name: 'name',
                	index: 'name', 
                	hidden: true,
                	formatter: function(data,opt,cell){
                		return cell.commercialId!=-1?cell.commercialName:'品牌';
                	}
                },
                {
                	name: 'commercialId', 
                	index: 'commercialId', 
                	width: 200,
                    key: true,
                	formatter: function(data,opt,cell){
                		var showCommercialId = cell.commercialId!=-1?cell.commercialId:cell.brandId;
                		if(cell.isDisabled!=0){
                			return "<span style='color:#9D9D9D;'>"+showCommercialId+"</span>";
                		}
                		return showCommercialId;
                	}
                },
                {
                	name: 'commercialName', 
                	index: 'commercialName', 
                	width: 300,
                	formatter: function(data,opt,cell){
	                	if(cell.commercialId==-1) return data + '(<span style="color:red;">品牌</span>)';
	                	return cell.isDisabled==0?data:("<span style='color:#9D9D9D;'>"+data+'(<span style="color:red;">已停用</span>)</span>');
                	}
                },
                {
                	name: 'commercialAddress', 
                	index: 'commercialAddress', 
                	width: 415,
                	formatter: function(data,opt,cell){
	                	return cell.isDisabled==0?data:("<span style='color:#9D9D9D;'>"+data+"</span>");
                	}
                }
            ],
            sortname: 'commercialId',
            sortorder: "asc",
            pager: "",
            rowNum: "0",
            onSortCol: function () {
                skuCommercialSelect.opts.isSortCol = true;
            },
            beforeRequest: function(){
                if(skuCommercialSelect.opts.isSortCol){
                    return;
                }
                showLoading('#btnCommercialSearch');
            },
            loadComplete: function(data){
                hideLoading('#btnCommercialSearch');
                if(data.dataList && data.dataList.length == 1){
                    var selectId = data.dataList[0].commercialId;
                    $(skuCommercialSelect.opts.gridTableId).jqGrid("setSelection", selectId);
                }
            },
            loadError: function(xhr, status, error){
                hideLoading('#btnCommercialSearch');
                $('#addCommercialCount').html(error);
            }
        });
    },


    /**
     * select级联
     */
    startSelectCascading: function(){
        var _this = this;
        //关联“城市”-“区/县”
        _this.delegateSelects('#cityCode', '#districtCode');
        //关联“省份”-“城市”
        _this.delegateSelects('#provinceCode', '#cityCode');
        //“城市”列表默认选中第一个，即“城市”
        $('#cityCode').parent().find('ul').find('li').hide();
        $('#cityCode').parent().find('ul').find('li:first').show();
        //“区/县”列表默认选中第一个，即“区/县”
        $('#districtCode').parent().find('ul').find('li').hide();
        $('#districtCode').parent().find('ul').find('li:first').show();
    },

    delegateSelects: function(selectId, subSelectId){
        var _this = this;
        $(document).delegate(selectId, "change", function(){
            _this.cascadeSubSelect(subSelectId, $(this).val());
        });
    },

    cascadeSubSelect: function(subSelectId, selectedValue){
        var _this = this;
        var optionsOfSelected = $(subSelectId).find('option[name=parent-val-' + selectedValue + ']');
        if(optionsOfSelected != undefined && optionsOfSelected.length > 0) { //用户选中的不是第一个
            //隐藏所有li，只允许显示第一个
            $(subSelectId).parent().find('ul').find('li').hide();
            $(subSelectId).parent().find('ul').find('li:first').show();
            //找到所有“属于上一级”的li，并显示
            var hit = [];
            $.each($(subSelectId).find('option[name=parent-val-' + selectedValue + ']'), function (index, option) {
                hit.push($(option).text()); // 根据第一个select找出第二个select可展示的options
            });
            $.each($(subSelectId).parent().find('ul').find('li'), function(i, li){
                $.each(hit, function (i, h) {
                    if ($(li).text() == h) {
                        $(li).show(); // 显示所有可展示的li
                    }
                });
            });
        } else{ //用户选中的是第一个
            $(subSelectId).parent().find('ul').find('li').hide();
            $(subSelectId).parent().find('ul').find('li:first').show();

        }
        $(subSelectId).parent().find('ul').find('li:first').click();
    }

};

/**
 * 刷新JqGrid表格
 */
refreshCommercialInfo = function(target) {
    skuCommercialSelect.opts.isSortCol = false;
    //验证查询条件是否改变
    var condition = $('#' + skuCommercialSelect.opts.searchFormId).serialize();
    if(!target && skuCommercialSelect.opts.searchCondition == condition && $(skuCommercialSelect.opts.gridTableId).jqGrid('getRowData').length>0){
        $('#'+skuCommercialSelect.opts.addBtnId).click();
        return false;
    }
    $(skuCommercialSelect.opts.gridTableId).refresh(); // 第二次及之后的查询
    //保存查询条件
    skuCommercialSelect.opts.searchCondition = condition;
};

function addSkuCommercialBefore(){

    addSkuCommercial();
}

/**
 * 添加商户到外部商户列表
 * @returns {boolean}
 */
addSkuCommercial = function() {
    var addCount = 0; //添加个数
    var notAddCount = 0; //未添加的重复个数
    skuCommercialSelect.opts.gridObj = $('#' + skuCommercialSelect.opts.outterGridId); //取得最新节点
    $('#commercialIdOrName').focus();

    if (skuCommercialSelect.opts.outterGridId == undefined || skuCommercialSelect.opts.outterGridId == '') {
        alert('表格id不能为空');
        return false;
    }
    var commercialIds = [];
    var selectedRowIds = $(skuCommercialSelect.opts.gridTableId).jqGrid("getGridParam", "selarrrow");
    if (selectedRowIds == undefined || selectedRowIds == null || selectedRowIds.length == 0) {
        $.layerMsg('请添加商户后再次尝试！', false);
        return false;
    }
    //已选择商户
    for (var i = 0; i < selectedRowIds.length; i++) {
        var rowIdToInsert = selectedRowIds[i];
        var commercialIdToSelect = $(skuCommercialSelect.opts.gridTableId).getCell(rowIdToInsert, 'id');
        commercialIds.push(commercialIdToSelect);
    }
    $('#addSkuCommercial').attr('onclick', '');
    layer.confirm('本次价格同步，将会对所勾选门店下所有商品的采购价和成本价进行覆盖，确认执行？', {icon: 3, offset: '30%'}, function (index) {
        $('#addSkuCommercial').attr('disabled', 'disabled');
        $(skuCommercialSelect.opts.commercialSelectModalId).modal('hide');
        $.ajax({
            url: ctxPath + "/sku/synBrandPrice",
            type: "post",
            data: {commercialIds: commercialIds.join(',')},
            dataType: "json",
            beforeSend: bkeruyun.showLoading,
            success: function (result) {
                if (result.success) {
                    $.layerMsg('同步成功!', true);
                } else {
                    $.layerMsg('价格同步失败，请重新尝试!', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $.layerMsg('价格同步失败，请重新尝试!', false);
            },
            complete: function (jqXHR, textStatus) {
                $('#addSkuCommercial').removeAttr('disabled', 'disabled');
                $('#addSkuCommercial').attr('onclick', 'addSkuCommercialBefore()');
                //取消遮罩
                bkeruyun.hideLoading();
            }
        });
    }, function (index) {
        layer.close(index);
        $('#addSkuCommercial').attr('onclick', 'addSkuCommercialBefore()');
        return false;
    });
};

$(function () {
    var tmplCommericalSelect = '\
    <div id="commercialSelectModal" class="modal fade in" aria-hidden="true">\
        <div class="modal-dialog"  style="width: 1014px; margin-top: 60px;">\
            <div class="modal-content">\
                <div class="modal-header">\
                    <a class="close" data-dismiss="modal">&times;</a>\
                    <h3 style="font-size: 17px">品牌价格同步到门店<span style="margin-left: 15px;font-size: 13px;color: red;">注：本次价格同步，将会对所勾选门店下所有商品的采购价和成本价进行覆盖，请谨慎操作！</span></h3>\
                </div>\
                <div style="padding:0px;" class="modal-body">\
                    <form id="commercialselectConditions" action="#" method="post" style="margin: 0">\
                        <input name="showBrand" type="hidden" value="{{showBrand}}"/>\
                        <div class="panel-body">\
                            <div class="pull-left">\
                                        <div class="panel-item w180" >\
                                            <select class="form-control" name="provinceCode" id="provinceCode">\
                                                <option value="">省份</option>\
                                            </select>\
                                        </div>\
                            </div>\
                            <div class="pull-left">\
                                        <div class="panel-item w180" >\
                                            <select class="form-control" name="cityCode" id="cityCode">\
                                                <option value="">城市</option>\
                                            </select>\
                                        </div>\
                            </div>\
                            <div class="pull-left">\
                                        <div class="panel-item w180" >\
                                            <select class="form-control" name="districtCode" id="districtCode">\
                                                <option value="">区/县</option>\
                                            </select>\
                                        </div>\
                            </div>\
                            <div style="margin-top:5px;" class="pull-left">\
                                    <div class="search-box">\
                                        <input data-format="name" type="text" name="commercialIdOrName" id="commercialIdOrName" class="form-control w260"\
                                               placeholder="请输入商户编码/名称" maxlength="48"/>\
                                        <button id="btnClose" type="button" class="close" aria-hidden="true">&times;</button>\
                                    </div>\
                            </div>\
                            <div class="pull-left">\
                                    <div class="panel-item">\
                                        <a id="btnCommercialSearch" class="btn-blue btn-search" role="button" onclick="refreshCommercialInfo(this)" style="position:relative;" >查 询<span class="iconfont loading icon-b"></span></a>\
                                    </div>\
                              </div>\
                        </div>\
                        <input type="text" class="hidden"/>\
                    </form>\
                </div>\
                <div style="width: 999px; height: 385px" >\
                    <table id="skuCommercialSelectGrid"></table>\
                </div>\
                <div class="modal-footer" style="margin-top: 0;padding: 7px 17px;position:relative;">\
                    <span id="addCommercialCount" style="position: absolute;display: inline-block;left: 40%;top: 50%;margin-top: -10px;color: #5cb85c;"></span>\
                    <a id="addSkuCommercial" onfocus="this.blur()" href="#" class="btn btn-success positionRelative" onclick="addSkuCommercialBefore()">保 存<span class="iconfont loading icon-b" style="visibility: hidden;"></span></a>\
                    <a href="#" class="btn btn-primary" data-dismiss="modal">关 闭</a>\
                </div>\
            </div>\
        </div>\
        <input type="hidden" id="ctxPath" value="{{ctxPath}}" />\
        <input type="hidden" id="outterShopGridId" value="{{gridId}}" />\
    </div>';
    var render = template.compile(tmplCommericalSelect);
    $commercialSelectDiv = $('#commercialSelectDiv');
    var htmlCommercialSelect = render({
        ctxPath: ctxPath,
        gridId: $commercialSelectDiv.attr('gridId'),
        showBrand: $commercialSelectDiv.attr('showBrand') || false
    });
    $('#commercialSelectDiv').html(htmlCommercialSelect);

    var modal = $('#commercialSelectModal');
    if(modal){
        var opts = {};
        skuCommercialSelect._init(opts);
    }

    //模态框可见时执行
    $(skuCommercialSelect.opts.commercialSelectModalId).on('shown.bs.modal', function () {
        $('#commercialIdOrName').focus();
        $(window).on('keydown.modal', function (e) {
            if($('#commercialSelectModal').css('display') == 'block'){
                var keyCode = e.keyCode;
                switch(keyCode){
                    case 13:
                        //Enter
                        refreshCommercialInfo();
                        break;
                    case 27:
                        //Escape
                        if(!$('div.layui-layer-shade').attr('style')){
                            $(skuCommercialSelect.opts.commercialSelectModalId).modal('hide');
                        }else{
                            $(skuCommercialSelect.opts.commercialSelectModalId).modal('hide');
                            var value=$('div.layui-layer-shade').attr('class','');
                            $('#addSkuCommercial').attr('onclick', 'addSkuCommercialBefore()');
                        }
                        break;
                }
            }
        });
        $('select').on('change.modal', function () {
            $('#commercialIdOrName').focus();
        });
    });
    //模态框隐藏时间执行
    $(skuCommercialSelect.opts.commercialSelectModalId).on('hidden.bs.modal', function () {
        skuCommercialSelect.opts.searchCondition = '';
        $('div.layui-layer').hide();
        $('#commercialSelectModal').off('keydown.modal');
        $(window).off('keydown.modal');
        $('#addCommercialCount').html('');
    });
});

