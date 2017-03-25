/**
 * 提示信息组件
 * Created by zhulf on 2016/3/15.
 */
(function($){
    $(function () {
        if($('.tooltip-box').length == 0){
            $('body').append($('<div class="tooltip-box"></div>'));
            $('body').append($('<span class="tooltip-triangle"></span>'));
        }

        $('body').on('mouseover mouseout', '.iconfont.question', function (e) {
            var $box = $('.tooltip-box');
            var $triangle = $('.tooltip-triangle');
            var $this = $(this);
            if(e.type == 'mouseover'){
                $box.html($this.data('content'));
                var offset = $this.offset();
                $box.show();
                $triangle.show();
                $box.css({
                    top: offset.top - $box.outerHeight() - 8,
                    left: offset.left
                });
                $triangle.css({
                    top: offset.top,
                    left: offset.left
                });
                $this.removeClass("color-g").addClass("color-b");

                //显示框达到边界线时，向左移动
                if((document.body.clientWidth-$this.offset().left) < 121){
                    $this.data('offset','left');
                }else{
                    $this.data('offset','');
                }

                if($this.data('offset') == 'left'){
                    $box.css('margin-left','-' + ($box.width()-5) + 'px');
                }else{
                    $box.css('margin-left','-' + ($box.width()/2) + 'px');
                    $triangle.css('margin-left',($this.width()/2)-8 + 'px');
                }
                if($this.data('offset') == 'down'){
                    $box.css({
                        top: offset.top + $this.outerHeight() + 8,
                        left: offset.left
                    });
                    $triangle.css({
                        top: offset.top + $this.outerHeight(),
                        left: offset.left
                    });
                }else{
                    $triangle.css('margin-top', '-16px');
                }
            }else if(e.type == 'mouseout'){
                $box.hide().html('');
                $triangle.hide();
                $('body').append($box);
                $('body').append($triangle);
                $box.css('margin-left','-100px');
                $this.removeClass("color-b").addClass("color-g");
            }
        });
    });
})(jQuery);
