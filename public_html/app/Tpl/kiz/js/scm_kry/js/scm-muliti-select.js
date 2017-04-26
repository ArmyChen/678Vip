/**
 * Created by my on 2016/3/1.
 */
(function($){
    //多选下拉框数据
    var commercials = {};
    //是否已经绑定事件
    var isBind = false;
    //重置多选框数据
    $.setSelectMultiData = function(data){
        commercials = data;
        $('input:hidden.checkbox-selected').each(function(){
            initValue($(this));
        });
    };
    //选择列表初始化
    $.fn.initSelectList = function (){
        var $this = $(this);
        var lis = '';
        var i = 0;

        //添加筛选列
        lis += '      <li>'
            +'    <div class="select-search-box"><input id="searchList" class="form-control" type="text" autocomplete="off"><span class="glyphicon glyphicon-search search-icon"></span></div>'
            +'      </li>';
        //添加多选列表
        for(var commercialId in commercials){
            var i = i+1;

            lis +='         <li>'
                +'                <label class="checkbox commercial" for="commercial-' + i + '">'
                +'                    <span></span>'
                +'                    <input type="checkbox" name="commercialIds" id="commercial-' + i + '"'
                +'                           value="' + commercialId + '" data-text="' + commercials[commercialId] + '">' + commercials[commercialId]
                +'                </label>'
                +'            </li>';
        }

        var items = $this.siblings('.multi-select-item');
        items.children('ul').html(lis);
        var selectedIds = $this.val().split(',');
        items.find(':checkbox[name="commercialIds"]').each(function(){
            var id = $(this).val();
            if(selectedIds.indexOf(id) > -1){
                $(this).click();
            }
        });

        //绑定筛选事件
        var $searchList = $("#searchList");
        $searchList.on('keyup', function () {
            $searchList.parents('li').siblings("li").each(function () {
                $li = $(this);
                if($li.find('input[name="commercialIds"]').data('text').indexOf($searchList.val()) > -1){
                    $li.show();
                }else{
                    $li.hide();
                }
            });
        });
    };
    //初始化多选框
    $.fn.selectMulti = function(options){
        $(this).each(function(){
            var $this = $(this);

            //防止重复初始化
            if($this.parent('.multi-select').length > 0){
                return;
            }
            initCompnent($this);

            // 交互
            $this.siblings('.select-control').on("click",function(e){

                var showList = $(this).next(".multi-select-item");
                if(showList.is(":hidden")){
                    //关闭并清空其他多选框
                    $(".multi-select-item").hide();
                    $(".multi-select-item").children().empty();
                    $this.initSelectList();
                    showList.show();
                    $("#searchList").focus();
                }else{
                    showList.hide();
                    showList.children().empty();
                }
            });
        });

        if(isBind){
            return;
        }
        //任意点击隐藏下拉层
        $(document).bind("click",function(e){
            var target = $(e.target);
            //当target不在popover/coupons-set 内是 隐藏
            if(target.closest(".multi-select-item").length == 0 && target.closest(".select-control").length == 0){
                $(".multi-select-item").hide();
                $(".multi-select-item").children().empty();
            }
        });

        //checkbox选择后
        $(document).delegate(':checkbox[name="commercialIds"]','change',function(e){
            var $multiSelectItems = $(this).parents('ul.multi-select-items').find(':checkbox:checked');
            var selectedIds = [];
            var selectedNames = [];
            $multiSelectItems.each(function (i,e) {
                selectedIds.push(e.value);
                selectedNames.push(e.dataset['text']);
            });
            var $multiSelectBox = $(this).parents('div.multi-select');
            var selectName = selectedNames.join(',');
            $multiSelectBox.find('em').text(selectName).attr('title',selectName);
            $multiSelectBox.find('input.checkbox-selected').val(selectedIds.join(','));
            //没有值时显示为“无”
            if(selectedNames.length<=0) $multiSelectBox.find('em').text("无").attr("title","无");
        });
        isBind = true;
    };

    //组件初始化
    function initCompnent($this){
        var select = '<div class="select-control"><em></em></div>'
            +'<div class="multi-select-item">'
            +'    <ul class="multi-select-items commercials">'
            +'    </ul>'
            +'</div>';

        var $selectBox = $('<div class="multi-select"></div>');
        $selectBox.html(select);
        $this.before($selectBox);
        $this.prev().append($this);

        var selectedIds = $this.val().split(',');
        var selectedNames = [];
        if(selectedIds.length > 0){
            for(var i=0;i<selectedIds.length;i++){
                selectedNames.push(commercials[selectedIds[i]]);
            }
        }

        initValue($this);
    }

    //初始化Id值和显示的名字
    function initValue($this){
        var value = $this.val().split(',');
        var selectedIds = [];
        var selectedNames = [];
        if(value.length > 0){
            for(var i=0;i<value.length;i++){
                var name = commercials[value[i]];
                if(name){
                    selectedIds.push(value[i]);
                    selectedNames.push(commercials[value[i]]);
                }
            }
        }

        $this.val(selectedIds.join(','));
        $this.siblings('div.select-control').children().html(selectedNames.join(','));
        $this.parents('td').find("em").attr('title',selectedNames.join(','));
        //没有值时显示为“无”
        if(selectedNames.length<=0) $this.siblings('div.select-control').children().html("无").attr("title","无");
    }
})(jQuery);