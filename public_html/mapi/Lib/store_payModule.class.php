<?php

// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class store_payModule extends MainBaseModule {

    /**
     * 用户到店支付页面
     * 输入
     * location_id int 门店ID
     * 
     * 
     * 输出
     * user_login_status int 用户登录状态
     * 
     * 登录成功返回
     * page_title : string 标题
     * location_id : int 门店ID
     * location_name : string  门店名称
     * supplier_id : int 商户ID
     * 促销规则内容
     * promote_data :array
     * 结构如下:
     * array(
     *      [0]=>array(
     *              "id"=>1,    int  促销规则ID
     *              "name"=>"每满58减10" string 促销规则说明
     *              )
     * )
     * 
     */
    public function pay() {
        //获取页面参数
        $location_id = intval($GLOBALS['request']['location_id']);


        
        //检查用户,用户密码
        $user_info = $GLOBALS['user_info'];
        /*
        $user_login_status = check_login();
        if ($user_login_status != LOGIN_STATUS_LOGINED) {
            $root['user_login_status'] = $user_login_status;
            output($root);
        }
        $root['user_login_status'] = $user_login_status;
        */
        //商户数据
        $location_info = $GLOBALS['db']->getRow("select id,name,supplier_id from " . DB_PREFIX . "supplier_location where id=" . $location_id);
        if(!$location_info){
        	
        	output($root,-1,'无此商家');
        }
        
        $root['location_id'] = $location_info['id'];
        $root['location_name'] = $location_info['name'];
        $root['supplier_id'] = $location_info['supplier_id'];

        //获取促销规则
         
        
        $promote= $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "promote where supplier_id =" . $location_info['supplier_id']);

        if($promote){
	        foreach($promote as $k=>$v){
	        	$promote[$k]['descriptions']=$v['description'];
	        }
        }
        $root['promote']=$promote;

        $root['page_title'] = $location_info['name'];
        output($root);
    }
    
 /**
     * 订单生成成功
     * 输入
     * location_id int 门店ID
     * money :float 输入金额
     * 
     * 输出
     * user_login_status int 用户登录状态
     * 
     * 订单生成成功返回
     * location_id : int 门店ID
	 * order_id ：订单号
     * 
     */
    public function make_order() {
    
    	//获取参数
    

    	$money = floatval($GLOBALS['request']['money']);
    	$location_id = intval($GLOBALS['request']['location_id']);
    
    	$user_info = $GLOBALS['user_info'];
    
    
    	$root = array();
    
    
    
    	if ((check_login() == LOGIN_STATUS_TEMP && $GLOBALS['user_info']['money'] > 0) || check_login() == LOGIN_STATUS_NOLOGIN) {
    		$root['user_login_status']=0;
    		output($root);
    	}else{
    	 
    		$root['user_login_status']=1;
    		$root['location_id']=$location_id;
    		if($money==0){
    			output($root, 0, "请输入消费金额");
    		}
    		 
    		$location_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where  open_store_payment = 1 and id=".$location_id);
    
    		if(empty($location_info)){
    			output($root, 0, "本店不支持到店买单");
    		}
    		 
    		$total_price = $money;
    
    		//生成订单数据
    		$order_data = array();
    		$order_data['type'] =  0;
    		$order_data['supplier_id'] =  $location_info['supplier_id'];
    		$order_data['create_time'] = NOW_TIME ;
    		$order_data['pay_status'] = 0 ;
    		$order_data['total_price'] =  $money;
    		$order_data['pay_amount'] =  0;
    		$order_data['discount_price'] = 0 ;
    		$order_data['promote_ids'] = '';
    		$order_data['promote_data'] = '' ;
    		$order_data['after_sale'] = 0 ;
    		$order_data['order_status'] =  0;
    		$order_data['location_id'] =  $location_id;
    		$order_data['user_id'] = $user_info['id'] ;
    		$order_data['user_mobile'] =  $user_info['mobile'];
    
    
    		//计算优惠
    		require_once APP_ROOT_PATH.'system/model/store_pay.php';
    		$count_data = count_store_pay_total($location_id, $money);
    		//  $order_data['pay_amount'] =  $count_data['pay_price'];
    		$order_data['discount_price'] = $count_data['discount_price'];
    		$order_data['promote_ids'] = $count_data['promote_ids'];
    		$order_data['promote_data'] = $count_data['promote_data'];
    		$order_data['promote'] = $count_data['promote'];
    
    		do
    		{
    			$order_data['order_sn'] = to_date(NOW_TIME,"Ymdhis").rand(10,99);
    			$GLOBALS['db']->autoExecute(DB_PREFIX."store_pay_order",$order_data,'INSERT','','SILENT');
    			$order_id = intval($GLOBALS['db']->insert_id());
    		}while($order_id==0);
    
    		$root['order_id'] = $order_id;

    		output($root);
    	}
    }
    
    /**
     * 订单支付页面
     * 输入
     * order_id int 门店ID
     *
     * 输出
     * user_login_status int 用户登录状态
     *
     * order_info：array 订单信息
     * order_id ：int 订单号
     * has_account： int 是否显示余额支付方式
     * payment_list:付款方式列表
     * account_money：用户帐户中的余额
     * order_info['total_price']:总计金额
     * order_info['discount_price']：已优惠金额
     * pay_data:为输出订单的支付款项，包括  总计，已优惠，手续费
     * [pay_data] => Array
        (
            [0] => Array
                (
                    [name] => 总计
                    [price] => 1000
                )

            [1] => Array
                (
                    [name] => 已优惠
                    [price] => 100.0000
                )

        )
     */
    public function check() {

        //获取参数
        
    	$order_id = intval($GLOBALS['request']['order_id']);

        $user_info = $GLOBALS['user_info'];

        
        $root = array();

        if ((check_login() == LOGIN_STATUS_TEMP && $GLOBALS['user_info']['money'] > 0) || check_login() == LOGIN_STATUS_NOLOGIN) {
            $root['user_login_status']=0;
    		output($root, 0, "请先登录");
        }else{
        $root['user_login_status']=check_login();
        $order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."store_pay_order where id=".$order_id);
        
        	$root['location_id']=$order_info['location_id'];
        	if($order_info['pay_status']==2 || $order_info['order_status']==1 || $order_info['is_delete']==1){
        		output($root, 0, "非法订单");
        	}
        	
        	
        	//计算优惠
        	require_once APP_ROOT_PATH.'system/model/store_pay.php';
        	$count_data = count_store_pay_total($order_info['location_id'], $order_info['total_price'],0,0,0,0,$order_info['user_id'],$order_id);
        	//  $order_data['pay_amount'] =  $count_data['pay_price'];
        	
        	/*
        	$order_data_u['discount_price'] = $count_data['discount_price'];
        	$order_data_u['promote_ids'] = $count_data['promote_ids'];
        	$order_data_u['promote_data'] = $count_data['promote_data'];
        	$GLOBALS['db']->autoExecute(DB_PREFIX."store_pay_order",$order_data_u,'UPDATE',"id=".$order_info['id']);
        	*/
        	$order_info['total_price']-=$order_info['payment_fee'];
            $total_price = $order_info['total_price'];
            $order_id = $order_info['id'];
         $root['order_id'] = $order_id;
        
        $root['order_info']=$order_info;           
        
 

        if($total_price > 0)
                $show_payment = 1;
        else
                $show_payment = 0;
        $root['show_payment'] = $show_payment;
        if($GLOBALS['user_info']['money'] >= $total_price){
        	$root['has_account'] = 1;  //允许余额支付
        }else{
        	$root['has_account'] = 0;  //允许余额支付
        }
        
                
        //输出支付方式
        if ($GLOBALS['request']['from'] == 'wap') {
            //支付列表
            $sql = "select id, class_name as code, logo from " . DB_PREFIX . "payment where (online_pay = 2 or online_pay = 4 or online_pay = 5) and is_effect = 1";
        } else {
            //支付列表
            $sql = "select id, class_name as code, logo from " . DB_PREFIX . "payment where (online_pay = 3 or online_pay = 4 or online_pay = 5) and is_effect = 1";
        }
        if (allow_show_api()) {
            $payment_list = $GLOBALS['db']->getAll($sql);
        }

        foreach ($payment_list as $k => $v) {
            $directory = APP_ROOT_PATH . "system/payment/";
            $file = $directory . '/' . $v['code'] . "_payment.php";
            if (file_exists($file)) {
                require_once($file);
                $payment_class = $v['code'] . "_payment";
                $payment_object = new $payment_class();
                $payment_list[$k]['name'] = $payment_object->get_display_code();
            }

            if ($v['logo'] != "")
                $payment_list[$k]['logo'] = get_abs_img_root(get_spec_image($v['logo'], 40, 40, 1));
        }

        sort($payment_list);
        $root['payment_list'] = $payment_list;

        $root['page_title'] = "提交订单";
        $root['account_money'] = round($GLOBALS['user_info']['money'], 2);
        
        $show_pay_info=array();
        if($total_price > 0){
        	$pay_info=array();
        	$pay_info['name']='总计';
        	$pay_info['value']=format_price($order_info['total_price']);
        	$show_pay_info[]=$pay_info;
        }
        
        if($order_info['discount_price'] > 0){
        	$pay_info=array();
        	$pay_info['name']='已优惠';
        	$pay_info['value']=format_price($order_info['discount_price']);
        	$show_pay_info[]=$pay_info;
        }
        $root['pay_data']=$show_pay_info;
        
        output($root);
       }
    }

    /**
	 * 订单支付页，点击付款方式的请求接口
	 * 
	 * 输入:
	 * order_id: int 订单ID
	 * all_account_money：是否全额支付
	 * payment_id：付款方式的ID
	 * 
	 * 输出:
	 * status:int 状态 0:失败 1:成功
	 * info: string 失败的原因
	 * 以下参数为成功时返回
	 * pay_status: int 支付状态 0:未支付 1:已支付 
	 * order_id: int 订单ID
	 * order_sn: string 订单号
	 * order_info:array 订单信息
	 * pay_info: string 显示的信息
	 * 
	 * pay_status ：int 订单是否已经支付成功，当pay_status=1时，显示pay_info信息
	 * 
     * pay_data:为输出订单的支付款项，包括  总计，已优惠，手续费
     * [pay_data] => Array
        (
            [0] => Array
                (
                    [name] => 总计
                    [price] => 1000
                )

            [1] => Array
                (
                    [name] => 已优惠
                    [price] => 100.0000
                )

        )
	 */
	public function count_store_pay_total()
	{
		global_run();
                $user_info = $GLOBALS['user_info'];
                
        $root = array();
        if (check_login() == LOGIN_STATUS_TEMP || check_login() == LOGIN_STATUS_NOLOGIN) {
            output($root, -1, "请先登录");
        }        
                
	
		$order_id = intval($GLOBALS['request']['order_id']);
		$all_account_money = intval($GLOBALS['request']['all_account_money']);
                //支付ID
                $payment_id = intval($GLOBALS['request']['payment_id']);
                $bank_id = intval($GLOBALS['request']['bank_id']);
                

		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."store_pay_order where id = ".$order_id);
                
		if(empty($order_info))
		{
			output(array(),0,"订单不存在");
		}
                
		$order_info['total_price']-=$order_info['payment_fee'];
                
         $order_info['payment_id'] = $payment_id;
		
		$root['order_sn'] = $order_info['order_sn'];
		$root['order_id'] = $order_id;
		$root['order_info']=$order_info;
		if($order_info['pay_status']==2)
		{
			if($order_info['type']==0)
			{
				
				
				$root['pay_status'] = 1;					
				$root['pay_info'] = '订单已经收款';
				output($root);
			}
		}
		else
		{
			
			
			
			require_once APP_ROOT_PATH.'system/model/store_pay.php';
                        
			$data = count_store_pay_total($order_info['location_id'], $order_info['total_price'], $payment_id,$bank_id,0,$all_account_money,$user_info['id'],$order_id);
		
			
			$pay_price = $data['pay_price'];
                        if($pay_price>0){
                            $up_order_data = array("payment_id"=>$payment_id,"bank_id"=>$bank_id);
                        }else{
                            $up_order_data = array("payment_id"=>0,"bank_id"=>0);
                        }
                        //选择了支付方式更新订单表
                        $GLOBALS['db']->autoExecute(DB_PREFIX."store_pay_order",$up_order_data,"UPDATE","id=".$order_id);

            $root['pay_price'] = $pay_price;
            $root['payment_fee'] = $data['payment_fee'];
                        
			$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$order_info['payment_id']);

			if($pay_price>0 && empty($payment_info))
			{
				output($root,0,"请选择支付方式");
			}


			if($all_account_money==0 && $payment_info && $pay_price>0){
				if($GLOBALS['request']['from']=="wap")
				{
					if ($payment_info['online_pay']!=2&&$payment_info['online_pay']!=4&&$payment_info['online_pay']!=5)
					{
						output($root,0,"该支付方式不支持wap支付");
					}
				}
				else
				{
					if ($payment_info['online_pay']!=3&&$payment_info['online_pay']!=4&&$payment_info['online_pay']!=5)
					{
						output($root,0,"该支付方式不支持手机支付");
					}
				}
			}

			

			$root['pay_status'] = 0;
			$show_pay_info=array();
			if($order_info['total_price'] > 0){
				$pay_info=array();
				$pay_info['name']='总计';
				$pay_info['value']=format_price($order_info['total_price']);
				$show_pay_info[]=$pay_info;
			}
			
			if($order_info['discount_price'] > 0){
				$pay_info=array();
				$pay_info['name']='已优惠';
				$pay_info['value']=format_price($order_info['discount_price']);
				$show_pay_info[]=$pay_info;
			}
			
			if( $root['payment_fee'] > 0){
				$pay_info=array();
				$pay_info['name']='手续费';
				$pay_info['value']= format_price($root['payment_fee']);
				$show_pay_info[]=$pay_info;
			}
			
			$root['pay_data']=$show_pay_info;
			
			//$root['payment_code'] = $payment_code;
			output($root);
		}		
	}
	/**
	 * 订单支付页，点击付款方式的请求接口
	 *
	 * 输入:
	 * order_id: int 订单ID
	 * all_account_money：是否全额支付
	 * payment_id：付款方式的ID
	 *
	 * 输出:
	 * status:int 状态 0:失败 1:成功
	 * info: string 失败的原因
	 * 以下参数为成功时返回
	 * pay_status: int 支付状态 0:未支付 1:已支付 2：付款单号重复支付
	 *
	 * order_id: int 订单ID
	 * payment_notice_id：当pay_status为0和2时，返回的付款单号
	 * 
	 * 
	 */
        public function done(){
        	
        	
        	global_run();
        	if(check_login() == LOGIN_STATUS_TEMP || check_login() == LOGIN_STATUS_NOLOGIN)
        	{

        		$root['user_login_status']=0;
        		output($root,0,"请先登录");
        		 
        		
        	}else{
        		
        		$root['user_login_status']=1;
        		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
        	
        		$order_id = intval($GLOBALS['request']['order_id']);
        		$all_account_money = intval($GLOBALS['request']['all_account_money']);
        		$payment_id = intval($GLOBALS['request']['payment_id']);
        		$bank_id = intval($GLOBALS['request']['bank_id']);
        		
        		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."store_pay_order where id = ".$order_id);
        		
        		if(empty($order_info))
        		{
        			output($root,0,"订单不存在");
        		}

        		
        		require_once APP_ROOT_PATH.'system/model/store_pay.php';
        		
        		$data = count_store_pay_total($order_info['location_id'], $order_info['total_price']-$order_info['payment_fee'], $payment_id,$bank_id,0,$all_account_money,$order_info['user_id'],$order_id);
        		

        		if($data['pay_price']>0 && empty($data['payment_info']))
        		{
        			output($root,0,"请选择支付方式");
        		}
        			
        		$root['data']=$data;
        		
        		
        		$now = NOW_TIME;
        		
        		$order['payment_id'] = $payment_id;
        		$order['payment_fee'] = $data['payment_fee'];
        		$total_price=$order_info['total_price']-$order_info['payment_fee'];
        		$order['total_price'] = $total_price+$order['payment_fee'];
        		$GLOBALS['db']->autoExecute(DB_PREFIX."store_pay_order",$order,'UPDATE','id='.$order_id,'SILENT');
        		
        		$account_money=$data['account_money'];
        		require_once APP_ROOT_PATH.'system/model/cart.php';
        		//1. 余额支付
        		$account_pid = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
        		if(floatval($account_money) > 0 && $all_account_money==1)
        		{
        			$payment_notice_id = make_store_pay_payment_notice($account_money,$order_id,$account_pid);
        			require_once APP_ROOT_PATH."system/payment/Account_payment.php";
        			$account_payment = new Account_payment();
        			$account_payment->get_payment_code($payment_notice_id);
        		}
        			
        		
        		//3. 相应的支付接口
        		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id=".$payment_id);
        		if($payment_info && $data['pay_price']> 0)
        		{
        			$payment_notice_id = make_store_pay_payment_notice($data['pay_price'],$order_id,$payment_info['id']);
        			//创建支付接口的付款单
        		}
        		
        		$rs = store_pay_order_paid($order_id);
        		
        		if($rs){
        			//正常支付，支付完成
        		
        			$root['pay_status']=1;
        			$root['order_id'] = $order_id;
        			output($root);
        				
        		}
        		else
        		{
        			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."store_pay_order where id = ".$order_id);
        			if($order_info['pay_status'] == 2)
        			{   //付款单号重复支付,当前支付的退到会员帐户
        		
        				$root['pay_status']=2;
        				$root['order_id'] = $order_id;
        				$root['payment_notice_id']=$payment_notice_id;
        				output($root);
        			}else{ //正常支付，还有部分未完成
        				$root['pay_status']=0;
        				$root['order_id'] = $order_id;
        				$root['payment_notice_id']=$payment_notice_id;
        		
        			}
        			output($root);
        		}
        		

        	}
        	
            
        }
        
        /**
         * 输入金额，获取优惠金额接口
         * 输入
         * location_id int 门店ID
         * money：float 输入金额
         *
         * pay_price : float 应付金额
         * money ：float 输入金额
         * promote:array 优惠数据
         * discount_price：float 为总的优惠金额
         * pay_price :float 应付金额
         * id：array 为每一个优惠政策下的优惠数据，其中的 discount_price为该优惠政策下的优惠金额
         * [promote] => Array
	        (
	            [discount_price] => 100
	            [id] => Array
	                (
	                    [0] => Array
	                        (
	                            [pay_amount] => 900
	                            [discount_price] => 100
	                            [id] => 18
	                        )
	
	                )
	
	            [pay_price] => 900
	        )
         */
        public function promote() {
        
        	//获取参数
        
        	$root = array();
        	$money = floatval($GLOBALS['request']['money']);
     
        	$location_id = intval($GLOBALS['request']['location_id']);
        	
        	$location_info = $GLOBALS['db']->getRow("select id,name,supplier_id from " . DB_PREFIX . "supplier_location where id=" . $location_id);
        	if(!$location_info){
        		 
        		output($root,0,'无此商家');
        	}
        	
        	$root['location_id'] = $location_info['id'];
        	$root['location_name'] = $location_info['name'];
        	
        	$supplier_id=$location_info['supplier_id'];
        	$directory = APP_ROOT_PATH . "system/promote/";
        	$promote=array();
        	$promote['discount_price']=0;
        	
        	$promote_arr=array();
        	$promote_row = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "promote where supplier_id = " . $supplier_id);
        	if($promote_row){
        		foreach($promote_row as $k=>$v){
        			$file = $directory . '/' . $v['class_name'] . "_promote.php";
        			if (file_exists($file)) {
        				require_once($file);
        				$promote_class = $v['class_name'] . "_promote";
        				$promote_object = new $promote_class();
        				$tmp_arr = array();
        				$tmp_arr = $promote_object->count_store_pay($money,$v['config']);
        				$v['discount_price']=$tmp_arr['discount_price'];
        				$v['descriptions']=$v['description'];
        				$promote_arr[]=$v;
        				$promote['discount_price']+=$tmp_arr['discount_price'];
        			}
        		}
        	
        	}
        
        	$root['promote']=$promote_arr;
        	$root['discount_price']=$promote['discount_price'];
        	$root['pay_price']=$money-$promote['discount_price'];
        	
        	$root['money']=$money;
        	//$root['promote_data']=$promote;
        	
        	output($root);
        	
        }
        
}

?>