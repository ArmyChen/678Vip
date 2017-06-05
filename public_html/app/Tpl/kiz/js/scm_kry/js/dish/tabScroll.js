/**
 * @fileOverview: tabScroll.js 标签栏，点击左右按键滚动，支持一次滚动1~n个标签
 * @author: tianxiaoyun 
 * @contact: email misstian2008@163.com || tianxy@shishike.com
 * @version: 1.0
 * @external: [jquery-1.10.2.min.js]
 */
;(function($, window, document,undefined) {
    //定义TabScroll的构造函数
    var TabScroll = function(ele, opt) {
        this.$element = ele,
        this.defaults = {
            'wrapObj': $('#customer-tab'),//外层div
            'innerObj': $('#customer-tab > ul'),//被滚动的元素
            'innerObjLeft':0,//被滚动元素距离外层div左边距
            'item':$('#customer-tab > ul > li'),//单个元素
            'itemN': 8,//默认显示几个元素
            'itemMarginL':0,//
            'itemMarginR':0,
            'itemPaddingL':0,
            'itemPaddingR':0,
            'moveItemN':1,
            'speed':500
        },
        this.options = $.extend({}, this.defaults, opt)
    };
    //定义TabScroll的方法
    TabScroll.prototype = {
    	init:function(){
    		//创建基础布局
    		this._creatLayout();
    		this.moveTabs();
    	},
    	/**
    	 * 构建布局
    	 */
        _creatLayout: function() {
        	var _this = this;
        	
            var wrapW = _this.options.wrapObj.outerWidth();
            var wrapH = _this.options.wrapObj.outerHeight();
            var itemW = Math.ceil((wrapW*1-44 - (_this.options.itemMarginL+_this.options.itemMarginR)*(_this.options.itemN-1))/_this.options.itemN);
            var itemL = _this.options.item.length;
            var nextBtn = (itemL > _this.options.itemN ) ? '<span class="next"></span>' : '<span class="next" data-status="disabled"></span>';
        	var prevBtn = '<span class="prev" data-status="disabled"></span>';

            _this.options.item.each(function(){
                var emObj = $(this).find("em");
                var txt = emObj.text();
                emObj.text(_this._ellipsis(txt,3));
            });
            // console.log("wrapW=="+wrapW+" wrapH=="+wrapH + " itemW=="+itemW +"=="+_this.options.innerObjLeft);
            if(_this.options.item.length > _this.options.itemN){
                _this.options.wrapObj.append(nextBtn).append(prevBtn).addClass("tabScroll-wrap").css({'width':wrapW+'px','height':wrapH+'px'});
                _this.options.innerObj.addClass("tabScroll-inner").css({"left":_this.options.innerObjLeft+'px'});
                _this.options.item.width((itemW-_this.options.itemPaddingL-_this.options.itemPaddingR)+'px');
            }
            // console.log((itemW-_this.options.itemPaddingL-_this.options.itemPaddingR));
        },
        /**
         * 超过3个字返回3个字+...
         * @txt {string}
         * @len {number}
         */
        _ellipsis:function(txt,len){
            var newTxt = (txt.length > len) ? txt.substring(0,len)+'...' : txt;
            return newTxt;
        },
        moveTabs:function(){
        	var _this = this;
        	var moveObj = _this.options.innerObj,
				nextBtn = _this.options.wrapObj.find(".next"),
			    prevBtn = _this.options.wrapObj.find(".prev"),
			    wrapW = _this.options.wrapObj.outerWidth(),
			    itemW = Math.ceil((wrapW*1-44 - (_this.options.itemMarginL+_this.options.itemMarginR)*(_this.options.itemN-1))/_this.options.itemN),
			    posWidth = (itemW + _this.options.itemMarginL+_this.options.itemMarginR)*_this.options.moveItemN,
				len = Math.ceil(_this.options.item.length/_this.options.moveItemN),//总个数/每次滚动个
				index = 0;
				// console.log("posWidth=="+posWidth+"== "+_this.options.itemMarginL+"=="+_this.options.itemMarginR);
				
			//下一个
			nextBtn.bind("click",function(){
				if(index < len-1){
					posMove(moveObj,posWidth,-1);
					prevBtn.removeAttr("data-status");
					if(index == len-1){
						nextBtn.attr("data-status","disabled");
					}
				}
			});
			//上一个
			prevBtn.bind("click",function(){
				if(index > 0){
					posMove(moveObj,posWidth,1);
					nextBtn.removeAttr("data-status");
					if(index == 0){
						prevBtn.attr("data-status","disabled");
						
					}
				}
			});
			function posMove(obj,pw,step){
				if(!$(obj).is(":animated")){
		//			console.log("--" + index + "-pw-" + pw);
					var L = parseInt($(obj).css("left"));
					L += pw*step;
					index -= step;
					//console.log("++" + index + "w===" + L);
					$(obj).animate({left:L + "px"},_this.options.speed);
				}
			}
        }


    };
    //在插件中使用TabScroll对象
    $.fn.tabScroll = function(options) {
        //创建TabScroll的实体
        var tabScroll = new TabScroll(this, options);
        //调用其方法
        return tabScroll.init();
    }
})(jQuery, window, document);