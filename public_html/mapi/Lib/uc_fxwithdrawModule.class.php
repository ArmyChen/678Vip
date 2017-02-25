<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class uc_fxwithdrawModule extends MainBaseModule
{
	
	/**
	 * 	 会员中心分销提现列表接口
	 * 
	 * 	  输入：
	 *  page:int 当前的页数
	 *  
	 *  
	 *  输出：
	 * user_login_status:int   0表示未登录   1表示已登录
	
	 * page_title:string 页面标题
	 * fxmoney:string 当前分销收益	 
	 * list:array:array 分销提现记录列表，结构如下
	 *  Array
		(
		    [0] => Array
		        (
		                [id] => 19 int 提现记录id
			            [money] =>¥3.9 string 提现金额
			            [create_time] => 2015-06-16 18:12:31  string  申请时间
			            [is_paid] => 1 int 管理员是否确认提现  0表示未确认 1表示已确认
			            [type] => 1 int 提现方式  1表示提现至银行卡  0表示提现至账户余额

		        )
		)
	 */
	public function index()
	{
		$root = array();		
			
		$user_data = $GLOBALS['user_info'];	
		$user_id = intval($user_data['id']);
		$page = intval($GLOBALS['request']['page'])?intval($GLOBALS['request']['page']):1; //当前分页
		
		$user_login_status = check_login();	
		if($user_login_status!=LOGIN_STATUS_LOGINED){	
				
			$root['user_login_status'] = $user_login_status;	
		}else{
			$root['user_login_status'] = 1;
			$root['fxmoney']=format_price($user_data['fx_money']);
			
			
			require_once APP_ROOT_PATH."system/model/fx.php";
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;			
			$result = get_fx_withdraw($limit,$user_id);
			$page_total = ceil($result['count']/$page_size);
			
			foreach($result['list'] as $k=>$v)
			{	
				$list[$k]['id']=$v['id'];
				
				$list[$k]['money'] = format_price($v['money']);
				$list[$k]['create_time']=to_date($v['create_time']);
				$list[$k]['is_paid']=$v['is_paid'];

				$list[$k]['type']=$v['type'];
			}			
			
			
			$root['list'] = $list?$list:array();
			
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$result['count']);
			$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
			$root['page_title'].="分销提现";

		}
		
		output($root);

	}

	
	
	
	

	
	

	
	/**
	 * 	 会员中心分销提现接口
	 * 
	 * 	  输入：
	 *  sms_verify:string 手机验证码，仅在app初始化配置中开启短信功能时传入，没开短信功能时不传
	 *  money:5.5 [int] 提现金额
	 *  type:0[int]  提现类型 0表示提现至余额 1表示提至银行卡
	 *  bank_name:中国建设银行[string] 开户行名称
	 *  bank_account:6227001856239566887 [string] 银行卡号
	 *  bank_user:陈新国 [string] 开户人姓名

	 *  
	 *  输出：
	 * user_login_status:[int]   0表示未登录   1表示已登录

		底层的status字段为1时表示成功，0表示失败，info表示失败信息
	

   
	 */
	public function save()
	{
		$root = array();		
			
		$user_data = $GLOBALS['user_info'];		
		$user_id = intval($user_data['id']);
		$id = intval($GLOBALS['request']['id']);
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){			
			$root['user_login_status'] = $user_login_status;	
		}else{
			$root['user_login_status'] = 1;

			$data['sms_verify'] = intval($GLOBALS['request']['sms_verify']);
			$data['money'] = floatval($GLOBALS['request']['money']);
			$data['type'] = intval($GLOBALS['request']['type']);
			$mobile = $GLOBALS['user_info']['mobile'];
			
			$data['bank_name'] = strim($GLOBALS['request']['bank_name']);
			$data['bank_account'] = strim($GLOBALS['request']['bank_account']);
			$data['bank_user'] = strim($GLOBALS['request']['bank_user']);
	

			if($data['bank_name']==""&&$data['type']==1)
			{

				output($root,0,"请输入开户行全称");
			}
			if($data['bank_account'] ==""&&$data['type']==1)
			{
				

				output($root,0,"请输入开户行账号");
			}
			if($data['bank_user']==""&&$data['type']==1)
			{				

				output($root,0,"请输入开户人真实姓名");				
			}
			if($data['money']<=0)
			{				

				output($root,0,"请输入正确的提现金额");		
			}			
			
			if(app_conf("SMS_ON")==1)
			{
				if($mobile=="")
				{

					output($root,0,"请先完善会员的手机号码");		
				}				
			
				if($data['sms_verify']=="")
				{					

					output($root,0,"请输入收到的验证码");	
				}
			
				//短信码验证
				$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
				$GLOBALS['db']->query($sql);
			
				$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile."'");
			
				if($mobile_data['code']!=$data['sms_verify'])
				{

					output($root,0,"验证码错误");					
				}			
				
			}			
			

			
				$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."fx_withdraw where user_id = ".$GLOBALS['user_info']['id']." and is_delete = 0 and is_paid = 0"));
				if($submitted_money+$data['money']>$GLOBALS['user_info']['fx_money'])
				{					

					output($root,0,"提现超额");		
				}
				
				$withdraw_data = array();
				$withdraw_data['user_id'] = $GLOBALS['user_info']['id'];
				$withdraw_data['money'] = $data['money'];
				$withdraw_data['create_time'] = NOW_TIME;
				$withdraw_data['bank_name'] =$data['bank_name'];
				$withdraw_data['bank_account'] = $data['bank_account'];
				$withdraw_data['bank_user'] = $data['bank_user'];
				$withdraw_data['type'] = $data['type'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."fx_withdraw",$withdraw_data);
				
				$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile."'");

				
			$root['add_status'] = 1;					
				
		}

	
		
		output($root);

	}		
	
	

	
}
?>