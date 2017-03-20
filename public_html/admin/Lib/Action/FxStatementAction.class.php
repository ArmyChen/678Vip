<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class FxStatementAction extends CommonAction{

	public function index()
	{
// 		8..订单收入明细、2. 充值收入收细、5商户提现明细、4会员提现明细、6会员退款明细
		
		$type = intval($_REQUEST['type']);
		if($type<0||$type>3)
			$type = 0;
		
		$this->assign("type",$type);
		
		$balance_title = "营业额";
		if($type==0)
			$balance_title = "营业额";
		if($type==1)
			$balance_title = "分销佣金";
		if($type==2)
			$balance_title = "推广佣金";
		if($type==3)
			$balance_title = "分销提现";
		
		
		//
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		$current_year = intval(to_date(NOW_TIME,"Y"));
		$current_month = intval(to_date(NOW_TIME,"m"));
		
		if($year==0)$year = $current_year;
		if($month==0)$month = $current_month;
		
		$year_list = array();
		for($i=$current_year-10;$i<=$current_year+10;$i++)
		{
			$current = $year==$i?true:false;
			$year_list[] = array("year"=>$i,"current"=>$current);
		}
		
		$month_list = array();
		for($i=1;$i<=12;$i++)
		{
			$current = $month==$i?true:false;
			$month_list[] = array("month"=>$i,"current"=>$current);
		}
		
		
		$this->assign("year_list",$year_list);
		$this->assign("month_list",$month_list);
		
		$this->assign("cyear",$year);
		$this->assign("cmonth",$month);
		
		
		$begin_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		
		$next_month = $month+1;
		$next_year = $year;
		if($next_month > 12)
		{
			$next_month = 1;
			$next_year = $next_year + 1;
		}
		$end_time = $next_year."-".str_pad($next_month,2,"0",STR_PAD_LEFT)."-01";
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		$this->assign("balance_title",$year."-".str_pad($month,2,"0",STR_PAD_LEFT)." ".$balance_title);
		$this->assign("month_title",$year."-".str_pad($month,2,"0",STR_PAD_LEFT));
		//
		
		$map['type'] = $type;
		$map['money'] = array("gt",0);
		if($begin_time_s&&$end_time_s)
		{
			$map['create_time'] = array("between",array($begin_time_s,$end_time_s));
		}
		elseif($begin_time_s)
		{
			$map['create_time'] = array("gt",$begin_time_s);
		}
		elseif($end_time_s)
		{
			$map['create_time'] = array("lt",$end_time_s);
		}

		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}

		$model = D ("FxStatementsLog");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		$sum_money = $model->where($map)->sum("money");
		$this->assign("sum_money",$sum_money);
		
		$voList = $this->get("list");
		$page_sum_money = 0;
		foreach($voList as $row)
		{
			$page_sum_money+=floatval($row['money']);
		}
		$this->assign("page_sum_money",$page_sum_money);
		
		
		
		//开始计算利润率		
		$stat_month = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);		
		$sql = "select sum(sale_money) as sale_money,
				sum(fx_extend_salary) as fx_extend_salary,
				sum(fx_salary) as fx_salary from ".DB_PREFIX."fx_statements where stat_month = '".$stat_month."'";
		$stat_result = $GLOBALS['db']->getRow($sql);
		
		
		$fx_cost = floatval($stat_result['fx_extend_salary']) + floatval($stat_result['fx_salary']);

		$this->assign("fx_cost",$fx_cost);
		$this->assign("stat_result",$stat_result);
		
		$this->display ();
		return;
	}
	
	
	public function foreverdelete() {
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		if($year==0||$month==0)
		{
			$this->error("请选择日期");
		}
		
		
		$begin_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		
		$next_month = $month+1;
		$next_year = $year;
		if($next_month > 12)
		{
			$next_month = 1;
			$next_year = $next_year + 1;
		}
		$end_time = $next_year."-".str_pad($next_month,2,"0",STR_PAD_LEFT)."-01";
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		$stat_month = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."fx_statements_log where create_time between $begin_time_s and $end_time_s");
		$GLOBALS['db']->query("delete from ".DB_PREFIX."fx_statements where stat_month = '".$stat_month."'");
		
		$this->error("清空成功");
		
	}
	
	
}
?>