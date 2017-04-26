//门店选择组件
var ShopData = {
    selectDefaultHtml : '请选择商户',
	//初始化门店选择
    init: function (initId) {
        // 交互
        $(".multi-select > .select-control").on("click",function(){
            var showList = $(this).next(".multi-select-con");
            if(showList.is(":hidden")){
                $(".multi-select-con").hide();
                showList.show();
            }else{
                showList.hide();
            }
        });

        delegateCheckbox('commercialIds');

        //任意点击隐藏下拉层
        $(document).bind("click",function(e){
            var target = $(e.target);
            //当target不在popover/coupons-set 内是 隐藏
            if(target.closest(".multi-select-con").length == 0 && target.closest(".select-control").length == 0){
                $(".multi-select-con").hide();
            }
        });

        //初始选中
       if(initId){
    	   $("#"+initId).click();
       }else{
           $(".multi-select").find("em").each(function(){
               $this = $(this);
               $this.attr("style","color:#939393;").html($this.data('hint') || ShopData.selectDefaultHtml);
           });
       }
       
    }
};

/**
 * 监听下拉选框
 * @param name
 * @param id
 * @param refreshWmsOrNot 是否刷新“仓库”的下拉选框
 */
function delegateCheckbox(name){
    //业务类型 条件选择
    $(document).delegate(":checkbox[name='"+ name + "']","change",function(){
        var all = $(this).parents(".multi-select-items").find('.checkbox-all');
        associatedCheckAll(this, all);
        filterConditions(this, name, $(this).parents(".multi-select-con").prev(".select-control").find("em"),$(this).parents(".multi-select-con").next(":hidden"));
    });
    //业务类型 条件选择 全选
    $(document).delegate(".checkbox-all","change",function(){
        checkAll(this, name);
        filterConditions(this, name, $(this).parents(".multi-select-con").prev(".select-control").find("em"),$(this).parents(".multi-select-con").next(":hidden"));
    });
}

/**
 *    associatedCheckAll     //关联全选
 *    @param  object         e           需要操作对象
 *    @param  jqueryObj      $obj        全选对象
 **/
function associatedCheckAll(e,$obj){
    var flag = true;
    var $name = $(e).attr("name");
    checkboxChange(e,'checkbox-check');
    $(e).parents(".multi-select-items").find("[name='"+ $name +"']:checkbox").not(":disabled").each(function(){
        if(!this.checked){
            flag = false;
        }
    });
    $obj.get(0).checked = flag;
    checkboxChange($obj.get(0),'checkbox-check');
}

/**
 * 条件选择
 * @param checkboxName      string                  checkbox name
 * @param $textObj          jquery object           要改变字符串的元素
 * @param $hiddenObj        jquery object           要改变的隐藏域
 */
function filterConditions(target,checkboxName,$textObj,$hiddenObj){
    var checkboxs = $(target).parents(".multi-select-items").find(":checkbox[name='" + checkboxName + "']");
    var checkboxsChecked = $(target).parents(".multi-select-items").find(":checkbox[name='" + checkboxName + "']:checked");
    var len = checkboxs.length;
    var lenChecked = checkboxsChecked.length;
    var str = '';
    var value1 = '';

    for(var i=0;i<lenChecked;i++){
        if(i==0){
            str += checkboxsChecked.eq(i).attr("data-text");
            value1 += checkboxsChecked.eq(i).attr("value");
        }else{
            str += ',' + checkboxsChecked.eq(i).attr("data-text");
            value1 += "," + checkboxsChecked.eq(i).attr("value");
        }
    }

    if(str.length>0){
    	$textObj.attr("style","").text(str);
    }else{
    	$textObj.attr("style","color:#939393;").text($textObj.data('hint') || ShopData.selectDefaultHtml);
    }
    $hiddenObj.val(value1);

    if(lenChecked == len){
        $textObj.text("全部");
    }
}

/**
 *    checked all            //全选
 *    @param  object         e           需要操作对象
 *    @param  nameGroup      string      checkbox name
 **/
function checkAll(e,nameGroup){
    if(e.checked){
        //alert($("[name='"+ nameGroup+"']:checkbox"));
        $(e).parents(".multi-select-items").find("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
            this.checked = true;
            checkboxChange(this,'checkbox-check');
        });
    }else{
        $(e).parents(".multi-select-items").find("[name='"+ nameGroup+"']:checkbox").not(":disabled").each(function(){
            this.checked = false;
            checkboxChange(this,'checkbox-check');
        });
    }
    checkboxChange(e,'checkbox-check');
}

/**
 *    checkbox               //模拟checkbox功能
 *    @param  object         element     需要操作对象
 *    @param  className      class       切换的样式
 **/
function checkboxChange(element,className){
    if(element.readOnly){return false;}
    if(element.checked){
        $(element).parent().addClass(className);
    }else{
        $(element).parent().removeClass(className);
    }
}

//注册移除事件
$("#undo-all").on("click",function(){
	$("#shopIds").val("");
    $(".multi-select").find("em").each(function(){
        $this = $(this);
        $this.attr("style","color:#939393;").html($this.data('hint') || ShopData.selectDefaultHtml);
    });
});