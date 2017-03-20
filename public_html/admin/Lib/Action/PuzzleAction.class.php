<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class PuzzleAction extends CommonAction{
	public $page = 1;
	
	private $RELATE_GOODS_NUM = 6;//可以关联商品的个数
	
	public function __construct(){
		parent::__construct();
		
		if(isset($_REQUEST['page'])){
			$this->page = max(1,(int)$_REQUEST['page']);
		}
		if(isset($_REQUEST['isajax'])){
			$this->isajax = (int)$_REQUEST['isajax'] > 0 ? 1 : 0;
		}
		if(isset($_REQUEST['page'])){
			$this->page = max(1,(int)$_REQUEST['page']);
		}
		$this->assign('pager_num_now',$this->page);
	}
	
	public function index()
	{
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//分类
		
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		
		if(strim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.strim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		if (isset($_REQUEST['status'])){
        $map["status"]=array("eq",intval($_REQUEST['status']));
		}else{
		$map["status"]=array("egt",-2);
		}
		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cateid'] = array("in",$cate_ids);
		}
		
		/*
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
		*/
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = M ('Puzzle');
		
		//$locationlog = M ("location_money_log");
        //$locallog=$locationlog->where($map)->order('id desc')->select(); 
		 
		if (! empty ( $model )) {
			$this->_list ( $model, $map );	
           // var_dump($homelist);			
		}
		
		$main_title='联盟活动列表';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	//商户报名列表 2016-5-19
	public function slist()
	{
		
		$map['pid'] = intval($_REQUEST['pid']);			
		
        
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = M ('Puzzle_log');
		
		//$locationlog = M ("location_money_log");
        //$locallog=$locationlog->where($map)->order('id desc')->select(); 
		 
		if (! empty ( $model )) {
			$this->_list ( $model, $map );           		
		}
		
		$main_title='商家报名列表';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	
	public function ulist()
	{
		
		$map['pid'] = intval($_REQUEST['pid']);			
		
        
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = M ('Puzzle_user_log')->order('status');
		
		//$locationlog = M ("location_money_log");
        //$locallog=$locationlog->where($map)->order('id desc')->select(); 
		 
		if (! empty ( $model )) {
			$this->_list ( $model, $map );           		
		}
		
		$main_title='用户参与列表';
		$this->assign("main_title",$main_title);
		$this->display ();
		return;
	}
	
	public function add()
	{
				
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		$this->display();
	}
	//插入新记录 2016-5-19
	public function insert() {
		
		$data = $_POST;
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		
	 	if(intval($data['pcount'])<1)
		{
			$this->error("参加商户数必须大于1");
		}
		if(floatval($data['return_money'])<0)
		{
			$this->error("现金返还不能为负数");
		}
		if(!check_empty($data['name']))
		{
			$this->error("活动名称不能为空");
		}	
		if(!check_empty($data['slyue']))
		{
			$this->error("商家余额要求不能为空");
		}
		
		if(!check_empty($data['begin_time']))
		{
			$this->error("开始时间不能为空");
		}	
		if(!check_empty($data['end_time']))
		{
			$this->error("结束时间不能为空");
		}			
		if(!check_empty($data['content']))
		{
			$this->error("描述不能为空");
		}
		// 更新数据

		
		$data['STime'] = $data['begin_time'];
		$data['ETime'] = $data['end_time'];
		$data['CreateTime'] = to_date(NOW_TIME);
		$data['UpdateTime'] = to_date(NOW_TIME);
		$data['status'] = 0;
		$list=M(MODULE_NAME)->add($data);        
        $this->success(L("INSERT_SUCCESS"));
	}
	
	//编辑保存 2016-5-19
	public function save_edit() {
		
		$data = $_POST;
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		
	 	if(intval($data['pcount'])<1)
		{
			$this->error("参加商户数必须大于1");
		}
		if(floatval($data['return_money'])<0)
		{
			$this->error("现金返还不能为负数");
		}
		if(!check_empty($data['name']))
		{
			$this->error("活动名称不能为空");
		}	
		if(!check_empty($data['slyue']))
		{
			$this->error("商家余额要求不能为空");
		}
		if(!check_empty($data['fmoney']))
		{
			$this->error("返利金额不能为空");
		}
		if(!check_empty($data['begin_time']))
		{
			$this->error("开始时间不能为空");
		}	
		if(!check_empty($data['end_time']))
		{
			$this->error("结束时间不能为空");
		}			
		if(!check_empty($data['content']))
		{
			$this->error("描述不能为空");
		}
		// 更新数据

		
		$data['STime'] = $data['begin_time'];
		$data['ETime'] = $data['end_time'];
		$data['UpdateTime'] = to_date(NOW_TIME);
		$data['status'] = 0;
		//var_dump($data);
		$list=M(MODULE_NAME)->where("id=".$data['id'])->save($data);  //更新数据 
               
        $this->success(L("UPDATE_SUCCESS"));
	}	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['STime'];
		$vo['end_time'] = $vo['ETime'];
		$this->assign ( 'vo', $vo );
		
		
				
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
			
		
		$this->display ();
	}
	//用户结算
	public function uedit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$ulog = M(puzzle_user_log)->where($condition)->find(); 
		if ($ulog['status']==0){
		//完成金额
		$pid=$ulog['pid'];
		$uid=$ulog['userid'];
        $tuanid=$ulog['tuanid'];
        $orderid=$ulog['orderid'];
		
		$hadfmoney = M("puzzle_user_log")->where("pid=".$pid." and userid=".$uid." and status=0")->sum('fmoney');  //已返金额		
        $hadslid = M("puzzle_user_log")->where("pid=".$pid." and userid=".$uid." and status=0")->count();  //已参加商家 
		
		$plog = M("puzzle")->where("id=".$pid)->find();
		
		$isfinish= M("deal_coupon")->where("order_id=".$orderid." and user_id=".$uid." and deal_id=".$tuanid." and refund_status != 2 and is_valid=2")->find();
		
		if ($plog['fmoney']==$hadfmoney && $plog['pcount']==$hadslid && $isfinish){
		//任务完成
		//用户金额增加 ：
        $GLOBALS['db']->query("update ".DB_PREFIX."user set money = money+".$hadfmoney." where id = ".$uid);
		$moneylog=array(
		'log_info'=>'会员ID'.$uid.'完成'.$pid.'号活动，返利总金额：'.$hadfmoney.'元',
		'pid'=>$pid,
		'user_id'=>$uid,
		'create_time'=>NOW_TIME,
		'money'=>$hadfmoney,
		'type'=>3
		);	
		M("puzzle_money_log")->add($moneylog); //保存会员入款日志
		
		//读取每单的金额
		$sqlarr=array(
		'pid'=>$pid,
		'userid'=>$uid,
		'status'=>0		
		);
		
		
		$list=M("puzzle_user_log")->where($sqlarr)->select();
		
		
		foreach($list as $key=>$val){
			//减门店保证金额
			
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location set yy_baozhengjin = yy_baozhengjin-".$val['fmoney']." where id = ".$val['slid']);
			
			//存Money Log
			$moneylog=array(
		   'log_info'=>'会员ID'.$uid.'完成'.$pid.'号活动，返利金额：'.$val['fmoney'].'元',
		   'pid'=>$pid,
		   'user_id'=>$uid,
		   'location_id'=>$val['slid'],
		   'create_time'=>NOW_TIME,
		   'money'=>$val['fmoney'],
		   'type'=>2
		  );
		  M("puzzle_money_log")->add($moneylog); //保存会员入款日志
		
		}
	
		//完成门店扣款
		
		//更新userlog
		$upuser=array('status'=>1,'UpdateTime'=>to_date(NOW_TIME));
		M("puzzle_user_log")->where($sqlarr)->save($upuser);   //更新UserLog状态
			
		$this->success(L("结算成功！"));
		}else{
		//任务没有完成
		$this->error("任务没有完成，不能结算！");
		}
		
		
		}else{
		$this->error("已经审核结算的用户！");
		}
	}
	
	
	public function tuanview(){
		$id = intval($_REQUEST ['id']);
		$tuanid=M("Puzzle_log")->where("id=".$id)->getField('tuanid'); //得到团ID
		header("location:/index.php?ctl=deal&act=$tuanid");
		
	}

	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$list=M("Puzzle")->where($condition)->setField("status",'-1');
				if ($list!==false) {
				$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
				$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function sedit() {
		//审核报名
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => $id);
				$loglist=M("Puzzle_log")->where($condition)->find();
				$pid=intval($loglist['pid']);
				$fmoney=floatval($loglist['fmoney']);
				
				$ucount=M("Puzzle")->where("id=".$pid)->getField('ucount'); //得到需求会员数
				$baozhengjin=floatval($fmoney)*$ucount; //计算保证金	
				
				$get_location_money=M("supplier_location")->where("id=".$loglist['slid'])->getField('money'); //得到会员余额
				$supplier_id=M("supplier_location")->where("id=".$loglist['slid'])->getField('supplier_id'); //得到会员余额
				if ($get_location_money<$baozhengjin){
				$this->error (l("审核失败,商户可用余额不足以支付保证金！"),$ajax);					
				}else{	
				
				if($loglist['status']==0){
				$data['status']=1;
				$data['UpdateTime']=to_date(NOW_TIME);
				//更新状态
				$list=M("Puzzle_log")->where($condition)->save($data);
				//更新返利金额

				$GLOBALS['db']->query("update ".DB_PREFIX ."puzzle set fmoney=fmoney+$fmoney,yshenhe=yshenhe+1 where id=".$pid);
                $GLOBALS['db']->query("update ".DB_PREFIX ."supplier_location set money=money-$baozhengjin,yy_baozhengjin=yy_baozhengjin+$baozhengjin where id=".$loglist['slid']);
             	
				
				$log_data = array();
			    $log_data['log_info'] = '参与活动:'.$pid.'冻结保证金额：'.$baozhengjin;
			    $log_data['location_id']=$loglist['slid'];
			    $log_data['supplier_id'] = $supplier_id;
			    $log_data['create_time'] = NOW_TIME;
			    $log_data['money'] = floatval($baozhengjin);
			    $log_data['type'] = 9; //冻结保证金额
			    $list=M("supplier_money_log")->add($log_data);		
			
			
			
				
				if ($list!==false) {
				$this->success (l("审核成功"),$ajax);
				} else {
				$this->error (l("审核失败"),$ajax);
				}
				}else{
				$this->error (l("已经审核的请勿重复进行！"),$ajax);	
				}
		      }
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function update() {
		//发布上线
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$list=M("Puzzle")->where($condition)->setField("status",'1');
				if ($list!==false) {
				$this->success (l("发布上线成功"),$ajax);
				} else {
				$this->error (l("发布失败"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	
	
	
	
	
	
}
?>