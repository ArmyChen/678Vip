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
        $this->kcnx=$kcnx;
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
    public function basic_setting_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        $cangku=$GLOBALS['db']->getRow("select * from fanwe_cangku where id=".$id);
        $disable = "";
        $enable = "";
        if($cangku['isdisable']){//为1就是启用
            $disable = "selected";
            $enable = "";
        }else{
            $disable = "";
            $enable = "selected";
        }
        if($cangku['isdeal']){//为1就是启用
            $ddisable = "selected";
            $denable = "";
        }else{
            $ddisable = "";
            $denable = "selected";
        }
//        var_dump($denable);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangku", $cangku);
        $GLOBALS['tmpl']->assign("disable", $disable);
        $GLOBALS['tmpl']->assign("ddisable", $ddisable);
        $GLOBALS['tmpl']->assign("enable", $enable);
        $GLOBALS['tmpl']->assign("denable", $denable);

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑仓库");
        $GLOBALS['tmpl']->display("pages/basic/settingEdit.html");
    }
    #单位管理
    public function basic_unit_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "单位管理");
        $GLOBALS['tmpl']->display("pages/basic/unit.html");
    }

    public function basic_unit_add()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增单位");
        $GLOBALS['tmpl']->display("pages/basic/unitAdd.html");
    }
    public function basic_unit_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        $cangku=$GLOBALS['db']->getRow("select * from fanwe_dc_supplier_unit_cate where id=".$id);
        $disable = "";
        $enable = "";
        if($cangku['is_effect']){
            $disable = "checked";
            $enable = "";
        }else{
            $disable = "";
            $enable = "checked";
        }
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("cangku", $cangku);
        $GLOBALS['tmpl']->assign("disable", $disable);
        $GLOBALS['tmpl']->assign("enable", $enable);

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "编辑单位");
        $GLOBALS['tmpl']->display("pages/basic/unitEdit.html");
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

    /**
     * 新增分类页面
     */
    public function basic_category_add()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        foreach($wmenulist as $wmenu)
        {
            if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
        }
        foreach($wmenulist as $wmenu0)
        {
            if($wmenu0['wcategory'] == '0')
            {
                $wmenu0['parentTypeName'] = "";
                $list[] = $wmenu0;
                foreach($wsublist[$wmenu0['id']] as $wmenu1)
                {
                    $wmenu1['parentTypeName'] = $wmenu0['name'];
                    $list[] = $wmenu1;
                    foreach($wsublist[$wmenu1['id']] as $wmenu2)
                    {
                        $wmenu2['parentTypeName'] = $wmenu1['name'];
                        $list[] = $wmenu2;
                        foreach($wsublist[$wmenu2['id']] as $wmenu3)
                        {
                            $wmenu3['parentTypeName'] = $wmenu2['name'];
                            $list[] = $wmenu3;
                        }
                    }
                }
            }
        }
        $listsort = toFormatTree($list,"name");

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign("page_title", "新增原料类别保存 保存并复制 返回");
        $GLOBALS['tmpl']->display("pages/basic/categoryAdd.html");
    }

    /**
     * 编辑分类
     */
    public function basic_category_edit()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        foreach($wmenulist as $wmenu)
        {
            if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
        }
        foreach($wmenulist as $wmenu0)
        {
            if($wmenu0['wcategory'] == '0')
            {
                $wmenu0['parentTypeName'] = "";
                $list[] = $wmenu0;
                foreach($wsublist[$wmenu0['id']] as $wmenu1)
                {
                    $wmenu1['parentTypeName'] = $wmenu0['name'];
                    $list[] = $wmenu1;
                    foreach($wsublist[$wmenu1['id']] as $wmenu2)
                    {
                        $wmenu2['parentTypeName'] = $wmenu1['name'];
                        $list[] = $wmenu2;
                        foreach($wsublist[$wmenu2['id']] as $wmenu3)
                        {
                            $wmenu3['parentTypeName'] = $wmenu2['name'];
                            $list[] = $wmenu3;
                        }
                    }
                }
            }
        }

        $listsort = toFormatTree($list,"name");

        $id = $_REQUEST['id'];
        $category = $GLOBALS['db']->getRow("select * from fanwe_dc_supplier_menu_cate where id=".$id);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign("category", $category);

        $GLOBALS['tmpl']->assign("page_title", "新增原料类别保存 保存并复制 返回");
        $GLOBALS['tmpl']->display("pages/basic/categoryEdit.html");
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
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);
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
    public function basic_warehouse_edit()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate where wlevel<4 and is_effect=0 and location_id =".$slid ;

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        //获取商品信息
        $sql = "select *,g.name as standerStr,g.id as id from fanwe_dc_menu g LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id where g.id=".$id;
        $dc_menu=$GLOBALS['db']->getRow($sql);

        $listsort = toFormatTree($wmenulist,"name");
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("listsort", $listsort);
        $GLOBALS['tmpl']->assign("dc_menu", $dc_menu);
        $GLOBALS['tmpl']->assign("unitlist",json_encode(parent::get_unit_list($slid)));
        $GLOBALS['tmpl']->assign('kcnx',$this->kcnx);
        $GLOBALS['tmpl']->assign("page_title", "编辑原料");
        $GLOBALS['tmpl']->display("pages/basic/warehouseEdit.html");
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
//var_dump(parent::get_basic_reason_list(0,2));
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("reason1", parent::get_basic_reason_list(0,1));
        $GLOBALS['tmpl']->assign("reason2",  parent::get_basic_reason_list(0,2));
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
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "库存预警设定");
        $GLOBALS['tmpl']->display("pages/basic/inventoryWarningEdit.html");
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

    #部门列表
    public function bumen_index()
    {
        init_app_page();
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "部门列表");
        $GLOBALS['tmpl']->display("pages/basic/bumen.html");
    }
    public function bumen_add()
    {
        init_app_page();
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "新增部门");
        $GLOBALS['tmpl']->display("pages/basic/bumenAdd.html");
    }
    public function bumen_edit()
    {
        init_app_page();
        $id = $_REQUEST['id'];
        $g = $GLOBALS['db']->getRow("select * from fanwe_cangku_bumen where id=$id");
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("g", $g);
        $GLOBALS['tmpl']->assign("page_title", "编辑部门");
        $GLOBALS['tmpl']->display("pages/basic/bumenEdit.html");
    }
}

?>