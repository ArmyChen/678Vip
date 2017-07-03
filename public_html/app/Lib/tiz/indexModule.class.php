<?php
require_once 'core/pinyin.php';
require_once 'core/page.php';

class indexModule extends TizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();
    }

    public function index(){
        echo 1;
    }
}