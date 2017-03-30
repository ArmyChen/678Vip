<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class reportModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();

//        $this->check_auth();
    }
    #库存查询
    public function report_stock_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "库存查询");
        $GLOBALS['tmpl']->display("pages/report/stockSearch.html");
    }

    #库存分布明细表
    public function report_stock_detail_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "库存分布明细表");
        $GLOBALS['tmpl']->display("pages/report/stockDetailSearch.html");
    }

    #出入库明细
    public function report_stock_dubbo_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品出入库明细表");
        $GLOBALS['tmpl']->display("pages/report/stockDubboSearch.html");
    }


}

?>