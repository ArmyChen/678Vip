<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class customerModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();

        global_run();

        parent::init();
//        $this->check_auth();
    }

    /**
     * 顾客管理
     */
    public function customer_list(){
        init_app_page();

        $GLOBALS['tmpl']->assign("page_title", "顾客管理");
        $GLOBALS['tmpl']->display("pages/customer/list.html");
    }
}

?>