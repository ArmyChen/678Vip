<?php

// +----------------------------------------------------------------------

// | Fanweo2o商业系统 最新版V3.03.3285  含4个手机APP。

// +----------------------------------------------------------------------

// | 购买本程序，请联系QQ：78282385  旺旺名：alert988

// +----------------------------------------------------------------------

// | 淘宝购买地址：https://shop36624490.taobao.com/

// +----------------------------------------------------------------------



class PaymentNoticeAction extends CommonAction{

	public function index()

	{

		if(strim($_REQUEST['order_sn'])!='')

		{

			$condition['order_id'] = M("DealOrder")->where("order_sn='".strim($_REQUEST['order_sn'])."'")->getField("id");

		}

		if(strim($_REQUEST['notice_sn'])!='')

		{

			$condition['notice_sn'] = $_REQUEST['notice_sn'];

		}	

	   if(strim($_REQUEST['user_name'])!='')

		{

			$condition['user_id'] = M("User")->where("user_name='".strim($_REQUEST['user_name'])."'")->getField("id");

		}	

		

		if(intval($_REQUEST['payment_id'])==0)unset($_REQUEST['payment_id']);

		$this->assign("default_map",$condition);

		$this->assign("payment_list",M("Payment")->findAll());

		parent::index();

	}

}

?>