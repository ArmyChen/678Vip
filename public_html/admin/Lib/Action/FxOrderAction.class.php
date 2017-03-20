<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class FxOrderAction extends CommonAction{	
	/*
	 * 分销订单
	 */
	public function index()
	{
		$condition = " 1=1 ";
		if(strim($_REQUEST['order_sn'])!="")	$condition .= " and o.order_sn = ".strim($_REQUEST['order_sn']);
		if(intval($_REQUEST['deal_id'])>0) $condition .= " and i.deal_id = ".intval($_REQUEST['deal_id']);
		if(strim($_REQUEST['user_name'])!=''){
			$condition .= " and o.user_name = '".strim($_REQUEST['user_name'])."' ";
		}

		if(strim($_REQUEST['order_status'])==1)$condition .= " and o.order_status = 0";
		if(strim($_REQUEST['order_status'])==2)$condition .= " and o.order_status = 1";

		$count =$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item as i left join ".DB_PREFIX."deal_order as o on i.order_id=o.id where i.fx_user_id>0 and ".$condition);
		$p = new Page ( $count);
		$limit=$p->firstRow . ',' . $p->listRows;
		$list =$GLOBALS['db']->getAll("select i.*,o.order_status,o.order_sn,o.deal_order_item from ".DB_PREFIX."deal_order_item as i left join ".DB_PREFIX."deal_order as o on i.order_id=o.id where i.fx_user_id>0 and ".$condition." order by o.create_time desc limit ".$limit);
		foreach ($list as $k=>$v){

			$v['fx_salary_all']=unserialize($v['fx_salary_all']);
			$list[$k]['fx_salary_all']="销售佣金".round($v['fx_salary_all'][0],2)."元，一级推广佣金".round($v['fx_salary_all'][1],2)."元，二级推广佣金".round($v['fx_salary_all'][2],2)."元";
			if($v['order_status']==0){
				$list[$k]['fx_salary_all']='-';
				$list[$k]['fx_salary']='-';
				$list[$k]['fx_salary_total']='-';
			}elseif($v['order_status']==1){
				$list[$k]['fx_salary']=format_price($v['fx_salary']);
				$list[$k]['fx_salary_total']=format_price($v['fx_salary_total']);	
			}
			foreach(unserialize($v['deal_order_item']) as $kk=>$vv){
				if($vv['id']==$list[$k]['id']) $list[$k]['deal_name']="ID：".$list[$k]['deal_id']."。".msubstr($vv['name'],0,25);
			}
			//print_r(unserialize($v['deal_order_item']));exit;
		}
		$page = $p->show ();
		$this->assign("list",$list);
		$this->assign ( "page", $page );
		$this->display ();
		return;
	}
	
	
	
	

	
	
}
?>