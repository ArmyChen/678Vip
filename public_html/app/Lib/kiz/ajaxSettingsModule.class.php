<?php
require_once 'core/pinyin.php';
require_once 'core/page.php';

class ajaxSettingsModule extends KizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();

    }

}