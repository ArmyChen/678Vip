<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_withdrawalModule extends MainBaseModule
{

    /**
     * 	提现列表
     *
     * 	 输入:
     *  page    [int] 分页
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  
     *  有权限的情况下返回以下内容
     *  
     *[supplier_info] => Array      ：array 商户信息
        (
            [id] => 35              ：int 商户编号
            [name] => 美丽人生摄影工作室      ：string 商户名称
            [money] => 191          ：float 允许提现总金额
            [bank_name] => 工商银行福州分行     ：绑定的银行
            [bank_info] => 尾号0794   ：绑定的银行卡号信息
        )

        [item] => Array     ：array  提现的历史记录
        (
            [0] => Array
                (
                    [create_time] => 1433812556         ：string 提现日期
                    [money] => 1                ：float  提现金额
                    [status] => 待审核     ：string 提现申请状态
                    [f_create_time] => 2015-06-09       ：string 格式化提现日期
                )

        )
      

     */
	private $slid = '';
	private $slname = '';
  	function __construct()
    {
        global_run();
		$account_info = $GLOBALS['account_info'];	
		//var_dump($account_info);
		$sql="SELECT  b.id,b.name FROM fanwe_supplier_location b 
LEFT JOIN fanwe_supplier_account_location_link c ON b.id = c.location_id
LEFT JOIN fanwe_supplier_account d ON c.account_id = d.id
WHERE d.account_name = '".$account_info['account_name']."'";
	$slid_info=$GLOBALS['db']->getRow($sql);
	if (!$slid_info){
	$supplier_id=$account_info['supplier_id'];
	$sql="SELECT id,`name` from fanwe_supplier_location where supplier_id=$supplier_id and is_main=1";
	$slid_info=$GLOBALS['db']->getRow($sql);
	}	
	$slid=$slid_info['id'];	
	$slname=$slid_info['name'];
	$this->slid=$slid;
	$this->slname=$slname;	
	} 
	 
	 
	 
	public function index(){
	    
	    /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
		$location_id = $this->slid; //门店ID

        /*获取参数*/
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
  
	    //返回商户权限
	    if(!check_module_auth("withdrawal")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作权限");
	    }else{
	        $root['is_auth'] = 1;
	    }
	    
		$supplier_info=$GLOBALS['db']->getRow("select id,name,money,bank_name,bank_info,tel from  ".DB_PREFIX."supplier_location where id=".$location_id);
        $supplier_info['money'] = round($supplier_info['money'],2);
		$mobile = $supplier_info['tel'];
		
		//$location_money=$GLOBALS['db']->getOne("select sum(money_ys) from  orders  where (zffs='alipay' or zffs='weixipay' or zffs='bestpay' or zffs='jdpay' or zffs='qqpay' or zffs='baidupay') and zhifustatus=1 and shoukuanfang=0 and mid=".$location_id);
        //$supplier_info['location_money']=$location_money;
		
		/*/2016-10-7
		$end_time_s = NOW_TIME; //当前时间
		$sqlstr = "WHERE shoukuanfang=0 and zhifustatus=1 and mid =  '".$location_id."'";	
		$sqlstr .=" and otime < '".$end_time_s."'";			
		$sql="SELECT sum(cmoney) as money FROM orders_pay ".$sqlstr;		
		$total_in_money = $GLOBALS['db']->getone($sql);		
		$sqlstrlogo = "WHERE (type>4) and location_id =  '".$location_id."'";	
		$sqlstrlogo .=" and create_time < '".$end_time_s."'";		
		$sql="SELECT sum(money) FROM fanwe_location_money_log ".str_replace("type>4","type=4",$sqlstrlogo);
		$total_in_money_sub = $GLOBALS['db']->getone($sql);  //从子账户结转来的钱
		
		$total_in_money=$total_in_money+$total_in_money_sub;
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where location_id=".$location_id." and supplier_id = ".$supplier_id." and type=1 and status = 0"));
		
		$sql="SELECT sum(money) FROM fanwe_location_money_log ".$sqlstrlogo;
		$total_hadpay = $GLOBALS['db']->getone($sql);  //已经打过的款合计	
		$supplier_info['location_money']=round($total_in_money-$total_hadpay-$submitted_money,2);
		
		2016-10-7 Over
		*/
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where location_id=".$location_id." and supplier_id = ".$supplier_id." and type=1 and status = 0"));
		
		stat_do_day($supplier_id);	
		//获取可提money
		$get_ktmoney=get_ktmoney($location_id);
		//var_dump($get_ktmoney);
		$total_in_money=$get_ktmoney['total_in_money'];
		$total_hadpay=$get_ktmoney['total_hadpay'];		
		$supplier_info['location_money']=$get_ktmoney['total_ktmoney']-$submitted_money;
		
		
		
	    if($supplier_info['bank_info']){
	        $bank_info_str = str_replace(" ", "", $supplier_info['bank_info']);
	        $supplier_info['bank_info'] = "尾号".substr($bank_info_str, -4);
	    }else{
	        $supplier_info['bank_info'] = '';
	    }
	    $root['is_band_bank'] = $supplier_info['bank_info']?1:0;
	    
	    $supplier_info['money'] = round($supplier_info['money'],2);
	    
	    
	    $root['supplier_info'] = $supplier_info;
		
		if ($account_info['is_main']==1){
		$sqlslidstr="and location_id in (".implode(",",$account_info['location_ids']).")";	
		}else{
		$sqlslidstr="and location_id=".$slid;		
		}		
		
	    //分页
	    $page_size = PAGE_SIZE;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    $list = $GLOBALS['db']->getAll("select create_time,money,status,reason,type,location_id from ".DB_PREFIX."supplier_money_submit  where supplier_id=".$supplier_id." ".$sqlslidstr." order by status asc,create_time desc limit ".$limit);
	    foreach($list as $k=>$v){
			if($v['status']==1){
				$list[$k]['status']="已到账";
			}else if($v['status']==2)
			{
				$list[$k]['status']="已拒绝";
				if($v['reason'])
					$list[$k]['status'].=":".$v['reason'];
			}
			else{
				$list[$k]['status']="进行中";
			}
			$list[$k]['f_create_time']= to_date($v['create_time'],'Y-m-d')."(".$v["location_id"].")";
			$list[$k]['money'] = round($v['money'],2);
			
	    }
	    
	    $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_money_submit  where supplier_id=".$supplier_id);

        
	    //分页
	    $page_total = ceil($count/$page_size);
	    

	    $root['item'] = $list?$list:array();
	    $root['page_title'] = "商户提现";
	    $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        
        
        output($root);
    }
    
    
    
    /**
     * 	提现表单页面
     *
     * 	 输入:
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *
     *  有权限的情况下返回以下内容
     *
     *[supplier_info] => Array      ：array 商户信息
        (
            [id] => 35              ：int 商户id
            [name] => 美丽人生摄影工作室      ：string  商户名称
            [money] => 191          ：float  商户可以提现的总金额
            [bank_info] => 6222 6007 2000 7040 794      ：string 商户的银行卡号
        )
    
    [mobile] => 13677890998     ：string 商户的验证手机号
    [sms_on] => 0               ：短信开启和关闭的状态   0 关闭  ，1开启
    
     */
    public function submit_form(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $this->slid; //门店ID
        
        /*获取参数*/
      
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("withdrawal")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        $supplier_info=$GLOBALS['db']->getRow("select id,name,money,bank_info,tel from  ".DB_PREFIX."supplier_location where id=".$location_id);
        $supplier_info['money'] = round($supplier_info['money'],2);
		$mobile = $supplier_info['tel'];
		
		/*/$location_money=$GLOBALS['db']->getOne("select sum(money_ys) from  orders  where (zffs='alipay' or zffs='weixipay' or zffs='bestpay' or zffs='jdpay' or zffs='qqpay' or zffs='baidupay') and zhifustatus=1 and shoukuanfang=0 and mid=".$location_id);
      //  $supplier_info['location_money']=$location_money;
		
       //2016-10-7
		$end_time_s = NOW_TIME; //当前时间
		$sqlstr = "WHERE shoukuanfang=0 and zhifustatus=1 and mid =  '".$location_id."'";	
		$sqlstr .=" and otime < '".$end_time_s."'";			
		$sql="SELECT sum(cmoney) as money FROM orders_pay ".$sqlstr;		
		$total_in_money = $GLOBALS['db']->getone($sql);		
		$sqlstrlogo = "WHERE (type>4) and location_id =  '".$location_id."'";	
		$sqlstrlogo .=" and create_time < '".$end_time_s."'";		
		$sql="SELECT sum(money) FROM fanwe_location_money_log ".str_replace("type>4","type=4",$sqlstrlogo);
		$total_in_money_sub = $GLOBALS['db']->getone($sql);  //从子账户结转来的钱
		
		$total_in_money=$total_in_money+$total_in_money_sub;
		
		$sql="SELECT sum(money) FROM fanwe_location_money_log ".$sqlstrlogo;
		$total_hadpay = $GLOBALS['db']->getone($sql);  //已经打过的款合计

         //2016-11-23 已经提交的      
		
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where location_id=".$location_id." and supplier_id = ".$supplier_id." and type=1 and status = 0"));
		
		
		$supplier_info['location_money']=round($total_in_money-$total_hadpay-$submitted_money,2);
		
		//2016-10-7 Over
		*/
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where location_id=".$location_id." and supplier_id = ".$supplier_id." and type=1 and status = 0"));
		
		$get_ktmoney=get_ktmoney($location_id);
		//var_dump($get_ktmoney);
		$total_in_money=$get_ktmoney['total_in_money'];
		$total_hadpay=$get_ktmoney['total_hadpay'];		
		$supplier_info['location_money']=$get_ktmoney['total_ktmoney']-$submitted_money;
        
        if($supplier_info['bank_info'] == ''){
            output($root,0,"还没有绑定银行卡，请先在电脑上绑定！");
        }
        
        if($mobile == ''){
            output($root,0,"还没有绑定手机号，请先在电脑上绑定！");
        }
        
        $root['supplier_info'] = $supplier_info;
        $root['mobile'] = $mobile;
        $root['sms_on'] = app_conf("SMS_ON");
        
        $root['page_title'] = $supplier_info['name'];

        output($root);
    }
    
    
    /**
     * 	提现提交
     *
     * 	 输入:
     *  money    [float] 提现金额
     *  
     *  没开启短信时候传密码/其中一个必填
     *  sms_verify  [string] 短信验证码
     *  pwd_verify     [string] 密码 
     *  
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *
     *  有权限的情况下返回以下内容
     *
     *  
    
    
     */
    public function do_submit(){
        /*初始化*/
		$root = array();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $this->slid; //门店ID
		 
        /*获取参数*/
        $money = floatval($GLOBALS['request']['money']);
        $ttmoney = floatval($GLOBALS['request']['ttmoney']);
        $sms_verify = strim($GLOBALS['request']['sms_verify']);
        $account_password = strim($GLOBALS['request']['pwd_verify']);
        $type = strim($GLOBALS['request']['tixian_type']);
       
        
		
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("withdrawal")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        
        if(app_conf("SMS_ON")==1){
            //短信码验证
            
            $mobile_phone=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where id=".$location_id);
            if($sms_verify == ''){
                output($root,0,"请输入手机验证码");
            }
            $sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
            $GLOBALS['db']->query($sql);
            	
            $mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
        
            if($mobile_data['code']!=$sms_verify)
            {
                output($root,0,"手机验证码错误");
            }
        }else{
            
            if($account_password == ''){
                output($root,0,"请输入密码");
            }
            if(md5($account_password)!=$account_info['account_password']){
                output($root,0,"密码不正确");
            }
        }
        
		if($money<=0){output($root,0,"请输入正确的提现金额");}
			
		
		if ($type==0){
		
		$ktmoney=$GLOBALS['db']->getone("select money from  ".DB_PREFIX."supplier_location where id=".$location_id);
		
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where location_id=".$location_id." and supplier_id = ".$supplier_id." and type=0 and status = 0"));
		if($submitted_money+$money>$ktmoney)
		{
			output($root,0,"提现超额1");		
		}
		}else{
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where location_id=".$location_id." and supplier_id = ".$supplier_id." and type=1 and status = 0"));
		//取得可提MONEY
		$get_ktmoney=get_ktmoney($location_id);
		$ktmoney=$get_ktmoney['total_ktmoney'];
		
		if($submitted_money+$money>$ktmoney)
		{
			output($root,0,"提现超额2");		
		}
		}
		
        
        $withdraw_data = array();
        $withdraw_data['supplier_id'] = $supplier_id;
		$withdraw_data['location_id'] = $location_id;
        $withdraw_data['money'] = $money;
        $withdraw_data['create_time'] = NOW_TIME;
		$withdraw_data['type'] = $type;
        $withdraw_data['status'] = 0;		
			
        
        $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_money_submit",$withdraw_data);
        
        $GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");

        output($root,1,"提现申请提交成功，请等待管理员审核");
		
    }
        
    /**
     * 绑定银行卡页面接口
     * 
     * 输入：
     * 
     * 输出：
     * biz_user_status [int] 商户登录状态
     * is_auth  [int] 是否有权限操作这个接口
     * 
     * 有权限返回以下
     * sms_on [int] 短信是否开启
     * mobile [string] 短信发送的手机号
     */
    public function bindbank(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        
        /*获取参数*/
        
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("withdrawal")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        //短信是否打开
        $root['sms_on'] = app_conf("SMS_ON");
        $root['mobile'] = $account_info['mobile'];
        $root['page_title'] = "银行卡绑定";
        output($root);
        
    }
    
    /**
     * 绑定银行卡提交接口
     *
     * 输入：
     * bank_name    ：string 开户行
     * bank_num    ：string 卡号
     * bank_user    ：string 持卡人
     * sms_verify    ：string 短信验证码
     * pwd_verify    ：string 登录密码
     * 
     * 输出：          
     * biz_user_status [int] 商户登录状态
     * is_auth  [int] 是否有权限操作这个接口
     *
     * status   状态
     * info     错误消息
     */
    public function do_bindbank(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
    
        /*获取参数*/
        $bank_name = strim($GLOBALS['request']['bank_name']);
        $bank_num = strim($GLOBALS['request']['bank_num']);
        $bank_user = strim($GLOBALS['request']['bank_user']);
        
        $sms_verify = strim($GLOBALS['request']['sms_verify']);
        $pwd_verify = strim($GLOBALS['request']['pwd_verify']);
    
    
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
    
        //返回商户权限
        if(!check_module_auth("withdrawal")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
    
    
        if(app_conf("SMS_ON")==1){
            //短信码验证
    
            $mobile_phone=$GLOBALS['db']->getOne("select mobile from ".DB_PREFIX."supplier_account where supplier_id=".$supplier_id." and is_main=1");
            if($sms_verify == ''){
                output($root,0,"请输入手机验证码");
            }
            $sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
            $GLOBALS['db']->query($sql);
             
            $mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
    
            if($mobile_data['code']!=$sms_verify)
            {
                output($root,0,"手机验证码错误");
            }
        }else{
    
            if($pwd_verify == ''){
                output($root,0,"请输入密码");
            }
            if(md5($pwd_verify)!=$account_info['account_password']){
                output($root,0,"密码不正确");
            }
        }
    
        
        $updata = array();
        $updata['bank_name'] = $bank_name;
        $updata['bank_info'] = $bank_num;
        $updata['bank_user'] = $bank_user;
    
    
        $GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$updata,'UPDATE',"id = ".$supplier_id);
    
        $GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
    
        output($root,1,"银行卡绑定成功");
    }
   
    
}
?>

