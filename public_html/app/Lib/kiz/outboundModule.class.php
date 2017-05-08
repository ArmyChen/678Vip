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
        $GLOBALS['tmpl']->assign("cangkulist", parent::get_cangku_list());
        $GLOBALS['tmpl']->assign("page_title", "新增报废单");
        $GLOBALS['tmpl']->display("pages/outbound/scrapAdd.html");
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