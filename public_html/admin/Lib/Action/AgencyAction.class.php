<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class AgencyAction extends CommonAction{
	public function index()
	{
		$condition['is_delete'] = 0;
		$this->assign("default_map",$condition);
		
		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		//追加默认参数
		if($this->get("default_map"))
		$map = array_merge($map,$this->get("default_map"));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$list = $this->get("list");
		
		$result = array();
		$row = 0;
		foreach($list as $k=>$v)
		{
			$v['province_name']=M("DealCity")->where('id = '.$v['province_id'])->getField('name');
			$v['city_name']=M("DealCity")->where('id = '.$v['city_id'])->getField('name');
			$v['region_name']=M("Area")->where('id = '.$v['region_id'])->getField('name');
			$result[$row] = $v;

			$row++;
			
		}
		//dump($result);exit;
		$this->assign("list",$result);
		$this->display ();
		return;
	}
// 	public function trash()
// 	{
// 		$condition['is_delete'] = 1;
// 		$this->assign("default_map",$condition);
// 		parent::index();
// 	}
	public function add()
	{
		$province_list = M("DealCity")->where('pid = 0')->findAll();
		$city_list = M("DealCity")->where('pid != 0')->findAll();
		$region_list = M("Area")->where('pid = 0')->findAll();
		$this->assign("province_list",$province_list);
		$this->assign("city_list",$city_list);
		$this->assign("region_list",$region_list);
		$this->assign("new_sort", M("DealCity")->where("is_delete=0")->max("sort")+1);
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		$province_list = M("DealCity")->where('pid = 0')->findAll();
		$city_list = M("DealCity")->where('pid != 0')->findAll();
		$region_list = M("Area")->where('pid = 0')->findAll();
		$this->assign("province_list",$province_list);
		$this->assign("city_list",$city_list);
		$this->assign("region_list",$region_list);
        //生成邀请码规则
        $x = ($vo['id']+6)*7-8+678;
        $share_code=$x;
		$this->assign("share_code",$share_code);
		$this->display ();
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
		
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
				//删除相关预览图
//				foreach($rel_data as $data)
//				{
//					@unlink(get_real_path().$data['preview']);
//				}			
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function insert() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$data['account_password'] = md5($data['account_password']);
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("名称不能为空"));
		}	
		
		/*
		$ref_mobile = strim($_REQUEST['ref_mobile']);;
		$ref_user_info =  M("User")->where("mobile='".$ref_mobile."' and is_delete = 0")->field("user_name,id,mobile")->find();
		if($ref_user_info){
			$data['ref_user_id']=$ref_user_info['id'];
			$data['ref_user_name']=$ref_user_info['user_name'];
			$data['ref_mobile']=$ref_user_info['mobile'];
		}else{
			$data['ref_user_id']=0;
			$data['ref_user_name']='';
			$data['ref_mobile']='';
		}
		*/
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$DBerr,0);
			$this->error(L("INSERT_FAILED").$DBerr);
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();

		if($_REQUEST['account_password']){

			$data['account_password'] = md5($_REQUEST['account_password']);
		}else{
			unset($data['account_password']);
		}
		
		/*
		$ref_mobile = strim($_REQUEST['ref_mobile']);;
		$ref_user_info =  M("User")->where("mobile='".$ref_mobile."' and is_delete = 0")->field("user_name,id,mobile")->find();
		if($ref_user_info){
			$data['ref_user_id']=$ref_user_info['id'];
			$data['ref_user_name']=$ref_user_info['user_name'];
			$data['ref_mobile']=$ref_user_info['mobile'];
		}else{
			$data['ref_user_id']=0;
			$data['ref_user_name']='';
			$data['ref_mobile']='';
		}
		*/
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("名称不能为空"));
		}	
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			 
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$DBerr,0);
			$this->error(L("UPDATE_FAILED").$DBerr,0);
		}
	}


	
	public function get_city_list()
	{
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		$province_id = intval($_REQUEST['province_id']);
		if($province_id > 0){
			$city_list = M("DealCity")->where('pid = '.$province_id)->findAll();
			$this->assign("city_list",$city_list);
			
		}
		$html=$this->fetch();
		ajax_return($html);
	}
	
	public function get_region_list()
	{
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$city_id = intval($_REQUEST['city_id']);
		
		if($city_id > 0){
			$region_list = M("Area")->where('pid = 0 and city_id='.$city_id)->findAll();
			$this->assign("region_list",$region_list);
		}
		$html=$this->fetch();
		ajax_return($html);
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
	}
	
	/*
	 * 代理商提现
	 */
	public function withdrawal_index()
	{
	    if(isset($_REQUEST['is_paid']))
	    {
	        if(intval($_REQUEST['is_paid'])==0)
	        {
	            $map['is_paid'] = intval($_REQUEST['is_paid']);
	        }
	    }
		$map['is_delete'] = 0;
	    $model = D ("AgencyMoneySubmit");
	    if (! empty ( $model )) {
	        $this->_list ( $model, $map );
	    }
	    $this->display ();
	    return;
	}
	
	
	/*
	 * 代理商提现编辑
	 */
	public function withdrawal_edit()
	{
	    $id = intval($_REQUEST['id']);
	
	    $withdrawal_info = M("AgencyMoneySubmit")->getById($id);
	    $agency_info= M("Agency")->where("id=".$withdrawal_info['agency_id'])->find();
	    $withdrawal_info['agency_name']=$agency_info['name'];
	    $this->assign("withdrawal_info",$withdrawal_info);
	    $this->display();
	}
	
	
	/*
	 * 代理商提现审核
	 */
	public function do_withdrawal()
	{
	    $id = intval($_REQUEST['id']);
	    $log=strim($_REQUEST['log']);
	    require_once APP_ROOT_PATH."system/model/user.php";
	
	    $withdrawal_info = M("AgencyMoneySubmit")->getById($id);
	    $agency_info=M("Agency")->getById($withdrawal_info['agency_id']);
	
	    if($withdrawal_info['is_paid']==0)
	    {
	        M("AgencyMoneySubmit")->where("id=".$id)->setField("is_paid",1);
	        M("AgencyMoneySubmit")->where("id=".$id)->setField("pay_time",NOW_TIME);
	      $id_arr=explode(",",$withdrawal_info['id_str']);
	       foreach($id_arr as $k=>$v){
	           M("AgencyStatements")->where("id=".$v)->setField("wd_status",1);
	       }
	       modify_agency_account($withdrawal_info['money'],$withdrawal_info['agency_id'],4,$agency_info['name']."提现".format_price($withdrawal_info['money']));
	        save_log($agency_info['name']."提现".format_price($withdrawal_info['money'])."审核通过。".$log,1);
	        $this->success("确认提现成功");
	    }
	    else
	    {
	        $this->error("已提现过，无需再次提现");
	    }
	
	
	}
	/*
	 * 代理商提现审核不通过
	 */
	public function not_withdrawal()
	{
	    $id = intval($_REQUEST['id']);
	    $log=strim($_REQUEST['log']);
	    require_once APP_ROOT_PATH."system/model/user.php";
	
	    $withdrawal_info = M("AgencyMoneySubmit")->getById($id);
	    $agency_info=M("Agency")->getById($withdrawal_info['agency_id']);
	
	    if($withdrawal_info['is_paid']==0)
	    {
	        M("AgencyMoneySubmit")->where("id=".$id)->setField("is_paid",2);
	        M("AgencyMoneySubmit")->where("id=".$id)->setField("pay_time",NOW_TIME);
	      $id_arr=explode(",",$withdrawal_info['id_str']);
	       foreach($id_arr as $k=>$v){
	           M("AgencyStatements")->where("id=".$v)->setField("wd_status",0);
	       }
	        save_log($agency_info['name']."提现".format_price($withdrawal_info['money'])."审核不通过。".$log,1);
	        $this->success("拒绝成功",1);
	    }
	    else
	    {
	        $this->error("已经过审核，无需再次审核");
	    }
	
	
	}
	
	
	public function del_withdrawal()
	{
	    $id = intval($_REQUEST['id']);
	    $withdrawal = M("AgencyMoneySubmit")->getById($id);

	    $list = M("AgencyMoneySubmit")->where ("id=".$id )->setField("is_delete",1);
		save_log($withdrawal['agency_id']."号代理商提现".format_price($withdrawal['money'])."记录".l("FOREVER_DELETE_SUCCESS"),1);
	    $this->success (l("FOREVER_DELETE_SUCCESS"),1);
		
		/* $list = M("AgencyMoneySubmit")->where ("id=".$id )->delete();
	    if ($list!==false) {
	        if($withdrawal['is_paid']==0){
	            $id_arr=explode(",",$withdrawal['id_str']);
	            foreach($id_arr as $k=>$v){
	                M("AgencyStatements")->where("id=".$v)->setField("wd_status",0);
	            }
	        }
	        save_log($withdrawal['agency_id']."号代理商提现".format_price($withdrawal['money'])."记录".l("FOREVER_DELETE_SUCCESS"),1);
	        $this->success (l("FOREVER_DELETE_SUCCESS"),1);
	    } */ 
	
	}
	
	
	public function get_ref_user()
	{
		$mobile = strim($_REQUEST['mobile']);;
		$ref_user_info =  M("User")->where("mobile='".$mobile."' and is_delete = 0")->field("user_name,id,mobile")->find();
	
		if(!$ref_user_info)
			ajax_return(l("NO_USER"));
		else
			$str= "<a target='_blank' href='".u("User/index",array("user_name"=>$ref_user_info['user_name']))."'>".$ref_user_info['user_name']."</a>";
		
			ajax_return($str);
	}
	
}
?>