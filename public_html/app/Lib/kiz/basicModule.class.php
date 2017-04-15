<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class basicModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();
        $kcnx=array(
//            "0"=>"默认",
//            "1"=>"现制商品",
//            "2"=>"预制商品",
//            "3"=>"外购商品",
            "4"=>"原物料",
            "6"=>"半成品",

        );
        $index_kcnx=array(
            "0"=>"暂无",
            "1"=>"现制商品",
            "2"=>"预制商品",
            "3"=>"外购商品",
            "4"=>"原物料",
            "6"=>"半成品",
        );

        $this->index_kcnx=$index_kcnx;
//        $this->check_auth();
    }
    #仓库管理
	public function basic_setting_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "仓库管理");
        $GLOBALS['tmpl']->display("pages/basic/setting.html");
    }

    public function basic_setting_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增仓库");
        $GLOBALS['tmpl']->display("pages/basic/settingAdd.html");
    }

    #期初设定
    public function basic_master_index()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "期初库存");
        $GLOBALS['tmpl']->display("pages/basic/master.html");
    }

    #商品-原料类别设定
    public function basic_category_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品-原料类别");
        $GLOBALS['tmpl']->display("pages/basic/category.html");
    }

    public function basic_category_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        $listsort = toFormatTree($wmenulist,"name");
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign("page_title", "新增原料类别保存 保存并复制 返回");
        $GLOBALS['tmpl']->display("pages/basic/categoryAdd.html");
    }

    #商品-原料设定
    public function basic_warehouse_index()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        $listsort = toFormatTree($wmenulist,"name");
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign('kcnx',$this->index_kcnx);
        $GLOBALS['tmpl']->assign("page_title", "商品-原料");
        $GLOBALS['tmpl']->display("pages/basic/warehouse.html");
    }

    public function basic_warehouse_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        $listsort = toFormatTree($wmenulist,"name");
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign("unitlist",json_encode(parent::get_unit_list($slid)));
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);
        $GLOBALS['tmpl']->assign("page_title", "新增原料");
        $GLOBALS['tmpl']->display("pages/basic/warehouseAdd.html");
    }

    #商品配方设定
    public function basic_skuBom_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品配方设定");
        $GLOBALS['tmpl']->display("pages/basic/skuBom.html");
    }
    #退回、报废原因设定
    public function basic_reason_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "退回、报废原因设定");
        $GLOBALS['tmpl']->display("pages/basic/reason.html");
    }
    #库存参数设定
    public function basic_param_setting_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "库存参数设定");
        $GLOBALS['tmpl']->display("pages/basic/paramSetting.html");
    }
    #库存预警设定
    public function basic_inventoryWarning_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "库存预警设定");
        $GLOBALS['tmpl']->display("pages/basic/inventoryWarning.html");
    }
    public function basic_inventoryWarning_edit()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑库存预警设定");
        $GLOBALS['tmpl']->display("pages/basic/inventoryWarningEdit.html");
    }
    public function index1(){
        init_app_page();
//        $sql = "alter table fanwe_cangku_menu auto_increment  =  1075;";
        $sql = "select * from fanwe_dc_menu order by id desc limit 1";
//        var_dump($GLOBALS['db']->query($sql));
        var_dump($GLOBALS['db']->getAll($sql));
    }
}

?>