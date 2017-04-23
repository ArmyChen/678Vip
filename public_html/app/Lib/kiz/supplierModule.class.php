<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class supplierModule extends KizBaseModule

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

    #商品采购采购入库单
    /**
     * 仓库采购入库查询
     */
    public function go_down_index()	{
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
        $GLOBALS['tmpl']->assign("danjuhao", $_REQUEST['danjuhao']);
        $GLOBALS['tmpl']->assign("page_title", "采购入库单");
        $GLOBALS['tmpl']->display("pages/supplier/goDown.html");

    }
    /**
     * 仓库采购入库查询
     */
    public function go_down_index_view()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /*获取采购入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_log where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);
//var_dump($result);
            $datailinfo = array();
            foreach(unserialize($result['dd_detail']) as $k=>$v){
                $dc_menu = $this->getCangkuMenuInfoByMid($v['mid']);
                $dc_menu2 = $this->getDcMenuInfoByMid($v['mid']);
                $datailinfo[$k]['id'] = $v['mid'];//24733
                $datailinfo[$k]['skuId'] = $dc_menu['cate_id'];
                $datailinfo[$k]['skuTypeName'] = empty($this->get_dc_supplier_menu($dc_menu2['cate_id']))?"":$this->get_dc_supplier_menu($dc_menu2['cate_id'])['name'];
                $datailinfo[$k]['skuCode'] = $dc_menu['barcode'];
                $datailinfo[$k]['skuName'] = $dc_menu['mname'];
                $datailinfo[$k]['uom'] = $dc_menu['unit'];
                $datailinfo[$k]['price'] = $v['price'];
                $datailinfo[$k]['actualQty'] = $v['num'];
                $datailinfo[$k]['amount'] = $v['price']* $v['num'];
                $datailinfo[$k]['standardsupplierQty'] = $dc_menu2['stock'];
                $datailinfo[$k]['supplierQty'] = $v['num'] + $dc_menu2['stock'];

            }
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "采购入库单");
            $GLOBALS['tmpl']->display("pages/supplier/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("page_title", "查看采购入库单");
        $GLOBALS['tmpl']->display("pages/supplier/goDownView.html");

    }

    /**
     * 仓库出库查询
     */
    public function go_up_index_view()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /*获取采购入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_log where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);
//var_dump($result);
            $datailinfo = array();
            foreach(unserialize($result['dd_detail']) as $k=>$v){
                $dc_menu = $this->getCangkuMenuInfoByMid($v['mid']);
                $dc_menu2 = $this->getDcMenuInfoByMid($v['mid']);
                $datailinfo[$k]['id'] = $v['mid'];//24733
                $datailinfo[$k]['skuId'] = $dc_menu['cate_id'];
                $datailinfo[$k]['skuTypeName'] = empty($this->get_dc_supplier_menu($dc_menu2['cate_id']))?"":$this->get_dc_supplier_menu($dc_menu2['cate_id'])['name'];
                $datailinfo[$k]['skuCode'] = $dc_menu['barcode'];
                $datailinfo[$k]['skuName'] = $dc_menu['mname'];
                $datailinfo[$k]['uom'] = $dc_menu['unit'];
                $datailinfo[$k]['price'] = $v['price'];
                $datailinfo[$k]['actualQty'] = $v['num'];
                $datailinfo[$k]['amount'] = $v['price']* $v['num'];
                $datailinfo[$k]['standardsupplierQty'] = $dc_menu2['stock'];
                $datailinfo[$k]['supplierQty'] = $v['num'] + $dc_menu2['stock'];

            }
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "出库单");
            $GLOBALS['tmpl']->display("pages/supplier/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("page_title", "查看出库单");
        $GLOBALS['tmpl']->display("pages/supplier/goUpView.html");

    }

    /**
     * 仓库采购入库添加
     */
    public function go_down_add()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
//        $sss=$GLOBALS['db']->getAll("select * from fanwe_cangku_menu limit 1");
//        var_dump($sss);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("gonghuoren", parent::get_bumen_list($slid));
        $GLOBALS['tmpl']->assign("gys", parent::get_gys_list($slid));
        $GLOBALS['tmpl']->assign("page_title", "采购入库单");
        $GLOBALS['tmpl']->display("pages/supplier/goDownAdd.html");

    }


    /**
     * 仓库出库查询
     */
    public function go_up_index()	{
        init_app_page();

        $account_info = $GLOBALS['account_info'];
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
        $GLOBALS['tmpl']->assign("danjuhao", $_REQUEST['danjuhao']);
        $GLOBALS['tmpl']->assign("page_title", "出库单");
        $GLOBALS['tmpl']->display("pages/supplier/goUp.html");

    }
    /**
     * 仓库出库添加
     */
    public function go_up_add()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
//        获取部门信息
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign('bumen',parent::get_bumen_list($slid));
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("page_title", "出库单");
        $GLOBALS['tmpl']->display("pages/supplier/goUpAdd.html");

    }

    /**
     * 仓库出库查询
     */
    public function go_transfer_index()	{
        init_app_page();

        $account_info = $GLOBALS['account_info'];
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
        $GLOBALS['tmpl']->assign("danjuhao", $_REQUEST['danjuhao']);
        $GLOBALS['tmpl']->assign("page_title", "移库单");
        $GLOBALS['tmpl']->display("pages/supplier/goTransfer.html");

    }
    /**
     * 仓库出库添加
     */
    public function go_transfer_add()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("page_title", "移库单");
        $GLOBALS['tmpl']->display("pages/supplier/goTransferAdd.html");

    }
}

?>