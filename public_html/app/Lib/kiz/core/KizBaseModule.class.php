<?php 

// +----------------------------------------------------------------------

// | Fanwe 方维o2o商业系统

// +----------------------------------------------------------------------

// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: 云淡风轻(97139915@qq.com)

// +----------------------------------------------------------------------



class KizBaseModule{

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

    }

    /**
     * 根据mid查询仓库信息
     * @param $id
     * @return mixed
     */
	public function getCangkuMenuInfoByMid($mid){
        if($mid > 0){
            $sql = "select * from fanwe_cangku_menu where mid=".$mid;
            $result = $GLOBALS['db']->getRow($sql);
            return $result;
        }
        return null;
    }

    /**
     * 根据mid查询商品信息
     * @param $id
     * @return mixed
     */
    public function getDcMenuInfoByMid($id){
        if($id > 0){
            $sql = "select * from fanwe_dc_menu where id=".$id;
            $result = $GLOBALS['db']->getRow($sql);
            return $result;
        }
        return null;
    }


    /**
     * 根据id查询分类信息
     * @param $id
     * @return mixed
     */
    function get_dc_supplier_menu($id){
        if($id > 0) {
            $check = $GLOBALS['db']->getRow("select * from fanwe_dc_supplier_menu_cate where id = " . $id);
            if ($check) {
                return $check;
            } else {
                return '';
            }
        }
        return null;
    }
}

?>