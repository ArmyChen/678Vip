/**
 * 商品批量导出
 * @type {{opts: {urlRoot: string, listGridId: string, exportUrl: string, cachedQueryConditions: string}, _init: Function, delegateBtnExport: Function, export: Function}}
 */
var dishmanageexport = {

    opts: {
        urlRoot: ctx2Path,
        queryConditionsId : 'queryForm',
        cachedQueryConditions : '',
        cachedDishTypeId : '',
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

        var currentQueryConditions = serializeFormById(dishmanageexport.opts.queryConditionsId);
        if(currentQueryConditions != dishmanageexport.opts.cachedQueryConditions){
            $.layerMsg('条件已改变，请先点击查询按钮！', false);
            return false;
        }
        var currentDishTypeId = $("#input").attr("data-id");
        if(currentDishTypeId != dishmanageexport.opts.cachedDishTypeId){
            $.layerMsg('条件已改变，请先点击查询按钮！', false);
            return false;
        }
        var $gridObj = $("#grid");


        var totalSize = $gridObj.jqGrid('getGridParam','records');

        if(totalSize > 0){

            var sidx = $gridObj.jqGrid('getGridParam','sortname');
            var sord = $gridObj.jqGrid('getGridParam','sortorder');
            var dishTypeId = $("#input").attr("data-id"),
                typeOrInClass = $("#input").attr("data-type");

            //rows=0将获取所有记录，不分页
            var exportUrl = _this.opts.urlRoot + _this.opts.exportUrl + "?rows=0&sidx=" + sidx + "&sord=" + sord ;
            if(dishTypeId !== null && dishTypeId !== "" && dishTypeId !== undefined) {
                exportUrl = exportUrl + "&dishTypeId=" + dishTypeId;
            }
            if(typeOrInClass !== null && typeOrInClass!== "" && typeOrInClass !== undefined) {
                exportUrl = exportUrl + "&typeOrInClass=" + typeOrInClass;
            }


            $("#queryForm").attr("action", exportUrl).attr("target", "_blank");
            $("#queryForm").submit();
        } else{
            $.layerMsg('导出记录为空！', false);
        }
    }




};





