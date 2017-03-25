/**
 * 商品删除/停用前端-提示框
 * $.message.showDialog(opt)：显示弹出框
 * 需要依赖 template.min.js
 *
 * opt: 参数格式{}
 * 输入:{
 *  confirm : true (确认取消 默认为true)  false (确认)
 *  hint : ''      (提示信息)
 *  dataHint : ''  (详情提示信息)
 *  dataList : []  (详情数据)
 *  callBack : function (回调函数)
 * }
 *
 * create by zhulf 2016-03-30
 **/
(function($){
        //命名空间
        $.message = {};
        var _this = $.message;

        var defaultOpt = {
            confirm : true,
            hint : '', //提示信息
            dataHint : '', //详情提示信息
            dataList : [], //详情数据
            callBack : undefined, //回调函数
            callBackArgs : {}, //回调函数参数
            callCancelBack : undefined,//取消的回调函数
            callCancelBackArgs : {}, //取消的回调函数参数
            showDetail : false, //显示详细信息
            showTemplates : false, //显示模版
            showBills : false //显示单据
        };

        //显示打印框
        _this.showDialog = function(opt){
            var _opt = $.extend({},defaultOpt,opt);

            if(_opt.dataList && _opt.dataList.length > 0){
                _opt.showDetail = true;
                var keys = Object.keys(_opt.dataList[0]);

                if(keys.length == 4){
                    //数据为4列的时候显示模版表
                    _opt.showTemplates = true;
                }else{
                    //数据为3列的时候显示单据表
                    _opt.showBills = true;
                }
            }

            var messageBox = template('messageTemplate', _opt);

            var index = layer.open({
                title: '提&nbsp;&nbsp;示',
                type: 1,
                area: ['600px',''],
                content: messageBox,
                success: function(layero){
                    $('#layerConfirm').on('click',function(){
                        if(typeof _opt.callBack == 'function'){
                            _opt.callBack(_opt.callBackArgs);
                        }
                        layer.close(index);
                    });
                    $('#layerClose').on('click',function(){
                    	if(typeof _opt.callCancelBack == 'function'){
                            _opt.callCancelBack(_opt.callCancelBackArgs);
                        }
                        layer.close(index);
                    });
                    $('#hidHint').on('click',function(){
                        $('.data-hint').toggle();
                        $('.datas').toggle();
                        if($('.data-hint:hidden').length > 0){
                            $(this).html('查看详情>');
                        }else{
                            $(this).html('隐藏详情>');
                        }
                    });
                }
            });

        };

    }
)(jQuery);
