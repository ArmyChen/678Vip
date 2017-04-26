<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class countModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();

        global_run();
        $ywsort=array(
            "-5"=>"生产退料",
            "-4"=>"退还入库",
            "-3"=>"预配退货",
//            "-2"=>"其他入库",
//            "-1"=>"盘盈",
            "1"=>"盘盈",
            "2"=>"无订单入库",
            "3"=>"要货调入",
            "4"=>"初始库存",
            "6"=>"盘亏",
            "7"=>"无订单出库",
            "8"=>"要货调出",
            "9"=>"退货",
            "10"=>"生产领料",
            "11"=>"借用出库",
            "12"=>"其他出库",
            "13"=>"配送领料",
            "14"=>"品牌销售出库",

        );
        $this->ywsort=$ywsort;
        $this->gonghuoren=array(
            "1"=>"临时客户",
            "2"=>"临时运输商",
            "3"=>"临时供应商",
            "4"=>"领料出库"
        );

        parent::init();
//        $this->check_auth();
    }
    #盘点管理设定
    public function count_setting_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "盘点管理设定");
        $GLOBALS['tmpl']->display("pages/count/countSetting.html");
    }
    #盘点模板列表
    public function count_stock_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "盘点模板");
        $GLOBALS['tmpl']->display("pages/count/countStock.html");
    }
    public function count_stock_add()
    {
        init_app_page();
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增盘点模板");
        $GLOBALS['tmpl']->display("pages/count/countStockAdd.html");
    }
    public function count_stock_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑盘点模板");
        $GLOBALS['tmpl']->display("pages/count/countStockEdit.html");
    }
    #商品需求汇总单
    public function count_task_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "盘点单");
        $GLOBALS['tmpl']->display("pages/count/countTask.html");
    }

    public function count_task_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增盘点单");
        $GLOBALS['tmpl']->display("pages/count/countTaskAdd.html");
    }
}

?>