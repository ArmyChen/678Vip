<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class dishModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();

        global_run();

        parent::init();
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

    public function dish_category()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品类别");
        $GLOBALS['tmpl']->display("pages/dish/category.html");
    }

    public function dish_list()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品管理");
        $GLOBALS['tmpl']->display("pages/dish/list.html");
    }
}

?>