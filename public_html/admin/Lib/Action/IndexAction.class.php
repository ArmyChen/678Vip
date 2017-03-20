<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class IndexAction extends AuthAction{
	//首页
    public function index(){
		$this->display();
		
    }
    

    //框架头
	public function top()
	{
		$navs = require_once APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/admnav_cfg.php";	
		if(OPEN_WEIXIN)
		{
			$config_file = APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/wxadmnav_cfg.php";
			$navs = array_merge_admnav($navs, $config_file);
		}
		if(OPEN_FX)
		{
			$config_file = APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/fxadmnav_cfg.php";
			$navs = array_merge_admnav($navs, $config_file);
		}
		if(OPEN_DC)
		{
			$config_file = APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/dcadmnav_cfg.php";
			$navs = array_merge_admnav($navs, $config_file);
		}
		
		
		$this->assign("navs",$navs);
		$this->display();
	}
	//框架左侧
	public function left()
	{
		$navs = require_once APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/admnav_cfg.php";
		if(OPEN_WEIXIN)
		{
			$config_file = APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/wxadmnav_cfg.php";
			$navs = array_merge_admnav($navs, $config_file);
		}
		if(OPEN_FX)
		{
			$config_file = APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/fxadmnav_cfg.php";
			$navs = array_merge_admnav($navs, $config_file);
		}
		if(OPEN_DC)
		{
			$config_file = APP_ROOT_PATH."system/adm_cfg/".APP_TYPE."/dcadmnav_cfg.php";
			$navs = array_merge_admnav($navs, $config_file);
		}
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$adm_id = intval($adm_session['adm_id']);
		
		$nav_key = strim($_REQUEST['key']);
		$nav_group = $navs[$nav_key]['groups'];
		$this->assign("menus",$nav_group);
		$this->display();
	}
	//默认框架主区域
	public function main()
	{
		$this->assign("apptype",APP_TYPE);
		$this->assign("FANWE_APP_ID",FANWE_APP_ID);
		//关于订单
			
		
		$income_order = M("Statements")->sum("income_order");
		$this->assign("income_order",$income_order);
		$refund_money = M("Statements")->sum("refund_money");
		$this->assign("refund_money",$refund_money);
		$dealing_order = M("DealOrder")->where("order_status = 0")->count();
		$this->assign("dealing_order",$dealing_order);
		$refund_order = M("DealOrder")->where("refund_status = 1")->count();
		$this->assign("refund_order",$refund_order);
		$no_arrival_order = M("DealOrder")->where("is_refuse_delivery = 1")->count();
		$this->assign("no_arrival_order",$no_arrival_order);
		
		
		//关于用户
		$user_count = M("User")->count();
		$this->assign("user_count",$user_count);
		$income_incharge = M("Statements")->sum("income_incharge");
		$this->assign("income_incharge",$income_incharge);
		$withdraw = M("Withdraw")->where("is_paid = 0 and is_delete = 0")->count();
		$this->assign("withdraw",$withdraw);
		
		//上线的团购
		$tuan_count = M("Deal")->where("is_shop = 0 and is_effect = 1 and is_delete = 0")->count();
		$this->assign("tuan_count",$tuan_count);
		$tuan_dp_wait_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp  where dp.deal_id >0 and dp.reply_content = '' ");
		$tuan_dp_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp  where dp.deal_id >0 ");
		$this->assign("tuan_dp_wait_count",$tuan_dp_wait_count);
		$this->assign("tuan_dp_count",$tuan_dp_count);
		
		$tuan_submit_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_submit where is_shop = 0 and admin_check_status = 0");
		$this->assign("tuan_submit_count",$tuan_submit_count);
		
		//上线的商品
		$shop_count = M("Deal")->where("is_shop = 1 and is_effect = 1 and is_delete = 0")->count();
		$this->assign("shop_count",$shop_count);
		
		$this->assign("shop_dp_wait_count",$tuan_dp_wait_count);
		$this->assign("shop_dp_count",$tuan_dp_count);
		
		$shop_submit_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_submit where is_shop = 1 and admin_check_status = 0");
		$this->assign("shop_submit_count",$shop_submit_count);
		
		//关于优惠
		$youhui_count = M("Youhui")->where("is_effect = 1")->count();
		$this->assign("youhui_count",$youhui_count);
		
		$youhui_dp_wait_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.youhui_id >0 and dp.reply_content = ''");
		$youhui_dp_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.youhui_id >0");
		$this->assign("youhui_dp_wait_count",$youhui_dp_wait_count);
		$this->assign("youhui_dp_count",$youhui_dp_count);
		
		$youhui_submit_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_biz_submit where admin_check_status = 0");
		$this->assign("youhui_submit_count",$youhui_submit_count);
		
		//关于活动
		$event_count = M("Event")->where("is_effect = 1")->count();
		$this->assign("event_count",$event_count);
		
		$event_dp_wait_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.event_id >0 and dp.reply_content = ''");
		$event_dp_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.event_id >0");
		$this->assign("event_dp_wait_count",$event_dp_wait_count);
		$this->assign("event_dp_count",$event_dp_count);
		
		$event_submit_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event_biz_submit where admin_check_status = 0");
		$this->assign("event_submit_count",$event_submit_count);
		
		//关于商户
		$supplier_count = M("Supplier")->count();
		$this->assign("supplier_count",$supplier_count);
		$store_count = M("SupplierLocation")->where("is_effect = 1")->count();
		$this->assign("store_count",$store_count);
		
		$supplier_submit_count = M("SupplierSubmit")->where("is_publish = 0")->count();
		$this->assign("supplier_submit_count",$supplier_submit_count);
		
		$store_dp_wait_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.supplier_location_id >0 and dp.reply_content = ''");
		$store_dp_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.supplier_location_id >0");
		$this->assign("store_dp_wait_count",$store_dp_wait_count);
		$this->assign("store_dp_count",$store_dp_count);
		
		$location_submit_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_biz_submit where admin_check_status = 0");
		$this->assign("location_submit_count",$location_submit_count);
		
		$sp_withdraw_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_money_submit where status = 0");
		$this->assign("sp_withdraw_count",$sp_withdraw_count);
		
		//2016.5.7
		$diffdates=0;
		$diffdates2=$diffdates+1;
		$start=date('Y-m-d',strtotime($diffdates.' month', strtotime(date('Y-m', time()).'-01 00:00:00')));//上月第一天
		$end_time=date('Y-m-d',strtotime(date('Y-m-d',strtotime($diffdates2.' month', strtotime(date('Y-m', time()).'-01 00:00:00'))))-1);//上月第一天
		//echo  $start."----".$end_time;
		$begin_time_s = to_timespan($start);
		$end_time_s = to_timespan($end_time);	
		
		$day_start=date("Y-m-d",NOW_TIME)." 00:00:00"; 
		$day_end=date("Y-m-d",NOW_TIME)." 23:59:59"; 
		
		$day_start_s = to_timespan($day_start);
		$day_end_s = to_timespan($day_end);	
		/*
		$paythree['total'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='alipay' or zffs='weixipay' or zffs='bestpay')");
		$paythree['month_total'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='alipay' or zffs='weixipay' or zffs='bestpay') and (otime between $begin_time_s and $end_time_s)");
		$paythree['day_total'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='alipay' or zffs='weixipay' or zffs='bestpay') and (otime between $day_start_s and $day_end_s)");
		
		
		$paythree['total_alipay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='alipay')");
		$paythree['month_total_alipay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='alipay') and (otime between $begin_time_s and $end_time_s)");
		$paythree['day_total_alipay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='alipay') and (otime between $day_start_s and $day_end_s)");
	
	
	    $paythree['total_weixipay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='weixipay')");
		$paythree['month_total_weixipay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='weixipay') and (otime between $begin_time_s and $end_time_s)");
		$paythree['day_total_weixipay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='weixipay') and (otime between $day_start_s and $day_end_s)");
	
	    $paythree['total_bestpay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='bestpay')");
		$paythree['month_total_bestpay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='bestpay') and (otime between $begin_time_s and $end_time_s)");
		$paythree['day_total_bestpay'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='bestpay') and (otime between $day_start_s and $day_end_s)");
	
		
		$paythree['cash_total'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='cash')");
		$paythree['cash_month_total'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='cash') and (otime between $begin_time_s and $end_time_s)");
		$paythree['cash_day_total'] = $GLOBALS['db']->getOne("select sum(money_ys) from orders where zhifustatus > 0 and (zffs='cash') and (otime between $day_start_s and $day_end_s)");
		*/
		
		$paythree_total=$GLOBALS['db']->getAll("select zffs,sum(cmoney) as money_ys from orders_pay where zhifustatus > 0 group by zffs");
		//echo ("select zffs,sum(money_ys) as money_ys from orders where zhifustatus > 0 group by zffs");
		//$paythree_total=$GLOBALS['db']->getAll("select zffs,sum(money_ys) as money_ys from orders where zhifustatus > 0 group by zffs");
		$paythree_month_total=$GLOBALS['db']->getAll("select zffs,sum(cmoney) as money_ys from orders_pay where zhifustatus > 0  and (otime between $begin_time_s and $end_time_s) group by zffs");
		$paythree_day_total=$GLOBALS['db']->getAll("select zffs,sum(cmoney) as money_ys from orders_pay where zhifustatus > 0  and (otime between $day_start_s and $day_end_s) group by zffs");
		
		foreach($paythree_total as $kt=>$vt){
		$paythree['total_'.$vt['zffs']]=$vt['money_ys'];
		if($vt['zffs']=='bestpay' || $vt['zffs']=='alipay' || $vt['zffs']=='hbpay'|| $vt['zffs']=='weixipay' || $vt['zffs']=='jdpay' || $vt['zffs']=='qqpay'){
        $paythree['total']= $paythree['total']+	$vt['money_ys'];
        }		
		}
		foreach($paythree_month_total as $kt=>$vt){
		$paythree['month_total_'.$vt['zffs']]=$vt['money_ys'];
		if($vt['zffs']=='bestpay' || $vt['zffs']=='alipay' || $vt['zffs']=='hbpay' || $vt['zffs']=='weixipay' || $vt['zffs']=='jdpay' || $vt['zffs']=='qqpay'){
		$paythree['month_total']= $paythree['month_total']+	$vt['money_ys'];	
		}		
		}
		foreach($paythree_day_total as $kt=>$vt){
		$paythree['day_total_'.$vt['zffs']]=$vt['money_ys'];
		if($vt['zffs']=='bestpay' || $vt['zffs']=='alipay' || $vt['zffs']=='hbpay' || $vt['zffs']=='weixipay' || $vt['zffs']=='jdpay' || $vt['zffs']=='qqpay'){
		$paythree['day_total']= $paythree['day_total']+	$vt['money_ys'];
		}		
		}
		
		//支付总额
		$paythree['all_pay_total']=$GLOBALS['db']->getOne("select sum(cmoney) from orders_pay where zhifustatus > 0");
		$paythree['month_pay_total']=$GLOBALS['db']->getOne("select sum(cmoney)  from orders_pay where zhifustatus > 0 and (otime between $begin_time_s and $end_time_s)");
		$paythree['day_pay_total']=$GLOBALS['db']->getOne("select sum(cmoney) from orders_pay where zhifustatus > 0 and (otime between $day_start_s and $day_end_s)");
		//订单总数 
		$paythree['sum_total'] = $GLOBALS['db']->getOne("select count(id) from orders where zhifustatus > 0");
		$paythree['sum_month_total'] = $GLOBALS['db']->getOne("select count(id)  from orders where zhifustatus > 0 and (otime between $begin_time_s and $end_time_s)");
		$paythree['sum_day_total'] = $GLOBALS['db']->getOne("select count(id)  from orders where zhifustatus > 0  and (otime between $day_start_s and $day_end_s)");
		
		
		
		
	    $this->assign("paythree",$paythree);
	
	
        $huizong =	$GLOBALS['db']->getRow("select sum(sale_money) as sale_money,sum(money) as money,sum(refund_money) as refund_money,sum(wd_money) as wd_money from ".DB_PREFIX."supplier_location");
        $paythreetotal=$GLOBALS['db']->getAll("select shoukuanfang,zhifustatus,sum(cmoney) as money from orders_pay where (zhifustatus=1 or zhifustatus=9) and (zffs='alipay' or zffs='weixipay' or zffs='bestpay' or zffs='hbpay' or zffs='qqpay' or zffs='jdpay') group by shoukuanfang,zhifustatus");
		$paythreetotal_array = array_reduce($paythreetotal, create_function('$v,$w', '$v[$w["shoukuanfang"]][$w["zhifustatus"]]=$w["money"];return $v;'));  	
		//var_dump($paythreetotal_array);
		$huizong['mdsale_money']=$paythree['total_alipay'] + $paythree['total_weixipay']+$paythree['total_bestpay']+ $paythree['total_qqpay']+$paythree['total_jdpay']+$paythree['total_hbpay'];
		$huizong['mdwd_money']= $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."location_money_log where type=5");
		$huizong['mdjz_money']= $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."location_money_log where type=6");
		$huizong['plat_daishou']=$paythreetotal_array['0']['1'];
		$huizong['plat_daishou_refund']=$paythreetotal_array['0']['9'];
		$huizong['plat_zishou']=$paythreetotal_array['1']['1'];
		$huizong['plat_zishou_refund']=$paythreetotal_array['1']['9'];
		$huizong['weitixian']=$huizong['plat_daishou']-$huizong['mdwd_money']-$huizong['mdjz_money'];
		
		//$huizong['waiting_for_sh']=$GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."location_money_log");
		
		$this->assign("huizong",$huizong);
	
	    $appnum=$GLOBALS['db']->getAll("select count(distinct(appid)) as appnum,type from fanwe_app group by type");
		foreach($appnum as $ka=>$va){
			$appset[$va['type']]=$va['appnum'];
		}
		$this->assign("appset",$appset);
		$this->display();
	}	
		
	//底部
	public function footer()
	{
		$this->display();
	}
	
	//修改管理员密码
	public function change_password()
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$this->assign("adm_data",$adm_session);
		$this->display();
	}
	public function do_change_password()
	{
		$adm_id = intval($_REQUEST['adm_id']);
		if(!check_empty($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_EMPTY_TIP"));
		}
		if(!check_empty($_REQUEST['adm_new_password']))
		{
			$this->error(L("ADM_NEW_PASSWORD_EMPTY_TIP"));
		}
		if($_REQUEST['adm_confirm_password']!=$_REQUEST['adm_new_password'])
		{
			$this->error(L("ADM_NEW_PASSWORD_NOT_MATCH_TIP"));
		}		
		if(M("Admin")->where("id=".$adm_id)->getField("adm_password")!=md5($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_ERROR"));
		}
		M("Admin")->where("id=".$adm_id)->setField("adm_password",md5($_REQUEST['adm_new_password']));
		save_log(M("Admin")->where("id=".$adm_id)->getField("adm_name").L("CHANGE_SUCCESS"),1);
		$this->success(L("CHANGE_SUCCESS"));
		
		
	}
	
	public function reset_sending()
	{
		$field = strim($_REQUEST['field']);
		if($field=='DEAL_MSG_LOCK'||$field=='PROMOTE_MSG_LOCK'||$field=='APNS_MSG_LOCK')
		{
			M("Conf")->where("name='".$field."'")->setField("value",'0');
			$this->success(L("RESET_SUCCESS"),1);
		}
		else
		{
			$this->error(L("INVALID_OPERATION"),1);
		}
	}
}
?>