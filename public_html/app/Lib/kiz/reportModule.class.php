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

    #商品月出入库汇总明细表
    public function report_stock_detail_index()
    {
        init_app_page();


        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("listsort", parent::goods_category_tree_ajax());
        $GLOBALS['tmpl']->assign("page_title", "商品月出入库汇总明细表");
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
        $GLOBALS['tmpl']->assign("page_title", "盘点盈亏表");
        $GLOBALS['tmpl']->display("pages/report/stockDiff.html");
    }
    #调拨差异分析表
    public function report_allocation_difference_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "调拨差异分析表");
        $GLOBALS['tmpl']->display("pages/report/allocationDifference.html");
    }

    #库存分布明细表
    public function report_stock_dubbo_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("listsort", parent::goods_category_tree_ajax());
        $GLOBALS['tmpl']->assign("page_title", "库存分布明细表");
        $GLOBALS['tmpl']->display("pages/report/stockDubboSearch.html");
    }

    #商品生产分析表
    public function report_product_analysis_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品生产分析表");
        $GLOBALS['tmpl']->display("pages/report/productAnalysis.html");
    }

    #退回报废分析表
    public function report_return_and_scrap_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "退回报废分析表");
        $GLOBALS['tmpl']->display("pages/report/returnScrap.html");
    }

    #配送差异分析表
    public function report_delivery_difference_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "配送差异分析表");
        $GLOBALS['tmpl']->display("pages/report/deliveryDifference.html");
    }

    #商品出入库原因分析表
    public function report_ioreason_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品出入库原因分析表");
        $GLOBALS['tmpl']->display("pages/report/ioreason.html");
    }

    #采购分析表
    public function report_purchase_analysis_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "采购分析表");
        $GLOBALS['tmpl']->display("pages/report/purchaseAnalysis.html");
    }

    #采购明细表
    public function report_purchase_detail_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "采购明细表");
        $GLOBALS['tmpl']->display("pages/report/purchaseDetail.html");
    }

    #配方估算成本分析表
    public function report_cost_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "配方估算成本分析表");
        $GLOBALS['tmpl']->display("pages/report/cost.html");
    }

    #商品销售成本分析表
    public function report_selling_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品销售成本分析表");
        $GLOBALS['tmpl']->display("pages/report/selling.html");
    }

    #销售明细表
    public function report_sale_detail_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "销售明细表");
        $GLOBALS['tmpl']->display("pages/report/saleDetail.html");
    }

}

?>