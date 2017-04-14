<?php
header("Content-Type: text/json;charset=utf-8");
include("../web/ljg.php");
   
	require_once 'check5.php';	
	$datajsonstr=$GLOBALS['HTTP_RAW_POST_DATA'];
	if ($datajsonstr==''){
	$datajsonstr=$_POST['json'];
	}
	$data=json_decode($datajsonstr,true);//把接受到的 json 变成数组。	

    $poststr="account_name,sign,call_num,detail_id";
	check_item($data,$poststr); //检查字段 	
	$Checkin=checkMac($data['account_name'],$data['sign']);		
	
	if ($Checkin){
	
	 $updata=array();	 
	 $updata['call_num']='call_num+'.intval($data['call_num']);
	 $res=updatesql($updata,"ims_tiny_wmall_plus_assign_board","where id=".$data['detail_id']);
     if($res){
	 echo '{"status":"success","msg":"成功"}'; 
	 }else{
	  echo '{"status":"fail","result":"fail","msg":"detail_id号错误"}'; 
	 }
	 
   }else{
	echo '{"status":"fail","result":"fail"}';
   }
	

	
?>