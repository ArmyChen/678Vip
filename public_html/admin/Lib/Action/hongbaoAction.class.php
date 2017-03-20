<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class hongbaoAction extends CommonAction{

	
	
	public function foreverdelete() {
		$id = intval($_REQUEST['id']);	
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."apk where id=".$id);
	
		$this->error("清空成功");
		
	}
	
	/**
	 * 结算报表
	 * 针对商户的报表查看
	 */
	public function chongzhi()
	{
		
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
	
		$month_format = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
		
		$this->assign("balance_title",$month_format);
		$this->assign("month_title",$month_format);
		//
			
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		$start_time=to_date($begin_time_s);
		$over_time=to_date($end_time_s-1);
		
		$ex_condition="where (h.cztime BETWEEN '$start_time' AND '$over_time')";
				
		
		if(substr($order, 0,6)=="month_")
			$order = null;
		
		$type = $_REQUEST['type'];
		switch ($type){
		case 1:
		$showtype=1;
		$ex_condition .= " and h.issucess = ".$showtype." ";
		break;  
		case 2:		
		$showtype=0;
		$ex_condition .= " and h.issucess =".$showtype." ";
		break;
		case 9:		
		$showtype=9;
		break;
		default:
		$showtype=9;
		$ex_condition .= " and h.issucess =1 ";
 		}
		
						
		
		
		$this->assign("type",$showtype);
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		if(isset($order))
		{
			$orderby = "order by ".$order." ".$sort;
		}else
		{
			$orderby = "";
		}
		

		
		
		if(strim($_REQUEST['name'])!='')
		{
			
		$ex_condition .=" and l.name like '%".strim($_REQUEST['name'])."%'";
			
		}
		
		
		$sql="select h.*,l.name from ".DB_PREFIX."hongbao_chongzhi_log h left join ".DB_PREFIX."supplier_location l on h.slid=l.id $ex_condition  $orderby limit ".$limit;
		$tsql="select count(h.id) from ".DB_PREFIX."hongbao_chongzhi_log h left join ".DB_PREFIX."supplier_location l on h.slid=l.id $ex_condition  $orderby";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		
		$p = new Page ( $total, '' );
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		//查询当月报表
				
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 

		
		$this->display ();
		return;
	}
	
	public function index()
	{
		
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
	
		$month_format = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
		
		$this->assign("balance_title",$month_format);
		$this->assign("month_title",$month_format);
		//
			
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		$start_time=to_date($begin_time_s);
		$over_time=to_date($end_time_s-1);
		
		$ex_condition="where (h.sendtime BETWEEN '$start_time' AND '$over_time')";
				
		
		if(substr($order, 0,6)=="month_")
			$order = null;
		
		$type = $_REQUEST['type'];
		switch ($type){
		case 1:
		$showtype=1;
		$ex_condition .= " and h.type = ".$showtype." ";
		break;  
		case 2:		
		$showtype=0;
		$ex_condition .= " and h.type =".$showtype." ";
		break;
		default:
		$showtype=9;
 		}
		
						
		
		
		$this->assign("type",$showtype);
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		if(isset($order))
		{
			$orderby = "order by ".$order." ".$sort;
		}else
		{
			$orderby = "";
		}
		

		
		
		if(strim($_REQUEST['name'])!='')
		{
			
		$ex_condition .=" and l.name like '%".strim($_REQUEST['name'])."%'";
			
		}
		
		
		$sql="select h.*,l.name,u.user_name from ".DB_PREFIX."hongbao_log h left join ".DB_PREFIX."supplier_location l on h.slid=l.id left join ".DB_PREFIX."user u on h.userid=u.id $ex_condition  $orderby limit ".$limit;
		$tsql="select count(h.id) from ".DB_PREFIX."hongbao_log h left join ".DB_PREFIX."supplier_location l on h.slid=l.id left join ".DB_PREFIX."user u on h.userid=u.id $ex_condition  $orderby";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($tsql);
		
		$p = new Page ( $total, '' );
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		//查询当月报表
				
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 

		
		$this->display ();
		return;
	}
	
	
 
	
	
	
	
}
?>