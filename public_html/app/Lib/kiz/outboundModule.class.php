<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class outboundModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();

//        $this->check_auth();
    }
    #报废单
    public function outbound_scrap_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "报废单");
        $GLOBALS['tmpl']->display("pages/outbound/scrap.html");
    }
    public function outbound_scrap_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("reason2",  json_encode(parent::get_basic_reason_list(0,2)));
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "新增报废单");
        $GLOBALS['tmpl']->display("pages/outbound/scrapAdd.html");
    }
    public function outbound_scrap_edit()
    {
        init_app_page();
        $id=$_REQUEST['id'];
        $sql = "select * from fanwe_cangku_outbound_stat where djid=".$id;
        $detail = $GLOBALS['db']->getAll($sql);
        $datailinfo = array();
        foreach($detail as $k=>$v){
            $datailinfo[$k]['id'] = $v['mid'];//24733
            $datailinfo[$k]['skuId'] = $v['mid'];
            $datailinfo[$k]['skuTypeId'] = $v['cate_id'];
            $datailinfo[$k]['skuTypeName'] = empty($this->get_dc_supplier_menu($v['cate_id']))?"":$this->get_dc_supplier_menu($v['cate_id'])['name'];
            $datailinfo[$k]['skuCode'] = $v['mbarcode'];
            $datailinfo[$k]['skuName'] = $v['mname'];
            $datailinfo[$k]['uom'] = $v['unit'];
            $datailinfo[$k]['price'] = $v['mprice'];
            $datailinfo[$k]['actualQty'] = $v['num'];
            $datailinfo[$k]['amount'] = $v['mprice']* $v['out_num'];
            $datailinfo[$k]['standardInventoryQty'] = $v['out_num'];
            $datailinfo[$k]['inventoryQty'] = $v['out_num'];

        }

        $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
        $GLOBALS['tmpl']->assign("result", $datailinfo);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("reason2",  json_encode(parent::get_basic_reason_list(0,2)));
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "新增报废单");
        $GLOBALS['tmpl']->display("pages/outbound/scrapEdit.html");
    }
    public function outbound_scrap_view()
    {
        init_app_page();
        $id=$_REQUEST['id'];
        $sql = "select * from fanwe_cangku_outbound where id =".$id;
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("reason2",  json_encode(parent::get_basic_reason_list(0,2)));
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "新增报废单");
        $GLOBALS['tmpl']->display("pages/outbound/scrapView.html");
    }
    #退回入库单
    public function outbound_backstorage_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "退回入库单");
        $GLOBALS['tmpl']->display("pages/outbound/backStorage.html");
    }

}

?>