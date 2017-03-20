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

		$mid = $slid=intval($_REQUEST['id']);	
        $supplier_info = M("supplier_location")->getById($mid); //得到门店信息
		if(!$supplier_info)
		{
			$this->error("非法的门店ID");
		}	
		//执行统计程序
		stat_do_day($supplier_info['supplier_id']);	
		
		/*	
		
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
		*/	
		//20170112
		$get_last_jiekuan_info=$GLOBALS['db']->getRow("select create_time,money,endtime from fanwe_location_money_cashed where location_id=".$slid." order by create_time desc limit 1");
		
		if($get_last_jiekuan_info){
		$begin_time1=$get_last_jiekuan_info['endtime'];
		$create_time=$get_last_jiekuan_info['create_time'];
		$jieuye=round($get_last_jiekuan_info['money'],2);	//上次提款后结余的金额		
		}		
		$yesterday_time=to_date(NOW_TIME,"Y-m-d");		//截止昨天23：59分	
		$begin_time=$_REQUEST['begin_time']?$_REQUEST['begin_time']:$begin_time1;
		$end_time=strim($_REQUEST['end_time'])?strim($_REQUEST['end_time']):$yesterday_time;
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		$sqlstr="WHERE shoukuanfang=0 ";	
		$sqlstr .= " and location_id=".$slid;
		$sqlstrlogo = "WHERE location_id=".$slid;
		
		if($begin_time){
			$sqlstr .=" and stat_time > '".$begin_time."'";	
			$sqlstrlogo .=" and create_time > '".$begin_time_s."'";
		}
		if($end_time){
			$sqlstr .=" and stat_time < '".$end_time."'";			
		}
		if($create_time){		
		$sqlstrlogo .=" and create_time <= '".$create_time."'";
		}else{
		$sqlstrlogo .=" and create_time <= '".NOW_TIME."'";	
		}
		if($type){
		$showlogsql =" and type=".$type;	
		}
		
		$listdata=array();//定义要去模板的变量

        $datazffslist=array('weixipay','alipay','bestpay','jdpay','baidupay','hbpay','unipay','qqpay','xusercard','platformpay','meishika','guazhang','cash','scorepay','otherpay');
		$sumstr="";
		foreach($datazffslist as $val){
		$sumstr.=$val."+";
		}
		$sumstr = substr($sumstr,0,strlen($sumstr)-1); 
		
		$sql="SELECT sum(".$sumstr.") as zsy,sum(weixipay+alipay+bestpay+jdpay+baidupay+hbpay+qqpay+platformpay) as zds,sum(weixipay) as weixipay,sum(alipay) as alipay,sum(bestpay) as bestpay,sum(qqpay) as qqpay,sum(jdpay) as jdpay,sum(baidupay) as baidupay,sum(hbpay) as hbpay,sum(platformpay) as platformpay,sum(scorepay) as scorepay FROM fanwe_location_statements ".$sqlstr." and zhifustatus=1";
	    //echo $sql;
		$tsql="SELECT sum(".$sumstr.") as ztk,sum(weixipay) as weixipay,sum(alipay) as alipay,sum(bestpay) as bestpay,sum(qqpay) as qqpay,sum(jdpay) as jdpay,sum(baidupay) as baidupay,sum(hbpay) as hbpay,sum(platformpay) as platformpay,sum(scorepay) as scorepay FROM fanwe_location_statements ".$sqlstr." and zhifustatus=9";
	    $shoukuan_data = $GLOBALS['db']->getRow($sql);
	
        $tuikuan_data = $GLOBALS['db']->getRow($tsql);
		
		$sql="SELECT sum(money) as money,type FROM fanwe_location_money_log ".$sqlstrlogo." group by type";
		// echo $sql;
		 $total_pay = $GLOBALS['db']->getAll($sql);
		//var_dump($total_pay);
		 foreach($total_pay as $k=>$v){
			$totaljz_hadpay[$v['type']]=$v['money'];			 
		 }
		// var_dump($totaljz_hadpay);
		foreach($shoukuan_data as $k=>$v){
			$shoukuan_data[$k."_name"]=$zffsarr[$k];
		}
		
		$listdata=$shoukuan_data;//定义要去模板的变量		
	    $listdata['ztk']=$tuikuan_data['ztk'];
		
		
		$listdata['xse']=$listdata['zsy']+$listdata['ztk'];  //三种支付方式收到的款
		
		 $listdata['zds']=$listdata['zds']+$totaljz_hadpay['4']+$jieuye;  //加上结余额	 
		 
		 $listdata['hadpay']=$totaljz_hadpay['5']+$totaljz_hadpay['6']+$totaljz_hadpay['7']; //已经打款	
         $get_ktmoney=get_ktmoney($slid); 	 
		 $listdata['nopay']=$get_ktmoney['total_ktmoney']; //未打款金额





	   	
				
		//$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		//$GLOBALS['tmpl']->assign("end_time",$end_time);
		//$GLOBALS['tmpl']->assign("slid",$mid);
		//echo "hdkfd";
		//echo $zhifustatus_str;
		
		 
		 
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
		 	 
*/
		 
		
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
		$this->assign("tuikuan_data",$tuikuan_data);
		
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