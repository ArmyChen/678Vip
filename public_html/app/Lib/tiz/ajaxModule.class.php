<?php
require_once 'core/pinyin.php';
require_once 'core/page.php';

class ajaxModule extends TizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();
    }
}