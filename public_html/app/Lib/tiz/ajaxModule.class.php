<?php
require_once 'core/pinyin.php';
require_once 'core/page.php';

// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
//dc_menu where (( g.is_effect = 0 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1))
class ajaxModule extends KizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();
    }
}