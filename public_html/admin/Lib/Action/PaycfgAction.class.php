<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  含4个手机APP。
// +----------------------------------------------------------------------
// | 购买本程序，请联系QQ：78282385  旺旺名：alert988
// +----------------------------------------------------------------------
// | 淘宝购买地址：https://shop36624490.taobao.com/
// +----------------------------------------------------------------------

class PaycfgAction extends CommonAction{
	public function wechat()
	{
		
		$conf_res = M("Payconf")->where("group_id = 1 and is_effect = 1 and is_conf = 1")->order("sort asc")->findAll();
		
		foreach($conf_res as $k=>$v)

		{
			$v['value'] = htmlspecialchars($v['value']);
			$v['value_scope'] = $tmpls;
			$v['value_scope'] = explode(",",$v['value_scope']);
			$conf[$v['group_id']][] = $v;
		}
		
		$this->assign("conf",$conf);
		$this->display();
			
	}
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );			
				
				$list = M(MODULE_NAME)->where ( $condition )->delete();
				if ($list!==false) {
					
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
		
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	public function coupon()
	{
		if(strim($_REQUEST['msg'])!='')
		{
			$map['msg'] = array('like','%'.strim($_REQUEST['msg']).'%');			
		}
		if(strim($_REQUEST['query_id'])!='')
		{
			$map['query_id'] = strim($_REQUEST['query_id']);			
		}
		if(strim($_REQUEST['coupon_sn'])!='')
		{
			$map['coupon_sn'] = strim($_REQUEST['coupon_sn']);			
		}
		
		
		$this->assign("default_map",$map);
		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		//追加默认参数
		if($this->get("default_map"))
		$map = array_merge($map,$this->get("default_map"));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		
		$model = D ("CouponLog");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function foreverdeletelog() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );			
				
				$list = M("CouponLog")->where ( $condition )->delete();
				if ($list!==false) {
					
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
		
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
}
?>