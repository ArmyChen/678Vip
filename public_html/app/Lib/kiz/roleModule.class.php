<?php

require APP_ROOT_PATH.'app/Lib/page.php';

class roleModule extends KizBaseModule

{
    function __construct()
    {
        parent::__construct();

        global_run();
        parent::init();
//        $this->check_auth();
    }

    public function role_list(){
        init_app_page();

        $GLOBALS['tmpl']->assign("page_title", "角色设置");
        $GLOBALS['tmpl']->display("pages/role/list.html");
    }

    public function userBrand_list(){
        init_app_page();

        $GLOBALS['tmpl']->assign("page_title", "角色设置");
        $GLOBALS['tmpl']->display("pages/role/userBrand.html");
    }
}

?>