<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class adsAction extends CommonAction{
	public $page = 1;
	
	private $RELATE_GOODS_NUM = 6;//可以关联商品的个数
	
	public function __construct(){
		parent::__construct();
		
		if(isset($_REQUEST['page'])){
			$this->page = max(1,(int)$_REQUEST['page']);
		}
		if(isset($_REQUEST['isajax'])){
			$this->isajax = (int)$_REQUEST['isajax'] > 0 ? 1 : 0;
		}
		if(isset($_REQUEST['page'])){
			$this->page = max(1,(int)$_REQUEST['page']);
		}
		$this->assign('pager_num_now',$this->page);
	}
	
	public function index()
	{
		
		
		
		$type = $_REQUEST['type'];		
		$name = $_REQUEST['name'];		
		$location_name = $_REQUEST['location_name'];		
		$sqlstr="where 1=1";
		
		if($location_name){
		$sqlstr.=" and (b.name like '%".$location_name."%' or a.slid='$location_name')";
		}
			
		if($type){
		$sqlstr.=" and a.type='$type' ";	
		}
		if($name){
		$sqlstr.=" and a.name like '%".$name."%'";	
		}
		
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = 20;
		$limit = (($page_idx-1)*$page_size).",".$page_size;	
	
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='location_name'){
			$orderby = "order by a.slid ".$sort;	
			}else{
			$orderby = "order by a.".$order." ".$sort;
			}
		}else
		{
			$orderby = "";
		}
		
		
		
		$sql  ="select a.*,b.name as location_name from ".DB_PREFIX."ads a left join ".DB_PREFIX."supplier_location b on a.slid=b.id $sqlstr $orderby limit ".$limit;;
		//echo $sql;		
		$tsql  ="select count(a.id) from ".DB_PREFIX."ads a left join ".DB_PREFIX."supplier_location b on a.slid=b.id $sqlstr $orderby ";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		
		foreach ($list as $k=>$v){
			if($v["slid"]==0){
			$list[$k]["location_name"]='平台';
			}
		}
	
		$p = new Page ( $total, $page_size );
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		//查询当月报表
				
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 
		
		$main_title='WPOS广告管理';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	
	public function ads_hall()
	{
		
		$ads_name = $_REQUEST['ads_name'];		
		$name = $_REQUEST['name'];		
		$location_name = $_REQUEST['location_name'];		
		$sqlstr="where 1=1";
		
		if($location_name){
		$sqlstr.=" and (b.name like '%".$location_name."%' or a.slid='$location_name')";
		}
			
		if($ads_name){
		$sqlstr.=" and a.ads_name='$ads_name' ";	
		}
		if($name){
		$sqlstr.=" and a.name like '%".$name."%'";	
		}
		
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = 20;
		$limit = (($page_idx-1)*$page_size).",".$page_size;	
	
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='location_name'){
			$orderby = "order by a.slid ".$sort;	
			}else{
			$orderby = "order by a.".$order." ".$sort;
			}
		}else
		{
			$orderby = "";
		}
		
		$ads_list=json_decode(ADSLIST,true); //解析广告方式
		foreach($ads_list as $ke=>$va){
			
			if ($ads_name==$ke){
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va);	
			}			
		   }
		$this->assign ( 'ads_list_show', $ads_list_show );
		
		$sql  ="select a.*,b.name as location_name from ".DB_PREFIX."ads_hall a left join ".DB_PREFIX."supplier_location b on a.slid=b.id $sqlstr $orderby limit ".$limit;;
		//echo $sql;		
		$tsql  ="select count(a.id) from ".DB_PREFIX."ads_hall a left join ".DB_PREFIX."supplier_location b on a.slid=b.id $sqlstr $orderby ";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		/*
		foreach ($list as $k=>$v){
			if($v["slid"]==0){
			$list[$k]["location_name"]='平台';
			}
		}
		*/
		foreach($list as $key => $val)
		{
			$list[$key]['avg']=floatval($val['avg']);		
	        $list[$key]['show_ads_name']=$ads_list[$val['ads_name']];	
            if ($val['danwei']=='1'){
            $list[$key]['show_danwei'] = '天';			
			}else{
			$list[$key]['show_danwei'] = '次';   			
			}
			if ($val['islocked']=='1'){
            $list[$key]['islocked_show'] = '已售';			
			}else{
			$list[$key]['islocked_show'] = '待售';   			
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
		
		
	
		$p = new Page ( $total, $page_size );
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		//查询当月报表
				
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 
		
		$main_title='交易大厅';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	
	public function ads_trade()
	{
		
		$ads_name = $_REQUEST['ads_name'];		
		$name = $_REQUEST['name'];		
		$seller_slid = $_REQUEST['seller_slid'];		
		$buyer_slid = $_REQUEST['buyer_slid'];		
		$sqlstr="where 1=1";
		
		if($seller_slid){
		$sqlstr.=" and a.seller_slid=".$seller_slid;
		}
		if($buyer_slid){
		$sqlstr.=" and a.buyer_slid=".$buyer_slid;
		}
			
		if($ads_name){
		$sqlstr.=" and a.ads_name='$ads_name' ";	
		}
		if($name){
		$sqlstr.=" and a.name like '%".$name."%'";	
		}
		
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = 20;
		$limit = (($page_idx-1)*$page_size).",".$page_size;	
	
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		$order=$_REQUEST ['_order'];
		if(isset($order))
		{   
	        if ($order=='location_name'){
			$orderby = "order by a.slid ".$sort;	
			}else{
			$orderby = "order by a.".$order." ".$sort;
			}
		}else
		{
			$orderby = "";
		}
		
		$ads_list=json_decode(ADSLIST,true); //解析广告方式
		foreach($ads_list as $ke=>$va){
			
			if ($ads_name==$ke){
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$ads_list_show[]=array('typ'=>$ke,'name'=>$va);	
			}			
		   }
		$this->assign ( 'ads_list_show', $ads_list_show );
		
		$sql="SELECT a.* FROM `fanwe_ads_order` a  $sqlstr $orderby LIMIT " . $limit;
		$tsql="SELECT count(id) FROM `fanwe_ads_order` a  $sqlstr $orderby";
		
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		/*
		foreach ($list as $k=>$v){
			if($v["slid"]==0){
			$list[$k]["location_name"]='平台';
			}
		}
		*/
		foreach($list as $key => $val)
		{
			$list[$key]['show_ads_name']=$ads_list[$val['ads_name']];	
            if ($val['danwei']=='1'){
            $list[$key]['show_danwei'] = '天';			
			}else{
			$list[$key]['show_danwei'] = '次';   			
			}
			if ($val['is_effect']=='1'){
            $list[$key]['status'] = '已启用';			
			}else{
			$list[$key]['status'] = '未启用';   			
			}
			if ($val['islocked']=='1'){
            $list[$key]['islocked_show'] = '已结束';			
			}else{
			$list[$key]['islocked_show'] = '正在进行';   			
			}
			
			
			
		
		}
		
		
	
		$p = new Page ( $total, $page_size );
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		//查询当月报表
				
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 
		
		$main_title='交易管理';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	
	public function update()

	{

		$id = intval($_REQUEST['charge_id']);
        $_REQUEST['Uptime']=to_date(NOW_TIME);
		if($id>0){
        $GLOBALS['db']->autoExecute(DB_PREFIX."ads",$_REQUEST,"update","id=".$id);		
		$this->success("更新成功");			
		}else{
		$GLOBALS['db']->autoExecute(DB_PREFIX."ads",$_REQUEST);			
        $this->success("添加成功");	
		}

	}
	
	public function edit()

	{
		$ads_list=json_decode(ADSLIST,true); //解析支付方式
		
	
		
		$id = intval($_REQUEST['id']);
		
		if ($id>0){

				$charge_info = M("ads")->getById($id);
						
               
				$this->assign("type",1);				

				$this->assign("charge_info",$charge_info);				
				
				
		}else{
		$charge_info['Uptime']=to_date(NOW_TIME);
		}	
		
        foreach($ads_list as $ke=>$va){		
			
			if ($charge_info['ads_name']==$ke){
			$list[]=array('typ'=>$ke,'name'=>$va,'select'=>'selected="selected"');	
			}else{
			$list[]=array('typ'=>$ke,'name'=>$va);	
			}
		}		
		$this->assign("charge_info",$charge_info);
		$this->assign("list",$list);
               
		$this->display();

	}
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$list=M("ads")->where($condition)->delete();
				if ($list!==false) {
				$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
				$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
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
		
		
		
		$type=ereg_replace('^[[:alnum:]]([-_.]?[[:alnum:]])*\.','.',$picname); 
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
}
?>