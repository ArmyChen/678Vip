<?php
echo str_replace('mapi/Lib/hy/index.php', '', str_replace('\\', '/', __FILE__))."system/libs/hy_tool.class.php";
require_once str_replace('mapi/Lib/hy/index.php', '', str_replace('\\', '/', __FILE__))."system/libs/hy_tool.class.php";

$ht = new HyTool ();
echo var_dump ( $ht->getToken () );
echo var_dump ( $ht->regist ( "2113", "123456" ) );
// $url = "http://a2.easemob.com/1174161029115536/wsh/token";
// $data = array (
// 'grant_type' => 'client_credentials',

// 'client_secret' => 'YXA61JeZY9FuZ7HfXseot5JYX49jTao',

// 'client_id' => 'YXA64a0GUJ17EeaIc7mMvq6GIg'
// )
// ;
// $php_json = json_encode ( $data );
// echo $a->post1 ( $url, $php_json );
// echo "\n";
// echo $a->post ( "/token", $data );

?>