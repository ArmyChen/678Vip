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
     * 商品分类ajax
     */
    public function goods_category_tree_ajax(){
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        //分类
        $sortconditions = " where is_effect = 0 and  wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
        $sortconditions .= " and location_id=".$slid;
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
        $sqlsort.=$sortconditions. " order by sort desc";

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

        $listsort = toFormatTree($wmenulist,"name");
        return $listsort;
    }

    /**
     * 获取仓库列表
     * @param int $id
     * @return
     */
    public function get_cangku_list($id=0){
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        if($id>0){
            $cangku=$GLOBALS['db']->getRow("select id,name from fanwe_cangku where id=".$id);
            return $cangku;
        }
        /* 系统默认 */
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        return $cangkulist;
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

    /**
     * 获取单位列表
     * @param int $slid
     * @return mixed
     */
    function get_unit_list($slid = 0){
        $unitList = " select id,name,is_effect,sort from fanwe_dc_supplier_unit_cate  where 1 = 1";
        if($slid > 0) {
            $check = $GLOBALS['db']->getAll($unitList." and location_id=".$slid);
            return $check;
        }else{
            $check = $GLOBALS['db']->getAll($unitList);
            return $check;
        }
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

    /**
     * 根据id下级分类
     * @param $id
     * @return mixed
     */
    function get_dc_supplier_cate($id){
        if($id > 0) {
            $res = $id;
            $check = $GLOBALS['db']->getAll("select * from fanwe_dc_supplier_menu_cate where wcategory=".$id);
            foreach ($check as $item) {
                if($item){
                    $res .= ','.$item['id'];
                }
            }
            return $res;
        }

        return null;
    }

    /**
     * 根据id当前分类
     * @param $id
     * @return mixed
     */
    function get_dc_current_supplier_cate($id){
        if($id > 0) {
            $res = $id;
            $check = $GLOBALS['db']->getRow("select * from fanwe_dc_supplier_menu_cate where id=".$id);
            return $check;
        }

        return null;
    }

    /**
     * 根据id当前分类
     * @param $id
     * @return mixed
     */
    function get_dc_current_gys_cate($id){
        if($id > 0) {
            $res = $id;
            $check = $GLOBALS['db']->getRow("select * from fanwe_gys_category where id=".$id);
            return $check;
        }

        return null;
    }


    /**
     * 获取部门列表
     */
    function get_bumen_list($slid){
        $location=$GLOBALS['db']->getAll("select * from fanwe_cangku_bumen where slid=".$slid);
        return $location;


    }

    /**
     * 获取供应商列表
     */
    function get_gys_list($slid){
        $location=$GLOBALS['db']->getAll("select * from fanwe_cangku_gys where slid=".$slid);
        return $location;


    }

    function get_gonghuoren_name($supplier_id,$slid,$gonghuoren){

        $linshi=array(
            "1"=>"临时客户",
            "2"=>"临时运输商",
            "3"=>"临时供应商"
        );

        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
        $slid_names = array();
        $slid_names = array_reduce($slidlist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
        $sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";
        $gyslist=$GLOBALS['db']->getAll($sql_gys);


        $city_names = array();
        $city_names = array_reduce($gyslist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
        $local_names = array();
        $local_names = array_reduce($location_gys, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $location_bumen=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_bumen where slid=".$slid);
        $local_bumen = array();
        $local_bumen = array_reduce($location_bumen, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $gonghuoren_arr=explode('_',$gonghuoren);
        $gonghuoren_type=$gonghuoren_arr[0];
        $gonghuoren_id=$gonghuoren_arr[1];
        if ($gonghuoren_type=='linshi'){
            $gys_name=$linshi[$gonghuoren_id];
        }elseif($gonghuoren_type=='slid'){
            $gys_name=$slid_names[$gonghuoren_id];
        }elseif($gonghuoren_type=='citygys'){
            $gys_name=$city_names[$gonghuoren_id];
        }elseif($gonghuoren_type=='localgys'){
            $gys_name=$local_names[$gonghuoren_id];
        }elseif($gonghuoren_type=='bumen'){
            $gys_name=$local_bumen[$gonghuoren_id];
        }elseif($gonghuoren_type=='other'){
            $gys_name=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$gonghuoren_id);
        }elseif($gonghuoren_type=='user'){
            $gys_name='存货用户：'.$GLOBALS['db']->getOne("select user_name from fanwe_user where id=".$gonghuoren_id);
        }
        return $gys_name;
    }

    //$html-被查找的字符串 $tag-被查找的标签 $attr-被查找的属性名 $value-被查找的属性值
    function get_tag_data($html,$tag,$attr,$value){
        $regex = "/.*value=\"(.*)?\".*/is";
        preg_match_all($regex,$html,$matches,PREG_PATTERN_ORDER);
        return $matches[1];
    }

    /**
     * 获取仓库列表
     * @param int $id
     * @return
     */
    public function get_count_template_list($id=0){
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        if($id>0){
            $pandian=$GLOBALS['db']->getRow("select id,name from fanwe_cangku_pandian_mb where id=".$id);
            return $pandian;
        }
        /* 系统默认 */
        $pandianlist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_pandian_mb where slid=".$slid);
        return $pandianlist;
    }

    /**
     * 查询报废原因
     * @param int $id
     * @return
     */
    public function get_basic_reason_list($id=0,$type=1){
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        if($id>0){

            $pandian=$GLOBALS['db']->getRow("select id,content from fanwe_basic_reason where id=".$id);
            return $pandian;
        }
        $where = " 1=1";
        if($type > 0){//退回原因
            $where .=" and reasonType=$type";
        }
        /* 系统默认 */
        $pandianlist=$GLOBALS['db']->getAll("select id,content from fanwe_basic_reason where $where and slid=".$slid);
        return $pandianlist;
    }

    /**
     * 获取商品列表
     * @param int $id
     * @return
     */
    public function get_basic_goods_list($id=0,$type=1,$where='',$print=1){
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        if($id>0){
            $pandian=$GLOBALS['db']->getRow("select * from fanwe_dc_menu where id=".$id);
            return $pandian;
        }
        if(empty($where)){//退回原因
            $where .= ' 1=1';
        }
        /* 系统默认 */
        $list=$GLOBALS['db']->getAll("select * from fanwe_dc_menu where $where and location_id=".$slid);
        return $list;
    }
//$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_product_mb  $str order by id limit $limit desc ");
    /**
     * 获取生产模板列表
     * @param int $id
     * @return
     */
    public function get_product_template_list($id=0){
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        if($id>0){
            $pandian=$GLOBALS['db']->getRow("select id,name from fanwe_cangku_product_mb where id=".$id);
            return $pandian;
        }
        /* 系统默认 */
        $pandianlist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_product_mb where slid=".$slid);
        return $pandianlist;
    }
}

?>