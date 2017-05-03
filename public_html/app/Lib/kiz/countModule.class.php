<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class countModule extends KizBaseModule

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
    #盘点管理设定
    public function count_setting_index()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
        $GLOBALS['tmpl']->assign("slidlist", $slidlist);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "盘点管理设定");
        $GLOBALS['tmpl']->display("pages/count/countSetting.html");
    }
    #盘点模板列表
    public function count_stock_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "盘点模板");
        $GLOBALS['tmpl']->display("pages/count/countStock.html");
    }
    public function count_stock_add()
    {
        init_app_page();
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增盘点模板");
        $GLOBALS['tmpl']->display("pages/count/countStockAdd.html");
    }
    public function count_stock_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑盘点模板");
        $GLOBALS['tmpl']->display("pages/count/countStockEdit.html");
    }
    #商品需求汇总单
    public function count_task_index()
    {
        init_app_page();
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("templatelist", parent::get_count_template_list());
        $GLOBALS['tmpl']->assign("page_title", "盘点单");
        $GLOBALS['tmpl']->display("pages/count/countTask.html");
    }

    public function count_task_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $sql = "select fc.id,fc.name from fanwe_cangku fc where fc.slid=$slid";


        $list = $GLOBALS['db']->getAll($sql);
        foreach ($list as $key=>$item) {
            $pandian = "select * from fanwe_cangku_pandian_danju where isdisable=1 and cangku_id=".$item['id']."  and slid=$slid";
            $pandianlist = $GLOBALS['db']->getAll($pandian);
            if(count($pandianlist) > 0){
                unset($list[$key]);
            }
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $list);
        $GLOBALS['tmpl']->assign("templatelist", parent::get_count_template_list());
        $GLOBALS['tmpl']->assign("page_title", "新增盘点单");
        $GLOBALS['tmpl']->display("pages/count/countTaskAdd.html");
    }

    public function count_task_edit()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $sql = "select fc.id,fc.name from fanwe_cangku fc where fc.slid=$slid";
        $gsql = "select * from fanwe_cangku_pandian_danju where id=$id";
        $glist = $GLOBALS['db']->getRow($gsql);
        $glist['warehousename']=parent::get_cangku_list($glist['cangku_id'])['name'];
        $ddsql = "select * from fanwe_cangku_pandian_stat where djid=".$glist['id'];
        $clist = $GLOBALS['db']->getAll($ddsql);
        $list = $GLOBALS['db']->getAll($sql);
        foreach ($list as $key=>$item) {
            $pandian = "select * from fanwe_cangku_pandian_danju where isdisable=1 and cangku_id=".$item['id']."  and slid=$slid";
            $pandianlist = $GLOBALS['db']->getAll($pandian);
            if(count($pandianlist) > 0){
                unset($list[$key]);
            }
        }
        $inventoryAmount = 0;
        $ccAmount = 0;
        $dd_detail = [];
//        var_dump($clist);
        foreach ($clist as $key=>$item) {
            $typeName = parent::get_dc_current_supplier_cate($item['cate_id']);
            if (!empty($typeName)){
                $dd_detail[$key]['skuTypeName'] = $typeName['name'];
            }else{
                $dd_detail[$key]['skuTypeName'] = '<span style="color:red">顶级分类</span>';
            }
            $dd_detail[$key]['skuId'] = $item['mid'];
            $dd_detail[$key]['skuTypeId'] = $item['cate_id'];
            $dd_detail[$key]['skuCode'] = $item['id'];
            $dd_detail[$key]['skuName'] = $item['mname'];
            $dd_detail[$key]['uom'] = $item['unit'];
            $dd_detail[$key]['price'] = $item['mprice'];
            $dd_detail[$key]['inventoryQty'] = $item['stock'];
            $dd_detail[$key]['realTimeInventory'] = $item['mstock'];
            $dd_detail[$key]['ccQty'] = $item['pandianshu'];
            $dd_detail[$key]['qtyDiff'] = 0;
            $dd_detail[$key]['amountDiff'] = 0;
            $dd_detail[$key]['remarks'] = '';
            $dd_detail[$key]['ccAmount'] = $item['stock']*$item['mprice'];
            $dd_detail[$key]['relTimeAmount'] = $item['mstock']*$item['mprice'];
            $dd_detail[$key]['alreadyData'] = 1;
            $dd_detail[$key]['remarks'] =$item['memo'];
            $dd_detail[$key]['djid'] = $item['id'];
            $inventoryAmount +=  $dd_detail[$key]['inventoryQty'];
            $ccAmount +=  $dd_detail[$key]['ccAmount'];
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $list);
        $GLOBALS['tmpl']->assign("glist", $glist);
        $GLOBALS['tmpl']->assign("inventoryAmount", $inventoryAmount);
        $GLOBALS['tmpl']->assign("ccAmount", $ccAmount);

        $GLOBALS['tmpl']->assign("dd_detail", json_encode($dd_detail));
        $GLOBALS['tmpl']->assign("templatelist", parent::get_count_template_list());
        $GLOBALS['tmpl']->assign("page_title", "编辑盘点单");
        $GLOBALS['tmpl']->display("pages/count/countTaskEdit.html");
    }
}

?>