<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class dishModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();

        global_run();

        parent::init();
//        $this->check_auth();
    }
    #供应商列表
    public function dish_cookingway()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "做法管理");
        $GLOBALS['tmpl']->display("pages/dish/cookingway.html");
    }

    public function dish_unit()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "单位管理");
        $GLOBALS['tmpl']->display("pages/dish/unit.html");
    }

    public function dish_category()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品类别");
        $GLOBALS['tmpl']->display("pages/dish/category.html");
    }

    public function dish_list()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品管理");
        $GLOBALS['tmpl']->display("pages/dish/list.html");
    }
}

?>