<?php 

// +----------------------------------------------------------------------

// | Fanwe 方维o2o商业系统

// +----------------------------------------------------------------------

// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: 云淡风轻(97139915@qq.com)

// +----------------------------------------------------------------------

use Qiniu\Auth;

require __DIR__.'/autoload.php';

class TizBaseModule{

	public function __construct()

	{

		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ES_FILE")

		{

			global $syn_image_ci;

			global $curl_param;

			//global $syn_image_idx;

			$syn_image_idx = 0;

			$syn_image_ci  =  curl_init($GLOBALS['distribution_cfg']['OSS_DOMAIN']."/es_file.php");

			curl_setopt($syn_image_ci, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYHOST, false);

			curl_setopt($syn_image_ci, CURLOPT_NOPROGRESS, true);

			curl_setopt($syn_image_ci, CURLOPT_HEADER, false);

			curl_setopt($syn_image_ci, CURLOPT_POST, TRUE);

			curl_setopt($syn_image_ci, CURLOPT_TIMEOUT, 1);

			curl_setopt($syn_image_ci, CURLOPT_TIMECONDITION, 1);

			$curl_param['username'] = $GLOBALS['distribution_cfg']['OSS_ACCESS_ID'];

			$curl_param['password'] = $GLOBALS['distribution_cfg']['OSS_ACCESS_KEY'];

			$curl_param['act'] = 2;

		}

		

		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);

		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);

		

		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");

		$GLOBALS['dynamic_cache'] = $GLOBALS['cache']->get("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME);

		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");

		$GLOBALS['dynamic_avatar_cache'] = $GLOBALS['cache']->get("AVATAR_DYNAMIC_CACHE"); //头像的动态缓存

		if(

				MODULE_NAME=="account"&&ACTION_NAME=="index"||

				MODULE_NAME=="balance"&&ACTION_NAME=="index"||

				MODULE_NAME=="bankinfo"&&ACTION_NAME=="index"||

				MODULE_NAME=="deal"&&ACTION_NAME=="index"||

				MODULE_NAME=="dealo"&&ACTION_NAME=="index"||

				MODULE_NAME=="dealr"&&ACTION_NAME=="index"||

				MODULE_NAME=="dealv"&&ACTION_NAME=="index"||

				MODULE_NAME=="delivery"&&ACTION_NAME=="index"||

				MODULE_NAME=="event"&&ACTION_NAME=="index"||

				MODULE_NAME=="evento"&&ACTION_NAME=="index"||

				MODULE_NAME=="eventr"&&ACTION_NAME=="index"||

				MODULE_NAME=="eventv"&&ACTION_NAME=="index"||

				MODULE_NAME=="goods"&&ACTION_NAME=="index"||

				MODULE_NAME=="goodso"&&ACTION_NAME=="index"||

				MODULE_NAME=="index"&&ACTION_NAME=="index"||

				MODULE_NAME=="location"&&ACTION_NAME=="index"||

				MODULE_NAME=="storer"&&ACTION_NAME=="index"||

				MODULE_NAME=="withdrawal"&&ACTION_NAME=="index"||

				MODULE_NAME=="wxconf"&&ACTION_NAME=="index"||

				MODULE_NAME=="youhui"&&ACTION_NAME=="index"||

				MODULE_NAME=="youhuio"&&ACTION_NAME=="index"||

				MODULE_NAME=="youhuir"&&ACTION_NAME=="index"||

				MODULE_NAME=="youhuiv"&&ACTION_NAME=="index"||

                MODULE_NAME=="inventory"&&ACTION_NAME=="index"||

                MODULE_NAME=="report"&&ACTION_NAME=="index"


		)

		set_biz_gopreview();

	}

    public function get_update_token(){
        // 用于签名的公钥和私钥
        $accessKey = 'B-A4mcgEPAH8V99AuRQNCf9O47G4x-cdhZVK4atc';
        $secretKey = '4be7iIt4RtA32QIK2WKTWmqLNN1ZXpKKPX4nG0Ih';
        $bucket = '678sh';

        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);
        $token = $auth->uploadToken($bucket);
        return $token;
    }

	public function index()

	{

		showErr("invalid access");

	}

	public function __destruct()

	{

		if(isset($GLOBALS['cache']))

		{

			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");

			$GLOBALS['cache']->set("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME,$GLOBALS['dynamic_cache']);

			if(count($GLOBALS['dynamic_avatar_cache'])<=500)

			{

				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");

				$GLOBALS['cache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存

			}

		}

		

		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ES_FILE")

		{

			if(count($GLOBALS['curl_param']['images'])>0)

			{

				$GLOBALS['curl_param']['images'] =  base64_encode(serialize($GLOBALS['curl_param']['images']));

				curl_setopt($GLOBALS['syn_image_ci'], CURLOPT_POSTFIELDS, $GLOBALS['curl_param']);

				$rss = curl_exec($GLOBALS['syn_image_ci']);

			}

			curl_close($GLOBALS['syn_image_ci']);

		}

		if($GLOBALS['refresh_page']&&!IS_DEBUG)

		{

			echo "<script>location.reload();</script>";

			exit;

		}

		unset($this);

	}

	

	/**

	 * 验证用户权限

	 */

	protected function check_auth()

	{

	    $ajax = intval($_REQUEST['ajax']);

		$s_account_info = $GLOBALS['account_info'];

		

		if(intval($s_account_info['id'])==0)

		{

		    showBizErr("没有登录商户账户，请先登录!",$ajax,url("biz","user#login"));

		}

		else

		{

		   //获取权限进行判断
 $userqx=check_module_auth(MODULE_NAME);
 //echo "权限调试输出：$userqx";
		   if(!$userqx){

		       showBizErr("没有操作模块的权限，请更换有权限的账户登录!",$ajax);

		   }

		}

	}

    public function init(){
        $slid=$GLOBALS['account_info']['slid'];
        $slname=$GLOBALS['account_info']['slname'];
        $opreview = $GLOBALS['config']['ERP_LOGO'];
        $supplier_id = $GLOBALS['account_info']['supplier_id'];
        define("SLIDNAME",$slname);
        define("SLID",$slid);

        $preview=$GLOBALS['db']->getOne("select preview from fanwe_supplier_location where id=".$slid);
        if ($preview==""){
            $preview="http://www.678sh.com/app/Tpl/biz/img/logo.jpg";
        }

        $GLOBALS['tmpl']->assign("preview",$preview);
        $GLOBALS['tmpl']->assign("opreview",$opreview);
        $GLOBALS['tmpl']->assign("supplier_name",$slname);
        $GLOBALS['tmpl']->assign("account_info",$GLOBALS['account_info']);
//        var_dump($_SESSION['fanweaccount_info']);die;
        $GLOBALS['tmpl']->assign("biz_gen_qrcode",gen_qrcode(SITE_DOMAIN.url("biz","downapp"),app_conf("QRCODE_SIZE")));


        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
        $GLOBALS['tmpl']->assign("slidlist", $slidlist);

        $gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
        $sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";

        $gyslist=$GLOBALS['db']->getAll($sql_gys);
        $GLOBALS['tmpl']->assign("gyslist", $gyslist);

        $location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
        $GLOBALS['tmpl']->assign("location_gys", $location_gys);

        $location_bumen=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_bumen where slid=".$slid);
        $GLOBALS['tmpl']->assign("location_bumen", $location_bumen);
    }


    /**
     * 获取键值
     * @param $arrays
     * @param $akey
     * @return mixed
     */
    function getCollectionValue($arrays,$akey){
        foreach ($arrays as $key=>$item) {
            if($key == $akey){
                return $item;
            }
        }
    }

    //$html-被查找的字符串 $tag-被查找的标签 $attr-被查找的属性名 $value-被查找的属性值
    function get_tag_data($html,$tag,$attr,$value){
        $regex = "/.*value=\"(.*)?\".*/is";
        preg_match_all($regex,$html,$matches,PREG_PATTERN_ORDER);
        return $matches[1];
    }


    /**

     * $str 原始中文字符串

     * $encoding 原始字符串的编码，默认utf-8

     * $prefix 编码后的前缀，默认"&#"

     * $postfix 编码后的后缀，默认";"

     */

    function unicode_encode($str, $encoding = 'utf-8', $prefix = '&#', $postfix = ';') {

        //将字符串拆分

        $str = iconv("UTF-8", "gb2312", $str);

        $cind = 0;

        $arr_cont = array();



        for ($i = 0; $i < strlen($str); $i++) {

            if (strlen(substr($str, $cind, 1)) > 0) {

                if (ord(substr($str, $cind, 1)) < 0xA1) { //如果为英文则取1个字节

                    array_push($arr_cont, substr($str, $cind, 1));

                    $cind++;

                } else {

                    array_push($arr_cont, substr($str, $cind, 2));

                    $cind+=2;

                }

            }

        }

        foreach ($arr_cont as &$row) {

            $row = iconv("gb2312", "UTF-8", $row);

        }



        //转换Unicode码

        foreach ($arr_cont as $key => $value) {

            $unicodestr.= $prefix . base_convert(bin2hex(iconv('utf-8', 'UCS-4', $value)), 16, 10) .$postfix;

        }



        return $unicodestr;

    }



    /**

     * $str Unicode编码后的字符串

     * $decoding 原始字符串的编码，默认utf-8

     * $prefix 编码字符串的前缀，默认"&#"

     * $postfix 编码字符串的后缀，默认";"

     */

    function unicode_decode($unistr, $encoding = 'utf-8', $prefix = '&#', $postfix = ';') {

        $arruni = explode($prefix, $unistr);

        $unistr = '';

        for ($i = 1, $len = count($arruni); $i < $len; $i++) {

            if (strlen($postfix) > 0) {

                $arruni[$i] = substr($arruni[$i], 0, strlen($arruni[$i]) - strlen($postfix));

            }

            $temp = intval($arruni[$i]);

            $unistr .= ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256);

        }

        return iconv('UCS-2', $encoding, $unistr);

    }


    function check_zffs($zffs,$zffsarr){
        foreach($zffsarr as $k=>$v){
            if ($k==$zffs){
                return true;
            }
        }
    }
}

?>