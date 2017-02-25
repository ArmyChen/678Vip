<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcbuyModule extends MainBaseModule
{
	
	/**
	 * 商家详细页中的外卖页面
	 * location_dc_table_cart:预订的购物车信息
	 * location_dc_cart：外卖的购物车信息
	 **/
	public function index()
	{

		//var_dump($_COOKIE);
	    $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	    es_cookie::set("current_wx_url",$current_wx_url);
		global_run();
		$location_id= intval($_REQUEST['lid']);
		$user_info = es_session::get('user_info');
		//2017.2.6 增加商户扫码点餐手机验证设置
		$dc_is_checktel=$GLOBALS['db']->getOne("select dc_is_checktel from ".DB_PREFIX."supplier_location where id=".$location_id);
		if($dc_is_checktel==1 && $user_info['mobile']=="")  { //开启必须验证手机号
		app_redirect(wap_url('index','user#wx_register',""));
		}
		$zpid= intval($_REQUEST['zpid']);
		$zpname= $_REQUEST['zpname'];
		$dc_wsno=$_REQUEST['dc_wsno']?$_REQUEST['dc_wsno']:es_cookie::get('dc_wsno');
//		echo $location_id;die;
		if($zpid){
		$isopen_waiter=$GLOBALS['db']->getOne("select isopen_waiter from fanwe_supplier_location where id=".$location_id);
		if($isopen_waiter==1 && $dc_wsno==""){

		  $sql="select wid,sno,realname,tel,picurl from fanwe_waiter where isdisable=1 and slid=".$location_id;
		  $dc_yxy = $GLOBALS['db']->getAll($sql);
		  $GLOBALS['tmpl']->assign("dc_yxy",$dc_yxy);
		  $GLOBALS['tmpl']->assign("location_id",$location_id);
		  $GLOBALS['tmpl']->assign("zpid",$zpid);
		  $GLOBALS['tmpl']->assign("zpname",$zpname);
		  $GLOBALS['tmpl']->display("dc/dcbuy2.html");
		}else{
		app_redirect(wap_url('index','dcbuy#dcbuy',array("lid"=>$location_id,"zpid"=>$zpid,"zpname"=>$zpname)));
		}
		}else{
		app_redirect(wap_url('index','dcbuy#dcbuy',array("lid"=>$location_id)));
		}

	}

	public function save_dc_wsno()
	{
		global_run();
		$dc_wsno=$_REQUEST['dc_wsno'];
		$realname=$_REQUEST['realname'];
		es_cookie::set('dc_wsno',$dc_wsno,3600*1*1);//写入COOKIE
		es_cookie::set('realname',$realname,3600*1*1);//写入COOKIE
		$location_id= intval($_REQUEST['lid']);
		$zpid= intval($_REQUEST['zpid']);
		$zpname= $_REQUEST['zpname'];
		app_redirect(wap_url('index','dcbuy#dcbuy',array("lid"=>$location_id,"zpid"=>$zpid,"zpname"=>$zpname)));

	}
	public function dcbuy()
	{	
		
	    // $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	    //es_cookie::set("current_wx_url",$current_wx_url);
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$location_id=$_REQUEST["lid"];

		//echo $user_id;/

		$dc_order_id=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_order where user_id=$user_id and location_id=$location_id and confirm_status<2 and pay_status=1 order by id desc limit 1");
		/*
		if($dc_order_id){
         showSuccess('已下单，正在转入订单详情，请等待！',0,wap_url('index','dc_dcorder&act=view&id='.$dc_order_id));
         die;
	     }
		*/

		//开始身边团购的地理定位
	    $tid=intval($_REQUEST['tid']);

		$param['lid'] =$location_id= intval($_REQUEST['lid']);

		require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
		$lid_info=array('location_id'=>$location_id,'menu_status'=>1);
		dcajaxModule::set_dc_cart_menu_status(0,$lid_info);

		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
//	var_dump($location_dc_cart);die;
		$data = request_api("dcbuy","index",$param);
//		var_dump($data);die;

		if($data['is_has_location']==1)
		{	
			$GLOBALS['tmpl']->assign('s_info',$s_info);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			//print_r($location_dc_cart);
			//print_r($data);

		//	echo get_gopreview();
			$GLOBALS['tmpl']->assign("tid",$tid);
			$GLOBALS['tmpl']->display("dc/dcbuy.html");
		
		}
		else
		{	
			showErr('商家不存在',0,wap_url('index','dc'));

		}
		
		
	}

	public function quhao()
	{



	    $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	    es_cookie::set("current_wx_url",$current_wx_url);
		global_run();

		$start=to_date(NOW_TIME,"Y-m-d");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=$start.' 00:00:00';
        $end_time=to_date($startend);

		$wx_openid=$_SESSION['fanwewx_info']['openid'];
		$user_id=$_SESSION['fanweuser_info']['id'];


		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$wx_openid=isset($GLOBALS['wx_info']['openid'])?$GLOBALS['wx_info']['openid']:0;
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign("wx_openid",$wx_openid);
		//开始身边团购的地理定位
	    $tid=intval($_REQUEST['tid']);

		$param['lid'] =$location_id= intval($_REQUEST['lid']);

		$location_info=$GLOBALS['db']->getRow("select name,address,tel,preview from fanwe_supplier_location where id=".$location_id);
		$GLOBALS['tmpl']->assign("location_info",$location_info);
		$sql="select id,dname,gnum,(select count(*) from fanwe_quhao_log b where a.id=b.qdid and b.status=1 and (b.ptime between '$begin_time' and '$end_time' ) ) as waitnum,(select haoma from fanwe_quhao_log b where a.id=b.qdid and b.status=1 and (b.ptime between '$begin_time' and '$end_time' )  order by haomaint asc limit 1) as dangqian from fanwe_quhao a where a.slid=$location_id order by a.gnum asc";
		//echo $sql;
		$dangqianlist=$GLOBALS['db']->getAll($sql);

		$GLOBALS['tmpl']->assign("dangqianlist",$dangqianlist);
		$dqhm=$GLOBALS['db']->getRow("select qdid,haoma from fanwe_quhao_log where slid=$location_id and wxopenid='$wx_openid' and `status`=1 and (ptime between '$begin_time' and '$end_time' )");
		if($dqhm){
		$qdid=$dqhm['qdid'];
		$beforenum=$GLOBALS['db']->getOne("select count(*) from fanwe_quhao_log where slid=$location_id and qdid=$qdid and `status`=1 and (ptime between '$begin_time' and '$end_time' )");

		if ($beforenum){
		$beforenum=$beforenum-1;
		}
		$dqhm['beforenum']=$beforenum;
		}

		$GLOBALS['tmpl']->assign("dqhm",$dqhm);
		$GLOBALS['tmpl']->assign("location_id",$location_id);



			$GLOBALS['tmpl']->assign('s_info',$s_info);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			//print_r($location_dc_cart);
			//print_r($data);

		//	echo get_gopreview();
			$GLOBALS['tmpl']->assign("tid",$tid);
			$GLOBALS['tmpl']->display("dc/quhao.html");




	}


	public function quhao_save()
	{


		global_run();
		$start=to_date(NOW_TIME,"Y-m-d");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=$start.' 00:00:00';
        $end_time=to_date($startend);

		$location_id= intval($_REQUEST['lid']);
		$wx_openid=$_SESSION['fanwewx_info']['openid'];
		$user_id=$_SESSION['fanweuser_info']['id'];
		$gnum=intval($_REQUEST['gnum']);
		$maxqhidrow=$GLOBALS['db']->getRow("select id,qianzhui,gnum from fanwe_quhao where slid=$location_id  order by gnum desc limit 1");
		if ($gnum>$maxqhidrow['gnum']){
		$qhidrow=$maxqhidrow;
		}else{
		$qhidrow=$GLOBALS['db']->getRow("select id,qianzhui from fanwe_quhao where slid=$location_id and isdisable=1 and gnum>=$gnum order by gnum asc limit 1");
		}
		$lasthaoma=$GLOBALS['db']->getOne("select haomaint from fanwe_quhao_log where slid=$location_id and status=1 and qdid=".$qhidrow['id']." and (ptime between '$begin_time' and '$end_time' ) order by haomaint desc limit 1");
		if (!$lasthaoma){$lasthaoma=1;}else{$lasthaoma=$lasthaoma+1;}

		$data=array(
		"slid"=>$location_id,
		"qdid"=>$qhidrow['id'],
		"haoma"=>$qhidrow['qianzhui'].$lasthaoma,
		"haomaint"=>$lasthaoma,
		"user_id"=>$user_id,
		"tel"=>$_REQUEST['tel'],
		"status"=>1,
		"gnum"=>$gnum,
		"nstatus"=>0,
		"ptime"=>to_date(NOW_TIME),
		"wxopenid"=>$wx_openid
		);
		$res=$GLOBALS['db']->autoExecute(DB_PREFIX."quhao_log",$data);
		if ($res){
		  showSuccess('取号成功',0,wap_url('index','dcbuy&act=quhao&lid='.$location_id));
		}else{
		  showErr('取号错误',0,wap_url('index','dcbuy&act=quhao&lid='.$location_id));
		}
	}

	public function quhao_action()
	{


		global_run();
		$start=to_date(NOW_TIME,"Y-m-d");
        $startend=strtotime($start)+24*3600-1;
        $begin_time=$start.' 00:00:00';
        $end_time=to_date($startend);

		$location_id= intval($_REQUEST['lid']);
		$wx_openid=$_SESSION['fanwewx_info']['openid'];
		$user_id=$_SESSION['fanweuser_info']['id'];


		$res=$GLOBALS['db']->query("update fanwe_quhao_log set `status`=3 where slid=$location_id and wxopenid='$wx_openid' and `status`=1");
		if ($res){
		  showSuccess('成功取消',0,wap_url('index','dcbuy&act=quhao&lid='.$location_id));
		}else{
		  showErr('错误',0,wap_url('index','dcbuy&act=quhao&lid='.$location_id));
		}
	}


    public function dcNative()
	{

	    $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	    es_cookie::set("current_wx_url",$current_wx_url);
		global_run();
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;

		//开始身边团购的地理定位
	    $tid=intval($_REQUEST['tid']);
		//2016=7-16 扫码点餐 位置写入cookie

        $zpid=intval($_REQUEST['zpid']);
        $slid=intval($_REQUEST['lid']);
        $zpname=$_REQUEST['zpname'];
		es_cookie::set("zpid",$zpid,3600*1*1);
		es_cookie::set("zpname",$zpname,3600*1*1);
		es_cookie::set("location_id",$slid,3600*1*1);


		$param['lid'] =$location_id= intval($_REQUEST['lid']);


		require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
		$lid_info=array('location_id'=>$location_id,'menu_status'=>1);
		dcajaxModule::set_dc_cart_menu_status(0,$lid_info);

		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);

		$data = request_api("dcbuy","index",$param);

		if($data['is_has_location']==1)
		{
			$GLOBALS['tmpl']->assign('s_info',$s_info);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			//print_r($location_dc_cart);
			//print_r($data);

		//	echo get_gopreview();
		//$zpname=es_cookie::get('zpname');
		//echo $zpname;

			$GLOBALS['tmpl']->assign("tid",$tid);
			$GLOBALS['tmpl']->assign("zpid",$zpid);
			$GLOBALS['tmpl']->assign("slid",$slid);
			$GLOBALS['tmpl']->assign("zpname",$zpname);
			$url="index.php?ctl=store&data_id=".$slid."&zpid=".$zpid."&zpname= ".$zpname;
			$GLOBALS['tmpl']->assign("tzurl",$url);
			$GLOBALS['tmpl']->display("dc/dcNative.html");

		}
		else
		{
			showErr('商家不存在',0,wap_url('index','dc'));

		}


	}

	 public function weixindcNative()
	{

	    $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	    es_cookie::set("current_wx_url",$current_wx_url);
		global_run();
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;

		//开始身边团购的地理定位
	    $tid=intval($_REQUEST['tid']);
		//2016=7-16 扫码点餐 位置写入cookie

        $zpid=intval($_REQUEST['zpid']);
        $slid=intval($_REQUEST['lid']);
        $zpname=$_REQUEST['zpname'];
		es_cookie::set("zpid",$zpid,3600*1*1);
		es_cookie::set("zpname",$zpname,3600*1*1);
		es_cookie::set("location_id",$slid,3600*1*1);


		$param['lid'] =$location_id= intval($_REQUEST['lid']);


		require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
		$lid_info=array('location_id'=>$location_id,'menu_status'=>1);
		dcajaxModule::set_dc_cart_menu_status(0,$lid_info);

		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);

		$data = request_api("dcbuy","index",$param);
		if($data['is_has_location']==1)
		{
			$GLOBALS['tmpl']->assign('s_info',$s_info);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			//print_r($location_dc_cart);
			//print_r($data);

		//	echo get_gopreview();
		//$zpname=es_cookie::get('zpname');
		//echo $zpname;

			$GLOBALS['tmpl']->assign("tid",$tid);
			$GLOBALS['tmpl']->display("dc/dcNative.html");

		}
		else
		{
			showErr('商家不存在',0,wap_url('index','dc'));

		}


	}
    public function do_searchname()
    {
       if($_REQUEST['sname'])
       {
		$location_id=intval($_REQUEST['slid']);
		$bname=$_REQUEST['sname'];
        $dataname = $GLOBALS['db']->getAll("select * from fanwe_dc_menu where location_id=$location_id  and name like '%".$bname."%' or barcode like '%".$bname."%'");
       // $countname = $GLOBALS['db']->getAll("select * from fanwe_dc_cart where location_id=$location_id  and name like '%".$bname."%' or barcode like '%".$bname."%'");

        $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        es_cookie::set("current_wx_url",$current_wx_url);
        global_run();
        require_once APP_ROOT_PATH."system/model/dc.php";
        $s_info=get_lastest_search_name();
        $user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
        $param['lid'] =$location_id;
        require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
        $lid_info=array('location_id'=>$location_id,'menu_status'=>1);
        dcajaxModule::set_dc_cart_menu_status(0,$lid_info);

        $location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
        $location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);

        $data = request_api("dcbuy","index",$param);

         //print_r($data['dclocation']['location_menu_cate']['sub_menu']);
         $dclocation=array();
         $menu=array();
         foreach ($data['dclocation']['location_menu_cate'] as $key=>$val){
         $dclocation[]=$val['sub_menu'];
         }

         foreach ($dclocation as $key=>$val){
             foreach ($val as $k=>$v){
                $menu[$v['name']]=$v;
             }
         }

         $smenu=array();

         foreach ($dataname as $k=>$v){
             $smenu[$v['name']]=$v;
         }
        // print_r($menu);

         //print_r($smenu);


         function myfunction($a,$b)
        {
        if ($a===$b)
          {
          return 0;
          }
          return ($a>$b)?1:-1;
        }
        $result=array_intersect_ukey($menu,$smenu,"myfunction");
        //print_r($result);
        //die;
		if($dataname)
		{
			$GLOBALS['tmpl']->assign('s_info',$s_info);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("dataname",$dataname);
			$GLOBALS['tmpl']->assign("result",$result);
			//$GLOBALS['tmpl']->assign("smenu",$smenu);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			//print_r($location_dc_cart);
			//print_r($data);

		//	echo get_gopreview();
		//$zpname=es_cookie::get('zpname');
		//echo $zpname;

			$GLOBALS['tmpl']->assign("tid",$tid);
			$GLOBALS['tmpl']->display("dc/dcbuy.html");

		}
		else
		{
			showErr('商品不存在',0,wap_url('index','dcbuy#dcNativeweixin',$param));

		}
       }else
		{
			showErr('搜索不能为空',0,wap_url('index','dcbuy#dcNativeweixin',$param));

		}
	}

	//测试用
    public function dcattr()
    {

        // $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        //es_cookie::set("current_wx_url",$current_wx_url);
        global_run();
        $sql = "select * from ".DB_PREFIX."dc_supplier_taste where shops like '%".$_REQUEST['id']."%' and location_id in (".$_REQUEST['lid'].")";
//        $sql = "select * from ".DB_PREFIX."dc_supplier_taste where location_id in (".$_REQUEST['lid'].")";
        $deal_attrs = $GLOBALS['db']->getAll($sql);
        echo json_encode($deal_attrs);die;
        // $current_wx_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        //es_cookie::set("current_wx_url",$current_wx_url);


    }

    public function test(){
        global_run();
        $sql = "select * from ".DB_PREFIX."dc_rs_item_time order by id desc limit 10";
        $cart = $GLOBALS['db']->getAll($sql);
//        echo json_encode($sql2);
        echo json_encode($cart);
        die;
    }

    public function testCart(){
        global_run();
        $sql = "select * from ".DB_PREFIX."dc_order order by id desc limit 10";
        $cart = $GLOBALS['db']->getAll($sql);
//        echo json_encode($sql2);
        echo json_encode($cart);
        die;
    }


    function output($a,$b,$msg){
     return $msg;
    }
}
?>