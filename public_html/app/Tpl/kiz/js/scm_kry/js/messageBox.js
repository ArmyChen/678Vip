/*
(function ($) {
    $.fn.extend({
        //pass the options variable to the function
        confirmModal: function (options) {
            var html = '<div class="modal" id="confirmContainer"><div class="modal-header"><a class="close" data-dismiss="modal">×</a>' +
            '<h3>#Heading#</h3></div><div class="modal-body">' +
            '#Body#</div><div class="modal-footer">' +
            '<a href="#" class="btn btn-primary" id="confirmYesBtn">Confirm</a>' +
            '<a href="#" class="btn" data-dismiss="modal">Close</a></div></div>';
 
            var defaults = {
                heading: 'Please confirm',
                body:'Body contents',
                callback : null
            };
 
            var options = $.extend(defaults, options);
            html = html.replace('#Heading#',options.heading).replace('#Body#',options.body);
            $(this).html(html);
            $(this).modal('show');
            var context = $(this);
            $('#confirmYesBtn',this).click(function(){
                if(options.callback!=null)
                    options.callback();
                $(context).modal('hide');
            });
			alert("暂不执行");
        }
    });
 
})(jQuery);
*/
var Message={
	confirm: function(message,sureFunction,cancelFunction){
			this.sureFunction=sureFunction||function(){};
			this.cancelFunction=cancelFunction||function(){};
			 
			var msgObj = '<div id="msgbox" class="panel-popover" style="width:310px;margin-left:-155px;margin-top:-140px;display:none;">';
				msgObj +='	<div class="panel-popover-heading">';
				msgObj +='    	<div class="panel-popover-title">' + message.title + '</div>';
				msgObj +='    </div>';
				msgObj +='	<div class="panel-popover-body" >';
				msgObj +='        <p class="text-center">'+message.describe+'</p>';
				msgObj +='    </div>';
				msgObj +='    <div class="panel-popover-footer">';
				msgObj +='    	<div class="btn-wrap text-center"><a class="btn-blue btn-suspended mr10" onclick="Message.sure()" role="button">确 定</a><a class="btn-blue"  onclick="Message.cancel()" role="button">取 消</a></div>';
				msgObj +='    </div>';
				msgObj +='</div>';
			//添加至body
			$(document.body).append(msgObj);
			//显示遮罩层
			bkeruyun.showLayer();
			//显示提示框
			$("#msgbox").show();
			},
	alert: function(message,sureFunction){
		   this.sureFunction=sureFunction||function(){};
		   var msgObj = '<div id="msgbox" class="panel-popover" style="width:310px;margin-left:-155px;margin-top:-140px;display:none;">';
				msgObj +='	<div class="panel-popover-heading">';
				msgObj +='    	<div class="panel-popover-title">' + message.title + '</div>';
				msgObj +='    </div>';
				msgObj +='	<div class="panel-popover-body" >';
				msgObj +='        <p class="text-center">'+message.describe+'</p>';
				msgObj +='    </div>';
				msgObj +='    <div class="panel-popover-footer">';
				msgObj +='    	<div class="btn-wrap text-center"><a class="btn-blue btn-suspended mr10" onclick="Message.sure()" role="button">确 定</a></div>';
				msgObj +='    </div>';
				msgObj +='</div>';
			//添加至body
			$(document.body).append(msgObj);
			//显示遮罩层
			bkeruyun.showLayer();
			//显示提示框
			$("#msgbox").show();
		   },
	sure: function(){
				//移除div
				this.display();
				this.sureFunction();
			},
	cancel: function(){
				//移除div
				this.display();
				this.cancelFunction();
			},
	display: function(){
			bkeruyun.hideLayer();
				$("#msgbox").remove();
	        }
	
};