<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/dc.php";
class dcModule extends BizBaseModule
{
	public function __construct()
	{
		parent::__construct();
		global_run();
		$this->check_auth();
		$kcnx=array(

                 "1"=>"现制商品",
                 "2"=>"预制商品",
                 "3"=>"外购商品",


        );
        $this->kcnx=$kcnx;
	}



	public function index()
	{
		  /* 基本参数初始化 */
		// var_dump($GLOBALS['account_info']);
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		//echo $supplier_id ;
		$account_id = $account_info['id'];
		
		$slid=$account_info['slid'];
		$account_info['is_main']=$GLOBALS['db']->getOne("select is_main from fanwe_supplier_location where id=".$slid);
	    if ($account_info['is_main']=='1'){
		$slidlist=$GLOBALS['db']->getAll("select id from fanwe_supplier_location where supplier_id=".$supplier_id);
    	$account_info['location_ids']= array_reduce($slidlist, create_function('$v,$w', '$v[]=$w["id"];return $v;'));	
		}
		

		/* 获取参数 */

		/* 业务逻辑部分 */
		$conditions = " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件


		// 需要连表操作 只查询支持门店的

		$conditions .= " and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";


		$sql_count = " select count(id) from " . DB_PREFIX . "supplier_location";
		//$sql = " select id,name,preview,is_close from " . DB_PREFIX . "supplier_location";
$sql = " select * from " . DB_PREFIX . "supplier_location";
		/* 分页 */
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);


		$list = $GLOBALS['db']->getAll($sql.$conditions . " order by id desc limit " . $limit);
		//获取分类数量
		$menu_cate_count = $GLOBALS['db']->getAll("select count(*) as count,location_id from ".DB_PREFIX."dc_supplier_menu_cate where is_effect=1 and location_id in(" . implode(",", $account_info['location_ids']) . ") GROUP BY location_id");
		//var_dump($menu_cate_count);
		foreach ($menu_cate_count as $k=>$v){
			$f_menu_cate_count[$v['location_id']] = $v;
		}
		$menu_count = $GLOBALS['db']->getAll("select count(*) as count,location_id from ".DB_PREFIX."dc_menu where is_effect=1 and is_delete=1 and location_id in(" . implode(",", $account_info['location_ids']) . ") GROUP BY location_id");

		foreach ($menu_count as $k=>$v){
			$f_menu_count[$v['location_id']] = $v;
		}

		foreach ($list as $k=>$v){
			$list[$k]['menu_cate_count'] = $f_menu_cate_count[$v['id']]['count']?$f_menu_cate_count[$v['id']]['count']:0;
			$list[$k]['menu_count'] = $f_menu_count[$v['id']]['count']?$f_menu_count[$v['id']]['count']:0;
			$list[$k]['menu_cate_url'] = url("biz","dc#dc_menu_cate_index",array("id"=>$v['id']));
			$list[$k]['menu_url'] = url("biz","dc#dc_menu_index",array("id"=>$v['id']));
		}

		/* 数据 */
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "外卖预订管理");
		$GLOBALS['tmpl']->display("pages/dc/index.html");
	}

	/**
	 * APP收款员工管理
	 *
	 */
	public function app_user(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (sname='$kw' or sno='$kw' or tel='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "app_user where slid=$slid and isdisable=$isdd $str order by sid desc ");



		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "APP收款员工管理");
		$GLOBALS['tmpl']->display("pages/dc/app_user.html");
	}

	public function app_user_add(){
		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$sno = $_REQUEST['sno'];
		$sname = $_REQUEST['sname'];
		$passwd = $_REQUEST['passwd'];
		$tel = $_REQUEST['tel'];
		$isdisable = $_REQUEST['isdisable'];


		if($sno){
			$data['slid'] = $slid;
			$data['sno'] = $sno;
			$data['sname'] = $sname;
			$data['passwd'] = $passwd;
			$data['tel'] = $tel;
			$data['isdisable'] = $isdisable;
		}

		if($sid && $data){
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "app_user where slid='$slid' and sno='$sno' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."app_user",$data,"UPDATE","sid='$sid'");
				header("location:/biz.php?ctl=dc&act=app_user&id=$slid");
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);
				$GLOBALS['tmpl']->assign("has", '1');
			}
		}elseif($data){
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "app_user where slid='$slid' and sno='$sno' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."app_user",$data);
				header("location:/biz.php?ctl=dc&act=app_user&id=$slid");
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);
				$GLOBALS['tmpl']->assign("has", '1');
			}
		}else{

			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "app_user where sid=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加收款员");
		$GLOBALS['tmpl']->display("pages/dc/app_user_add.html");
	}

public function app_user_edit(){
	init_app_page();

	$sid = intval($_REQUEST['sid']);
	$slid = intval($_REQUEST['id']);
	(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
	$sno = $_REQUEST['sno'];
	$sname = $_REQUEST['sname'];
	$passwd = $_REQUEST['passwd'];
	$tel = $_REQUEST['tel'];
	$isdisable = $_REQUEST['isdisable'];


	if($sno){
		$data['slid'] = $slid;
		$data['sno'] = $sno;
		$data['sname'] = $sname;
		$data['passwd'] = $passwd;
		$data['tel'] = $tel;
		$data['isdisable'] = $isdisable;
	}

	if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."app_user",$data,"UPDATE","sid='$sid'");
			header("location:/biz.php?ctl=dc&act=app_user&id=$slid");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $data);
			$GLOBALS['tmpl']->assign("has", '1');

	} else{

		$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "app_user where sid=$sid limit 1");

		/* 数据 */
		$GLOBALS['tmpl']->assign("syy", $syy);
	}

	$GLOBALS['tmpl']->assign("sid",$sid);
	$GLOBALS['tmpl']->assign("slid",$slid);
	$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

	/* 系统默认 */
	$GLOBALS['tmpl']->assign("page_title", "收款员信息修改");
	$GLOBALS['tmpl']->display("pages/dc/app_user_edit.html");
}

	public function app_user_del(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."app_user where sid='$sid'");
		header("location:/biz.php?ctl=dc&act=app_user&id=$slid");
	}

	/**
	 * 订餐设置
	 */
	public function dc_set()
	{
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];	
		$account_id = $account_info['id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件


		// 只查询支持门店的

		$conditions .= " and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";


		$sql = " select * from " . DB_PREFIX . "supplier_location";
		
		$data = $GLOBALS['db']->getRow($sql.$conditions);
		$data["isopen_waiter"]=intval($data["isopen_waiter"]);
		
		if(empty($data)){
			showBizErr("数据不存在/没有管理权限！",0,url("biz","dc#index"));
		}

		//获取餐厅分类
		$dc_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate where type=0 and is_effect=1");
		$dc_cate_cur = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate_supplier_location_link where location_id = ".$data['id']);
		foreach ($dc_cate_cur as $k=>$v){
			$f_dc_cate_cur[] = $v['dc_cate_id'];
		}
		foreach ($dc_cate as $k=>$v){
			if(in_array($v['id'], $f_dc_cate_cur)){
				$dc_cate[$k]['is_checked'] = 1;
			}
		}

		//获取时间数据
		$open_time = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$data['id']);

		$open_time = array_sort($open_time,"begin_time_h");
		foreach ($open_time as $k=>$v){
			$v['end_time_m'] = str_pad($v['end_time_m'],2,0,STR_PAD_LEFT);
			$temp_time['begin_time'] = $v['begin_time_h'].":".$v['begin_time_m'];
			$temp_time['end_time'] = $v['end_time_h'].":".$v['end_time_m'];
			$open_time_list[] = $temp_time;
		}

		//获取配送地址数据
		$delivery_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_delivery where location_id=".$data['id']." order by id asc");

		//获取打包费数据
		$package_conf =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_package_conf where location_id=".$data['id']);

		/* 数据 */
		$GLOBALS['tmpl']->assign("form_url", url("biz", "dc#do_save_dcset"));

		$GLOBALS['tmpl']->assign("dc_cate", $dc_cate);
		$GLOBALS['tmpl']->assign("open_time_list", $open_time_list);
		$GLOBALS['tmpl']->assign("delivery_data", $delivery_data);
		$GLOBALS['tmpl']->assign("package_conf", $package_conf);
		$GLOBALS['tmpl']->assign("vo", $data);
        

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "餐厅设置");
		$GLOBALS['tmpl']->display("pages/dc/dc_set.html");
	}

	/**
	 * 保存配置
	 */
	public function do_save_dcset(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$account_id = $account_info['id'];
        
		
		
		/* 获取参数 */
		$datasl['is_reserve'] = intval($_REQUEST['is_reserve']);
		$datasl['isopen_waiter'] = intval($_REQUEST['isopen_waiter']);
		$datasl['dc_allow_ddf'] = intval($_REQUEST['dc_allow_ddf']);
		$datasl['dc_allow_wxf'] = intval($_REQUEST['dc_allow_wxf']);
		$datasl['dc_allow_yzf'] = intval($_REQUEST['dc_allow_yzf']);
		$datasl['dc_is_checktel'] = intval($_REQUEST['dc_is_checktel']);
		$datasl['isopen_qcode'] = intval($_REQUEST['isopen_qcode']);
		$datasl['max_delivery_scale']=max($_REQUEST['scale']);
		$datasl['dc_location_notice']=strim($_REQUEST['dc_location_notice']);
		$dc_cate_ids = $_REQUEST['cate_id'];
		
		
		//营业时间
		$id = intval($_REQUEST['id']);
		$op_begin_time = $_REQUEST['op_begin_time'];
		$op_end_time = $_REQUEST['op_end_time'];

		//配送价格

		$scale = $_REQUEST['scale'];
		$start_price = $_REQUEST['start_price'];
		$delivery_price = $_REQUEST['delivery_price'];

		//打包费用
		$package_conf['package_price'] = floatval($_REQUEST['package_price']);
		$package_conf['package_start_price'] = floatval($_REQUEST['package_start_price']);


		$conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";

		$sql = " select * from " . DB_PREFIX . "supplier_location";
		$data = $GLOBALS['db']->getRow($sql.$conditions);
        
		if(empty($data)){
			$data['status'] = 0;
			$data['info'] = "数据不存在/没有管理权限！222";
			ajax_return($data);
		}


		/* 业务逻辑部分 */

		//保存餐厅分类
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cate_supplier_location_link where location_id=".$id);
		foreach($dc_cate_ids as $dc_cate)
		{
			$cate_data['dc_cate_id'] = $dc_cate;
			$cate_data['location_id'] = $id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cate_supplier_location_link",$cate_data);
		}
		syn_supplier_location_dc_cate_match($id);

		//清除营业时间
		//$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$id);
		foreach ($op_begin_time as $k=>$v){
			if($v){
				$temp_op_begin_time = explode(":",$v);
				$temp_op_end_time = explode(":",$op_end_time[$k]);
				$temp_time['begin_time_h'][] = trim($temp_op_begin_time[0]);
				$temp_time['begin_time_m'][] = trim($temp_op_begin_time[1]);
				$temp_time['end_time_h'][] = trim($temp_op_end_time[0]);
				$temp_time['end_time_m'][] = trim($temp_op_end_time[1]);

				//保存时间数组($table, $field_values, $mode = 'INSERT'
// 	            $GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_location_open_time",$temp_time);
			}
		}
		syn_supplier_location_open_time_match($temp_time,$id);
		//清除配送配置
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_delivery where location_id=".$id);
		foreach ($scale as $k=>$v){
			if($v){

				$temp_delivery['scale'] = floatval($scale[$k]);
				$temp_delivery['start_price'] = floatval($start_price[$k]);
				$temp_delivery['delivery_price'] = floatval($delivery_price[$k]);
				$temp_delivery['location_id'] = $id;
				//保存配送配置
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_delivery",$temp_delivery);
			}
		}

		//保存打包费用
		$package_conf['location_id'] = $id;

		$package_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_package_conf where location_id =".$package_conf['location_id']);
		if($package_id)
		{
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_package_conf",$package_conf,"UPDATE","location_id=".$package_conf['location_id']);
		}
		else
		{

			 $GLOBALS['db']->autoExecute(DB_PREFIX."dc_package_conf",$package_conf);
		}


		//更新坐标
		$menu['xpoint']=$data['xpoint'];
		$menu['ypoint']=$data['ypoint'];
		$menu['location_id']=$id;
		sys_location_menu_xypoint($menu);
        
		//更新主表
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$datasl,"UPDATE","id=".$id);

		/* 数据 */
		$data['status'] = 1;
		$data['jump'] = url("biz","dc#index");
		$data['info'] = "修改成功";
		ajax_return($data);
	}


	public function set_is_close(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$account_id = $account_info['id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " and id=".$id." and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";

		$sql = " select * from " . DB_PREFIX . "supplier_location";

		$data = $GLOBALS['db']->getRow($sql.$conditions);

		if(empty($data)){
			$data['status'] = 0;
			$data['info'] = "数据不存在/没有管理权限！";
			ajax_return($data);
		}

		$s_value = $data['is_close']>0?0:1;

		if($GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",array("is_close"=>$s_value),"UPDATE","id=".$id)){
			$data['status'] = 1;
			$data['info']="操作成功";
			$data['is_close'] = $s_value;
		}else{
			$data['status']=0;
			$data['info']="操作失败";
		}
		ajax_return($data);

	}

	/**
	 * 口味组分类
	 */
	public function dc_menu_taste(){
		  /* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		 /* 业务逻辑部分 */
	   // $conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " where location_id=".$id."  ";

		$sql_count = " select count(id) from " . DB_PREFIX . "dc_supplier_taste";
		$sql = " select * from " . DB_PREFIX . "dc_supplier_taste";

		/* 分页 */
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);


		$list = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc limit " . $limit);

		foreach($list as $key=>$val)
		{
			 $tmp=json_decode($list[$key]["flavor"],true);
			$list[$key]["flavor"]="";
		//	var_dump($tmp);
			foreach($tmp as $v)
			{
				 $list[$key]["flavor"].="口味:".urldecode($v["name"])." 价格:".$v["price"];
			}
			//$list[$key]["flavor"]
			 $list[$key]["switchbox"]=($list[$key]["switchbox"]=="1")?"单选":"多选";
		}

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $id);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "口味管理");
		$GLOBALS['tmpl']->display("pages/dc/menu_taste.html");



		}



	/**
	 * 单位分类
	 */
	public function dc_menu_unit_index(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		 /* 业务逻辑部分 */
	   // $conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " where location_id=".$id."  ";

		$sql_count = " select count(id) from " . DB_PREFIX . "dc_supplier_unit_cate";
		$sql = " select id,name,is_effect,sort from " . DB_PREFIX . "dc_supplier_unit_cate ";

		/* 分页 */
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);


		$list = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc limit " . $limit);

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $id);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "单位管理");
		$GLOBALS['tmpl']->display("pages/dc/menu_unit_index.html");

	}






	/**
	 * 餐厅打印分类
	 */
	public function dc_menu_print_index(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		 /* 业务逻辑部分 */
	  //  $conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " where location_id=".$id."  ";

		$sql_count = " select count(id) from " . DB_PREFIX . "dc_supplier_print_cate";
		$sql = " select id,name,is_effect,sort,type from " . DB_PREFIX . "dc_supplier_print_cate ";

		/* 分页 */
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);


		$list = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc limit " . $limit);

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $id);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "餐厅打印管理");
		$GLOBALS['tmpl']->display("pages/dc/menu_print_index.html");

	}


		/**
	 *口味添加
	 */
	public function load_add_menu_taste_weebox(){
			/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		$location_id = intval($_REQUEST['location_id']);


		   /* 业务逻辑部分 */
		$conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " and location_id=".$location_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";


		$sql = " select id,name,is_effect,cate_id,price,image from " . DB_PREFIX . "dc_menu ";



		$list = $GLOBALS['db']->getAll($sql.$conditions);



		$GLOBALS['tmpl']->assign("list", $list);



		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_menu_taste_weebox.html");
		ajax_return($data);
	}
	/**
	 * 菜单分类添加
	 */
	public function load_add_unit_cate_weebox(){
		$location_id = intval($_REQUEST['location_id']);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_unit_cate_weebox.html");
		ajax_return($data);
	}

	public function do_save_unit_cate(){
		/*初始化*/
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*活出参数*/
		$location_id = $_REQUEST['location_id'];
		$name = strim($_REQUEST['cate_name']);

		/*业务逻辑部分*/
		$root['status'] = 0;
		$root['info'] = "";
	/*    if(!in_array($location_id, $account_info['location_ids'])){
			$root['status'] = 0;
			$root['info'] = "您没有添加权限";
			ajax_return($root);
		}*/
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_unit_cate where name='".$name."' and location_id = ".$location_id)){
			$root['status'] = 0;
			$root['info'] = "单位名称重复";
			ajax_return($root);
		}


		$data = array();
		$data['name'] = $name;
		$data['sort'] = 100;
		$data['is_effect'] = 1;
		$data['supplier_id'] = $supplier_id;
		$data['location_id'] = $location_id;

		if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_unit_cate",$data)){
			$root['status']=1;
			$root['jump']= url("biz","dc#dc_menu_unit_index",array('id'=>$location_id));
		}
		ajax_return($root);

	}





	/**
	 *分类删除
	 */
	public function dc_unit_cate_del(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_unit_cate where id=".$id);
		//判断是否有权限和数据存在
	  /*  if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}*/


		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_unit_cate where id=".$id);
		/* 数据 */
		$root['status'] =1;
		$root['jump'] = url("biz","dc#dc_menu_unit_index",array('id'=>$data['location_id']));


		/* ajax返回数据 */
		ajax_return($root);

	}







	/**
	 *分类删除
	 */
	public function dc_menu_print_del(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_print_cate where id=".$id);
		//判断是否有权限和数据存在
	  /*  if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}*/


		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_print_cate where id=".$id);
		/* 数据 */
		$root['status'] =1;
		$root['jump'] = url("biz","dc#dc_menu_print_index",array('id'=>$data['location_id']));


		/* ajax返回数据 */
		ajax_return($root);

	}


	/**
	 * 菜单分类添加
	 */
	public function load_add_print_cate_weebox(){
		$location_id = intval($_REQUEST['location_id']);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_print_cate_weebox.html");
		ajax_return($data);
	}

	public function do_save_print_cate(){
		/*初始化*/
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*活出参数*/
		$location_id = $_REQUEST['location_id'];
		$name = strim($_REQUEST['cate_name']);
		  $type = strim($_REQUEST['type']);
		/*业务逻辑部分*/
		$root['status'] = 0;
		$root['info'] = "";
	 /*   if(!in_array($location_id, $account_info['location_ids'])){
			$root['status'] = 0;
			$root['info'] = "您没有添加权限";
			ajax_return($root);
		}*/
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_print_cate where name='".$name."' and location_id = ".$location_id)){
			$root['status'] = 0;
			$root['info'] = "名称重复";
			ajax_return($root);
		}


		$data = array();
		$data['name'] = $name;   $data['type'] = $type;
		$data['sort'] = 100;
		$data['is_effect'] = 1;
		$data['supplier_id'] = $supplier_id;
		$data['location_id'] = $location_id;

		if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_print_cate",$data)){
			$root['status']=1;
			$root['jump']= url("biz","dc#dc_menu_print_index",array('id'=>$location_id));
		}
		ajax_return($root);

	}



	/**
	 *分类删除
	 */
	public function dc_print_cate_del(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_print_cate where id=".$id);
		//判断是否有权限和数据存在
	   /* if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}*/


		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_print_cate where id=".$id);
		/* 数据 */
		$root['status'] =1;
		$root['jump'] = url("biz","dc#dc_menu_print_index",array('id'=>$data['location_id']));


		/* ajax返回数据 */
		ajax_return($root);

	}














































	/*=========================菜单分类部分==================================*/

	/**
	 * 菜单分类
	 */
	public function dc_menu_cate_index(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		 /* 业务逻辑部分 */
		//$conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		
		//武林二次开发
		$conditions .= " where is_effect=1 and  wlevel<4 "; // 查询条件
		
		// 只查询支持门店的
		$conditions .= " and location_id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";

//		$sql_count = " select count(id) from " . DB_PREFIX . "dc_supplier_menu_cate";
//		$sql = " select id,name,is_effect,sort from " . DB_PREFIX . "dc_supplier_menu_cate ";

		/* 分页 */
//		$page_size = 10;
//		$page = intval($_REQUEST['p']);
//		if ($page == 0)
//			$page = 1;
//		$limit = (($page - 1) * $page_size) . "," . $page_size;
//
//		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
//		$page = new Page($total, $page_size); // 初始化分页对象
//		$p = $page->show();
//		$GLOBALS['tmpl']->assign('pages', $p);


//		$list = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc limit " . $limit);
		
		$sql = " select * from " . DB_PREFIX . "dc_supplier_menu_cate ";
		
		$list = array();
		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc");
		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$list[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$list[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$list[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$list[] = $wmenu3;
						}
					}
				}
			}
		}

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $id);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("sublist", $wsublist);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "菜单分类管理");
		$GLOBALS['tmpl']->display("pages/dc/menu_cate_index.html");

	}





	public function add_zp(){
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		/*获取参数*/
		$zpid = intval($_REQUEST['zpid']);

		$GLOBALS['tmpl']->assign("id", $id);
		$GLOBALS['tmpl']->assign("zpid", $zpid);

		$zp = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "dc_zp where zpid=$zpid limit 1");


		/* 数据 */
		$GLOBALS['tmpl']->assign("zp", $zp);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));



		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", $zpid ? '编辑/查看桌牌' : "添加桌牌");
		$GLOBALS['tmpl']->display("pages/dc/add_menu_zp.html");
	}



	/**
	 * 拍桌管理
	 */
	public function dc_menu_zp_index(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$check=$GLOBALS['db']->getRow("select a.*,b.id as sid from ims_tiny_wmall_plus_tables_category a left join ims_tiny_wmall_plus_store b on a.sid=b.id where slid=".$id);
		//var_dump($check);
	    if($check){
		$url='http://wm.678sh.com/addons/we7_wmall_plus/admin/index.php?c=site&a=entry&op=table_list&do=table&m=we7_wmall_plus';
		header("location:$url");
        exit;		
		}
 
	
		
		$zpid = intval($_REQUEST['zpid']);
		$txt = $_REQUEST['txt'];
		$zpname = $_REQUEST['zpname'];
		$eachdesk=intval($_REQUEST['eachdesk'])?intval($_REQUEST['eachdesk']):4;
		
		if(empty($zpname)){

			$list = $GLOBALS['db']->getAll(" select zpid,zpname,txt,eachdesk from " . DB_PREFIX . "dc_zp where slid=$id order by zpid desc ");

			/* 数据 */
			$GLOBALS['tmpl']->assign("list", $list);
			$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


			/* 系统默认 */
			$GLOBALS['tmpl']->assign("page_title", "桌牌管理");
			$GLOBALS['tmpl']->display("pages/dc/menu_zp_index.html");
		}elseif(empty($zpid)){//新增区域

			$data['slid']=$id;
			$data['zpname']=$zpname;
			$data['txt']=$txt;
			$data['eachdesk']=$eachdesk;

			 $GLOBALS['db']->autoExecute(DB_PREFIX."dc_zp",$data);
			header("location:/biz.php?ctl=dc&act=dc_menu_zp_index&id=$id");
		}elseif($zpid){//修改区域
			$data['zpname'] = $zpname;
			$data['txt'] = $txt;
			$data['eachdesk']=$eachdesk;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_zp",$data,"UPDATE","zpid='$zpid'");
			header("location:/biz.php?ctl=dc&act=dc_menu_zp_index&id=$id");
		}
	}

	public function dc_del_paytype(){
		$slid = intval($_REQUEST['id']);
		$dpid = $_REQUEST['dpid'];
		$type = $_REQUEST['type'];
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_paytype where dpid='$dpid'");
		showBizSuccess("删除成功",0,url("biz","dc#paytype&id=$slid&type=$type"));
		
	}
    public function dc_del_tuitype(){
		$slid = intval($_REQUEST['id']);
		$dpid = $_REQUEST['dpid'];
		$type = $_REQUEST['type'];
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_tuitype where dpid='$dpid'");
		showBizSuccess("删除成功",0,url("biz","dc#tuitype&id=$slid&type=$type"));
		
	}
	public function dc_del_guanzhangren(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."guanzhang where slid='$slid' and id='$sid'");
		showBizSuccess("删除成功",0,url("biz","dc#dc_guanzhang&id=$slid"));
		
	}
    public function danweiyuangong_del(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."danweiyuangong where slid='$slid' and id='$sid'");
		showBizSuccess("删除成功",0,url("biz","dc#danweiyuangong&id=$slid"));
		
	}
	public function dc_del_zp(){
		$id = intval($_REQUEST['id']);
		$zpid = intval($_REQUEST['zpid']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_zp where zpid='$zpid'");
		header("location:/biz.php?ctl=dc&act=dc_menu_zp_index&id=$id");
	}


	public function add_paytype(){
		init_app_page();

		$id = intval($_REQUEST['id']);
		$dpid = $_REQUEST['dpid'];
		$type = $_REQUEST['type'];
      
		$paytype = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "dc_paytype where dpid=$dpid limit 1 ");


		/* 数据 */
		$GLOBALS['tmpl']->assign("paytype", $paytype);
		$GLOBALS['tmpl']->assign("id", $id);
		$GLOBALS['tmpl']->assign("dpid", $dpid);
		$GLOBALS['tmpl']->assign("type", $type);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

        if ($type==1){
		$page_title='支付备注';
		}elseif($type==2){
		$page_title='支付折扣';
		}elseif($type==3){
		$page_title='退款原因';
		}elseif($type==4){
		$page_title='赠菜原因';
		}else{
		$page_title='支付方式';	
		}
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", $page_title);
		$GLOBALS['tmpl']->display("pages/dc/add_paytype.html");
	}


	public function paytype(){
		init_app_page();

		/*获取参数*/
		$dpid = intval($_REQUEST['dpid']);
		$slid = intval($_REQUEST['slid']);
		$id = intval($_REQUEST['id']);
		$type = intval($_REQUEST['type']);
		$ptname = $_REQUEST['ptname'];
		$data = array();
		
		if ($type==1){
		$page_title='支付备注';
		$data['memo']=$ptname;
		}elseif($type==2){
		$page_title='支付折扣';
		$data['zhekou']=round(floatval($ptname),2);
		}elseif($type==3){
		$page_title='退款原因';
		$data['tuireason']=($ptname);
		}elseif($type==4){
		$page_title='赠菜原因';
		$data['zencaiyuanyin']=($ptname);
		}else{
		$page_title='支付方式';	
		$data['ptname']=$ptname;
		}
		$data['type']=$type;
		
		
		if(!empty($dpid)){
			$res=$GLOBALS['db']->autoExecute("fanwe_dc_paytype",$data,'UPDATE',"dpid=$dpid");
			if($res){
			showBizSuccess("修改成功",0,url("biz","dc#paytype&id=$slid&type=$type"));
			}
		}elseif($ptname){
			$data['slid']=$slid;
			$res=$GLOBALS['db']->autoExecute(DB_PREFIX."dc_paytype",$data);
			if($res){
			showBizSuccess("添加成功",0,url("biz","dc#paytype&id=$slid&type=$type"));
			}
		}else{


		$list = $GLOBALS['db']->getAll(" select * from " . DB_PREFIX . "dc_paytype where slid=$id and type=$type order by dpid desc ");

		/* 数据 */
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", $page_title);
		$GLOBALS['tmpl']->display("pages/dc/paytype.html");
		}
	}







	public function dc_yg(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (sname='$kw' or sno='$kw' or tel='$kw' or realname='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "syy where slid=$slid and isdisable=$isdd $str order by sid desc ");
		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "员工管理");
		$GLOBALS['tmpl']->display("pages/dc/yg.html");
	}
	
    public function dc_waiter(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (sname='$kw' or sno='$kw' or tel='$kw' or realname='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "waiter where slid=$slid and isdisable=$isdd $str order by wid desc ");



		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "营销员管理");
		$GLOBALS['tmpl']->display("pages/dc/waiter.html");
	}
	
	public function dc_waiter_tj(){
		init_app_page();

		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
		$startstr=strtotime(to_date(NOW_TIME,"Y-m")."-1");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=to_date($startstr); 
        $end_time=to_date($startend); 
        }
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$begin_time_s = to_timespan($begin_time);
		$end_time_s = to_timespan($end_time);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='yxpnum'){
			$orderby = " order by yxpnum ".$sort;	
			}elseif($order=='tichengmoney'){
			$orderby = " order by tichengmoney ".$sort;  
            }else{	
			$orderby = " order by a.".$order." ".$sort;
			}
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else
		{
			$orderby = "";
			$sortImg=array();
		}
		//var_dump($sortImg);
		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (a.sno='$kw' or a.tel='$kw' or a.realname='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$sql="SELECT a.*,sum(b.pnum) as yxpnum,sum(b.tichengmoney) as tichengmoney FROM " . DB_PREFIX . "waiter a left join orders_tj b on a.sno=b.wsno where b.ticheng_status=1 and b.wsno>0 and a.slid=$slid and b.slid=$slid and a.isdisable=$isdd $str and (b.otime between $begin_time_s and $end_time_s)  GROUP BY b.wsno $orderby ";
				//$sql="SELECT * FROM " . DB_PREFIX . "waiter  where  (b.otime between $begin_time_s and $end_time_s)  ";
		//echo $sql;
		$list = $GLOBALS['db']->getAll($sql);
		//var_dump($list);


		/* 数据 */		
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);		
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$sort = $sort == 'asc' ? 1 : 0; //排序方式
		//模板赋值显示
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'kw', $_REQUEST['kw'] );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );
		$GLOBALS['tmpl']->assign ( 'sortType', $sortAlt );
		
		$GLOBALS['tmpl']->assign("page_title", "营销员统计");
		$GLOBALS['tmpl']->display("pages/dc/waiter_tj.html");
	}
	
	public function dc_waiter_zdtj(){
		init_app_page();

		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
		$startstr=strtotime(to_date(NOW_TIME,"Y-m")."-1");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=to_date($startstr); 
        $end_time=to_date($startend); 
        }
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$begin_time_s = to_timespan($begin_time);
		$end_time_s = to_timespan($end_time);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='yxpnum'){
			$orderby = " order by yxpnum ".$sort;	
			}elseif($order=='tichengmoney'){
			$orderby = " order by tichengmoney ".$sort;  
            }else{	
			$orderby = " order by a.".$order." ".$sort;
			}
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else
		{
			$orderby = "";
			$sortImg=array();
		}
		//var_dump($sortImg);
		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (a.sno='$kw' or a.tel='$kw' or a.realname='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$sql="SELECT a.*,count(b.onum) as yxpnum,sum(b.money_ys) as tichengmoney FROM " . DB_PREFIX . "waiter a left join orders b on a.sno=b.wsno where b.zhifustatus=1 and a.slid=$slid and b.mid=$slid and a.isdisable=$isdd $str and (b.otime between $begin_time_s and $end_time_s)  GROUP BY b.wsno $orderby ";
		
		//echo $sql;
		$list = $GLOBALS['db']->getAll($sql);



		/* 数据 */
		//var_dump($list);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);		
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$sort = $sort == 'asc' ? 1 : 0; //排序方式
		//模板赋值显示
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'kw', $_REQUEST['kw'] );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );
		$GLOBALS['tmpl']->assign ( 'sortType', $sortAlt );
		
		$GLOBALS['tmpl']->assign("page_title", "整单营销员统计");
		$GLOBALS['tmpl']->display("pages/dc/waiter_zdtj.html");
	}
	
	public function dc_waiter_detail(){
		init_app_page();
        $zffsarr=json_decode(ZFFSLIST,true); //解析支付方式	
		
		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
		$startstr=strtotime($start);
        $startend=strtotime($start)+24*3600-1;
        $begin_time=to_date($startstr); 
        $end_time=to_date($startend); 
        }
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$begin_time_s = to_timespan($begin_time);
		$end_time_s = to_timespan($end_time);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		
		
		
		
		
		
		
		
			
		
		
		
		
		
		
		
		
		$sno=$_REQUEST ['sno'];
		$GLOBALS['tmpl']->assign("sno",$sno);
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='money_ys'){
			$orderby = " order by b.money_ys ".$sort;
			}elseif ($order=='name'){
			$orderby = " order by c.name ".$sort;
            }else{	
			$orderby = " order by a.".$order." ".$sort;
			}
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else
		{
			$orderby = "";
			$sortImg=array();
		}
		//var_dump($sortImg);
		$slid = intval($_REQUEST['slid']);		
		
		!isset($isdd) && $isdd = 1;
		
		$page_size = 30;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;

		$sql="SELECT a.*,b.money_ys,c.name FROM orders_tj a left join orders b on a.onum=b.onum left join fanwe_dc_menu c on a.pid=c.id where a.ticheng_status=1 and a.wsno=$sno and a.slid=$slid and a.zhifustatus=1 and (a.otime between $begin_time_s and $end_time_s) $orderby limit ".$limit;
		//echo $sql;
		$sql_count="SELECT count(*) FROM orders_tj a left join orders b on a.onum=b.onum where a.ticheng_status=1 and a.wsno=$sno and a.slid=$slid and a.zhifustatus=1 and (a.otime between $begin_time_s and $end_time_s) $orderby ";
		$list = $GLOBALS['db']->getAll($sql);
        foreach($list as $k=>$v){
		$zffs=$v['zffs'];
	    if($this->check_zffs($zffs,$zffsarr)){
		$list[$k]['zffs']=$zffsarr[$zffs];
		}
	   	$list[$k]['tjid']=$i;
		//小计开始
		$total['money_ys']=$total['money_ys']+$v['money_ys'];
		$total['pprice']=$total['pprice']+$v['pprice'];
		$total['pmoney']=$total['pmoney']+$v['pmoney'];
		$total['pnum']=$total['pnum']+$v['pnum'];
		$total['tichengmoney']=$total['tichengmoney']+$v['tichengmoney'];		
		}


		/* 数据 */
		$totalcount = $GLOBALS['db']->getOne($sql_count);		
	    $page = new Page($totalcount,$page_size);   //初始化分页对象
	    $p  =  $page->show();
				
	    $GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("total", $total);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
				
				
				
		//
		if(!empty($_POST['Excel']))
		{
									include'././Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		for ($column = 'A'; $column <= 'I'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '序号')
					->setCellValue('B1', '订单号')
					->setCellValue('C1', '订单总价')
					->setCellValue('D1', '菜品名称')
					->setCellValue('E1', '当前销售价格')
					->setCellValue('F1', '实收金额')
					->setCellValue('G1', '数量')
					->setCellValue('H1', '营销佣金')
					->setCellValue('I1', '支付方式');


		foreach($list as $k=>$v){
			$v['uregdate'] = date("Y-m-d h:i:s",$v['uregdate']);
			$v['edndate'] = date("Y-m-d h:i:s",$v['edndate']);
			$v['yxsz'] = $v['yxsz'] ? '否' : '是';
			$v['isdisable'] = $v['isdisable'] ? '启用' : '禁用';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), $v['id'])
				->setCellValue('B'.intval($k+2), $v['onum'])
				->setCellValue('C'.intval($k+2), $v['money_ys'])
				->setCellValue('D'.intval($k+2), $v['name'])
				->setCellValue('E'.intval($k+2), $v['pprice'])
				->setCellValue('F'.intval($k+2), $v['pmoney'])
				->setCellValue('G'.intval($k+2), $v['pnum'])
				->setCellValue('H'.intval($k+2), $v['tichengmoney'])
				->setCellValue('I'.intval($k+2), $v['zffs']);
		}
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), "合计")
				->setCellValue('B'.intval($k+2), "")
				->setCellValue('C'.intval($k+2), $total['money_ys'])
				->setCellValue('D'.intval($k+2), "")
				->setCellValue('E'.intval($k+2), $total['pprice'])
				->setCellValue('F'.intval($k+2), $total['pmoney'])
				->setCellValue('G'.intval($k+2), $total['pmoney'])
				->setCellValue('H'.intval($k+2), $total['tichengmoney'])
				->setCellValue('I'.intval($k+2), "");
                 
		$objPHPExcel->getActiveSheet()->setTitle('营销人员详单');
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'营销人员详单.xls"');
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
				
				
				
				
				
				
				
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$sort = $sort == 'asc' ? 1 : 0; //排序方式
		//模板赋值显示
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'kw', $_REQUEST['kw'] );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );
		$GLOBALS['tmpl']->assign ( 'sortType', $sortAlt );
		
		$GLOBALS['tmpl']->assign("page_title", "营销员销售列表");
		$GLOBALS['tmpl']->display("pages/dc/detail.html");
	}
	
	public function dc_waiter_zddetail(){
		init_app_page();
        $zffsarr=json_decode(ZFFSLIST,true); //解析支付方式	
		
		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
		$startstr=strtotime($start);
        $startend=strtotime($start)+24*3600-1;
        $begin_time=to_date($startstr); 
        $end_time=to_date($startend); 
        }
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$begin_time_s = to_timespan($begin_time);
		$end_time_s = to_timespan($end_time);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$sno=$_REQUEST ['sno'];
		$GLOBALS['tmpl']->assign("sno",$sno);
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{ 
	        
		$orderby = " order by a.".$order." ".$sort;
		$sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else
		{
			$orderby = "";
			$sortImg=array();
		}
		//var_dump($sortImg);
		$slid = intval($_REQUEST['slid']);		
		
		!isset($isdd) && $isdd = 1;
		
		$page_size = 30;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;

		$sql="SELECT id,onum,price,money_ys,zffs FROM orders where wsno=$sno and mid=$slid and zhifustatus=1 and (otime between $begin_time_s and $end_time_s) $orderby limit ".$limit;
	 //   echo $sql;
	    $sql_count="SELECT count(*) FROM orders where wsno=$sno and mid=$slid and zhifustatus=1 and (otime between $begin_time_s and $end_time_s) $orderby ";
		//$sql_count="SELECT count(*) FROM orders_tj a left join orders b on a.onum=b.onum where a.ticheng_status=1 and a.wsno=$sno and a.slid=$slid and a.zhifustatus=1 and (a.otime between $begin_time_s and $end_time_s) $orderby ";
		$list = $GLOBALS['db']->getAll($sql);
        foreach($list as $k=>$v){
		$zffs=$v['zffs'];
	    if($this->check_zffs($zffs,$zffsarr)){
		$list[$k]['zffs']=$zffsarr[$zffs];
		}
	   	$list[$k]['tjid']=$i;
		//小计开始
		$total['money_ys']=$total['money_ys']+$v['money_ys'];
		$total['price']=$total['price']+$v['price'];	
		}


		/* 数据 */
		$totalcount = $GLOBALS['db']->getOne($sql_count);		
	    $page = new Page($totalcount,$page_size);   //初始化分页对象
	    $p  =  $page->show();
				
	    $GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("total", $total);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		
		
			//
		if(!empty($_POST['Excel']))
		{
		include'././Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		for ($column = 'A'; $column <= 'I'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '序号')
					->setCellValue('B1', '订单号')
					->setCellValue('C1', '订单总价')
					->setCellValue('D1', '实收金额')
					->setCellValue('E1', '支付方式');


		foreach($list as $k=>$v){
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), $v['id'])
				->setCellValue('B'.intval($k+2), $v['onum'])
				->setCellValue('C'.intval($k+2), $v['price'])
				->setCellValue('D'.intval($k+2), $v['money_ys'])
				->setCellValue('E'.intval($k+2), $v['zffs']);
		}
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), "合计")
				->setCellValue('B'.intval($k+2), "")
				->setCellValue('C'.intval($k+2), $total['price'])
				->setCellValue('D'.intval($k+2), $total['money_ys'])
				->setCellValue('E'.intval($k+2), "");
                 
		$objPHPExcel->getActiveSheet()->setTitle('营销人员详单');
		$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'营销人员详单.xls"');
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
		
		
		
		
		//var_dump($list);	
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$sort = $sort == 'asc' ? 1 : 0; //排序方式
		//模板赋值显示
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'kw', $_REQUEST['kw'] );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );
		$GLOBALS['tmpl']->assign ( 'sortType', $sortAlt );
		
		$GLOBALS['tmpl']->assign("page_title", "整单营销员销售列表");
		$GLOBALS['tmpl']->display("pages/dc/zddetail.html");
	}
	
	
	
	
	
	public function printer(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (printer_name='$kw' or printer_id='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "printer where slid=$slid and isdisable=$isdd $str order by sid desc ");



		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "打印机管理");
		$GLOBALS['tmpl']->display("pages/dc/printer.html");
	}
	
	public function printer_mb(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (printer_name='$kw' or printer_id='$kw')";
		}

		!isset($isdd) && $isdd = 1;

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "printer_mb where slid=$slid and isdisable=$isdd $str order by sid desc ");



		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "打印模板管理");
		$GLOBALS['tmpl']->display("pages/dc/printer_mb.html");
	}
	
	public function dc_pay(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		$list = $GLOBALS['db']->getAll("SELECT * FROM fanwe_cashier_config where storeid=$slid order by sid desc ");

		/* 数据 */
		$GLOBALS['tmpl']->assign("list", $list);
	

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "支付管理");
		$GLOBALS['tmpl']->display("pages/dc/dc_pay.html");
	}

	public function dc_add_pay_wx(){
		init_app_page();
		if($_POST){
			$u=$_POST['user'];
			$x=$_POST['weixin'];
			if ($GLOBALS['db']->getOne("select * from ".DB_PREFIX."cashier_config where storeid=" . intval($_REQUEST['id']) . "")) {
				$data = " storeid='".$u['storeid']."', weixin_appid='".$x['appid']."', weixin_appsecret='".$x['appSecret']."',
		   weixin_mchid='".$x['mchid']."',  weixin_key='".$x['key']."', weixin_apiclient_cert='".$x['apiclient_cert']."',
		   weixin_apiclient_key='".$x['apiclient_key']."', weixin_rootca='".$x['rootca']."'  ";
				$sql="update ".DB_PREFIX."cashier_config set $data  where storeid = '".intval($_REQUEST['id'])."'";
				if($GLOBALS['db']->query($sql)){
					$result['msg']="更新成功";
				}else{
					$result['msg']="更新失败1";
				}
			}else{
				$sql = "INSERT INTO `".DB_PREFIX."cashier_config` (`storeid` ,`weixin_appid` ,`weixin_appsecret` ,
`weixin_mchid` ,`weixin_key` ,`weixin_apiclient_cert` ,`weixin_apiclient_key` ,`weixin_rootca`)
VALUES (
'".$u['storeid']."', '".$x['appid']."', '".$x['appSecret']."', '".$x['mchid']."', '".$x['key']."', '".$x['apiclient_cert']."', '".$x['apiclient_key']."', '".$x['rootca']."');";
				if($GLOBALS['db']->query($sql)){
					$result['msg']="更新成功";
				}else{
					$result['msg']="更新失败0";
				}

			}
			ajax_return($result);
		}else {
			$row=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."cashier_config where storeid=" . intval($_REQUEST['id']) . "");
			$GLOBALS['tmpl']->assign("row", $row[0]);
			$GLOBALS['tmpl']->assign("store_id", intval($_REQUEST['id']));
			/* 系统默认 */
			$GLOBALS['tmpl']->assign("page_title", "微信支付配置");
			$GLOBALS['tmpl']->display("pages/dc/add_pay_wx.html");
		}
	}
	

	
	public function dc_add_pay(){

		init_app_page();
		$storeid=$_REQUEST['storeid'];
		$id=$_REQUEST['id'];
		$sid = intval($_REQUEST['sid']);
		$alipay_appid = $_REQUEST['alipay_appid'];  //开放平台账号AppID
		$alipay_pid=$_REQUEST['alipay_pid'];
		$alipay_key=$_REQUEST['alipay_key'];
		$alipay_name=$_REQUEST['alipay_name'];

			$data=" alipay_appid='".$alipay_appid."', alipay_pid='".$alipay_pid."', alipay_key='".$alipay_key."', alipay_name='".$alipay_name."' ";


		if(!empty($_POST['alipay_appid'])){
		 	$has = $GLOBALS['db']->query(" select * from fanwe_cashier_config where storeid='.$storeid.' limit 1 ");
			if($has){
		 $sql="update ".DB_PREFIX."cashier_config set $data  where storeid = '".intval($_REQUEST['id'])."'";
				if($GLOBALS['db']->query($sql)){
					$result['msg']="更新成功";
				}else{
					$result['msg']="更新失败0";
				}
			}else{
		  $sql = "INSERT INTO `".DB_PREFIX."cashier_config` (`storeid` ,`alipay_appid` ,`alipay_pid` ,`alipay_key` ,`alipay_name` ) VALUES ('".intval($_REQUEST['id'])."', '".$alipay_appid."','".$alipay_pid."','".$alipay_key."','".$alipay_name."');";
				if($GLOBALS['db']->query($sql)){
					$result['msg']="添加成功";
				}else{
					$result['msg']="更新失败1";
				}
			}
			ajax_return($result);
		}else{

			$syy = $GLOBALS['db']->getRow("select * from fanwe_cashier_config where storeid=$id limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
			$GLOBALS['tmpl']->assign("sid",$sid);
			$GLOBALS['tmpl']->assign("storeid",$syy['storeid']);
			$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

			/* 系统默认 */
			$GLOBALS['tmpl']->assign("page_title", "支付宝配置");
			$GLOBALS['tmpl']->display("pages/dc/add_pay.html");
		}

	}
	

	public function dc_del_yg(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."syy where sid='$sid'");
		header("location:/biz.php?ctl=dc&act=dc_yg&id=$slid");
	}
    public function dc_del_waiter(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."waiter where wid='$sid'");
		header("location:/biz.php?ctl=dc&act=dc_waiter&id=$slid");
	}
	public function dc_del_wxduilie(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."quhao where id='$sid'");
		header("location:/biz.php?ctl=dc&act=wx_duilie&id=$slid");
	}
	public function dc_del_wxduilie_log(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."quhao_log where id='$sid'");
		header("location:/biz.php?ctl=dc&act=wx_duilie_log&id=$slid");
	}
	
	public function dc_del_cangku(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."cangku where id='$sid'");
		header("location:/biz.php?ctl=dc&act=dc_cangku&id=$slid");
	}
	public function dc_del_printer_mb(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."printer_mb where wid='$sid'");
		header("location:/biz.php?ctl=dc&act=printer_mb&id=$slid");
	}

	public function dc_add_yg(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$sno = $_REQUEST['sno'];
		$sname = $_REQUEST['sname'];
		
		if (isset($_REQUEST['passwd']) && (strlen($_REQUEST['passwd']))<6){
			showBizErr("密码长度最少是6位！",0,url("biz","dc#dc_add_yg&id=$slid"));
		}
        $passwd = $_REQUEST['passwd'];		
		$tel = $_REQUEST['tel'];
		$isdisable = $_REQUEST['isdisable'];
		$realname = $_REQUEST['realname'];


		if($sno){
			$data['slid'] = $slid;
			$data['sno'] = $sno;
			$data['sname'] = $sname;
			$data['passwd'] = $passwd;
			$data['tel'] = $tel;
			$data['isdisable'] = $isdisable;
			$data['realname'] = $realname;
		}

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."syy",$data,"UPDATE","sid='$sid'");
			
			header("location:/biz.php?ctl=dc&act=dc_yg&id=$slid");
			
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "syy where slid='$slid' and sname='$sname' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."syy",$data);
				header("location:/biz.php?ctl=dc&act=dc_yg&id=$slid");
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);
				$GLOBALS['tmpl']->assign("has", '1');
				$GLOBALS['tmpl']->assign("tishi", '<font color="red">用户名重复</font>');
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "syy where sid=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加收银员");
		$GLOBALS['tmpl']->display("pages/dc/add_yg.html");

	} 
      public function dc_add_printer(){
		  
		 

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		//$data=array();
		//$data['printer_name']=$_REQUEST['printer_name'];


		if(isset($_REQUEST['printer_name'])){
			
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "printer where slid='$slid' and printer_name='".$_REQUEST['printer_name']."' limit 1 ");
			if($has==false){
				$GLOBALS['db']->autoExecute(DB_PREFIX."printer",$_REQUEST);
				header("location:/biz.php?ctl=dc&act=printer&id=$slid");
			}else{
				/* 数据 */
				
				if ($_REQUEST['sid']>0){	
				
				 if(isset($_REQUEST['sm'])==false){$_REQUEST['sm']=0;}
				 if(isset($_REQUEST['sy'])==false){$_REQUEST['sy']=0;}
				 if(isset($_REQUEST['yd'])==false){$_REQUEST['yd']=0;}
				 if(isset($_REQUEST['wm'])==false){$_REQUEST['wm']=0;}
				 if(isset($_REQUEST['b1'])==false){$_REQUEST['b1']=0;}
				 if(isset($_REQUEST['b2'])==false){$_REQUEST['b2']=0;}
				// var_dump($_REQUEST);
				$GLOBALS['db']->autoExecute(DB_PREFIX."printer",$_REQUEST,"UPDATE","sid='$sid'");			
			    header("location:/biz.php?ctl=dc&act=printer&id=$slid");	
				}else{
				$GLOBALS['tmpl']->assign("syy", $_REQUEST);
				$GLOBALS['tmpl']->assign("tishi", '<font color="red">打印机名称重复</font>');
				}
				
				
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "printer where sid=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加打印机");
		$GLOBALS['tmpl']->display("pages/dc/add_printer.html");
   
	}

	 public function dc_add_printer_mb(){
		  
		 

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		//$data=array();
		//$data['printer_name']=$_REQUEST['printer_name'];


		if(isset($_REQUEST['mobanstr'])){
			
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "printer_mb where slid='$slid' and type='".$_REQUEST['type']."' limit 1 ");
			if($has==false){
				$GLOBALS['db']->autoExecute(DB_PREFIX."printer_mb",$_REQUEST);
				header("location:/biz.php?ctl=dc&act=printer_mb&id=$slid");
			}else{
				/* 数据 */
				
				if ($_REQUEST['sid']>0){	
							
				$GLOBALS['db']->autoExecute(DB_PREFIX."printer_mb",$_REQUEST,"UPDATE","sid='$sid'");			
			    header("location:/biz.php?ctl=dc&act=printer_mb&id=$slid");	
				}else{
				$GLOBALS['tmpl']->assign("syy", $_REQUEST);
				$GLOBALS['tmpl']->assign("tishi", '<font color="red">类型已有重复的方案</font>');
				}
				
				
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "printer_mb where sid=$sid limit 1");
            //var_dump($syy);
			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加打印模板");
		$GLOBALS['tmpl']->display("pages/dc/add_printer_mb.html");
   
	}
	
	 public function dc_add_waiter(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$sno = $_REQUEST['sno'];		
		$tel = $_REQUEST['tel'];
		$isdisable = $_REQUEST['isdisable'];
		$realname = $_REQUEST['realname'];









		if($sno){
			$data['slid'] = $slid;
			$data['sno'] = $sno;			
			$data['tel'] = $tel;
			$data['isdisable'] = $isdisable;
			$data['realname'] = $realname;
			$data['img']=$file_name;
		}

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."waiter",$data,"UPDATE","wid='$sid'");
			header("location:/biz.php?ctl=dc&act=dc_waiter&id=$slid");
			
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "waiter where slid='$slid' and sno='$sno' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."waiter",$data);
				header("location:/biz.php?ctl=dc&act=dc_waiter&id=$slid");
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);
				$GLOBALS['tmpl']->assign("has", '1');
				$GLOBALS['tmpl']->assign("tishi", '<font color="red">编号重复</font>');
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "waiter where wid=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加营销员");
		$GLOBALS['tmpl']->display("pages/dc/add_waiter.html");

	}	
	
	
	
	/**
	 * 根据需要的格式将时间戳格式化成日期字符串
	 * @param string $format def 'Y-m-d H:i:s'
	 * @param int $timest
	 * @return string
	 */
	function myDate($format = 'Y-m-d H:i:s',$timest = 0){
		global $sysCliTime;
		if(!$timest){
			$timest = time();
		}
		$addtime = $sysCliTime*3600;
		if(empty($format)){
			$format = 'Y-m-d H:i:s';
		}
		return gmdate($format,$timest+$addtime);
	}

	function getMillSec($tag = 'dateTime',$format = ''){
	$t_array = explode(' ',microtime());
	if($tag=='ms'){
		return $P_S_T = $t_array[0]+$t_array[1];
	}
	if($tag=='msstr'){
		return str_replace('.','',$t_array[0]+$t_array[1]);
	}
	if($tag=='dateTime'){
		return myDate($format,$t_array[1]);
	}else{
		return $t_array[1];
	}
}

	//==================员工模块开始===========================//

	public function dc_addusers(){
		init_app_page();
		$slid = intval($_REQUEST['id']);
		$uid = intval($_REQUEST['uid']);
		$isdisable = intval($_REQUEST['isdisable']);
		$uno = intval($_REQUEST['uno']);
		$uname = $_REQUEST['uname'];
		$ulevel = intval($_REQUEST['ulevel']);
		$uzk = intval($_REQUEST['uzk']);
		$uye = floatval($_REQUEST['uye']);
		$ujf = intval($_REQUEST['ujf']);
		$tel = $_REQUEST['tel'];
		$upasswd = $_REQUEST['upasswd'];
		$ubirthday = $_REQUEST['ubirthday'];
		$edndate = $_REQUEST['edndate'];
		$edndate = strtotime($edndate.' 00:00:00');
		$yxsz = intval($_REQUEST['yxsz']);
		$qq = $_REQUEST['qq'];
		$mail = $_REQUEST['mail'];
		$address = $_REQUEST['address'];
		$biz = $_REQUEST['biz'];


		$data = array(
			'slid'=>$slid,
			'isdisable'=>$isdisable,
			'uname'=>$uname,
			'ulevel'=>$ulevel,
			'uzk'=>$uzk,
			'uye'=>$uye,
			'ujf'=>$ujf,
			'tel'=>$tel,
			//'upasswd'=>$upasswd,
			'ubirthday'=>$ubirthday,
			'edndate'=>$edndate,
			'yxsz'=>$yxsz,
			'qq'=>$qq,
			'mail'=>$mail,
			'address'=>$address,
			'biz'=>$biz
		);
		
		if(!empty($upasswd)) $data['upasswd'] = strtoupper(md5($upasswd));

		if(!empty($uname)){//有数据
			 if(empty($uid)){//新增
				$t_array = explode(' ',microtime());
				$data['uregdate'] = $t_array[1];
				$data['uno'] = $uno;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_users",$data);
				header("location:/biz.php?ctl=dc&act=dc_users&id=$slid");
			 }else{//修改
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_users",$data,"UPDATE","uid=$uid");
				header("location:/biz.php?ctl=dc&act=dc_users&id=$slid");
			 }
		}elseif(!empty($uid)){//修改获取数据
			$user = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "dc_users where uid=$uid limit 1");
			$user['edndate'] = $this->myDate('Y-m-d',$user['edndate']);

			$GLOBALS['tmpl']->assign("user", $user );
		}

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "dc_ulevel where slid=$slid order by dulid ASC");


		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("uid", $uid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加会员");
		$GLOBALS['tmpl']->display("pages/dc/addusers.html");
	}



	public function dc_del_user(){
		$uid = intval($_REQUEST['uid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_users where uid='$uid'");
		header("location:/biz.php?ctl=dc&act=dc_users&id=$slid");
	}
	
	public function dc_del_printer(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."printer where sid='$sid'");
		header("location:/biz.php?ctl=dc&act=printer&id=$slid");
	}

	public function dc_upfile(){
		init_app_page();
		$slid = intval($_REQUEST['id']);
		$GLOBALS['tmpl']->assign("slid", $slid);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "文件上传");
		$GLOBALS['tmpl']->display("pages/dc/upfile.html");
	}



	public function dc_upfileac(){
		$slid = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH.'app/Classes/PHPExcel/IOFactory.php';
		$reader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPExcel = $reader->load($_FILES["ufile"]["tmp_name"]);
		$sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
		$highestRow = $sheet->getHighestRow(); // 取得总行数
		$highestColumm = $sheet->getHighestColumn(); // 取得总列数

		$data = array();
		for ($row = 2; $row <= $highestRow; $row++){//行数是以第1行开始
			$dataset = array();
			for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
				$dataset[] = $sheet->getCell($column.$row)->getValue();
			}
			$data[] = $dataset;
		}

		$coutns = count($data);
		foreach($data as $k=>$v){
			$sql .= '(null';
			$v['15'] = '启用'==$v['15'] ? 1 : 0;
			$v[8] = strtotime($v[8].' 00:00:00');;
			$v[7] = strtotime($v[7].' 00:00:00');
			$v[14] = '是'==$v['14'] ? 1 : 0;
			$sql.=",'$v[15]','$slid','$v[0]','$v[1]','','$v[3]','$v[4]','$v[2]','$v[5]','$v[6]','$v[9]','$v[7]','$v[8]','$v[14]','$v[10]','$v[11]','$v[12]','$v[13]'";
			$sql .= '),';
		}

		$sql = substr($sql,0,strlen($sql)-1);
		$sql = "INSERT INTO `fanwe_dc_users` values $sql;";

		$GLOBALS['db']->query($sql);



		echo "导入成功, 共计:$coutns 人 [ <a href='javascript:history.go(-2)'>返回</a> ]";

	}

	public function dc_userout(){
		$slid = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH.'app/Classes/PHPExcel.php';
		$dateStr = date('Ymdhis');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$list = $GLOBALS['db']->getAll("SELECT * FROM `fanwe_dc_users` where slid=$slid order by uid asc");
		for ($column = 'A'; $column <= 'P'; $column++) {//列数是以A列开始
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
		}

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '会员号')
					->setCellValue('B1', '姓名')
					->setCellValue('C1', '积分')
					->setCellValue('D1', '折扣')
					->setCellValue('E1', '余额')
					->setCellValue('F1', '电话')
					->setCellValue('G1', '密码')
					->setCellValue('H1', '开卡时间')
					->setCellValue('I1', '到期时间')
					->setCellValue('J1', '会员生日')
					->setCellValue('K1', 'QQ')
					->setCellValue('L1', '邮箱')
					->setCellValue('M1', '地址')
					->setCellValue('N1', '备注')
					->setCellValue('O1', '赊账')
					->setCellValue('P1', '是否启用');


		foreach($list as $k=>$v){
			$v['uregdate'] = $this->myDate('Y-m-d',$v['uregdate']);
			$v['edndate'] = $this->myDate('Y-m-d',$v['edndate']);
			$v['yxsz'] = $v['yxsz'] ? '否' : '是';
			$v['isdisable'] = $v['isdisable'] ? '启用' : '禁用';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.intval($k+2), $v['uno'])
				->setCellValue('B'.intval($k+2), $v['uname'])
				->setCellValue('C'.intval($k+2), $v['ujf'])
				->setCellValue('D'.intval($k+2), $v['uzk'])
				->setCellValue('E'.intval($k+2), $v['uye'])
				->setCellValue('F'.intval($k+2), $v['tel'])
				->setCellValue('G'.intval($k+2), $v['upasswd'])
				->setCellValue('H'.intval($k+2), $v['uregdate'])
				->setCellValue('I'.intval($k+2), $v['edndate'])
				->setCellValue('J'.intval($k+2), $v['ubirthday'])
				->setCellValue('K'.intval($k+2), $v['qq'])
				->setCellValue('L'.intval($k+2), $v['mail'])
				->setCellValue('M'.intval($k+2), $v['address'])
				->setCellValue('N'.intval($k+2), $v['biz'])
				->setCellValue('O'.intval($k+2), $v['yxsz'])
				->setCellValue('P'.intval($k+2), $v['isdisable']);
		}
		$objPHPExcel->getActiveSheet()->setTitle('用户数据');
		$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateStr.'用户数据.xls"');
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
	
   //2016-5-3 枫叶增加 红包管理区块
   //红包发放记录
	public function hongbaoguanli(){
		init_app_page();
		$slid = intval($_REQUEST['id']);
		
		$wfdata = '';
		$wfsql = '';
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (h.userid ='$wfdata' or u.name like '%{$wfdata}%')";
		}
		
		
		
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_hongbao_log` where slid=$slid {$wfsql} order by id desc");		
		$list = $GLOBALS['db']->getAll("SELECT h.*,u.user_name FROM `fanwe_hongbao_log` h left join fanwe_user u on h.userid=u.id  where h.slid=$slid {$wfsql} order by h.id desc LIMIT " . $limit);

		foreach($list as $key => $val)
		{
			if ($val['type']=='0'){
			$list[$key]['typ'] = '线上外买';	
			}else{
			$list[$key]['typ'] = '线下门店';	
			}
		
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "红包发放记录");
		$GLOBALS['tmpl']->display("pages/dc/hongbao.html");
	}
	
	//充值
	public function chongbao_autocz(){
		init_app_page();
		$slid = intval($_REQUEST['id']);
		$chongzhi_money=intval($_REQUEST['chongzhi_money']);
		$chongzhi_memo=$_REQUEST['chongzhi_memo'];
		if ($chongzhi_money){
		$data['jine']=$chongzhi_money;
		$data['slid']=$slid;
		$data['cztime']=to_date(NOW_TIME);
		$data['memo']=$chongzhi_memo;
		$data['ordersn']=date("YmdHis");
		//写入数据库
		$GLOBALS['db']->autoExecute(DB_PREFIX."hongbao_chongzhi_log",$data);
		$id = $GLOBALS['db']->insert_id();
        
		header("location:/callback/payment/hongbao_autocz.php?id=$id&slid=$slid&money=$chongzhi_money");	
        exit;		
		}		
		
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "发放红包准备金充值");
		$GLOBALS['tmpl']->display("pages/dc/hongbao_autocz.html");
	}
	
	
	//充值记录
	public function chongbao_autocz_log(){
		init_app_page();
		$slid = intval($_REQUEST['id']);
		
		
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_hongbao_chongzhi_log` where slid=$slid and issucess=1 order by id desc");		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `fanwe_hongbao_chongzhi_log` where slid=$slid and issucess=1 order by id desc LIMIT " . $limit);

				
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "红包充值记录");
		$GLOBALS['tmpl']->display("pages/dc/hongbao_cz_log.html");
	}
	//红包设置
	public function hongbao_cfg(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		//echo $slid;
		$isdisable = $_REQUEST['isdisable'];
		$isdinge = $_REQUEST['isdinge'];
		$min_hb = $_REQUEST['min_hb'];
		$max_hb = $_REQUEST['max_hb'];
		
		if (isset($_REQUEST['min_hb'])){
			if (intval($_REQUEST['min_hb'])<1){
            showBizErr("最小红包金额不能小于1元",0,url("biz","dc#hongbao_cfg&id=$slid"));
			}elseif(intval($_REQUEST['isdinge'])==0 && $max_hb<$min_hb){
			 showBizErr("设置为随机金额的话，最大红包必须大于最小红包金额！",0,url("biz","dc#hongbao_cfg&id=$slid"));			
		    }else{
			$data['slid'] = $slid;
			$data['isdisable'] = $isdisable;
			$data['isdinge'] = $isdinge;
			$data['min_hb'] = round($min_hb*100,0);
			$data['max_hb'] = round($max_hb*100,0);
			}
		}
		if($sid && $data){
			//echo "1";
			$GLOBALS['db']->autoExecute(DB_PREFIX."hongbao_set",$data,"UPDATE","id='$sid'");
			showBizSuccess("修改成功",0,url("biz","dc#hongbao_cfg&id=$slid"));					
		}elseif($data){
			//echo "2";
			
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "hongbao_set where slid='$slid' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."hongbao_set",$data);
				showBizSuccess("设置成功！",0,url("biz","dc#hongbao_cfg&id=$slid"));
						
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);			
			}
		}else{
          
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX ."hongbao_set where slid=$slid limit 1");
			$syy['min_hb']=$syy['min_hb']/100;
			$syy['max_hb']=$syy['max_hb']/100;

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);			
			$sid=$syy['id'];
		}
        
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "红包设置");
		$GLOBALS['tmpl']->display("pages/dc/hongbao_cfg.html");

	} 
	
	//金额结转
	public function hongbao_jiezhuan(){
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$slid=$account_info['location_ids'][0];		
		$account_name=$account_info['account_name'];	
		$supplier_id=$account_info['supplier_id'];	
		
		$money=$GLOBALS['db']->getOne("select money from " . DB_PREFIX . "supplier_location where id='$slid'");
		
		$jiezhuan=floatval($_REQUEST['chongzhi_money']);
		$chongzhi_memo=$_REQUEST['chongzhi_memo'];
		if(isset($_REQUEST['chongzhi_money']) && $jiezhuan==0){
	    showBizErr("结转金额不能为0",0,url("biz","dc#hongbao_jiezhuan"));
	    }
	    if($jiezhuan>$money){
	    showBizErr("结转超限额了",0,url("biz","dc#hongbao_jiezhuan"));
	    }
	 
		if ($jiezhuan){	
		
		    $log_data = array();
			$log_data['log_info']="线上结转红包营销帐户，操作人员：".$account_name."，共计：".format_price($jiezhuan)."元。";
			$log_data['location_id']=$slid;
			$log_data['supplier_id'] = $supplier_id;
			$log_data['create_time'] = NOW_TIME;
			$log_data['money'] = $jiezhuan;
			$log_data['type'] = 7;
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_money_log",$log_data);
		    //减余额
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location set money=money-$jiezhuan where id=".$slid );
			//红包余额增加 
			$GLOBALS['db']->query("update `fanwe_hongbao_set` set `hongbaoyue`=hongbaoyue+$jiezhuan where `slid`='".$slid."'"); //更新Orders状态
			//写记录
        $data['jine']=$jiezhuan;
		$data['slid']=$slid;
		$data['cztime']=to_date(NOW_TIME);
		$data['memo']=$chongzhi_memo."线上余额转红包营销余额";
		$data['ordersn']=date("YmdHis");
		$data['issucess']=1;		
		//写入数据库
		$GLOBALS['db']->autoExecute(DB_PREFIX."hongbao_chongzhi_log",$data);	
		 showBizSuccess("结转成功！",0,url("biz","dc#chongbao_autocz_log&id=$slid"));		
		}		
		
		
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("money", $money);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "线上余额转红包营销余额");
		$GLOBALS['tmpl']->display("pages/dc/hongbao_jiezhuan.html");
	}
	
	
	
	
	public function tuangouseting(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		//echo $slid;
		
		
		if ($_POST['mt_user'] || $_POST['nm_user'] || $_POST['dz_user'] || $_POST['ele_user']){
			$data['slid'] = $slid;
			$data['mt_user'] = $_POST['mt_user'];
			$data['mt_pwd'] = $_POST['mt_pwd'];
			$data['nm_user'] = $_POST['nm_user'];
			$data['nm_pwd'] = $_POST['nm_pwd'];
			$data['dz_user'] = $_POST['dz_user'];
			$data['dz_pwd'] = $_POST['dz_pwd'];
			$data['ele_user'] = $_POST['ele_user'];
			$data['ele_pwd'] = $_POST['ele_pwd'];		
		}
		//var_dump($data);
		//die;
		if($sid && $data){
			//echo "1";
			$GLOBALS['db']->autoExecute(DB_PREFIX."tuangou_cfg",$data,"UPDATE","id='$sid'");
			header("location:/biz.php?ctl=dc&act=tuangouseting&id=$slid&sid=$sid");		
		}elseif($data){
			//echo "2";
			
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "tuangou_cfg where slid='$slid' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."tuangou_cfg",$data);
				header("location:/biz.php?ctl=dc&act=tuangouseting&id=$slid");		
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);			
			}
		}else{
          
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX ."tuangou_cfg where slid=$slid limit 1");
			
			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);			
			$sid=$syy['id'];
		}
        
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "团购验证帐户设置");
		$GLOBALS['tmpl']->display("pages/dc/tuangou_cfg.html");

	} 
	
	
	
	//2016-5-17 枫叶增加 团购验证记录
   //红包发放记录
	public function tuan_detail(){
		init_app_page();
		
		if($_REQUEST['id']){
		$slid = intval($_REQUEST['id']);
		}else{
		$slid=$s_account_info['slid'];	
		}
				
		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		$begin_time=$_REQUEST['begin_time'];
		$end_time=$_REQUEST['end_time'];
		if(!$_REQUEST['begin_time']||!$_REQUEST['end_time'])
		{
		 $begin_time=to_date(NOW_TIME,"Y-m-d")." 00:00:00";
		 $end_time=to_date(NOW_TIME,"Y-m-d")." 23:59:59";
		}
			
		$GLOBALS['tmpl']->assign("begin_time", $begin_time);
		$GLOBALS['tmpl']->assign("end_time", $end_time);
		
		$wfdata = $_REQUEST['wfid'];
		$type = intval($_REQUEST['type']);
		
		$GLOBALS['tmpl']->assign("type", $type);
		
		
		
		$sqlstr="where slid='$slid' and (deal_time between '$begin_time' and '$end_time')";
		
		if ($wfdata){
		$sqlstr .=" and tuan_code='$wfdata'";
		}
		if ($_REQUEST['type']){
		$sqlstr .=" and type='$type'";
		}
		
		//排序开始
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	     $oderstr = " order by ".$order." ".$sort;		  
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else{
			$oderstr = "order by id desc";
			$sortImg=array();
		}
		$sort = $sort == 'asc' ? 1 : 0; //排序方式		
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );	
		
		
		
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_tuangou_detail` ".$sqlstr." order by id desc");		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `fanwe_tuangou_detail` ".$sqlstr." $oderstr  LIMIT " . $limit);

		foreach($list as $key => $val)
		{
			if ($val['type']=='1'){
			$list[$key]['typ'] = '美团';	
			}elseif($val['type']=='2'){
			$list[$key]['typ'] = '大众点评';	
			}elseif($val['type']=='3'){
		    $list[$key]['typ'] = '百度糯米';	
			}else{
			$list[$key]['typ'] = '饿了么';	
			}
			$list[$key]['tuan_detail'] = unserialize($val['tuan_detail']);
		}

		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "团购验证记录");
		$GLOBALS['tmpl']->display("pages/dc/tuanweb.html");
		
	}
	
	
	
	
		
	

	public function dc_users(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		
		$wfdata = '';
		$wfsql = '';
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (uno like '%{$wfdata}%' or uname like '%{$wfdata}%' or `tel` like '%{$wfdata}%' or `keys` like '%{$wfdata}%' or `qq` like '%{$wfdata}%' or `uid` like '%{$wfdata}%')";
		}

		//武林会员整改
		
		//$list = $GLOBALS['db']->getAll("SELECT u.uid, u.slid, u.uno,u.uname, l.duname, u.uye, u.ujf, u.tel, u.upasswd, u.uregdate FROM `fanwe_dc_users` u LEFT JOIN `fanwe_dc_ulevel` l ON u.ulevel = l.dulid where u.slid=$slid order by u.isdisable desc,u.uid desc LIMIT 0 , 1000");
		
		//武林分页==================================
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_dc_users` u where u.slid=$slid{$wfsql} order by u.isdisable desc,u.uid desc LIMIT 0 , 1000");
		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `fanwe_dc_users` where slid=$slid{$wfsql} order by isdisable desc,uid desc LIMIT " . $limit);

		foreach($list as $key => $val)
		{
			$list[$key]['uregdate'] = date('Y-m-d',$val['uregdate']);
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		//武林分页==================================
		
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员管理");
		$GLOBALS['tmpl']->display("pages/dc/users.html");
	}
	
//	public function dc_sql(){
//		
//		$index = intval(empty($_GET['index']) ? 0 : $_GET['index'] * 200);
//	
//		//$WGoods = $GLOBALS['db']->getRow("SELECT * from bn50_Goods LIMIT {$index} , 200;");
//		
//		$list = $GLOBALS['db']->getAll("SELECT * from bn50_citycard");
//	
//		foreach($list as $val)
//		{
//
//			$GLOBALS['db']->query("update fanwe_dc_users set Recordsum = '{$val[Recordsum]}',Ordersum='{$val[Ordersum]}',opencardmoney = '{$val[opencardmoney]}' where uid = '{$val[id]}'");
//		
//		}
//
//	}
	
	//武林扩展消费记录
	public function dc_posorder(){
		init_app_page();

		$tel = $_REQUEST['tel'];
		$slid = $_REQUEST['id'];
		
		//武林分页==================================
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `posordermain` where Businessid='$slid' and Accountid = '$tel'");
		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `posordermain` where  Businessid='$slid' and Accountid = '$tel' order by id desc LIMIT " . $limit);
		

		foreach($list as $key => $val)
		{
			//$val['CreateTime'] = date('Y-m-d',strtotime($val['CreateTime']));
			$list[$key] = $val;
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		//武林分页==================================

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员消费记录");
		$GLOBALS['tmpl']->display("pages/dc/posorder.html");
	}
	
	//武林扩展消费记录明细
	public function dc_posordermain(){
		init_app_page();

		$slid = intval($_REQUEST['uid']);  //会员ID
		$id = intval($_REQUEST['id']);  //门店ID		
		$soid = $_REQUEST['oid']; //单号
		
		//武林分页==================================
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$checkold=substr($soid,0,2); //判断新旧平台订单号 如果前两位是20则是新单号，
		
		if ($checkold=='20'){ //新平台订单库
		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `orders_tj` where slid='{$id}' and onum = '{$soid}'");
		$orderlist = $GLOBALS['db']->getAll("SELECT a.*,a.pnum*a.pmoney as zm,b.name,b.price FROM `orders_tj` a left join `fanwe_dc_menu` b on a.pid=b.id  where slid='{$id}' and onum = '{$soid}' LIMIT " . $limit);
		$i=0;
		foreach($orderlist as $key => $val)
		{
		    if($val['zhifustatus'] == '1') {
			 $orderlist[$i]['zhifu'] = '已支付';
		    }elseif($val['zhifustatus'] == '9'){
			$orderlist[$i]['zhifu'] = '已退款';	
			}
			$i++;
		}
		
		}else{  //旧平台订单库
		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `posorder` where OrderId = '{$soid}'");
		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `posorder` where OrderId = '{$soid}' LIMIT " . $limit);

		foreach($list as $key => $val)
		{
			if($val['status'] == '-1' || $val['status'] == '0') $val['wshowpay'] = '已支付';
			
			if($val['HideWGoodId'] != '')
			{
				//老平台商品信息
				$WGoodsDb = $GLOBALS['db']->getOne("SELECT `Name` FROM `bn50_HideWGoodDb` where Id = '{$val['HideWGoodId']}'");
				$val['WGoodsName'] = $WGoodsDb;
			}
			else
			{
				//新平台商品信息
			}
			
			$list[$key] = $val;
		}
		}
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		$GLOBALS['tmpl']->assign('orderlist', $orderlist);
		$GLOBALS['tmpl']->assign('uuid', $slid);
		//武林分页==================================

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员消费记录明细");
		$GLOBALS['tmpl']->display("pages/dc/posordermain.html");
	}
	
	//武林扩展充值记录
	public function dc_posrecord(){
		$zffsarr=json_decode(ZFFSLIST,true); //解析支付方式
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$tel = $_REQUEST['tel'];
		
		//武林分页==================================
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `posrecord` where `slid` = '$slid' and `account_id`= '$tel'");
		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `posrecord` where  `slid` = '$slid' and `accountid` = '$tel' order by id desc LIMIT " . $limit);
		
      
		foreach($list as $key => $val)
		{
			$val['uregdate'] = date('Y-m-d',$val['uregdate']);
			
			$zffs=$val['zffs'];
			$val['showpaytype']=$zffsarr[$zffs];	   
			$list[$key] = $val;
		}
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		//武林分页==================================

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员充值记录");
		$GLOBALS['tmpl']->display("pages/dc/posrecord.html");
	}



	public function dc_ulevel(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
//		$kw = $_REQUEST['kw'];

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "dc_ulevel where slid=$slid order by dulid desc");


		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员等级管理");
		$GLOBALS['tmpl']->display("pages/dc/ulevel.html");
	}



	/**
	 * 添加等级 dulid slid duname duzk upval duvali
	 */
	public function dc_addulevel(){

		init_app_page();

		$slid = intval($_REQUEST['id']);
		$dulid = intval($_REQUEST['dulid']);
		$duzk = intval($_REQUEST['duzk']);
		$upval = intval($_REQUEST['upval']);
		$duvali = intval($_REQUEST['duvali']);
		$isdisable = intval($_REQUEST['isdisable']);
		$duname = $_REQUEST['duname'];

		if(0==$isdisable){
			$duvali=0;
			$upval = 0;
		}


		if(empty($dulid) && $duname){ //添加
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_ulevel",array(
				"slid"=>$slid,
				"duzk"=>$duzk,
				"upval"=>$upval,
				"duvali"=>$duvali,
				"duname"=>$duname
			));
			header("location:/biz.php?ctl=dc&act=dc_ulevel&id=$slid");
		}elseif($dulid && $duname){ //编辑
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_ulevel",array(
				"slid"=>$slid,
				"duzk"=>$duzk,
				"upval"=>$upval,
				"duvali"=>$duvali,
				"duname"=>$duname
			),"UPDATE","dulid='$dulid'");
			header("location:/biz.php?ctl=dc&act=dc_ulevel&id=$slid");
		}elseif($dulid && empty($duname)){
			$ulevel = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "dc_ulevel where dulid=$dulid limit 1");
			/* 数据 */
			$GLOBALS['tmpl']->assign("ulevel", $ulevel);
		}




		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("dulid", $dulid);
		$GLOBALS['tmpl']->assign("duzk", $duzk);
		$GLOBALS['tmpl']->assign("upval", $upval);
		$GLOBALS['tmpl']->assign("duvali", $duvali);
		$GLOBALS['tmpl']->assign("duname", $duname);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));



		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加等级");
		$GLOBALS['tmpl']->display("pages/dc/addulevel.html");
	}








	/**
	 * 导入菜单
	 */
	public function load_btnImport_weebox(){
		$location_id = intval($_REQUEST['location_id']);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/btnImport_weebox.html");
		ajax_return($data);
	}


	/**
	 * 导出菜单
	 */
	public function load_btnExport_weebox(){
		$location_id = intval($_REQUEST['location_id']);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/btnExport_weebox.html");
		ajax_return($data);
	}
	public function Import_weebox(){


	   /* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];


		$id = intval($_REQUEST['location_id']);


 if (! empty ( $_FILES ['excel'] ['name'] ))
{
	$tmp_file = $_FILES ['excel'] ['tmp_name'];
	$file_types = explode ( ".", $_FILES ['excel'] ['name'] );
	$file_type = $file_types [count ( $file_types ) - 1];


	 if (strtolower ( $file_type ) != "xls")
	{
		 exit( '不是Excel文件，重新上传' );
	 }


	 $savePath = APP_ROOT_PATH."public/excel/";


	 $str = date ( 'Ymdhis' );
	 $file_name = $str . "." . $file_type;


	 if (! copy ( $tmp_file, $savePath . $file_name ))
	  {
		 exit( '上传失败' );
	  }
 }
/** Include PHPExcel */
//require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';




//var_dump($savePath . $file_name);
$inputFileName =$savePath . $file_name;

//$inputFileName ='/www/web/o2o_678sh_com/public_html/public/excel/20160217031739.xls';
//echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);


//echo '<hr />';

$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
foreach($sheetData as $key=>$val)
{
 if($key>1){
   $sql = " select m.*,c.name as catename,com.name as companyname from " . DB_PREFIX . "dc_menu m  left join ".DB_PREFIX."dc_supplier_menu_cate c on c.id=m.cate_id left join ".DB_PREFIX."dc_supplier_companyname com on com.id=m.company";
// var_dump($val)	;
 /*array(19) { ["A"]=> string(7) "萝卜3" ["B"]=> string(9) "牛肉面" ["C"]=> float(1455007007816) ["D"]=> float(1) ["E"]=> float(100) ["F"]=> float(100) ["G"]=> float(100) ["H"]=> float(100) ["I"]=> float(100) ["J"]=> float(1) ["K"]=> float(2002) ["L"]=> float(20033) ["M"]=> float(1) ["N"]=> string(6) "简餐" ["O"]=> string(19) "2016-02-10 19:00:00" ["P"]=> float(200) ["Q"]=> string(5) "luobo" ["R"]=> float(1) ["S"]=> float(202020202) }*/

   //$account_info = $GLOBALS['account_info'];
	  //  $supplier_id = $account_info['supplier_id'];

		/*获取参数*/
	 //   $id = intval($_REQUEST['id']);

		$location_id = intval($_REQUEST['location_id']);
		$data['name'] = strim($val["A"]);
	   // $data['cate_id'] = intval($_REQUEST['cate_id']);
		//$data['image'] =  replace_domain_to_public(strim($_REQUEST['image']));
		$data['price'] = floatval($val["G"]);
	   // $data['tags'] = implode(",", $_REQUEST['tags']);
		$data['is_effect'] = intval($val["R"]);

		  $data['barcode'] = strim($val["C"]);
			$data['buyPrice'] = floatval($val["F"]);
		  $data['stock'] = intval($val["E"]);
			$data['customerPrice'] = floatval($val["I"]);
			  $data['sellPrice2'] = floatval($val["H"]);
				$data['unit'] = strim($val["T"]);
				  $data['pinyin'] = strim($val["Q"]);


					  $data['productionDate'] = strim($val["O"]);
						$data['shelfLife'] = strim($val["P"]);
						  $data['maxStock'] = intval($val["K"]);

							$data['minStock'] = intval($val["L"]);
							  $data['biaoqian'] = strim($val["M"]);
								$data['print'] = strim($val["D"]);
								  $data['info'] = strim($val["S"]);
								  $data['isdazhe'] = strim($val["J"]);
								  $data['funit'] = strim($val["U"]);
								  $data['times'] = floatval($val["V"]);

	   $location_info = $GLOBALS['db']->getRow("select xpoint,ypoint from ".DB_PREFIX."supplier_location where id=".$location_id);
	   $cate_info = $GLOBALS['db']->getRow("select id from  ".DB_PREFIX."dc_supplier_menu_cate where name='".$val["B"]."' and supplier_id=".$supplier_id." and location_id=".$location_id);

		$company_info = $GLOBALS['db']->getRow("select id from  ".DB_PREFIX."dc_supplier_companyname where name='".$val["N"]."' ");

	   $data['location_id'] = $location_id;
	   $data['supplier_id'] = $supplier_id;
	   $data['xpoint'] = $location_info['xpoint'];
	   $data['ypoint'] = $location_info['ypoint'];

	   $data['cate_id'] = intval($cate_info['id']);
		$data['company'] = strim($company_info["id"]);
	   /*获取标签中文,同步函数*/


		 $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data);
		 $id = $GLOBALS['db']->insert_id();
		  // var_dump($cate_info);
		//   syn_supplier_location_menu_match($id);
		   //$root['info'] = "添加成功";


 }
}

showBizSuccess("导入成功",0,url("biz","dc#dc_menu_index&id=$location_id"));

	}

public function Export_weebox(){


	   /* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];


		$id = intval($_REQUEST['location_id']);
				$conditions .= " where m.supplier_id = ".$supplier_id; // 查询条件

		$conditions .= " and m.location_id=".$id." and m.location_id in(" . implode(",", $account_info['location_ids']) . ") ";

		$sql_count = " select count(id) from " . DB_PREFIX . "dc_menu";
	   // $sql = " select id,name,is_effect,cate_id,price,image from " . DB_PREFIX . "dc_menu ";
		$sql = " select m.*,c.name as catename,com.name as companyname from " . DB_PREFIX . "dc_menu m  left join ".DB_PREFIX."dc_supplier_menu_cate c on c.id=m.cate_id left join ".DB_PREFIX."dc_supplier_companyname com on com.id=m.company";

  $list = $GLOBALS['db']->getAll($sql.$conditions);
//var_dump($sql.$conditions);
//var_dump($list); exit();



/** Include PHPExcel */
require_once 'Classes/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// Add some data
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', '名称（必填）')
			->setCellValue('B1', '分类')
			->setCellValue('C1', '条码')
			->setCellValue('D1', '厨房票打')
			->setCellValue('E1', '库存量（必填）')
			->setCellValue('F1', '进货价（必填）')
			->setCellValue('G1', '销售价（必填）')
			->setCellValue('H1', '批发价')
			->setCellValue('I1', '会员价')
			->setCellValue('J1', '会员折扣')
			->setCellValue('K1', '库存上限')
			->setCellValue('L1', '库存下限')
			->setCellValue('M1', '标签打印')
			->setCellValue('N1', '供货商')
			->setCellValue('O1', '生产日期')
			->setCellValue('P1', '保质期')
			->setCellValue('Q1', '拼音码')
			->setCellValue('R1', '商品状态')
			->setCellValue('S1', '商品备注')
			->setCellValue('T1', '单位')
			->setCellValue('U1', '副单位')
			->setCellValue('V1', '兑换倍数');
/*
 ["id"]=> string(1) "2" ["name"]=> string(9) "牛肉面" ["cate_id"]=> string(1) "5" ["price"]=> string(6) "7.0000" ["image"]=> string(71) "./public/attachment/201602/03/10/fa30aaf9690fbe668f0816c177bda09875.jpg" ["tags"]=> string(0) "" ["tags_match"]=> string(0) "" ["tags_match_row"]=> string(0) "" ["is_effect"]=> string(1) "1" ["location_id"]=> string(3) "837" ["supplier_id"]=> string(3) "839" ["buy_count"]=> string(1) "0" ["xpoint"]=> string(18) "103.81730638713016" ["ypoint"]=> string(17) "36.05841065457739" ["menu_cate_type"]=> string(1) "0" ["open_time_cfg_str"]=> string(11) "08:00-23:59" ["barcode"]=> string(1) "0" ["buyPrice"]=> string(4) "0.00" ["stock"]=> string(1) "0" ["customerPrice"]=> string(4) "0.00" ["sellPrice2"]=> string(4) "0.00" ["unit"]=> string(1) "0" ["pinyin"]=> string(1) "0" ["company"]=> string(1) "0" ["productionDate"]=> string(19) "0000-00-00 00:00:00" ["shelfLife"]=> string(1) "0" ["maxStock"]=> string(1) "0" ["minStock"]=> string(1) "0" ["biaoqian"]=> string(1) "0" ["print"]=> string(1) "0" ["info"]=> string(0) "" } [1]=>



 array(31) { ["id"]=> string(1) "3" ["name"]=> string(15) "酸菜牛肉面" ["cate_id"]=> string(1) "5" ["price"]=> string(6) "8.0000" ["image"]=> string(71) "./public/attachment/201602/10/11/5582206fce4475097ac0c06ce2b5e33641.jpg" ["tags"]=> string(0) "" ["tags_match"]=> string(0) "" ["tags_match_row"]=> string(0) "" ["is_effect"]=> string(1) "1" ["location_id"]=> string(3) "837" ["supplier_id"]=> string(3) "839" ["buy_count"]=> string(1) "0" ["xpoint"]=> string(18) "103.81730638713016" ["ypoint"]=> string(17) "36.05841065457739" ["menu_cate_type"]=> string(1) "0" ["open_time_cfg_str"]=> string(11) "08:00-23:59" ["barcode"]=> string(1) "0" ["buyPrice"]=> string(4) "0.00" ["stock"]=> string(1) "0" ["customerPrice"]=> string(4) "0.00" ["sellPrice2"]=> string(4) "0.00" ["unit"]=> string(3) "份" ["pinyin"]=> string(1) "0" ["company"]=> string(1) "0" ["productionDate"]=> string(19) "0000-00-00 00:00:00" ["shelfLife"]=> string(1) "0" ["maxStock"]=> string(1) "0" ["minStock"]=> string(1) "0" ["biaoqian"]=> string(1) "0" ["print"]=> string(1) "0" ["info"]=> string(0) ""
*/

foreach($list as $key=> $val)
{
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.($key+2), $val["name"])
			->setCellValue('B'.($key+2), $val["catename"])
			->setCellValue('C'.($key+2),  $val["barcode"])
			->setCellValue('D'.($key+2),  $val["print"])
			->setCellValue('E'.($key+2), $val["stock"])
			->setCellValue('F'.($key+2), $val["buyPrice"])
			->setCellValue('G'.($key+2), $val["price"])
			->setCellValue('H'.($key+2), $val["sellPrice2"])
			->setCellValue('I'.($key+2),  $val["customerPrice"])
			->setCellValue('J'.($key+2), ($val["customerPrice"]/$val["price"]))
			->setCellValue('K'.($key+2),  $val["maxStock"])
			->setCellValue('L'.($key+2),  $val["minStock"])
			->setCellValue('M'.($key+2), $val["biaoqian"])
			->setCellValue('N'.($key+2), $val["companyname"])
			->setCellValue('O'.($key+2), $val["productionDate"])
			->setCellValue('P'.($key+2),  $val["shelfLife"])
			->setCellValue('Q'.($key+2), $val["pinyin"])
			->setCellValue('R'.($key+2), $val["is_effect"])
			->setCellValue('S'.($key+2), $val["info"])
			->setCellValue('T'.($key+2), $val["unit"])
			->setCellValue('U'.($key+2), $val["funit"])
			->setCellValue('V'.($key+2), $val["times"]);
}
// Miscellaneous glyphs, UTF-8
/*$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A4', 'Miscellaneous glyphs')
			->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');*/

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('导出数据');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

ob_end_clean();//清除缓冲区,避免乱码
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="01simple.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

	}


	/**
	 * 菜单分类添加
	 */
	public function load_add_menu_cate_weebox(){
		//=====================================武林二次开发
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		
		/*获取参数*/
		$location_id = intval($_REQUEST['location_id']);
		$menu_cate_list = array();
		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll("select id,name,wcategory,wlevel from ".DB_PREFIX."dc_supplier_menu_cate where is_effect=1 and wlevel<3 and location_id=".$location_id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		$menu_cate_list[] = array('id'=>'0','name'=>'顶级分类');
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$menu_cate_list[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$wmenu1['name'] = '| - ' . $wmenu1['name'];
					$menu_cate_list[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$wmenu2['name'] = '| - - ' . $wmenu2['name'];
						$menu_cate_list[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$wmenu3['name'] = '| - - - ' . $wmenu3['name'];
							$menu_cate_list[] = $wmenu3;
						}
					}
				}
			}
		}
		//=====================================武林二次开发
		$location_id = intval($_REQUEST['location_id']);
		$GLOBALS['tmpl']->assign("menu_cate",$menu_cate_list);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_menu_cate_weebox.html");
		ajax_return($data);
	}

	/*添加口味组 do_save_menu_taste*/

	public function do_save_menu_taste(){
		 /*初始化*/
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		  $id = intval($_REQUEST['id']);
		/*活出参数*/
		$location_id = $_REQUEST['location_id'];
		$name = strim($_REQUEST['name']);

		$flavorName = ($_REQUEST['flavorName']);
		$flavorPrice = ($_REQUEST['flavorPrice']);

		foreach($flavorName as $key=>$val)
		{
			 $flavor[]=array("name"=>urlencode($val),"price"=>$flavorPrice[$key]);
		}

		//$flavor = strim($_REQUEST['flavor']);
		$switchbox = strim($_REQUEST['switchbox']);
		$shops = ($_REQUEST['shops']);
		$flavor=json_encode($flavor);
		$shops=json_encode($shops);
		/*业务逻辑部分*/
		$root['status'] = 0;
		$root['info'] = "";





		if(empty($id)&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_taste where name='".$name."' and location_id = ".$location_id)){
			$root['status'] = 0;
			$root['info'] = "口味名称重复";
			ajax_return($root);
		}


		$data = array();
		$data['name'] = $name;

		 $data['flavor'] = $flavor;
		  $data['switchbox'] = $switchbox;
		   $data['shops'] = $shops;

		$data['sort'] = 100;
		$data['is_effect'] = 1;
	 //    $data['supplier_id'] = $supplier_id;
		$data['location_id'] = $location_id;

		  if($id>0){
		   $GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$data,"UPDATE","id=".$id);
		   syn_supplier_location_menu_match($id);
		   $root['info'] = "修改成功";
			$root['status']=1;
			$root['jump']= url("biz","dc#dc_menu_taste",array('id'=>$location_id));
	   }else{
		if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$data)){
			  $root['info'] = "添加成功";
			$root['status']=1;
			$root['jump']= url("biz","dc#dc_menu_taste",array('id'=>$location_id));
		}
	   }
		ajax_return($root);

		}

	public function do_save_menu_cate(){
		/*初始化*/
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*活出参数*/
		$location_id = $_REQUEST['location_id'];
		$name = strim($_REQUEST['cate_name']);

		/*业务逻辑部分*/
		$root['status'] = 0;
		$root['info'] = "";
		if(!in_array($location_id, $account_info['location_ids'])){
			$root['status'] = 0;
			$root['info'] = "您没有添加权限";
			ajax_return($root);
		}
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_menu_cate where name='".$name."' and location_id = ".$location_id)){
			$root['status'] = 0;
			$root['info'] = "分类名称重复";
			ajax_return($root);
		}
		//武林二次开发=================================================
		$cate_id = @intval($_REQUEST['cate_id']);
		if(!empty($cate_id))
		{
			$wlevel = $GLOBALS['db']->getRow("select id,wlevel from ".DB_PREFIX."dc_supplier_menu_cate where id = ".$cate_id);
			if(empty($wlevel['id']))
			{
				$root['status'] = 0;
				$root['info'] = "您选择的分类不存在";
				ajax_return($root);
			}
			if($wlevel['wlevel']>2)
			{
				$root['status'] = 0;
				$root['info'] = "非常抱歉仅允许创建4级分类";
				ajax_return($root);
			}
		}
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_menu_cate where location_id = ".$location_id)>200){			$root['status'] = 0;
			$root['status'] = 0;
			$root['info'] = "最多只允许创建200个分类";
			ajax_return($root);
		}
		//武林二次开发=================================================

		$data = array();
		$data['wcategory'] = $cate_id;
		$data['wlevel'] = $cate_id > 0 ? $wlevel['wlevel'] + 1 : 0;
		$data['name'] = $name;
		$data['sort'] = 100;
		$data['is_effect'] = 1;
		$data['supplier_id'] = $supplier_id;
		$data['location_id'] = $location_id;

		if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",$data)){
			$root['status']=1;
			$root['jump']= url("biz","dc#dc_menu_cate_index",array('id'=>$location_id));
		}
		ajax_return($root);

	}



	/**
	 * 菜单分类删除
	 */
	public function dc_menu_cate_del(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		//判断存在关联菜单
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_menu where cate_id=".$id)){
			$root['status'] =0;
			$root['info'] = "有关联菜单存在无法删除";
			ajax_return($root);
		}
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id);
		/* 数据 */
		$root['status'] =1;
		$root['jump'] = url("biz","dc#dc_menu_cate_index",array('id'=>$data['location_id']));


		/* ajax返回数据 */
		ajax_return($root);

	}

	/**
	 * 菜单状态修改
	 */
	public function dc_menu_cate_status(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		$is_effect = $data['is_effect']>0?0:1;
		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",array("is_effect"=>$is_effect),"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['is_effect'] = $is_effect;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}

	/*
	 * 修改排序
	 */
	public function do_menu_cate_sort(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		$is_effect = $data['is_effect']>0?0:1;
		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",array("sort"=>$sort),"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}

	public function do_edit_menu_cate_name(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$name = strim($_REQUEST['name']);

		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." ");
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",array("name"=>$name),"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}
	
	public function do_edit_menu_stock(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$stock = strim($_REQUEST['stock']);

		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}
		$updata=array();
		$updata['stock']=$stock;
		
		if($stock>$data['minStock']&&$stock<$data['maxStock']){
		$updata['yujingtishi']=0;	
		}else{
		$updata['yujingtishi']=1;	
		}

		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$updata,"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}
	

	/*=========================会员卡充值优惠方案==================================*/
	public function chongzhi_setting(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$slid = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$conditions .= " where slid = ".$slid; // 查询条件
	
		
		$sql_count = " select count(id) from " . DB_PREFIX . "chongzhi_setting";
		$sql = " select * from " . DB_PREFIX . "chongzhi_setting ";

		/* 分页 */
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);

        //echo $sql.$conditions . " limit " . $limit;
		$list = $GLOBALS['db']->getAll($sql.$conditions . " limit " . $limit);
	

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $slid);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "充值赠送方案设置");
		$GLOBALS['tmpl']->display("pages/dc/chongzhi_setting.html");
	}
	

	/*=========================会员卡充值优惠方案==================================*/
	public function dc_chongzhi_add(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$slid = intval($_REQUEST['id']);

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $slid);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$i=0;
		$GLOBALS['tmpl']->assign("i", $i);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加充值赠送");
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->display("pages/dc/add_chongzhi.html");
	}
		/*=========================会员卡充值优惠方案==================================*/
	public function dc_chongzhi_edit(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$slid = intval($_REQUEST['id']);
		//$c_id = intval($_REQUEST['cid']);
/* 业务逻辑部分 */
		$conditions .= " where slid = ".$slid; // 查询条件
	
		$sql = " select * from " . DB_PREFIX . "chongzhi_setting ";
        //echo $sql.$conditions . " limit " . $limit;
		$list = $GLOBALS['db']->getAll($sql.$conditions);
		$i=0;
		foreach($list as $k=>$v){
			$list[$k]['i']=$i;
			$i++;
		}
		//$countlist=count($list); //计算当前有几行
		
		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $slid);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("i", $i);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "修改充值赠送方案");
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->display("pages/dc/add_chongzhi.html");
	}
		/*=========================会员卡充值优惠方案==================================*/
	public function save_chongzhi_add(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$slid = intval($_REQUEST['id']);
		$location_info=$GLOBALS['db']->getRow("select isZhiying,is_main from fanwe_supplier_location where id=".$slid);
		$isZhiying=$location_info["isZhiying"];
		$is_main=$location_info["is_main"];
		if ($isZhiying==0 && $is_main==0){
          showBizErr("直营店只有主店才有权利进行此项修改！",0,url("biz","dc#chongzhi_setting&id=$slid"));			
		}
		$c_id=$_POST['c_id'];
        $money=$_POST['money'];
		//var_dump($money);
	    $alipay=$_POST['alipay'];
	    $bestpay=$_POST['bestpay'];
	    $weixipay=$_POST['weixipay'];
	    $baidupay=$_POST['baidupay'];
	    $qqpay=$_POST['qqpay'];
	    $jdpay=$_POST['jdpay'];
	    $unipay=$_POST['unipay'];
	    $cash=$_POST['cash'];
		foreach($money as $k=>$v){
		   //循环入库		   
		   if ($v != ""){
		   $data['slid']=$slid;
		   $data['supplier_id']=$supplier_id;
		   $data['isZhiying']=$isZhiying;
		   $data['money']=$v;
		   $data['alipay']=$alipay[$k];
		   $data['weixipay']=$weixipay[$k];
		   $data['bestpay']=$bestpay[$k];
		   $data['baidupay']=$baidupay[$k];
		   $data['qqpay']=$qqpay[$k];
		   $data['jdpay']=$jdpay[$k];
		   $data['unipay']=$unipay[$k];
		   $data['cash']=$cash[$k];		
		   if ($c_id[$k]>0){ //修改
           $GLOBALS['db']->autoExecute(DB_PREFIX."chongzhi_setting",$data,"update","id=".$c_id[$k]);		   
		   }else{ //添加
		   $GLOBALS['db']->autoExecute(DB_PREFIX."chongzhi_setting",$data);
		   }
		   
           // echo $id;
		   }
		}
		$url="/biz.php?ctl=dc&act=chongzhi_setting&id=".$slid;
		Header("Location:$url"); 
	}

	public function dc_chongzhi_del(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
        //  var_dump($account_info['location_ids']);
		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$slid = intval($_REQUEST['slid']);
		$slids = implode(",",$account_info['location_ids']);
		$sql="delete from ".DB_PREFIX."chongzhi_setting where slid in('".$slids."') and id=".$id;
		//echo $sql;
		$GLOBALS['db']->query($sql);
		$url="/biz.php?ctl=dc&act=chongzhi_setting&id=".$slid;
		Header("Location:$url"); 
	}
	
	/*=========================菜单部分==================================*/
	public function dc_menu_index(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
        $page = intval($_REQUEST['p']);
		/*获取参数*/
		$id = intval($_REQUEST['id']);
		if ($id==0){
		$id = $account_info['slid'];	
		}
        $cate_id = intval($_REQUEST['cate_id'])?intval($_REQUEST['cate_id']):0;
        
		/* 业务逻辑部分 */
		$conditions .= " where 1=1 and is_delete=1"; // 查询条件
		// 只查询支持门店的
		$conditions .= " and location_id=".$id." ";

		//只显示前台商品
        $conditions .= " and print in (1,2,3) ";
          
		if ($_REQUEST['name'] != ""){
		$bname=strim($_REQUEST['name']);
		$conditions .=" and (name like '%".$bname."%' or pinyin like '%".$bname."%' or barcode like '".$bname."')";	
		}
		if ($cate_id>0){		
		es_cookie::set("cate_id",$cate_id,2);  //2016-8-15 cate_id写入cookie
		$conditions .=" and (cate_id = $cate_id )";	  		
		}
		if ($cate_id==-9){ //全部分类 删除COOKIE
		es_cookie::delete("cate_id");		
		}
		$cate_id=es_cookie::get("cate_id");	
		if ($page>1 && $cate_id>0){		
		$conditions .=" and (cate_id = $cate_id )";	
		}
		
		
		//分类
		$sortconditions .= " where is_effect=1 and wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
		$sortconditions .= " and location_id=".$id;
		$sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
		$sqlsort.=$sortconditions. " order by sort desc";

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
		$GLOBALS['tmpl']->assign("cate_id", $cate_id);
		
		
		
		$sql_count = " select count(id) from " . DB_PREFIX . "dc_menu";
		$sql = " select * from " . DB_PREFIX . "dc_menu ";
     //   echo $sql.$conditions;
		/* 分页 */
		$page_size = 25;
		
		if ($page == 0)
			$page = 1;
		$current_page=$page;
        $GLOBALS['tmpl']->assign("page", $current_page);
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size,'&name='.$bname); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);

       // echo $sql.$conditions . " limit " . $limit;
	//	echo ($sql.$conditions . " limit " . $limit);
		$list = $GLOBALS['db']->getAll($sql.$conditions . " order by orderid asc limit " . $limit);
		//var_dump($list);
		//获取菜单分类
		$menu_cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		foreach ($menu_cate_list as $k=>$v){
			$f_menu_cate_list[$v['id']] =  $v['name'];
		}

		foreach ($list as $k=>$v){
			$list[$k]['cate_name'] = $f_menu_cate_list[$v['cate_id']]?$f_menu_cate_list[$v['cate_id']]:"暂无";
           $list[$k]['kclx']=$this->kcnx[$v['print']];
		}


		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $id);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("bname", $bname);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "菜单管理");
		$GLOBALS['tmpl']->display("pages/dc/menu_index.html");
	}

	public function load_add_menu_weebox(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$location_id = intval($_REQUEST['location_id']);

		/* 业务逻辑部分 */


		//获取菜单分类
		//$menu_cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$location_id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
	
		//=====================================武林二次开发
		$menu_cate_list = array();
		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll("select id,name,wcategory,wlevel from ".DB_PREFIX."dc_supplier_menu_cate where is_effect=1 and wlevel<4 and location_id=".$location_id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$menu_cate_list[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$wmenu1['name'] = '| - ' . $wmenu1['name'];
					$menu_cate_list[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$wmenu2['name'] = '| - - ' . $wmenu2['name'];
						$menu_cate_list[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$wmenu3['name'] = '| - - - ' . $wmenu3['name'];
							$menu_cate_list[] = $wmenu3;
						}
					}
				}
			}
		}
		//=====================================武林二次开发

		//获取菜单分类
		$menu_unit_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_unit_cate where location_id=".$location_id." ");

		//获取菜单分类
		$menu_print_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_print_cate where location_id=".$location_id." ");



		 //获取供应商
		$menu_companyname_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_companyname where location_id=".$location_id." ");


		//获取标签数据
		$tags = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_menu_cate where type=1 and is_effect=1");
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);
		$GLOBALS['tmpl']->assign("menu_cate",$menu_cate_list);
		$GLOBALS['tmpl']->assign("menu_unit",$menu_unit_list);
		 $GLOBALS['tmpl']->assign("menu_print",$menu_print_list);

		$GLOBALS['tmpl']->assign("menu_company",$menu_companyname_list);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$GLOBALS['tmpl']->assign("tags",$tags);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_menu_weebox.html");
		ajax_return($data);
	}
	public function raw_material(){
	      /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
		
		$slid=$account_info['location_ids'][0];
        //echo "<PRE>";
        //print_r($account_info);
		$sql="select * from ".DB_PREFIX."dc_menu" ;
		$list=$GLOBALS['db']->getAll($sql);
		echo "<PRE>";
		print_r($list);
		$GLOBALS['tmpl']->assign("list",$list);
		
		$GLOBALS['tmpl']->assign('supplier_id',$supplier_id);
        $GLOBALS['tmpl']->display("pages/location/raw_materal.html");
	}
	public function do_save_menu(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		$location_id = intval($_REQUEST['location_id']);
		$data['name'] = strim($_REQUEST['menu_name']);
		$data['fu_title'] = strim($_REQUEST['fu_title']);
		$data['m_desc'] = strim($_REQUEST['m_desc']);
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		$data['funit'] = $_REQUEST['funit'];
		$data['tichengmoney'] = $_REQUEST['tichengmoney'];
		$data['ticheng_style'] = $_REQUEST['ticheng_style'];
		$data['times'] = floatval($_REQUEST['times']);
		$data['orderid'] = intval($_REQUEST['orderid']);
		$data['is_effect_enable'] = intval($_REQUEST['is_effect_enable']);
		$data['is_stock'] = intval($_REQUEST['is_stock']);
		$data['is_stock_enable'] = intval($_REQUEST['is_stock_enable']);
		//缩略图片 
		if(!empty($_REQUEST['image'])){
			$pic_path=$_REQUEST['image'];
			$pic_path=str_replace("http://www.678sh.com",".",$pic_path);

            if(strpos($_REQUEST['image'],'/public/attachment') > 0){
                $pic_path = str_replace('/./','',$_REQUEST['image']);
                $data['image'] = $pic_path;
            }else{
                require_once APP_ROOT_PATH."openApi/thumpic.php";
                $t = new ThumbHandler();
                $t->setSrcImg($pic_path);
                $t->setDstImg($pic_path);
                $t->setMaskPosition(4);
                $t->setMaskImgPct(80);
                $t->createImg(400,300);
                $data['image'] =  replace_domain_to_public(strim($_REQUEST['image']));
            }
		}






		$data['price'] = floatval($_REQUEST['price']);
		$data['tags'] = implode(",", $_REQUEST['tags']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		//2016.4.24 枫叶增加 
		$data['isdazhe'] = intval($_REQUEST['isdazhe']);

		  $data['barcode'] = strim($_REQUEST['barcode']);
			$data['buyPrice'] = floatval($_REQUEST['buyPrice']);
	
			$data['customerPrice'] = floatval($_REQUEST['customerPrice']);
			  $data['sellPrice2'] = floatval($_REQUEST['sellPrice2']);
				$data['unit'] = strim($_REQUEST['unit']);
				  $data['pinyin'] = strim($_REQUEST['pinyin']);

					$data['company'] = strim($_REQUEST['company']);
					  $data['productionDate'] = strim($_REQUEST['productionDate']);
						$data['shelfLife'] = strim($_REQUEST['shelfLife']);
						  $data['maxStock'] = intval($_REQUEST['maxStock']);

							$data['minStock'] = intval($_REQUEST['minStock']);
							  $data['biaoqian'] = strim($_REQUEST['biaoqian']);
								$data['print'] = strim($_REQUEST['print']);
								  $data['info'] = strim($_REQUEST['info']);
		/* 业务逻辑部分 */
		if (!in_array($location_id, $account_info['location_ids'])){
		   $root['status'] = 0;
		   $root['info'] = "没有权限添加/修改该门店的菜单";
		}

	   $location_info = $GLOBALS['db']->getRow("select xpoint,ypoint from ".DB_PREFIX."supplier_location where id=".$location_id);
	   $data['location_id'] = $location_id;
	   $data['supplier_id'] = $supplier_id;
	   $data['xpoint'] = $location_info['xpoint'];
	   $data['ypoint'] = $location_info['ypoint'];

	   /*获取标签中文,同步函数*/

	   if($id>0){
		   $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data,"UPDATE","id=".$id);
		   
		   syn_supplier_location_menu_match($id);
		   $root['info'] = "修改成功";
	   }else{
		   $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data);
		   $id = $GLOBALS['db']->insert_id();
		   
		   syn_supplier_location_menu_match($id);
		   $root['info'] = "添加成功";
	   }

	   if($data['is_effect']==1) {
		$this->caipinpush($location_id);  
	   }

	   $root['status'] = 1;

	   $root['jump'] = url("biz","dc#dc_menu_index",array("id"=>$location_id));
	   ajax_return($root);

	}





	   public function load_edit_taste_weebox(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		/* 业务逻辑部分 */

		$vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_taste where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		//判断是否有权限和数据存在
		if(empty($vo)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}
		$location_id = $vo['location_id'];
		$vo['flavor']=json_decode( urldecode($vo['flavor']),true);
		$vo['shops']=implode(",",json_decode( $vo['shops'],true));
		$conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " and location_id=".$location_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";

$sql = " select id,name,is_effect,cate_id,price,image from " . DB_PREFIX . "dc_menu ";



		$list = $GLOBALS['db']->getAll($sql.$conditions);

	  foreach($list as $key=>$val)
	  {
		 $list[$key]["selected"]  = strpos($vo['shops'],$val["id"])===FALSE?0:1;
	  }

		$GLOBALS['tmpl']->assign("vo",$vo);
	  $GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("location_id",$location_id);


		$root['status'] =1;
		$root['html'] = $GLOBALS['tmpl']->fetch("pages/dc/edit_taste_weebox.html");

		ajax_return($root);
	}


	public function load_edit_menu_weebox(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
        $page = intval($_REQUEST['page']);
		/* 业务逻辑部分 */

		$vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." ");
		//判断是否有权限和数据存在
		if(empty($vo)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}
		$location_id = $vo['location_id'];

		//获取菜单分类
		//$menu_cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$location_id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");

		//=====================================武林二次开发
		$menu_cate_list = array();
		
		$wsublist = array();
		$wmenulist = $GLOBALS['db']->getAll("select id,name,wcategory,wlevel from ".DB_PREFIX."dc_supplier_menu_cate where  is_effect=1 and  wlevel<4 and location_id=".$location_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		
		foreach($wmenulist as $wmenu)
		{
			if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
		}
		foreach($wmenulist as $wmenu0)
		{
			if($wmenu0['wcategory'] == '0')
			{
				$menu_cate_list[] = $wmenu0;
				
				foreach($wsublist[$wmenu0['id']] as $wmenu1)
				{
					$wmenu1['name'] = '| - ' . $wmenu1['name'];
					$menu_cate_list[] = $wmenu1;
					foreach($wsublist[$wmenu1['id']] as $wmenu2)
					{
						$wmenu2['name'] = '| - - ' . $wmenu2['name'];
						$menu_cate_list[] = $wmenu2;
						foreach($wsublist[$wmenu2['id']] as $wmenu3)
						{
							$wmenu3['name'] = '| - - - ' . $wmenu3['name'];
							$menu_cate_list[] = $wmenu3;
						}
					}
				}
			}
		}
		//=====================================武林二次开发

		//获取菜单分类
		$menu_unit_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_unit_cate where location_id=".$location_id." ");

		//获取菜单分类
		$menu_print_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_print_cate where location_id=".$location_id." ");



		 //获取供应商
		$menu_companyname_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_companyname");



		//获取标签数据
		$tags = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_menu_cate where type=1 and is_effect=1");

		$cur_tags = explode(",", $vo['tags']);

		foreach ($tags as $k=>$v){
			if(in_array($v['id'], $cur_tags)){
				$tags[$k]['is_checked'] =1;
			}
		}

        $GLOBALS['tmpl']->assign("vo",$vo);
        $GLOBALS['tmpl']->assign("page",$page);
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);
		$GLOBALS['tmpl']->assign("vo",$vo);
		$GLOBALS['tmpl']->assign("menu_cate",$menu_cate_list);
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$GLOBALS['tmpl']->assign("tags",$tags);

		$GLOBALS['tmpl']->assign("menu_unit",$menu_unit_list);
		 $GLOBALS['tmpl']->assign("menu_print",$menu_print_list);

		$GLOBALS['tmpl']->assign("menu_company",$menu_companyname_list);

		$root['status'] =1;
		$root['html'] = $GLOBALS['tmpl']->fetch("pages/dc/edit_menu_weebox.html");

		ajax_return($root);
	}

	public function dc_menu_status(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$location_id = $account_info['location_ids'][0];
		/*获取参数*/
		$id = intval($_REQUEST['id']);

		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		$is_effect = $data['is_effect']>0?0:1;
		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",array("is_effect"=>$is_effect),"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['is_effect'] = $is_effect;
			$root['info'] = "修改成功";
			
			//百度推送
	   //$pushjg=menu_push($location_id,$id);
	   /*
	    require_once APP_ROOT_PATH."baidupush/sdk.php";
		$sdk = new PushSDK();	       
		
        // $tuidata=$GLOBALS['db']->getRow("select * from fanwe_dc_menu where id=".$id);		 
         $description=array();
         $description['code']='1001';
         //$description['data']=$tuidata;

		$message = array (    
 		'title' => '提示',
        'description' =>'有菜品更新' ,
		'custom_content'=>$description
		//'custom_content'=>'你有新的外卖订单'
        );
// 设置消息类型为 通知类型.
        $opts = array (
            'msg_type' => 1 
        );        
// 向目标设备发送一条消息
       // var_dump($message);
        //var_dump($opts);
		
       

        $list=$GLOBALS['db']->getAll("select appid from fanwe_app where slid='$location_id' order by loginTime desc ");	
        foreach($list as $kc=>$vc){
			$channelIdlist[]=$vc['appid'];
		}
		//var_dump($channelIdlist);
		//执行发送程序 
        $rs = $sdk -> pushBatchUniMsg($channelIdlist, $message, $opts);
		*/
		$this->caipinpush($location_id);
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}





		public function dc_menu_taste_del(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_taste where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		//查询是否有关联菜单



		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_taste where id=".$id);
		/* 数据 */
		$root['status'] =1;
		$root['jump'] = url("biz","dc#dc_menu_taste",array('id'=>$data['location_id']));


		/* ajax返回数据 */
		ajax_return($root);
	}



	public function dc_menu_del(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$location_id = $account_info['location_ids'][0];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		//查询是否有关联菜单

      //  $GLOBALS['db']->autoExecute("menu_update",array("isnew"=>"2"),"UPDATE"," isnew=1 and pid=".$id);
		//$GLOBALS['db']->query("delete from menu_update where isnew=0 and pid=".$id);
		
		//百度推送
	   //$pushjg=menu_push($location_id,$id);
	   /*
	    require_once APP_ROOT_PATH."baidupush/sdk.php";
		$sdk = new PushSDK();	       
		       		 
         $description=array();
         $description['code']='1001';
        // $description['data']=$tuidata;

		$message = array (    
 		'title' => '提示',
        'description' =>'有菜品删除' ,
		'custom_content'=>$description
		//'custom_content'=>'你有新的外卖订单'
        );
// 设置消息类型为 通知类型.
        $opts = array (
            'msg_type' => 1 
        );        
// 向目标设备发送一条消息
       // var_dump($message);
        //var_dump($opts);
		
        $list=$GLOBALS['db']->getAll("select appid from fanwe_app where slid='$location_id' order by loginTime desc ");	
        foreach($list as $kc=>$vc){
			$channelIdlist[]=$vc['appid'];
		}
		//var_dump($channelIdlist);
		//执行发送程序 
        $rs = $sdk -> pushBatchUniMsg($channelIdlist, $message, $opts);
		//return $rs;
		//$rst=json_encode($rs);
		*/
		
		$this->caipinpush($location_id);
		
		
		
		//删除数据 
		//2017-4-12修改
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set is_delete=0 where id=".$id);
		/* 数据 */
		$root['status'] =1;
		$root['jump'] = url("biz","dc#dc_menu_index",array('id'=>$data['location_id']));


		/* ajax返回数据 */
		ajax_return($root);
	}


	public function batch_del_menu(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$location_id = $account_info['location_ids'][0];
		/*获取参数*/
		$location_id = intval($_REQUEST['location_id']);
		$ids = $_REQUEST['del_ids'];
		if(empty($ids)){
			$root['status'] = 0;
			$root['info'] = '至少选中一条数据';
			ajax_return($root);
		}


		/*业务逻辑*/

		if(!in_array($location_id, $account_info['location_ids'])){
			$root['status'] = 0;
			$root['info'] = '没有管理权限';
			ajax_return($root);
		}

		foreach ($ids as $k=>$v){
			$temp_ids[] = intval($v);
		}
		$id_str = implode(",", $temp_ids);
         //2017.4.1增加是否删除
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set is_delete=0 where id in(".$id_str.") and location_id=".$location_id);
		
		//批量删除
	//	$GLOBALS['db']->autoExecute("menu_update",array("isnew"=>"2"),"UPDATE"," isnew=1 and pid in(".$id_str.")");
		//$GLOBALS['db']->query("delete from menu_update where isnew=0 and pid in(".$id_str.")");
		
		
		//百度推送
	   //$pushjg=menu_push($location_id,$id);
	   /*
	    require_once APP_ROOT_PATH."baidupush/sdk.php";
		$sdk = new PushSDK();	       
		
      //   $tuidata=array("del_menu_id"=>$temp_ids);		 
         $description=array();
         $description['code']='1001';
        // $description['data']=$tuidata;

		$message = array (    
 		'title' => '提示',
        'description' =>'有菜品删除' ,
		'custom_content'=>$description
		//'custom_content'=>'你有新的外卖订单'
        );
// 设置消息类型为 通知类型.
        $opts = array (
            'msg_type' => 1 
        );        
// 向目标设备发送一条消息
       // var_dump($message);
        //var_dump($opts);
		
        $list=$GLOBALS['db']->getAll("select appid from fanwe_app where slid='$location_id' order by loginTime desc ");	
        foreach($list as $kc=>$vc){
			$channelIdlist[]=$vc['appid'];
		}
		//var_dump($channelIdlist);
		//执行发送程序 
        $rs = $sdk -> pushBatchUniMsg($channelIdlist, $message, $opts);
		//return $rs;
		//$rst=json_encode($rs);
		*/
		if ($data['is_effect']==1){
		$this->caipinpush($location_id);	
		}		
		
		
		//$GLOBALS['db']->query("delete from `menu_update` where id in(".$id_str.") and slid=".$location_id);
		$root['status'] = 1;
		$root['info'] = "删除成功";
		$root['jump'] = url("biz","dc#dc_menu_index",array("id"=>$location_id));
		ajax_return($root);
	}

	/*=========================餐桌设置部分==================================*/

	/**
	 * 餐桌列表
	 */
	public function dc_rsitem_index(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		/* 业务逻辑部分 */
		$conditions .= " where supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " and location_id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";

		$sql_count = " select count(id) from " . DB_PREFIX . "dc_rs_item";
		$sql = " select * from " . DB_PREFIX . "dc_rs_item ";

		/* 分页 */
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne($sql_count.$conditions);
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);


		$list = $GLOBALS['db']->getAll($sql.$conditions . "order by sort desc limit " . $limit);


		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $id);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "预约项目设置");
		$GLOBALS['tmpl']->display("pages/dc/rsitem_index.html");


	}

	public function do_rsitem_sort(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		$is_effect = $data['is_effect']>0?0:1;
		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",array("sort"=>$sort),"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}



		public function do_rsitem_price(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		/*获取参数*/
		$id = intval($_REQUEST['id']);
		$price = intval($_REQUEST['price']);
		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		 if($price==0){
			$root['status'] =0;
			$root['info'] = "定金不能为0";
			ajax_return($root);
		}

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}
		$is_effect = $data['is_effect']>0?0:1;
		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",array("price"=>$price),"UPDATE"," id=".$id)){

			$root['status'] = 1;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}



	public function do_rsitem_status(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/*获取参数*/
		$id = intval($_REQUEST['id']);

		/*业务逻辑*/
		$root['status'] = 0;
		$root['info'] = "";

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
		//判断是否有权限和数据存在
		if(empty($data)){
			$root['status'] =0;
			$root['info'] = "数据不存在/没有修改权限";
			ajax_return($root);
		}

		$is_effect = $data['is_effect']>0?0:1;
		if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",array("is_effect"=>$is_effect),"UPDATE"," id=".$id)){
			$root['status'] = 1;
			$root['is_effect'] = $is_effect;
			$root['info'] = "修改成功";
		}

		/*ajax 数据返回*/
		ajax_return($root);
	}
	public function dc_add_rsitem(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$account_id = $account_info['id'];

		/* 获取参数 */
		$location_id = intval($_REQUEST['location_id']);

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $location_id);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加项目");
		$GLOBALS['tmpl']->display("pages/dc/dc_add_rsitem.html");
	}
	public function dc_edit_rsitem(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$account_id = $account_info['id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/*业务逻辑*/
		$vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(".implode(",",$account_info['location_ids'] ).")");
		if(empty($vo)){
			$root['status'] = 0;
			$root['info'] = "数据不存在/没有管理权限！";
			ajax_return($root);
		}

		/* 查询时间设置列表 */
		$rs_time_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_rs_item_time where item_id=".$id);

		/* 数据 */
		$GLOBALS['tmpl']->assign("location_id", $vo['location_id']);
		$GLOBALS['tmpl']->assign("id", $id);
		$GLOBALS['tmpl']->assign("vo", $vo);
		$GLOBALS['tmpl']->assign("rs_time_data", $rs_time_data);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "编辑餐桌");
		$GLOBALS['tmpl']->display("pages/dc/dc_edit_rsitem.html");
	}

	public function do_save_rsitem(){
		/* 基本参数初始化 */
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$location_id = intval($_REQUEST['location_id']);
		$id = intval($_REQUEST['id']);

		/* 业务逻辑 */

		$data['name'] = strim($_REQUEST['name']);
		$data['location_id'] = $location_id;
		$data['supplier_id'] = $supplier_id;
		$data['sort'] = intval($_REQUEST['sort']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$data['price'] = floatval($_REQUEST['price']);

		 if($data['price']==0){

			$root['status'] = 0;
			$root['info'] = "定金不能为0";
			ajax_return($root);
		}

		$conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
		// 只查询支持门店的
		$conditions .= " and id=".$id." and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";

		$sql = " select * from " . DB_PREFIX . "supplier_location";
		$location_data = $GLOBALS['db']->getRow($sql.$conditions);


		if(!empty($location_data)){
			$root['status'] = 0;
			$root['info'] = "数据不存在/没有管理权限！";
			ajax_return($root);
		}

		if($id>0){
			$rsitem_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id=".$location_id);
			if(empty($rsitem_data)){
				$root['status'] = 0;
				$root['info'] = "参数错误！";
				ajax_return($root);
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",$data,"UPDATE"," id=".$id);
			$root['status'] = 1;
			$root['info'] = "修改成功";
		}else{
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",$data);
			$id = $GLOBALS['db']->insert_id();
			if ($id){
				$root['status'] = 1;
				$root['info'] = "添加成功";
			}
		}



		/*获取餐桌时间配置*/
		$rs_time_arr = $_REQUEST['rs_time'];
		$total_count_arr = $_REQUEST['total_count'];
		$t_is_effect_arr = $_REQUEST['t_is_effect'];
		$rs_time_id_arr = $_REQUEST['rs_time_id'];
		foreach ($rs_time_arr as $k=>$v){
			if($v){
				$ins_data['item_id'] =$id;
				$ins_data['rs_time'] = $v;
				$ins_data['total_count'] =$total_count_arr[$k];
				$ins_data['is_effect'] =$t_is_effect_arr[$k];
				$ins_data['supplier_id'] = $supplier_id;
				$ins_data['location_id'] = $location_id;
				if($rs_time_id_arr[$k]>0){
					$GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item_time",$ins_data,"update","id=".$rs_time_id_arr[$k]);
				}else{
					$GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item_time",$ins_data);
				}
			}
		}


		/* 数据 */
		$root['jump'] = url("biz","dc#dc_rsitem_index",array('id'=>$location_id));
		ajax_return($root);
	}
	public function do_del_rsitem(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑 */
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id);
		if(empty($data)){
			$root['status'] = 0;
			$root['info'] ="数据不存在/没有管理权限";
			ajax_return($root);
		}

		if(!in_array($data['location_id'], $account_info['location_ids'])){
			$root['status'] = 0;
			$root['info'] ="没有管理权限";
			ajax_return($root);
		}
		/*删除时间配置*/
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item_time where item_id=".$id);
		//删除餐桌
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item where id=".$id);
		$root['status'] = 1;
		$root['jump'] = url("biz","dc#dc_rsitem_index",array("id"=>$data['location_id']));
		$root['info'] ="删除成功";
		ajax_return($root);
	}

	/* =============================时间配置部分 =================================*/

	/**
	 * 删除餐桌时间配置
	 */
	public function do_del_time_item(){
		/* 基本参数初始化 */
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];

		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		/* 业务逻辑 */
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_time where id=".$id);
		if(empty($data)){
			$root['status'] = 0;
			$root['info'] ="数据不存在/没有管理权限";
			ajax_return($root);
		}

		if(!in_array($data['location_id'], $account_info['location_ids'])){
			$root['status'] = 0;
			$root['info'] ="没有管理权限";
			ajax_return($root);
		}
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item_time where id=".$id);
		$root['status'] = 1;
		$root['info'] ="删除成功";
		ajax_return($root);
	}
	
	/* =============================银行卡设置 =================================*/
	public function bank_info()
	{		
				
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		/* 获取参数 */
		$id = intval($_REQUEST['id']);

		
		$supplier_info=$GLOBALS['db']->getRow("select id,tel,name,bank_info,bank_name,bank_user from  ".DB_PREFIX."supplier_location where id=".$id);

	    $supplier_info['tel']=substr_replace($supplier_info['tel'],'****',3,4);
	    $GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
	    $GLOBALS['tmpl']->assign("sms_ipcount",load_sms_ipcount());

	    
	    $GLOBALS['tmpl']->assign("supplier_info",$supplier_info);		
		
		$GLOBALS['tmpl']->assign("head_title","银行卡绑定");
		$GLOBALS['tmpl']->display("pages/bankinfo/index.html");	
	
	}
	
	public function biz_sms_code()
	{
		$s_account_info = $GLOBALS["account_info"];
		$verify_code = strim($_REQUEST['verify_code']);
		$id = intval($_REQUEST['id']);  //门店ID
	
	
		$sms_ipcount = load_sms_ipcount();
		if($sms_ipcount>1)
		{
			//需要图形验证码
			if(es_session::get("verify")!=md5($verify_code))
			{
				$data['status'] = false;
				$data['info'] = "图形验证码错误";
				$data['field'] = "verify_code";
				ajax_return($data);
			}
		}
	
		if(!check_ipop_limit(CLIENT_IP, "send_sms_code",SMS_TIMESPAN))
		{
			showErr("请勿频繁发送短信",1);
		}
	
		$mobile_phone=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where id=".$id);
		
		if(empty($mobile_phone))
		{
			$data['status'] = false;
			$data['info'] = "商户未提供验证手机号，请联系管理员";
			ajax_return($data);
		}
	
		//删除失效验证码
		$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
		$GLOBALS['db']->query($sql);
	
		$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
		if($mobile_data)
		{
			//重新发送未失效的验证码
			$code = $mobile_data['code'];
			$mobile_data['add_time'] = NOW_TIME;
			$GLOBALS['db']->query("update ".DB_PREFIX."sms_mobile_verify set add_time = '".$mobile_data['add_time']."',send_count = send_count + 1 where mobile_phone = '".$mobile_phone."'");
		}
		else
		{
			$code = rand(100000,999999);
			$mobile_data['mobile_phone'] = $mobile_phone;
			$mobile_data['add_time'] = NOW_TIME;
			$mobile_data['code'] = $code;
			$mobile_data['ip'] = CLIENT_IP;
			$GLOBALS['db']->autoExecute(DB_PREFIX."sms_mobile_verify",$mobile_data,"INSERT","","SILENT");
				
		}
		send_verify_sms($mobile_phone,$code);
		es_session::delete("verify"); //删除图形验证码
		$data['status'] = true;
		$data['info'] = "发送成功";
		$data['lesstime'] = SMS_TIMESPAN -(NOW_TIME - $mobile_data['add_time']);  //剩余时间
		$data['sms_ipcount'] = load_sms_ipcount();
		ajax_return($data);	
	
	}	
	
	public function bankupdate()
	{
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);	
		
		$bank_num=strim($_REQUEST['bank_num']);	
		$bank_name=strim($_REQUEST['bank_name']);	
		$bank_account_name=strim($_REQUEST['bank_user']);
		$location_id=intval($_REQUEST['location_id']);  
		
		if($bank_num == ''){
				$data['status'] = false;
				$data['info'] = "请输入银行账号";			
				ajax_return($data);
		}
		if($bank_name == ''){
				$data['status'] = false;
				$data['info'] = "请输入银行名称";			
				ajax_return($data);
		}
		if($bank_account_name == ''){
				$data['status'] = false;
				$data['info'] = "请输入开户人姓名";			
				ajax_return($data);
		}	
		
		if(app_conf("SMS_ON")==1){
			//短信码验证
			$sms_verify = strim($_REQUEST['sms_verify']);
			$mobile_phone=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where id=".$location_id);
			if($sms_verify == ''){
				$data['status'] = false;
				$data['info'] = "请输入手机验证码";			
				ajax_return($data);
			}
			$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
			$GLOBALS['db']->query($sql);
			
			$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");			

			if($mobile_data['code']!=$sms_verify)
			{
				$data['status'] = false;
				$data['info']	=  "手机验证码错误";
				$data['field'] = "sms_verify";
				ajax_return($data);
			}
		}else{
			$account_password = strim($_REQUEST['pwd']);			
			if($account_password == ''){
				$data['status'] = false;
				$data['info'] = "请输入密码";			
				ajax_return($data);
			}
			if(md5($account_password)!=$s_account_info['account_password']){
				$data['status'] = false;
				$data['info'] = "密码不正确";			
				ajax_return($data);
			}
		}
		
		$supplier_info=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."supplier_location where id=".$location_id);
		
		
		$datas = array();
		$datas['bank_info'] = $bank_num;
		$datas['bank_name'] = $bank_name;
		$datas['bank_user'] = $bank_account_name;


		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$datas,"UPDATE","id=".$location_id);		

		$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
			
		$data['status'] = 1;
		$data['info'] = "银行卡信息修改成功";
		ajax_return($data);		
		
	}
	
	
	/* =============================银行卡设置 =================================*/
	
	public function update_xiaofei()
	{
	/*	
	$sql="select * from posordermain where CreateTime between '2016-03-01 00:00:00' and '2016-03-31 23:59:59' ";
    $list = $GLOBALS['db']->getAll($sql);	
	//var_dump($list );
	foreach ($list as $k=>$v){
		
		$uno=$v['CardID'];
		$slid=$v['BusinessId'];	
		if ($v['Accountid']==NULL) {
        $tel=$GLOBALS['db']->getone("select tel from fanwe_dc_users where uno='$uno' and slid='$slid' and isdisable=1");		
		echo $uno.'----------'.$slid.'----------'.$tel.'<br>';
		$GLOBALS['db']->query("update posordermain set Accountid='$tel' where CardID='$uno' and BusinessId='$slid'");
		}
	}
	
	
	*/
    }
	
	 //2016-5-21 枫叶增加 异业联盟
   //红包发放记录
	public function yiyelianmeng(){
		init_app_page();
		//过滤HTML函数 
		
	   //判断是否报名
	   function isbaoming($pid,$slid,$status){
		// echo ("SELECT a.status FROM `fanwe_puzzle_log` a left join `fanwe_puzzle` b on a.pid=b.id where a.pid=$pid and a.slid=$slid");		
		 $isbaoming = $GLOBALS['db']->getRow("SELECT a.status FROM `fanwe_puzzle_log` a left join `fanwe_puzzle` b on a.pid=b.id where a.pid=$pid and a.slid=$slid");		
		if($status==0){
		 if ($isbaoming){
           //已报名			 
			 return $isbaoming['status'];
		 }else{
			 //可报名			 
			 return '2'; 
		 }
		 }else{
			 if ($isbaoming){
           //已报名			 
			 return $isbaoming['status'];
		    }else{
			  //不可报名
			return '-1'; 
		   } 
					
		 }
	   }

		$slid = intval($_REQUEST['id']);
		if ($slid==0){
		$account_info = $GLOBALS['account_info'];
		$slid=$account_info['location_ids'][0];
		}
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;
		
		
		$wfdata = '';
		$wfsql = 'where 1=1';
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (p.id ='$wfdata' or p.name like '%$wfdata%')";
			$GLOBALS['tmpl']->assign("wfdata", $wfdata);  //替换显示搜索名
		}
		
		if(isset($_REQUEST['status'])){
			$status = $_REQUEST['status'];
			$wfsql .= " and (p.status='$status')";
             $GLOBALS['tmpl']->assign("status", $status);  //替换显示状态
		}else{
			 $GLOBALS['tmpl']->assign("status", 9);  //替换显示状态
		}
	
		
		
		if($_REQUEST['type']==1){
		//我参与的活动
		//echo ("SELECT p.* FROM `fanwe_puzzle` p left join `fanwe_puzzle_log` b on p.id=b.pid ".$wfsql." and b.slid='$slid' order by p.id desc LIMIT " . $limit);
		$list = $GLOBALS['db']->getAll("SELECT p.* FROM `fanwe_puzzle` p left join `fanwe_puzzle_log` b on p.id=b.pid ".$wfsql." and b.slid='$slid' order by p.id desc LIMIT " . $limit);
		$total = $GLOBALS['db']->getAll("SELECT count(p.*) FROM `fanwe_puzzle` p left join `fanwe_puzzle_log` b on p.id=b.pid ".$wfsql." and b.slid='$slid'");
		
		}else{
		
		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_puzzle` p ".$wfsql." order by id desc");		
		$list = $GLOBALS['db']->getAll("SELECT p.* FROM `fanwe_puzzle` p ".$wfsql." order by p.id desc LIMIT " . $limit);	
		
		}
		
		if (isset($_REQUEST['type'])){
		$GLOBALS['tmpl']->assign("type", $_REQUEST['type']);  //替换报名情况		
		}else{		
		$GLOBALS['tmpl']->assign("type",9);  //替换报名情况	
		}	
		
	    
		
		foreach($list as $key => $val)
		{
			if ($val['status']=='0'){
			$list[$key]['typ'] = '未发布';	
			}elseif($val['status']=='1'){
			$list[$key]['typ'] = '已发布';	
			}elseif($val['status']=='-1'){
			$list[$key]['typ'] = '已删除';	
			}else{
			$list[$key]['typ'] = '已过期';		
			}
			$list[$key]['STime']=to_date(to_timespan($val['STime']),'Y-m-d');
			$list[$key]['ETime']=to_date(to_timespan($val['ETime']),'Y-m-d');
			$list[$key]['UpdateTime']=to_date(to_timespan($val['UpdateTime']),'Y-m-d');
		    $list[$key]['content'] = substr(htmlspecialchars($val['content']),0,40);
			$list[$key]['cityname']=get_deal_city_name($val['city_id']);
			$list[$key]['catename']=get_deal_cate_name($val['cateid']);
		    $list[$key]['isbaoming']=isbaoming($val['id'],$slid,$val['status']);
			//echo $list[$key]['isbaoming'];
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "联盟活动列表");
		$GLOBALS['tmpl']->display("pages/dc/lianmeng.html");
	}
	
	public function lianmengbaoming(){
		init_app_page();
		//过滤HTML函数
		$pid = intval($_REQUEST['id']); //活动ID
		$slid= intval($_REQUEST['slid']); //门店ID
		
		
		$op = $GLOBALS['db']->getRow("select * from fanwe_puzzle where id=".$pid);		
        $ETime=to_timespan($op['ETime']);			
        if ($ETime < NOW_TIME){
        $GLOBALS['db']->query("update fanwe_puzzle set status=-2 where id=".$pid);
        showBizErr("对不起，该活动时间已经到期了，不能进行报名！",0,url("biz","dc#yiyelianmeng&id=$slid"));			
        }
		
		
		
		$pname = $GLOBALS['db']->getRow("SELECT name,ucount from fanwe_puzzle where id=$pid");		
		$conditions .= " where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 0"; // 查询条件        
        $join = " left join " . DB_PREFIX . "deal_location_link dll on dll.deal_id = d.id ";
        $conditions .= " and dll.location_id=".$slid;  
        $sql = " select distinct(d.id),d.name from " . DB_PREFIX . "deal d";           
        $list = $GLOBALS['db']->getAll($sql . $join . $conditions . " order by d.id desc");
	    		
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("pid", $pid);
		$GLOBALS['tmpl']->assign("pname", $pname);
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("page_title", '联盟活动报名');
		$GLOBALS['tmpl']->display("pages/dc/lianmeng_bm.html");
	}
	
	public function do_lianmeng_bm(){
		init_app_page();
		//过滤HTML函数
		$pid = intval($_REQUEST['pid']); //活动ID
		$slid= intval($_REQUEST['slid']); //活动ID
		if (intval($_REQUEST['fmoney'])<1){
		showBizErr("返利金额不能低于1块钱！",0,url("biz","dc#yiyelianmeng&id=$slid"));	
		}
		
		
		$location_uye= $GLOBALS['db']->getOne("SELECT money from fanwe_supplier_location where id=$slid");
		$ucount= $GLOBALS['db']->getOne("SELECT ucount from fanwe_puzzle where id=$pid");   
		$baozhengjin=intval($_REQUEST['fmoney'])*$ucount; //计算保证金
		if ($location_uye<$baozhengjin){
		showBizErr("当前门店余额不足以发放所有的返例，请充值保证金!",0,url("biz","dc#yiyelianmeng&id=$slid"));	
		}
	
		$fmoney = $GLOBALS['db']->getOne("SELECT fmoney from fanwe_puzzle_log where pid=$pid and slid=$slid");
		
		
		if ($fmoney){
		//已经报过了	
		showBizErr("已经报过了",0,url("biz","dc#yiyelianmeng&id=$slid"));
		}else{

		//开始报名
		$data=array(
		'pid'=>intval($_REQUEST['pid']),
		'slid'=>intval($_REQUEST['slid']),
		'content'=>$_REQUEST['content'],
		'tuanid'=>intval($_REQUEST['tuanid']),
		'fmoney'=>round($_REQUEST['fmoney'],2),
		'status'=>0,
		'CreateTime'=>to_date(NOW_TIME)
		);
		$fmoney = $GLOBALS['db']->query("update ".DB_PREFIX."puzzle set baoming=baoming+1 where id=$pid");
		$GLOBALS['db']->autoExecute(DB_PREFIX."puzzle_log",$data);
		showBizSuccess("报名成功",0,url("biz","dc#yiyelianmeng&id=$slid"));		
		}
		
	}
	public function yiyelianmeng_log(){
		init_app_page();
		
		if($_REQUEST['id']){
		$slid = intval($_REQUEST['id']);
		}else{
		$slid=$s_account_info['location_ids'][0];	
		}
				
		$begin_time=$_REQUEST['begin_time'];
		$end_time=$_REQUEST['end_time'];
		if(!$_REQUEST['begin_time']||!$_REQUEST['end_time'])
		{
		 $begin_time=to_date(NOW_TIME,"Y-m")."-01 00:00:00";
		 $end_time=to_date(NOW_TIME,"Y-m-d")." 23:59:59";
		}
			
		$GLOBALS['tmpl']->assign("begin_time", $begin_time);
		$GLOBALS['tmpl']->assign("end_time", $end_time);
		
		$begin_time_s=to_timespan($begin_time);
		$end_time_s=to_timespan($end_time);
		
		$wfdata = $_REQUEST['wfid'];		
				
		$sqlstr="where a.location_id='$slid' and (a.create_time between '$begin_time_s' and '$end_time_s')";
		
		if ($wfdata){
		$sqlstr .=" and ( b.id='$wfdata' or b.user_name like '%$wfdata%')";
		}
		if ($_REQUEST['pname']){
		$pname=$_REQUEST['pname'];	
		$sqlstr .=" and ( c.id='$pname' or c.name like '%$pname%')";
		}
		
	
		
		
		
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_puzzle_money_log` a left join `fanwe_user` b on a.user_id=b.id left join `fanwe_puzzle` c on a.pid=c.id ".$sqlstr." order by a.id desc");		
		$list = $GLOBALS['db']->getAll("SELECT a.*,b.user_name,c.name FROM `fanwe_puzzle_money_log` a left join `fanwe_user` b on a.user_id=b.id left join `fanwe_puzzle` c on a.pid=c.id ".$sqlstr." order by a.id desc LIMIT " . $limit);
        foreach($list as $k=>$v){
			$list[$k]['time']=to_date($v['create_time'],'Y-m-d H:i');
		}
		
		//var_dump($list);
				
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("pname", $pname);
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "联盟发放记录");
		$GLOBALS['tmpl']->display("pages/dc/lianmeng_log.html");
		
	}
	//2015-5-30 广告管理
	public function ads_gl(){
		init_app_page();
		$ads_list=json_decode(ADSLIST,true); //解析广告方式
		//var_dump($ads_list);
		$slid = intval($_REQUEST['id']);
		
		$wfdata = '';
		$wfsql = 'where slid='.$slid;
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (name like '%{$wfdata}%')";
		}
		
		
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_ads` {$wfsql} order by id desc LIMIT " . $limit);		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `fanwe_ads` {$wfsql} order by id desc LIMIT " . $limit);

		foreach($list as $key => $val)
		{
			$list[$key]['show_ads_name']=$ads_list[$val['ads_name']];	
			//echo  $val['show_ads_name'];   
            if ($val['ads_type']=='txt'){
            $list[$key]['show_ads_type'] = '文本';			
			}elseif($val['ads_type']=='pic'){
			$list[$key]['show_ads_type'] = '图片';
   			$list[$key]['content']='<img src="'.$val['content'].'" width="300" height="200">';
			}
			if ($val['is_effect']=='0'){
            $list[$key]['show_is_effect'] = '否';
			}elseif($val['is_effect']=='1'){
			$list[$key]['show_is_effect'] = '是';	
			}
					
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "广告管理");				
		$GLOBALS['tmpl']->display("pages/dc/ads_gl.html");
	}
	public function ads_add(){

		init_app_page();
        $ads_list=json_decode(ADSLIST,true); //解析广告方式
		$sid = intval($_REQUEST['sid']); //广告ID
		$slid = intval($_REQUEST['id']);
		$account_info = $GLOBALS['account_info'];
        
		if (!$slid){
		$slid=$account_info['location_ids'][0];
		}
				
		$name = $_REQUEST['name'];
		
		if($name){
			$data['slid'] = $slid;
			$data['type'] = $_REQUEST['type'];
			$data['ads_name'] = $_REQUEST['ads_name'];
			$data['name'] = $_REQUEST['name'];
			$data['is_effect'] = $_REQUEST['is_effect'];
			$data['ads_type'] = $_REQUEST['ads_type'];
			$data['content'] = $_REQUEST['content'];
			$data['Uptime'] =to_date(NOW_TIME);		
		}
        
		$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "ads where is_effect=1 and slid='$slid' and ads_name='".$_REQUEST['ads_name']."' limit 1 ");
		if ($has){
		 showBizErr("该位置已经添加过了,如果确实需要增加，请先修改之前的广告位为禁用状态！",0,url("biz","dc#ads_gl&id=$slid"));
         die;		 
		}
		
		if($sid && $data){
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."ads",$data,"UPDATE","id='$sid'");
			showBizSuccess("修改成功",0,url("biz","dc#ads_gl&id=$slid"));						
		}elseif($data){			
			$GLOBALS['db']->autoExecute(DB_PREFIX."ads",$data);
			showBizSuccess("添加成功",0,url("biz","dc#ads_gl&id=$slid"));			

		}else{
           // echo "3";
			$charge_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "ads where id=$sid limit 1");
			$GLOBALS['tmpl']->assign("charge_info", $charge_info);
			foreach($ads_list as $ke=>$va){
			
			if ($charge_info['ads_name']==$ke){
			$list[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$list[]=array('typ'=>$ke,'name'=>$va);	
			}
			$GLOBALS['tmpl']->assign("list", $list);
		   }
			
		}
        if ($sid){
		$GLOBALS['tmpl']->assign("action_name", "修改广告");
		}else{
		$GLOBALS['tmpl']->assign("action_name", "增加广告");
		}
		
			
		
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加广告");
		$GLOBALS['tmpl']->display("pages/dc/ads_add.html");

	} 
	
	private $formatTree; //用于树型数组完成递归格式的全局变量

	private function _toFormatTree($list,$level=0,$title = 'title') 

	{

			  foreach($list as $key=>$val)

			  {

				$tmp_str=str_repeat("&nbsp;&nbsp;",$level*2);

				$tmp_str.="|--";



				$val['level'] = $level;

				$val['title_show'] = $tmp_str.$val[$title];

				if(!array_key_exists('_child',$val))

				{

				   array_push($this->formatTree,$val);

				}

				else

				{

				   $tmp_ary = $val['_child'];

				   unset($val['_child']);

				   array_push($this->formatTree,$val);

				   $this->_toFormatTree($tmp_ary,$level+1,$title); //进行下一层递归

				}

			  }

			  return;

	}

	

	public function toFormatTree($list,$title = 'title')

	{

		$list = $this->toTree($list);

		$this->formatTree = array();

		$this->_toFormatTree($list,0,$title);

		return $this->formatTree;

	}
	
	
	
	public function ads_sell(){

		init_app_page();
        $ads_list=json_decode(ADSLIST,true); //解析广告方式
		$sid = intval($_REQUEST['sid']); //广告ID
		$account_info = $GLOBALS['account_info'];
       	$slid=$account_info['location_ids'][0];
		if($_REQUEST['refuse_cate_id']){
		$refuse_cate_id_str=implode(",",$_REQUEST['refuse_cate_id']);
		}else{
		$refuse_cate_id_str='0';
		}
		$cate_tree = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "deal_cate where is_delete=0 order by id ASC");
		//require_once APP_ROOT_PATH."admin/Lib/Model/CommonModel.class.php";
		$cate_tree = toFormatTree($cate_tree,'name');
		
		if(!$sid){
		$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "ads_hall where is_effect=1 and islocked=0 and slid='$slid' and ads_name='".$_REQUEST['ads_name']."' limit 1 ");
		if ($has){
		 showBizErr("该位置已经在出售了！",0,url("biz","dc#ads_hall&id=$slid"));
         die;		 
		}
		}		
		$name = $_REQUEST['name'];
		
		if($name){
			$data['slid'] = $slid;
			$data['is_effect'] = $_REQUEST['is_effect'];
			$data['ads_name'] = $_REQUEST['ads_name'];
			$data['name'] = $_REQUEST['name'];
			$data['refuse_cate_id'] = $refuse_cate_id_str;
			$data['price'] = $_REQUEST['price'];
			$data['danwei'] = $_REQUEST['danwei'];
			$data['sellnum'] = $_REQUEST['sellnum'];
			$data['Uptime'] =to_date(NOW_TIME);		
		}
        
				
		if($sid && $data){			
			$GLOBALS['db']->autoExecute(DB_PREFIX."ads_hall",$data,"UPDATE","id='$sid'");
			showBizSuccess("修改成功",0,url("biz","dc#ads_hall&id=$slid"));						
		}elseif($data){			
			$GLOBALS['db']->autoExecute(DB_PREFIX."ads_hall",$data);
			showBizSuccess("添加成功",0,url("biz","dc#ads_hall&id=$slid"));			

		}else{
           // echo "3";
			$charge_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "ads_hall where id=$sid limit 1");
			$GLOBALS['tmpl']->assign("charge_info", $charge_info);
			foreach($ads_list as $ke=>$va){
			
			if ($charge_info['ads_name']==$ke){
			$list[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$list[]=array('typ'=>$ke,'name'=>$va);	
			}
			//以下参数处理Catetree
			if(strpos($charge_info['refuse_cate_id'],',')){			
			$post_arr=explode(",",$charge_info['refuse_cate_id']);
			 foreach ($post_arr as $value){
				 foreach($cate_tree as $kc=>$vc){
					 if($vc['id']==$value){
					$cate_tree[$kc]['select']='checked="checked"';
					}
				 }
			 }		
			
	     	}else{
			    foreach($cate_tree as $kc=>$vc){
					 if($vc['id']==$charge_info['refuse_cate_id']){
					$cate_tree[$kc]['select']='checked="checked"';
					}
				 }	
				
			}
			//Over
			
			
			
			
			$GLOBALS['tmpl']->assign("list", $list);
		}
			//var_dump($cate_tree);
		}
        if ($sid){
		$GLOBALS['tmpl']->assign("action_name", "发布出售广告");
		}else{
		$GLOBALS['tmpl']->assign("action_name", "修改出售广告");
		}
		
			
		
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "发布出售广告");
		$GLOBALS['tmpl']->display("pages/dc/ads_sell.html");

	} 
	public function ads_delete(){
		init_app_page();

		$sid = intval($_REQUEST['sid']); //广告ID
		$account_info = $GLOBALS['account_info'];
        if (!$slid){
		$slid=$account_info['location_ids'][0];
		}
		$do_delete = $GLOBALS['db']->query("delete from ".DB_PREFIX."ads where slid='$slid' and id='$sid'");
		if ($do_delete){
		showBizSuccess("删除成功",0,url("biz","dc#ads_gl&id=$slid"));		
		}else{
		showBizErr("数据错误",0,url("biz","dc#ads_gl&id=$slid"));
        }	
	}
	public function img(){
		$action = $_GET['act'];
		if($action=='delimg'){
		$filename = $_POST['imagename'];
		if(!empty($filename)){
		unlink('ads/'.$filename);
		echo '1';
		}else{
		echo '删除失败.';
		}
				}else{
		$picname = $_FILES['mypic']['name'];
		$picsize = $_FILES['mypic']['size'];
		if ($picname != "") {
		if ($picsize > 102400000000) {
		echo '图片大小不能超过1M';
		exit;
		}
		/*
		$array_extention_interdite=array('.gif','.jpg','.png'); 
		$type=ereg_replace('^[[:alnum:]]([-_.]?[[:alnum:]])*\.','.',$picname); 
		if(!in_array($type,$array_extention_interdite)){ 
		echo '{"status":"fail","result":"fail","msg":"请选择正确的文件类型"}';
		die;
		} 
		*/
		
	
		//$type=preg_replace('^[[:alnum:]]([-_.]?[[:alnum:]])*\.','.',$picname); 
		$type = strstr($picname, '.');
		$array_extention_interdite=array('.gif','.jpg','.png');
		
		//if ($type != ".jpg" && $type != ".gif" && $type != ".bmp") {
		if(!in_array($type,$array_extention_interdite)){	
			echo '图片格式不对！';
			exit;
		}
		$rand = rand(100, 999);
		$pics = date("YmdHis") . $rand . $type;
		//上传路径
		$pic_path = "ads/". $pics;
		
		
		move_uploaded_file($_FILES['mypic']['tmp_name'], $pic_path);
		}
		$arr = array(
		'name'=>$picname,
		'pic'=>$pics,
		'size'=>$size,
		'version'=>$apkversion,
		'versioncode'=>$apkcode
			);
		echo json_encode($arr);
		}
	}
	
	public function ads_hall(){
		init_app_page();
		$ads_list=json_decode(ADSLIST,true); //解析广告方式
		//var_dump($ads_list);
		$account_info = $GLOBALS['account_info'];        
		$slid=$account_info['location_ids'][0];
		
		
		$wfdata = '';
		$wfsql = 'where a.is_effect=1 and a.islocked=0';
		
		if($_REQUEST['ads_name'])
		{
			$ads_name = $_REQUEST['ads_name'];
			$wfsql .= " and a.ads_name='".$ads_name."'";
		}
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (a.name like '%{$wfdata}%')";
		}
		
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{        	   
	       $orderbynum=intval(es_cookie::get("ads_hall_".$order));
		   $orderbynum=$orderbynum+1;
		   if($orderbynum){			
			 es_cookie::set("ads_hall_".$order,$orderbynum,60);
		   }else{
			  es_cookie::set("ads_hall_".$order,$orderbynum,60);  
		   }
		   if ($order=='avg'){
		   $orderby = "order by ".$order;   
		   }else{
			$orderby = "order by a.".$order;   
		   }
			
			
			if ( $orderbynum%2==1){
			$orderby.=' desc'	;
			}else{
			$orderby.=' asc'	;	
			}
			
			
		}
		else
		{
			$orderby = "";
		}
		//echo $orderby;

		foreach($ads_list as $ke=>$va){
			
			if ($wfdata==$ke){
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va);	
			}			
		   }
		
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		//$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_ads` {$wfsql} order by id desc LIMIT " . $limit);		
		$list = $GLOBALS['db']->getAll("SELECT a.*,b.name as location_name,avg(c.num) as avg FROM `fanwe_ads_hall` a left join `fanwe_supplier_location` b on a.slid=b.id left join `fanwe_ads_stat` c on (a.slid=c.slid and a.ads_name=c.ads_name)  {$wfsql} group by a.id ".$orderby." LIMIT " . $limit);
	//	echo ("SELECT a.*,b.name as location_name,avg(c.num) FROM `fanwe_ads_hall` a left join `fanwe_supplier_location` b on a.slid=b.id left join `fanwe_ads_stat` c on a.slid=c.slid and a.ads_name=c.ads_name  {$wfsql} order by a.id desc LIMIT " . $limit);
	
	    $total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_ads_hall` a left join `fanwe_supplier_location` b on a.slid=b.id  {$wfsql} ");
    // var_dump($list);
		foreach($list as $key => $val)
		{
			$list[$key]['avg']=floatval($val['avg']);		
	        $list[$key]['show_ads_name']=$ads_list[$val['ads_name']];	
            if ($val['danwei']=='1'){
            $list[$key]['show_danwei'] = '天';			
			}else{
			$list[$key]['show_danwei'] = '次';   			
			}
			
			if(strpos($val['refuse_cate_id'],',')){
			$catenamestr="";
			$post_arr=explode(",",$val['refuse_cate_id']);
	     	foreach ($post_arr as $value)
	     	{
			$catenamestr .=	get_deal_cate_name($value).',';	  
            }
			//去逗号
	        $catenamestr = substr($catenamestr,0,strlen($catenamestr)-1); 
			$list[$key]['catename']=$catenamestr;	        
			}else{
			$list[$key]['catename']=get_deal_cate_name($val['refuse_cate_id']);	
			}
			
		
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ads_list_show", $ads_list_show);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "广告交易大厅");				
		$GLOBALS['tmpl']->display("pages/dc/ads_hall.html");
	}
	public function my_ads(){
		init_app_page();
		$ads_list=json_decode(ADSLIST,true); //解析广告方式
		//var_dump($ads_list);
		$account_info = $GLOBALS['account_info'];        
		$slid=$account_info['location_ids'][0];
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;
		
		$wfdata = '';
		$wfsql = 'where 1=1';
		
		
		if($_REQUEST['mytype']){
		$mytype=1;	
		$wfdata = '';
		$wfsql = 'where a.slid='.$slid;
		
		if($_REQUEST['ads_name'])
		{
			$ads_name = $_REQUEST['ads_name'];
			$wfsql .= " and a.ads_name='".$ads_name."'";
		}
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (a.name like '%{$wfdata}%')";
		}
		
				

		foreach($ads_list as $ke=>$va){
			
			if ($wfdata==$ke){
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va);	
			}			
		   }
		
					
		$list = $GLOBALS['db']->getAll("SELECT a.*,b.name as location_name,avg(c.num) as avg FROM `fanwe_ads_hall` a left join `fanwe_supplier_location` b on a.slid=b.id left join `fanwe_ads_stat` c on (a.slid=c.slid and a.ads_name=c.ads_name)  {$wfsql} group by a.id ".$orderby." LIMIT " . $limit);
	//	echo ("SELECT a.*,b.name as location_name,avg(c.num) as avg FROM `fanwe_ads_hall` a left join `fanwe_supplier_location` b on a.slid=b.id left join `fanwe_ads_stat` c on (a.slid=c.slid and a.ads_name=c.ads_name)  {$wfsql} group by a.id ".$orderby." LIMIT " . $limit);
	 
	    $total = $GLOBALS['db']->getOne("SELECT count(*) FROM `fanwe_ads_hall` a left join `fanwe_supplier_location` b on a.slid=b.id  {$wfsql} ");
  // var_dump($list);
		foreach($list as $key => $val)
		{
			$list[$key]['avg']=floatval($val['avg']);		
	        $list[$key]['show_ads_name']=$ads_list[$val['ads_name']];	
            if ($val['danwei']=='1'){
            $list[$key]['show_danwei'] = '天';			
			}else{
			$list[$key]['show_danwei'] = '次';   			
			}
			
			if(strpos($val['refuse_cate_id'],',')){
			$catenamestr="";
			$post_arr=explode(",",$val['refuse_cate_id']);
	     	foreach ($post_arr as $value)
	     	{
			$catenamestr .=	get_deal_cate_name($value).',';	  
            }
			//去逗号
	        $catenamestr = substr($catenamestr,0,strlen($catenamestr)-1); 
			$list[$key]['catename']=$catenamestr;	        
			}else{
			$list[$key]['catename']=get_deal_cate_name($val['refuse_cate_id']);	
			}
			
		
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ads_list_show", $ads_list_show);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("mytype", $mytype);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "我的广告中心");				
		$GLOBALS['tmpl']->display("pages/dc/ads_my.html");
		
		
		}else{
		//mytype=0 我是买家
		$wfsql.=" and a.buyer_slid=".$slid;	
		$mytype=0;
		
		
		
		
		if($_REQUEST['ads_name'])
		{
			$ads_name = $_REQUEST['ads_name'];
			$wfsql .= " and a.ads_name='".$ads_name."'";
		}
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (a.name like '%{$wfdata}%')";
		}
		
		
		

		foreach($ads_list as $ke=>$va){
			
			if ($wfdata==$ke){
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va);	
			}			
		   }
		
		
		$list = $GLOBALS['db']->getAll("SELECT a.*,b.refuse_cate_id FROM `fanwe_ads_order` a left join `fanwe_ads_hall` b on a.hallid=b.id {$wfsql} order by a.id desc LIMIT " . $limit);
		
		$total = $GLOBALS['db']->getOne("SELECT count(id) FROM `fanwe_ads_order` {$wfsql} ");    

		foreach($list as $key => $val)
		{
				
	        $list[$key]['show_ads_name']=$ads_list[$val['ads_name']];	
            if ($val['danwei']=='1'){
            $list[$key]['show_danwei'] = '天';			
			}else{
			$list[$key]['show_danwei'] = '次';   			
			}
			
			if ($val['ads_type']=='txt'){
				//echo $val['ads_type'];
            $list[$key]['show_ads_type'] = '文本';			
			}elseif($val['ads_type']=='pic'){
			$list[$key]['show_ads_type'] = '图片';
   			$list[$key]['content']='<img src="'.$val['content'].'" width="300" height="40">';
			}
			
			//操作拒绝分类 
			if(strpos($val['refuse_cate_id'],',')){
			$catenamestr="";
			$post_arr=explode(",",$val['refuse_cate_id']);
	     	foreach ($post_arr as $value)
	     	{
			$catenamestr .=	get_deal_cate_name($value).',';	  
            }
			//去逗号
	        $catenamestr = substr($catenamestr,0,strlen($catenamestr)-1); 
			$list[$key]['catename']=$catenamestr;	        
			}else{
			$list[$key]['catename']=get_deal_cate_name($val['refuse_cate_id']);	
			}
			
		
		}
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	
		/* 数据 */
		//条件
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		$GLOBALS['tmpl']->assign("ads_list_show", $ads_list_show);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("mytype", $mytype);
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "我的广告中心");				
		$GLOBALS['tmpl']->display("pages/dc/ads_my.html");
		}
	}
	public function ads_buy(){

		init_app_page();
        $ads_list=json_decode(ADSLIST,true); //解析广告方式
		$sid = intval($_REQUEST['sid']); //广告ID
		$oid = intval($_REQUEST['oid']); //广告ID
		
		$account_info = $GLOBALS['account_info'];
       // var_dump($account_info)	;	
        $slid=$account_info['location_ids'][0];
        $supplier_id=$account_info['supplier_id'];
		
		$myinfo=$GLOBALS['db']->getRow("select deal_cate_id,money from ". DB_PREFIX . "supplier_location where id=".$slid); //读取
		$mycate_id=$myinfo['deal_cate_id'];
		$mymoney=$myinfo['money'];
		if($_REQUEST['price']>$mymoney){
		showBizErr("您的帐户余额不足，请充值后购买！",0,url("biz","dc#ads_hall&id=$slid"));	
		}
		
		if (isset($_REQUEST['action']) && $_REQUEST['action']=='new' && isset($_REQUEST['sid']) ){
		$seller_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "ads_hall where is_effect=1 and islocked=0 and id=$sid limit 1");
		if(!$seller_info){
		showBizErr("数据错误",0,url("biz","dc#ads_hall&id=$slid"));	
		}
		    $seller_info['show_ads_name']=$ads_list[$seller_info['ads_name']];	
            if ($seller_info['danwei']=='1'){
            $seller_info['show_danwei'] = '天';			
			}else{
			$seller_info['show_danwei'] = '次';   			
			}
		}elseif(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'){		      
		
		}
		
		//if(strpos($seller_info['refuse_cate_id'],',')){
		$post_arr=explode(",",$seller_info['refuse_cate_id']);	
		  if(in_array($mycate_id,$post_arr)){
		  showBizErr("禁止您所在的分类购买该广告！",0,url("biz","dc#ads_hall&id=$slid"));		
		  }			
		//}
		
		
		$seller_slid = intval($_REQUEST['seller_slid']);
		$seller_supplier_id=$GLOBALS['db']->getOne("select supplier_id from ". DB_PREFIX . "supplier_location where id=".$seller_slid); //读取分类
		
		if($seller_slid){
			$data['hallid'] = $sid; //大厅ID
			$data['seller_slid'] = $seller_slid;
			$data['buyer_slid'] = $slid;
			$data['is_effect'] = $_REQUEST['is_effect'];
			$data['ads_name'] = $_REQUEST['ads_name'];						
			$data['name'] = $_REQUEST['name'];			
			$data['ads_type'] = $_REQUEST['ads_type'];
			$data['content'] = $_REQUEST['content'];
			$data['price'] = $_REQUEST['price'];
			$data['danwei'] = $_REQUEST['danwei'];
			$data['buynum'] = $_REQUEST['buynum'];			
			$data['name'] = $_REQUEST['name'];			
			$data['Uptime'] =to_date(NOW_TIME);		
		}
        		
		if($oid && $data){
			$data['hallid'] = $_REQUEST['hallid'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."ads_order",$data,"UPDATE","id='$oid'");
			showBizSuccess("广告修改成功",0,url("biz","dc#my_ads&id=$slid"));						
		}elseif($data){	
              require_once APP_ROOT_PATH."system/model/supplier.php";
	         //更新买家金额 type=6  减帐户余额
              $info='购买商家：'.$_REQUEST['seller_slid']."广告位：".$ads_list[$_REQUEST['ads_name']].",总计：". $_REQUEST['price']."元";  
    		  modify_supplier_account($_REQUEST['price'],$supplier_id,6,$info,$slid);
			//更新卖家金额：
			 $info='出售给商家：'.$slid."广告位：".$ads_list[$_REQUEST['ads_name']].",总计：". $_REQUEST['price']."元";  
    		  modify_supplier_account($_REQUEST['price'],$seller_supplier_id,3,$info,$seller_slid);
			  //锁定广告位，不能再次出售
			$GLOBALS['db']->query("update " . DB_PREFIX . "ads_hall set islocked=1 where id=".$sid);
			  
			  
			$GLOBALS['db']->autoExecute(DB_PREFIX."ads_order",$data);
			showBizSuccess("购买成功",0,url("biz","dc#my_ads&id=$slid"));			

		}else{
           // echo "3";
			$charge_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "ads_order where id=$oid limit 1");
			$GLOBALS['tmpl']->assign("charge_info", $charge_info);
			$charge_info['show_ads_name']=$ads_list[$charge_info['ads_name']];
			if ($charge_info['danwei']=='1'){
            $charge_info['show_danwei'] = '天';			
			}else{
			$charge_info['show_danwei'] = '次';   			
			}
			
		}
       		
		
		
			
		
		
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("seller_info",$seller_info);
		$GLOBALS['tmpl']->assign("charge_info",$charge_info);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		
		if (isset($_REQUEST['action']) && $_REQUEST['action']=='new' && isset($_REQUEST['sid']) ){
		$GLOBALS['tmpl']->assign("page_title", "购买广告");	
		$GLOBALS['tmpl']->display("pages/dc/ads_buy.html");
		}else{
		$GLOBALS['tmpl']->assign("page_title", "编辑广告");
		$GLOBALS['tmpl']->display("pages/dc/ads_edit.html");	
		}

	} 
	//2016-6-16 虚拟会员卡开启 及商户积分兑现设置
	public function xuser_score(){

		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$sid = intval($_REQUEST['sid']);
		//$slid = intval($_REQUEST['id']);
		$slid =  $account_info['slid'];
		//echo $slid;
		$isdisable = $_REQUEST['isdisable'];
		$trade = $_REQUEST['trade'];
		$scoretocash = $_REQUEST['scoretocash'];
        $location_info=$GLOBALS['db']->getRow("select isZhiying,is_main from fanwe_supplier_location where id=".$slid);
        $isZhiying=$location_info["isZhiying"];
        $is_main=$location_info["is_main"];	
		if($is_main==0 && $isZhiying==0){
		showBizSuccess("正在转向会员管理。。。",0,url("biz","dc#xusers&id=$slid"));	
		}
		
		
		if (isset($_REQUEST['isdisable']) && intval($_REQUEST['isdisable'])==1){
		//创建数据库表
        if ($isZhiying==1){ //加盟店
$GLOBALS['db']->query("CREATE TABLE `fanwe_user_".$supplier_id."_".$slid."_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uno` varchar(10) DEFAULT NULL COMMENT '会员印刷卡号',
  `user_id` int(11) NOT NULL COMMENT '会员ID',
  `ulevel` int(11) DEFAULT NULL,
  `tel` varchar(11) DEFAULT NULL COMMENT '会员卡消费手机号码',
  `upasswd` varchar(32) DEFAULT NULL COMMENT '会员卡消费PASS',
  `keys` varchar(10) DEFAULT NULL COMMENT 'FID 卡的KEY值',
  `slid` int(11) DEFAULT NULL COMMENT '门店ID',
  `uye` double(20,4) NOT NULL COMMENT '余额',
  `score` int(11) NOT NULL COMMENT '积分',
  `point` int(11) NOT NULL COMMENT '经验',
  `weixinid` varchar(50) DEFAULT NULL COMMENT '微信ID',
  `recordsum` int(11) DEFAULT '0' COMMENT '充值金额汇总',
  `ordersum` double(11,2) DEFAULT '0.00' COMMENT '消费金额汇总',
  `opencardmoney` int(11) DEFAULT '0' COMMENT '开卡金额',
  `isdisable` tinyint(1) DEFAULT NULL COMMENT '是否启用',
  `regDate` datetime DEFAULT NULL COMMENT '首次生成时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员的资金、积分、经验日志'");	
		}else{
				$GLOBALS['db']->query("CREATE TABLE `fanwe_user_".$supplier_id."_0_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uno` varchar(10) DEFAULT NULL COMMENT '会员印刷卡号',
  `user_id` int(11) NOT NULL COMMENT '会员ID',
  `ulevel` int(11) DEFAULT NULL,
  `tel` varchar(11) DEFAULT NULL COMMENT '会员卡消费手机号码',
  `upasswd` varchar(32) DEFAULT NULL COMMENT '会员卡消费PASS',
  `keys` varchar(10) DEFAULT NULL COMMENT 'FID 卡的KEY值',
  `slid` int(11) DEFAULT NULL COMMENT '门店ID',
  `uye` double(20,4) NOT NULL COMMENT '余额',
  `score` int(11) NOT NULL COMMENT '积分',
  `point` int(11) NOT NULL COMMENT '经验',
  `weixinid` varchar(50) DEFAULT NULL COMMENT '微信ID',
  `recordsum` int(11) DEFAULT '0' COMMENT '充值金额汇总',
  `ordersum` double(11,2) DEFAULT '0.00' COMMENT '消费金额汇总',
  `opencardmoney` int(11) DEFAULT '0' COMMENT '开卡金额',  
  `isdisable` tinyint(1) DEFAULT NULL COMMENT '是否启用',
  `regDate` datetime DEFAULT NULL COMMENT '首次生成时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员的资金、积分、经验日志'");	
		}

		}
		
		
		if (isset($_REQUEST['trade'])){
			if (intval($_REQUEST['trade'])==0 || intval($_REQUEST['scoretocash'])==0){
            showBizErr("对不起，此值不能为0！",0,url("biz","dc#xuser_score&id=$slid"));
			}else{
			$data['slid'] = $slid;
			$data['isdisable'] = $isdisable;
			$data['trade'] = $trade;
			$data['scoretocash'] = $scoretocash;
			$data['supplier_id'] = $supplier_id;
			$data['isZhiying'] = $isZhiying;
			}
		}
		
		if($sid && $data){
			//echo "1";
			$GLOBALS['db']->autoExecute(DB_PREFIX."score_cfg",$data,"UPDATE","id='$sid'");
			showBizSuccess("修改成功",0,url("biz","dc#xuser_score&id=$slid"));					
		}elseif($data){
			//echo "2";
			
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "score_cfg where slid='$slid' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."score_cfg",$data);
				showBizSuccess("设置成功！",0,url("biz","dc#xuser_score&id=$slid"));
						
			}else{
				/* 数据 */
				$GLOBALS['tmpl']->assign("syy", $data);			
			}
		}else{
            
			if ($isZhiying==1){	
			$sql="select * from fanwe_score_cfg where slid=".$slid." limit 1";
			}else{
			$sql="select a.* from fanwe_score_cfg a left join fanwe_supplier_location b on a.slid=b.id where b.is_main=1 and b.supplier_id=".$supplier_id;		
			}
			//echo $sql;
			$syy = $GLOBALS['db']->getRow($sql);
			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);			
			$sid=$syy['id'];
		}
		
		
        
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员设置-积分兑换比例");
		$GLOBALS['tmpl']->display("pages/dc/xuser_score.html");

	} 
	
	public function xusers_score_cfg(){

		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid =  $_REQUEST['id']?$_REQUEST['id']:$account_info['slid'];
		//echo $slid;		
        $location_info=$GLOBALS['db']->getRow("select isZhiying,is_main,balance_money from fanwe_supplier_location where id=".$slid);
        $isZhiying=$location_info["isZhiying"];
        $is_main=$location_info["is_main"];	
        $balance_money=$location_info["balance_money"];	
		if($balance_money<3000){
		showBizErr("开启积分消费，需要冻结保证金3000元以上！",0,url("biz","dc#xuser_score_bzj&id=$slid"));		
		}
		
		
		if($is_main==0 && $isZhiying==0){
		showBizErr("对不起，直营店只有主店拥有修改权限！",0,url("biz","dc#xusers"));	
		}
			
		$sql="select a.*,b.name from fanwe_score_xiaofei_cfg a left join fanwe_supplier_location b on a.xf_slid=b.id where a.slid=".$slid;		
		$list = $GLOBALS['db']->getAll($sql);			
		$GLOBALS['tmpl']->assign("list", $list);	
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "积分消费单位设置");
		$GLOBALS['tmpl']->display("pages/dc/xusers_score_cfg.html");

	} 
	public function xuser_score_bzj(){

		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid =  $_REQUEST['id']?$_REQUEST['id']:$account_info['slid'];
		$baozhengjin=$_REQUEST['baozhengjin']?$_REQUEST['baozhengjin']:0;
		$money=$GLOBALS['db']->getOne("select money from fanwe_supplier_location where id=".$slid);
		
		
		if($baozhengjin>0){
			if($money<$baozhengjin){
			showBizErr("用户当前余额不足，请充值！",0,url("biz","balance#autocz"));		
			}else{
			$GLOBALS['db']->query("update ".DB_PREFIX ."supplier_location set money=money-$baozhengjin,balance_money=balance_money+$baozhengjin where id=".$slid);
			   
    			//记冻结日志
				$log_data = array();
			    $log_data['log_info'] = '冻结积分交易保证金额：'.$baozhengjin;
			    $log_data['location_id']=$slid;
			    $log_data['supplier_id'] = $supplier_id;
			    $log_data['create_time'] = NOW_TIME;
			    $log_data['money'] = floatval($baozhengjin);
			    $log_data['type'] = 9; //冻结保证金额
			    $GLOBALS['db']->autoExecute("fanwe_supplier_money_log",$log_data);
				
				showBizSuccess("冻结成功！",0,url("biz","dc#xusers_score_cfg&id=$slid"));	
							    
			}
		 	
		}
         
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "商户保证金");
		$GLOBALS['tmpl']->display("pages/dc/xuser_score_bzj.html");
	}
	public function xusers(){
		init_app_page();
        $account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid = intval($_REQUEST['id']);
		$isZhiying=$GLOBALS['db']->getOne("select isZhiying from fanwe_supplier_location where id=".$slid);
		if($isZhiying==1){
		$dbname="fanwe_user_".$supplier_id."_".$slid."_info";
		}else{
		$dbname="fanwe_user_".$supplier_id."_0_info";
		}
		
		$action=$_REQUEST["action"];
		
		$wfdata = '';
		$wfsql = 'where 1=1';
		
		if($_REQUEST['wfid'])
		{
			$wfdata = $_REQUEST['wfid'];
			$wfsql .= " and (a.uno like '%{$wfdata}%' or a.`tel` like '%{$wfdata}%' or a.`keys` like '%{$wfdata}%' or a.`user_id` like '%{$wfdata}%')";
		}
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `".$dbname."` a {$wfsql} order by a.isdisable desc,a.user_id desc LIMIT 0,1000");
		
		
		
		
	//	echo ("SELECT * FROM `".$dbname."` {$wfsql} order by isdisable desc,user_id desc LIMIT " . $limit);
		$sql="SELECT a.*,b.duname FROM `".$dbname."` a left join fanwe_dc_ulevel_xusers b on a.ulevel=b.dulid {$wfsql} order by a.isdisable desc,a.user_id desc LIMIT " . $limit;
		
		if ($action=="excel"){
		$sql="SELECT a.*,b.duname FROM `".$dbname."` a left join fanwe_dc_ulevel_xusers b on a.ulevel=b.dulid {$wfsql} order by a.isdisable desc,a.user_id desc";
		}	
		
		$list = $GLOBALS['db']->getAll($sql);
      //  var_dump($list);
	  foreach($list as $k=>$v){
		 $list[$k]['regDate']=to_date(to_timespan($v['regDate'])+28800);
	  }
	    
		if ($action=="excel"){
		require_once 'Classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
// Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");
							 
		// 表头  
		    $objPHPExcel->setActiveSheetIndex()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->setActiveSheetIndex(0)  
                        ->setCellValue('A1', '会员ID') 			
                        ->setCellValue('B1', '会员级别')
                        ->setCellValue('C1', '会员名称')
                        ->setCellValue('D1', '电话')		 
                        ->setCellValue('E1', '开卡金额')
                        ->setCellValue('F1', '余额')
                        ->setCellValue('G1', '积分')
                        ->setCellValue('H1', '充值金额')
                        ->setCellValue('I1', '消费金额')             
                        ->setCellValue('J1', '开卡时间');                 
			foreach($list as $k => $v){
             $num=$k+2;
             $objPHPExcel->setActiveSheetIndex(0)
                         //Excel的第A列，uid是你查出数组的键值，下面以此类推
                          ->setCellValue('A'.$num, $v['user_id'])                        						  
                          ->setCellValue('B'.$num, $v['duname'])
                          ->setCellValue('C'.$num, $v['uno'])						 
                          //->setCellValue('D'.$num, $v['tel'])
						  ->setCellValueExplicit('D'.$num, $v['tel'],PHPExcel_Cell_DataType::TYPE_STRING)	
                          ->setCellValue('E'.$num, $v['opencardmoney'])
                          ->setCellValue('F'.$num, $v['uye'])						  
                          ->setCellValue('G'.$num, $v['score'])						  
                          ->setCellValue('H'.$num, $v['recordsum'])						  
                          ->setCellValue('I'.$num, $v['ordersum'])						  
						  ->setCellValue('J'.$num, $v['regDate']); 
            }
			

			
			$objPHPExcel->getActiveSheet()->setTitle('会员管理');
            $objPHPExcel->setActiveSheetIndex(0);	
			$filename='会员管理'.date('YmdHis');
			ob_end_clean();//清除缓冲区,避免乱码
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
            header('Cache-Control: max-age=0');            
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;					
					
		}		
		
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		
		
		$GLOBALS['tmpl']->assign("wfdata", $wfdata);
		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "整合版会员管理");
		$GLOBALS['tmpl']->display("pages/dc/xusers.html");
	}
	
	public function add_xusers(){
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid = intval($_REQUEST['id']);
		$isZhiying=$GLOBALS['db']->getOne("select isZhiying from fanwe_supplier_location where id=".$slid);
		if($isZhiying==1){
		$dbname="fanwe_user_".$supplier_id."_".$slid."_info";
		$sqlstr="supplier_id=$supplier_id and slid=$slid";
		}else{
		$dbname="fanwe_user_".$supplier_id."_0_info";
		$sqlstr="supplier_id=$supplier_id and slid=0";	
		}
						
		$user_id = intval($_REQUEST['user_id']);
		$sid = intval($_REQUEST['sid']);
		$isdisable = intval($_REQUEST['isdisable']);
		$uno = $_REQUEST['uno'];
		$uname = $_REQUEST['uname'];
		$ulevel = intval($_REQUEST['ulevel']);
		$uye = floatval($_REQUEST['uye']);
		$score = intval($_REQUEST['score']);
		$tel = $_REQUEST['tel'];
		$upasswd = $_REQUEST['upasswd'];
		$ubirthday = $_REQUEST['ubirthday'];
		$mail = $_REQUEST['mail'];
		$bithday=explode("-",$ubirthday);
		$byear=$bithday[0];
		$bmonth=$bithday[1];
		$bday=$bithday[2];
        $GLOBALS['tmpl']->assign("page_title", "添加会员");
		//XusersData
		$data = array(			
			'isdisable'=>$isdisable,
			'regDate'=>to_date(NOW_TIME),
			'uno'=>$uno,
			'ulevel'=>$ulevel,		
			'uye'=>$uye,
			'score'=>$score,
			'tel'=>$tel,		
		);
		
		if(!empty($upasswd)) $data['upasswd'] = strtoupper(md5($upasswd));
        //结束 
		
		if(!empty($tel)){//有数据
		    //检测大系统中是否存在该会员
		$userdata=array(
			'user_name'=>$uname,
			'create_time'=>NOW_TIME,
			'is_effect'=>$isdisable,
			'email'=>$mail,
			'mobile'=>$tel,
			'byear'=>$byear,
			'bmonth'=>$bmonth,
			'bday'=>$bday,
		);
		if(!empty($upasswd)) $userdata['user_pwd'] = md5($upasswd);
		 if(!empty($user_id)){
		   $data['user_id']=$user_id;
		    unset($userdata['mobile']);
		    unset($userdata['user_name']);
  		   $GLOBALS['db']->autoExecute(DB_PREFIX."user",$userdata,"UPDATE","id=$user_id"); 
		 }else{
		 $checkuser=$GLOBALS['db']->getRow("select * from fanwe_user where mobile='".$tel."'");
		 if ($checkuser){
			//有会员
		 $user_id=$checkuser['id'];
		 unset($userdata['mobile']);
		 unset($userdata['user_name']);
		 $GLOBALS['db']->autoExecute(DB_PREFIX."user",$userdata,"UPDATE","id=$user_id"); 
		  
		 }else{
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$userdata); 
		    $user_id = $GLOBALS['db']->insert_id();				
		 }
		 $data['user_id']=$user_id;	
		}
         	  
		
			 if(empty($sid)){//新增
				$GLOBALS['db']->autoExecute($dbname,$data);
				showBizSuccess("添加成功",0,url("biz","dc#xusers&id=$slid"));			
			 }else{//修改
			    unset($data['tel']);
				$GLOBALS['db']->autoExecute($dbname,$data,"UPDATE","id=$sid");
				showBizSuccess("修改成功",0,url("biz","dc#xusers&id=$slid"));	
			 }
		
		
		}elseif(!empty($sid)){//修改获取数据
			$user = $GLOBALS['db']->getRow("select a.*,b.user_name,b.email,b.byear,b.bmonth,b.bday from $dbname a left join fanwe_user b on a.user_id=b.id where a.id=$sid limit 1");
			$user['ubirthday']=$user["byear"]."-".$user["bmonth"]."-".$user["bday"];
			$GLOBALS['tmpl']->assign("user", $user );
			$GLOBALS['tmpl']->assign("page_title", "编辑会员");
		}

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "dc_ulevel_xusers where $sqlstr order by dulid ASC");
		

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		
		$GLOBALS['tmpl']->display("pages/dc/add_xusers.html");
	}
	
	public function ulevel_xusers(){
		init_app_page();
        $account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid = intval($_REQUEST['id']);
		$isZhiying=$GLOBALS['db']->getOne("select isZhiying from fanwe_supplier_location where id=".$slid);
		
		if($isZhiying==1){
		$sqlstr="supplier_id=$supplier_id and slid=$slid";
		}else{
		$sqlstr="supplier_id=$supplier_id and slid=0";	
		}

//		$kw = $_REQUEST['kw'];

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "dc_ulevel_xusers where $sqlstr order by dulid desc");


		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员等级管理");
		$GLOBALS['tmpl']->display("pages/dc/ulevel_xusers.html");
	}
	
	public function add_ulevel_xusers(){

		init_app_page();
        $account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid = intval($_REQUEST['id']);
		$check_edit=$GLOBALS['db']->getRow("select isZhiying,is_main from fanwe_supplier_location where id=".$slid);
		$isZhiying=$check_edit['isZhiying'];
		$is_main=$check_edit['is_main'];
		
		if ($isZhiying==0 && $is_main==0){
		showBizErr("直营店只有主店才有权利进行此项修改！",0,url("biz","dc#ulevel_xusers&id=$slid"));	
		}		
		if($isZhiying==1){
		$inslid=$slid;
		}else{
		$inslid=0;
		}
		
		$dulid = intval($_REQUEST['dulid']);
		$duzk = floatval($_REQUEST['duzk']);
		$upval = intval($_REQUEST['upval']);
		$duvali = intval($_REQUEST['duvali']);
		$isdisable = intval($_REQUEST['isdisable']);
		$duname = $_REQUEST['duname'];

		if(0==$isdisable){
			$duvali=0;
			$upval = 0;
		}

       $GLOBALS['tmpl']->assign("page_title", "添加等级");
		if(empty($dulid) && $duname){ //添加
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_ulevel_xusers",array(
				"supplier_id"=>$supplier_id,
				"slid"=>$inslid,
				"duzk"=>$duzk,
				"upval"=>$upval,
				"duvali"=>$duvali,
				"duname"=>$duname
			));
			showBizSuccess("添加成功",0,url("biz","dc#ulevel_xusers&id=$slid"));
				}elseif($dulid && $duname){ //编辑
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_ulevel_xusers",array(
				"supplier_id"=>$supplier_id,
				"slid"=>$inslid,
				"duzk"=>$duzk,
				"upval"=>$upval,
				"duvali"=>$duvali,
				"duname"=>$duname
			),"UPDATE","dulid='$dulid'");
			showBizSuccess("编辑成功",0,url("biz","dc#ulevel_xusers&id=$slid"));
		}elseif($dulid && empty($duname)){
			$ulevel = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "dc_ulevel_xusers where dulid=$dulid limit 1");
			/* 数据 */
			$GLOBALS['tmpl']->assign("ulevel", $ulevel);
			$GLOBALS['tmpl']->assign("page_title", "编辑等级");
		}




		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("dulid", $dulid);
		$GLOBALS['tmpl']->assign("duzk", $duzk);
		$GLOBALS['tmpl']->assign("upval", $upval);
		$GLOBALS['tmpl']->assign("duvali", $duvali);
		$GLOBALS['tmpl']->assign("duname", $duname);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));



		/* 系统默认 */		
		$GLOBALS['tmpl']->display("pages/dc/add_ulevel_xusers.html");
	}
	
	public function del_xuser(){
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$isZhiying=$GLOBALS['db']->getOne("select isZhiying from fanwe_supplier_location where id=".$slid);
		if($isZhiying==1){
		$dbname="fanwe_user_".$supplier_id."_".$slid."_info";
		}else{
		$dbname="fanwe_user_".$supplier_id."_0_info";
		}
		$GLOBALS['db']->query("delete from $dbname where id='$sid'");
		showBizSuccess("删除成功",0,url("biz","dc#xusers&id=$slid"));
	}
	
	public function xuser_posorder(){
		init_app_page();

		$user_id = $_REQUEST['user_id'];
		$slid = $_REQUEST['id'];
		
		
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$isZhiying=$GLOBALS['db']->getOne("select isZhiying from fanwe_supplier_location where id=".$slid);
		if($isZhiying==1){
		$sqlstr="where a.supplier_id=$supplier_id and a.slid=$slid and a.isZhiying=1";
		}else{
		$sqlstr="where a.supplier_id=$supplier_id and a.isZhiying=0";
		}
		
		if($user_id){
		$sqlstr.=" and a.user_id=$user_id ";	
		}
		
		
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `xusers_posordermain` $sqlstr");
	    $sql = "SELECT a.*,b.name as location_name FROM `xusers_posordermain` a left join `fanwe_supplier_location` b on a.slid=b.id $sqlstr order by id desc LIMIT " . $limit;
	    		
		$list = $GLOBALS['db']->getAll($sql);
	    if($list){
			
		foreach($list as $key => $val)
		{
			$list[$key]['CreateTime'] = to_date(strtotime($val['CreateTime'])+28800);
			if($val["Status"]=='1'){
			$list[$key]["show_status"] = '正';
			}else{
			$list[$key]["show_status"] = '负';	
			}
		}
		}
		
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		//武林分页==================================

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员消费记录");
		$GLOBALS['tmpl']->display("pages/dc/xusers_orderlist.html");
		
	}
	
	public function score_xflog(){
		init_app_page();

		$user_id = $_REQUEST['user_id'];
		$slid = $_REQUEST['id'];
		
		
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$isZhiying=$GLOBALS['db']->getOne("select isZhiying from fanwe_supplier_location where id=".$slid);
		if($isZhiying==1){
		$sqlstr="where c.supplier_id=$supplier_id and c.slid=$slid and a.isZhiying=1";
		}else{
		$sqlstr="where c.supplier_id=$supplier_id and a.isZhiying=0";
		}
		
		if($user_id){
		$sqlstr.=" and a.user_id=$user_id ";	
		}
		$sqlstr.=" and c.log_admin_id=9"; //积分消费
		
		
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `xusers_posordermain` $sqlstr");
	    $sql = "SELECT a.OrderId,a.slid,a.OriginalPrice,a.ActualPrice,a.CreateTime,a.user_id,a.tel,b.name as location_name,c.score FROM `xusers_posordermain` a left join `fanwe_supplier_location` b on a.slid=b.id left join fanwe_user_location_log c on a.OrderId=c.onum $sqlstr order by c.id desc LIMIT " . $limit;
	    		
		$list = $GLOBALS['db']->getAll($sql);
	    	
		
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
		//武林分页==================================

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "积分消费记录");
		$GLOBALS['tmpl']->display("pages/dc/xusers_score_orderlist.html");
		
	}
	
	public function xuser_posrecord(){
		$zffsarr=json_decode(ZFFSLIST,true); //解析支付方式
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		
		$slid = intval($_REQUEST['id']);
		$user_id = $_REQUEST['user_id'];
		if($user_id){
		$userstr="and `user_id`= '$user_id'";
		}
		$page_size = 50;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM `xusers_posrecord` where `slid` = '$slid' $userstr");
		
		$list = $GLOBALS['db']->getAll("SELECT * FROM `xusers_posrecord` where  `slid` = '$slid' $userstr order by id desc LIMIT " . $limit);
		
      
		foreach($list as $key => $val)
		{
			$zffs=$val['zffs'];
			$val['showpaytype']=$zffsarr[$zffs];	
			if($val["status"]=='1'){
			$val["show_status"] = '正';
			}else{
			$val["show_status"] = '负';	
			}
			
			$list[$key] = $val;
			
		}
		$page = new Page($total, $page_size); // 初始化分页对象
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);
	

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "会员充值记录");
		$GLOBALS['tmpl']->display("pages/dc/xusers_posrecord.html");
	}
	
	function generateQRfromGoogle($slid,$zpid,$zpname,$widhtHeight ='400',$EC_level='L',$margin='0') 
	{ 
	return '<center><img src="/saoma/index.php?slid='.$slid.'&zpid='.$zpid.'&zpname='.$zpname.'" widhth="'.$widhtHeight.'" Height="'.$widhtHeight.'"/></center>'; 
	} 
	
	public function creat_qrcode(){
		init_app_page();
		$zpid=intval($_REQUEST['zpid']);
		$slid=intval($_REQUEST['id']);
		$list = $GLOBALS['db']->getRow("SELECT * FROM `fanwe_dc_zp` where zpid=".$zpid);
		$qcodestr="";
		$areaname=$list['zpname'];
		$txt=$list['txt'];
		
		$zptxt=json_decode($txt,true);	
	
		foreach($zptxt as $v){            			
			$qcodestr.=$this->generateQRfromGoogle($slid,$zpid,$v);
			$qcodestr.='<br><center>'.$areaname.'--'.$v.'</center><br>';
		}
		echo $qcodestr;
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

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "仓库管理");
		$GLOBALS['tmpl']->display("pages/dc/cangku.html");
	}
	
	//添加挂账人 
	public function dc_add_guanzhangren(){

		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		
		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$name = $_REQUEST['name'];
		if($name){
			$data=$_REQUEST;
			$data['supplier_id']=$supplier_id;
			unset($data['ctl']);
			unset($data['act']);			
		}
		

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."guanzhang",$data,"UPDATE","id='$sid'");
		    showBizSuccess("编辑成功",0,url("biz","dc#dc_guanzhang&id=$slid"));
			
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "guanzhang where slid='$slid' and name='$name' limit 1 ");
			if(empty($has)){				
				
				$res=$GLOBALS['db']->autoExecute(DB_PREFIX."guanzhang",$data,"INSERT");			
				
				showBizSuccess("添加成功",0,url("biz","dc#dc_guanzhang&id=$slid"));				
			}else{
				showBizErr("已经存在的名称",0,url("biz","dc#dc_guanzhang&id=$slid"));				
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "guanzhang where id=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "添加挂账人");
		$GLOBALS['tmpl']->display("pages/dc/add_guazhangren.html");

	}
	
	//添加挂账人 
	public function dc_guanzhang_qz(){

		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		
		$gzrid = intval($_REQUEST['gzrid']);
		$slid = intval($_REQUEST['id']);
		$money = floatval($_REQUEST['money']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		
		if($money>0){
        $data=array(
		 "slid"=>$slid,
		 "gid"=>$gzrid,
		 "onum"=>'挂账冲正',
		 "ctime"=>date("Y-m-d H:i:s"),
		 "money"=>"-".$money,
		 "gname"=>$_REQUEST['gname'],
		 "memo"=>'清账');
		 
		 $sql = "update `fanwe_guanzhang` set `guamoney` = guamoney-$money where `id` = ".$gzrid;
	     $result2=$GLOBALS['db']->query($sql); //更新状态
		 $onum=date('YmdHis').rand(1000,9999);
		 $otime=NOW_TIME;
		 
		 $data_tj=array(
		 "onum"=>$onum,
		 "otime"=>$otime,
		 "pid"=>0,
		 "pnum"=>1,
		 "pmoney"=>$money,
		 "pprice"=>$money,
		 "zffs"=>'cash',
		 "slid"=>$slid,
		 "zhifustatus"=>1,
		 "tichengmoney"=>0,
		 "ticheng_status"=>1 
		 );
		  $GLOBALS['db']->autoExecute("orders_tj",$data_tj);
		 
		 $data_orders=array(
		 "onum"=>$onum,
		 "otime"=>$otime,		 
		 "money_ys"=>$money,
		 "price"=>$money,
		 "zffs"=>'cash',
		 "mid"=>$slid,
		 "zhifustatus"=>1,
		 "zdbs"=>'后台'
		 );
		  $GLOBALS['db']->autoExecute("orders",$data_orders);
		 
		 
		 $data_pay=array(
		 "onum"=>$onum,
		 "otime"=>$otime,		 
		 "zmoney"=>$money,
		 "cmoney"=>$money,
		 "zffs"=>'cash',
		 "mid"=>$slid,
		 "zhifustatus"=>1,
		 "shoukuanfang"=>1,
		 "zdbs"=>'后台',
		 "payorder"=>'Houtaiwudan'
		 );
		  $GLOBALS['db']->autoExecute("orders_pay",$data_pay);
		 
		 
		 $GLOBALS['db']->autoExecute(DB_PREFIX."guanzhang_log",$data);
		 showBizSuccess("清账成功",0,url("biz","dc#guanzhang_log&id=$slid"));
		}else{
			$gzrlist = $GLOBALS['db']->getAll("SELECT id,name,guamoney FROM " . DB_PREFIX . "guanzhang where slid=$slid order by id desc ");
          //  var_dump($gzrlist);
			$GLOBALS['tmpl']->assign("gzrlist", $gzrlist);     
		    $GLOBALS['tmpl']->assign("slid",$slid);
		    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
				
		}
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "清账");
		$GLOBALS['tmpl']->display("pages/dc/guanzhangqing.html");	

		

	}
	
	
	public function dc_guanzhang(){
		init_app_page();

		$slid = intval($_REQUEST['id']);
		$isdd = $_REQUEST['isdd'];
		$kw = $_REQUEST['kw'];

		if($kw){
			$str = "and (name like '%{$kw}%' or contact like '%{$kw}%' or tel='{$kw}')";
		}


		!isset($isdd) && $isdd = 1;
		
				

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "guanzhang where slid=$slid and isdisable=$isdd $str order by id desc ");
		//echo ("SELECT * FROM " . DB_PREFIX . "guanzhang where slid=$slid and isdisable=$isdd $str order by id desc ");
		 
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


  
		
		$GLOBALS['tmpl']->assign("page_title", "挂账人管理");
		$GLOBALS['tmpl']->display("pages/dc/cguanzhang.html");
		
	}
	
	public function guanzhang_log(){
		init_app_page();

		$slid = intval($_REQUEST['id']);	
		$kw = $_REQUEST['kw'];
		$gzrid=intval($_REQUEST['gzrid']);
		$type=intval($_REQUEST['type']);
        $str='';
		if($kw){
			$str .= "and (b.name like '%{$kw}%' or b.contact like '%{$kw}%' or b.tel='{$kw}')";
		}
		if($gzrid){
			$str .= "and (a.gid=$gzrid)";
		}
		
		if($type==2){
			$str .= "and (a.money<0 and a.memo='清账')";
		}
		if($type==1){
			$str .= "and (a.money>0)";
		}
		
        $gzrlist = $GLOBALS['db']->getAll("SELECT id,name FROM " . DB_PREFIX . "guanzhang where slid=$slid order by id desc ");
		$GLOBALS['tmpl']->assign("gzrlist", $gzrlist);
		$GLOBALS['tmpl']->assign("gzrid", $gzrid);
		$GLOBALS['tmpl']->assign("type", $type);
		
		/* 分页 */
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if ($page == 0)
			$page = 1;
		$limit = (($page - 1) * $page_size) . "," . $page_size;

		
		

		!isset($isdd) && $isdd = 1;
        $sql="select a.*,b.name from fanwe_guanzhang_log a left join fanwe_guanzhang b on a.gid=b.id where a.slid=$slid $str order by a.id desc"; 
		//echo $sql;
		$total = count($GLOBALS['db']->getAll($sql));
		$page = new Page($total, $page_size);
		$p = $page->show();
		$GLOBALS['tmpl']->assign('pages', $p);	
		
		$list = $GLOBALS['db']->getAll($sql." limit " .$limit);	
		
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


  
		
		$GLOBALS['tmpl']->assign("page_title", "挂账日志");
		$GLOBALS['tmpl']->display("pages/dc/guanzhang_log.html");
		
	}
	
	function check_zffs($zffs,$zffsarr){
	  foreach($zffsarr as $k=>$v){		 
		 if ($k==$zffs){
		 return true;
		 }
	  }		
	}
	
	 public function wx_duilie_new(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$dname = $_REQUEST['dname'];	
		if($dname){
			
			$data=$_REQUEST;
			
		}

		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."quhao",$data,"UPDATE","id='$sid'");		
			showBizSuccess("编辑成功",0,url("biz","dc#wx_duilie&id=$slid"));	
		}elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "quhao where slid='$slid' and dname='$dname' limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."quhao",$data);
				showBizSuccess("添加成功",0,url("biz","dc#wx_duilie&id=$slid"));				
			}else{
				showBizErr("队列名称重复",0,url("biz","dc#wx_duilie_new&id=$slid"));	
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "quhao where id=$sid limit 1");

			/* 数据 */
			$GLOBALS['tmpl']->assign("syy", $syy);
		}

		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "新建队列");
		$GLOBALS['tmpl']->display("pages/dc/wx_duilie_new.html");

	}	
	
	
	public function wx_duilie(){
		init_app_page();

		$slid = intval($_REQUEST['id']);		
		$kw = $_REQUEST['kw'];
        $isdd = $_REQUEST['isdd']?$_REQUEST['isdd']:"";
       
		if($kw){
			$str = "and (dname like '%$kw%')";
		}
		if($isdd){
			$str .= "and (isdisable=$isdd)";
		}
        // echo $str;
		

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "quhao where slid=$slid $str order by id desc ");



		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "客户队列");
		$GLOBALS['tmpl']->display("pages/dc/wx_paihaoduilie.html");
	}
	
	public function wx_duilie_log(){
		init_app_page();
        $account_info = $GLOBALS['account_info'];	
		
		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
		$startstr=strtotime(to_date(NOW_TIME,"Y-m")."-1");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=to_date($startstr); 
        $end_time=to_date($startend); 
        }
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$begin_time_s = to_timespan($begin_time);
		$end_time_s = to_timespan($end_time);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='dname'){
			$orderby = " order by b.dname ".$sort;			
            }else{	
			$orderby = " order by a.".$order." ".$sort;
			}
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else
		{
			$orderby = "";
			$sortImg=array();
		}
		//var_dump($sortImg);
		$slid = intval($_REQUEST['id'])?intval($_REQUEST['id']):$account_info['slid'];		
		$qdid = $_REQUEST['qdid']?$_REQUEST['qdid']:0;
		$status = $_REQUEST['status']?$_REQUEST['status']:0;
		
		$kw = $_REQUEST['kw'];
		$sqlstr="where a.slid=$slid and (a.ptime between '$begin_time' and '$end_time')";
		
		if($kw){
			$sqlstr .= "and (a.tel='$kw')";
		}
		if($qdid){
			$sqlstr .= "and (a.qdid='$qdid')";
		}
		if($status){
			$sqlstr .= "and (a.status='$status')";
		}

		$qdlist=$GLOBALS['db']->getAll("select id,dname from fanwe_quhao where slid=".$slid);
		

		$page_size = 20;		
		$page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;		

		
		
		
		$sql="SELECT a.*,b.dname FROM " . DB_PREFIX . "quhao_log a left join " . DB_PREFIX . "quhao b on a.qdid=b.id  $sqlstr $orderby limit ".$limit;
		//echo $sql;
		$tsql="SELECT count(*) FROM " . DB_PREFIX . "quhao_log a left join " . DB_PREFIX . "quhao b on a.qdid=b.id $sqlstr $orderby ";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		foreach($list as $k=>$v){
		  if($v['status']==1){
		  $list[$k]['show_status']='排队中';
		  }elseif($v['status']==2){
			$list[$k]['show_status']='已入号';  
		  }elseif($v['status']==3){
			$list[$k]['show_status']='已取消';  
		  }else{
			$list[$k]['show_status']='已过号';  
		  }
		  
		  if($v['nstatus']==1){
			$list[$k]['show_nstatus']='已通知';  
		  }else{
			$list[$k]['show_nstatus']='未通知';    
		  }
			  
			  
		}

      // var_dump($list);

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("status", $status);
		$GLOBALS['tmpl']->assign("qdlist", $qdlist);		
		$GLOBALS['tmpl']->assign("qdid", $qdid);		
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

        $p = new Page ($total,$page_size);	
		$page = $p->show();		
		$GLOBALS['tmpl']->assign ( "pages", $page );
		
		/* 系统默认 */
		$sort = $sort == 'asc' ? 1 : 0; //排序方式
		//模板赋值显示
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'kw', $_REQUEST['kw'] );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );
		$GLOBALS['tmpl']->assign ( 'sortType', $sortAlt );
		
		$GLOBALS['tmpl']->assign("page_title", "客户队列记录");
		$GLOBALS['tmpl']->display("pages/dc/wx_duilie_log.html");
	}
	
	//2016.10.22 单位职工管理
	
	
	public function danweiyuangong_cfg(){

		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$slid =  $_REQUEST['id']?$_REQUEST['id']:$account_info['slid'];
			
        $location_info=$GLOBALS['db']->getRow("select isZhiying,is_main,balance_money from fanwe_supplier_location where id=".$slid);
        $isZhiying=$location_info["isZhiying"];
        $is_main=$location_info["is_main"];	
        $balance_money=$location_info["balance_money"];	
		if($balance_money<3000){
		showBizErr("开启单位员工消费，请先预留足够的保证金！",0,url("biz","dc#xuser_score_bzj&id=$slid"));		
		}
		
		
		if($is_main==0 && $isZhiying==0){
		showBizErr("对不起，直营店只有主店拥有修改权限！",0,url("biz","dc#xuser_score&id=$slid"));	
		}
			
		$sql="select a.*,b.name from fanwe_danweiyuangong_cfg a left join fanwe_supplier_location b on a.xf_slid=b.id where a.slid=".$slid;		
		$list = $GLOBALS['db']->getAll($sql);			
		$GLOBALS['tmpl']->assign("list", $list);	
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "消费门店设置");
		$GLOBALS['tmpl']->display("pages/dc/danwei_cfg.html");

	}
	
	
	
	
	
	
	
	
	
	
	
	public function danweiyuangong(){
		init_app_page();

		$slid = intval($_REQUEST['id']);		
		$kw = $_REQUEST['kw'];
        $isdd = $_REQUEST['isdd']?$_REQUEST['isdd']:"";
       
		if($kw){
			$str = "and (name like '%$kw%' or tel=$kw)";
		}
		if($isdd){
			$str .= "and (isdisable=$isdd)";
		}
        // echo $str;
		

		$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "danweiyuangong where slid=$slid $str order by id desc ");



		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("isdd", $isdd);
		$GLOBALS['tmpl']->assign("kw", $kw);
		$GLOBALS['tmpl']->assign("list", $list);

		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));


		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "单位员工管理");
		$GLOBALS['tmpl']->display("pages/dc/danweiyuangong.html");
	}
	
	public function danweiyuangong_log(){
		init_app_page();
        $account_info = $GLOBALS['account_info'];	
		
		$CURRENT_URL='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$GLOBALS['tmpl']->assign("CURRENT_URL",$CURRENT_URL);
		
		
		if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);	
		}else{	 //默认为当天的时间		
		$start=to_date(NOW_TIME,"Y-m-d");
		$startstr=strtotime(to_date(NOW_TIME,"Y-m")."-1");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=to_date($startstr); 
        $end_time=to_date($startend); 
        }
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$begin_time_s = to_timespan($begin_time);
		$end_time_s = to_timespan($end_time);
		
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'desc' : 'asc';
		} else {
			$sort = 'asc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='name' || $order=='tel' || $order=='recordmoney' ){
			$orderby = " order by b.".$order." ".$sort;			
            }elseif($order=='cmoney')
			$orderby = " order by ".$order." ".$sort;	
			else{	
			$orderby = " order by a.".$order." ".$sort;
			}
		 $sortImg=array($order=>'<img src="/admin/Tpl/default/Common/images/'.$sort.'.gif" width="12" height="17" border="0" align="absmiddle">');
		}else
		{
			$orderby = "order by a.id desc";
			$sortImg=array();
		}
		//var_dump($sortImg);
		$slid = intval($_REQUEST['id'])?intval($_REQUEST['id']):$account_info['slid'];		
		$ygid = $_REQUEST['ygid']?$_REQUEST['ygid']:0;
		$status = $_REQUEST['status']?$_REQUEST['status']:0;
		
		$kw = $_REQUEST['kw'];
		$sqlstr="where a.slid=$slid and (a.ptime between '$begin_time' and '$end_time')";
		
		if($kw){
			$sqlstr .= "and (b.tel='$kw' or b.name like '%{$kw}%')";
		}
		if($ygid){
			$sqlstr .= "and (a.ygid='$ygid')";		}
		

		
		$page_size = 50;		
		$page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;		

		
		
		
		$sql="SELECT a.*,b.name,b.tel,b.money as cmoney,b.recordmoney FROM " . DB_PREFIX . "danweiyuangong_log a left join " . DB_PREFIX . "danweiyuangong b on a.ygid=b.id  $sqlstr $orderby limit ".$limit;
		//echo $sql;
		$tsql="SELECT count(*) FROM " . DB_PREFIX . "danweiyuangong_log a left join " . DB_PREFIX . "danweiyuangong b on a.ygid=b.id $sqlstr $orderby ";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		// var_dump($list);

		/* 数据 */
		$GLOBALS['tmpl']->assign("slid", $slid);
		$GLOBALS['tmpl']->assign("status", $status);
		$GLOBALS['tmpl']->assign("qdlist", $qdlist);		
		$GLOBALS['tmpl']->assign("qdid", $qdid);		
		$GLOBALS['tmpl']->assign("list", $list);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

        $p = new Page ($total,$page_size);	
		$page = $p->show();		
		$GLOBALS['tmpl']->assign ( "pages", $page );
		
		/* 系统默认 */
		$sort = $sort == 'asc' ? 1 : 0; //排序方式
		//模板赋值显示
		$GLOBALS['tmpl']->assign ( 'sort', $sort );
		$GLOBALS['tmpl']->assign ( 'kw', $_REQUEST['kw'] );
		$GLOBALS['tmpl']->assign ( 'order', $order );
		$GLOBALS['tmpl']->assign ( 'sortImg', $sortImg );
		$GLOBALS['tmpl']->assign ( 'sortType', $sortAlt );
		
		$GLOBALS['tmpl']->assign("page_title", "单位员工充值记录");
		$GLOBALS['tmpl']->display("pages/dc/danweiyuangong_log.html");
	}
	
	public function danweiyuangong_add(){

		init_app_page();

		$sid = intval($_REQUEST['sid']);
		$slid = intval($_REQUEST['id']);
		$user_id = intval($_REQUEST['user_id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
		$name = $_REQUEST['name'];	
		$mobile = $_REQUEST['tel'];	
		if ($_REQUEST['order_pwd']){
		$code = md5(trim($_REQUEST['order_pwd']));	
		}	
		$location_info=$GLOBALS['db']->getRow("select isZhiying,is_main,supplier_id from fanwe_supplier_location where id=".$slid);
        $isZhiying=$location_info["isZhiying"];
        $is_main=$location_info["is_main"];	
        $supplier_id=$location_info["supplier_id"];	
		if($isZhiying==1){
		$dbname="fanwe_user_".$supplier_id."_".$slid."_info";	
	    }else{		
		$dbname="fanwe_user_".$supplier_id."_0_info";	
	    }
		//创建系统会员Start
		if($sid==0){
		$user_info=$GLOBALS['db']->getRow("select id from fanwe_user where mobile='".$mobile."'");
		if($user_info){
		//更新
        $user_id=$user_info['id'];
		}else{		//创建		
		$userdata=array();
		$userdata['user_name']=$mobile;
		$userdata['user_pwd']=$code;
		$userdata['is_effect']=1;
		$userdata['mobile']=$mobile;        
		$userdata['create_time']=NOW_TIME;
		$userdata['update_time']=NOW_TIME;	  
		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$userdata);
		$user_id=$GLOBALS['db']->insert_id();
		}
		//创建商户会员		
		$shdata=array();
		$shdata['user_id']=$user_id;
		$shdata['upasswd']=strtoupper($code);
		$shdata['ulevel']=0;
		$shdata['isdisable']=1;
		$shdata['tel']=$mobile;     
		$shdata['slid']=$slid;     
		$shdata['regDate']=to_date(NOW_TIME,"Y-m-d H:i:s"); 	  
		$GLOBALS['db']->autoExecute($dbname,$shdata);
		//OVER
		}else{
			//更新UPDATE
		$userdata=array();
		//$userdata['user_name']=$mobile;
		if ($code){$userdata['user_pwd']=$code;}
		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$userdata,"UPDATE","id='$user_id'");
		
		$shdata=array();
		if ($code){$shdata['upasswd']=strtoupper($code);}	
		//$shdata['tel']=$mobile;       
		$GLOBALS['db']->autoExecute($dbname,$shdata,"UPDATE","user_id='$user_id'");
		}
		
		
		if($name){			
			$data=$_REQUEST;
			$data["user_id"]=$user_id;
		}
		if ($sid>0 && $_REQUEST['order_pwd']){
			$data['order_pwd']=md5(trim($_REQUEST['order_pwd']));
		}elseif($sid==0 && $_REQUEST['order_pwd']){
		    $data['order_pwd']=md5(trim($_REQUEST['order_pwd']));
		}else{
			unset($data['order_pwd']);
		}
        $ttitle='编辑员工';
		if($sid && $data){
			$GLOBALS['db']->autoExecute(DB_PREFIX."danweiyuangong",$data,"UPDATE","id='$sid'");	
            showBizSuccess("编辑成功",0,url("biz","dc#danweiyuangong&id=$slid"));
        }elseif($data){
			//echo "2";
			$has = $GLOBALS['db']->getRow(" select * from " . DB_PREFIX . "danweiyuangong where slid='$slid' and (`name`='$name' or `tel`='$mobile')  limit 1 ");
			if(empty($has)){
				$GLOBALS['db']->autoExecute(DB_PREFIX."danweiyuangong",$data);
				showBizSuccess("添加成功",0,url("biz","dc#danweiyuangong&id=$slid"));				
			}else{
				showBizErr("名称重复",0,url("biz","dc#danweiyuangong_add&id=$slid"));	
			}
		}else{
           // echo "3";
			$syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "danweiyuangong where id=$sid limit 1");
            
			/* 数据 */
			if (!$syy){
			$syy=array("isdisable"=>1,"createtime"=>to_date(NOW_TIME,"Y-m-d H:i:s"));	
			$ttitle='增加员工';
			}
			$GLOBALS['tmpl']->assign("syy", $syy);
			
		}
		$GLOBALS['tmpl']->assign("sid",$sid);
		$GLOBALS['tmpl']->assign("ttitle",$ttitle);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", $ttitle);
		$GLOBALS['tmpl']->display("pages/dc/danweiyuangong_add.html");

	}	
	
		public function danweiyuangong_cz(){

		init_app_page();

		$slid = intval($_REQUEST['id']);
		$user_id = intval($_REQUEST['user_id']);
		(empty($slid) || 0==$slid) && ($slid = intval($_REQUEST['slid']));
			
		//获取单位用户ID
		$danwei_users=$GLOBALS['db']->getAll("select * from fanwe_danweiyuangong where isdisable=1 and slid=".$slid); 		
		$GLOBALS['tmpl']->assign("danwei_users",$danwei_users);
		
		//$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 order by sort desc");
		//$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		
		//$tongcheng_mendian=$GLOBALS['db']->getAll("SELECT id,name from fanwe_supplier_location where city_id=(SELECT city_id from fanwe_supplier_location where id='$slid')");
		//$GLOBALS['tmpl']->assign("tongcheng_mendian",$tongcheng_mendian);
		
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", $ttitle);
		$GLOBALS['tmpl']->display("pages/dc/danweiyuangong_cz.html");

	}	
	public function danweiyuangong_cz_save(){

		init_app_page();
		$slid=$_REQUEST['slid'];	
		$money=round($_REQUEST['money'],2);
		if ($money<=0){
		showBizErr("充值金额不能小于0",0,url("biz","dc#danweiyuangong_cz"));	
		}
		$users=$_REQUEST['users'];
		if (count($users)<1){
		showBizErr("充值会员最少为1个",0,url("biz","dc#danweiyuangong_cz"));	
		}
		//增加余额
		$GLOBALS['db']->query("update fanwe_danweiyuangong set money=money+$money,recordmoney=recordmoney+$money,isOrder_list='$slids' where id in (".implode(",",$users).")"); 
		//写入日志记录
		$strin="";
		foreach($users as $u){
		$strin.="('".$slid."','".$u."','".$money."','".to_date(NOW_TIME)."'),";	 	
		}
		$strin = substr($strin,0,strlen($strin)-1); 
		$sql = "insert into `fanwe_danweiyuangong_log` (slid,ygid,money,ptime) values $strin";
	    $res=$GLOBALS['db']->query($sql);
		if($res){
		showBizSuccess("充值成功",0,url("biz","dc#danweiyuangong_log&id=$slid"));	
		}else{
		showBizErr("数据操作失败",0,url("biz","dc#danweiyuangong_cz"));
		}
		
	}
	
			//获取申请创建时间
	private function wget_time($wgetid) {
		
		global $wget_list_account;
		
		if(empty($wget_list_account)) $wget_list_account = array();
		
		if(empty($wget_list_account[$wgetid])) $wget_list_account[$wgetid] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sqgl whele  id = '{$wgetid}'");
		
		return $wget_list_account[$wgetid];
		
    }
	
	
		public function sqgl_add(){
	    init_app_page();
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."lxname ");
		$GLOBALS['tmpl']->assign('list', $list);
		$sprlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sqgl_spr");
		$GLOBALS['tmpl']->assign('sprlist', $sprlist);

				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "申请管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_add.html");
		}
		public function sqgl_index(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "新建申请");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_index.html");
		}
		public function sqgl_add_seve(){
			init_app_page();
			if($_REQUEST['button']!="")
			{
				$sqlx=$_REQUEST['sqlx'];//申请类型
				$sqrtext=$_REQUEST['sqrtext'];//申请人名字
				$sqbmtext=$_REQUEST['sqbmtext'];//申请部门
				echo "以保存";
				$slid=$_REQUEST['slid'];//店铺id
				$splx=$_REQUEST['select'];//审批类型
				$sqr=$_REQUEST['sqrtext'];//申请人
				$sqnr=$_REQUEST['sqnrtext'];//申请内容
				$spr=$_REQUEST['sprtext'];//申批人
				$add_fundsflow_sql = "insert into ".DB_PREFIX."sqgl (lx,uid,sqr,sqbm,sqnr,spr,zhuangtai)  
				   values('".$sqlx."','".$slid."','".$sqrtext."','".$sqbmtext."','".$sqnr."','".$spr."','"."等待审批"."')";
			$GLOBALS['db']->query($add_fundsflow_sql);
				$GLOBALS['tmpl']->assign("slid",$slid);
				$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
				/* 系统默认 */
				$GLOBALS['tmpl']->assign("page_title", "保存申请");
				$GLOBALS['tmpl']->display("pages/dc/sqgl_index.html");
				}else 
				{
						 echo "取消申请";
					     $GLOBALS['tmpl']->assign("page_title", "保存申请");
						 $GLOBALS['tmpl']->display("pages/dc/sqgl_index.html");
						 }
						}
						
	//我的审批
public function sqgl_mysp(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sqgl");
		$GLOBALS['tmpl']->assign('list', $list);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "我的审批");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_mysp.html");
		}
		
		
		//以归档
public function sqgl_ygd(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sqgl where zhuangtai='以归档'");
		$GLOBALS['tmpl']->assign('list', $list);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "以归档文件");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_ygd.html");
		}
		
				//回收管理
public function sqgl_hsgl(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "回收管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_hsgl.html");
		}
				//审批管理
public function sqgl_spgl(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "审批管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_spgl.html");
		}
		
						//审批管理
public function sqgl_ss(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "审批查询");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_ss.html");
		}
								//审批人管理
public function sqgl_sprgl(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sqgl_spr");
		$GLOBALS['tmpl']->assign('list', $list);
				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "申批人管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_sprgl.html");
		}
		//类型设置
		public function sqgl_lxsz(){
		init_app_page();
		$slid=$_REQUEST['slid'];
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."lxname");
		$GLOBALS['tmpl']->assign('list', $list);
				/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "类型管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_lxsz.html");
		}
			//删除审批
		public function sqgl_dele(){
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$add_fundsflow_sql = "delete from ".DB_PREFIX."sqgl where id='".$id ."'";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
	$data['status'] = 1;
		$data['info'] = "删除成功";
		ajax_return($data);
		}
			//通过审批
		public function sqgl_shtg(){
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$add_fundsflow_sql = "update ".DB_PREFIX."sqgl  set zhuangtai='以通过'  where id='".$id ."'";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
	$data['status'] = 1;
		$data['info'] = "审核成功";
		ajax_return($data);
		}
		
					//拒绝审核
		public function sqgl_shjj(){
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$add_fundsflow_sql = "update ".DB_PREFIX."sqgl  set zhuangtai='以拒绝'  where id='".$id ."'";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
	$data['status'] = 1;
		$data['info'] = "审核失败";
		ajax_return($data);
		}
		
		
		public function sqgl_gdsq(){
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$gdtime= time();
		$add_fundsflow_sql = "update ".DB_PREFIX."sqgl  set zhuangtai='以归档',gdtime='$gdtime'		where id='".$id ."'";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));

				/* 系统默认 */
	$data['status'] = 1;
		$data['info'] = "归档成功";
		ajax_return($data);
		}
		
		//载入添加类型页面
		public function sqgl_splxadd()
		{
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX." where zhuangtai='以归档'");
		$GLOBALS['tmpl']->assign('list', $list);
				$GLOBALS['tmpl']->assign("page_title", "类型管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_splxadd.html");
		}
		//添加申请项目后台功能
		public function sqgl_splxadd_seve()
		{
			init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$lxmcname=$_REQUEST['name'];
		$add_fundsflow_sql = "insert into ".DB_PREFIX."lxname (lxmcname)  values('".$lxmcname."')";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."lxname");
		$GLOBALS['tmpl']->assign('list', $list);
		$GLOBALS['tmpl']->display("pages/dc/sqgl_lxsz.html");
		}
		//删除申请项目类
		public function sqgl_lxsz_dele(){
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$add_fundsflow_sql = "delete from ".DB_PREFIX."lxname where id='".$id."'";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
				/* 系统默认 */
	$data['status'] = 1;
		$data['info'] = "删除成功";
		ajax_return($data);
		}
			//载入添加审批人页面
		public function sqgl_spradd()
		{
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
				$GLOBALS['tmpl']->assign("page_title", "类型管理");
		$GLOBALS['tmpl']->display("pages/dc/sqgl_spradd.html");
		}
		//添加审批人功能
		public function sqgl_spradd_seve()
		{
			init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$name=$_REQUEST['name'];
		$add_fundsflow_sql = "insert into ".DB_PREFIX."sqgl_spr (name)  values('".$name."')";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sqgl_spr");
		$GLOBALS['tmpl']->assign('list', $list);
		$GLOBALS['tmpl']->display("pages/dc/sqgl_sprgl.html");
		}
			//删除审批人
		public function sqgl_spr_dele(){
		init_app_page();
		$ajax = intval($_REQUEST['ajax']);
		$slid=$_REQUEST['slid'];
		$id=$_REQUEST['id'];
		$add_fundsflow_sql = "delete from ".DB_PREFIX."sqgl_spr where id='".$id."'";
		$GLOBALS['db']->query($add_fundsflow_sql);
		$GLOBALS['tmpl']->assign("slid",$slid);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
				/* 系统默认 */
	$data['status'] = 1;
		$data['info'] = "删除成功";
		ajax_return($data);
		}
		
		
	public function get_slids(){
		init_app_page();
		$account_info = $GLOBALS['account_info'];
		$slid=$account_info['slid'];
		$kwd=$_REQUEST['kwd'];
		$sql = "select id,name,isZhiying from ".DB_PREFIX."supplier_location where id!=$slid and (id='".$kwd."' or name like '%".$kwd."%') limit 50";
		//echo $sql;
		$list=$GLOBALS['db']->getAll($sql);
		$showarray=array("status"=>"success","data"=>$list);
		echo json_encode($showarray);
	}

	public function save_score_cfg(){
		init_app_page();
		$data=array();
		$data['scoretocash']=$_REQUEST['scoretocash'];
		$data['xf_slid']=$_REQUEST['xf_slid']; //消费门店
		$account_info = $GLOBALS['account_info'];
		$data['slid']=$account_info['slid'];
		$data['supplier_id']=$account_info['supplier_id'];
		$data['isZhiying']=$_REQUEST['isZhiying'];
		$slidname=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$data['xf_slid']);
		$check=$GLOBALS['db']->getRow("select * from fanwe_score_xiaofei_cfg where slid=".$data['slid']." and xf_slid=".$data['xf_slid']);
		if($check){
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."score_xiaofei_cfg",$data,"UPDATE","id=".$check['id']); 
        $id=$check['id'];
		}else{
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."score_xiaofei_cfg",$data); 
		$id = $GLOBALS['db']->insert_id();
		}
		if($res){
		$showarray=array("status"=>"success","id"=>$id,"slidname"=>$slidname);	
		}else{
		$showarray=array("status"=>"fail");
		}		
		echo json_encode($showarray);
	}	
	
	
	public function save_danweiyuangong_cfg(){
		init_app_page();
		$data=array();
		$data['xf_slid']=$_REQUEST['xf_slid']; //消费门店
		$account_info = $GLOBALS['account_info'];
		$data['slid']=$account_info['slid'];
		$data['supplier_id']=$account_info['supplier_id'];
		$data['isZhiying']=$_REQUEST['isZhiying'];
		$slidname=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$data['xf_slid']);
		$check=$GLOBALS['db']->getRow("select * from fanwe_danweiyuangong_cfg where slid=".$data['slid']." and xf_slid=".$data['xf_slid']);
		if($check){
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."danweiyuangong_cfg",$data,"UPDATE","id=".$check['id']); 
        $id=$check['id'];
		}else{
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."danweiyuangong_cfg",$data); 
		$id = $GLOBALS['db']->insert_id();
		}
		if($res){
		$showarray=array("status"=>"success","id"=>$id,"slidname"=>$slidname);	
		}else{
		$showarray=array("status"=>"fail");
		}		
		echo json_encode($showarray);
	}	
	
    public function del_score_cfg(){
		init_app_page();
		$id=$_REQUEST['id'];
		$res=$GLOBALS['db']->query("delete from ".DB_PREFIX."score_xiaofei_cfg where id='$id'");
		if($res){
		$showarray=array("status"=>"success","id"=>$id);	
		}else{
		$showarray=array("status"=>"fail");
		}
		echo json_encode($showarray);
	}	
	
	public function del_danweiyuangong_cfg(){
		init_app_page();
		$id=$_REQUEST['id'];
		$res=$GLOBALS['db']->query("delete from ".DB_PREFIX."danweiyuangong_cfg where id='$id'");
		if($res){
		$showarray=array("status"=>"success","id"=>$id);	
		}else{
		$showarray=array("status"=>"fail");
		}
		echo json_encode($showarray);
	}	
	
	function caipinpush($location_id){
		
		
		require_once APP_ROOT_PATH."openApi/gl/hy_tool.class.php";
		
		$list=$GLOBALS['db']->getAll("select appid from fanwe_app where slid='$location_id' order by loginTime desc ");	
        foreach($list as $kc=>$vc){
			$channelIdlist[]=$vc['appid'];
		}		
			
		$type = "cmd";

			 
        $description=array('code'=>'1001');
	    $message = array (    
 		'title' => '提示',
        'description' =>'有菜品更新' ,
		'custom_content'=>$description
        ); 		
		
		$ht = new HyTool ();
        $ht->sendMessage ($channelIdlist,$type,$message);
        $ht->sendMessage_NEW ($channelIdlist,$type,$message);


	}
		
}
?>
