/**
 * 商品批量导出
 * @type {{opts: {urlRoot: string, listGridId: string, exportUrl: string, cachedQueryConditions: string}, _init: Function, delegateBtnExport: Function, export: Function}}
 */
var skuexport = {

    opts: {
        urlRoot: '',
        queryConditionsId : 'queryConditions',
        listGridId: '#grid',
        exportUrl: '/export'
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        _this.delegateBtnExport();
    },

    /**
     * 绑定“批量导出”按钮
     */
    delegateBtnExport: function(){

        var _this = this;

        $(document).delegate('#btn-export', 'click', function(){
            _this.export();
        });
    },

    export : function(){

        var _this = this;

        var currentQueryConditions = serializeFormById(skulist.opts.queryConditionsId);

        if(currentQueryConditions != skulist.opts.cachedQueryConditions){
            $.layerMsg('条件已改变，请先点击查询按钮！', false);
            return false;
        }

        var $gridObj = $("#grid");


        var totalSize = $gridObj.jqGrid('getGridParam','records');

        if(totalSize > 0){

            var sidx = $gridObj.jqGrid('getGridParam','sortname');
            var sord = $gridObj.jqGrid('getGridParam','sortorder');

            //rows=0将获取所有记录，不分页
            var exportUrl = _this.opts.urlRoot + _this.opts.exportUrl + "?rows=0&sidx=" + sidx + "&sord=" + sord;

            $("#queryConditions").attr("action", exportUrl).attr("target", "_blank");
            $("#queryConditions").submit();
        } else{
            $.layerMsg('导出记录为空！', false);
        }
    }




};





