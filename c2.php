
<?php
	

//$url="http://www.678sh.com/openApi/opencard.php";
//$url="http://www.678sh.com/openApi/chongzhiaction.php";
//$url="http://www.678sh.com/openApi/chongzhi_log.php";
//$url="http://www.678sh.com/openApi/xiaofei_log.php";
//$url="http://www.678sh.com/openApi/add_menu.php";
//$url="http://www.678sh.com/openApi/add_menu_cate.php";
//$url="http://www.678sh.com/openApi/deal_wm_order.php";
//$url="http://www.678sh.com/openApi/get_wm_order.php";
//$url="http://www.678sh.com/openApi/mdsupplier.php";
//$url="http://www.678sh.com/openApi/mdsupplier.php";
//$url="http://www.678sh.com/openApi/get_ads.php";
//$url="http://www.678sh.com/openApi/huacai_list.php";
//$url="http://www.678sh.com/openApi/huacai_desk.php";
//$url="http://www.678sh.com/openApi/huacai_action.php";
//$url="http://www.678sh.com/openApi/gedit3.php";
//$url="http://www.678sh.com/openApi/user_login.php";
//$url="http://www.678sh.com/openApi/jiesuan5.php";
//$url="http://www.678sh.com/openApi/getsyy2.php";
//$url="http://www.678sh.com/openApi/get_user_grade.php";
//$url="http://www.678sh.com/openApi/opencard2.php";
//$url="http://www.678sh.com/openApi/chongzhiaction2.php";
//$url="http://www.678sh.com/openApi/xiaofei_log2.php";
//$url="http://www.678sh.com/openApi/gys_sort.php";
//$url="http://www.678sh.com/openApi/gys_order.php";
//$url="http://www.678sh.com/openApi/user_xflog.php";
//$url="http://www.678sh.com/openApi/user_menu_cx.php";
//$url="http://www.678sh.com/openApi/huacai.php";
//$url="http://www.678sh.com/openApi/xuser_score_cfg.php";
//$url="http://www.678sh.com/openApi/get_wm_order.php";
//$url="http://www.678sh.com/openApi/tj_gaikuang2.php";
//$url="http://www.678sh.com/openApi/tv_dp_list_all.php";
//$url="http://www.678sh.com/openApi/get_slid_list.php";
//$url="http://www.678sh.com/openApi/diaobo_action.php";
$url="http://www.678sh.com/openApi/cangku_menu.php";
$url="http://www.678sh.com/openApi/mdzp2.php";
$url="http://www.678sh.com/openApi/paihao_board_list_detail.php";
$url="http://www.678sh.com/openApi/paihao_board_list_deal.php";
$url="http://www.678sh.com/openApi/paihao_get_qrcode.php";
$url="http://www.678sh.com/openApi/get_wm_all_order.php";
$url="http://www.678sh.com/openApi/paihao_get_number.php";
$url="http://www.678sh.com/openApi/paihao_board_call_deal.php";

$postdata=array();
$postdata['account_name']='dfhyc';
$postdata['account_passwd']='111111';
$postdata['sign']=strtoupper(md5($postdata['account_name'].md5($postdata['account_passwd'])));
$postdata['detail_id']='57';
$postdata['call_num']='1';
/*
$postdata['uniacid']='7';
$postdata['mobile']='13122223333';
$postdata['guest_num']='5';
//$postdata['guest_num']='5';

$postdata['slid']='40';
$postdata['detail_id']='1';
$postdata['status']='1';

$postdata['type']=1;
$postdata['slid']='40';
$postdata['tuan_code']='123456684';
$postdata['mobile']='13111112222';
$postdata['deal_time']='2017-02-28 13:00:00';
$postdata['tuan_time']='2017-02-28';
$postdata['money']=100.05;
$postdata['youhui_money']=80;
$postdata['tuan_num']=8;
$postdata['trade_no']='212328';
$postdata['tuan_detail']=array(
'location_name'=>'东方美',"item_name"=>'团购测试','yuanjia'=>500
);
*/

$postdatajson = json_encode($postdata); 

//$postdatajson='{"supplier_id":41,"begin_time":"2017-03-18 00:00:00","end_time":"2017-03-18 23:59:59","sign":"72881DADAE006CE0DE5216A3D4E30AD7","account_name":"tsl001","slid":2720}';
//$postdatajson='{"danjuhao":"201703231600511932","cidtwo":46,"sign":"22917D9C8DC86AFC3F609D1D94D52F47","memo":"杨贵妃","cid":18,"znum":3.0,"ctime":"2017-03-23 16:00:51","detail":[{"price":23.0,"funit":"箱","mid":33321,"memo":"","zmoney":69.0,"barcode":"1489889257711","name":"牛肉页面","times":30.0,"unit":"个","num":3.0,"unit_type":0,"type":0,"yuan_price":23.0}],"ztiji":0,"slid":40,"zmoney":69.0,"account_name":"dfhyc","zweight":0,"lihuo_user":1001}';
echo $postdatajson;
$pubkeyget=http_post_postdata($url,$postdatajson);
echo $pubkeyget;




       function http_post_postdata($url, $postdata_string) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Content-Length: ' . strlen($postdata_string))
		);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        return $return_content;
       }



?>
