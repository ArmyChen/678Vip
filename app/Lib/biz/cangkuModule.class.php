<?php

require APP_ROOT_PATH . 'app/Lib/page.php';

function werror()
{
}
function wsuccess()
{
}

class cangkuModule extends BizBaseModule{
    function __construct()
    {

        parent::__construct();

        global_run();
		$ywsort=array(
		"1"=>"盘盈",
		"2"=>"无订单入库",
		"3"=>"要货调入",
		"4"=>"初始库存",
		"5"=>"仓库调拨",
		"6"=>"盘亏",
		"7"=>"无订单出库",
		"8"=>"要货调出",
		"9"=>"退货"
		);
		$this->ywsort=$ywsort;
        $this->gonghuoren=array(
		"1"=>"临时客户",
		"2"=>"临时运输商",
		"3"=>"临时供应商",
		"4"=>"领料出库"		
		);
        $kcnx=array(
            "0"=>"默认",
            "1"=>"现制商品",
            "2"=>"预制商品",
            "3"=>"外购商品",
            "4"=>"原物料",
            "6"=>"半成品",

        );
        $this->kcnx=$kcnx;

		
        //$this->check_auth();

    }
	
	public function index()	{

	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
		
		
		$type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
		$ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';       
        
				
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当月的			
		$begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
		$end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }	
		$begin_time_s = strtotime($begin_time);
		$end_time_s = strtotime($end_time);	
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
		
		$sqlstr="where 1=1";
		$sqlstr.=' and ( a.slid='.$location_id.')';
		
		if($begin_time_s){
		$sqlstr .=" and a.ctime > ".$begin_time_s." ";
		}
		if($end_time_s){
		$sqlstr .=" and a.ctime < ".$end_time_s." ";
		}
		
		if ($type !=99 ){	
		$sqlstr .=" and a.type = ".$type." ";
		}
		if ($ywsortid !=99 ){	
		$sqlstr .=" and a.ywsort = ".$ywsortid." ";
		}
		if($_REQUEST['danjuhao'] !=""){
		$sqlstr .=" and a.danjuhao = '".$_REQUEST['danjuhao']."' ";	
		}
		
		
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlc="select count(a.id) from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";
		
		
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		$list=$GLOBALS['db']->getAll($sql);
			foreach($list as $kl=>$vl){
			$vl['ctime']=to_date($vl['ctime'],'m-d H:i:s');			
			$vl['detail']=unserialize($vl['dd_detail']);			
			if ($vl['type']==1){
			 $vl['type_show']	='入库';
			 $vl['gonghuo_show']	='供货人';			 
			}else{
			 $vl['type_show']	='出库';
			 $vl['gonghuo_show']	='收货人';	
			}
			$vl['ywsort']=$this->ywsort[$vl['ywsort']];			
			$vl['gonghuo']=$this->get_gonghuoren_name($supplier_id,$location_id,$vl['gonghuoren']);						
			$list[$kl]=$vl;			
		}
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
		$GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
		$GLOBALS['tmpl']->assign("type", $type);
		$GLOBALS['tmpl']->assign("begin_time", $begin_time);
		$GLOBALS['tmpl']->assign("end_time", $end_time);	
		$GLOBALS['tmpl']->assign("slid", $location_id);
		$GLOBALS['tmpl']->assign("danjuhao", $_REQUEST['danjuhao']);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "出入库明细");
		$GLOBALS['tmpl']->display("pages/cangku/cangku_log.html");
			
	}
	
	public function diaobo_list()	{

	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
		      
				
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当月的			
		$begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
		$end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }	
		$begin_time_s = strtotime($begin_time);
		$end_time_s = strtotime($end_time);	
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
		
		$sqlstr="where 1=1";
		$sqlstr.=' and slid='.$location_id;
		
		if($begin_time_s){
		$sqlstr .=" and ctime > ".$begin_time_s." ";
		}
		if($end_time_s){
		$sqlstr .=" and ctime < ".$end_time_s." ";
		}
				
		if($_REQUEST['danjuhao'] !=""){
		$sqlstr .=" and danjuhao = '".$_REQUEST['danjuhao']."' ";	
		}
		
		$cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$location_id);				
		$cangku_names = array();
        $cangku_names = array_reduce($cangku_list, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
        $sql="select * from ".DB_PREFIX."cangku_diaobo ".$sqlstr." order by id desc limit ".$limit;
        $sqlc="select count(id) from ".DB_PREFIX."cangku_diaobo ".$sqlstr;        
		
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		$list=$GLOBALS['db']->getAll($sql);
			foreach($list as $kl=>$vl){
			$vl['ctime']=to_date($vl['ctime'],'m-d H:i:s');			
			$vl['detail']=unserialize($vl['dd_detail']);			
			$vl['cid']= $cangku_names[$vl['cid']];			
			$vl['cidtwo']= $cangku_names[$vl['cidtwo']];
									
			$list[$kl]=$vl;			
		}
		/* 系统默认 */
		
		$GLOBALS['tmpl']->assign("begin_time", $begin_time);
		$GLOBALS['tmpl']->assign("end_time", $end_time);	
		$GLOBALS['tmpl']->assign("slid", $location_id);
		$GLOBALS['tmpl']->assign("danjuhao", $_REQUEST['danjuhao']);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "调拨明细");
		$GLOBALS['tmpl']->display("pages/cangku/diaobo_log.html");
			
	}
	
	public function stocktaking()	{ //仓库盘点历史

	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$location_id = $account_info['slid'];
		
		       
				
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当月的			
		$begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
		$end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }	
		$begin_time_s = strtotime($begin_time);
		$end_time_s = strtotime($end_time);	
		
		$page_size = 30;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
		
		$sqlstr="where 1=1";
		$sqlstr.=' and ( a.slid='.$location_id.')';
		$sqlstr.=' and ( c.slid='.$location_id.')';
		
		if($begin_time_s){
		$sqlstr .=" and a.ctime > ".$begin_time_s." ";
		}
		if($end_time_s){
		$sqlstr .=" and a.ctime < ".$end_time_s." ";
		}
			
		if($_REQUEST['uname'] !=""){
		$sqlstr .=" and a.uname = '".$_REQUEST['uname']."' ";	
		}
		
		$syy_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."syy where slid=".$location_id);
		$GLOBALS['tmpl']->assign("syy_list", $syy_list);
		$sql="select a.*,c.realname from ".DB_PREFIX."cangku_pandian_log a left join ".DB_PREFIX."syy c on a.uname=c.sname ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlc="select count(a.id) from ".DB_PREFIX."cangku_pandian_log a left join ".DB_PREFIX."syy c on a.uname=c.sname ".$sqlstr." order by a.id desc";
		
		
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		$list=$GLOBALS['db']->getAll($sql);
			foreach($list as $kl=>$vl){
			$vl['ctime']=to_date($vl['ctime'],'Y-m-d H:i:s');			
			$vl['detail']=unserialize($vl['loginfo']);	
			 foreach($vl['detail'] as $key=>$vey){
				$vl['detail'][$key]['mname'] =$GLOBALS['db']->getOne("SELECT name from fanwe_dc_menu where id=".$vey['mid']);
			 }
            $list[$kl]=$vl;			
		}
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
		$GLOBALS['tmpl']->assign("type", $type);
		$GLOBALS['tmpl']->assign("begin_time", $begin_time);
		$GLOBALS['tmpl']->assign("end_time", $end_time);	
		$GLOBALS['tmpl']->assign("slid", $location_id);
		$GLOBALS['tmpl']->assign("uname", $_REQUEST['uname']);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "盘点历史");
		$GLOBALS['tmpl']->display("pages/cangku/cangku_pandian_log.html");
			
	}
	
	
	public function ruku()
	{
        init_app_page();
		$account_info = $GLOBALS['account_info'];		
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
		$ywsortid=2;
		$psid = $_REQUEST['psid']?intval($_REQUEST['psid']):'0';   
		$dhid = $_REQUEST['dhid']?intval($_REQUEST['dhid']):'0'; 
		
		$sqlcheck="select dd_detail from fanwe_cangku_log where slid=$slid and (danjuhao='$psid' or danjuhao='$dhid')";	
		$isRuku  =	$GLOBALS['db']->getRow($sqlcheck);
		//var_dump($isRuku);
		if($isRuku){
		 showBizErr("已经入过库了，请勿重复操作",0,url("biz","order#my_order"));	 	
		}
		if ($psid){  //配送中心
		 $peisong_data=	$GLOBALS['db']->getRow("select * from fanwe_dinghuo where id=".$psid);
		 $seller_slid=$peisong_data['seller_slid'];
		 $buyer_slid=$peisong_data['buyer_slid'];
		// $detail=unserialize($peisong_data['detail']);
		 $zmoney=$peisong_data['money'];
		 $ywsortid=3;
		 $rukutype=1;
		 $GLOBALS['tmpl']->assign("danjuho", $psid);
		 $GLOBALS['tmpl']->assign("ddbz", $psid);
			 
		}
		if ($dhid){  //调货
		 $diaohuo_data=	$GLOBALS['db']->getRow("select * from fanwe_diaohuo where id=".$dhid);
		 $seller_slid=$diaohuo_data['seller_slid'];
		 $buyer_slid=$diaohuo_data['buyer_slid'];
		// $detail=unserialize($diaohuo_data['detail']);
		 $GLOBALS['tmpl']->assign("danjuho", $dhid);
		 $GLOBALS['tmpl']->assign("ddbz", $dhid);
		 $rukutype=2;		
		 $zmoney=$diaohuo_data['money'];
		 $ywsortid=3;
		}
		
		$detail=$GLOBALS['db']->getOne("select dd_detail from fanwe_cangku_log where slid=".$seller_slid." and (danjuhao='$psid' or danjuhao='$dhid')");
		$detail=unserialize($detail);
		
		
		foreach($detail as $key=>$val){
		 if($val['unit_type']=='1'){
		  $val['yuan_price'] = $val['price']/$val['times'];
		 }else{
		  $val['yuan_price'] =$val['price'];
		 }
		 $val['mname']=$val['name'];
		 $val['menu_id']=$val['mid'];		 
		 $val['money']=$val['zmoney'];		 
		 $val['op_stock']=$val['num'];		 
		 $detail[$key]=$val;		 
		}
		
		$GLOBALS['tmpl']->assign("zmoney", $zmoney);
		$GLOBALS['tmpl']->assign("seller_slid", $seller_slid);
		$GLOBALS['tmpl']->assign("buyer_slid", $buyer_slid);
		$GLOBALS['tmpl']->assign("detail", $detail);
		//print_r($detail);		
		
		$slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$GLOBALS['tmpl']->assign("slidlist", $slidlist);
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";
	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);
		$GLOBALS['tmpl']->assign("gyslist", $gyslist);
		
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_gys", $location_gys);
		
		
		$cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
		$GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
		$GLOBALS['tmpl']->assign("ywsortid",$ywsortid);
		$GLOBALS['tmpl']->assign("rukuval",1); //入库
		$GLOBALS['tmpl']->assign("rukutype",$rukutype);	
		
		$nowdate=to_date(NOW_TIME,"Y-m-d H:i:s");		
		$GLOBALS['tmpl']->assign("nowdate",$nowdate); 

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("page_title", "入库");
		$GLOBALS['tmpl']->assign("lihuo_user", $account_info['account_name']);
		$GLOBALS['tmpl']->display("pages/cangku/ruku.html");
    
	}
	
   public function chuku(){
        init_app_page();
		$account_info = $GLOBALS['account_info'];		
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
		$ywsortid=7;
		$psid = $_REQUEST['psid']?intval($_REQUEST['psid']):'0';   
		$dhid = $_REQUEST['dhid']?intval($_REQUEST['dhid']):'0'; 
        $zmoney	=-1;	
		if ($psid){  //配送中心
		 $peisong_data=	$GLOBALS['db']->getRow("select * from fanwe_dinghuo where id=".$psid);
		 $seller_slid=$peisong_data['seller_slid'];
		 $buyer_slid=$peisong_data['buyer_slid'];
		 $detail=unserialize($peisong_data['detail']);
		 $zmoney=$peisong_data['money'];
		 $ywsortid=8;
		 $rukutype=1;
		 $GLOBALS['tmpl']->assign("danjuho", $psid);		 
		 $GLOBALS['tmpl']->assign("ddbz", $psid);		 
		 
		}
		if ($dhid){  //调货
		 $diaohuo_data=	$GLOBALS['db']->getRow("select * from fanwe_diaohuo where id=".$dhid);
		 $seller_slid=$diaohuo_data['seller_slid'];
		 $buyer_slid=$diaohuo_data['buyer_slid'];
		 $detail=unserialize($diaohuo_data['detail']);
		 $rukutype=2;
		 $GLOBALS['tmpl']->assign("danjuho", $dhid);
		 $GLOBALS['tmpl']->assign("ddbz", $dhid);	

		 $zmoney=$diaohuo_data['money'];
		 $ywsortid=8;
		}
		foreach($detail as $key=>$val){
		 if($val['unit_type']=='1'){
		  $val['yuan_price'] = $val['price']/$val['times'];
		 }else{
		  $val['yuan_price'] =$val['price'];
		 }
		 $detail[$key]=$val;		 
		}
		
		
		$GLOBALS['tmpl']->assign("zmoney", $zmoney);
		$GLOBALS['tmpl']->assign("seller_slid", $seller_slid);
		$GLOBALS['tmpl']->assign("buyer_slid", $buyer_slid);
		$GLOBALS['tmpl']->assign("detail", $detail);
		
		
		
		$slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$GLOBALS['tmpl']->assign("slidlist", $slidlist);
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";
	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);
		$GLOBALS['tmpl']->assign("gyslist", $gyslist);
		
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_gys", $location_gys);
		
		$location_bumen=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_bumen where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_bumen", $location_bumen);
		
		$cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
		$GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
		$GLOBALS['tmpl']->assign("ywsortid",$ywsortid);
		$GLOBALS['tmpl']->assign("rukuval",2); //出库
		 $GLOBALS['tmpl']->assign("rukutype",$rukutype);	
		$nowdate=to_date(NOW_TIME,"Y-m-d H:i:s");		
		$GLOBALS['tmpl']->assign("nowdate",$nowdate); 
		
		

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("page_title", "出库");
		$GLOBALS['tmpl']->assign("lihuo_user", $account_info['account_name']);
		$GLOBALS['tmpl']->display("pages/cangku/ruku.html");
    
	}
	
	
	public function diaobo(){
         init_app_page();
		$account_info = $GLOBALS['account_info'];		
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
			
		
		$cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
		
		
		$nowdate=to_date(NOW_TIME,"Y-m-d H:i:s");		
		$GLOBALS['tmpl']->assign("nowdate",$nowdate); 

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("page_title", "新增调拨");
		$GLOBALS['tmpl']->assign("lihuo_user", $account_info['account_name']);
		$GLOBALS['tmpl']->display("pages/cangku/diaobo.html");
    
	}
	
	public function diaobo_saving()
	{
	init_app_page();
	$account_info = $GLOBALS['account_info'];
	$supplier_id = $account_info['supplier_id'];
	//$slid = $account_info['slid'];
	$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
	
	$dd_detail=serialize($_REQUEST['detail']);	
	$cid=intval($_REQUEST['cid']);
	$cidtwo=intval($_REQUEST['cidtwo']);
	$cate_id=$_REQUEST['cate_id'];
	$gonghuoren=$_REQUEST['gonghuoren'];	
	$ddbz = $_REQUEST['ddbz']?intval($_REQUEST['ddbz']):'0';  
	
	$datain=$_REQUEST;
	$datain['ctime']=to_timespan($_REQUEST['ctime']);
	$datain['dd_detail']=$dd_detail;
	$datain['slid']=$slid;
	$datain['danjuhao']=to_date(NOW_TIME,"YmdHis").rand(4);
		
	//更新仓库 
	$detail=$_REQUEST['detail'];	
	foreach($detail as $k=>$v){		
	$mid=$v['mid']; 
	$order_num=floatval($v['num']);
	$unit_type=intval($v['unit_type']);		
	if ($unit_type==1){  //使用的是副单位
	  $order_num=$order_num*$v['times']; //换算成主单位			
	}		
	
    //减库
	$sqlstr="where slid=$slid and mid=$mid and cid=$cid";	 //减库条件
	$sqlstrtwo="where slid=$slid and mid=$mid and cid=$cidtwo";	 //加库条件		
	$res1=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);	

	$check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstrtwo);
	if($check){
	$res2=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,stock=stock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstrtwo);				
	}else{
		//添加 
		$data_menu=array(
		"slid"=>$slid,
		"mid"=>$mid,
		"cid"=>$cidtwo,
		"cate_id"=>$v['cate_id'],
		"mbarcode"=>$v['barcode'],
		"mname"=>$v['name'],
		"mstock"=>$order_num,
		"stock"=>$order_num,
		"minStock"=>10,
		"maxStock"=>10000,
		"unit"=>$v['unit'],
		"funit"=>$v['funit'],
		"times"=>$v['times'],
		"type"=>$v['type'],
		"ctime"=>to_date(NOW_TIME)
		);
		$res2=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $data_menu ,"INSERT");
	  }
	}
		
	
	
  	if($res1 && $res2){	
	$res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_diaobo", $datain);  //写入调拨记录
	//写出库记录
	$datain['ywsort']=5; //仓库调拨
	$datain['gonghuoren']='cangku_'.$cidtwo; 
	unset($datain['cidtwo']); //销毁入库的仓库ID
	$datain['type']=2;
	$res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入出库记录
	$datain['cid']=$cidtwo;
	$datain['type']=1;
	$datain['gonghuoren']='cangku_'.$cid; 
	$res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入入库记录	
	
	showBizSuccess("操作成功",0,url("biz","cangku#diaobo_list&id=$slid"));
	}else{
	 showBizErr("出现错误",0,url("biz","cangku#diaobo&id=$slid"));
	}
	
	
	}
	
	public function saving()
	{
	init_app_page();
	$account_info = $GLOBALS['account_info'];
	$supplier_id = $account_info['supplier_id'];
	//$slid = $account_info['slid'];
	$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
	
	$dd_detail=serialize($_REQUEST['detail']);	
	$cid=$_REQUEST['cid'];
	$cate_id=$_REQUEST['cate_id'];
	$gonghuoren=$_REQUEST['gonghuoren'];	
	$ddbz = $_REQUEST['ddbz']?intval($_REQUEST['ddbz']):'0';  
	
	
	if($unit_type==9){$unit_type==0;}
	$datain=$_REQUEST;
	$datain['ctime']=to_timespan($_REQUEST['ctime']);
	$datain['dd_detail']=$dd_detail;

	$datain['slid']=$slid;
	
	
	//更新仓库 
	$detail=$_REQUEST['detail'];
	
	foreach($detail as $k=>$v){		
	 if (intval($v['mid'])==0){
		continue;
	 }
	 //print_r($v);
	 
	$mid=$v['mid'];   
   //0805 查询本店的ID 根据商品条码 
   if($ddbz>0){
	 if($v['barcode'] !="")  {
	$mid=$GLOBALS['db']->getOne("select id from fanwe_dc_menu where location_id='".$slid."' and (barcode='".$v['barcode']."')");
	}else{
	$mid=$GLOBALS['db']->getOne("select id from fanwe_dc_menu where location_id='".$slid."' and (name='".$v['name']."')");
	}
	if (!$mid){
	//如果ID不存在，则自动增加商品进入产品库，返回ID
	    $dc_menu_data=array(
		"location_id"=>$slid,
		"supplier_id"=>$supplier_id,		
		"barcode"=>$v['barcode'],
		"name"=>$v['name'],	
	    "cate_id"=>$v['cate_id'],
		"price"=>floatval($v['price']),	
		"unit"=>$v['unit'],
		"funit"=>$v['funit'],
		"times"=>$v['times'],
		"type"=>$v['type']
		);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $dc_menu_data ,"INSERT");
	    $mid = $GLOBALS['db']->insert_id();	
	}   
   }
    
	
	
    
	$cid=$GLOBALS['db']->getOne("select cid from fanwe_cangku_bangding_cangku where slid=$slid and mid=$mid"); //取得仓库ID
    if(!$cid){
    $cid=$GLOBALS['db']->getOne("select id from fanwe_cangku where slid=$slid and isdisable=1 order by id asc limit 1"); //取得仓库ID  
    }
	
	
   
	$sqlstr="where slid=$slid and mid=$mid and cid=$cid";	
	$order_num=floatval($v['num']);
	$cate_id=$v['cate_id'];
	$unit_type=intval($v['unit_type']);	
	if ($unit_type==1){  //使用的是副单位
	  $order_num=$order_num*$v['times']; //换算成主单位			
	}		
	 
	 
	//存在的话更新数量	
        if ($_REQUEST['type']==1){ //入库
		$check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstr);
		if($check){			
		$res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,stock=stock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);				
		}else{
		//添加 
		$data_menu=array(
		"slid"=>$slid,
		"mid"=>$mid,
		"cid"=>$cid,
		"cate_id"=>$v['cate_id'],
		"mbarcode"=>$v['barcode'],
		"mname"=>$v['name'],
		"mstock"=>$order_num,
		"stock"=>$order_num,
		"minStock"=>10,
		"maxStock"=>10000,
		"unit"=>$v['unit'],
		"funit"=>$v['funit'],
		"times"=>$v['times'],
		"type"=>$v['type'],
		"ctime"=>to_date(NOW_TIME)
		);
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $data_menu ,"INSERT");
	  }
		

         //写入库商品明细
		 
		$gonghuoren=$GLOBALS['db']->getOne("select gid from fanwe_cangku_bangding_gys where slid=$slid and mid=$mid"); //取得绑定的供应商
        if(!$cid){
        $gonghuoren='linshi_3'; //临时供应商3  
        }
		 
		 $data_gys=array(
		"slid"=>$slid,
		"mid"=>$mid,
		"cid"=>$cid,
		"mbarcode"=>$v['barcode'],
		"mname"=>$v['name'],
		"stock"=>$order_num,
		"gonghuoren"=>$gonghuoren,
		"unit"=>$v['unit'],
		"funit"=>$v['funit'],
		"times"=>$v['times'],
		"type"=>$v['type'],
		"price"=>floatval($v['price']),	
		"ctime"=>to_date(NOW_TIME)
		);
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu_gys", $data_gys ,"INSERT");






		
		}else{ //出库
		 $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
		 if($order_num>$check['mstock']){
		  showBizErr("库存不足,非法提交，后果自负！",0,url("biz","cangku#index&id=$slid"));	 
		 }else{//操作减库存 
			$res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);							  	  
		}
		
		}
		
	
	//增加MENU库表
	if ($_REQUEST['type']==1){ //入库
	$res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+$order_num where id=".$mid);
	}else{
	$res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$order_num where id=".$mid);	
	}
  }
	if($res){	
	$res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain ,"INSERT");
	showBizSuccess("操作成功",0,url("biz","cangku#index&id=$slid"));
	}else{
	 showBizErr("出现错误",0,url("biz","cangku#index&id=$slid"));
	}
	
	
	}
	
	public function findstock(){
	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
		
		$cid = $_REQUEST['cid']?intval($_REQUEST['cid']):'';
		$cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';     
		$mid = $_REQUEST['mid']?$_REQUEST['mid']:'';     
		
		
		$sqlstr="where a.slid=$slid";
		if($cid){ //配送中心
		$sqlstr.=' and a.cid='.$cid;	
		}
		if($cate_id){ //配送中心
		$sqlstr.=' and a.cate_id='.$cate_id;	
		}
		if($mid){
		$sqlstr .=" and (c.pinyin='".$mid."' or a.mid = '".$mid."' or a.mbarcode='".$mid."' or a.mname like '%".$mid."%' )";
		}
		$GLOBALS['tmpl']->assign("cid", $cid);
		$GLOBALS['tmpl']->assign("cate_id", $cate_id);
		$GLOBALS['tmpl']->assign("mid", $mid);
		
		$cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangku_list", $cangku_list);
		
		//分类
		$conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$conditions .= " and location_id=".$slid;
		$sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
		$sqlsort.=$conditions . " order by sort desc";

		$listsort = array();		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sqlsort);		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$listsort[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$listsort[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$listsort[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$listsort[] = $wmenu3;
						}
					}
				}
			}
		}
         
		$GLOBALS['tmpl']->assign("sortlist", $listsort);
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
			
		
        $sql="select a.*,b.name as cname from ".DB_PREFIX."cangku_menu a left join ".DB_PREFIX."cangku b on a.cid=b.id left join ".DB_PREFIX."dc_menu c on a.mid=c.id ".$sqlstr." order by a.ctime desc limit ".$limit;
        //echo $sql;
	   $sqlc="select count(a.id) from ".DB_PREFIX."cangku_menu a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." order by a.id desc";
        $total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		$list=$GLOBALS['db']->getAll($sql);	
		//print_r($sql);
		//print_r($list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "库存查询");
		$GLOBALS['tmpl']->display("pages/cangku/my_center.html");
		
	}
	
	
	public function gyslog(){
	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
		$cid = $_REQUEST['cid']?intval($_REQUEST['cid']):'';
		$cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';     
		$mid = $_REQUEST['mid']?$_REQUEST['mid']:'';    
		$gonghuoren = $_REQUEST['gonghuoren']?$_REQUEST['gonghuoren']:'';    
		
		
        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$GLOBALS['tmpl']->assign("slidlist", $slidlist);
		
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);
		$GLOBALS['tmpl']->assign("gyslist", $gyslist);
		
		
		
		
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_gys", $location_gys);
		
		
		

		
		
		$sqlstr="where a.slid=$slid";
		if($cid){ //配送中心
		$sqlstr.=' and a.cid='.$cid;	
		}
		if($cate_id){ //配送中心
		$sqlstr.=' and b.cate_id='.$cate_id;	
		}
		if($gonghuoren){ //供货商
		$sqlstr.=' and a.gonghuoren="'.$gonghuoren.'"';	
		}
		if($mid){
		$sqlstr .=" and (a.mid = '".$mid."' or a.mbarcode='".$mid."' or a.mname like '%".$mid."%' )";
		}
		$GLOBALS['tmpl']->assign("cid", $cid);
		$GLOBALS['tmpl']->assign("cate_id", $cate_id);
		$GLOBALS['tmpl']->assign("mid", $mid);
		$GLOBALS['tmpl']->assign("gonghuoren", $gonghuoren);
		
		$cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangku_list", $cangku_list);
		
		//分类
		$conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$conditions .= " and location_id=".$slid;
		$sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
		$sqlsort.=$conditions . " order by sort desc";

		$listsort = array();		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sqlsort);		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$listsort[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$listsort[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$listsort[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$listsort[] = $wmenu3;
						}
					}
				}
			}
		}
         
		$GLOBALS['tmpl']->assign("sortlist", $listsort);
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
			
		
        $sql="select a.*,b.name as cname from ".DB_PREFIX."cangku_menu_gys a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." order by a.id desc limit ".$limit;
      //  echo $sql;
		$sqlc="select count(a.id) from ".DB_PREFIX."cangku_menu_gys a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." order by a.id desc";
        
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		
		
		
		
		
		
		$list=$GLOBALS['db']->getAll($sql);	
        foreach($list as $k=>$v){			
		$gonghuoren=$v['gonghuoren'];		
		$v['gys_name']=$this->get_gonghuoren_name($supplier_id,$slid,$gonghuoren);
		
		$list[$k]=$v;		
		}
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "供货明细查询");
		$GLOBALS['tmpl']->display("pages/cangku/my_center_gys.html");
		
	}
	
	public function priceanalyze(){ //进货价分析
	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
		$cid = $_REQUEST['cid']?intval($_REQUEST['cid']):'';
		$cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';     
		$mid = $_REQUEST['mid']?$_REQUEST['mid']:'';    
		$gonghuoren = $_REQUEST['gonghuoren']?$_REQUEST['gonghuoren']:'';    
		
		
        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$GLOBALS['tmpl']->assign("slidlist", $slidlist);
		
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);
		$GLOBALS['tmpl']->assign("gyslist", $gyslist);
		
		
		
		
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_gys", $location_gys);
		
		
		

		
		
		$sqlstr="where a.slid=$slid";
		if($cid){ //配送中心
		$sqlstr.=' and a.cid='.$cid;	
		}
		if($cate_id){ //配送中心
		$sqlstr.=' and b.cate_id='.$cate_id;	
		}
		if($gonghuoren){ //供货商
		$sqlstr.=' and a.gonghuoren="'.$gonghuoren.'"';	
		}
		if($mid){
		$sqlstr .=" and (a.mid = '".$mid."' or a.mbarcode='".$mid."' or a.mname like '%".$mid."%' )";
		}
		$GLOBALS['tmpl']->assign("cid", $cid);
		$GLOBALS['tmpl']->assign("cate_id", $cate_id);
		$GLOBALS['tmpl']->assign("mid", $mid);
		$GLOBALS['tmpl']->assign("gonghuoren", $gonghuoren);
		
		$cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangku_list", $cangku_list);
		
		//分类
		$conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$conditions .= " and location_id=".$slid;
		$sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
		$sqlsort.=$conditions . " order by sort desc";

		$listsort = array();		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sqlsort);		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$listsort[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$listsort[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$listsort[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$listsort[] = $wmenu3;
						}
					}
				}
			}
		}
         
		$GLOBALS['tmpl']->assign("sortlist", $listsort);
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
			
		
        $sql="select a.*,count(DISTINCT a.gonghuoren) as gysnum,count(a.id) as jinhuonum,max(a.price) as maxprice,min(a.price) as minprice,AVG(a.price) as avgprice,max(a.ctime) as ctime,b.name as cname from ".DB_PREFIX."cangku_menu_gys a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." group by a.mid order by a.id desc limit ".$limit;
        $sqlc="select count(DISTINCT a.mid) from ".DB_PREFIX."cangku_menu_gys a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." order by a.id desc";
        
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		
		
		
		
		
		
		$list=$GLOBALS['db']->getAll($sql);	
        foreach($list as $k=>$v){			
		$gonghuoren=$v['gonghuoren'];		
		$v['gys_name']=$this->get_gonghuoren_name($supplier_id,$slid,$gonghuoren);
		
		$list[$k]=$v;		
		}
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "进货价分析");
		$GLOBALS['tmpl']->display("pages/cangku/my_center_jhfx.html");
		
	}
	
	
	public function gys_tongji(){
	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
		$cid = $_REQUEST['cid']?intval($_REQUEST['cid']):'';
		$cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';     
		$mid = $_REQUEST['mid']?$_REQUEST['mid']:'';    
		$gonghuoren = $_REQUEST['gonghuoren']?$_REQUEST['gonghuoren']:'';    
		
		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$GLOBALS['tmpl']->assign("slidlist", $slidlist);
		$slid_names = array();
        $slid_names = array_reduce($slidlist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);
		$GLOBALS['tmpl']->assign("gyslist", $gyslist);
		
		$city_names = array();
        $city_names = array_reduce($gyslist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_gys", $location_gys);
		
		$local_names = array();
        $local_names = array_reduce($location_gys, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
        if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
        $startstr=strtotime($start);
        $startend=strtotime($start)+24*3600-1;
        $begin_time=date("Y-m-d H:i:s",$startstr); 
        $end_time=date("Y-m-d H:i:s",$startend); 
        }			
		
		
		$sqlstr="where a.slid=$slid";
		if($cid){ //配送中心
		$sqlstr.=' and a.cid='.$cid;	
		}
		if($cate_id){ //配送中心
		$sqlstr.=' and b.cate_id='.$cate_id;	
		}
		if($gonghuoren){ //供货商
		$sqlstr.=' and a.gonghuoren="'.$gonghuoren.'"';	
		}
		if($mid){
		$sqlstr .=" and (a.mid = '".$mid."' or a.mbarcode='".$mid."' or a.mname like '%".$mid."%' )";
		}
		if($begin_time){
		$sqlstr .=" and a.ctime > '".$begin_time."' ";
		}
		if($end_time){
		$sqlstr .=" and a.ctime < '".$end_time."' ";
		}
		
		$GLOBALS['tmpl']->assign("cid", $cid);
		$GLOBALS['tmpl']->assign("cate_id", $cate_id);
		$GLOBALS['tmpl']->assign("mid", $mid);
		$GLOBALS['tmpl']->assign("gonghuoren", $gonghuoren);
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		$cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangku_list", $cangku_list);
		
		//分类
		$conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$conditions .= " and location_id=".$slid;
		$sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
		$sqlsort.=$conditions . " order by sort desc";

		$listsort = array();		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sqlsort);		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$listsort[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$listsort[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$listsort[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$listsort[] = $wmenu3;
						}
					}
				}
			}
		}
         
		$GLOBALS['tmpl']->assign("sortlist", $listsort);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	     $oderstr = " order by a.".$order." ".$sort;
		  if($order=='cnum'){
			 $oderstr = " order by ".$order." ".$sort;  
		  }
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else{
			$oderstr = "order by a.id desc";
			$sortImg=array();
		}
		$sort = $sort == 'asc' ? 1 : 0; //排序方式		
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );	
		
		
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
			
		
        $sql="select a.*,b.name as cname,sum(a.stock) as cnum from ".DB_PREFIX."cangku_menu_gys a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." GROUP BY a.gonghuoren,a.mid $oderstr limit ".$limit;
       // echo $sql;
		$sqlc="select count(DISTINCT gonghuoren) from ".DB_PREFIX."cangku_menu_gys a left join ".DB_PREFIX."cangku b on a.cid=b.id ".$sqlstr." order by a.id desc";
        
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
		
		
		
		
		
		$linshi=array(
		"1"=>"临时客户",
		"2"=>"临时运输商",
		"3"=>"临时供应商"		
		);
		$list=$GLOBALS['db']->getAll($sql);	
        foreach($list as $k=>$v){
			
		$gonghuoren=$v['gonghuoren'];
		
		$gonghuoren_arr=explode('_',$gonghuoren);
		$gonghuoren_type=$gonghuoren_arr[0];
		$gonghuoren_id=$gonghuoren_arr[1];	
		if ($gonghuoren_type=='linshi'){
		  $v['gys_name']=$linshi[$gonghuoren_id];
		}elseif($gonghuoren_type=='slid'){
		  $v['gys_name']=$slid_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='citygys'){
		  $v['gys_name']=$city_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='localgys'){
		  $v['gys_name']=$local_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='other'){
		  $v['gys_name']=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$gonghuoren_id);
		}elseif($gonghuoren_type=='user'){
		  $v['gys_name']='存货用户：'.$GLOBALS['db']->getOne("select user_name from fanwe_user where id=".$gonghuoren_id);
		}
		$list[$k]=$v;		
		}
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "供货统计");
		$GLOBALS['tmpl']->display("pages/cangku/my_center_gys_tj.html");
		
	}
	
	
	public function bangding(){
	    init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
	    $linshi=array(
		"1"=>"临时客户",
		"2"=>"临时运输商",
		"3"=>"临时供应商"		
		);
		$cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';     
		$mid = $_REQUEST['mid']?$_REQUEST['mid']:'';    
		
		$sqlstr="where a.location_id=$slid";		
		if($cate_id){ //配送中心
		$sqlstr.=' and a.cate_id='.$cate_id;			}
		
		if($mid){
		$sqlstr .=" and (a.pinyin='".strtoupper($mid)."' or a.id = '".$mid."' or a.barcode='".$mid."' or a.name like '%".$mid."%' )";
		}
	
		
		$GLOBALS['tmpl']->assign("cate_id", $cate_id);
		$GLOBALS['tmpl']->assign("mid", $mid);
	
	    $cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
		$GLOBALS['tmpl']->assign("cangku_list", $cangku_list);
		
	
	
		$slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$GLOBALS['tmpl']->assign("slidlist", $slidlist);
		$slid_names = array();
        $slid_names = array_reduce($slidlist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);
		$GLOBALS['tmpl']->assign("gyslist", $gyslist);
		
		$city_names = array();
        $city_names = array_reduce($gyslist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
		$GLOBALS['tmpl']->assign("location_gys", $location_gys);
		
		$local_names = array();
        $local_names = array_reduce($location_gys, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		
		
		
		
		//分类
		$conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$conditions .= " and location_id=".$slid;
		$sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
		$sqlsort.=$conditions . " order by sort desc";

		$listsort = array();		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sqlsort);		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$listsort[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$listsort[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$listsort[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$listsort[] = $wmenu3;
						}
					}
				}
			}
		}
         
		$GLOBALS['tmpl']->assign("sortlist", $listsort);
		
		$page_size = 50;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
			
		
        $sql="select a.id,a.name,a.cate_id,a.image,a.barcode,b.name as cname from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id  ".$sqlstr." order by a.id desc limit ".$limit;
      // echo $sql;
		$sqlc="select count(a.id) from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id ".$sqlstr." order by a.id desc";
        
		$total = $GLOBALS['db']->getOne($sqlc);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);		
		$list=$GLOBALS['db']->getAll($sql);	
        
        foreach($list as $k=>$v){
        $get_bangding=$GLOBALS['db']->getRow("select * from fanwe_cangku_bangding_gys where mid=".$v['id']);
		if($get_bangding['gid']!=""){
		$gonghuoren=$get_bangding['gid'];
		$gonghuoren_arr=explode('_',$gonghuoren);
		$gonghuoren_type=$gonghuoren_arr[0];
		$gonghuoren_id=$gonghuoren_arr[1];	
		if ($gonghuoren_type=='linshi'){
		  $v['gys_name']=$linshi[$gonghuoren_id];
		}elseif($gonghuoren_type=='slid'){
		  $v['gys_name']=$slid_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='citygys'){
		  $v['gys_name']=$city_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='localgys'){
		  $v['gys_name']=$local_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='other'){
		  $v['gys_name']=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$gonghuoren_id);
		}elseif($gonghuoren_type=='user'){
		  $v['gys_name']='存货用户：'.$GLOBALS['db']->getOne("select user_name from fanwe_user where id=".$gonghuoren_id);
		}
		}
		$get_bangding=$GLOBALS['db']->getRow("select * from fanwe_cangku_bangding_cangku where mid=".$v['id']);	

		if($get_bangding['cid']!=""){
		 $v['cangku_name']=$GLOBALS['db']->getOne("select name from fanwe_cangku where id=".$get_bangding['cid']);	
		 
		}
		$list[$k]=$v;
						
		}		
	
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("page_title", "绑定商品属性");
		$GLOBALS['tmpl']->display("pages/cangku/bangding.html");		
	}
    public function peifang(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        //$slid = $account_info['slid'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
        $cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';
        $mid = $_REQUEST['mid']?$_REQUEST['mid']:'';
        //预制、现制、半成品   1 2 6
        $sqlstr="where a.location_id=$slid and a.print in (1,2,6) ";
        if($cate_id){ //配送中心
            $sqlstr.=' and a.cate_id='.$cate_id;
        }

        if($mid){
            $sqlstr .=" and (a.pinyin='".strtoupper($mid)."' or a.id = '".$mid."' or a.barcode='".$mid."' or a.name like '%".$mid."%' )";
        }


        $GLOBALS['tmpl']->assign("cate_id", $cate_id);
        $GLOBALS['tmpl']->assign("mid", $mid);


        //分类
        $conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
        $conditions .= " and location_id=".$slid;
        $sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
        $sqlsort.=$conditions . " order by sort desc";

        $listsort = array();
        $wsublist = array();
        $wmenulist = $GLOBALS['db']->getAll($sqlsort);
        foreach($wmenulist as $wmenu)
        {
            if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
        }
        foreach($wmenulist as $wmenu0)
        {
            if($wmenu0['wcategory'] == '0')
            {
                $listsort[] = $wmenu0;

                foreach($wsublist[$wmenu0['id']] as $wmenu1)
                {
                    $listsort[] = $wmenu1;
                    foreach($wsublist[$wmenu1['id']] as $wmenu2)
                    {
                        $listsort[] = $wmenu2;
                        foreach($wsublist[$wmenu2['id']] as $wmenu3)
                        {
                            $listsort[] = $wmenu3;
                        }
                    }
                }
            }
        }

        $GLOBALS['tmpl']->assign("sortlist", $listsort);


        $page_size = 50;
        $page = intval($_REQUEST['p']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;


        $sql="select a.id,a.name,a.cate_id,a.image,a.barcode,a.print,a.unit,b.name as cname from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id  ".$sqlstr." order by a.id desc limit ".$limit;
        // echo $sql;
        $sqlc="select count(a.id) from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id ".$sqlstr." order by a.id desc";

        $total = $GLOBALS['db']->getOne($sqlc);
        $page = new Page($total,$page_size);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        $list=$GLOBALS['db']->getAll($sql);

        $peifang_info=$GLOBALS['db']->getAll("select id,menu_id,datetime from fanwe_cangku_peifang where slid=".$slid);
        $peifang=array();
        foreach ($peifang_info as $ke=>$ve){
            $peifang[$ve['menu_id']]=$ve;
        }


        foreach($list as $k=>$v){
            $v['pfdate']=to_date($v[$peifang[$v['id']]['datetime']],"Y-m-d H:i:s");
            if(!empty($peifang[$v['id']])){
                $v['pf_status']=1;
            }else{
                $v['pf_status']=0;
            }
            $v['kclx']=$this->kcnx[$v['print']];
            $list[$k]=$v;

        }


        /* 系统默认 */
        $GLOBALS['tmpl']->assign("slid", $slid);
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("page_title", "配方管理");
        $GLOBALS['tmpl']->display("pages/cangku/peifang.html");
    }
    public function peifang_view(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $menu_id = $_REQUEST['menu_id']?intval($_REQUEST['menu_id']):0;

        $peifang_sql="select * from fanwe_cangku_peifang where menu_id=".$menu_id;
        $peifang_info=$GLOBALS['db']->getRow($peifang_sql);
        $pfid=$peifang_info['id'];

        if($peifang_info){
            /*
                        $peifang_stat=$GLOBALS['db']->getAll("select a.*,b.name,b.barcode,b.print,b.price,b.buyPrice,b.sellPrice2,b.customerPrice,b.chupinliu,b.unit,c.name as cname from fanwe_cangku_peifang_stat a left join fanwe_dc_menu b on a.menu_id=b.id left join fanwe_dc_supplier_menu_cate c on b.cate_id=c.id where a.pfid=".$pfid);
                        foreach ($peifang_stat as $k=>$v){
                            $peifang_stat[$k]['kclx']=$this->kcnx[$v['print']];

                            "0"=>"暂无",
                        "1"=>"现制商品",
                        "2"=>"预制商品",
                        "3"=>"外购商品",
                        "4"=>"原物料",
                        "6"=>"半成品",
            price 售卖价 buyPrice 采购价
            sellPrice2 成本价 customprice 结算价


                            if($v['print']==4 || $v['print']==3){
                                $peifang_stat[$k]['gusuanchengben']=$v['buyPrice'];
                            }elseif ($v['print']==6 || $v['print']==1){
                                $peifang_stat[$k]['gusuanchengben']=$v['sellPrice2'];
                            }


                        }*/

            $peifang_stat=unserialize($peifang_info['data_json']);
            foreach ($peifang_stat as $k=>$v){
                $menu_info=$GLOBALS['db']->getRow("select b.name,b.barcode,b.print,c.name as cname from fanwe_dc_menu b  left join fanwe_dc_supplier_menu_cate c on b.cate_id=c.id where b.id=".$v['menu_id']);
                $v['id']=$v['menu_id'];
                $v['kclx']=$this->kcnx[$menu_info['print']];
                $v['cname']=$menu_info['cname'];
                $v['barcode']=$menu_info['barcode'];
                $v['name']=$menu_info['name'];
                $peifang_stat[$k]=$v;
            }
          // var_dump($peifang_stat);

        }else{
            $peifang_stat=$GLOBALS['db']->getAll("select b.id,b.name,b.barcode,b.print,b.price,b.buyPrice,b.sellPrice2,b.customerPrice,b.chupinliu,b.unit,c.name as cname from fanwe_dc_menu b  left join fanwe_dc_supplier_menu_cate c on b.cate_id=c.id where b.location_id=".$slid);

            foreach ($peifang_stat as $k=>$v){
                $peifang_stat[$k]['kclx']=$this->kcnx[$v['print']];
                /*
                                "0"=>"暂无",
                            "1"=>"现制商品",
                            "2"=>"预制商品",
                            "3"=>"外购商品",
                            "4"=>"原物料",
                            "6"=>"半成品",
                price 售卖价 buyPrice 采购价
                sellPrice2 成本价 customprice 结算价
                                */

                if($v['print']==4 || $v['print']==3){
                    $peifang_stat[$k]['gusuan']=$v['buyPrice'];
                }elseif ($v['print']==6 || $v['print']==1){
                    $peifang_stat[$k]['gusuan']=$v['sellPrice2'];
                }


            }
        }



        /* 系统默认 */
        $GLOBALS['tmpl']->assign("peifang_info", $peifang_info);
        $GLOBALS['tmpl']->assign("list", $peifang_stat);
        $GLOBALS['tmpl']->assign("menu_id", $menu_id);
        $GLOBALS['tmpl']->assign("page_title", "编辑配方");
        $GLOBALS['tmpl']->display("pages/cangku/peifang_edit.html");
    }
    public function peifang_saving(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $menu_id=$_REQUEST['menu_id'];
        //配方ID
        $pfid=$_REQUEST['pfid']?intval($_REQUEST['pfid']):0;
        //配方数组
        $data_peifang=array(
            "menu_id"=>$menu_id,
            "slid"=>$slid,
            "gusuanchengben"=>$_REQUEST['gusuanchengben'],
            "datetime"=>NOW_TIME,
            "num"=>intval($_REQUEST['num'])
        );
        //存在配方ID，则更新，否则插入，取到配方ID
        if($pfid){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang",$data_peifang,"UPDATE","id='$pfid'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang",$data_peifang);
            $pfid= $GLOBALS['db']->insert_id();
        }

        //插入配方，取到配方ID

        $mid=$_REQUEST['mid'];
       // var_dump($mid);
        //构造数组，填入统计表 由于这个是临时用，没有JS特效，这块帮的比较麻烦，如果使用AJAX的话会相对简单，组成以下的数组就行了
        $data_stat=array();
        foreach ($mid as $key=>$item) {
            $data_stat[$key]['pfid']=$pfid;
            $data_stat[$key]['menu_id']=$item;
            $data_stat[$key]['gusuan']=$_REQUEST['gusuan'][$item];
            $data_stat[$key]['num_j']=$_REQUEST['num_j'][$item];
            $data_stat[$key]['chupinliu']=$_REQUEST['chupinliu'][$item];
            $data_stat[$key]['num_m']=$_REQUEST['num_m'][$item];
            $data_stat[$key]['unit']=$_REQUEST['unit'][$item];
            $data_stat[$key]['gusuanjine']=$_REQUEST['gusuanjine'][$item];
            $data_stat[$key]['datetime']=NOW_TIME;
        }
       // var_dump($data_stat);
        //更新配方里的data_json字段
        $data_json=array('data_json'=>serialize($data_stat));
        $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang",$data_json,"UPDATE","id='$pfid'");

        //删除该配方的所有信息
        $GLOBALS['db']->query("delete from fanwe_cangku_peifang_stat where pfid=".$pfid);
        //根据数组，依次插入数据库，这种方法效率比较低，临时使用
        foreach ($data_stat as $value){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang_stat",$value);
        }
        if($res){//成功
            showBizSuccess("配方设置成功！",0,url("biz","cangku#peifang&id=$slid"));
        }else{
            showBizErr("错误！",0,url("biz","cangku#peifang&act=peifang_view&menu_id=$menu_id"));
        }


    }
    public function bangding_saving(){
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//$slid = $account_info['slid'];
		$slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];;
		$mids=$_REQUEST['mid'];
		$cid=$_REQUEST['cid'];
		$gid=$_REQUEST['gonghuoren'];
		
		$order_str1="";//入统计库参数准备
		$order_str2="";//入统计库参数准备
		foreach($mids as $va){
		if($cid){	
        $order_str1=$order_str1."($slid,$va,$cid),";
		}
		if($gid){	
        $order_str2=$order_str2."($slid,$va,'$gid'),";
		}
	    }
		$order_str1 = substr($order_str1,0,strlen($order_str1)-1); 
		$order_str2 = substr($order_str2,0,strlen($order_str2)-1); 
		
		
		if($cid){
		$GLOBALS['db']->query("delete from fanwe_cangku_bangding_cangku where mid in(".implode(",",$mids).")");	
		$sql1 = "insert into `fanwe_cangku_bangding_cangku` (slid,mid,cid) values".$order_str1;
		
		$res=$GLOBALS['db']->query($sql1);	
		}
		if($gid){
		$GLOBALS['db']->query("delete from fanwe_cangku_bangding_gys where mid in(".implode(",",$mids).")");	
		$sql2 = "insert into `fanwe_cangku_bangding_gys` (slid,mid,gid) values".$order_str2;
		//echo $sql2;
		$res=$GLOBALS['db']->query($sql2);
		
		}
		
		if($res){
		 showBizSuccess("绑定成功",0,url("biz","cangku#bangding"));	
		}else{
		 showBizErr("绑定失败",0,url("biz","cangku#bangding"));	
		}	
	}
	
	
	public function get_menu()
	{
	init_app_page();
	$slid = intval($_REQUEST['slid']);   

     $check=$GLOBALS['db']->getAll("select id,name,barcode,unit,funit,times,price,pinyin,cate_id from fanwe_dc_menu where location_id=".$slid);
	//echo ("select stock from fanwe_dc_menu ".$sqlstr);
	
	if($check){		
	$showjson=json_encode($check);	
	$showjson=str_replace("},{","},\n{",$showjson);
	$showjson=str_replace("[{","[\n{",$showjson);
	$showjson=str_replace("}]","}\n]",$showjson);
	echo $showjson;	
	//print_r($showjson);
	}else{
		echo '{"status":"fail","msg":"读取失败！"}';	
	}
	
	}
	
	public function get_menu_cangku()
	{
	init_app_page();
	$slid = intval($_REQUEST['slid']);   
	$cid = intval($_REQUEST['cid']);   

     $check=$GLOBALS['db']->getAll("select a.id,a.name,a.barcode,a.unit,a.funit,a.times,a.price,a.pinyin,a.cate_id,b.mstock from fanwe_dc_menu a left join fanwe_cangku_menu b on a.id=b.mid where a.location_id=".$slid." and b.cid=".$cid);
	//echo ("select stock from fanwe_dc_menu ".$sqlstr);
	
	if($check){		
	$showjson=json_encode($check);	
	$showjson=str_replace("},{","},\n{",$showjson);
	$showjson=str_replace("[{","[\n{",$showjson);
	$showjson=str_replace("}]","}\n]",$showjson);
	echo $showjson;	
	//print_r($showjson);
	}else{
		echo '{"status":"fail","msg":"读取失败！"}';	
	}
	
	}
	
	public function get_gonghuoren()
	{
	init_app_page();
	$slid = intval($_REQUEST['slid']);  

    $location_list=$GLOBALS['db']->getAll("select id,tel,name from fanwe_supplier_location order by id desc");
	foreach($location_list as $k=>$v){
		$v['name']="门店名称：".$v['name'];
		$v['uid']="O".$v['id'];
		$v['id']="other_".$v['id'];
		
		$location_list[$k]=$v;
	}
	$location_info=$GLOBALS['db']->getRow("select supplier_id,isZhiying from fanwe_supplier_location where id=".$slid);	
    $supplier_id=$location_info["supplier_id"];	
    $isZhiying=$location_info["isZhiying"];		
    if($isZhiying==1){
		$dbname="fanwe_user_".$supplier_id."_".$slid."_info";		
	}else{		
		$dbname="fanwe_user_".$supplier_id."_0_info";
	}
    
	$user_list=$GLOBALS['db']->getAll("select a.user_id as id,a.tel,b.user_name as name from ".$dbname." a left join fanwe_user b on a.user_id=b.id order by id desc");
	foreach($user_list as $k=>$v){
		$v['name']="用户名：".$v['name'];
		$v['uid']="U".$v['id'];
		$v['id']="user_".$v['id'];
		
		$user_list[$k]=$v;
	}
	//echo ("select stock from fanwe_dc_menu ".$sqlstr);
	if ($user_list){
	$check=array_merge($location_list,$user_list);	
	}else{
	$check=	$location_list;
	}
	
	
	
	if($check){		
	$showjson=json_encode($check);	
	$showjson=str_replace("},{","},\n{",$showjson);
	$showjson=str_replace("[{","[\n{",$showjson);
	$showjson=str_replace("}]","}\n]",$showjson);
	echo $showjson;	
	}else{
		echo '{"status":"fail","msg":"读取失败！"}';	
	}
	
	}
	
	public function get_kucun()
	{
	init_app_page();
	$slid = intval($_REQUEST['slid']);   
	$mid = intval($_REQUEST['mid']);   
	$cid = intval($_REQUEST['cid']);   

    $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu where slid=$slid and mid=$mid and cid=$cid");
	//echo ("select stock from fanwe_dc_menu ".$sqlstr);
	if($check){		
	$showjson=json_encode(array("status"=>"success","stock"=>$check['mstock']));	
	echo $showjson;	
	}else{
		echo '{"status":"fail","msg":"读取失败！"}';	
	}
	
	}
	
	public function dc_cangku(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (name='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku where slid=$slid and isdisable=$isdd $str order by id desc ");

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","cangku"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "仓库管理");
		$GLOBALS['tmpl']->display("pages/cangku/cangku.html");
	}
	
	//添加仓库 
	public function dc_add_cangku(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$name = $_REQUEST['name'];
		if($name){
			$data=$_REQUEST;
		}

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."cangku",$data,"UPDATE","id='$sid'");
		    showBizSuccess("编辑成功",0,url("biz","cangku#dc_cangku&id=$slid"));
			
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "cangku where slid='$slid' and name='$name' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."cangku",$data);
				showBizSuccess("添加成功",0,url("biz","cangku#dc_cangku&id=$slid"));				
			}else{
				showBizErr("已经存在的名称",0,url("biz","cangku#dc_cangku&id=$slid"));				
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "cangku where id=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","cangku"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加仓库");
		$GLOBALS['tmpl']->display("pages/cangku/add_cangku.html");

	}
	
		public function dc_cangku_bumen(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (name='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_bumen where slid=$slid and isdisable=$isdd $str order by id desc ");

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","cangku"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "部门管理");
		$GLOBALS['tmpl']->display("pages/cangku/cangku_bumen.html");
	}
	
	//添加部门
	public function dc_add_cangku_bumen(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$name = $_REQUEST['name'];
		if($name){
			$data=$_REQUEST;
		}

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_bumen",$data,"UPDATE","id='$sid'");
		    showBizSuccess("编辑成功",0,url("biz","cangku#dc_cangku_bumen&id=$slid"));
			
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "cangku_bumen where slid='$slid' and name='$name' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_bumen",$data);
				showBizSuccess("添加成功",0,url("biz","cangku#dc_cangku_bumen&id=$slid"));				
			}else{
				showBizErr("已经存在的名称",0,url("biz","cangku#dc_cangku_bumen&id=$slid"));				
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "cangku_bumen where id=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","cangku"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加部门");
		$GLOBALS['tmpl']->display("pages/cangku/add_cangku_bumen.html");

	}
	
	
	public function dc_cangku_gys(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (name='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_gys where slid=$slid and isdisable=$isdd $str order by id desc ");
        
        $listcp= $GLOBALS['db']->getAll("SELECT name,gys_id FROM " . DB_PREFIX . "dc_menu where location_id=$slid");
        
        print_r($listcp);
		
		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("listcp", $listcp);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "供应商管理");
		$GLOBALS['tmpl']->display("pages/cangku/cangku_gys.html");
	}
	
	//添加供应商
	public function dc_add_cangku_gys(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$name = $_REQUEST['name'];
	// $menu_list = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "dc_menu where location_id=2872 ");
		print_r($menu_list);
		if($name){
			$data=$_REQUEST;
		}

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_gys",$data,"UPDATE","id='$sid'");
		    showBizSuccess("编辑成功",0,url("biz","cangku#dc_cangku_gys&id=$slid"));
			
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "cangku_gys where slid='$slid' and name='$name' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_gys",$data);
				showBizSuccess("添加成功",0,url("biz","cangku#dc_cangku_gys&id=$slid"));				
			}else{
				showBizErr("已经存在的名称",0,url("biz","cangku#dc_cangku_gys&id=$slid"));				
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "cangku_gys where id=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加供应商");
		$GLOBALS['tmpl']->display("pages/cangku/add_cangku_gys.html");

	}
	//仓库统计报表
	public function dc_tjbaobiao()
	{	
	    init_app_page();
	    $sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$cate_id = intval($_REQUEST['cate_id']);
		$account_info = $GLOBALS['account_info'];;
		$supplier_id = $account_info['supplier_id'];
		
		
			$sqlstr="where a.slid=$slid";
		if($cid){ //配送中心
		$sqlstr.=' and a.cid='.$cid;	
		}
		if($cate_id){ //配送中心
		$sqlstr.=' and a.cate_id='.$cate_id;	
		}
		
		$sql="select a.*,b.name as cname from ".DB_PREFIX."cangku_menu a left join ".DB_PREFIX."cangku b on a.cid=b.id  ".$sqlstr." ";
		//echo($sql);
		$list = $GLOBALS['db']->getall($sql);
				foreach($list as $k=>$v){
			$list[$k]['xhtock'] = $v['stock']-$v['mstock'];
			}
		$GLOBALS['tmpl']->assign("list",$list);
		
		//分类
		$conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$conditions .= " and location_id=".$slid;
		//$sqlsort = " SELECT  * FROM from ".DB_PREFIX."cangku_menu ";
				$sqlsort = " SELECT DISTINCT * FROM from ".DB_PREFIX." cangku_menu ";
				echo($sqlsort);
			$listsort = $GLOBALS['db']->getAll($sqlsort);	
		//$sqlsort.=$conditions . " order by sort desc";
/*
		$listsort = array();		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sqlsort);		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$listsort[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$listsort[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$listsort[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$listsort[] = $wmenu3;
						}
					}
				}
			}
		}
		*/
         var_dump($listsort);
		$GLOBALS['tmpl']->assign("sortlist", $listsort);
	

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		
		
		$GLOBALS['tmpl']->assign("page_title", "统计报表");
		$GLOBALS['tmpl']->display("pages/cangku/dc_tjbaobiao.html");
	}
	public function dc_del_cangku(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."cangku where id='$sid'");
		header("location:/biz.php?ctl=cangku&act=dc_cangku&id=$slid");
	}
	public function dc_del_cangku_gys(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."cangku_gys where id='$sid'");
		header("location:/biz.php?ctl=cangku&act=dc_del_cangku_gys&id=$slid");
	}
	public function stocktoo()
	{
	init_app_page();
	$account_info = $GLOBALS['account_info'];
	$slid=$account_info['slid'];
	$GLOBALS['db']->query("update fanwe_dc_menu set stock=0 where location_id=$slid");
	$GLOBALS['db']->query("update fanwe_cangku_menu set mstock=0 where slid=$slid");
	showBizSuccess("成功",0,url("biz","cangku#findstock&id=$slid"));
	}
	 /*
	 函数名：Excel
	 函数功能：Excel导出数据
	 作者：柴仲健
	 时间：20161227
	 */
	 public function Excel()
	 {
	 require APP_ROOT_PATH . 'app/Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');
        $slid=$_REQUEST['id'];
		$sqlstr="where 1=1";
		$sqlstr.=" and a.slid=".$slid;
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$list = $GLOBALS['db']->getAll("select a.*,c.name as cname from fanwe_cangku_log a left join fanwe_cangku c on a.cid=c.id $sqlstr order by a.id desc ");
		
		for ($column = 'A'; $column <= 'P'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'id')
					->setCellValue('B1', '类型')
					->setCellValue('C1', '时间')
					->setCellValue('D1', '出库明细')
					->setCellValue('E1', '仓库名称')
					->setCellValue('F1', '业务类型')
					->setCellValue('G1', '单据号')
					->setCellValue('H1', '单据备注')
					->setCellValue('I1', '收货人')
					->setCellValue('J1', '理货员')
					->setCellValue('K1', '总金额')
					->setCellValue('L1', '总数量')
					->setCellValue('M1', '体积')
					->setCellValue('N1', '重量')
					->setCellValue('O1', '运费')
					->setCellValue('P1', '物流公司');

		$i=0;
		foreach($list as $k=>$v){
			$v['ctime'] = date("Y-m-d h:i:s",$v['ctime']);
			if($v['type']==1){ $v['type']="入库"; }else
			{
				if($v['type']==2){ $v['type']="出库"; }
			}
			//$v['isdisable'] = $v['isdisable'] ? '启用' : '禁用';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($i+2), $v['id'])
				->setCellValue('B'.intval($i+2), $v['type'])
				->setCellValue('C'.intval($i+2), $v['ctime'])
				->setCellValue('D'.intval($i+2), '具体明细')
				->setCellValue('E'.intval($i+2), $v['cname'])
				->setCellValue('F'.intval($i+2), $v['ywsort'])
				->setCellValue('G'.intval($i+2), $v['danjuhao'])
				->setCellValue('H'.intval($i+2), $v['memo'])
				->setCellValue('I'.intval($i+2), $v['gonghuo'])
				->setCellValue('J'.intval($i+2), $v['lihuo_user'])
				->setCellValue('K'.intval($i+2), $v['zmoney'])
				->setCellValue('L'.intval($i+2), $v['znum'])
				->setCellValue('M'.intval($i+2), $v['ztiji'])
				->setCellValue('N'.intval($i+2), $v['zweigh'])
				->setCellValue('O'.intval($i+2), $v['wuliu_yunfe'])
				->setCellValue('P'.intval($i+2), $v['wuliu_company']);
				$i++;
				
				$detail=unserialize($v['dd_detail']);
				if(is_array($detail)){
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($i+2), '')
				->setCellValue('B'.intval($i+2), '')
				->setCellValue('C'.intval($i+2), '')
				->setCellValue('D'.intval($i+2), '名称')
				->setCellValue('E'.intval($i+2), '条码')
				->setCellValue('F'.intval($i+2), '数量')
				->setCellValue('G'.intval($i+2), "单位")
				->setCellValue('H'.intval($i+2), "价格")
				->setCellValue('I'.intval($i+2), "总价")
				->setCellValue('J'.intval($i+2), "规格")
				->setCellValue('K'.intval($i+2), "")
				->setCellValue('L'.intval($i+2), "")
				->setCellValue('M'.intval($i+2), "")
				->setCellValue('N'.intval($i+2), "")
				->setCellValue('O'.intval($i+2), "")
				->setCellValue('P'.intval($i+2), "");
				
				
				foreach($detail as $kd=>$vd){
					if($vd['unit_type']==1){
					$sunit=	$vd['funit'];
					}else{
					$sunit=	$vd['unit'];	
					}
					
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($i+$kd), '')
				->setCellValue('B'.intval($i+$kd), '')
				->setCellValue('C'.intval($i+$kd), '')
				->setCellValue('D'.intval($i+$kd), $vd['name'])
				->setCellValue('E'.intval($i+$kd), $vd['barcode'])
				->setCellValue('F'.intval($i+$kd), $vd['num'])
				->setCellValue('G'.intval($i+$kd), $sunit)
				->setCellValue('H'.intval($i+$kd), $vd['price'])
				->setCellValue('I'.intval($i+$kd), $vd['zmoney'])
				->setCellValue('J'.intval($i+$kd), $v['times'])
				->setCellValue('K'.intval($i+$kd), "")
				->setCellValue('L'.intval($i+$kd), "")
				->setCellValue('M'.intval($i+$kd), "")
				->setCellValue('N'.intval($i+$kd), "")
				->setCellValue('O'.intval($i+$kd), "")
				->setCellValue('P'.intval($i+$kd), "");	
				$i++;
				}			
			}	
		}
		$objPHPExcel->getActiveSheet()->setTitle('出入库明细');
		$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'出入库明细.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');		
		exit();

	 }
	  /*
	 函数名：Excelghmx
	 函数功能：Excel导出供货明细数据
	 作者：柴仲健
	 时间：20161227
	 */
	 public function Excelghmx()
	 {
	 

	 
	 
	 
	
 require APP_ROOT_PATH . 'app/Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
				$slid = intval($_REQUEST['id']);
		$list = $GLOBALS['db']->getAll("select a.*,b.name as cname from fanwe_cangku_menu_gys a left join fanwe_cangku b on a.cid=b.id where a.slid=".$slid." order by a.id desc ");
		$linshi=array(
		"1"=>"临时客户",
		"2"=>"临时运输商",
		"3"=>"临时供应商"		
		);
		
		     foreach($list as $k=>$v){
			
		$gonghuoren=$v['gonghuoren'];
		
		$gonghuoren_arr=explode('_',$gonghuoren);
		$gonghuoren_type=$gonghuoren_arr[0];
		$gonghuoren_id=$gonghuoren_arr[1];	
		if ($gonghuoren_type=='linshi'){
		  $v['gys_name']=$linshi[$gonghuoren_id];
		}elseif($gonghuoren_type=='slid'){
		  $v['gys_name']=$slid_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='citygys'){
		  $v['gys_name']=$city_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='localgys'){
		  $v['gys_name']=$local_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='other'){
		  $v['gys_name']=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$gonghuoren_id);
		}elseif($gonghuoren_type=='user'){
		  $v['gys_name']='存货用户：'.$GLOBALS['db']->getOne("select user_name from fanwe_user where id=".$gonghuoren_id);
		}
		$list[$k]=$v;		
		}
		
		for ($column = 'A'; $column <= 'I'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}

$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '供货商')
					->setCellValue('B1', '存放仓库')
					->setCellValue('C1', '条码')
					->setCellValue('D1', '名称')
					->setCellValue('E1', '数量')
					->setCellValue('F1', '主单位')
					->setCellValue('G1', '副单位')
					->setCellValue('H1', '规格')
					->setCellValue('I1', '时间');

		
		foreach($list as $t=>$v){
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($t+2), $v['gys_name'])
				->setCellValue('B'.intval($t+2), $v['cname'])
				->setCellValue('C'.intval($t+2), $v['mbarcode'])
				->setCellValue('D'.intval($t+2), $v['mname'])
				->setCellValue('E'.intval($t+2), $v['stock'])
				->setCellValue('F'.intval($t+2), $v['unit'])
				->setCellValue('G'.intval($t+2), $v['funit'])
				->setCellValue('H'.intval($t+2), $v['times'])
				->setCellValue('I'.intval($t+2), $v['ctime']);
		}
		$objPHPExcel->getActiveSheet()->setTitle('供货明细');
		$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'供货明细.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		$GLOBALS['tmpl']->assign("page_title", "导出数据");
		$GLOBALS['tmpl']->display("pages/cangku/cangku_log.html");
		exit();
		

	 }
//库存查询信息导出
public function Excelkccx()
	 {
	 require APP_ROOT_PATH . 'app/Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');
		$slid = intval($_REQUEST['id']);
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$list = $GLOBALS['db']->getAll("select a.*,b.name as cname from fanwe_cangku_menu a left join fanwe_cangku b on a.cid=b.id where a.slid=".$slid." order by a.ctime desc  ");
		
		for ($column = 'A'; $column <= 'J'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '菜单id')
					->setCellValue('B1', '仓库名称')
					->setCellValue('C1', '条码')
					->setCellValue('D1', '名称')
					->setCellValue('E1', '库存')
					->setCellValue('F1', '主单位')
					->setCellValue('G1', '副单位')
					->setCellValue('H1', '规格')
					->setCellValue('I1', '类型')
					->setCellValue('J1', '最后更新时间');

		
		foreach($list as $k=>$v){
			//$v['ctime'] = to_date($v['ctime'],"Y-m-d H:i:s");
			if($v['type']==0){ 
			$v['type']="出售商品"; 
			}
			if($v['type']==2){
			$v['type']="消耗品"; 
			}			
			//$v['isdisable'] = $v['isdisable'] ? '启用' : '禁用';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), $v['mid'])
				->setCellValue('B'.intval($k+2), $v['cname'])
				->setCellValue('C'.intval($k+2), $v['mbarcode'])
				->setCellValue('D'.intval($k+2), $v['mname'])
				->setCellValue('E'.intval($k+2), $v['mstock'])
				->setCellValue('F'.intval($k+2), $v['unit'])
				->setCellValue('G'.intval($k+2), $v['funit'])
				->setCellValue('H'.intval($k+2), $v['times'])
				->setCellValue('I'.intval($k+2), $v['type'])
				->setCellValue('J'.intval($k+2), $v['ctime']);
		}
		$objPHPExcel->getActiveSheet()->setTitle('库存明细');
		$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'库存明细.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		$GLOBALS['tmpl']->assign("page_title", "导出数据");
		$GLOBALS['tmpl']->display("pages/cangku/my_center.html");
		exit();

	 }
	 //统计报表息导出
public function Exceltjbb()
	 {
	 require APP_ROOT_PATH . 'app/Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');
		$slid = intval($_REQUEST['id']);
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$list = $GLOBALS['db']->getAll("select a.*,b.name as cname from fanwe_cangku_menu a left join fanwe_cangku b on a.cid=b.id where a.slid=".$slid."  ");
				
		foreach($list as $k=>$v){
			$list[$k]['xhtock'] = $v['stock']-$v['mstock'];
			}
	
		for ($column = 'A'; $column <= 'E'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}
			
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '商品id')
					->setCellValue('B1', '商品名称')
					->setCellValue('C1', '入库数量')
					->setCellValue('D1', '商品剩余库存')
					->setCellValue('E1', '已使用库存');

			foreach($list as $k=>$v){
			//$v['isdisable'] = $v['isdisable'] ? '启用' : '禁用';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), $v['id'])
				->setCellValue('B'.intval($k+2), $v['mname'])
				->setCellValue('C'.intval($k+2), $v['stock'])
				->setCellValue('D'.intval($k+2), $v['mstock'])
				->setCellValue('E'.intval($k+2), $v['xhtock']);
		}
		$objPHPExcel->getActiveSheet()->setTitle('统计报表');
		$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'统计报表.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		$GLOBALS['tmpl']->assign("page_title", "导出数据");
		$GLOBALS['tmpl']->display("pages/cangku/dc_tjbaobiao.html");
		exit();

	 }
	 
	function get_gonghuoren_name($supplier_id,$slid,$gonghuoren){
		
		$linshi=array(
		"1"=>"临时客户",
		"2"=>"临时运输商",
		"3"=>"临时供应商"		
		);
		
	    $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
		$slid_names = array();
        $slid_names = array_reduce($slidlist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		$gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
	  	$sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";	
		$gyslist=$GLOBALS['db']->getAll($sql_gys);

		
		$city_names = array();
        $city_names = array_reduce($gyslist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
				
		$location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);				
		$local_names = array();
        $local_names = array_reduce($location_gys, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		$location_bumen=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_bumen where slid=".$slid);				
		$local_bumen = array();
        $local_bumen = array_reduce($location_bumen, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
		$cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);				
		$cangku_names = array();
        $cangku_names = array_reduce($cangku_list, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));
		
        $gonghuoren_arr=explode('_',$gonghuoren);
		$gonghuoren_type=$gonghuoren_arr[0];
		$gonghuoren_id=$gonghuoren_arr[1];	
		if ($gonghuoren_type=='linshi'){
		  $gys_name=$linshi[$gonghuoren_id];
		}elseif($gonghuoren_type=='slid'){
		  $gys_name=$slid_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='citygys'){
		  $gys_name=$city_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='localgys'){
		  $gys_name=$local_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='cangku'){
		  $gys_name=$cangku_names[$gonghuoren_id];
		}elseif($gonghuoren_type=='bumen'){
		  $gys_name=$local_bumen[$gonghuoren_id];  
		}elseif($gonghuoren_type=='other'){
		  $gys_name=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$gonghuoren_id);
		}elseif($gonghuoren_type=='user'){
		  $gys_name='存货用户：'.$GLOBALS['db']->getOne("select user_name from fanwe_user where id=".$gonghuoren_id);
		}
	  return $gys_name;
	}
}

?>