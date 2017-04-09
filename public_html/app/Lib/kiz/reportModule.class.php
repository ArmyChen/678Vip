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

    #库存预警表
    public function report_stock_warning_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "库存预警表");
        $GLOBALS['tmpl']->display("pages/report/stockWarning.html");
    }

    #库存交易凭证
    public function report_stock_trade_cert_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "库存交易凭证（日志）");
        $GLOBALS['tmpl']->display("pages/report/stockTradeCert.html");
    }
    #盘点盈亏表导出
    public function report_stock_diff_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "盘点盈亏表导出");
        $GLOBALS['tmpl']->display("pages/report/stockDiff.html");
    }
    #调拨差异分析表导出
    public function report_allocation_difference_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "调拨差异分析表导出");
        $GLOBALS['tmpl']->display("pages/report/allocationDifference.html");
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