<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class productModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        $ywsort=array(
            "-6"=>"生产入库",
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
            "15"=>"直拨出入库"
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
    #生产
    public function product_index()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
//        var_dump($account_info);die;
        $supplier_id = $account_info['supplier_id'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';

        if ((isset($_REQUEST['begin_time']))|| (isset($_REQUEST['end_time']))){
            $begin_time = strim($_REQUEST['begin_time']);
            $end_time = strim($_REQUEST['end_time']);
        }else{	 //默认为当月的
            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("type", $type);
        $GLOBALS['tmpl']->assign("begin_time", $begin_time);
        $GLOBALS['tmpl']->assign("end_time", $end_time);
        $GLOBALS['tmpl']->assign("slid", $location_id);
        $GLOBALS['tmpl']->assign("productlist", parent::get_product_template_list());
        $GLOBALS['tmpl']->assign("danjuhao", $_REQUEST['danjuhao']);
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
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("productlist", parent::get_product_template_list());

        $GLOBALS['tmpl']->assign("page_title", "生产单");
        $GLOBALS['tmpl']->display("pages/product/inventory.html");
    }
    public function product_inventory_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("productlist", parent::get_product_template_list());
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "生产单");
        $GLOBALS['tmpl']->display("pages/product/inventoryAdd.html");
    }

    /**
     * 生产单编辑功能
     */
    public function basic_product_index_edit()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $account_info['slid'];
        /*获取入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_log where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);
//var_dump($result);
            $datailinfo = array();
            $detail = unserialize($result['dd_detail']);
            foreach($detail as $k=>$v){
                $datailinfo[$k]['id'] = $v['mid'];//24733
                $datailinfo[$k]['skuId'] = $v['mid'];
                $datailinfo[$k]['skuTypeId'] = $v['cate_id'];
                $datailinfo[$k]['skuTypeName'] = empty($this->get_dc_supplier_menu($v['cate_id']))?"":$this->get_dc_supplier_menu($v['cate_id'])['name'];
                $datailinfo[$k]['skuCode'] = $v['barcode'];
                $datailinfo[$k]['skuName'] = $v['name'];
                $datailinfo[$k]['uom'] = $v['unit'];
                $datailinfo[$k]['price'] = $v['price'];
                $datailinfo[$k]['actualQty'] = $v['num'];
                $datailinfo[$k]['amount'] = $v['price']* $v['num'];
                $datailinfo[$k]['standardInventoryQty'] = $v['ssnum'];
                $datailinfo[$k]['inventoryQty'] = $v['num'];

            }
//            var_dump($datailinfo);
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "入库单");
            $GLOBALS['tmpl']->display("pages/inventory/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("result", $result);
        $GLOBALS['tmpl']->assign("productlist", parent::get_product_template_list());
        $GLOBALS['tmpl']->assign("page_title", "生产入库单");
        $GLOBALS['tmpl']->display("pages/product/inventoryEdit.html");

    }

    /**
     * 生产单编辑功能
     */
    public function basic_product_index_view()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $account_info['slid'];
        /*获取入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_log where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);

            $datailinfo = array();
            $detail = unserialize($result['dd_detail']);
            foreach($detail as $k=>$v){
                $datailinfo[$k]['id'] = $v['mid'];//24733
                $datailinfo[$k]['skuId'] = $v['mid'];
                $datailinfo[$k]['skuTypeId'] = $v['cate_id'];
                $datailinfo[$k]['skuTypeName'] = empty($this->get_dc_supplier_menu($v['cate_id']))?"":$this->get_dc_supplier_menu($v['cate_id'])['name'];
                $datailinfo[$k]['skuCode'] = $v['barcode'];
                $datailinfo[$k]['skuName'] = $v['name'];
                $datailinfo[$k]['uom'] = $v['unit'];
                $datailinfo[$k]['price'] = $v['price'];
                $datailinfo[$k]['actualQty'] = $v['num'];
                $datailinfo[$k]['amount'] = $v['price']* $v['num'];
                $datailinfo[$k]['standardInventoryQty'] = $v['ssnum'];
                $datailinfo[$k]['inventoryQty'] = $v['num'];

            }
//            var_dump($datailinfo);
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "入库单");
            $GLOBALS['tmpl']->display("pages/inventory/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("productlist", parent::get_product_template_list());
        $GLOBALS['tmpl']->assign("page_title", "生产入库单");
        $GLOBALS['tmpl']->display("pages/product/inventoryView.html");

    }

}

?>