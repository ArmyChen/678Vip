

var openinginventory = {

    opts: {
        urlRoot: ctxPath,
        commandType: '0',
        formId: '#openingInventoryForm',
        listGridId: '#grid',
        fileId: 'file',
        queryUrl: '&act=get_master_import_log_ajax',
        uploadFileUrl: '&act=basic_master_import_ajax',
        downloadFileUrl: '/app/master.xls',
        warehouseId: '#warehouseId'
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType) {
            case 0 ://列表查询
                //_this.delegateFileUpload();
                _this.delegateFileDownload();
                _this.initQueryList();
                $(_this.opts.listGridId).refresh();
                break;
            default :
                break;
        }
    },

    //初始化查询列表grid
    initQueryList : function(){

        var _this = this;

        var $listGrid = $(_this.opts.listGridId);

        var width = $('.panel').width() - 18; // 适应父标签的宽度

        $listGrid.dataGrid({
            formId: _this.opts.queryConditionsId,
            url: _this.opts.urlRoot + _this.opts.queryUrl,
            datatype: 'local',
            showEmptyGrid: true,
            width: width,
            rowNum: 9999,
            colNames: ['操作人','仓库名称', '汇入日期'/*, '期初金额'*/],
            colModel: [
                {name: 'username', index: 'username', sortable: false},
                {name: 'warehouseName', index: 'warehouseName', sortable: false},
                {name: 'createTime', index: 'createTime', align: "center", sortable: false}
                /*,
                {
                    name: 'amount',
                    index: 'amount',
                    align: "right",
                    formatter: customCurrencyFormatter,
                    sortable: false
                }*/
            ],
            showOperate:false
        });

        $listGrid.setGridWidth(width);
    },

    //delegateFileUpload : function(){
    //
    //    $(document).delegate('#btnFileupload', 'click', function(){
    //        uploadFiles();
    //    });
    //},

    delegateFileDownload : function(){

        $(document).delegate('#downloadTemplate', 'click', function(){
            downloadFile();
        });
    }
}

/**
 * 文件上传
 * @returns {boolean}
 */
uploadFiles = function(){
    var warehouseId = $(openinginventory.opts.warehouseId).val();
    if(!warehouseId){
        $.layerMsg('请选择仓库！', false);
        return false;
    }

    var formId = openinginventory.opts.formId;
    var fileId = openinginventory.opts.fileId;

    var file = document.getElementById(fileId);
    var fileName = file.value;
    var suffix = fileName.substring(fileName.lastIndexOf('.') + 1, fileName.length);
    if(fileName === ''){
        $.layerMsg('请选择文件！', false);
        return false;
    }
    if(suffix != 'xls'){
        $.layerMsg('仅允许上传xls格式的文件，请重新上传！', false);
        return false;
    }

    var warehouseId = $(openinginventory.opts.warehouseId).val();

    bkeruyun.showLoading();

    $.ajaxFileUpload({
        url:  ctxPath + openinginventory.opts.uploadFileUrl,
        secureuri: false,
        fileElementId: openinginventory.opts.fileId,//file标签的id
        type: 'post',
        data:{warehouseId: warehouseId},
        dataType: 'text',
        success: function(result, status){
            bkeruyun.hideLoading();
            result = decodeURI(result).replace(/\%3A/g, ':').replace(/\%3B/g, '; ').replace(/\%2C/g, ','); // 替换 ":" 和 "," （后台先经过encode，前台再decode，否则中文有乱码，冒号和逗号编解码无效，需另外替换）
            if(result.indexOf('<') >= 0 && result.indexOf('>') >= 0){
                result = result.replace(/<[^>]+>/g, ''); // 解决Chrome Firefox下有<pre>...</pre>的问题
            } else if(result.indexOf(']}') >= 0){
                result = result.substring(0, result.indexOf(']}') + 2); // 解决IE下返回值后有{toFunction ... toDo ...}的问题
            }
            result = JSON.parse(result);
            //console.log(result);
            if(!result.success){
                if(result.data == null){
                    result.data = [];
                    result.data.push(result.message);
                }
                showErrorMsgs(result.data);
                return false;
            } else{
                $.layerMsg('汇入成功', true);
                $(openinginventory.opts.listGridId).refresh();
            }
            return false;
        },
        error: function(data, status, e){
            $.layerMsg("汇入失败", false);
        }
    });

};


showErrorMsgs = function(errors){

    $('#errorMsgModal').modal({
        backdrop: 'static'
    });

    $('#errorMsgBody').html('');

    $.each(errors, function(index, error){
        if(error.rowNo){
            $('#errorMsgBody').append('<p><span class="red">第' + error.rowNo + '行：' + error.msg + '</span></p>');
        } else{
            $('#errorMsgBody').append('<p><span class="red">' + error + '</span></p>');
        }
    });

}


downloadFile = function(){

    var url = openinginventory.opts.downloadFileUrl;

    window.open(url, '_blank');
}