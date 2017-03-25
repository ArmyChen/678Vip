<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class basicModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();
        global_run();
        parent::init();
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

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "期初库存");
        $GLOBALS['tmpl']->display("pages/basic/master.html");
    }

    #商品-原料类别设定
    public function basic_category_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品-原料类别设定");
        $GLOBALS['tmpl']->display("pages/basic/category.html");
    }

    #商品-原料设定
    public function basic_warehouse_index()
    {
        init_app_page();

        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商品-原料设定");
        $GLOBALS['tmpl']->display("pages/basic/warehouse.html");
    }

}

?>