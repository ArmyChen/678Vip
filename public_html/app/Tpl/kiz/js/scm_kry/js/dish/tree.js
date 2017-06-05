/**
 * @fileOverview: tree.js 树结构 通过传递参数可折叠 复选框 分类全选功能
 * @author: tianxiaoyun 
 * @contact: email misstian2008@163.com || tianxy@shishike.com
 * @version: 1.0
 * @external: [jquery-1.10.2.min.js]
 */
;(function($, window, document,undefined) {
    // 参数详解
    // {
    //         'folding': true,//是否折叠
    //         'foldingType': 'more',//single：一个打开其他关闭；more:可以独自打开关闭，不关闭其他
    //         'foldingSwitch':'.icon-folding',//操作折叠的按钮的class
    //         'marquee':true,//是否带选框操作
    //         'marqueeType':'radio'//radio:单选操作;checkbox:复选操作

    //     }
    //定义tree的构造函数
    var Tree = function(eleId, opt) {
        this.$elementId = $(eleId).attr("id"),
        this.defaults = {
            'folding': true,
            'foldingType': 'more',
            'foldingSwitch':'.icon-folding',
            'marquee':true,
            'marqueeType':'radio'

        },
        this.options = $.extend({}, this.defaults, opt)
    };
    //定义tree的方法
    Tree.prototype = {
        init: function() {
            var that = this;
            if(that.options.folding){
                that.folding();
            }
            if (that.options.marquee) {
                that.marquee();

}
            return this;
        },
        /**
         * 折叠
         */
        folding:function(){
            if(this.options.foldingType === 'single'){
                this.foldingSingle();
            }else{
                this.foldingMore();
            }
        },
        /**
         * 同级的只展示一个，打开一个其他关闭
         */
        foldingSingle:function(){
            var that = this;
            $(document).delegate('#'+that.$elementId+' '+that.options.foldingSwitch,'click',function(){
                var liObj = $(this).parent("li");
                liObj.addClass("open").siblings().removeClass("open");
            });
        },
        /**
         * 一个分类可以打开或关闭，不影响其他的
         */
        foldingMore:function(){
            var that = this;
            $(document).delegate('#'+that.$elementId+' '+that.options.foldingSwitch,'click',function(){
                var liObj = $(this).parent("li");
                if(liObj.is(".open")){
                    liObj.removeClass("open");
                }else{
                    liObj.addClass("open");
                }
            });
        },
        /**
         * 设置分类显示区域最小高度
         */
        minHeight:function(){

        },
        /**
         * 设置分类显示区域最大高度
         */
        maxHeight:function(){

        },
        /**
         * 是否带选框
         */
        marquee:function(){
            if(this.options.marqueeType === 'radio'){
                this.marqueeRadio();
            }else{
                this.marqueeCheckbox();
            }  
        },
        /**
         * 按单选方式操作
         */
        marqueeRadio:function(){
            var that = this;
            $(document).delegate('#'+that.$elementId+' :checkbox:not(":disabled")','change',function(){
                var currentName = $(this).attr("name");
                var checkboxObjs = $('#'+that.$elementId+' :checkbox[name='+currentName+']:not(":disabled")');
                if(this.checked){
                    checkboxObjs.removeAttr('checked').parent().removeClass('checkbox-check');
                    $(this).attr('checked','checked').parent().addClass('checkbox-check');
                }else{
                    $(this).attr('checked','checked').parent().addClass('checkbox-check');
                }
                
            });
        },
        /**
         * 多选操作 全选 分类全选
         */
        marqueeCheckbox:function(){
            var that = this;
            //全选
            //关联全选

            $(document).delegate('#'+that.$elementId+' :checkbox','change',function(){
                checkFn(this);
                checkAllFn($("#checkedAll"),$(':checkbox[name="goods"]:not(":disabled")'));
            });
            function checkFn(checkboxObj){
                var liObj = $(checkboxObj).parent().parent("li");//当前li
                var childrenObj = liObj.children("ul");//包含子元素列表
                var parentObj = liObj.parent().parent();
                var siblingsObj = liObj.siblings();
                
                if(checkboxObj.checked){
                    //如果包含子元素 进行全选操作
                    if(childrenObj.length > 0){
                        var childrenObjLis = childrenObj.eq(0).find("li");
                        
                        childrenObjLis.find(":checkbox:not(':disabled')").each(function(){
                            // var checkObj = $(this).find(".checkbox > :checkbox:not(':disabled')");
                            if(!this.checked){
                                // alert(this.checked);
                                this.checked = true;
                                $(this).parent().addClass("checkbox-check");
                            }
                        });

                    }
                    //如果有父元素 关联操作
                    if(parentObj && parentObj.is('li')){
                        var flag = true;
                        siblingsObj.each(function(){
                            var checkboxObj = $(this).children(".checkbox").children(":checkbox").get(0);
                            if(!checkboxObj.disabled && !checkboxObj.checked){
                                flag = false;
                            }
                        });
                        parentObj.children(".checkbox").children(":checkbox").get(0).checked = flag;
                        if(flag){
                             parentObj.children(".checkbox").addClass("checkbox-check");
                        }else{
                            parentObj.children(".checkbox").removeClass("checkbox-check");
                        }
                    }
                    
                }else{
                    //如果包含子元素 进行全选操作
                    if(childrenObj.length > 0){
                        var childrenObjLis = childrenObj.eq(0).find("li");
                        // var flag = true;
                        childrenObjLis.find(":checkbox:not(':disabled')").each(function(){
                            if(this.checked){
                                this.checked = false;
                                $(this).parent().removeClass("checkbox-check");
                            }
                        });

                    }
                    //如果有父元素 关联操作
                    if(parentObj && parentObj.is('li')){
                        parentObj.children(".checkbox").children(":checkbox").get(0).checked = false;
                        parentObj.children(".checkbox").removeClass("checkbox-check");
                        // checkFn(parentObj.get(0));
                    }
                }
            }
            //全选
            function checkAllFn(allObj,groupCheckboxs){
                var flag = true;
                for(var i=0,len=groupCheckboxs.length;i<len;i++){
                    if(!groupCheckboxs.get(i).checked){
                        flag = false;
                    }
                }
                // alert(flag);
                allObj.get(0).checked = flag;
                if(flag){
                    allObj.parent(".checkbox").addClass("checkbox-check");
                }else{
                    allObj.parent(".checkbox").removeClass("checkbox-check");
                }
            }
        }


    };
    //在插件中使用Tree对象
    $.fn.tree = function(options) {
        //创建Tree的实体
        var tree = new Tree(this, options);
        //调用其方法
        return tree.init();
    }
})(jQuery, window, document);