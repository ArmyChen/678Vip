<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class salesmanAction extends CommonAction{
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
		
		$kewwords = $_REQUEST['location_name'];		
		$sqlstr="where 1=1";		
		if($kewwords){
		$sqlstr.=" and (a.name like '%".$kewwords."%' or a.tel='$kewwords')";
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
	        if($order=='city_name'){
			 $orderby = "order by a.city_id ".$sort;	
			}else{
			 $orderby = "order by a.".$order." ".$sort;	
			}
	       
	
		}else
		{
			$orderby = "";
		}
		
		
		
		$sql  ="select a.*,b.name as city_name from ".DB_PREFIX."salesman a left join ".DB_PREFIX."deal_city b on a.city_id=b.id $sqlstr $orderby limit ".$limit;;
		$tsql  ="select count(*) from ".DB_PREFIX."salesman $sqlstr $orderby";

		
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
		
		$main_title='业务员管理';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	
	
	public function update()

	{

		$id = intval($_REQUEST['charge_id']);
        $_REQUEST['Uptime']=to_date(NOW_TIME);
		if($id>0){
		 if($_REQUEST['password']==""){
		  unset($_REQUEST['password']);
		 }else{
		  $_REQUEST['password']=md5(trim($_REQUEST['password']));//去空格MD5加密
		 }
        $GLOBALS['db']->autoExecute(DB_PREFIX."salesman",$_REQUEST,"update","id=".$id);		
		$this->success("更新成功");			
		}else{
		$check=M("salesman")->where("tel='".$_REQUEST['tel']."'")->find();
		if($check){
		$this->error (l("手机号码已经存在"),$ajax);
		}else{
		$_REQUEST['password']=md5(trim($_REQUEST['password']));//去空格MD5加密	
		$GLOBALS['db']->autoExecute(DB_PREFIX."salesman",$_REQUEST);			
        $this->success("添加成功");	
		}
		}

	}
	
	
	public function edit()

	{
	
		$id = intval($_REQUEST['id']);
		
		if ($id>0){

				$charge_info = M("salesman")->getById($id);
						
               
				$this->assign("type",1);				

				$this->assign("charge_info",$charge_info);				
				
				
		}else{
		$charge_info['Uptime']=to_date(NOW_TIME);
		}
        
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);			
		$this->assign("charge_info",$charge_info);               
		$this->display();

	}
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$list=M("salesman")->where($condition)->delete();
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
		unlink('salesman/'.$filename);
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
		$pic_path = "salesman/". $pics;
		
		
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