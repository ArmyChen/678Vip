<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_store_pay_orderModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的抽奖
	 * 输入：
	 * page:int 当前的页数
	 * pay_status:int 支付状态 0未支付 1已支付
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * item:array 订单列表
	 * Array(
	 *    Array
                (
                    [id] => 52 int 订单ID
                    [order_sn] => 2015050405530018 string 订单编号
                    [order_status] => 0 int 订单状态 0:未结单 1:结单(将出现删除订单按钮)
                    [pay_status] => 0 int 支付状态 0:未支付(出现取消订单按钮) 1:已支付
                    [create_time] => 2015-05-04 17:53:00  string 下单时间
                    [pay_amount] => 0 float 已付金额
                    [total_price] => 16.9 float 应付金额

                        )

                    [status] => 未支付 string 订单状态
                )
          )
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * page_title:string 页面标题
	 */
	public function index()
	{
		$root = array();		
		/*参数初始化*/
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);			

		$id = intval($GLOBALS['request']['id']);
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){
		    $root['user_login_status'] = $user_login_status;	
		}
		else
		{
			$root['user_login_status'] = $user_login_status;	
			
			
			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
			$sql = "select sto.* , sl.name as location_name from ".DB_PREFIX."store_pay_order as sto left join ".DB_PREFIX."supplier_location as sl on sto.location_id=sl.id where sto.user_id={$user_id} AND sto.is_delete=0 order by sto.create_time desc limit ".$limit;
			$sql_count = "select count(*) from ".DB_PREFIX."store_pay_order as sto left join ".DB_PREFIX."supplier_location as sl on sto.location_id=sl.id where sto.user_id={$user_id} AND  sto.is_delete=0";
			$list = $GLOBALS['db']->getAll($sql);		
			$count = $GLOBALS['db']->getOne($sql_count);
		
				
				
			$page_total = ceil($count/$page_size);
			//end 分页

			//要返回的字段
			$data = array();
			foreach($list as $k=>$v)
			{
				$order_item = array();
				$order_item['id'] = $v['id'];
				$order_item['order_sn'] = $v['order_sn'];
				
				if($v['pay_status']==0){
					$order_item['total_price'] = round($v['total_price']-$v['payment_fee'],2);
				}else{
					$order_item['total_price'] = round($v['total_price'],2);
				}
				
				$order_item['order_status'] = $v['order_status'];
				$order_item['pay_status'] = $v['pay_status'];
				$order_item['create_time'] = to_date($v['create_time']);
				$order_item['pay_amount'] = round($v['pay_amount'],2);
				
				$order_item['payment_fee'] = round($v['payment_fee'],2);
				$order_item['promote'] = unserialize($v['promote']);
				$order_item['discount_price'] = round($v['discount_price'],2);
				$order_item['location_name'] = $v['location_name'];
				//开始处理订单状态
				$order_status = "";				
				if($v['order_status'] == 1) //结单的订单显示说明
				$order_status = "订单已完结";
				else
				{
					if($v['pay_status'] != 2)
					{
						$order_status = "未支付";
					}
					else
					{
						$order_status = "已支付";
					}
				}				
				$order_item['status'] = $order_status;
				//订单状态
				
				$data[$k] = $order_item;
			}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	
		

		$root['page_title'].="到店付订单";
		output($root);
	}	


}
?>