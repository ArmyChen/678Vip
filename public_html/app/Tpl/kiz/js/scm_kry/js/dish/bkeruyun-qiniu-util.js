(function (root, undefined) {
  var bkeruyun = window.bkeruyun || window;
  var qiniuUtil = {
    _config: {
      'uptoken_url': ctxPath + '/ueditor/uptokenStr',
      // domain 必须设置
      'domain': null
    },
    //提供修改config 方法
    setConfigItem: function (key, value) {
      if ('domain' === key) {
        //domain 格式为:  http://www.baidu.com/
        Qiniu.domain = value;
      }
      this._config[key] = value;
    },
    getConfigItem: function (key) {
      return this._config[key];
    },
    showUploadTip: function (tipText) {
      this.hideUploadProgressModal();
      Message.alert({
        title: '提示',
        describe: tipText
      }, Message.display);
    },
    getFileType: function (fileName) {
      var type = '';
      var set = fileName.split('.');
      var temp = set[set.length - 1];
      type = temp.toLowerCase();
      return type;
    },
    //清空 文件队列
    clearFileQueue: function (up) {
      var fileList = up.files;
      for (var i = 0; i < fileList.length; i++) {
        up.removeFile(fileList[i].id);
      }
    },
    getModalDomStr: function (modalDomId, modalDialogStyleStr) {
      var modalSet = [];
      modalSet.push('<div class="modal fade" id="' + modalDomId + '" tabindex="-1" role="dialog" data-backdrop="static">');
      modalSet.push('    <div class="modal-dialog" role="document" ' + modalDialogStyleStr + '> ');
      modalSet.push('        <div class="modal-content" >');
      modalSet.push('            <div class="modal-header">');
      modalSet.push('                <h2 class="modal-title">');
      modalSet.push('                    <strong>&nbsp;</strong>');
      modalSet.push('                </h2>');
      modalSet.push('            </div>');
      modalSet.push('            <div class="modal-body">');
      modalSet.push('            </div>');
      modalSet.push('        </div>');
      modalSet.push('    </div>');
      modalSet.push('</div>');
      return modalSet.join('');
    },
    getProgressStr: function (percent) {
      var htmlSet = [];
      htmlSet.push('<div class="progress-tip">');
      htmlSet.push('</div>');
      htmlSet.push('<div class="progress">');
      htmlSet.push(' <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"');
      htmlSet.push('  aria-valuemin="0" aria-valuemax="100" ');
      htmlSet.push('  aria-valuenow="' + percent + '" style="width: ' + percent + '%" >');
      htmlSet.push('   <span class="sr-only"></span>');
      htmlSet.push(' </div>');
      htmlSet.push('</div>');
      return htmlSet.join('');
    },
    // 设置进度条状态
    setProgress: function (percent, tipMsg) {
      var $progress = this._$uploadProgressModal.find('.progress');
      if ($progress.length === 0) {
        this._$uploadProgressModal.find('.upload-progress-container').append(this.getProgressStr(percent))
      } else {
        $progress.find('.progress-bar').attr('aria-valuenow', percent).css('width', percent + '%');
      }
      if (tipMsg) {
        this._$uploadProgressModal.find('.progress-tip').text(tipMsg);
      }
    },
    // 显示 上传进度条对话框
    showUploadProgressModal: function (callback) {
      var $uploadProgressModal = $('#uploadProgressModal');
      if ($('#uploadProgressModal').length === 0) {
        var html = this.getModalDomStr('uploadProgressModal', 'style="width: 300px;"');
        $uploadProgressModal = $(html);
        $uploadProgressModal.appendTo($('body'));
        this._$uploadProgressModal = $uploadProgressModal;
        $uploadProgressModal.find('.modal-body:first').append('<div class="upload-progress-container"></div>')
      }
      $uploadProgressModal.modal('show');
      $uploadProgressModal.find('.upload-progress-container').empty();
      //console.log('....showUploadProgressModal');
      if(typeof callback === 'function'){
        callback();
      }
    },
    // 隐藏 上传进度条对话框
    hideUploadProgressModal: function (callback) {
      var _this = this;
      setTimeout(function () {
        try {
          _this._$uploadProgressModal.modal('hide');
          if(typeof callback === 'function'){
            callback();
          }
        } catch (e) {
        }
      },1000);
    },
    // key 转换为 七牛地址
    keyToQiniuUrl: function (key) {
      return this.getConfigItem('domain')+ key ;
    },
    // 七牛地址 转换为 key
    qiniuUrlTokey: function (httpUrl) {
      return httpUrl.replace(this.getConfigItem('domain'), '');
    }
  };
  /**
   * 图片处理部分
   * 使用前提 需要先设置 domain属性
   *  例如: qiniuUtil.setConfigItem('domain',$('#domain').val());
   */
  qiniuUtil.photoshop = {
    compressImage2: function (key, width, height, beforeCallback) {
      var option = {
        mode: 2,  // 缩略模式，共6种[0-5]
        w: width,   // 具体含义由缩略模式决定
        h: height,   // 具体含义由缩略模式决定
        q: 100,   // 新图的图像质量，取值范围：1-100
        format: 'jpg'  // 新图的输出格式，取值范围：jpg，gif，png，webp等
      };
      if (typeof beforeCallback === 'function') {
        beforeCallback(option);
      }
      var imgLink = Qiniu.imageView2(option, key);
      //console.info(imgLink);
      return imgLink;
    },
    /**
     * 按照指定宽高,压缩图片
     * @param key
     * @param width
     * @param height
     * @param beforeCallback
     * @returns {String}
     */
    compressImage: function (key, width, height, beforeCallback) {
      /**
       * /thumbnail/<Width>x<Height>!
       * 限定目标图片宽高值，忽略原图宽高比例，按照指定宽高值强行缩略，可能导致目标图片变形。
       * 取值范围不限，但若宽高超过10000时只能缩不能放。
       */
      var thumbnail = width + 'x' + height + '!';
      var option = {
        'auto-orient': true,  // 布尔值，是否根据原图EXIF信息自动旋正，便于后续处理，建议放在首位。
        thumbnail: thumbnail,
        strip: true,   // 布尔值，是否去除图片中的元信息
        quality: 90,  // 图片质量，取值范围1-100
        format: 'jpg'// 新图的输出格式，取值范围：jpg，gif，png，webp等
      };
      if (typeof beforeCallback === 'function') {
        beforeCallback(option);
      }
      var imgLink = Qiniu.imageMogr2(option, key);
      //console.info(imgLink);
      return imgLink;
    },
    /**
     * 裁剪图片
     * @param key
     * @param cropObj (包括 w-宽带,h-高度,x-水平坐标,y-垂直坐标 参数)
     * @param width
     * @param height
     * @param beforeCallback
     * @returns {String}
     */
    clipImage: function (key, cropObj, width, height, beforeCallback) {

      /**
       * /thumbnail/<Width>x<Height>!
       * 限定目标图片宽高值，忽略原图宽高比例，按照指定宽高值强行缩略，可能导致目标图片变形。
       * 取值范围不限，但若宽高超过10000时只能缩不能放。
       */
      var thumbnail = width + 'x' + height + '!';
      var option = {
        'auto-orient': true,  // 布尔值，是否根据原图EXIF信息自动旋正，便于后续处理，建议放在首位。
        strip: true,   // 布尔值，是否去除图片中的元信息
        gravity: 'NorthWest',    // 裁剪锚点参数
        quality: 90,  // 图片质量，取值范围1-100
      };
      if(cropObj){
        //cropStr 格式如下 '!300x400a10a10'
        var cropStr = '!' + cropObj.w + 'x' + cropObj.h + 'a' + cropObj.x + 'a' + cropObj.y;
        option.crop = cropStr;
      }
      if (typeof beforeCallback === 'function') {
        beforeCallback(option);
      }
      var imgLink = Qiniu.imageMogr2(option, key);
      if(width && height){
        /**
         * thumbnail 不能加在 option.thumbnail,这样生成的图片,无法达到预期效果
         */
        imgLink = imgLink + '/thumbnail/' + thumbnail;
      }

      //console.info(imgLink);
      return imgLink;
    }
  };
  /**
   *
   * @param browseButtonId 触发浏览器选择文件 domId
   * @param containerId 文件上传 所在容器 domId (最后会将该dom 的style position 设置为 relative )
   * @param option
   * @constructor
   */
  qiniuUtil.LocalUpLoader = function (browseButtonId, containerId, option) {
    option.browse_button = browseButtonId;
    option.container = containerId;
    this.config = {
      option: option
    }
  };
  qiniuUtil.LocalUpLoader.prototype = {
    /**
     *
     * @param option {uptoken,domain, ...}
     * @return object
     */
    getDefaultOption: function (option) {
      var _qiniuUtil = qiniuUtil;
      var _this = this;
      var defaultOption = {
        runtimes: 'html5,flash,html4',
        // 必填
        //browse_button: 'pickVedioFiles',
        // 必填
        //container: 'video-container',
        max_file_size: '100mb',
        flash_swf_url: ctxPath + '/js/qiniu/plupload/Moxie.swf',
        uptoken_url: qiniuUtil._config.uptoken_url,
        domain: qiniuUtil._config.domain,
        get_new_uptoken: false,
        dragdrop: false,
        drop_element: 'video-container',
        chunk_size: '4mb',
        auto_start: true,
        log_level: 4,
        unique_names: false, save_key: false,
        init: {
          'FilesAdded': function (up, files) {
            //console.log('FilesAdded')
          },
          'BeforeUpload': function (up, file) {
            //console.log('BeforeUpload');
            _qiniuUtil.showUploadProgressModal();
            _this.handleBeforeUpload(up, file);
          },
          'UploadProgress': function (up, file) {
            //console.log('UploadProgress');
            var info = '';
            var size = plupload.formatSize(file.loaded).toUpperCase();
            var formatSpeed = plupload.formatSize(file.speed).toUpperCase();
            info = '已上传: ' + size + ' 上传速度： ' + formatSpeed + '/s';
            _qiniuUtil.setProgress(file.percent, info);
          },
          'UploadComplete': function (up, file) {
            //console.log('UploadComplete');
            _this.handleUploadComplete();
          },
          'FileUploaded': function (up, file, info) {
            //console.log('FileUploaded');
            _qiniuUtil.setProgress(file.percent, '上传已完成');
            var infoObj = JSON.parse(info);
            _qiniuUtil.hideUploadProgressModal(function(){
              _this.handleFileUploaded(up, file, infoObj);
            });
          },
          'Key': function (up, file) {
            var set = file.name.split('.');
            var temp = set[set.length - 1];
            var key = file.id + '.' + temp.toLowerCase();
            return key;
          },
          'Error': function (up, err, errTip) {
            if(-601 == err.code){
              if(up.settings.filters.mime_types.length>0){
                var extensions = up.settings.filters.mime_types[0].extensions;
                errTip = '上传文件仅支持后缀名:'+extensions
              }
            }
            _qiniuUtil.showUploadTip(errTip);
            _qiniuUtil.clearFileQueue(up);
            up.stop();
          }
        }
      };
      for (var item in option) {
        defaultOption[item] = option[item];
      }
      return defaultOption;
    },
    /**
     *
     * @param option
     */
    init: function () {
      var option = this.config.option;
      var Qiniu = new QiniuJsSDK();
      var theOption = $.extend(true, {}, this.getDefaultOption(), option);
      var uploader = Qiniu.uploader(theOption);
      this._uploader = uploader;
      this._isInited = true;
    },
    // 处理 FileUploaded 回调
    handleFileUploaded: function (up, file, infoObj) {
      //console.log('api: fileUploaded');
    },
    // 处理 beforeUpload 回调
    handleBeforeUpload: function (up, file) {
      //console.log('api: beforeUpload')
    },
    // 处理
    handleUploadComplete: function () {
      //console.log('api: uploadComplete')
    }
  };
  root.qiniuUtil = qiniuUtil;
})(window);