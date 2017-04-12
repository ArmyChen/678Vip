<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class supplierModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();

//        $this->check_auth();
    }
    #供应商列表
    public function supplier_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "供应商列表");
        $GLOBALS['tmpl']->display("pages/supplier/index.html");
    }
    public function supplier_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增供应商");
        $GLOBALS['tmpl']->display("pages/supplier/indexAdd.html");
    }
    #商品需求汇总单
    public function supplier_aggregate_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品需求汇总单");
        $GLOBALS['tmpl']->display("pages/supplier/aggregate.html");
    }

    public function supplier_aggregate_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增商品需求汇总单");
        $GLOBALS['tmpl']->display("pages/supplier/aggregateAdd.html");
    }

}

?>