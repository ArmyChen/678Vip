<?php
require_once 'core/pinyin.php';
require_once 'core/page.php';

class ajaxModule extends TizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();
    }

    public function tiz_root()
    {
        $sql = "DROP TABLE IF EXISTS `tiz_root`;CREATE TABLE `tiz_root` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `start_city` varchar(255) DEFAULT NULL,
  `start_city_code` varchar(255) DEFAULT NULL,
  `end_city` varchar(255) DEFAULT NULL,
  `ent_city_code` varchar(255) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `type_name` varchar(255) DEFAULT NULL,
  `nature_id` int(11) DEFAULT NULL,
  `nature_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `other_name` varchar(255) DEFAULT NULL,
  `root_code` varchar(255) DEFAULT NULL,
  `backway` varchar(255) DEFAULT NULL,
  `root_theme` varchar(255) DEFAULT NULL,
  `check_type` int(11) DEFAULT NULL,
  `description` text,
  `need_desc` text,
  `tip_desc` text,
  `fee_desc` text,
  `no_fee_desc` text,
  `safe_desc` text,
  `product_desc` text,
  `content_desc` text,
  `is_refund` tinyint(4) DEFAULT NULL,
  `detail_rule` int(11) DEFAULT NULL,
  `created` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        var_dump($GLOBALS['db']->query($sql));
    }


    public function tiz_root_date()
    {
        $sql = "DROP TABLE IF EXISTS `tiz_root_date`;
CREATE TABLE `tiz_root_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tiz_id` int(11) DEFAULT NULL,
  `start_time` varchar(255) DEFAULT NULL,
  `end_time` varchar(255) DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `msg` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        var_dump($GLOBALS['db']->query($sql));
    }


    public function root_add_ajax()
    {
        $id = $_REQUEST['id'];
        //校验
        if($_REQUEST["name"] == ""){
            $return['success'] = false;
            $return['message'] = '线路名称不能为空';
            echo json_encode($return);
            die;
        }

        if ($id > 0) {
            $res = $GLOBALS['db']->getAll("select * from tiz_root where id=".$id);
            $data = array(
                "name" => $_REQUEST["name"],
                "subtitle" => $_REQUEST["subtitle"],
                "start_city" => $_REQUEST["start_city"],
                "start_city_code" => $_REQUEST["start_city_code"],
                "end_city" => $_REQUEST["end_city"],
                "ent_city_code" => $_REQUEST["ent_city_code"],
                "type_id" => $_REQUEST["type_id"],
                "type_name" => $_REQUEST["type_name"],
                "nature_id" => $_REQUEST["nature_id"],
                "nature_name" => $_REQUEST["nature_name"],
                "price" => $_REQUEST["price"],
                "other_name" => $_REQUEST["other_name"],
                "root_code" => $_REQUEST["root_code"],
                "backway" => $_REQUEST["backway"],
                "root_theme" => $_REQUEST["root_theme"],
                "check_type" => $_REQUEST["check_type"],
                "description" => $_REQUEST["description"],
                "need_desc" => $_REQUEST["need_desc"],
                "tip_desc" => $_REQUEST["tip_desc"],
                "fee_desc" => $_REQUEST["fee_desc"],
                "no_fee_desc" => $_REQUEST["no_fee_desc"],
                "safe_desc" => $_REQUEST["safe_desc"],
                "product_desc" => $_REQUEST["product_desc"],
                "content_desc" => $_REQUEST["content_desc"],
                "is_refund" => $_REQUEST["is_refund"],
                "detail_rule" => $_REQUEST["detail_rule"],
                "created" => time()
            );

            $res = $GLOBALS['db']->autoExecute("tiz_root",$data,"update","id=".$id);

        } else {
            $data = array(
                "name" => $_REQUEST["name"],
                "subtitle" => $_REQUEST["subtitle"],
                "start_city" => $_REQUEST["start_city"],
                "start_city_code" => $_REQUEST["start_city_code"],
                "end_city" => $_REQUEST["end_city"],
                "ent_city_code" => $_REQUEST["ent_city_code"],
                "type_id" => $_REQUEST["type_id"],
                "type_name" => $_REQUEST["type_name"],
                "nature_id" => $_REQUEST["nature_id"],
                "nature_name" => $_REQUEST["nature_name"],
                "price" => $_REQUEST["price"],
                "other_name" => $_REQUEST["other_name"],
                "root_code" => $_REQUEST["root_code"],
                "backway" => $_REQUEST["backway"],
                "root_theme" => $_REQUEST["root_theme"],
                "check_type" => $_REQUEST["check_type"],
                "description" => $_REQUEST["description"],
                "need_desc" => $_REQUEST["need_desc"],
                "tip_desc" => $_REQUEST["tip_desc"],
                "fee_desc" => $_REQUEST["fee_desc"],
                "no_fee_desc" => $_REQUEST["no_fee_desc"],
                "safe_desc" => $_REQUEST["safe_desc"],
                "product_desc" => $_REQUEST["product_desc"],
                "content_desc" => $_REQUEST["content_desc"],
                "is_refund" => $_REQUEST["is_refund"],
                "detail_rule" => $_REQUEST["detail_rule"],
                "created" => time()
            );
//var_dump($data);die;
            $res = $GLOBALS['db']->autoExecute("tiz_root",$data);

        }

        if ($res) {//成功
            $return['success'] = true;
            $return['message'] = '操作成功';
        } else {
            $return['success'] = false;
            $return['message'] = '操作失败';
        }

        echo json_encode($return);
    }
}