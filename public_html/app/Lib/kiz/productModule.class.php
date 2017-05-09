<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class productModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();

//        $this->check_auth();
    }
    #生产
    public function product_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "生产模板");
        $GLOBALS['tmpl']->display("pages/product/index.html");
    }
    public function product_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("reason2",  json_encode(parent::get_basic_reason_list(0,2)));
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "新增生产模板");
        $GLOBALS['tmpl']->display("pages/product/add.html");
    }
    #入库单
    public function product_inventory_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "生产单");
        $GLOBALS['tmpl']->display("pages/product/inventory.html");
    }
    public function product_inventory_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增生产单");
        $GLOBALS['tmpl']->display("pages/product/inventoryAdd.html");
    }

}

?>