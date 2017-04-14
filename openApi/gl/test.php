<?php
require_once 'hy_tool.class.php';
$name = array (
		"spy-20170414100644-6159"
);
$type = "cmd";
$content = '{"title":"\u63d0\u793a","description":"\u6709\u65b0\u8ba2\u5355","custom_content":{"code":1009,"data":{"psid":450}}}';

$ht = new HyTool ();
$error = $ht->sendMessage ( $name, $type,$content );
if (isset ( $error )) {
	echo $error;
} else {
	echo "成功";
}
?>