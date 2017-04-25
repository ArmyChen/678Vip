<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class reportModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();
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

        $kcnx=array(
            "0"=>"暂无",
            "1"=>"现制商品",
            "2"=>"预制商品",
            "3"=>"外购商品",
            "4"=>"原物料",
            "6"=>"半成品",

        );
        $this->kcnx=$kcnx;
//        $this->check_auth();
    }
    #库存查询
    public function report_stock_index()
    {
        init_app_page();
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", parent::goods_category_tree_ajax());
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);


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
        $slid=$GLOBALS['account_info']['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign('bumen',parent::get_bumen_list($slid));
        $GLOBALS['tmpl']->assign("gonghuoren", parent::get_bumen_list($slid));
        $GLOBALS['tmpl']->assign("gys", parent::get_gys_list($slid));
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("listsort", parent::goods_category_tree_ajax());
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
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("listsort", parent::goods_category_tree_ajax());
        $GLOBALS['tmpl']->assign("kcnx",  $this->kcnx);
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