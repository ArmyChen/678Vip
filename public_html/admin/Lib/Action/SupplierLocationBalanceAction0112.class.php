<?php

// +----------------------------------------------------------------------

// | Fanweo2o商业系统 最新版V3.03.3285  含4个手机APP。

// +----------------------------------------------------------------------

// | 购买本程序，请联系QQ：78282385  旺旺名：alert988

// +----------------------------------------------------------------------

// | 淘宝购买地址：https://shop36624490.taobao.com/

// +----------------------------------------------------------------------



class SupplierLocationBalanceAction extends CommonAction{



	public function index()

	{

		$mid = intval($_REQUEST['id']);	
        $supplier_info = M("supplier_location")->getById($mid); //得到门店信息
		if(!$supplier_info)
		{
			$this->error("非法的门店ID");
		}			
		
		
		
		
        if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间	
     	$start=date("Y-m-d", strtotime('-180 day'));				
		$startend=strtotime($start)+181*24*3600-1;
		$startstr=strtotime($start);
        $begin_time=date("Y-m-d H:i:s",$startstr); 
        $end_time=date("Y-m-d H:i:s",$startend); 
        }	
        
        $begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		$this->assign("begin_time",$begin_time);
		$this->assign("end_time",$end_time);
		
				
		$sqlstr = "WHERE a.shoukuanfang=0 and a.mid =  '".$mid."'";
		$sqllogstr="WHERE (type=6 or type=5 or type=7) and location_id=$mid ";
		if($begin_time){
			$sqlstr .=" and a.otime > '".$begin_time_s."'";
			$sqllogstr .=" and create_time > '".$begin_time_s."'";
			}
		if($end_time){
			$sqlstr .=" and a.otime < '".$end_time_s."'";
			$sqllogstr .=" and create_time < '".$end_time_s."'";
			
			}
			   	
				
		//$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		//$GLOBALS['tmpl']->assign("end_time",$end_time);
		//$GLOBALS['tmpl']->assign("slid",$mid);
		//echo "hdkfd";
		//echo $zhifustatus_str;
		
		 $listdata=array();//定义要去模板的变量
		 
		 /*
	     $sql="SELECT sum(a.pay_money) as money,a.pay_state FROM fanwe_shoukuan_alipay_log a ".$sqlstr." group by a.pay_state order by a.pay_state DESC ";
         $alipaylist = $GLOBALS['db']->getAll($sql);
		  foreach($alipaylist as $k=>$v){
		  if ($v['pay_state']==1){$listdata['alipay']=number_format($v['money'],2);} //收到的款
		  if ($v['pay_state']==0){$listdata['ralipay']=number_format($v['money'],2);} //退款
	     }
		 $listdata['alipay_name']=$zffsarr['alipay'];	
		 
		 $sql="SELECT sum(a.pay_money) as money,a.pay_state FROM fanwe_shoukuan_weixin_log a ".$sqlstr." group by a.pay_state order by a.pay_state DESC ";
		 $alipaylist = $GLOBALS['db']->getAll($sql);
		 
		 foreach($alipaylist as $k=>$v){
		  if ($v['pay_state']==1){$listdata['weixipay']=number_format($v['money'],2);} //收到的款
		  if ($v['pay_state']==0){$listdata['rweixipay']=number_format($v['money'],2);} //退款
	     }
		 $listdata['weixipay_name']=$zffsarr['weixipay'];	
		 
		 $sql="SELECT sum(a.pay_money) as money,a.pay_state FROM fanwe_shoukuan_bestpay_log a ".$sqlstr." group by a.pay_state order by a.pay_state DESC ";
		 $alipaylist = $GLOBALS['db']->getAll($sql);
		 
		 foreach($alipaylist as $k=>$v){
		  if ($v['pay_state']==1){
			  $listdata['bestpay']=number_format($v['money'],2);
			  } //收到的款
		  if ($v['pay_state']==0){
			  $listdata['rbestpay']=number_format($v['money'],2);
			  } //退款
	     }
		 $listdata['bestpay_name']=$zffsarr['bestpay'];	
		 
		 //2016-6-2 增加QQ和京东
		$sql="SELECT sum(a.pay_money) as money,a.pay_state FROM fanwe_shoukuan_jdpay_log a ".$sqlstr." group by a.pay_state order by a.pay_state DESC ";
		 $alipaylist = $GLOBALS['db']->getAll($sql);
		 
		 foreach($alipaylist as $k=>$v){
		  if ($v['pay_state']==1){$listdata['jdpay']=number_format($v['money'],2);} //收到的款
		  if ($v['pay_state']==0){$listdata['rjdpay']=number_format($v['money'],2);} //退款
	     }
		 $listdata['jdpay_name']=$zffsarr['jdpay'];	
		 
		$sql="SELECT sum(a.pay_money) as money,a.pay_state FROM fanwe_shoukuan_qqpay_log a ".$sqlstr." group by a.pay_state order by a.pay_state DESC ";
		 $alipaylist = $GLOBALS['db']->getAll($sql);
		 
		 foreach($alipaylist as $k=>$v){
		  if ($v['pay_state']==1){$listdata['qqpay']=number_format($v['money'],2);} //收到的款
		  if ($v['pay_state']==0){$listdata['rqqpay']=number_format($v['money'],2);} //退款
	     }
		 $listdata['qqpay_name']=$zffsarr['qqpay'];	
		 
		 */
		 
		 $sql="SELECT sum(a.cmoney) as money,a.zhifustatus,a.zffs FROM orders_pay a ".$sqlstr." and a.zhifustatus=1 group by a.zffs order by a.zhifustatus ASC ";
		//echo $sql;
		//退款SQL
		$tsql="SELECT sum(a.cmoney) as money,a.zhifustatus,a.zffs FROM orders_pay a ".$sqlstr." and a.zhifustatus=9 group by a.zffs order by a.zhifustatus ASC ";
        
		//echo $tsql;
		
		
		$shoukuan_data = $GLOBALS['db']->getAll($sql);
        $tuikuan_data = $GLOBALS['db']->getAll($tsql);
		
		foreach($shoukuan_data as $k=>$v){		  
		$listdata[$v['zffs']]=round($v['money'],2);
		$listdata[$v['zffs'].'_name']=$zffsarr[$v['zffs']];
        $listdata['zsy']=$listdata['zsy']+$listdata[$v['zffs']];
	    }
		foreach($tuikuan_data as $k=>$v){		  
		$listdata['r'.$v['zffs']]=round($v['money'],2);	
		$listdata['ztk']=$listdata['ztk']+$listdata['r'.$v['zffs']];
	    }
		$listdata['xse']=$listdata['zsy']+$listdata['ztk'];  //三种支付方式收到的款
		 
		
		
		
		//$listdata['zsy']=$listdata['alipay']+$listdata['weixipay']+$listdata['bestpay'];
		//$listdata['ztk']=$listdata['ralipay']+$listdata['rweixipay']+$listdata['rbestpay'];  //TK
		//$listdata['xse']=$listdata['zsy']+$listdata['ztk'];  //三种支付方式收到的款
		$sql="SELECT sum(money) FROM fanwe_location_money_log ".str_replace("type=6 or type=5 or type=7","type=4",$sqllogstr);
		
		$listdata['kejiekuan'] = $GLOBALS['db']->getone($sql);
		
		$listdata['zsy']=$listdata['alipay']+$listdata['hbpay']+$listdata['weixipay']+$listdata['bestpay']+$listdata['jdpay']+$listdata['qqpay']+$listdata['kejiekuan'];
		$listdata['ztk']=$listdata['rhbpay']+$listdata['ralipay']+$listdata['rweixipay']+$listdata['rbestpay']+$listdata['rjdpay']+$listdata['rqqpay'];  //TK
		$listdata['xse']=$listdata['zsy']+$listdata['ztk'];  //收到的款
		
		
		 $sql="SELECT sum(money) FROM fanwe_location_money_log ".$sqllogstr;
		 $total_hadpay = $GLOBALS['db']->getone($sql);
		 
		 
		 
		 $listdata['hadpay']=$total_hadpay; //已经打款
		 $listdata['nopay']=$listdata['zsy']-$total_hadpay; //未打款
		 	 

		 
		
        //数据准备完成
		
			
	   $type = intval($_REQUEST['type']);
		if($type!=1&&$type!=3&&$type!=5&&$type!=4&&$type!=7&&$type!=8&&$type!=9&&$type!=6)
		$type = 1;
		$this->assign("type",$type);
		$this->assign("supplier_info",$supplier_info);	
		$balance_title = "销售明细";
		if($type==3)
			$balance_title = "消费明细";
		if($type==4)
			$balance_title = "退款明细";
		if($type==5)
			$balance_title = "打款明细";	
		if($type==6)
			$balance_title = "消费明细";	
	    if($type==7)
			$balance_title = "结转明细";
		if($type==8)
			$balance_title = "充值明细";
		if($type==9)
			$balance_title = "冻结明细";
		$this->assign("type",$type);
		
		
	
		
		
		
	//分页
	    $page_size = 2;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	     // echo $limit; 
			
		
		
		

		if($begin_time&&$end_time)

			$balance_title = $begin_time."至".$end_time." ".$balance_title;

		elseif($begin_time)

			$balance_title = $begin_time."至今 ".$balance_title;

		elseif($end_time)

			$balance_title = "至".$end_time." ".$balance_title;
		
	

		$this->assign("balance_title",$balance_title);

		

		$map['location_id'] = $mid;

		$map['type'] = $type;

		$map['money'] = array("gt",0);

		if($begin_time_s&&$end_time_s)

		{

			$map['create_time'] = array("between",array($begin_time_s,$end_time_s));

		}

		elseif($begin_time_s)

		{

			$map['create_time'] = array("gt",$begin_time_s);

		}

		elseif($end_time_s)

		{

			$map['create_time'] = array("lt",$end_time_s);

		}



		if (method_exists ( $this, '_filter' )) {

			$this->_filter ( $map );

		}
    
		//$model = M ("location_money_log");
		$model = D ("SupplierMoneyLog");
		
		$list=$model->where($map)->order('id desc')->select(); 
	    $this->assign("list",$list);
		 
		$sum_money = $model->where($map)->sum("money");
		$this->assign("sum_money",$sum_money);
		$voList = $this->get("list");
		$page_sum_money = 0;
		foreach($voList as $row)

		{
			$page_sum_money+=floatval($row['money']);
		}
		$this->assign("page_sum_money",$page_sum_money);
		$this->assign("listdata",$listdata);
		
	     $locationlog = D ("location_money_log");
		
         $locallog=$locationlog->where($map)->order('id desc')->select(); 
		 
		 $page_sum_money2 = 0;
		  foreach($locallog as $row)

		{
			$page_sum_money2+=floatval($row['money']);
		}
		 $this->assign("page_sum_money2",$page_sum_money2);
		// var_dump($locallog);
		 $this->assign("list2",$locallog);
		 $this->display ();

		
		
		
		
		return;

	}

	

	

	public function foreverdelete() {

		//彻底删除指定记录

		$ajax = intval($_REQUEST['ajax']);

		$id = $_REQUEST ['id'];

		if (isset ( $id )) {

			$condition = array ('id' => array ('in', explode ( ',', $id ) ) );

			

			$list = M("location_money_log")->where ( $condition )->delete();

				

			if ($list!==false) {

				save_log(l("FOREVER_DELETE_SUCCESS"),1);

				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);

			} else {

				save_log(l("FOREVER_DELETE_FAILED"),0);

				$this->error (l("FOREVER_DELETE_FAILED"),$ajax);

			}

		} else {

			$this->error (l("INVALID_OPERATION"),$ajax);

		}

	}

}

?>