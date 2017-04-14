<?php
require_once 'hy_tool.class.php';
$name = array (
		"spy-20170414100644-6159"
);
$type = "cmd";
$description=array('code'=>'1001');
$message = array (
    'title' => '提示',
    'description' =>'有菜品更新' ,
    'custom_content'=>$description
);

$ht = new HyTool ();
$error = $ht->sendMessage_NEW ( $name, $type,$message);
var_dump($error);
if (isset ( $error )) {
	echo $error;
} else {
	echo "成功";
}
?>