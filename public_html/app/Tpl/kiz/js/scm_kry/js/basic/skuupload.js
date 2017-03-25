/**
 * 商品批量导入
 * @type {{opts: {urlRoot: string, commandType: string, listGridId: string, fileId: string, uploadFileUrl: string, downloadFileUrl: string, skuUploadWindowIndex: null}, _init: Function, delegateBtnUpload: Function, delegateFileUpload: Function, delegateFileDownload: Function}}
 */
var skuupload = {

    opts: {
        urlRoot: '',
        commandType: '',
        listGridId: '#grid',
        fileId: 'file',
        uploadFileUrl: '/uploadFile',
        downloadFileUrl: '/downloadFile',
        skuUploadWindowIndex: null
    },

    //初始化
    _init: function (args) {
        var _this = this;
        _this.opts = $.extend(true, this.opts, args || {});

        switch (_this.opts.commandType) {
            case 0 ://列表查询
                _this.delegateFileUpload();
                _this.delegateFileDownload();
                break;
            default :
                break;
        }
    },

    /**
     * 绑定“批量导入”按钮
     */
    delegateBtnUpload: function(){

        var _this = this;

        $('#file').val('');
        _this.opts.skuUploadWindowIndex = layer.open({
        	type: 1,
        	title: '批量商品导入',
        	area: 'auto',
        	maxWidth: 600, // area='auto'时，maxWidth才有效
        	content: $('#skuUploadDiv')
        });
       
    },

    /**
     * 绑定“确认汇入”按钮
     */
    delegateFileUpload : function(){

        $(document).delegate('#btnFileupload', 'click', function(){
            uploadFiles();
        });
    },

    /**
     * 绑定“商品批量导入模板”超链接
     */
    delegateFileDownload : function(){

        $(document).delegate('#downloadTemplate', 'click', function(){
            downloadFile();
        });
    }
};




/**
 * 文件上传
 * @returns {boolean}
 */
uploadFiles = function(){


    var fileId = skuupload.opts.fileId;

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

    closeSkuUploadWindow();

    bkeruyun.showLoading();

    $.ajaxFileUpload({
        url: skuupload.opts.urlRoot + skuupload.opts.uploadFileUrl,
        secureuri: false,
        fileElementId: skuupload.opts.fileId,//file标签的id
        type: 'post',
        data:{},
        dataType: 'text',
        success: function(result, status){

            bkeruyun.hideLoading();

            closeSkuUploadWindow();

            // 替换 ":" 和 "," 等（后台先经过encode，前台再decode，否则中文有乱码，冒号和逗号编解码无效，需另外替换）
            result = decodeURI(result)
                .replace(/\%40/g, '@')
                .replace(/\%3A/g, ':')
                .replace(/\%3B/g, '; ')
                .replace(/\%3D/g, '=')
                .replace(/\%3F/g, '?')
                .replace(/\%2C/g, ',')
                .replace(/\%24/g, '$')
                .replace(/\%23/g, '#')
                .replace(/\%26/g, '&')
                .replace(/\+/g, ' ')  // 为正确传递加号和空格到前台，后台编码前将+替换成%2B，编码后将空格替换成+
                .replace(/\%2B/g, '+')  // 相应地，前台在解码后，先把+替换成空格，再把%2B替换成+
                .replace(/\%2F/g, '/');
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
                $(skuupload.opts.listGridId).refresh();
            }
            return false;
        },
        error: function(data, status, e){
            closeSkuUploadWindow();
            alert(e);
        }
    });

};


/**
 * 提示错误消息
 * @param errors
 */
showErrorMsgs = function(errors){

    $('#errorMsgModal').modal({
        backdrop: 'static'
    });

    $('#errorMsgBody').html('');

    $.each(errors, function(index, error){
        if(error.rowNo){
            $('#errorMsgBody').append('<p><span class="red">第' + error.rowNo + '行：' + error.msg.replace(/\s/g, '&nbsp;') + '</span></p>');
        } else{
            $('#errorMsgBody').append('<p><span class="red">' + error + '</span></p>');
        }
    });

};


/**
 * 下载模板文件
 */
downloadFile = function(){

    var url = skuupload.opts.urlRoot + skuupload.opts.downloadFileUrl;

    window.open(url, '_blank');
};

/**
 * 关闭批量导入的弹出窗
 */
closeSkuUploadWindow = function(){
    var index = skuupload.opts.skuUploadWindowIndex;
    if(index){
        layer.close(index);
    }
};