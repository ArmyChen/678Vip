<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 17:20
 */
require_once APP_ROOT_PATH."system/model/dc.php";

class apiModule
{

    /**
     * ajaxModule constructor.
     */
    public function __construct()
    {
        $this->checkToken();
        global_run();
    }

    private function checkToken(){
        $token = $_REQUEST['token'];
        if(empty($token)){
            $data['success']  = false;
            $data['message']  = "需要进行验证才能查看";
            echo json_encode($data);
            exit;
        }else{
            if($token != md5("678sh.com")){
                $data['success']  = false;
                $data['message']  = "密钥错误，请联系管理员";
                echo json_encode($data);
                exit;
            }
        }
    }
    public function get_chuan_info(){
        $mid = $_REQUEST['mid'];
        $sql = "select * from fanwe_goods_extends where  mid=" . $mid;
        $records = $GLOBALS['db']->getRow($sql);
        try{
            $chuan = unserialize($records['chuan']);
        }catch (Exception $e){
            $chuan = [];
        }
        $data = [];

        if(!empty($chuan)){
            foreach ($chuan as $k => $v) {
                $data[$k]['chuan'] = $v['chuan'];
                $data[$k]['isdisable'] = $v['isdisable'];
            }
        }

        echo json_encode($data);
        exit;
    }
}