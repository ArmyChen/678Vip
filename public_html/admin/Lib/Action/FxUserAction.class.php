<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class FxUserAction extends CommonAction{

	public function index()
	{
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		
		//定义条件
		$map[DB_PREFIX.'user.is_delete'] = 0;

		if(intval($_REQUEST['user_id'])>0)
		{
			$map[DB_PREFIX.'user.pid'] = intval($_REQUEST['user_id']);
		}		
		
		if(intval($_REQUEST['group_id'])>0)
		{
			$map[DB_PREFIX.'user.group_id'] = intval($_REQUEST['group_id']);
		}
		
		if(strim($_REQUEST['user_name'])!='')
		{
			$map[DB_PREFIX.'user.user_name'] = array('eq',strim($_REQUEST['user_name']));
		}
		if(strim($_REQUEST['email'])!='')
		{
			$map[DB_PREFIX.'user.email'] = array('eq',strim($_REQUEST['email']));
		}
		if(strim($_REQUEST['mobile'])!='')
		{
			$map[DB_PREFIX.'user.mobile'] = array('eq',strim($_REQUEST['mobile']));
		}
		if(strim($_REQUEST['pid_name'])!='')
		{
			$pid = M("User")->where("user_name='".strim($_REQUEST['pid_name'])."'")->getField("id");
			$map[DB_PREFIX.'user.pid'] = $pid;
		}
		
	
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ("User");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
	}

	public function edit_referrer()
	{
		$user_id = intval($_REQUEST['id']);
		$user_pid = M("User")->where("id=".$user_id)->getField("pid");
		$referrer_name = M("User")->where("id=".$user_pid)->getField("user_name");
		$this->assign("referrer_name",$referrer_name);
		$this->assign("id",$user_id);
		$this->display();
	}
	
	public function update_referrer()
	{
		$user_id=intval($_REQUEST['id']);
		$referrer=strim($_REQUEST['referrer']);

		$pid = M("User")->where("user_name='".$referrer."'")->getField("id");
		if($pid==$user_id){
				$this->error ("推荐人不能是自己",0);
		}
		if($pid >0||$referrer==""){
				M("User")->where("id=".$user_id)->setField("pid",$pid);
				save_log($user_id."号会员更改推荐人为".$referrer,1);				 
				$this->success ("设置成功",0);
		}else{
				$this->error ("推荐人不存在",0);
		}
		
	}

	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		
			if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("UserDeal")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['user_id'];	
				}
				if($info) $info = implode(",",$info);
				$ids = explode ( ',', $id );
				foreach($ids as $uid)
				{
					$GLOBALS['db']->query("delete from ".DB_PREFIX."user_deal where id = ".$uid);
				}
				save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
				 
				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
			}
	}
	
		
	

	//会员分销状态设置
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("user_name");
		$c_is_effect = M("User")->where("id=".$id)->getField("is_fx");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M("User")->where("id=".$id)->setField("is_fx",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	//商品分销状态设置
	public function set_deal_effect()
	{
		$id = intval($_REQUEST['id']);
		
		$info = M("UserDeal")->where("id=".$id)->find();
		$c_is_effect = $info["is_effect"];  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M("UserDeal")->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info["user_id"]."号会员".$info["deal_id"]."号分销商品".l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	/**
	 * 会员已上架分销商品列表
	 */
	public function deal_index(){
		$user_id=intval($_REQUEST['user_id']);
		$list = $GLOBALS['db']->getAll("select d.name,ud.* from ".DB_PREFIX."user_deal as ud left join ".DB_PREFIX."deal as d on ud.deal_id = d.id where ud.user_id = ".$user_id." and d.is_effect = 1 and d.is_delete = 0 order by d.sort asc,d.id asc");		
	    $user_name=M("User")->where("id=".$user_id)->getField("user_name");
	    $this->assign("user_name",$user_name);
		$this->assign("list",$list);
		$this->assign("user_id",$user_id);
		$this->assign("title_name",$user_name."的分销商品");
	    
	    $this->display();
	}
	
	public function add_deal(){
	    $user_id = intval($_REQUEST['user_id']);

	    $this->assign("user_id",$user_id);
	    $this->display();
	}

	public function load_seach_deal(){

	    $param = array();	    
	    
	    if(strim($_REQUEST['name'])!='')
	    {
	        $param['name'] = strim($_REQUEST['name']);
	        $map['name'] = array('like','%'.strim($_REQUEST['name']).'%');
	    }

	    
		if(strim($_REQUEST['supplier_name'])!='')
		{
			if(intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier"))<50000)
			$sql  ="select group_concat(id) from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['supplier_name'])."%'";
			else 
			{
				$kws_div = div_str(trim($_REQUEST['supplier_name']));
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$kw_unicode = implode(" ",$kw);
				$sql = "select group_concat(id) from ".DB_PREFIX."supplier where (match(name_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			}
			$ids = $GLOBALS['db']->getOne($sql);
			$map['supplier_id'] = array("in",$ids);
		}		    
	    
	    $deal_ids=$GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."user_deal where user_id=".intval($_REQUEST['user_id']));
	    $map['publish_wait'] = 0;
	    $map['is_fx'] = 2;
	    $map['is_delete'] = 0;
	    $map['is_effect'] = 1;
	    $map['buy_type'] = 0;
		if($deal_ids!="")$map['id'] = array("not in",$deal_ids);
	    
	    /*获取参数*/
	    $page = intval($_REQUEST['p']); //分页
	    $page=$page==0?1:$page;
	    
	    //分页
	    $page_size = 8;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    
	    $model = D ('Deal');
	    $voList = $model->where($map)->order('id desc')->limit($limit)->field('id,name,is_fx,current_price,is_fx')->findAll();

	    $count = $model->where($map)->count();// 查询满足要求的总记录数

	    //分页
	    $page_total = ceil($count/$page_size);

	    if($page_total>0){
	        $page = new Page($count,$page_size);  
	        foreach($param as $key=>$val) {
	            $page->parameter .= "$key=".urlencode($val).'&';
	        }
	        $p  =  $page->show();
	        
	        $this->assign('pages',$p);
	    }

	    $this->assign('vo',$voList);
	    $this->display();
	}	
	
	
	/**
	 * 添加
	 */
	public function save(){

			//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$user_id = intval($_REQUEST ['user_id']);
		$ids=strim($_REQUEST ['check_ids']);
		
		if (isset ( $ids )) {
			$condition = "user_id=".$user_id." and deal_id in(".$ids.") ";
			$data = M("UserDeal")->where($condition)->findAll();				
			if($data) {				
				$result['status']=0;
				$result['info']="已经添加的商品不能再添加";
				ajax_return($result);
				
			}
			
			
			
			

			$deal_ids = explode ( ',', $ids );
			foreach($deal_ids as $deal_id)
			{
				$datas['user_id']=$user_id;
				$datas['add_time']=NOW_TIME;
				$datas['deal_id']=$deal_id;
				$datas['is_effect']=1;
				$datas['type']=1;
				$list=M("UserDeal")->add($datas);
			}
			save_log($user_id."号用户添加".$ids."号商品成功",1);
			 
			$this->success ("添加成功",$ajax);
			
		} else {
			$this->error (l("INVALID_OPERATION"),$ajax);
		}		
		
	    
//	    if ($ajax){
//	        ajax_return($result);
//	    }else{
//	        $this->assign("jumpUrl",$result['jump']);
//	        $this->success(L("UPDATE_SUCCESS"));
//	    }    
	    
	}	
	
	
	public function money_log(){
		
			$model = D ("FxUserMoneyLog");
			$map['user_id']=intval($_REQUEST['user_id']);
			if (! empty ( $model )) {
				$this->_list ( $model, $map );
			}
			$this->display ();
	}	
	
	public function log_delete(){
			$ajax = intval($_REQUEST['ajax']);
			$id = $_REQUEST ['id'];
		
			if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("FxUserMoneyLog")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['user_id']."号会员".$data['id']."号分销资金日志删除成功，";	
				}
				if($info) $info = implode(",",$info);
				$ids = explode ( ',', $id );
				foreach($ids as $uid)
				{
					$GLOBALS['db']->query("delete from ".DB_PREFIX."fx_user_money_log where id = ".$uid);
				}
				save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
				 
				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
			}		
	}
	
}
?>