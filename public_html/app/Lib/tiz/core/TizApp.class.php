<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  含4个手机APP。
// +----------------------------------------------------------------------
// | 购买本程序，请联系QQ：78282385  旺旺名：alert988
// +----------------------------------------------------------------------
// | 淘宝购买地址：https://shop36624490.taobao.com/
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/tiz/core/TizBaseModule.class.php';
require APP_ROOT_PATH.'app/Lib/tiz/core/tiz_init.php';
define("CTL",'ctl');
define("ACT",'act');

class TizApp{
	private $module_obj;
	//网站项目构造
	public function __construct(){
		if($GLOBALS['pay_req'][CTL])
			$_REQUEST[CTL] = $GLOBALS['pay_req'][CTL];
		if($GLOBALS['pay_req'][ACT])
			$_REQUEST[ACT] = $GLOBALS['pay_req'][ACT];
		
		$module = strtolower($_REQUEST[CTL]?$_REQUEST[CTL]:"inventory");
		$action = strtolower($_REQUEST[ACT]?$_REQUEST[ACT]:"go_down_index");
		
		$module = filter_ctl_act_req($module);
		$action = filter_ctl_act_req($action);
		if(!file_exists(APP_ROOT_PATH."app/Lib/tiz/".$module."Module.class.php"))
		$module = "inventory";


		require_once APP_ROOT_PATH."app/Lib/tiz/".$module."Module.class.php";

		if(!class_exists($module."Module"))
		{
			$module = "inventory";
			require_once APP_ROOT_PATH."app/Lib/tiz/".$module."Module.class.php";
		}
		if(!method_exists($module."Module",$action))
		$action = "go_down_index";

		define("MODULE_NAME",$module);
		define("ACTION_NAME",$action);
		
		$module_name = $module."Module";
		$this->module_obj = new $module_name;
		$this->module_obj->$action();
	}
	
	public function __destruct()
	{
		unset($this);
	}
}
?>