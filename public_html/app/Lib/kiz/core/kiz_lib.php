<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


/**
 * 关于页面初始化时需要输出的信息
 * 全属使用的模板信息输出
 * 1. seo 基本信息
 * $GLOBALS['tmpl']->assign("shop_info",get_shop_info());
 * 2. 当前城市名称, 单城市不显示
 * 3. 输出APP_ROOT
 */
function init_app_page()
{
    //输出根路径
    $GLOBALS['tmpl']->assign("APP_ROOT", APP_ROOT);

    //定义当前语言包
    $GLOBALS['tmpl']->assign("LANG", $GLOBALS['lang']);

    //开始输出site_seo
    $site_seo['keyword'] = app_conf("SHOP_TITLE");
    $site_seo['description'] = app_conf("SHOP_TITLE");
    $site_seo['title'] = app_conf("SHOP_TITLE");
    $GLOBALS['tmpl']->assign("site_seo", $site_seo);

    //获取左侧菜单
    assign_biz_nav_list();

}


/**
 * 前端全运行函数，生成系统前台使用的全局变量
 * 1. 定位城市 GLOBALS['city'];
 * 2. 加载会员 GLOBALS['user_info'];
 * 3. 生成语言包
 * 4. 加载推荐人与来路
 * 5. 更新购物车
 */
function global_run()
{
    if (app_conf("SHOP_OPEN") == 0)  //网站关闭时跳转到站点关闭页
    {
        app_redirect(url("index", "close"));
    }


    //输出语言包的js
    if (!file_exists(get_real_path() . "public/runtime/app/lang.js")) {
        $str = "var LANG = {";
        foreach ($GLOBALS['lang'] as $k => $lang_row) {
            $str .= "\"" . $k . "\":\"" . str_replace("nbr", "\\n", addslashes($lang_row)) . "\",";
        }
        $str = substr($str, 0, -1);
        $str .= "};";
        @file_put_contents(get_real_path() . "public/runtime/app/lang.js", $str);
    }
    //会员信息
    global $user_info;
    $user_info = es_session::get('user_info');

    //商户信息
    global $account_info;
    require_once APP_ROOT_PATH . "system/libs/biz_user.php";
    $account_info = es_session::get('account_info');

    if (isset($_GET['admin_slid'])) {
        $slid = intval($_GET['admin_slid']); //门店ID
        es_cookie::set('admin_slid', $slid);
    }
    $adm_id = es_cookie::get('adm_id');  //大后台登陆的管理员ID COOKIE 12小时有限

    if ($adm_id == 0) {
        if (empty($account_info)) {
// 		$cookie_aname = es_cookie::get.txt("account_name")?es_cookie::get.txt("account_name"):'';
// 		$cookie_apwd = es_cookie::get.txt("account_pwd")?es_cookie::get.txt("account_pwd"):'';

// 		if($cookie_aname!=''&&$cookie_apwd!=''&&!es_session::get("account_info"))
// 		{
// 			$cookie_aname = strim($cookie_aname);
// 			$cookie_apwd = strim($cookie_apwd);

// 			auto_do_login_biz($cookie_aname,$cookie_apwd);
// 			$account_info = es_session::get('account_info');
// 		}
            es_cookie::set("userurl", $_SERVER['REQUEST_URI'], 2);
            app_redirect(url("biz", "user#login"));
        }
    }
    //实时刷新会员数据
    if ($account_info) {
        $account_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_account where is_delete = 0 and is_effect = 1 and id = " . intval($account_info['id']));
        $account_info['platform_status'] = $GLOBALS['db']->getOne("select platform_status from " . DB_PREFIX . "supplier where id = " . $account_info['supplier_id']);
        if ($account_info['is_main'] == 1) { //主账户取所有门店
            $account_locations = $GLOBALS['db']->getAll("select id as location_id from " . DB_PREFIX . "supplier_location where supplier_id = " . $account_info['supplier_id']);
        } else {
            $account_locations = $GLOBALS['db']->getAll("select location_id from " . DB_PREFIX . "supplier_account_location_link where account_id = " . $account_info['id']);
        }
        $account_location_ids = array();
        foreach ($account_locations as $row) {
            $account_location_ids[] = $row['location_id'];
        }
        $account_info['location_ids'] = $account_location_ids;
        $GLOBALS['account_info']['location_ids'] = $account_location_ids;

        $sql = "SELECT  b.id,b.name
FROM
fanwe_supplier_location b 
LEFT JOIN fanwe_supplier_account_location_link c ON b.id = c.location_id
LEFT JOIN fanwe_supplier_account d ON c.account_id = d.id
WHERE
d.account_name = '" . $account_info['account_name'] . "'";
        $slid_info = $GLOBALS['db']->getRow($sql);
        if (!$slid_info) {
            $supplier_id = $account_info['supplier_id'];
            $sql = "SELECT id,`name` from fanwe_supplier_location where supplier_id=$supplier_id and is_main=1";
            $slid_info = $GLOBALS['db']->getRow($sql);
        }
        if (empty($_SESSION['fanweaccount_info']['slid'])) {
            $s_id = $slid_info['id'];
        } else {
            $s_id = $_SESSION['fanweaccount_info']['slid'];
        }

        if (empty($_SESSION['fanweaccount_info']['slname'])) {
            $name = $slid_info['name'];
        } else {
            $name = $_SESSION['fanweaccount_info']['slname'];
        }
        $slid = empty($_REQUEST['commercialId']) ? $s_id : $_REQUEST['commercialId'];
        $slname = empty($_REQUEST['commercialName']) ? $name : $_REQUEST['commercialName'];


        $GLOBALS['account_info']['slid'] = $slid;
        $GLOBALS['account_info']['slname'] = $slname;

        es_session::set('account_info', $account_info);
//        var_dump($_SESSION['fanweaccount_info']['slid']);die;

        $s_account_info = es_session::get("account_info");
        //var_dump($s_account_info);
    }

    if ($adm_id != 0)   //更新COOKIE
    {

        //echo $slid;

        $slid = es_cookie::get('admin_slid');

        $account_info['id'] = $GLOBALS['db']->getOne("select account_id from " . DB_PREFIX . "supplier_account_location_link where location_id=" . $slid); //根据传入ID查的对应的会员ID

        if ($account_info['id'] == NULL) {
            $supplier_id = $GLOBALS['db']->getOne("select supplier_id from " . DB_PREFIX . "supplier_location where id=" . $slid);
            $account_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_account where is_delete = 0 and is_effect = 1 and supplier_id='$supplier_id' and is_main=1");
        } else {
            $account_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_account where is_delete = 0 and is_effect = 1 and id = " . intval($account_info['id']));
        }


        $account_info['platform_status'] = $GLOBALS['db']->getOne("select platform_status from " . DB_PREFIX . "supplier where id = " . $account_info['supplier_id']);
        if ($account_info['is_main'] == 1) { //主账户取所有门店
            $account_locations = $GLOBALS['db']->getAll("select id as location_id from " . DB_PREFIX . "supplier_location where supplier_id = " . $account_info['supplier_id'] . " order by is_main asc");
        } else {
            $account_locations = $GLOBALS['db']->getAll("select location_id from " . DB_PREFIX . "supplier_account_location_link where account_id = " . $account_info['id']);
        }
        $account_location_ids = array();
        foreach ($account_locations as $row) {
            $account_location_ids[] = $row['location_id'];
        }

        //var_dump($account_location_ids);
        $account_info['location_ids'] = $account_location_ids;
        $GLOBALS['account_info']['location_ids'] = $account_location_ids;

        $GLOBALS['account_info'] = $account_info;
        //  $account_info['adm_id']=$adm_id;  //把管理员ID写入COOKIE

        es_session::set('account_info', $account_info);
        $s_account_info = es_session::get("account_info");

        $account_info = $s_account_info;
        //$slid=$GLOBALS['account_info']['location_ids'][0];


        $sql = "SELECT id,`name` from fanwe_supplier_location where id=$slid";
        $slid_info = $GLOBALS['db']->getRow($sql);
        $slid = $slid_info['id'];
        $slname = $slid_info['name'];

        $GLOBALS['account_info']['slid'] = $slid;
        $GLOBALS['account_info']['slname'] = $slname;
        es_cookie::set("account_name", $account_info['account_name'], 3600 * 1 * 1);
        es_cookie::set("slid", $slid, 3600 * 1 * 1);
        es_cookie::set("mname", $slname, 3600 * 1 * 1);
        es_cookie::set("uname", 'admin', 3600 * 1 * 1);
        es_cookie::set("account_name", $account_info['account_name'], 3600 * 1 * 1);
        es_cookie::set("account_pwd", md5($account_info['account_password'] . "_EASE_COOKIE"), 3600 * 1 * 1);
    }


}


//编译生成css文件
function parse_css($urls)
{
    $color_cfg = require_once APP_ROOT_PATH . "app/Tpl/kiz/color_cfg.php";
    $showurl = $url = md5(implode(',', $urls) . SITE_DOMAIN);
    $css_url = 'public/runtime/statics/kiz/' . $url . '.css';
    $pathwithoupublic = 'runtime/statics/kiz/';
    $url_path = APP_ROOT_PATH . $css_url;
    if (!file_exists($url_path) || IS_DEBUG) {
        if (!file_exists(APP_ROOT_PATH . 'public/runtime/statics/'))
            mkdir(APP_ROOT_PATH . 'public/runtime/statics/', 0777);
        if (!file_exists(APP_ROOT_PATH . 'public/runtime/statics/kiz/'))
            mkdir(APP_ROOT_PATH . 'public/runtime/statics/kiz/', 0777);
        $tmpl_path = $GLOBALS['tmpl']->_var['TMPL'];

        $css_content = '';
        foreach ($urls as $url) {
            $css_content .= @file_get_contents($url);
        }
        $css_content = preg_replace("/[\r\n]/", '', $css_content);
        $css_content = str_replace("../images/", $tmpl_path . "/images/", $css_content);
        foreach ($color_cfg as $k => $v) {
            $css_content = str_replace($k, $v, $css_content);
        }
        //		@file_put_contents($url_path, unicode_encode($css_content));
        @file_put_contents($url_path, $css_content);

        if ($GLOBALS['distribution_cfg']['CSS_JS_OSS'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] != "NONE") {
            syn_to_remote_file_server($css_url);
            $GLOBALS['refresh_page'] = true;
        }
    }
    if ($GLOBALS['distribution_cfg']['CSS_JS_OSS'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] != "NONE") {
        $domain = $GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN'];
    } else {
        $domain = SITE_DOMAIN . APP_ROOT;
    }
    return $domain . "/" . $css_url;
}

/**
 *
 * @param $urls 载入的脚本
 * @param $encode_url 需加密的脚本
 */
function parse_script($urls, $encode_url = array())
{
    $showurl = $url = md5(implode(',', $urls));
    $js_url = 'public/runtime/statics/kiz/' . $url . '.js';
    $pathwithoupublic = 'runtime/statics/kiz/';
    $url_path = APP_ROOT_PATH . $js_url;
    if (!file_exists($url_path) || IS_DEBUG) {
        if (!file_exists(APP_ROOT_PATH . 'public/runtime/statics/'))
            mkdir(APP_ROOT_PATH . 'public/runtime/statics/', 0777);
        if (!file_exists(APP_ROOT_PATH . 'public/runtime/statics/kiz/'))
            mkdir(APP_ROOT_PATH . 'public/runtime/statics/kiz/', 0777);

        if (count($encode_url) > 0) {
            require_once APP_ROOT_PATH . "system/libs/javascriptpacker.php";
        }

        $js_content = '';
        foreach ($urls as $url) {
            $append_content = @file_get_contents($url) . "\r\n";
            if (in_array($url, $encode_url)) {
                $packer = new JavaScriptPacker($append_content);
                $append_content = $packer->pack();
            }
            $js_content .= $append_content;
        }
        //		require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
        //	    $packer = new JavaScriptPacker($js_content);
        //		$js_content = $packer->pack();
        @file_put_contents($url_path, $js_content);
        if ($GLOBALS['distribution_cfg']['CSS_JS_OSS'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] != "NONE") {
            syn_to_remote_file_server($js_url);
            $GLOBALS['refresh_page'] = true;
        }
    }
    if ($GLOBALS['distribution_cfg']['CSS_JS_OSS'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] && $GLOBALS['distribution_cfg']['OSS_TYPE'] != "NONE") {
        $domain = $GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN'];
    } else {
        $domain = SITE_DOMAIN . APP_ROOT;
    }
    return $domain . "/" . $js_url;
}

/**
 * 获取短信发送的倒计时
 */
function load_sms_lesstime()
{
    $data = es_session::get("send_sms_code_0_ip");
    $lesstime = SMS_TIMESPAN - (NOW_TIME - $data['time']);  //剩余时间
    if ($lesstime < 0) $lesstime = 0;
    return $lesstime;
}

/**
 * 同一IP的短信验证码发送量，用于判断是否显示验证码
 */
function load_sms_ipcount()
{
    $sql = "DELETE FROM " . DB_PREFIX . "sms_mobile_verify WHERE add_time <=" . (NOW_TIME - SMS_EXPIRESPAN);
    $GLOBALS['db']->query($sql);
    $ipcount = $GLOBALS['db']->getOne("select sum(send_count) from " . DB_PREFIX . "sms_mobile_verify where ip = '" . CLIENT_IP . "'");
    return intval($ipcount);
}

function check_auth($module, $node)
{
    global_run();
    if (!$GLOBALS['account_info']) {
        return false;
    }
    $biznode_auth = require_once APP_ROOT_PATH . '/system/biz_cfg/' . APP_TYPE . '/biznode_cfg.php';
    if (OPEN_WEIXIN) {
        $weixin_conf = load_auto_cache("weixin_conf");
        if ($weixin_conf['platform_status'] == 1 && $GLOBALS['account_info']['platform_status'] == 1) {
            $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/wxbiznode_cfg.php";
            $biznode_auth = array_merge_biznode($biznode_auth, $config_file);
        }
    }
    if (OPEN_FX) {
        $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/fxbiznode_cfg.php";
        $biznode_auth = array_merge_biznode($biznode_auth, $config_file);
    }
    if (OPEN_DC) {
        $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/dcbiznode_cfg.php";
        $biznode_auth = array_merge_biznode($biznode_auth, $config_file);
    }
    $is_has = 0;
    foreach ($biznode_auth as $k => $v) {
        if ($module == $k) {
            foreach ($v['node'] as $kk => $vv) {
                if ($kk == $node) {
                    $is_has = 1;
                }
            }
        }
    }
    if (!$is_has) { //必须是权限列表中存在的
        return false;
    }

    $account_info = $GLOBALS['account_info'];
    $result = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "supplier_account_auth WHERE supplier_account_id=" . $account_info['id'] . " AND module='" . $module);
    if ($result) {
        return true;
    } else {
        return false;
    }

}

//左侧导航菜单
function assign_biz_nav_list()
{
    if (empty($GLOBALS['account_info']))
        return false;
// 	if(es_session::get("biz_nav_list")){
// 		$nav_list = unserialize(base64_decode(es_session::get("biz_nav_list"))); 
// 	}else

    {
        $nav_list = require APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/biznav_cfg.php";
        if (OPEN_WEIXIN) {
            $weixin_conf = load_auto_cache("weixin_conf");
            if ($weixin_conf['platform_status'] == 1 && $GLOBALS['account_info']['platform_status'] == 1) {
                $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/wxbiznav_cfg.php";
                $nav_list = array_merge_biznav($nav_list, $config_file);
            }
        }
        if (OPEN_FX) {
            $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/fxbiznav_cfg.php";
            $nav_list = array_merge_biznav($nav_list, $config_file);
        }
        if (OPEN_DC) {
            $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/dcbiznav_cfg.php";
            $nav_list = array_merge_biznav($nav_list, $config_file);
        }

        //==========================云创
        //if($GLOBALS['account_info']['id'] == '25')
        if ($_GET['w50'] || $_GET['ctl'] == 'wmembercard') {
            $nav_list['wmembercard'] = array(
                "name" => "会员卡",
                "iconfont" => "&#xe60a",
                "node" => array(
                    "wmembercard_list" => array("name" => "会员卡管理", "module" => "wmembercard", "action" => "index"),
                )
            );
        }

        if ($GLOBALS['account_info']['is_main']) {
            foreach ($nav_list as $k => $v) {
                $module_name = $k;
                foreach ($v['node'] as $kk => $vv) {
                    $module_name = $vv['module'];
                    $action_name = $vv['action'];
                    $nav_list[$k]['node'][$kk]['url'] = url("biz", $module_name . "#" . $action_name);

                }
            }
        } else {
            $result = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "supplier_account_auth WHERE supplier_account_id=" . $GLOBALS['account_info']['id']);
            if (empty($result)) {
                return false;
            }
            foreach ($result as $k => $v) {
                $has_module[$k]['module'] = $v['module'];
                $has_module[$k]['action'] = $v['node'];
            }
            //$has_module = array_unique($has_module);
            foreach ($nav_list as $k => $v) {
                $note_count = 0;
                $module_name = $k;
                foreach ($v['node'] as $kk => $vv) {
                    /*
                    if(in_array($kk, $has_module) || $v['name']=='配送中心'){
                        $module_name = $vv['module'];
                        $action_name = $vv['action'];


                        $nav_list[$k]['node'][$kk]['url'] = url("biz",$module_name."#".$action_name);
                        $note_count++;
                    }
                    else if($vv['module'] == 'goodsales' || $vv['module'] == 'order'){
                        $module_name = $vv['module'];
                        $action_name = $vv['action'];

                        $nav_list[$k]['node'][$kk]['url'] = url("biz",$module_name."#".$action_name);
                        $note_count++;
                    }
                    else{
                        unset($nav_list[$k]['node'][$kk]);
                    }

                    */
                    /*if(in_array($kk, $has_module)){
                        $module_name = $vv['module'];
                        $action_name = $vv['action'];

                        $nav_list[$k]['node'][$kk]['url'] = url("biz",$module_name."#".$action_name);
                        $note_count++;
                    }else{
                        unset($nav_list[$k]['node'][$kk]);
                    }*/
                    foreach ($has_module as $lk => $val) {
                        if ($vv['module'] == $val['module'] && $vv['action'] == $val['action']) {

                            $module_name = $vv['module'];
                            $action_name = $vv['action'];
                            //$nav_list[$k]['node'][$kk]['url'] = url("biz",$module_name."#".$action_name);
                            $node[$k]['node'][$kk]['name'] = $vv['name'];
                            $node[$k]['node'][$kk]['module'] = $vv['module'];
                            $node[$k]['node'][$kk]['action'] = $vv['action'];
                            $node[$k]['node'][$kk]['url'] = url("biz", $module_name . "#" . $action_name);
                            $note_count++;

                        } else {
                            unset($nav_list[$k]['node'][$kk]);
                        }

                    }
                    if (!empty($node)) {
                        $nav_list[$k]['node'] = $node;
                    }
                }
                if ($note_count == 0) {
                    unset($nav_list[$k]);
                }
            }
            foreach ($nav_list as $k => $v) {
                if (!empty($v["node"])) {
                    $nav_list[$k]['node'] = $node[$k]['node'];
                }

            }
        }
        //echo "<PRE>";print_r($nav_list);
        es_session::set("biz_nav_list", base64_encode(serialize($nav_list)));
    }


    $GLOBALS['tmpl']->assign("nav_list", $nav_list);
}

function get_biz_account_auth()
{
    $s_account_info = $GLOBALS["account_info"];
    if (es_session::get("biz_account_auth")) {
        $biz_account_auth = unserialize(base64_decode(es_session::get("biz_account_auth")));
        // var_dump($biz_account_auth);
    } else {
        $nav_list = require APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/biznav_cfg.php";
        if (OPEN_WEIXIN) {
            $weixin_conf = load_auto_cache("weixin_conf");
            if ($weixin_conf['platform_status'] == 1 && $GLOBALS['account_info']['platform_status'] == 1) {
                $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/wxbiznav_cfg.php";
                $nav_list = array_merge_biznav($nav_list, $config_file);
            }
        }
        if (OPEN_FX) {
            $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/fxbiznav_cfg.php";
            $nav_list = array_merge_biznav($nav_list, $config_file);
        }
        if (OPEN_DC) {
            $config_file = APP_ROOT_PATH . "system/biz_cfg/" . APP_TYPE . "/dcbiznav_cfg.php";
            $nav_list = array_merge_biznav($nav_list, $config_file);
        }
        if ($s_account_info['is_main']) {//管理员
            foreach ($nav_list as $k => $v) {
                $module_name = $k;
                foreach ($v['node'] as $kk => $vv) {
                    $module_name = $vv['module'];
                    $biz_account_auth[] = $module_name;
                }
            }
        } else {

            $result = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "supplier_account_auth WHERE supplier_account_id='" . $s_account_info['id'] . "' order by id asc");
            if (empty($result)) {
                return false;
            }
            foreach ($result as $k => $v) {
                $has_module[] = $v['module'];
            }
            $biz_account_auth = array_unique($has_module);
        }
        es_session::set("biz_account_auth", base64_encode(serialize($biz_account_auth)));
    }
    return $biz_account_auth;
}


function check_module_auth($module)
{
    //获取权限进行判断
    $biz_account_auth = get_biz_account_auth();
    //echo "匹配文本信息：$module";
    if (!in_array($module, $biz_account_auth)) {
        return false;
    } else {
        return true;
    }
}

?>