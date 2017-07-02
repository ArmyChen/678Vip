<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class dishModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();

        global_run();

        parent::init();
        $kcnx=array(
//            "0"=>"默认",
            "1"=>"现制商品",
            "2"=>"预制商品",
            "3"=>"外购商品",
//            "4"=>"原物料",
//            "6"=>"半成品",

        );
        $index_kcnx=array(
            "0"=>"暂无",
            "1"=>"现制商品",
            "2"=>"预制商品",
            "3"=>"外购商品",
            "4"=>"原物料",
            "6"=>"半成品",
        );
        $this->kcnx=$kcnx;
        $this->index_kcnx=$index_kcnx;
//        $this->check_auth();
    }
    #供应商列表
    public function dish_cookingway()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $sql = "select * from fanwe_dc_supplier_taste where location_id=".$slid." order by sort asc";
        $rows = $GLOBALS['db']->getAll($sql);
        $flavors = [];
        $taste = [];
        $flavors = json_decode($rows[0]['flavor']);

        foreach ($rows as $k=>$v) {
            $taste[$k]['id'] = $v['id'];
            $taste[$k]['name'] = $v['name'];
            $taste[$k]['is_effect'] = $v['is_effect'];
            $taste[$k]['flavorCount'] = count(json_decode($v['flavor']));
        }

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("taste", $taste);
        $GLOBALS['tmpl']->assign("flavors", $flavors);
        $GLOBALS['tmpl']->assign("page_title", "做法管理");
        $GLOBALS['tmpl']->display("pages/dish/cookingway.html");
    }
    public function addCookingWayType()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增做法分类");
        $GLOBALS['tmpl']->display("pages/dish/cookingwayAdd.html");
    }
    public function updateCookingWayType()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id=$_REQUEST['id'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("r",parent::get_supplier_cate_row($id));
        $GLOBALS['tmpl']->assign("page_title", "编辑做法分类");
        $GLOBALS['tmpl']->display("pages/dish/cookingwayEdit.html");
    }
    public function addCookingWay()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['propertyTypeId'];
        $sql = "select * from fanwe_dc_supplier_taste where id=".$id;
        $r = $GLOBALS['db']->getRow($sql);

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("r", $r);
        $GLOBALS['tmpl']->assign("page_title", "新增做法");
        $GLOBALS['tmpl']->display("pages/dish/cookingwaysAdd.html");
    }
    public function updateCookingWay()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "修改做法分类");
        $GLOBALS['tmpl']->display("pages/dish/cookingwayEdit.html");
    }
    public function dish_unit()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "单位管理");
        $GLOBALS['tmpl']->display("pages/dish/unit.html");
    }
    public function dish_unit_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增单位管理");
        $GLOBALS['tmpl']->display("pages/dish/unitAdd.html");
    }
    public function dish_unit_edit()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("r", parent::get_supplier_cate_unit_row($id));
        $GLOBALS['tmpl']->assign("page_title", "编辑单位管理");
        $GLOBALS['tmpl']->display("pages/dish/unitEdit.html");
    }

    public function dish_category()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $r =  parent::goods_category_one_ajax($id);
        $taste = [];
        foreach ($r as $k=>$v) {
            $taste[$k]['id'] = $v['id'];
            $taste[$k]['name'] = $v['name'];
            $taste[$k]['is_effect'] = $v['is_effect'];
            $taste[$k]['flavorCount'] = count(parent::goods_category_two_ajax($v['id']));
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("r",$taste);
        $GLOBALS['tmpl']->assign("page_title", "商品类别");
        $GLOBALS['tmpl']->display("pages/dish/category.html");
    }
    /**
     * 商品分类新增
     */
    public function dish_category_type_add(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增商品大类");
        $GLOBALS['tmpl']->display("pages/dish/categoryAdd.html");
    }
    /**
     * 商品分类编辑
     */
    public function dish_category_type_edit(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        /* 系统默认 */

        $GLOBALS['tmpl']->assign("r", parent::goods_category_one_ajax($id));
        $GLOBALS['tmpl']->assign("page_title", "编辑商品大类");
        $GLOBALS['tmpl']->display("pages/dish/categoryEdit.html");
    }
    /**
     * 商品中类新增
     */
    public function dish_category_add(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['parentId'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("r", parent::goods_category_one_ajax($id));
        $GLOBALS['tmpl']->assign("page_title", "新增中类");
        $GLOBALS['tmpl']->display("pages/dish/categorysAdd.html");
    }
    /**
     * 商品中类编辑
     */
    public function dish_category_edit(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];//中类id
        $main = parent::goods_category_one_ajax($id);
        $sub = parent::goods_category_one_ajax($main['pid']);
        /* 系统默认 */

        $GLOBALS['tmpl']->assign("r", $main);
        $GLOBALS['tmpl']->assign("r2", $sub);
        $GLOBALS['tmpl']->assign("page_title", "编辑中类");
        $GLOBALS['tmpl']->display("pages/dish/categorysEdit.html");
    }

    public function dish_list()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        $listsort = toFormatTree($wmenulist,"name");
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);
        $GLOBALS['tmpl']->assign("page_title", "商品管理");
        $GLOBALS['tmpl']->display("pages/dish/list.html");
    }
    public function addDish()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增商品");
        $GLOBALS['tmpl']->display("pages/dish/addDish.html");
    }

    public function editDish()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = intval($_REQUEST['id']);
        $dish = parent::getDcMenuInfoByMid($id);
        $dishExtends = parent::getDcMenuExtendsByMid($id);
//var_dump($dish);
        /* 系统默认 */

        $GLOBALS['tmpl']->assign("dish", $dish);
        $GLOBALS['tmpl']->assign("dishExtends", $dishExtends);
        $GLOBALS['tmpl']->assign("kcnx", $this->kcnx);
        $GLOBALS['tmpl']->assign("page_title", "编辑商品");
        $GLOBALS['tmpl']->display("pages/dish/editDish.html");
    }

    public function dish_tag()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "标签管理");
        $GLOBALS['tmpl']->display("pages/dish/tag.html");
    }
    public function dish_tag_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增标签管理");
        $GLOBALS['tmpl']->display("pages/dish/tagAdd.html");
    }
    public function dish_tag_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        $cangku = $GLOBALS['db']->getRow("select * from fanwe_dish_goods_tag where id=" . $id);
        $disable = "";
        $enable = "";
        if (!empty($cangku['is_effect'])) {
            $disable = "checked";
            $enable = "";
        } else {
            $disable = "";
            $enable = "checked";
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangku", $cangku);
        $GLOBALS['tmpl']->assign("disable", $disable);
        $GLOBALS['tmpl']->assign("enable", $enable);

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑单位");
        $GLOBALS['tmpl']->display("pages/dish/tagEdit.html");
    }


    public function dish_pay()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $type = $_REQUEST['type'];
        if ($type==1){
            $page_title='支付备注';
        }elseif($type==2){
            $page_title='支付折扣';
        }elseif($type==3){
            $page_title='退菜备注';
        }elseif($type==4){
            $page_title='赠菜备注';
        }else{
            $page_title='支付方式';
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", $page_title);
        $GLOBALS['tmpl']->assign("type", $type);
        $GLOBALS['tmpl']->display("pages/dish/dpay.html");
    }
    public function dish_pay_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $type = $_REQUEST['type'];
        if ($type==1){
            $page_title='支付备注';
        }elseif($type==2){
            $page_title='支付折扣';
        }elseif($type==3){
            $page_title='退菜备注';
        }elseif($type==4){
            $page_title='赠菜备注';
        }else{
            $page_title='支付方式';
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增".$page_title);
        $GLOBALS['tmpl']->assign("type", $type);
        $GLOBALS['tmpl']->display("pages/dish/dpayAdd.html");
    }
    public function dish_pay_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        $cangku = $GLOBALS['db']->getRow("select * from fanwe_dc_paytype where dpid=" . $id);
//        $disable = "";
//        $enable = "";
//        if (!empty($cangku['is_effect'])) {
//            $disable = "checked";
//            $enable = "";
//        } else {
//            $disable = "";
//            $enable = "checked";
//        }
        /* 系统默认 */
//        $GLOBALS['tmpl']->assign("disable", $disable);
//        $GLOBALS['tmpl']->assign("enable", $enable);

        $type = $_REQUEST['type'];
        if ($type==1){
            $page_title='支付备注';
            $cangku['ptname'] = $cangku['memo'];

        }elseif($type==2){
            $page_title='支付折扣';
            $cangku['ptname'] = $cangku['zhekou'];

        }elseif($type==3){
            $page_title='退菜备注';
            $cangku['ptname'] = $cangku['tuireason'];

        }elseif($type==4){
            $page_title='赠菜备注';
            $cangku['ptname'] = $cangku['zencaiyuanyin'];

        }else{
            $page_title='支付方式';
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑".$page_title);
        $GLOBALS['tmpl']->assign("cangku", $cangku);
        $GLOBALS['tmpl']->assign("type", $type);
        $GLOBALS['tmpl']->display("pages/dish/dpayEdit.html");
    }


    public function dish_guazhang(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "挂账人管理");
        $GLOBALS['tmpl']->display("pages/dish/cguazhang.html");

    }

    public function dish_guazhang_add(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "添加挂账人管理");
        $GLOBALS['tmpl']->display("pages/dish/cguazhangAdd.html");

    }

    public function dish_guazhang_edit(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $r = $GLOBALS['db']->getRow("select * from fanwe_guanzhang where id=".$_REQUEST['id']);

        $GLOBALS['tmpl']->assign("page_title", "修改挂账人管理");
        $GLOBALS['tmpl']->assign("r", $r);
        $GLOBALS['tmpl']->display("pages/dish/cguazhangEdit.html");

    }

    public function dish_guazhang_qz(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "清账");
        $GLOBALS['tmpl']->assign("gzr", parent::guazhangren_list());
        $GLOBALS['tmpl']->display("pages/dish/cguazhangQZ.html");

    }

    public function dish_guazhang_rz(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];


        $GLOBALS['tmpl']->assign("page_title", "挂账日志");
        $GLOBALS['tmpl']->assign("gzr", parent::guazhangren_list());
        $GLOBALS['tmpl']->display("pages/dish/cguazhangRZ.html");

    }

    public function dish_dc_yg(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "收银员管理");
        $GLOBALS['tmpl']->display("pages/dish/dc_yg.html");

    }

    public function dish_dc_yg_add(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "添加收银员");
        $GLOBALS['tmpl']->display("pages/dish/dc_ygAdd.html");

    }

    public function dish_dc_yg_edit(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $r = $GLOBALS['db']->getRow("select * from fanwe_syy where sid=".$_REQUEST['id']);

        $GLOBALS['tmpl']->assign("page_title", "修改收银员");
        $GLOBALS['tmpl']->assign("r", $r);
        $GLOBALS['tmpl']->display("pages/dish/dc_ygEdit.html");

    }

    public function dish_dc_waiter(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "营销员管理");
        $GLOBALS['tmpl']->display("pages/dish/dc_waiter.html");

    }

    public function dish_dc_waiter_tj(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "营销员统计管理");
        $GLOBALS['tmpl']->display("pages/dish/dc_waiter_tj.html");

    }

    public function dish_dc_waiter_zdtj(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "营销员整单统计管理");
        $GLOBALS['tmpl']->display("pages/dish/dc_waiter_zdtj.html");

    }
    public function dish_dc_waiter_detail(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "营销员统计详情管理");
        $GLOBALS['tmpl']->display("pages/dish/dc_waiter_detail.html");

    }
    public function dish_dc_waiter_zddetail(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "营销员整单统计详情管理");
        $GLOBALS['tmpl']->display("pages/dish/dc_waiter_zddetail.html");

    }

    public function dish_dc_waiter_add(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "添加营销员");
        $GLOBALS['tmpl']->display("pages/dish/dc_waiterAdd.html");

    }

    public function dish_dc_waiter_edit(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $r = $GLOBALS['db']->getRow("select * from fanwe_waiter where wid=".$_REQUEST['id']);
//var_dump($r);
        $GLOBALS['tmpl']->assign("page_title", "修改营销员");
        $GLOBALS['tmpl']->assign("r", $r);
        $GLOBALS['tmpl']->display("pages/dish/dc_waiterEdit.html");

    }

    //红包设置
    public function dish_hongbao_cfg(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $syy = $GLOBALS['db']->getRow("select * from " . DB_PREFIX ."hongbao_set where slid=$slid limit 1");
        $r = [];
        $r=$syy;
        $r['min_hb']=$syy['min_hb']/100;
        $r['max_hb']=$syy['max_hb']/100;

        $GLOBALS['tmpl']->assign("r", $r);
        $GLOBALS['tmpl']->assign("page_title", "红包设置");
        $GLOBALS['tmpl']->display("pages/dish/dish_hongbao_cfg.html");

    }

    //红包发送记录
    public function dish_hongbaoguanlig(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "红包发送记录");
        $GLOBALS['tmpl']->display("pages/dish/dish_hongbaoguanlig.html");

    }

    //发放红包准备金充值
    public function dish_chongbao_autocz(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];


        $GLOBALS['tmpl']->assign("page_title", "发放红包准备金充值");
        $GLOBALS['tmpl']->display("pages/dish/dish_chongbao_autocz.html");

    }

    //红包准备金充值记录
    public function dish_chongbao_autocz_log(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        $GLOBALS['tmpl']->assign("page_title", "红包准备金充值记录");
        $GLOBALS['tmpl']->display("pages/dish/dish_chongbao_autocz_log.html");

    }

    //线上余额转红包营销余额
    public function dish_hongbao_jiezhuan(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $money=$GLOBALS['db']->getOne("select money from " . DB_PREFIX . "supplier_location where id='$slid'");
        $GLOBALS['tmpl']->assign("money", $money);
        $GLOBALS['tmpl']->assign("page_title", "线上余额转红包营销余额");
        $GLOBALS['tmpl']->display("pages/dish/dish_hongbao_jiezhuan.html");

    }

    //微信支付配置
    public function dish_cashier_wxpay(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $GLOBALS['tmpl']->assign("page_title", "微信支付配置");
        $GLOBALS['tmpl']->display("pages/dish/dish_cashier_wxpay.html");

    }

    //支付宝支付配置
    public function dish_cashier_alipay(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $GLOBALS['tmpl']->assign("page_title", "支付宝支付配置");
        $GLOBALS['tmpl']->display("pages/dish/dish_cashier_alipay.html");

    }

    //翼支付配置
    public function dish_cashier_bestpay(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $GLOBALS['tmpl']->assign("page_title", "翼支付配置");
        $GLOBALS['tmpl']->display("pages/dish/dish_cashier_bestpay.html");

    }

    //和包支付配置
    public function dish_cashier_hbpay(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $GLOBALS['tmpl']->assign("page_title", "和包支付配置");
        $GLOBALS['tmpl']->display("pages/dish/dish_cashier_hbpay.html");

    }



}

?>