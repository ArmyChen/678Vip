<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_integralApiModule extends MainBaseApiModule
{
	
	/**
	 * 积分中心
	 */
	/*public function index()
	{  
		$root = array();		
		//数初始化
		$tag = intval($GLOBALS['request']['tag']);
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);			

		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){
		    $root['user_login_status'] = $user_login_status;	
		}
		else
		{
			$root['user_login_status'] = $user_login_status;		
			
			$ext_condition = '';
			$root['user']['avatar']=$_SESSION['fanweuser_info']['avatar'];
			$root['user']['id']=$_SESSION['fanweuser_info']['id'];
			$root['user']['name']=$_SESSION['fanweuser_info']['user_name'];
			$root['user']['score']=$_SESSION['fanweuser_info']['score'];//积分
			$root['user']['money']=$_SESSION['fanweuser_info']['money'];//余额
			$root['tag']=$tag;
		}

			//分页
			
		return output($root);
	}	*/
	public function index() {
		$root = array ();
		if (isDebug ()) {
			
			$out = array ();
			$out ["slid"] = "门店id";
			$out ["name"] = "门店名";
			$out ["preview"] = "门店头图";
			$root ["out"] = $out;
			
			output ( $root );
		}
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			
			$sql = "select fnu.id,fsl.name,fsl.preview from " . DB_PREFIX . "n1_users as fnu left join " . DB_PREFIX . "supplier_location as fsl on fnu.slid = fsl.id where fnu.user_id = $userId";
			$result_list = $GLOBALS ['db']->getAll ( $sql );
			print_r($result_list);
			if ($result_list) {
				
				$data_list = array ();
				foreach ( $result_list as $v ) {
					$v ['preview'] = get_abs_img_root ( $v ['preview'] );
					$data_list [] = $v;
				}
				$root [data] = $data_list;
				
				return output ( $root, 0, "成功" );
			} else {
				return output ( "", 1, "失败" );
			}
		}
	}
	
	
}
?>