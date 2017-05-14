<?php

require APP_ROOT_PATH . 'app/Lib/page.php';

function werror()
{
}
function wsuccess()
{
}

class inventoryModule extends KizBaseModule{
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
        //$this->check_auth();

    }

    /**
     * 仓库入库查询
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
        $GLOBALS['tmpl']->assign("page_title", "入库单");
        $GLOBALS['tmpl']->display("pages/inventory/goDown.html");

    }
    /**
     * 仓库入库查询
     */
    public function go_down_index_view()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /*获取入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_log where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);
//var_dump(unserialize($result['dd_detail']));
            $datailinfo = array();
            foreach(unserialize($result['dd_detail']) as $k=>$v){
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
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "入库单");
            $GLOBALS['tmpl']->display("pages/inventory/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("page_title", "查看入库单");
        $GLOBALS['tmpl']->display("pages/inventory/goDownView.html");

    }

    /**
     * 仓库入库查询
     */
    public function go_down_index_edit()	{
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
        $GLOBALS['tmpl']->assign("page_title", "编辑入库单");
        $GLOBALS['tmpl']->display("pages/inventory/goDownEdit.html");

    }

    /**
     * 仓库入库查询
     */
    public function go_up_index_edit()	{
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
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "出库单");
            $GLOBALS['tmpl']->display("pages/inventory/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("result", $result);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("page_title", "编辑出库单");
        $GLOBALS['tmpl']->display("pages/inventory/goUpEdit.html");

    }


    /**
     * 仓库移库查询
     */
    public function go_transfer_index_view()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /*获取入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_diaobo where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);

            $datailinfo = array();
            foreach(unserialize($result['dd_detail']) as $k=>$v){
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

            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "移库单");
            $GLOBALS['tmpl']->display("pages/inventory/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("page_title", "查看移库单");
        $GLOBALS['tmpl']->display("pages/inventory/goTransferView.html");

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
        /*获取入库信息*/
        $id = $_REQUEST['id'];
        if($id > 0){
            $sql = "select * from fanwe_cangku_log where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);
//var_dump($result);
            $datailinfo = array();
            foreach(unserialize($result['dd_detail']) as $k=>$v){
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
            $GLOBALS['tmpl']->assign("dd_detail", json_encode($datailinfo));
            $GLOBALS['tmpl']->assign("result", $result);
        }else{
            $GLOBALS['tmpl']->assign("page_title", "出库单");
            $GLOBALS['tmpl']->display("pages/inventory/goDown.html");
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
        $GLOBALS['tmpl']->assign("page_title", "查看出库单");
        $GLOBALS['tmpl']->display("pages/inventory/goUpView.html");

    }

    /**
     * 仓库入库添加
     */
    public function go_down_add()	{
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        $GLOBALS['tmpl']->assign("ywsort", $this->ywsort);
        $GLOBALS['tmpl']->assign("ywsortid", $ywsortid);
        $GLOBALS['tmpl']->assign("page_title", "入库单");
        $GLOBALS['tmpl']->display("pages/inventory/goDownAdd.html");

    }


    /**
     * 仓库出库查询
     */
    public function go_up_index()	{
        init_app_page();
//        var_dump($GLOBALS['db']->getAll("select * from fanwe_cangku_log where TYPE =2"));

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
        $GLOBALS['tmpl']->display("pages/inventory/goUp.html");

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
        $GLOBALS['tmpl']->display("pages/inventory/goUpAdd.html");

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
        $GLOBALS['tmpl']->display("pages/inventory/goTransfer.html");

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
        $GLOBALS['tmpl']->display("pages/inventory/goTransferAdd.html");

    }

    #入库单打印
    public function go_down_print_view(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slname =$account_info['slname'];
        $account_name =$account_info['account_name'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $printType = $_REQUEST['printType'];
        $sql = "select * from fanwe_cangku_log where id=".$id;
        $result = $GLOBALS['db']->getRow($sql);
        $sum = 0;
        $amount = 0;
        $datailinfo = array();
        foreach(unserialize($result['dd_detail']) as $k=>$v){
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
            $sum += intval($v['num']);
            $amount += floatval($v['price']* $v['num']);

        }
//        var_dump($result['cid']);die;

        $result['gys'] = parent::get_gonghuoren_name($account_info['supplier_id'],$slid,$result['gys']);//供货人
        $result['gonghuoren'] = parent::get_gonghuoren_name($account_info['supplier_id'],$slid,$result['gonghuoren']);
        $result['ctime2'] = date("Y-m-d H:i:s",$result['ctime']);
        $result['cname'] = !parent::get_cangku_list($result['cid'])?"":parent::get_cangku_list($result['cid'])['name'];
        $result['supplier'] = $slname;
        $result['lihuoren'] = $account_name;
        $result['sum'] = $sum;
        $result['amount'] = $amount;
        $result['yuanyin'] = parent::getCollectionValue($this->ywsort,$result['ywsort']);

        $GLOBALS['tmpl']->assign("dd_detail", $datailinfo);
        $GLOBALS['tmpl']->assign("result", $result);

        $GLOBALS['tmpl']->assign("page_title", "打印入库单");
        $GLOBALS['tmpl']->assign("header", $slname."入库单");
        $GLOBALS['tmpl']->assign("printType", $printType);
        $GLOBALS['tmpl']->display("pages/inventory/goDownPrint.html");
    }

    #出库单打印
    public function go_up_print_view(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slname =$account_info['slname'];
        $account_name =$account_info['account_name'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $printType = $_REQUEST['printType'];
        $sql = "select * from fanwe_cangku_log where id=".$id;
        $result = $GLOBALS['db']->getRow($sql);
        $sum = 0;
        $amount = 0;
        $datailinfo = array();
        foreach(unserialize($result['dd_detail']) as $k=>$v){
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
            $sum += intval($v['num']);
            $amount += floatval($v['price']* $v['num']);

        }
        $result['gys'] = parent::get_gonghuoren_name($account_info['supplier_id'],$slid,$result['gys']);//供货人
        $result['gonghuoren'] = parent::get_gonghuoren_name($account_info['supplier_id'],$slid,$result['gonghuoren']);
        $result['ctime2'] = date("Y-m-d H:i:s",$result['ctime']);
        $result['cname'] = !parent::get_cangku_list($result['cid'])?"":parent::get_cangku_list($result['cid'])['name'];
        $result['supplier'] = $slname;
        $result['lihuoren'] = $account_name;
        $result['sum'] = $sum;
        $result['amount'] = $amount;

        $GLOBALS['tmpl']->assign("dd_detail", $datailinfo);
        $GLOBALS['tmpl']->assign("result", $result);

        $GLOBALS['tmpl']->assign("page_title", "打印出库单");
        $GLOBALS['tmpl']->assign("header", $slname."出库单");
        $GLOBALS['tmpl']->assign("printType", $printType);
        $GLOBALS['tmpl']->display("pages/inventory/goDownPrint.html");
    }

    #移库打印
    public function go_transfer_print_view(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slname =$account_info['slname'];
        $account_name =$account_info['account_name'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $printType = $_REQUEST['printType'];
        $sql = "select * from fanwe_cangku_diaobo where id=".$id;
        $result = $GLOBALS['db']->getRow($sql);
        $sum = 0;
        $amount = 0;
        $datailinfo = array();
        foreach(unserialize($result['dd_detail']) as $k=>$v){
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
            $sum += intval($v['num']);
            $amount += floatval($v['price']* $v['num']);

        }
        $result['gys'] = parent::get_gonghuoren_name($account_info['supplier_id'],$slid,$result['gys']);//供货人
        $result['gonghuoren'] = parent::get_gonghuoren_name($account_info['supplier_id'],$slid,$result['gonghuoren']);
        $result['ctime2'] = date("Y-m-d H:i:s",$result['ctime']);
        $result['cname'] = !parent::get_cangku_list($result['cid'])?"":parent::get_cangku_list($result['cid'])['name'];
        $result['cname2'] = !parent::get_cangku_list($result['cidtwo'])?"":parent::get_cangku_list($result['cidtwo'])['name'];
        $result['supplier'] = $slname;
        $result['lihuoren'] = $account_name;
        $result['sum'] = $sum;
        $result['amount'] = $amount;

        $GLOBALS['tmpl']->assign("dd_detail", $datailinfo);
        $GLOBALS['tmpl']->assign("result", $result);

        $GLOBALS['tmpl']->assign("page_title", "打印出库单");
        $GLOBALS['tmpl']->assign("header", $slname."出库单");
        $GLOBALS['tmpl']->assign("printType", $printType);
        $GLOBALS['tmpl']->display("pages/inventory/goTransferPrint.html");
    }
}

?>