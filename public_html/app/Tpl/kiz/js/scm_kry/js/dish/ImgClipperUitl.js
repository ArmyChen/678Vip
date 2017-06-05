/**
 * 用法:
 */
/**
 *
 * @param jcropConfig
 * @param option
 * @param $modal
 * @returns {ImgClipper}
 * @constructor
 */

function ImgClipper(jcropConfig,option, $modal){

    this.jcropConfig = jcropConfig;
    this.$modal = $modal;
    this.$sourceFile = $modal.find('.source-file-container:first img');
    this.isInit = false;

    this.selectObj = null;
    this.afterDoneCallback = function(){

    };
    this.option = $.extend(true, {
        image:{
            //理想的宽度,高度,缩放比例
            idealWidth:960,
            idealHeight:720,
            scalingRatio:0.3

        }
    },option);
    this.init();
    return this;
}
ImgClipper.prototype = {
    /**
     *
     * @param modalDomId
     * @param defaultImageUrl
     * @returns {string}
     */
    getModalDomStr: function (modalDomId,defaultImageUrl){
        var modalSet=[];
        modalSet.push('<div class="modal fade clipping-modal" id="'+modalDomId+'" tabindex="-1" role="dialog" >');
        modalSet.push('    <div class="modal-dialog modal-lg" role="document">');
        modalSet.push('        <div class="modal-content">');
        modalSet.push('            <div class="modal-header">');
        modalSet.push('                <button type="button" class="close" data-dismiss="modal" aria-label="Close">');
        modalSet.push('                        <span aria-hidden="true">&times;</span></button>');
        modalSet.push('                <h2 class="modal-title">');
        modalSet.push('                    <strong>图片裁剪</strong>:(上为效果预览图, 下为原图)');
        modalSet.push('                </h2>');
        modalSet.push('            </div>');
        modalSet.push('            <div class="modal-body">');
        modalSet.push('                <div class="img-clipper-container">');
        modalSet.push('                    <div class="clipping-preview-container">');
        modalSet.push('                        <div class="preview-pane">');
        modalSet.push('                            <div class="preview-container">');
        modalSet.push('                                <img src="'+defaultImageUrl+'" class="jcrop-preview" alt="Preview" />');
        modalSet.push('                            </div>');
        modalSet.push('                        </div>');
        modalSet.push('                    </div>');
        modalSet.push('                    <div class="source-file-container">');
        modalSet.push('                        <img src="'+defaultImageUrl+'" alt="">');
        modalSet.push('                    </div>');
        modalSet.push('                </div>');
        modalSet.push('            </div>');
        modalSet.push('            <div class="modal-footer">');
        modalSet.push('                <button type="button" class="btn btn-default btn-cancle" data-dismiss="modal">取消</button>');
        modalSet.push('                <button type="button" class="btn btn-primary">执行裁剪</button>');
        modalSet.push('            </div>');
        modalSet.push('        </div>');
        modalSet.push('    </div>');
        modalSet.push('</div>');
        return modalSet.join('');

    },
    init : function (){
        var _this = this;
        if(!this.isInit){
            this.isInit = true;
            this.$modal.find('.btn-primary').on('click',function(){
                if(_this.$target){
                    if(_this.selectObj){
                    _this.$target.attr('data-selectObj',JSON.stringify(_this.selectObj));
                    }else{
                        _this.$target.removeAttr('data-selectObj');
                    }

                    // 将裁剪预览效果 放到 $targetPreview
                    _this.afterDoneCallback(_this);
                    _this.$modal.modal('hide');
                }else{
                    // no $target
                    _this.$modal.modal('hide');
                }
            });

            this.$sourceFile.load(function(){
                console.log(' this.$sourceFile.load rendClipper()');
                _this.rendClipper();
            });
        }

    },
    rendClipper : function (){
        var _this = this;
        _this.destroyClipper();
        // Create variables (in this scope) to hold the API and image size
        var jcrop_api,
            boundx,
            boundy,

        // Grab some information about the preview pane
            $preview = _this.$modal.find('.preview-pane'),
            $pcnt = _this.$modal.find('.preview-pane .preview-container'),
            $pimg = _this.$modal.find('.preview-pane .preview-container img');

        this.$preview = $preview;
        this.$pcnt = $pcnt;
        this.$pimg = $pimg;

        $pcnt.css({
            width: (_this.option.image.idealWidth*_this.option.image.scalingRatio)+'px',
            height: (_this.option.image.idealHeight*_this.option.image.scalingRatio)+'px',
            overflow: 'hidden'
        });
        var xsize = $pcnt.width(),ysize = $pcnt.height();

        var jcropConfig= $.extend(true,{
            onChange: updatePreview,
            onSelect: updatePreview,
            onRelease: onRelease,
            aspectRatio: _this.option.image.idealWidth / _this.option.image.idealHeight
        },this.jcropConfig);

        var isRelease = false;
        //设置之前的选择区域
        if(this.$target){
            var selectObjStr = this.$target.attr('data-selectObj');
            if(selectObjStr){
                jcropConfig.setSelect = JSON.parse(selectObjStr);
            }else{
                isRelease = true;
            }
        }

        this.$sourceFile.Jcrop(jcropConfig,function(){
            // Use the API to get the real image size
            var bounds = this.getBounds();
            boundx = bounds[0];
            boundy = bounds[1];
            // Store the API in the jcrop_api variable
            jcrop_api = this;

            // Move the preview into the jcrop container for css positioning
            //$preview.appendTo(jcrop_api.ui.holder);
            _this.jcrop_api = jcrop_api;
        });

        if(isRelease){
            jcrop_api.release();
        }

        function updatePreview(c){
            if (parseInt(c.w) > 0)
            {
                var rx = xsize / c.w;
                var ry = ysize / c.h;
                _this.setSelectObj(c);
                $pimg.css({
                    width: Math.round(rx * boundx) + 'px',
                    height: Math.round(ry * boundy) + 'px',
                    marginLeft: '-' + Math.round(rx * c.x) + 'px',
                    marginTop: '-' + Math.round(ry * c.y) + 'px'
                });
            }
        }
        function onRelease (c){
            _this.setSelectObj(null);
            $pimg.css({
                width:'100%',
                height:'100%',
                marginLeft: '0px',
                marginTop: '0px'
            });
        }

        return this;
    },
    destroyClipper:function(){
        var $modal = this.$modal;
        if(this.jcrop_api && typeof this.jcrop_api.destroy === 'function' ){
            this.$preview.appendTo($modal.find('.clipping-preview-container'));
            $modal.find('.clipping-preview-container').css('height', this.option.image.idealHeight*this.option.image.scalingRatio+30);
            $modal.find('.img-clipper-container').css('height', this.option.image.idealHeight*this.option.image.scalingRatio+30);
            this.jcrop_api.destroy();
            console.log('this.jcrop_api.destroy(); ')
        }
    },

    setSelectObj: function(selectObj){
        this.selectObj  = selectObj;
    },
    setModalCancleBntStates: function(isShow){
        if(isShow){
            this.$modal.find('.btn-cancle').removeClass('hidden');
            this.$modal.find('.close').removeClass('hidden');
        }else{
            this.$modal.find('.btn-cancle').addClass('hidden');
            this.$modal.find('.close').addClass('hidden');
        }
    },
    open : function (imgSrc,$target, afterDoneCallback){
        var _this = this;
        _this.afterDoneCallback = afterDoneCallback;
        var $modal = _this.$modal;
        $modal.modal({
            backdrop: 'static',
            keyboard: false
        });

        if(this.$pimg){
            this.$pimg.attr('src',imgSrc);
        }
        this.$target = $target;
        this.$sourceFile.attr('src',imgSrc);
        imgClipper.$sourceFile.removeAttr('style');
        return this;
    }
};


