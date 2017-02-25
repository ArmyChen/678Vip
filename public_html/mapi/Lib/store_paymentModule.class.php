<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class store_paymentApiModule extends MainBaseApiModule
{
	
	

	/**
	 * 支付订单页面，点击“确认支付”的跳转地址
	 *
	 * 测试地址：http://localhost/o2onew/mapi/index.php?ctl=store_pay&act=done&r_type=2&order_id=41
	 * 输入：
	 * order_id：int 订单ID
	 *
	 *
	 * 输出：
	 *
	 * order_id：int 订单ID
	 * order_sn:订单编号
	 * pay_status：int 订单返回状态
	 * pay_status=0,订单未完成
	 * pay_status=1,正常支付，支付完成
	 * pay_info：提示信息
	 * page_title：标题
	 * payment_code：订单未支付完成时，返回的付款单号的信息，该值只有pay_status=0时才有
	 *
	 */
	public function done()
	{
		global_run();

		$order_id = intval($GLOBALS['request']['order_id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."store_pay_order where id = ".$order_id);
		
		if($order_info['pay_status']==2){
			$pay_status=1;
		}elseif($order_info['pay_status']==0){
			$pay_status=0;
		}
		
		
		if($pay_status==1){  //正常支付，支付完成

			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$root['pay_info']="支付成功,返回订单中心";
			$root['page_title']=$GLOBALS['lang']['PAY_SUCCESS'];
			return output($root);
	
				
		}elseif($pay_status==0){ //订单未完成
			
			$payment_id=$order_info['payment_id'];
			
			$payment_notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_id." and payment_id=".$payment_id." and order_type=4 order by create_time desc limit 1");
			$payment_notice_id =$payment_notice_info['id'];
			$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_notice_info['payment_id']);

			
			if($GLOBALS['request']['from']=="wap")
			{
				if ($payment_info['online_pay']!=2&&$payment_info['online_pay']!=4&&$payment_info['online_pay']!=5)
				{
					return output(array(),0,"该支付方式不支持wap支付");
				}
			}
			else
			{
				if ($payment_info['online_pay']!=3&&$payment_info['online_pay']!=4&&$payment_info['online_pay']!=5)
				{
					return output(array(),0,"该支付方式不支持手机支付");
				}
			}
				
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			$payment_code = $payment_object->get_payment_code($payment_notice_id);

			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$pay_price=$order_info['total_price']-$order_info['pay_amount']-$order_info['discount_price'];
			if($GLOBALS['request']['from']=="wap"){
				$root['page_title']='订单未完成';
				$root['pay_info']=format_price($pay_price)."未支付,请前往支付";
				$root['payment_code'] = $payment_code;
			}else{
				$root['page_title']='订单未完成';
				$root['pay_info']=format_price($pay_price)."未支付,请前往支付";
				$root['payment_code'] = $payment_code;
			}
			
			
			return output($root);
				
				
		}
	
	
	
	
	}
	


}
?>