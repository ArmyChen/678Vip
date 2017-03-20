<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class apkAction extends CommonAction{

	public function index()
	{
		
		$type = intval($_REQUEST['type']);
		if($type!=1&&$type!=2&&$type!=3)
			$type = 1;
		
		$this->assign("type",$type);
		$balance_title = "微POS版本管理";
		if($type==2)
			$balance_title = "安卓手机APP版本管理";
		if($type==3)
			$balance_title = "苹果手机APP版本管理";
		
				
		$map['type'] = $type;

         $model = M ("apk");
         $locallog=$model->where($map)->order('id desc')->select(); 
		
		 $this->assign("list",$locallog);
				
		$this->assign("balance_title",$balance_title);
		
		
		$this->display ();
		
		return;
	}
	
	
	public function foreverdelete() {
		$id = intval($_REQUEST['id']);	
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."apk where id=".$id);
	
		$this->error("清空成功");
		
	}
	
	/**
	 * 结算报表
	 * 针对商户的报表查看
	 */
	public function bill()
	{
		
					
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = 10;
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		$ex_condition="where 1=1 ";			
		$type = $_REQUEST['type'];
		if($type)
			$ex_condition .= " and a.type = '".$type."' ";
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		if(isset($order))
		{
			$orderby = "order by ".$order." ".$sort;
		}else
		{
			$orderby = "";
		}
		

		$list= $GLOBALS['db']->getAll("select a.*,b.name from ".DB_PREFIX."app a left join ".DB_PREFIX."supplier_location b on a.slid=b.id $ex_condition  $orderby limit ".$limit);
	//	echo ("select a.*,b.name from ".DB_PREFIX."app left join ".DB_PREFIX."supplier_location b on a.slid=b.id $ex_condition  $orderby limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(appid) from ".DB_PREFIX."app a $ex_condition");
		
	//	var_dump($list);
		$p = new Page ( $total, $page_size);
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
	    
		

	
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 

		
		$this->display ();
		return;
	}
	
	public function edit()

	{
		$id = intval($_REQUEST['id']);
		
		if ($id>0){

				$charge_info = M("apk")->getById($id);
						
               
				$this->assign("type",1);				

				$this->assign("charge_info",$charge_info);
				if ($charge_info['type']==1){
				$charge_info['showname']='微POS安卓版：'.$charge_info['version'];	
				}
				if ($charge_info['type']==2){
				$charge_info['showname']='安卓手机APP：'.$charge_info['version'];	
				}
				if ($charge_info['type']==3){
				$charge_info['showname']='苹果手机APP：'.$charge_info['version'];	
				}
		}else{
		$charge_info['fbdate']=to_date(NOW_TIME);
		}		
		$this->assign("charge_info",$charge_info);
               
		$this->display();

	}

   public function update()

	{

		$id = intval($_REQUEST['charge_id']);

		if($id>0){
        $GLOBALS['db']->autoExecute(DB_PREFIX."apk",$_REQUEST,"update","id=".$id);		
		$this->success("更新成功");			
		}else{
		$GLOBALS['db']->autoExecute(DB_PREFIX."apk",$_REQUEST);			
        $this->success("添加成功");	
		}

	}
	
	
	public function img(){
		$action = $_GET['act'];
		if($action=='delimg'){
		$filename = $_POST['imagename'];
		if(!empty($filename)){
		unlink('apk/'.$filename);
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
		$type = strstr($picname, '.');
		if ($type != ".apk" && $type != ".Apk" && $type != ".APK") {
			echo '图片格式不对！';
			exit;
		}
		$rand = rand(100, 999);
		$pics = date("YmdHis") . $rand . $type;
		//上传路径
		$pic_path = "apk/". $pics;
		
		
		move_uploaded_file($_FILES['mypic']['tmp_name'], $pic_path);
		}
		include('apkversion.php');
		$appObj  = new Apkparser(); 
		$res   = $appObj->open('apk/'.$pics); //读取 APK包	
		$apkversion=$appObj->getVersionName();  // 版本名称	
		$apkcode=$appObj->getVersionCode();  // 版本代码
		$size = round($picsize/1024,2);
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