/**
 * Created by Administrator on 2017/2/2.
 */
window.addEventListener('load', function() {
    FastClick.attach(document.body);
}, false);
$(function(){
    window.addEventListener("popstate", function(e) {
        window.location.reload();
    }, false);
});
$(document).ready(function(){





    /*$('.del_one').live('click',function(){

     var num=-1;

     dc_change_num($(this),num);

     });



     $('.add_one').live('click',function(){

     var num=1;



     dc_change_num($(this),num);

     });*/







    $('#dc_cart_clear').live('click',function(){

        dc_cart_clear();



    });



    //$('#dc_total ,#dc_cart_close').live('click',function(){
    //
    //
    //
    //	$('#dc_cart').slideToggle();
    //
    //
    //
    //});

    // $(document).on('click', $('#dc_total ,#dc_cart_close'),function(){

    // 	dc_cart_clear();



    // });



    // $(document).on('click', $('#dc_total ,#dc_cart_close'),function(){



    // 	$('#dc_cart').slideToggle();



    // });



});



function delc(obj){

    var num=-1;

    dc_change_num($(obj),num);

}



function addc(obj){

    var num=1;

    dc_change_num($(obj),num);

}



function dc_change_num(o,num){
    var menu_o= o.parent();

    var menu_id=parseInt(menu_o.attr('menu-id'));
    var id=parseInt(menu_o.attr('id'));

    var unit_price = parseFloat(menu_o.attr('cart-price'));
    var number=parseInt(num);

    var number_x=parseInt(menu_o.html())+num;

    var ajaxurl=DC_AJAX_URL;

    var query=new Object();

    query.menu_id=menu_id;
    query.id=id;

    query.number=number;

    query.tid=tid;

    query.location_id=location_id;

    query.supplier_id=supplier_id;

    query.distance=distance;

    query.act='dcorder_dc_add_cart';



    var ye_number = parseInt(menu_o.find("[cart-menuid]").html());

    if(isNaN(ye_number)){

        ye_number = 0;

    }

    var numm = ye_number+number;

    if(numm <= 0){

        menu_o.find("span[cart-menuid='"+menu_id+"']").html(0);

        menu_o.find("span[cart-menuid='"+menu_id+"']").addClass("hide1");

        menu_o.find("span[del-menuid='"+menu_id+"']").addClass("hide1");





        var card_add = '<div class="f_r" menu-id="'+menu_id+'" menu-count="0">'+

            '<span class="f_l block w040 h040 tc bor_7 bdr3 lh034 del_one hide1" onclick="delc(this)" del-menuid="'+menu_id+'"><i class="icon iconfont f_fe4d3d f024 delicon"></i></span>'+

            '<span class="f_fe4d3d f_l block h044 w054 lh044 tc hide1" cart-menuid="'+menu_id+'">0</span>'+

            '<span class="f_l block w040 h040 tc bor_7 bdr3 lh034 add_one" onclick="addc(this)"><i class="icon iconfont f_fe4d3d f024 addicon"></i></span></div>';





        o.siblings(".w_b_f_1").html("￥0");
        o.parent().remove();
        //$(".menu_right[menu-id='"+menu_id+"']").html(card_add);



    }else{

        // var card_add = '<div class="f_r" menu-id="'+menu_id+'" menu-count="'+numm+'">'+

        // '<span class="f_l block w040 h040 tc bor_7 bdr3 lh034 del_one"><i class="icon iconfont f_fe4d3d f024 delicon"></i></span>'+

        // '<span class="f_fe4d3d f_l block h044 w054 lh044 tc">'+numm+'</span>'+

        //'<span class="f_l block w040 h040 tc bor_7 bdr3 lh034 add_one"><i class="icon iconfont f_fe4d3d f024 addicon"></i></span></div>';





        var card_add = '<div class="f_r" menu-id="'+menu_id+'" menu-count="0">'+

            '<span class="f_l block w040 h040 tc bor_7 bdr3 lh034 del_one hide1" onclick="delc(this)" del-menuid="'+menu_id+'"><i class="icon iconfont f_fe4d3d f024 delicon"></i></span>'+

            '<span class="f_fe4d3d f_l block h044 w054 lh044 tc hide1" cart-menuid="'+menu_id+'">0</span>'+

            '<span class="f_l block w040 h040 tc bor_7 bdr3 lh034 add_one" onclick="addc(this)"><i class="icon iconfont f_fe4d3d f024 addicon"></i></span></div>';


        menu_o.find("span[cart-menuid='"+menu_id+"']").removeClass("hide1");

        menu_o.find("span[del-menuid='"+menu_id+"']").removeClass("hide1");

        menu_o.find("span[cart-menuid='"+menu_id+"']").html(numm);

        //$(".menu_right[menu-id='"+menu_id+"']").html(card_add);
        var total = isNaN(parseFloat(unit_price*numm))?0:unit_price*numm;
        o.siblings(".w_b_f_1").html("￥"+ total.toFixed(2));
    }



    $.ajax({

        url:ajaxurl,

        data:query,

        type:'post',

        dataType:'json',

        async:"true",

        success:function(data){

            if(data.status==1){

                //$('#dc_cartsection').html(data.html);



                $('#zjesy').html(data.total_price);



            }

        }

    });



}







function dc_cart_clear(){

    var query=new Object();

    var ajaxurl=DC_AJAX_URL;

    query.location_id=location_id;

    query.act='dc_cart_clear';

    $.ajax({

        url:ajaxurl,

        data:query,

        type:'post',

        dataType:'json',

        success:function(data){

            if(data.status==1){



                location.href=location.href;

            }

        }

    });
}



