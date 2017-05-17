<?php
require_once 'core/pinyin.php';
require_once 'core/page.php';

// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
//dc_menu where (( g.is_effect = 0 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1))
class ajaxModule extends KizBaseModule{
    function __construct()
    {
        parent::__construct();
        global_run();

        $ywsort=array(
            "-6"=>"生产入库",
            "-5"=>"生产退料",
            "-4"=>"退还入库",
            "-3"=>"预配退货",
//            "-2"=>"其他入库",
//            "-1"=>"盘盈",
            "1"=>"盘盈",
            "2"=>"无订单入库",
            "3"=>"要货调入",
            "4"=>"初始库存",
            "5"=>"仓库调拨",
            "6"=>"盘亏",
            "7"=>"无订单出库",
            "8"=>"要货调出",
            "9"=>"退货",
            "10"=>"生产领料",
            "11"=>"借用出库",
            "12"=>"其他出库",
            "13"=>"配送领料",
            "14"=>"品牌销售出库",
            "15"=>"直拨出入库"
        );
        $this->ywsort=$ywsort;
        $this->gonghuoren=array(
            "1"=>"临时客户",
            "2"=>"临时运输商",
            "3"=>"临时供应商",
            "4"=>"领料出库"
        );

        $kcnx=array(
            "0"=>"暂无",
            "1"=>"现制商品",
            "2"=>"预制商品",
            "3"=>"外购商品",
            "4"=>"原物料",
            "6"=>"半成品",

        );
        $this->kcnx=$kcnx;
    }

    //查询商品名称是否重复
    public function check_goods_name()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $name = $_REQUEST['name'];
        $res = $GLOBALS['db']->getAll("select * from fanwe_cangku_menu where slid=$slid and mname='".$name."'");
        if(count($res)>0){//成功
            $return['success'] = true;
            $return['message'] = '商品名称已存在';
        }else{

            $return['success'] = false;
            $return['message'] = '失败';
        }

        echo json_encode($return);exit;
    }

    //common function
    public function get_hanzi()
    {
        $pinyin = new pinyin();
        if($_REQUEST['name']){
            $result = $pinyin->pinyin1($_REQUEST['name']);
            echo $result;
        }
    }

    /**
     * 入库列表ajax
     */
    public function go_down_index_ajax(){
//        $r = $GLOBALS['db']->getAll("select * from fanwe_cangku_log where type=2");
//        var_dump($r);die;
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $warehouseId = $_REQUEST['warehouseId']?intval($_REQUEST['warehouseId']):'99';


        if (($_REQUEST['begin_time'])|| ($_REQUEST['end_time'])){
            $begin_time = strim($_REQUEST['begin_time']);
            $end_time = strim($_REQUEST['end_time']);
        }else{	 //默认为当月的
            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);
//        if($type == 1){
//            $sqlstr="where a.gys is null";
//        }else{
//            $sqlstr="where 1=1";
//        }
        $sqlstr="where 1=1";
        $sqlstr.=' and ( a.slid='.$location_id.')';

        if($begin_time_s){
            $sqlstr .=" and a.ctime > ".$begin_time_s." ";
        }
        if($end_time_s){
            $sqlstr .=" and a.ctime < ".$end_time_s." ";
        }
        if ($type !=99 ){
            $sqlstr .=" and a.type = ".$type." ";
        }
        if ($warehouseId !=99 ){
            $sqlstr .=" and a.cid = ".$warehouseId." ";
        }
        if ($ywsortid !=99 ){
            $sqlstr .=" and a.ywsort = ".$ywsortid." ";
        }
        if($_REQUEST['danjuhao'] !=""){
            $sqlstr .=" and a.danjuhao like '%".$_REQUEST['danjuhao']."%' ";
        }

//        $sqlstr .=" and f.print <> 4";
//        $sql2 = "select * from fanwe_cangku_log limit 1";
//        var_dump($GLOBALS['db']->getRow($sql2));
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlrecords="select count(a.id) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";
//        var_dump($sql);
        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//        var_dump($list);die;
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        foreach($list as $k=>$v){
            $v['ctime']=to_date($v['ctime'],'m-d H:i:s');
            $v['detail']=unserialize($v['dd_detail']);

            if ($v['type']==1){
                $v['type_show']	='入库';
                $v['gonghuo_show']	='供货人';
            }else{
                $v['type_show']	='出库';
                $v['gonghuo_show']	='收货人';
            }
            $v['ywsort']=$this->ywsort[$v['ywsort']];
            if(!empty($v['gys'])){
                if($type == 1){
                    $v['ywsort']='直拨入库';

                }else{
                    $v['ywsort']='直拨出库';

                }
            }
            $v['gonghuo']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gonghuoren']);
            $v['gys']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gys']);
            $list[$k]=$v;
        }
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    /**
     * 采购入库列表ajax
     */
    public function go_down_index_ajax2(){
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $warehouseId = $_REQUEST['warehouseId']?intval($_REQUEST['warehouseId']):'99';

        if (($_REQUEST['begin_time'])|| ($_REQUEST['end_time'])){
            $begin_time = strim($_REQUEST['begin_time']);
            $end_time = strim($_REQUEST['end_time']);
        }else{	 //默认为当月的
            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);

        $sqlstr="where a.gys is not null ";
        $sqlstr.=' and ( a.slid='.$location_id.')';

        if($begin_time_s){
            $sqlstr .=" and a.ctime > ".$begin_time_s." ";
        }
        if($end_time_s){
            $sqlstr .=" and a.ctime < ".$end_time_s." ";
        }
        if ($type !=99 ){
            $sqlstr .=" and a.type = ".$type." ";
        }
        if ($warehouseId !=99 ){
            $sqlstr .=" and a.cid = ".$warehouseId." ";
        }
        if ($ywsortid !=99 ){
            $sqlstr .=" and a.ywsort = ".$ywsortid." ";
        }
        if($_REQUEST['danjuhao'] !=""){
            $sqlstr .=" and a.danjuhao like '%".$_REQUEST['danjuhao']."%' ";
        }
//        $sqlstr .=" and f.print <> 4";
//        $sql2 = "select * from fanwe_cangku_log limit 1";
//        var_dump($GLOBALS['db']->getRow($sql2));
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlrecords="select count(a.id) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";
//        var_dump($sql);
        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//        var_dump($list);die;
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        foreach($list as $k=>$v){
            $v['ctime']=to_date($v['ctime'],'m-d H:i:s');
            $v['detail']=unserialize($v['dd_detail']);

            if ($v['type']==1){
                $v['type_show']	='入库';
                $v['gonghuo_show']	='供货人';
            }else{
                $v['type_show']	='出库';
                $v['gonghuo_show']	='收货人';
            }

            $v['ywsort']=$this->ywsort[$v['ywsort']];
            $v['gonghuo']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gonghuoren']);
            $v['gys']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gys']);
            $list[$k]=$v;
        }
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    /**
     * 部门领料列表ajax
     */
    public function go_bumen_index_ajax2(){
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $warehouseId = $_REQUEST['warehouseId']?intval($_REQUEST['warehouseId']):'99';
        $bumen = $_REQUEST['gonghuoren'];
        $gys = $_REQUEST['gys'];
        $status = $_REQUEST['status'];
        if (($_REQUEST['begin_time'])|| ($_REQUEST['end_time'])){
            $begin_time = strim($_REQUEST['begin_time']);
            $end_time = strim($_REQUEST['end_time']);
        }else{	 //默认为当月的
            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);

        $sqlstr="where a.gys is not null ";
        $sqlstr.=' and ( a.slid='.$location_id.')';

        if($begin_time_s){
            $sqlstr .=" and a.ctime > ".$begin_time_s." ";
        }
        if($end_time_s){
            $sqlstr .=" and a.ctime < ".$end_time_s." ";
        }
        if ($type !=99 ){
            $sqlstr .=" and a.type = ".$type." ";
        }
        if ($warehouseId !=99 ){
            $sqlstr .=" and a.cid = ".$warehouseId." ";
        }
        if ($ywsortid !=99 ){
            $sqlstr .=" and a.ywsort = ".$ywsortid." ";
        }
        if ($bumen){
            $sqlstr .=" and a.gonghuoren = '".$bumen."' ";
        }
        if ($gys){
            $sqlstr .=" and a.gys = '".$gys."' ";
        }
        if ($status!=99){
            $sqlstr .=" and a.type = ".$status." ";
        }
        if($_REQUEST['danjuhao'] !=""){
            $sqlstr .=" and a.danjuhao like '%".$_REQUEST['danjuhao']."%' ";
        }
//        $sqlstr .=" and f.print <> 4";
//        $sql2 = "select * from fanwe_cangku_log limit 1";
//        var_dump($GLOBALS['db']->getRow($sql2));
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlrecords="select count(a.id) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";
//        var_dump($sql);
        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//        var_dump($list);die;
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        foreach($list as $k=>$v){
            $v['ctime']=to_date($v['ctime'],'m-d H:i:s');
            $v['detail']=unserialize($v['dd_detail']);

            if ($v['type']==1){
                $v['type_show']	='入库';
                $v['gonghuo_show']	='供货人';
            }else{
                $v['type_show']	='出库';
                $v['gonghuo_show']	='收货人';
            }

            $v['ywsort']=$this->ywsort[$v['ywsort']];
            $v['gonghuo']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gonghuoren']);
            $v['gys']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gys']);
            $list[$k]=$v;
        }
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    /**
     * 商品搜索列表ajax
     */
    public function goods_list_ajax()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        $wmTypes = $_REQUEST['wmTypes'];
        $warehouseId = $_REQUEST['warehouseId'];

        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $where = "where  g.location_id=$slid";
//        $where .=" and g.is_effect = 0";//是否显示在终端
//        $where .= " and g.is_stock = 1 ";//是否是库存商品
//        $where .=" and g.is_delete = 1";//是否删除

        //库存商品
        $where .= " and (( g.is_effect = 0 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1))";

        if(!empty($wmTypes)){
            $where .= " and g.print in (".$wmTypes.")";//筛选库存类型
        }else{
            $where .= " and g.print <> 1";//库存类型不等于现制商品
        }


        if($_REQUEST['skuTypeId']){
            $where .= " and g.cate_id=".$_REQUEST['skuTypeId'];
        }
        if($_REQUEST['skuCodeOrName']){
            $where .= " and (g.name like '%".$_REQUEST['skuCodeOrName']."%'";
            $where .= " or g.barcode like '%".$_REQUEST['skuCodeOrName']."%'";
            $where .= " or g.id like '%".$_REQUEST['skuCodeOrName']."%' or g.pinyin like '%".$_REQUEST['skuCodeOrName']."%' )";
        }

//        var_dump($where);
        $sqlcount = "select count(id) from fanwe_dc_menu g $where";
        $records = $GLOBALS['db']->getOne($sqlcount);
        $sql = "select *,g.id as mmid,g.name as skuName,g.barcode as skuCode,g.unit as uom,g.funit,g.times,g.price,g.pinyin,g.cate_id as skuTypeId,c.name as skuTypeName,g.stock as inventoryQty from fanwe_dc_menu g  LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id $where limit $limit";
        $check=$GLOBALS['db']->getAll($sql);
//var_dump($check);
        $data=[];
        foreach ($check as $key=>$item) {
            $sql2 = "select * from fanwe_cangku_menu where cid=".$warehouseId." and mid=".$item['mmid'];
            $result = $GLOBALS['db']->getRow($sql2);
            if($item['print'] != 3){
                $price =$item['buyPrice'];
            }else{
                $price =$item['price'];
            }
            $data[$key]['id']=$item['mmid'];
            $data[$key]['skuName']=$item['skuName'];
            $data[$key]['skuCode']=$item['skuCode'];
            $data[$key]['uom']=$item['uom'];
            $data[$key]['wmType']=$item['print'];
            $data[$key]['funit']=$item['funit'];
            $data[$key]['times']=$item['times'];
            $data[$key]['price']=$item['price'];
            $data[$key]['pinyin']=$item['pinyin'];
            $data[$key]['reckonPrice'] = $price;
            $data[$key]['reckonPriceStr'] = $price;
            $data[$key]['skuTypeId']=$item['skuTypeId'];
            $data[$key]['yieldRateStr']=$item['chupinliu'];
            $data[$key]['skuTypeName']=$item['skuTypeName'];
            $data[$key]['inventoryQty']=empty($result)?0:$result['mstock'];


        }
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_diaobo' ");print_r($table);exit;

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        if($check){
            $return['dataList'] = $data;
        }else{
            $return['status'] = false;
            $return['resMsg'] = "查无结果！";
        }
        echo json_encode($return);exit;
    }

    /**
     * 商品搜索（扫码）ajax
     */
    public function goods_search_code_ajax()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $where = "where 1 and g.location_id=$slid";
        if($_REQUEST['cate_id']){
            $where .= " and g.cate_id=".$_REQUEST['cate_id'];
        }
        if($_REQUEST['barcode']){
            $where .= " and g.barcode='".$_REQUEST['barcode']."'";
        }

        $sql = "select g.id,g.name as skuName,g.barcode as skuCode,g.unit as uom,g.funit,g.times,g.price,g.pinyin,g.cate_id,c.name as skuTypeName,g.stock as inventoryQty from fanwe_dc_menu g LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id $where";
        $check=$GLOBALS['db']->getAll($sql);
//print_r($sql);exit;
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        $return['success'] = true;
        $return['message'] = null;
        if($check){
            $return['data'] = $check[0];
        }else{
            $return['success'] = false;
            $return['message'] = "查无结果！";
        }
        echo json_encode($return);exit;
    }
    /**
     * 入库保存ajax
     */
    public function saving_ajax()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        //$slid = $account_info['slid'];
        $slid = $account_info['slid'];
        $cid = $_REQUEST['warehouseId'];

        $dhid = $_REQUEST['asnNoView']?intval($_REQUEST['asnNoView']):'0';

        $sqlcheck="select dd_detail from fanwe_cangku_log where slid=$slid and  danjuhao='$dhid'";
        $isRuku  =	$GLOBALS['db']->getRow($sqlcheck);
        if($isRuku){
            $return['success'] = false;
            $return['message'] = "已经入过库了，请勿重复操作！";
            echo json_encode($return);exit;
        }
        $datailinfo = array();
        $oDetail = empty($_REQUEST['details'])?$_REQUEST['detail']:$_REQUEST['details'];
        $zmoney = 0;
        $znum = 0;

        foreach($oDetail as $k=>$v){
            $datailinfo[$k]['mid'] = $v['skuId'];
            $datailinfo[$k]['cate_id'] = $v['skuTypeId'];
            $datailinfo[$k]['cid'] = $cid;
            $datailinfo[$k]['unit'] = $v['uom'];
            $datailinfo[$k]['funit'] = $v['funit'];
            $datailinfo[$k]['times'] = $v['times'];
            $datailinfo[$k]['yuan_price'] = $v['price'];
            $datailinfo[$k]['name'] = $v['skuName'];
            $datailinfo[$k]['barcode'] = $v['skuCode'];
            $datailinfo[$k]['type'] = $v['type'];
            $datailinfo[$k]['unit_type'] = $v['unit_type'];
            $datailinfo[$k]['price'] = $v['price'];
            $datailinfo[$k]['num'] = $v['actualQty'];
            $datailinfo[$k]['ssnum'] = $v['standardInventoryQty'];
            $datailinfo[$k]['zmoney'] = $v['amount'];
            $datailinfo[$k]['memo'] = $v['memo'];
            $znum += $v['actualQty'];
            $zmoney += $v['price'];
        }

        $dd_detail=serialize($datailinfo);
        $ddbz = $_REQUEST['ddbz']?intval($_REQUEST['ddbz']):'0';
        $bumen = empty($_REQUEST['bumen'])?$_REQUEST['gonghuoren']:$_REQUEST['bumen'];
        //if($unit_type==9){$unit_type==0;}
        $datain=$_REQUEST;

        //验收入库单信息
        $time = $_REQUEST['time'];
        $gys = $_REQUEST['gys'];


        if($time){
            $datain['ctime'] =  strtotime($time);
        }else{
            $datain['ctime']= time()+ 60*60*8;
        }

        $datain['dd_detail']=$dd_detail;
        $datain['slid']=$slid;
        $datain['type'] = $_REQUEST['type'];
        $datain['danjuhao'] = empty($_REQUEST['asnNoView'])?time():$_REQUEST['asnNoView'];
        $datain['ywsort'] = $_REQUEST['senderId'];
        $datain['cid'] = $_REQUEST['warehouseId'];
        $datain['lihuo_user'] = $account_info['account_name'];
        $datain['isdisable'] = 1;
        $datain['zmoney'] = $zmoney;
        $datain['znum'] = $znum;
        $datain['memo'] = $_REQUEST['remarks'];
        if($bumen){
            $datain['gonghuoren'] = $bumen;
        }


        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        $return['success'] = true;
        $return['message'] = '保存成功';
        if ($_REQUEST['type']==1){ //入库
            $return['data']['url'] = url("kiz","inventory#go_down_index&id=$slid");
        }else{
            $return['data']['url'] = url("kiz","inventory#go_up_index&id=$slid");
        }
        $detail2 = $_REQUEST['details'];
        $amount = 0;//总金额
        foreach($detail2 as $k=>$v){
            $order_num=floatval($v['planMoveQty']);
            $amount += $order_num*$v['price'];
        }
        if(!empty($bumen)){
            if ($_REQUEST['type']==1){ //入库
                $return['data']['url'] = url("kiz","supplier#go_down_index&id=$slid");
            }else{
                $return['data']['url'] = url("kiz","supplier#go_up_index&id=$slid");
            }
        }
        //采购入库出库单
        //验收入库单url封装
        if(!empty($bumen)){
            //新增出库记录
            $datailinfo = array();
            $oDetail = empty($_REQUEST['details'])?$_REQUEST['detail']:$_REQUEST['details'];
            foreach($oDetail as $k=>$v){
                $datailinfo[$k]['mid'] = $v['skuId'];
                $datailinfo[$k]['unit'] = $v['uom'];
                $datailinfo[$k]['cate_id'] = $v['skuTypeId'];
                $datailinfo[$k]['funit'] = $v['funit'];
                $datailinfo[$k]['times'] = $v['times'];
                $datailinfo[$k]['yuan_price'] = $v['price'];
                $datailinfo[$k]['name'] = $v['skuName'];
                $datailinfo[$k]['barcode'] = $v['skuCode'];
                $datailinfo[$k]['type'] = 2;
                $datailinfo[$k]['unit_type'] = $v['unit_type'];
                $datailinfo[$k]['price'] = $v['price'];
                $datailinfo[$k]['num'] = $v['actualQty'];
                $datailinfo[$k]['ssnum'] = $v['standardInventoryQty'];
                $datailinfo[$k]['zmoney'] = $v['uom'];
                $datailinfo[$k]['memo'] = $v['memo'];
            }

            $dd_detail=serialize($datailinfo);

            $datainGys=$_REQUEST;

            //验收入库单信息
            $time = $_REQUEST['time'];
            $gys = $_REQUEST['gys'];


            if($time){
                $datainGys['ctime'] =  strtotime($time);
            }else{
                $datainGys['ctime']= time()+ 60*60*8;
            }

            $datainGys['dd_detail']=$dd_detail;
            $datainGys['slid']=$slid;
            $datainGys['type'] = 2;
            $datainGys['danjuhao'] = empty($_REQUEST['asnNoView'])?time():$_REQUEST['asnNoView'];
            $datainGys['ywsort'] = $_REQUEST['senderId'];
            $datainGys['cid'] = $_REQUEST['warehouseId'];
            $datainGys['lihuo_user'] = $account_info['account_name'];
            $datainGys['gonghuoren'] = $bumen;
            $datainGys['zmoney'] = $amount;
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datainGys ,"INSERT");
        }

//        $datain['zmoney'] = $amount;
        $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain ,"INSERT");
        echo json_encode($return);exit;
    }

    /**
     * 入库编辑保存ajax
     */
    public function edit_saving_ajax()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        //$slid = $account_info['slid'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $cid = $_REQUEST['warehouseId'];
        $dhid = $_REQUEST['asnNoView']?intval($_REQUEST['asnNoView']):'0';

        $datailinfo = array();
        $oDetail = empty($_REQUEST['details'])?$_REQUEST['detail']:$_REQUEST['details'];
        $zmoney = 0;
        $znum = 0;

        foreach($oDetail as $k=>$v){
            $datailinfo[$k]['mid'] = $v['skuId'];
            $datailinfo[$k]['cate_id'] = $v['skuTypeId'];
            $datailinfo[$k]['cid'] = $cid;
            $datailinfo[$k]['unit'] = $v['uom'];
            $datailinfo[$k]['funit'] = $v['funit'];
            $datailinfo[$k]['times'] = $v['times'];
            $datailinfo[$k]['yuan_price'] = $v['price'];
            $datailinfo[$k]['name'] = $v['skuName'];
            $datailinfo[$k]['barcode'] = $v['skuCode'];
            $datailinfo[$k]['type'] = $v['type'];
            $datailinfo[$k]['unit_type'] = $v['unit_type'];
            $datailinfo[$k]['price'] = $v['price'];
            $datailinfo[$k]['num'] = $v['actualQty'];
            $datailinfo[$k]['ssnum'] = $v['standardInventoryQty'];
            $datailinfo[$k]['zmoney'] = $v['amount'];
            $datailinfo[$k]['memo'] = $v['memo'];
            $znum += $v['actualQty'];
            $zmoney += $v['price'];
        }

        $dd_detail=serialize($datailinfo);

        $ddbz = $_REQUEST['ddbz']?intval($_REQUEST['ddbz']):'0';
        $bumen = empty($_REQUEST['bumen'])?$_REQUEST['gonghuoren']:$_REQUEST['bumen'];
        //if($unit_type==9){$unit_type==0;}
        $datain=$_REQUEST;

        //验收入库单信息
        $time = $_REQUEST['time'];
        $gys = $_REQUEST['gys'];


        if($time){
            $datain['ctime'] =  strtotime($time);
        }else{
            $datain['ctime']= time()+ 60*60*8;
        }

        $datain['dd_detail']=$dd_detail;
        $datain['slid']=$slid;
        $datain['type'] = $_REQUEST['type'];
        $datain['danjuhao'] = empty($_REQUEST['asnNoView'])?time():$_REQUEST['asnNoView'];
        $datain['ywsort'] = $_REQUEST['senderId'];
        $datain['cid'] = $_REQUEST['warehouseId'];
        $datain['lihuo_user'] = $account_info['account_name'];
        $datain['isdisable'] = 1;
        $datain['zmoney'] = $zmoney;
        $datain['znum'] = $znum;
        $datain['memo'] = $_REQUEST['remarks'];
        if($bumen){
            $datain['gonghuoren'] = $bumen;
        }


        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        $return['success'] = true;
        $return['message'] = '保存成功';
        if ($_REQUEST['type']==1){ //入库
            $return['data']['url'] = url("kiz","inventory#go_down_index&id=$slid");
        }else{
            $return['data']['url'] = url("kiz","inventory#go_up_index&id=$slid");
        }
        $detail2 = $_REQUEST['details'];
        $amount = 0;//总金额
        foreach($detail2 as $k=>$v){
            $order_num=floatval($v['planMoveQty']);
            $amount += $order_num*$v['price'];
        }
        if(!empty($bumen)){
            if ($_REQUEST['type']==1){ //入库
                $return['data']['url'] = url("kiz","supplier#go_down_index&id=$slid");
            }else{
                $return['data']['url'] = url("kiz","supplier#go_up_index&id=$slid");
            }
        }
        //采购入库出库单
        //验收入库单url封装
        if(!empty($bumen)){
            //新增出库记录
            $datailinfo = array();
            $oDetail = empty($_REQUEST['details'])?$_REQUEST['detail']:$_REQUEST['details'];
            foreach($oDetail as $k=>$v){
                $datailinfo[$k]['mid'] = $v['skuId'];
                $datailinfo[$k]['unit'] = $v['uom'];
                $datailinfo[$k]['cate_id'] = $v['skuTypeId'];
                $datailinfo[$k]['funit'] = $v['funit'];
                $datailinfo[$k]['times'] = $v['times'];
                $datailinfo[$k]['yuan_price'] = $v['price'];
                $datailinfo[$k]['name'] = $v['skuName'];
                $datailinfo[$k]['barcode'] = $v['skuCode'];
                $datailinfo[$k]['type'] = 2;
                $datailinfo[$k]['unit_type'] = $v['unit_type'];
                $datailinfo[$k]['price'] = $v['price'];
                $datailinfo[$k]['num'] = $v['actualQty'];
                $datailinfo[$k]['ssnum'] = $v['standardInventoryQty'];
                $datailinfo[$k]['zmoney'] = $v['uom'];
                $datailinfo[$k]['memo'] = $v['memo'];
            }

            $dd_detail=serialize($datailinfo);

            $datainGys=$_REQUEST;

            //验收入库单信息
            $time = $_REQUEST['time'];
            $gys = $_REQUEST['gys'];


            if($time){
                $datainGys['ctime'] =  strtotime($time);
            }else{
                $datainGys['ctime']= time()+ 60*60*8;
            }

            $datainGys['dd_detail']=$dd_detail;
            $datainGys['slid']=$slid;
            $datainGys['type'] = 2;
            $datainGys['danjuhao'] = empty($_REQUEST['asnNoView'])?time():$_REQUEST['asnNoView'];
            $datainGys['ywsort'] = $_REQUEST['senderId'];
            $datainGys['cid'] = $_REQUEST['warehouseId'];
            $datainGys['lihuo_user'] = $account_info['account_name'];
            $datainGys['gonghuoren'] = $bumen;
            $datainGys['zmoney'] = $amount;
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datainGys ,"update","id=".$id);
        }
//        var_dump($datain);die;

//        $datain['zmoney'] = $amount;
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain ,"update","id=".$id);

//        $sql = "select * from fanwe_cangku_log where id=".$id;
//        $result = $GLOBALS['db']->getRow($sql);
//        var_dump($result);die;
        echo json_encode($return);exit;
    }

    /**
     * 调拨列表ajax
     */
    public function diaobo_list_ajax(){
        init_app_page();
//        $res = $GLOBALS['db']->query("alter table fanwe_cangku_diaobo add isdisable int");
//        $res = $GLOBALS['db']->getAll("show columns from fanwe_cangku_diaobo");
//        var_dump($res);die;
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_diaobo' ");print_r($table);exit;
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];

        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $sqlstr="where 1=1";
        $sqlstr.=' and slid='.$location_id;

        if($_REQUEST['createTime']){
            $begin_time=strtotime($_REQUEST['createTime']);
            $end_time=strtotime($_REQUEST['createTime'])+24*60*60;
            $sqlstr .=" and ctime > ".$begin_time." ";
            $sqlstr .=" and ctime < ".$end_time." ";
        }

        if($_REQUEST['transferOrderNo'] !=""){
            $sqlstr .=" and danjuhao like '%".$_REQUEST['transferOrderNo']."%' ";
        }
        if($_REQUEST['fromWmId']){
            $sqlstr .=" and cid = ".$_REQUEST['fromWmId'];
        }
        if($_REQUEST['toWmId']){
            $sqlstr .=" and cidtwo = ".$_REQUEST['toWmId'];
        }

        $cangku_list=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$location_id);
        $cangku_names = array();
        $cangku_names = array_reduce($cangku_list, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $sql="select * from ".DB_PREFIX."cangku_diaobo ".$sqlstr." order by id desc limit ".$limit;
        $sqlc="select count(id) from ".DB_PREFIX."cangku_diaobo ".$sqlstr;

        $records = $GLOBALS['db']->getOne($sqlc);
        $list=$GLOBALS['db']->getAll($sql);
//        var_dump($list);die;
        foreach($list as $kl=>$vl){
            $vl['detail']=unserialize($vl['dd_detail']);
            $vl['fromWmName']= $cangku_names[$vl['cid']];
            $vl['toWmName']= $cangku_names[$vl['cidtwo']];
            $vl['transferOrderNo'] = $vl['danjuhao'];
            $vl['updateTime'] = to_date($vl['ctime'],'m-d H:i:s');
            $vl['statusName'] = "";
            $vl['status'] = $vl['isdisable'];
            $vl['amount'] = $vl['zmoney'];
            $list[$kl]=$vl;
        }
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    /**
     * 调拨ajax
     */
    public function diaobo_saving_ajax()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $disable = 1;

        $datailinfo = array();
        foreach($_REQUEST['details'] as $k=>$v){
            $datailinfo[$k]['mid'] = $v['skuId'];
            $datailinfo[$k]['cate_id'] = $v['skuTypeId'];
            $datailinfo[$k]['unit'] = $v['uom'];
            $datailinfo[$k]['funit'] = $v['funit'];
            $datailinfo[$k]['times'] = $v['times'];
            $datailinfo[$k]['yuan_price'] = $v['price'];
            $datailinfo[$k]['name'] = $v['skuName'];
            $datailinfo[$k]['barcode'] = $v['skuCode'];
            $datailinfo[$k]['type'] = $v['type'];
            $datailinfo[$k]['unit_type'] = $v['unit_type'];
            $datailinfo[$k]['price'] = $v['price'];
            $datailinfo[$k]['num'] = $v['planMoveQty'];
            $datailinfo[$k]['ssnum'] = $v['standardInventoryQty'];
            $datailinfo[$k]['zmoney'] = $v['uom'];
            $datailinfo[$k]['memo'] = $v['memo'];
        }
        $dd_detail=serialize($datailinfo);
        $cid=intval($_REQUEST['fromWmId']);
        $cidtwo=intval($_REQUEST['toWmId']);

        //更新仓库
        $detail=$_REQUEST['details'];

        $amount = 0;//总金额

        foreach($detail as $k=>$v){
            $mid=$v['skuId'];
            $order_num=floatval($v['planMoveQty']);
            $unit_type=intval($v['unit_type']);
            if ($unit_type==1){  //使用的是副单位
                $order_num=$order_num*$v['times']; //换算成主单位
            }


            $amount += $order_num*$v['price'];
        }

        $datain=$_REQUEST;
        $datain['ctime']= time()+ 60*60*8;
        $datain['dd_detail']=$dd_detail;
        $datain['slid']=$slid;
        $datain['type'] = $_REQUEST['type'];
        $datain['danjuhao'] = to_date(NOW_TIME,"YmdHis").rand(4);
        $datain['ywsort'] = $_REQUEST['senderId'];
        $datain['cid'] = $_REQUEST['warehouseId'];
        $datain['lihuo_user'] = $account_info['account_name'];
        $datain['cid'] = $cid;
        $datain['cidtwo'] = $cidtwo;
        $datain['znum'] = $order_num;
        $datain['zmoney'] = $amount;
        $datain['zweight'] = 0.00;
        $datain['ztiji'] = 0.00;
        $datain['memo'] = $_REQUEST['memo']?$_REQUEST['memo']:"";
        $datain['isdisable'] = $disable;

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        $return['success'] = true;
        $return['message'] = '保存成功';
        if ($_REQUEST['type']==1){ //入库
            $return['data']['url'] = url("kiz","inventory#go_transfer_index&id=$slid");
        }else{
            $return['data']['url'] = url("kiz","inventory#go_transfer_index&id=$slid");
        }

//        if($res1 && $res2){
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_diaobo", $datain);  //写入调拨记录
//        //写出库记录
//        $datain['ywsort']=5; //仓库调拨
//        $datain['gonghuoren']='cangku_'.$cidtwo;
//        unset($datain['cidtwo']); //销毁入库的仓库ID
//        $datain['type']=2;
//        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入出库记录
//        $datain['cid']=$cidtwo;
//        $datain['type']=1;
//        $datain['gonghuoren']='cangku_'.$cid;
//        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入入库记录
//        }else{
//            $return['success'] = false;
//            $return['message'] = "查无结果！";
//        }
        echo json_encode($return);exit;
    }


    /**
     * 调拨ajax
     */
    public function edit_diaobo_saving_ajax()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $disable = 1;

        $datailinfo = array();
        foreach($_REQUEST['details'] as $k=>$v){
            $datailinfo[$k]['mid'] = $v['skuId'];
            $datailinfo[$k]['cate_id'] = $v['skuTypeId'];
            $datailinfo[$k]['unit'] = $v['uom'];
            $datailinfo[$k]['funit'] = $v['funit'];
            $datailinfo[$k]['times'] = $v['times'];
            $datailinfo[$k]['yuan_price'] = $v['price'];
            $datailinfo[$k]['name'] = $v['skuName'];
            $datailinfo[$k]['barcode'] = $v['skuCode'];
            $datailinfo[$k]['type'] = $v['type'];
            $datailinfo[$k]['unit_type'] = $v['unit_type'];
            $datailinfo[$k]['price'] = $v['price'];
            $datailinfo[$k]['num'] = $v['planMoveQty'];
            $datailinfo[$k]['ssnum'] = $v['standardInventoryQty'];
            $datailinfo[$k]['zmoney'] = $v['uom'];
            $datailinfo[$k]['memo'] = $v['memo'];
        }
        $dd_detail=serialize($datailinfo);
        $cid=intval($_REQUEST['fromWmId']);
        $cidtwo=intval($_REQUEST['toWmId']);

        //更新仓库
        $detail=$_REQUEST['details'];

        $amount = 0;//总金额

        foreach($detail as $k=>$v){
            $mid=$v['skuId'];
            $order_num=floatval($v['planMoveQty']);
            $unit_type=intval($v['unit_type']);
            if ($unit_type==1){  //使用的是副单位
                $order_num=$order_num*$v['times']; //换算成主单位
            }


            $amount += $order_num*$v['price'];
        }

        $datain=$_REQUEST;
        $datain['ctime']= time()+ 60*60*8;
        $datain['dd_detail']=$dd_detail;
        $datain['slid']=$slid;
        $datain['type'] = $_REQUEST['type'];
        $datain['danjuhao'] = to_date(NOW_TIME,"YmdHis").rand(4);
        $datain['ywsort'] = $_REQUEST['senderId'];
        $datain['cid'] = $_REQUEST['warehouseId'];
        $datain['lihuo_user'] = $account_info['account_name'];
        $datain['cid'] = $cid;
        $datain['cidtwo'] = $cidtwo;
        $datain['znum'] = $order_num;
        $datain['zmoney'] = $amount;
        $datain['zweight'] = 0.00;
        $datain['ztiji'] = 0.00;
        $datain['memo'] = $_REQUEST['memo']?$_REQUEST['memo']:"";
        $datain['isdisable'] = $disable;

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        $return['success'] = true;
        $return['message'] = '保存成功';
        if ($_REQUEST['type']==1){ //入库
            $return['data']['url'] = url("kiz","inventory#go_transfer_index&id=$slid");
        }else{
            $return['data']['url'] = url("kiz","inventory#go_transfer_index&id=$slid");
        }

//        if($res1 && $res2){
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_diaobo", $datain,'update','id='.$id);  //写入调拨记录
//        //写出库记录
//        $datain['ywsort']=5; //仓库调拨
//        $datain['gonghuoren']='cangku_'.$cidtwo;
//        unset($datain['cidtwo']); //销毁入库的仓库ID
//        $datain['type']=2;
//        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入出库记录
//        $datain['cid']=$cidtwo;
//        $datain['type']=1;
//        $datain['gonghuoren']='cangku_'.$cid;
//        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入入库记录
//        }else{
//            $return['success'] = false;
//            $return['message'] = "查无结果！";
//        }
        echo json_encode($return);exit;
    }

    public function diaobo_index_doconfirm(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];

        $row = $GLOBALS['db']->getRow("select * from fanwe_cangku_diaobo where id=".$id);
        $details = unserialize($row['dd_detail']);
//        var_dump($row);die;
        $cid = $row['cid'];
        $cidtwo = $row['cidtwo'];
//var_dump($details);
        foreach ($details as $v) {
            $mid = $v['mid'];
            $order_num = $v['num'];
//            var_dump($v);die;
            //减库
            $sqlstr="where slid=$slid and mid=$mid and cid=$cid";	 //减库条件
            $sqlstrtwo="where slid=$slid and mid=$mid and cid=$cidtwo";	 //加库条件
//            var_dump("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
//            die;
            $res1=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
//var_dump($res1);die;
            $check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstrtwo);
            if($check){
                $res2=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,stock=stock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstrtwo);
            }else{
                //添加
                $data_menu=array(
                    "slid"=>$slid,
                    "mid"=>$mid,
                    "cid"=>$cidtwo,
                    "cate_id"=>$v['cate_id'],
                    "mbarcode"=>$v['barcode'],
                    "mname"=>$v['name'],
                    "mstock"=>$order_num,
                    "stock"=>$order_num,
                    "minStock"=>10,
                    "maxStock"=>10000,
                    "unit"=>$v['unit'],
                    "funit"=>$v['funit'],
                    "times"=>$v['times'],
                    "type"=>$v['type'],
                    "ctime"=>to_date(NOW_TIME)
                );
                $res2=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $data_menu ,"INSERT");
            }
        }
        //更新单据状态
        $sql = "update fanwe_cangku_diaobo set isdisable=2 where id=$id";
        $res = $GLOBALS['db']->query($sql);
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        //确认移库单的时候新增出入库记录
        $datain=$details;
        $datain['ctime']= time()+ 60*60*8;
        $datain['dd_detail']=$row['dd_detail'];
        $datain['slid']=$slid;
        $datain['type'] = $row['type'];
        $datain['danjuhao'] = to_date(NOW_TIME,"YmdHis").rand(4);
        $datain['cid'] = $row['cid'];
        $datain['lihuo_user'] = $row['lihuo_user'];
        $datain['cid'] = $cid;
        $datain['cidtwo'] = $cidtwo;
        $datain['znum'] = $row['znum'];
        $datain['zmoney'] = $row['zmoney'];
        $datain['zweight'] = $row['zweight'];
        $datain['ztiji'] = $row['ztiji'];
        $datain['memo'] = $row['memo']?$row['memo']:"";
        $datain['isdisable'] = 2;

        $datain['ywsort']=5; //仓库调拨
        $datain['gonghuoren']='cangku_'.$cidtwo;
        unset($datain['cidtwo']); //销毁入库的仓库ID
        $datain['type']=2;
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入出库记录
        $datain['cid']=$cidtwo;
        $datain['type']=1;
        $datain['gonghuoren']='cangku_'.$cid;
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入入库记录


        if($res){//成功
            $return['success'] = true;
            $return['message'] = '操作成功';
        }else{

            $return['success'] = false;
            $return['message'] = '操作失败';
        }

        /* 数据 */
        echo json_encode($return);exit;
    }

    public function diaobo_index_withdraw(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];

        $row = $GLOBALS['db']->getRow("select * from fanwe_cangku_diaobo where id=".$id);
        $details = unserialize($row['dd_detail']);
        $cid = $row['cid'];
        $cidtwo = $row['cidtwo'];

        foreach ($details as $v) {
            $mid = $v['mid'];
            $order_num = $v['num'];

            //减库
            $sqlstr="where slid=$slid and mid=$mid and cid=$cid";	 //减库条件
            $sqlstrtwo="where slid=$slid and mid=$mid and cid=$cidtwo";	 //加库条件
//            var_dump("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
//            die;
            $res1=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
//var_dump($res1);die;
            $check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstrtwo);
            $res2=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,stock=stock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstrtwo);
        }
//更新单据状态
        $sql = "update fanwe_cangku_diaobo set isdisable=1 where id=$id";
        $res = $GLOBALS['db']->query($sql);
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        if($res){//成功
            $return['success'] = true;
            $return['message'] = '操作成功';
        }else{

            $return['success'] = false;
            $return['message'] = '操作失败';
        }

        /* 数据 */
        echo json_encode($return);exit;
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
        echo json_encode($listsort);exit;
    }

    /**
     * 门店列表ajax
     */
    public function location_list_ajax()
    {
        /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $GLOBALS['account_info']['slid'];
        $account_info['is_main']=$GLOBALS['db']->getOne("select is_main from fanwe_supplier_location where id=".$slid);
        if ($account_info['is_main']=='1'){
            $slidlist=$GLOBALS['db']->getAll("select id from fanwe_supplier_location where supplier_id=".$supplier_id);
            $account_info['location_ids']= array_reduce($slidlist, create_function('$v,$w', '$v[]=$w["id"];return $v;'));
        }
        /* 业务逻辑部分 */
        $conditions = " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
        $conditions .= " and id in(" . implode(",", $account_info['location_ids']) . ") ";

        $sql = " select distinct(id),name,address,concat_ws(',',ypoint,xpoint) as latlong from " . DB_PREFIX . "supplier_location";
        $list = $GLOBALS['db']->getAll($sql.$conditions . " order by id desc");
        $return['current'] = array();
        $return['more'] = array();
        foreach($list as $v){
            if($v['id'] == $slid){
                $return['current'] = $v;
            }else{
                $return['more'][] = $v;
            }
        }
        echo json_encode($return);exit;
    }

    //查询仓库
    public function dc_cangku_ajax(){
        init_app_page();
        $slid = intval($_REQUEST['id'])?intval($_REQUEST['id']):$GLOBALS['account_info']['slid'];;
        $isdd = $_REQUEST['isDisable'];
        $kw = $_REQUEST['warehouseName'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $where="where 1=1";
        $where.=' and slid='.$slid;

        if($kw){
            $where = " and name like '%$kw%'";
        }
        if(isset($isdd)){
            $where .= " and isdisable=$isdd";
        }
        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku $where order by id desc limit $limit ");
        $records = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."cangku ".$where);
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        $cangkuArray = array();
        foreach($list as $k=>$v){
            $cangkuArray[$k]['id'] = $v['id'];
            $cangkuArray[$k]['wareshouseCode'] = '';
            $cangkuArray[$k]['warehouseName'] = $v['name'];
            $cangkuArray[$k]['createTime'] = '';
            $cangkuArray[$k]['updateTime'] = '';
            $cangkuArray[$k]['isDisable'] = $v['isdisable'];
            $cangkuArray[$k]['isdeal'] = $v['isdeal'];
            $cangkuArray[$k]['deductionName'] = '';
        }

        $return['dataList'] = $cangkuArray;
        echo json_encode($return);exit;
    }


    /**
     * 操作仓库
     */
    public function dc_cangku_add_ajax(){
        init_app_page();
        $slid = intval($_REQUEST['slid'])?intval($_REQUEST['slid']):$GLOBALS['account_info']['slid'];
        $id = intval($_REQUEST['id']);
        $cangkuArray['slid'] = $slid;
        $cangkuArray['tel'] = '';
        $cangkuArray['address'] = '';
        $cangkuArray['contact'] = '';
        $cangkuArray['name'] = $_REQUEST['warehouseName'];
        $cangkuArray['isdisable'] = $_REQUEST['isDisable'];
        $cangkuArray['isdeal'] = $_REQUEST['isdeal'];
         if($id > 0){
             $cangkuexsit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."cangku  where slid=".$slid." and id='".$id."'");
            if(!$cangkuexsit){
                $return['success'] = false;
                $return['message'] = "仓库名不存在！";
                echo json_encode($return);exit;
            }
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku", $cangkuArray ,"UPDATE","id=".$id);
        }else{
             $cangkuexsit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."cangku  where slid=".$slid." and name='".$_REQUEST['warehouseName']."'");
             if($cangkuexsit){
                $return['success'] = false;
                $return['message'] = "仓库名已存在！";
                echo json_encode($return);exit;
            }
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku", $cangkuArray ,"INSERT");
        }

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }


    /**
     * 仓库删除
     */
    public function ajax_setting_del()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        if($id > 0){
            $cangkuQqqqq = "select * from fanwe_cangku_menu WHERE cid=".$id." and slid = ".$slid;
            $res = $GLOBALS['db']->getAll($cangkuQqqqq);
            if(count($res) > 0){
                $return['success'] = false;
                $return['message'] = "操作失败，该仓库已经产生商品";
            }else{
                $deleteSQL = "delete from fanwe_cangku WHERE id=".$id." and slid = ".$slid;
                $res = $GLOBALS['db']->query($deleteSQL);
                if($res){
                    $return['success'] = true;
                    $return['message'] = "操作成功";
                }else{
                    $return['success'] = false;
                    $return['message'] = "操作失败";
                }
            }
            echo json_encode($return);exit;

        }
    }

    /**
     * 原料类别删除
     */
    public function ajax_category_del()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['ids'];
        if($id > 0){
            $cangkuQqqqq = "select * from fanwe_dc_menu WHERE cate_id=".$id." and location_id = ".$slid;
            $res = $GLOBALS['db']->getAll($cangkuQqqqq);
            if(count($res) > 0){
                $return['success'] = false;
                $return['message'] = "操作失败，该分类已经产生商品";
            }else{
                $deleteSQL = "delete from fanwe_dc_supplier_menu_cate WHERE id=".$id." and location_id = ".$slid;
                $res = $GLOBALS['db']->query($deleteSQL);
                if($res){
                    $return['success'] = true;
                    $return['message'] = "操作成功";
                }else{
                    $return['success'] = false;
                    $return['message'] = "操作失败";
                }
            }
            echo json_encode($return);exit;

        }
    }

    public function basic_warehouse_list_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $where = "where g.is_effect=0 and g.is_stock=1 and g.location_id=$slid";
        if($_REQUEST['skuTypeId']){
            $where .= " and g.cate_id=".$_REQUEST['skuTypeId'];
        }

        if($_REQUEST['skuTypeId']){
            $where .= " and g.cate_id=".$_REQUEST['skuTypeId'];
        }
        if($_REQUEST['skuCodeOrName']){
            $where .= " and (g.name like'%".$_REQUEST['skuCodeOrName']."%' or g.id like'%".$_REQUEST['skuCodeOrName']."%' or g.pinyin like'%".$_REQUEST['skuCodeOrName']."%')";
        }
        if($_REQUEST['wmType']>-1){
            $where .= " and g.print=".$_REQUEST['wmType'];
        }
        $sqlcount = "select count(id) as count from fanwe_dc_menu g $where";

        $r = $GLOBALS['db']->getRow($sqlcount);
        if(!empty($records)){
            $records = $r['count'];
        }else{
            $records = 0;
        }
        $sql = "select *,g.name as standerStr,g.id as id from fanwe_dc_menu g LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id $where limit $limit";

        $check=$GLOBALS['db']->getAll($sql);
//var_dump(count($check));
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_diaobo' ");print_r($table);exit;
//        var_dump($check[0]);exit;
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        if($check){
            $arr_list = array();
            foreach ($check as $item) {
                $str ='';
                $list = array();
                $list['id'] = $item['id'];
                $list['wmType'] = '';
                $list['skuTypeName'] = $this->get_dc_supplier_menu($item['cate_id']);
                $list['wmTypeName'] = $this->get_print($item['print']);
                $list['skuCode'] = $item['id'];
                $list['standerStr'] = $item['standerStr'];
                if(!empty($item['funit'])){
                    $str = "【".$item['funit']."(".$item['times'].")"."】";
                }
                $list['unitName'] = $item['unit'];
                $list['price'] = $item['price'];
                $list['purchasePrice'] = $item['buyPrice'];
                $list['costPrice'] = $item['sellPrice2'];
                $list['balancePrice'] = $item['customerPrice'];
                $list['status'] = 1;
                $list['isDisable'] = 1;
                array_push($arr_list,$list);
            }

            $return['dataList'] = $arr_list;
        }else{
            $return['status'] = false;
            $return['resMsg'] = "查无结果！";
        }
        echo json_encode($return);exit;
    }


    public function ajax_warehouse_del()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['skuIds'];
        if($id > 0){
            $sqlsort = "delete from " . DB_PREFIX . "dc_menu where id=".$id." and location_id =".$slid ;
            $res = $GLOBALS['db']->query($sqlsort);

            if($res){
                $return['success'] = true;
                $return['message'] = "操作成功";
            }else{
                $return['success'] = false;
                $return['message'] = "操作失败";
            }
            echo json_encode($return);exit;
        }else{
            $return['success'] = false;
            $return['message'] = "请选择进行操作";
        }
        echo json_encode($return);exit;
    }



    function get_dc_supplier_menu($id = 30){
        $check=$GLOBALS['db']->getRow("select * from fanwe_dc_supplier_menu_cate where id = ".$id);
        if($check){
            if(empty($check['name'])){
                return '<span style="color:red">顶级分类</span>';
            }else{
                return $check['name'];
            }
        }else{
            return '';
        }
    }


    function get_print($print){
        foreach ($this->kcnx as $key=>$value) {
            if($print == $key){
                return $value;
            }
        }

    }

    /**
     * 期初库存导入分类处理
     * @param $catename
     * @return mixed
     */
    private function basic_master_category($catename){
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_dc_supplier_menu_cate' ");print_r($table);exit;
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $GLOBALS['account_info']['slid'];

        $account_info['is_main'] = $GLOBALS['db']->getOne("select is_main from fanwe_supplier_location where id=".$slid);
        if ($account_info['is_main']=='1'){
            $slidlist=$GLOBALS['db']->getAll("select id from fanwe_supplier_location where supplier_id=".$supplier_id);
            $account_info['location_ids']= array_reduce($slidlist, create_function('$v,$w', '$v[]=$w["id"];return $v;'));
        }
        $conditions = " where wlevel<4 and is_effect=0"; // 查询条件
        // 只查询支持门店的
        $conditions .= " and location_id=$slid and location_id in(" . implode(",", $account_info['location_ids']) . ") ";
        $conditions .= " and name='".$catename."'";
        $sql = " select id,name from " . DB_PREFIX . "dc_supplier_menu_cate ".$conditions;
        $categoryinfo = $GLOBALS['db']->getRow($sql);
        if($categoryinfo){
            return $categoryinfo['id'];
        }else{
            $data['name'] = $catename;
            $data['sort'] = 0;
            $data['is_effect'] = 0;
            $data['supplier_id'] = $supplier_id;
            $data['location_id'] = $slid;
            $data['wcategory'] = 0;
            $data['wlevel'] = 0;

            $GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate", $data ,"INSERT");
            $cate_id = $GLOBALS['db']->insert_id();
            return $cate_id;
        }
    }

    /**
     * 期初库存导入
     * @param $name
     * @param $cate_id
     * @param $price
     * @param $unit
     * @param $num
     * @param $print
     * @return mixed
     */
    private function basic_master_dc_menu($name,$cate_id,$price,$unit,$num,$print){
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_dc_menu' ");print_r($table);exit;
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $GLOBALS['account_info']['slid'];
        $dc_menu_data=array(
            "location_id"=>$slid,
            "supplier_id"=>$supplier_id,
            "barcode"=>'',
            "name"=>$name,
            "cate_id"=>$cate_id,
            "price"=>$price,
            "unit"=>$unit,
            "stock"=>$num,
            "print"=>$print,
            "is_stock" =>1
        );
        $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $dc_menu_data ,"INSERT");
        $dc_menu_id = $GLOBALS['db']->insert_id();
        return $dc_menu_id;
    }
    /**
     * 期初库存ajax
     */
    public function basic_master_import_ajax()
    {
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_menu' ");print_r($table);exit;
        //接收前台文件
        $ex = $_FILES['file'];
        $warehouseId = $_REQUEST['warehouseId'];
        $cangku = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."cangku where id=$warehouseId");
        //重设置文件名
        $filename = time().substr($ex['name'],stripos($ex['name'],'.'));
        $path = APP_ROOT_PATH.'public/excel/'.$filename;//设置移动路径
        move_uploaded_file($ex['tmp_name'],$path);
        //表用函数方法 返回数组
        $list = $this->_readExcel($path);
        $total = count($list);
        if($total<3){
            $return['success'] = false;
            $return['message'] = '数据为空，不能导入！';
        }
        $insertSQL = "insert into ".DB_PREFIX."cangku_menu (slid,mid,cid,cate_id,stock,mstock,unit) values";
        for($i=2;$i<$total;$i++){
            $print = $list[$i][2];//类型
            $dc_menu_id = $list[$i][3];//编号
            $dc_menu_name = $list[$i][4];//名称
            $dc_unit = $list[$i][5];//单位
            $num = $list[$i][6];//数量
            $dc_price = $list[$i][7];//价格
            if(!$dc_menu_id){
                $dc_cate_id = $this->basic_master_category($list[$i][1]);
                $dc_menu_id = $this->basic_master_dc_menu($dc_menu_name,$dc_cate_id,$dc_price,$dc_unit,$num,$print);
            }

            $dc_menu = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=$dc_menu_id");

            //退商品表库存
            $GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$num where id=$dc_menu_id");

            if($dc_menu){
                $res = $GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set mstock=mstock+$num,stock=stock+$num where id=$dc_menu_id");
                $insertSQL .= "('".$dc_menu["supplier_id"]."','".$dc_menu_id."','".$warehouseId."','".$dc_menu["cate_id"]."','".$num."','".$num."','".$dc_menu['unit']."'),";
            }
        }
        $insertSQL = rtrim($insertSQL,',');
        $GLOBALS['db']->query("delete from ".DB_PREFIX."cangku_menu where slid=".$dc_menu['supplier_id']." and cid=$warehouseId");
        $insertres =  $GLOBALS['db']->query($insertSQL);

        //写记录表
        $insertMaster = "insert into ".DB_PREFIX."master_import_log (supplier_id,location_id,account_id,warehouseName,createTime) values (".$GLOBALS['account_info']['supplier_id'].",".$GLOBALS['account_info']['slid'].",".$GLOBALS['account_info']['id'].",'".$cangku["name"]."',".time().")";
        $insertMres =  $GLOBALS['db']->query($insertMaster);
        //echo $insertres;echo "|";echo $insertMaster;echo $insertMres; exit;
        if($insertMres && $insertres){
            $return['success'] = true;
            $return['message'] = '操作成功！';
        }else{
            $return['success'] = false;
            $return['message'] = '操作失败！';
        }
        echo json_encode($return);exit;
    }

    //创建一个读取excel数据，可用于入库
    private function _readExcel($path)
    {
        //引用PHPexcel 类
        require APP_ROOT_PATH . 'app/Classes/PHPExcel.php';
        $type = 'Excel5';//设置为Excel5代表支持2003或以下版本，Excel2007代表2007版
        $xlsReader = PHPExcel_IOFactory::createReader($type);
        $xlsReader->setReadDataOnly(true);
        $xlsReader->setLoadSheetsOnly(true);
        $Sheets = $xlsReader->load($path);
        //开始读取上传到服务器中的Excel文件，返回一个二维数组
        $dataArray = $Sheets->getSheet(0)->toArray();
        return $dataArray;
    }

    /**
     * 期初库存操作日志
     */
    public function get_master_import_log_ajax(){
        init_app_page();
        $slid = intval($_REQUEST['id'])?intval($_REQUEST['id']):$GLOBALS['account_info']['slid'];;

        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $where="where 1=1";
        $where.=' and l.location_id='.$slid;

        $list = $GLOBALS['db']->getAll("SELECT l.*,from_unixtime(l.createTime) as createTime,a.account_name as username  FROM " . DB_PREFIX . "master_import_log l left join ".DB_PREFIX."supplier_account a on l.account_id=a.id $where order by l.id desc limit $limit ");

        $records = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."master_import_log l ".$where);
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    /**
     * 原料类别（is_effect=0）
     */
    public function get_dc_menu_category_ajax(){
        /* 基本参数初始化 */
        init_app_page();
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $GLOBALS['account_info']['slid'];
        $account_info['is_main']=$GLOBALS['db']->getOne("select is_main from fanwe_supplier_location where id=".$slid);
        if ($account_info['is_main']=='1'){
            $slidlist=$GLOBALS['db']->getAll("select id from fanwe_supplier_location where supplier_id=".$supplier_id);
            $account_info['location_ids']= array_reduce($slidlist, create_function('$v,$w', '$v[]=$w["id"];return $v;'));
        }
        $conditions = " where wlevel<4 and is_effect=0"; // 查询条件
        // 只查询支持门店的
        $conditions .= " and location_id=$slid and location_id in(" . implode(",", $account_info['location_ids']) . ") ";
        if($_REQUEST['typeCodeOrName']){
            $conditions .= " and name like '%".$_REQUEST['typeCodeOrName']."%'";
        }
        $sql = " select id,name,name as typeName, is_effect,is_effect as isDisable,null as parentTypeCode,null as typeCode,null as isDisableName,null as dishTypeId,null as updateTime,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";

        $list = array();

        $wsublist = array();
        $wmenulist = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc limit $limit");

        foreach($wmenulist as $wmenu)
        {
            if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
        }
        foreach($wmenulist as $wmenu0)
        {
            if($wmenu0['wcategory'] == '0')
            {
                $wmenu0['parentTypeName'] = "";
                $list[] = $wmenu0;
                foreach($wsublist[$wmenu0['id']] as $wmenu1)
                {
                    $wmenu1['parentTypeName'] = $wmenu0['name'];
                    $list[] = $wmenu1;
                    foreach($wsublist[$wmenu1['id']] as $wmenu2)
                    {
                        $wmenu2['parentTypeName'] = $wmenu1['name'];
                        $list[] = $wmenu2;
                        foreach($wsublist[$wmenu2['id']] as $wmenu3)
                        {
                            $wmenu3['parentTypeName'] = $wmenu2['name'];
                            $list[] = $wmenu3;
                        }
                    }
                }
            }
        }
        $records = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."dc_supplier_menu_cate ".$conditions);
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    /**
     * 原料类别添加ajax
     */
    public function dc_menu_category_add_ajax(){
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_dc_supplier_menu_cate' ");print_r($table);exit;

        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = intval($_REQUEST['slid'])?intval($_REQUEST['slid']):$GLOBALS['account_info']['slid'];
        $id = intval($_REQUEST['id']);
        $cateArray['is_effect'] = 0;//原料为0
        $cateArray['icon_img'] = '';
        $cateArray['iconcolor'] = '';
        $cateArray['iconfont'] = '';
        $cateArray['name'] = $_REQUEST['typeName'];
        $cateArray['sort'] = $_REQUEST['sort'];
        $cateArray['supplier_id'] = $supplier_id;
        $cateArray['location_id'] = $slid;
        $cateArray['wcategory'] = $_REQUEST['parentId'];//父分类

        //编辑
        if($id > 0){
            if($_REQUEST['wcategory']){
                $parentCategory = $GLOBALS['db']->getRow("select name from ".DB_PREFIX."dc_supplier_menu_cate  id=".$_REQUEST['parentId']);
                if(!$parentCategory){
                    $return['success'] = false;
                    $return['message'] = "父分类不存在！";
                    echo json_encode($return);exit;
                }
                $cateArray['wlevel'] = $parentCategory['wlevel']+1;
            }else{
                $cateArray['wlevel'] = 0;
            }

            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate", $cateArray ,"UPDATE","id=".$id);
        }else{
            if($_REQUEST['wcategory']){
                $parentCategory = $GLOBALS['db']->getRow("select name from ".DB_PREFIX."dc_supplier_menu_cate  id=".$_REQUEST['parentId']);
                if(!$parentCategory){
                    $return['success'] = false;
                    $return['message'] = "父分类不存在！";
                    echo json_encode($return);exit;
                }
                $cateArray['wlevel'] = $parentCategory['wlevel']+1;
            }else{
                $cateArray['wlevel'] = 0;
            }

            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate", $cateArray ,"INSERT");
        }

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    /**
     * 原料设置ajax
     */
    public function yuanliao_set_ajax(){
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_menu' ");print_r($table);exit;
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = intval($account_info['slid']);
        $id = intval($_REQUEST['id']);

        $skuPrice = json_decode($_REQUEST['skuPrice']);
        $skuUnit = json_decode($_REQUEST['skuUnit']);

        $unit = "";
        $funit = "";
        $times = 0;
        if(count($skuUnit)>1){
            foreach($skuUnit as $k=>$v){
                if($v->unitSmall==1){
                    $unit = $v->unitName;
                }else{
                    $funit = $v->unitName;
                    $times = $v->skuConvert;
                }
            }
        }else{
            $unit = $skuUnit[0]->unitName;
        }

        //$standard = $_REQUEST['standard'];//规格
        $skuList = json_decode($_REQUEST['skuList']);
        if($id > 0){//编辑
            $dc_menu_data=array(
                "id"=>$id,
                "name"=>$skuList->skuName,
                "buyPrice"=>$skuPrice->purchasePrice,//采购价
                "price"=>$skuPrice->price,//售价
                "customerPrice"=>$skuPrice->balancePrice,//结算价
                "sellPrice2"=>$skuPrice->costPrice,//成本价
                "barcode"=>$skuList->barCode,
                "print"=>$skuList->wmType,
                "pinyin"=>$skuList->skuAliasName,//拼音码
            );
            if($unit){
               $dc_menu_data =  array_merge($dc_menu_data,array("unit"=>$unit));
            }

            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $dc_menu_data ,"UPDATE","id=".$id);
            if($res){
                $return['success'] = true;
                $return['message'] = "操作成功";
            }else{
                $return['success'] = false;
                $return['message'] = "操作失败";
            }
            echo json_encode($return);exit;
        }

        $dc_menu_data=array(
            "location_id"=>$slid,
            "supplier_id"=>$supplier_id,
            "barcode"=>$skuList->barCode,
            "name"=>$skuList->skuName,
            "pinyin"=>$skuList->skuAliasName,
            "cate_id"=>$skuList->skuTypeId,
            "unit"=>$unit,
            "funit"=>$funit,
            "times"=>$times,
            "type"=>'',
            "buyPrice"=>$skuPrice->purchasePrice,//采购价
            "price"=>$skuPrice->price,
            "customerPrice"=>$skuPrice->balancePrice,//结算价
            "sellPrice2"=>$skuPrice->costPrice,//成本价
            "print"=>$skuList->wmType,
            "is_stock"=>1,
            "chupinliu"=>$skuList->yieldRate
        );
        if($skuList->skuCode){
            $dc_menu_data['id'] = $skuList->skuCode;
            $dc_exsit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where  id=".$skuList->skuCode);
            if($dc_exsit){
                $return['success'] = false;
                $return['message'] = "编号重复，不能添加";
                echo json_encode($return);exit;
            }
        }

        $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $dc_menu_data ,"INSERT");
        $mid = $GLOBALS['db']->insert_id();
        if($mid){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    /**
     * 库存查询
     */
    public function stock_search_ajax(){
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_menu' ");print_r($table);exit;
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        $skuNameOrCode = $_REQUEST['skuNameOrCode'];
        $print = $_REQUEST['print'];
        $skuTypeIds = $_REQUEST['skuTypeIds'];
        $warehouseId = $_REQUEST['warehouseId'];


        if($page==0) $page = 1;
            $limit = (($page-1)*$page_size).",".$page_size;
    //        $where = " WHERE 1=1";
        $where = " where (( g.is_effect = 0 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1)) and aa.slid=$slid";
        if($skuNameOrCode){
            $where .= " and (g.name like '%".$skuNameOrCode."%' or g.barcode LIKE '%".$skuNameOrCode."%' or g.id LIKE '%".$skuNameOrCode."%' or g.pinyin LIKE '%".$skuNameOrCode."%')";
        }
        if($print>-1){
            $where .= " and g.print = $print";
        }else{
            $where .= " and g.print > 1 ";
        }
        if($skuTypeIds>-1){
            $parentids = parent::get_dc_supplier_cate($skuTypeIds);
            $where .= " and g.cate_id in ( $parentids )";
        }

        if(!empty($warehouseId)){
            $where .= " and aa.cid=".$warehouseId;
        }

//var_dump($where);die;
        $sqlrecords="select count(0) from fanwe_cangku_menu aa INNER JOIN fanwe_cangku fc on fc.id=aa.cid INNER join fanwe_dc_menu g on g.id=aa.mid".$where;
        $sql="select *,sum(g.price) as spirce,sum(g.buyPrice) as sbuy,sum(aa.mstock) as sstock,fc.name as cname from fanwe_cangku_menu aa INNER JOIN fanwe_cangku fc on fc.id=aa.cid INNER join fanwe_dc_menu g on g.id=aa.mid".$where." group by g.id limit ".$limit;



        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//        var_dump($list);
        $arr = [];
        foreach ($list as $key=>$item) {
            if($item['print'] != 3){
                $price =$item['sbuy'];
            }else{
                $price =$item['price'];
            }
//var_dump($price);
            $arr[$key]['mid'] =$item['mid'];
            $arr[$key]['commercialName'] =$account_info['slname'];
//            $arr[$key]['warehouseName'] = $item['cname'];
            $arr[$key]['skuCode'] =$item['mbarcode'];
            $arr[$key]['skuName'] =$item['name'];
            $arr[$key]['marketPrice'] = $price;
            $arr[$key]['cost'] =$price*$item['sstock'];
            $arr[$key]['uom'] =$item['unit'];
            $arr[$key]['qty'] = intval($item['sstock']);
            $arr[$key]['print'] =$item['print'];
            $arr[$key]['cate_id'] =$item['cate_id'];
        }

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $arr;

        echo json_encode($return);exit;
    }
    //出入库汇总明细表
    public function report_stock_detail_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $cid = $_REQUEST['cid']; //仓库ID
        $cate_id = $_REQUEST['cate_id']; //分类ID

        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $sql="select id,name,barcode,cate_id,is_delete,unit,funit,time from fanwe_dc_menu where $sqltr limit ".$limit;
        $records = $GLOBALS['db']->getOne($sql);
        echo json_encode($records);exit;

    }

    /**
     * 库存分布明细
     * 2017-4-18
     */
    public function report_stock_dubbo_ajax(){
        init_app_page();
//        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
//        $page = intval($_REQUEST['page']);
//        if($page==0) $page = 1;
//        $limit = (($page-1)*$page_size).",".$page_size;
//        var_dump($_REQUEST);
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $skuNameOrCode = $_REQUEST['skuNameOrCode'];
        $skuTypeIds = $_REQUEST['skuTypeIds'];
        $wmIds = $_REQUEST['wmIds'];
        $print = $_REQUEST['print'];

        if (($_REQUEST['confirmDateStart'])|| ($_REQUEST['confirmDateEnd'])){
            $begin_time = strim($_REQUEST['confirmDateStart']);
            $end_time = strim($_REQUEST['confirmDateEnd']);
        }
//        else{	 //默认为当月的
//            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
//            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
//        }
//        $begin_time_s = strtotime($begin_time);
//        $end_time_s = strtotime($end_time);
        $sqlstr = " where (( g.is_effect = 0 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1)) and fc.slid=$location_id";

        if($skuNameOrCode){
            $sqlstr .= " and (g.name like '%".$skuNameOrCode."%' or g.id LIKE '%".$skuNameOrCode."%' or g.pinyin LIKE '%".$skuNameOrCode."%')";
        }
        if($print>-1){
            $sqlstr .= " and g.print = $print";
        }else{
            $sqlstr .= " and g.print <> 1 ";
        }

        if($begin_time){
            $sqlstr .=" and fc.ctime > ".$begin_time." ";
        }
        if($end_time){
            $sqlstr .=" and fc.ctime < ".$end_time." ";
        }
        if($skuTypeIds){
            $sqlstr.=' and ( fc.cate_id='.$skuTypeIds.')';
        }
        if($wmIds){
            $sqlstr.=' and ( fc.cid='.$wmIds.')';
        }
//        $sql = 'select * from fanwe_cangku_menu';
//        $row = $GLOBALS['db']->query($sql);
//        var_dump($row);
//        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
//        $sqlrecords="select count(a.id) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";
//        $return = array();
//        $return['skuVOs'] = array();
//        $output = $return['skuVOs'];
        $sql = "select * from fanwe_cangku_menu fc LEFT JOIN fanwe_dc_menu g on g.id =fc.mid $sqlstr group by fc.mid";
//        $sql = "select * from fanwe_cangku_menu fc where cid=209";
        $row = $GLOBALS['db']->getAll($sql);
//var_dump($row);
        $output['skuVOs'] = array();
        foreach ($row as $key=>$item) {
            $sql = "select * from fanwe_cangku_log fc where fc.cid=".$item['cid'];
            $category  = parent::get_dc_current_supplier_cate($item['cate_id']);
            $wcategory  = parent::get_dc_current_supplier_cate($category['wcategory']);

            $output['skuVOs'][$key]['isDelete'] = 0;
            $output['skuVOs'][$key]['isDisable'] = 0;
            $output['skuVOs'][$key]['qtySum'] =0;
            $output['skuVOs'][$key]['skuCode'] = $item['mid'];
            $output['skuVOs'][$key]['skuId'] = $item['mid'];
            $output['skuVOs'][$key]['skuName'] = $item['name'];
            $output['skuVOs'][$key]['uom'] = $item['unit'];
            $output['skuVOs'][$key]['skuParentTypeName'] = !$wcategory['name']?'<span style="color:red">顶级分类</span>':$wcategory['name'];
            $output['skuVOs'][$key]['skuTypeName'] = $category['name'];

            //根据mid查询仓库信息
            $msqlstr = ' where 1=1';
            if($skuTypeIds){
                $msqlstr.=' and ( fc.cate_id='.$skuTypeIds.')';
            }
            if($wmIds){
                $msqlstr.=' and ( fc.cid='.$wmIds.')';
            }
            $csql = "select *,fc.mstock as fstock from fanwe_cangku_menu fc inner JOIN fanwe_cangku cc on fc.cid = cc.id INNER JOIN fanwe_dc_menu f on f.id =fc.mid $msqlstr and fc.mid=".$item['mid'];
//            $csql = "select * from fanwe_cangku_menu fc where cid=209";
            $row2 = $GLOBALS['db']->getAll($csql);
            $mtock = 0;
//var_dump($row2);

            if(empty($row2)){
                $output['skuVOs'][$key]['titleVOs']=[];
            }
            foreach ($row2 as $key2=>$item2) {

//                var_dump($item2);
                if($item['print'] != 3){
                    $price =$item['buyPrice'];
                }else{
                    $price =$item['price'];
                }
                $mtock += $item2['fstock'];
                $cangku = parent::get_cangku_list($item2['cid']);
                $output['skuVOs'][$key]['titleVOs'][$key2]['price']=$price;
                $output['skuVOs'][$key]['titleVOs'][$key2]['priceSum']=$price;
                $output['skuVOs'][$key]['titleVOs'][$key2]['amount']=$price*intval($item2['fstock']);
                $output['skuVOs'][$key]['titleVOs'][$key2]['amountSum']=$price*intval($item2['fstock']);
                $output['skuVOs'][$key]['titleVOs'][$key2]['commercialId']=$account_info['slid'];
                $output['skuVOs'][$key]['titleVOs'][$key2]['commercialName']=$account_info['slname'];
                $output['skuVOs'][$key]['titleVOs'][$key2]['qty']= intval($item2['fstock']);
                $output['skuVOs'][$key]['titleVOs'][$key2]['qtySum']=intval($item2['fstock']);
                $output['skuVOs'][$key]['titleVOs'][$key2]['warehouseId']=$item2['cid'];
                $output['skuVOs'][$key]['titleVOs'][$key2]['warehouseName']= $cangku['name'];

            }
            $output['skuVOs'][$key]['qtySum'] += $mtock;
        }
//        var_dump($output);
//        $records = $GLOBALS['db']->getOne($sqlrecords);
//        $list = $GLOBALS['db']->getAll($sql);
//
//        $return['page'] = $page;
//        $return['records'] = $records;
//        $return['total'] = ceil($records/$page_size);
//        $return['status'] = true;
//        $return['resMsg'] = null;
//
//        $return['dataList'] = $output;
        echo json_encode($output);exit;
    }

    //供应商类别列表
    public function supplier_cate_index_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];;
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        $isDisable = trim($_REQUEST['isDisable']);
        $supplierCateCode = trim($_REQUEST['supplierCateCode']);
        $supplierCateName = trim($_REQUEST['supplierCateName']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $where = ' 1=1 and slid= '.$slid;
        if($isDisable != ''){
            $where .= ' and state='.$isDisable;
        }
        if($supplierCateCode){
            $where .= " and supplierCode like '%".$supplierCateCode."%'";
        }
        if($supplierCateName){
            $where .= " and supplierName like '%".$supplierCateName."%'";
        }

        $sql="select * from fanwe_gys_category where $where order by createTime desc limit ".$limit;
//        $sql="select * from fanwe_gys_category";

        $sqlc="select count(0) from fanwe_gys_category where slid=$slid ";

        $total = $GLOBALS['db']->getOne($sqlc);
        $list=$GLOBALS['db']->getAll($sql);
//        var_dump($sql);die;
        $arr = [];
        foreach ($list as $key=>$item) {
            $arr[$key]['id'] =$item['id'];
            $arr[$key]['slid'] =$item['slid'];
            $arr[$key]['supplierCateCode'] =$item['supplierCode'];
            $arr[$key]['supplierCateName'] =$item['supplierName'];
            $arr[$key]['createTime'] = date('Y-m-d H:i:s',$item['createTime']);
            $arr[$key]['updateTime'] = date('Y-m-d H:i:s',$item['updateTime']);
            $arr[$key]['isDisable'] =$item['state'];
        }

        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $return['page'] = $_REQUEST['page'];
        $return['records'] = $total;
        $return['total'] = ceil($total/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $arr;
        echo json_encode($return);exit;
    }

    //新增供应商类别
    public function supplier_cate_add_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];

        $id = intval($_REQUEST['id']);
        if($id>0){
            $_data['id'] = $id;
            $_data['slid'] = $account_info['slid'];
            $_data['supplierCode']= $_REQUEST['supplierCateCode']?$_REQUEST['supplierCateCode']:time();
            $_data['supplierName'] = $_REQUEST['supplierCateName'];
            $_data['remark'] = $_REQUEST['memo'];
            $_data['updateTime'] = time();
            $res = $GLOBALS['db']->autoExecute("fanwe_gys_category", $_data ,"UPDATE","id=".$id);
        }else{
            $_data['slid'] = $account_info['slid'];
            $_data['supplierCode']= $_REQUEST['supplierCateCode']?$_REQUEST['supplierCateCode']:time();
            $_data['supplierName'] = $_REQUEST['supplierCateName'];
            $_data['remark'] = $_REQUEST['memo'];
            $_data['createTime'] = time();
            $_data['state'] = 0;
            $res = $GLOBALS['db']->autoExecute("fanwe_gys_category", $_data ,"INSERT");
        }


        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
            $return['data']['url'] = url("kiz","supplier#supplier_cate_index");
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    //新增供应商类别删除
    public function supplier_cate_del_ajax(){
        init_app_page();
        $id = $_REQUEST['id'];
        $res = $GLOBALS['db']->query('delete from fanwe_gys_category where id='.$id);

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
            $return['data']['url'] = url("kiz","supplier#supplier_cate_index");
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    //供应商类别状态修改
    public function supplier_cate_state_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = intval($_REQUEST['id']);
        if($id>0){
            $_data['id'] = $id;
            $_data['slid'] = $account_info['slid'];
            $_data['state']= $_REQUEST['isDisable'];
            $_data['updateTime'] = time();
            $res = $GLOBALS['db']->autoExecute("fanwe_gys_category", $_data ,"UPDATE","id=".$id);
        }

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
            $return['data']['url'] = url("kiz","supplier#supplier_cate_index");
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    /**
     * 采购明细表
     */
    public function report_purchase_detail_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:999999;
        $page = intval($_REQUEST['page']);
        $skuNameOrCode = $_REQUEST['skuName'];
        $danjuhao = $_REQUEST['orderNo'];
        $cid = $_REQUEST['wmIds'];
        $supplier = $_REQUEST['supplier'];
        $billDateStart = $_REQUEST['billDateStart'];
        $billDateEnd = $_REQUEST['billDateEnd'];
        $skuTypeIds = $_REQUEST['skuTypeIds'];
        $type = $_REQUEST['type'];
        $wmTypeStr = $_REQUEST['wmTypeStr'];
        $wareHouseStr = $_REQUEST['wareHouseStr'];
        $commercialStr = $_REQUEST['commercialStr'];
        $statusStr = $_REQUEST['statusStr'];
        $print = $_REQUEST['print'];


        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $dc_where = " WHERE 1=1";
        $where = " where a.slid=$slid and a.gys is not null";
        if($danjuhao){
            $where .= " and a.danjuhao like '%".$danjuhao."%'";
        }
        if($cid){
            $where .= " and c.id =".$cid;
        }
        if($billDateStart){
            $startTime = strtotime($billDateStart);
            $where .= " and a.ctime > $startTime";
        }
        if($billDateEnd){
            $endTime = strtotime($billDateEnd);
            $where .= " and a.ctime < $endTime";
        }

//        if($supplier){
//            $where .= " and a.gys like '%".$supplier."%'";
//        }
        if($type>-1&&$type < 3){
            $where .= " and a.type =".$type;
        }

        //dc_menu
        if($skuNameOrCode){
            $dc_where .= " and (g.name like '%".$skuNameOrCode."%' or g.barcode LIKE '%".$skuNameOrCode."%' or g.id LIKE '%".$skuNameOrCode."%' or g.pinyin LIKE '%".$skuNameOrCode."%')";
        }
        if($print>-1){
            $dc_where .= " and g.print = $print";
        }else{
            $dc_where .= " and g.print <> 1 ";
        }
        if($skuTypeIds>-1){
            $parentids = parent::get_dc_supplier_cate($skuTypeIds);
            $dc_where .= " and g.cate_id in ( $parentids )";
        }

//var_dump($where);die;
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$where." order by a.id desc limit ".$limit;
//        var_dump($sql);die;
        $sqlrecords="select count(0) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$where." order by a.id desc";
        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//var_dump($list);die;
        $arr = [];
        foreach ($list as $key=>$item) {
            $dd_detail = unserialize($item["dd_detail"]);

            foreach ($dd_detail as $key2=>$item2) {
                $detail = [];
                $tsql = "select * from fanwe_dc_menu g $dc_where and g.id=".$item2['mid'];
                $row = $GLOBALS['db']->getRow($tsql);
//var_dump($supplier);
                if($row){
                    $gys = parent::get_gonghuoren_name($supplier_id,$slid,$item['gys']);
                    if($supplier){
                        if(strpos($gys, $supplier) !==false){
                            $category =parent::get_dc_current_supplier_cate($row['cate_id']);
                            if($item['type']==1){
                                $type = '验收入库单';
                            }elseif($item['type']==2){
                                $type = '验收退货单';
                            }else{
                                $type = '其他';
                            }
                            $detail['id'] =$item['id'];
                            $detail['orderNo'] =$item['danjuhao'];
                            $detail['typeStr'] = $type;
                            $detail['billDate'] = date('Y-m-d H:i:s',$item['ctime']);
                            $detail['supplierCode'] =$item['gys'];
                            $detail['supplierName'] = $gys;
                            $detail['wmTypeStr'] = parent::getCollectionValue($this->kcnx,$item2['ywsort']);
                            $detail['cname'] = $item['cname'];
                            $detail['skuTypeName'] = $category['name'];
                            $detail['skuCode'] = $row['id'];
                            $detail['skuName'] = $row['name'];
                            $detail['skuTypeIsDisable'] =0;
                            $detail['skuIsDelete'] =0;
                            $detail['uom'] =$item2['unit'];
                            $detail['price'] =$item2['price'];
                            $detail['taxRate'] =0;
                            $detail['qty'] =$item2['num'];
                            $detail['amount'] = floatval($item2['price'])*floatval($item2['num']);
                            array_push($arr,$detail);
                        }
                    }else{
                        $category =parent::get_dc_current_supplier_cate($row['cate_id']);
                        if($item['type']==1){
                            $type = '验收入库单';
                        }elseif($item['type']==2){
                            $type = '验收退货单';
                        }else{
                            $type = '其他';
                        }
                        $detail['id'] =$item['id'];
                        $detail['orderNo'] =$item['danjuhao'];
                        $detail['typeStr'] = $type;
                        $detail['billDate'] = date('Y-m-d H:i:s',$item['ctime']);
                        $detail['supplierCode'] =$item['gys'];
                        $detail['supplierName'] = $gys;
                        $detail['wmTypeStr'] = parent::getCollectionValue($this->kcnx,$item2['ywsort']);
                        $detail['cname'] = $item['cname'];
                        $detail['skuTypeName'] = $category['name'];
                        $detail['skuCode'] = $row['id'];
                        $detail['skuName'] = $row['name'];
                        $detail['skuTypeIsDisable'] =0;
                        $detail['skuIsDelete'] =0;
                        $detail['uom'] =$item2['unit'];
                        $detail['price'] =$item2['price'];
                        $detail['taxRate'] =0;
                        $detail['qty'] =$item2['num'];
                        $detail['amount'] = floatval($item2['price'])*floatval($item2['num']);

                        array_push($arr,$detail);
                    }
                }


            }
        }
//        $return['page'] = $page;
//        $return['records'] = $records;
//        $return['total'] = ceil($records/$page_size);
//        $return['status'] = true;
//        $return['resMsg'] = null;
//        $return['dataList'] = $arr;

        echo json_encode($arr);exit;
    }

    //库存分布表
    public function report_purchase_analysis_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:999999;
        $page = intval($_REQUEST['page']);
        $skuNameOrCode = $_REQUEST['skuName'];
        $danjuhao = $_REQUEST['orderNo'];
        $cid = $_REQUEST['wmIds'];
        $supplier = $_REQUEST['supplier'];
        $billDateStart = $_REQUEST['billDateStart'];
        $billDateEnd = $_REQUEST['billDateEnd'];
        $skuTypeIds = $_REQUEST['skuTypeIds'];
        $type = $_REQUEST['type'];
        $wmTypeStr = $_REQUEST['wmTypeStr'];
        $wareHouseStr = $_REQUEST['wareHouseStr'];
        $commercialStr = $_REQUEST['commercialStr'];
        $statusStr = $_REQUEST['statusStr'];
        $print = $_REQUEST['print'];


        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $dc_where = " WHERE 1=1";
        $where = " where a.slid=$slid and a.gys is not null";
        if($danjuhao){
            $where .= " and a.danjuhao like '%".$danjuhao."%'";
        }
        if($cid){
            $where .= " and c.id =".$cid;
        }
        if($billDateStart){
            $startTime = strtotime($billDateStart);
            $where .= " and a.ctime > $startTime";
        }
        if($billDateEnd){
            $endTime = strtotime($billDateEnd);
            $where .= " and a.ctime < $endTime";
        }

//        if($supplier){
//            $where .= " and a.gys like '%".$supplier."%'";
//        }
        if($type>-1&&$type < 3){
            $where .= " and a.type =".$type;
        }

        //dc_menu
        if($skuNameOrCode){
            $dc_where .= " and (g.name like '%".$skuNameOrCode."%' or g.barcode LIKE '%".$skuNameOrCode."%' or g.id LIKE '%".$skuNameOrCode."%' or g.pinyin LIKE '%".$skuNameOrCode."%')";
        }
        if($print>-1){
            $dc_where .= " and g.print = $print";
        }else{
            $dc_where .= " and g.print <> 1 ";
        }
        if($skuTypeIds>-1){
            $parentids = parent::get_dc_supplier_cate($skuTypeIds);
            $dc_where .= " and g.cate_id in ( $parentids )";
        }

//var_dump($where);die;
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$where." order by a.id desc limit ".$limit;
//        var_dump($sql);die;
        $sqlrecords="select count(0) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$where." order by a.id desc";
        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//var_dump($list);die;
        $arr = [];
        foreach ($list as $key=>$item) {
            $dd_detail = unserialize($item["dd_detail"]);
            array_push($arr,$dd_detail);
        }

//        $return['page'] = $page;
//        $return['records'] = $records;
//        $return['total'] = ceil($records/$page_size);
//        $return['status'] = true;
//        $return['resMsg'] = null;
//        $return['dataList'] = $arr;

        echo json_encode($arr);exit;

    }

    //盘点模板列表
    public function count_stock_ajax(){
        init_app_page();
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];

        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $moban_keywords = $_REQUEST['name'];
        $menu_keywords = $_REQUEST['skuName'];
        $accept_location = $_REQUEST['shopIds'];
        $isDisable = trim($_REQUEST['isDisable']);
        !isset($isdd) && $isdd = 1;

        $str="where supplier_id=$supplier_id ";

        if($moban_keywords){
            $str .= " and (name like '%$moban_keywords%' or code like '%$moban_keywords%')";
        }
        if($menu_keywords){
            $str .= " and (accept_goods like '%$menu_keywords%')";
        }
        if($accept_location){
            $str .= " and (accept_location like '%$accept_location%')";
        }

        if($isDisable == '0'||$isDisable == '1'){
            $str .= " and (isdisable = ".$isDisable.")";
        }


        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_pandian_mb  $str order by id desc limit ".$limit);
        $recordslist = $GLOBALS['db']->getAll("SELECT count(*) as count FROM " . DB_PREFIX . "cangku_pandian_mb  $str order by id desc ");
        $records = count($recordslist);

        $data = [];
        foreach ($list as $key=>$item) {
            $data[$key]['id'] = $item['id'];
            $data[$key]['code'] = $item['code'];
            $data[$key]['name'] = $item['name'];
            $data[$key]['updaterName'] = $item['edit_user'];
            $data[$key]['updateTime'] = $item['datetime'];
            $data[$key]['isDisable'] = $item['isdisable'];
            $data[$key]['status'] = $item['isdisable'];
        }

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $data;

        /* 数据 */
        echo json_encode($return);exit;
    }

    //删除盘点模板
    public function count_stock_del_ajax(){
        init_app_page();
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];

        $res = $GLOBALS['db']->query("delete from fanwe_cangku_pandian_mb where id=".$id);
//var_dump("delete from fanwe_cangku_pandian_mb where id=".$id);
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($res){//成功
            $return['success'] = true;
            $return['message'] = '删除成功';
        }else{

            $return['success'] = false;
            $return['message'] = '删除失败';
        }

        /* 数据 */
        echo json_encode($return);exit;
    }

    //获取商户选择
    public function getTempletCommercialJqGridData(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $commercialIdOrName = $_REQUEST['commercialIdOrName'];

        $where = " where supplier_id=".$supplier_id;
        if($commercialIdOrName){
            $where .= " and ( name like '%$commercialIdOrName%' or id like '%$commercialIdOrName%')";
        }


        $slidlist=$GLOBALS['db']->getAll("select supplier_id,id,name,address from fanwe_supplier_location $where");

//        var_dump($slidlist);
        $data = [];
        foreach ($slidlist as $key=>$item) {
            $data[$key]['brandId'] = $item['supplier_id'];
            $data[$key]['commercialId'] = $item['id'];
            $data[$key]['commercialName'] = $item['name'];
            $data[$key]['commercialAddress'] = $item['address'];
        }

        $return['page'] = 1;
        $return['records'] = count($slidlist);
        $return['total'] = ceil( count($slidlist)/1);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $data;
        /* 数据 */
        echo json_encode($return);exit;
    }

    //新增盘点模板
    public function count_stock_saving_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        //模板模板ID
        $mbid=$_REQUEST['mbid']?intval($_REQUEST['mbid']):0;
        //数组
        $data_moban=array(
            "edit_user"=>$account_info['account_name'],
            "supplier_id"=>$supplier_id,
            "slid"=>$slid,
            "code"=>empty($_REQUEST['code'])?time():$_REQUEST['code'],
            "name"=>$_REQUEST['name'],
            "accept_location"=>serialize($_REQUEST['accept_location']),
            "memo"=>$_REQUEST['memo'],
            "datetime"=>to_date(NOW_TIME,'Y-m-d H:i:s'),
            "isdisable"=>1
        );

        //存在ID，则更新，否则插入，取到ID
        if($mbid){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_mb",$data_moban,"UPDATE","id='$mbid'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_mb",$data_moban);
            $mbid= $GLOBALS['db']->insert_id();
        }

        //插入配方，取到配方ID

        $mid=$_REQUEST['templateDetails'];
        // var_dump($mid);
        //构造数组，填入统计表 由于这个是临时用，没有JS特效，这块帮的比较麻烦，如果使用AJAX的话会相对简单，组成以下的数组就行了
        $data_stat=array();
        foreach ($mid as $key=>$item) {
            $data_stat[$key]['id']=$item['id'];
            $data_stat[$key]['mid']=$item['mid'];
            $data_stat[$key]['cate_id']=$item['skuTypeId'];
            $data_stat[$key]['cname']=$item['skuTypeName'];
            $data_stat[$key]['name']=$item['skuName'];
            $data_stat[$key]['unit']=$item['uom'];
            $data_stat[$key]['price']=$item['price'];
            $exceptShopStr = $item['exceptShopStr'];
            $regStr = parent::get_tag_data($exceptShopStr);
            $regStr = str_replace("\"","",$regStr);
            if($regStr){
                $feiS = explode(",",$regStr[0]);
                $fei_slid=$feiS;
            }else{
                $fei_slid='';
            }
            $data_stat[$key]['fei_slid']=json_encode($fei_slid);
        }

        //更新配方里的data_json字段
        $data_json=array('accept_goods'=>serialize($data_stat));
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_mb",$data_json,"UPDATE","id='$mbid'");

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($res){//成功
            $return['success'] = true;
            $return['message'] = '保存成功';
        }else{

            $return['success'] = false;
            $return['message'] = '保存失败';
        }

        echo json_encode($return);exit;
    }

    //新增盘点模板(检查)
    public function count_stock_checkName(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $name = $_REQUEST['name'];

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($name){
            $sql = "select * from fanwe_cangku_pandian_mb where name='".$name."'";
            $res = $GLOBALS['db']->getRow($sql);
            if($res){//成功
                $return['success'] = true;
                $return['message'] = '';
            }else{

                $return['success'] = false;
                $return['message'] = '';
            }
        }

        echo json_encode($return);exit;
    }

    //盘点单据列表
    public function count_task_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        //$slid = $account_info['slid'];
        $slid = $account_info['slid'];
        //仓库列表
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        $GLOBALS['tmpl']->assign("cangkulist", $cangkulist);
        //改成一维数据
        $cangku_name=array_column($cangkulist,'name','id');
        //模板列表
        $mobanlist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_pandian_mb where slid=".$slid);
        //改成一维数据
        $moban_name=array_column($mobanlist,'name','id');

        $danjuhao = $_REQUEST['ccTaskNo'];
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $cangku_id = $_REQUEST['warehouseId'];
        $moban_id = $_REQUEST['taskTemplateIds'];
        $isdisable = $_REQUEST['status'];
        if (($_REQUEST['createDateStart'])|| ($_REQUEST['createDateEnd'])){
            $begin_time = strim($_REQUEST['createDateStart']);
            $end_time = strim($_REQUEST['createDateEnd']);
        }

        $str="where slid=".$slid;
        if($isdisable > -1){
            $str .=  " and isdisable=".$isdisable;
        }
        if($danjuhao){
            $str .= " and (danjuhao='$danjuhao')";
        }
        if($cangku_id > -1){
            $str .= " and (cangku_id =$cangku_id)";
        }
        if($moban_id > 0){
            $str .= " and (moban_id in ($moban_id))";
        }
        if($begin_time){
            $str .=" and datetime > ".$begin_time." ";
        }
        if($end_time){
            $str .=" and datetime < ".$end_time." ";
        }
//        var_dump($str);
        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_pandian_danju  $str order by id desc limit ".$limit);
        $list2 = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_pandian_danju  $str order by id desc ");
        $records = count($list2);
        $data = [];
//        var_dump($list);
        foreach ($list as $k=>$v){
            $data[$k]['id']=$v['id'];
            $data[$k]['ccTaskNo']=$v['danjuhao'];
            $data[$k]['warehouseName']=$cangku_name[$v['cangku_id']];
            $data[$k]['templateName']=$moban_name[$v['moban_id']];
            $data[$k]['profitAmount']=$v['panying'];
            $data[$k]['lossAmount']=$v['pankui'];
            $data[$k]['updaterName']=$v['edit_user'];
            $data[$k]['updateTime']=$v['datetime'];
            if($v['isdisable'] == 1){
                $data[$k]['statusName']='已保存';
            }else if($v['isdisable'] == 2){
                $data[$k]['statusName']='已确认';
            }else{
                $data[$k]['statusName'] = '';
            }
            $data[$k]['status']=$v['isdisable'];
            $data[$k]['showNote']='';
        }

        $return['page'] = 1;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $data;
        /* 数据 */
        echo json_encode($return);exit;
    }

    //盘点单检查是否锁定
    public function count_task_isLocked()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $warehouseId = $_REQUEST['warehouseId'];
        $where = " where supplier_id=".$supplier_id." and isdisable=1";

        if($warehouseId > -1){
            $where .=" and cangku_id=$warehouseId";
        }

        $supplierSql = "select * from fanwe_cangku_pandian_danju where cangku_id=$warehouseId";
        $list=$GLOBALS['db']->getAll($supplierSql);

        $sql = "select *,g.id,g.name as skuName,g.barcode as skuCode,g.unit as uom,g.funit,g.times,g.price,g.pinyin,g.cate_id as skuTypeId,c.name as skuTypeName,g.stock as inventoryQty from fanwe_dc_menu g left join fanwe_cangku_menu fcm on fcm.mid=g.id LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id where fcm.cid=$warehouseId ";
        $check=$GLOBALS['db']->getAll($sql);

        $return = false;
        if(count($check)<0){
            $return = true;
        }else{
            $return = false;
        }
        echo json_encode($return);exit;
    }

    //根据模板id获得盘点单信息
    public function count_task_info()
    {
        init_app_page();

        $where = " where 1=1 ";
        $templateId = $_REQUEST['templateId'];
        if($templateId){
            $where .=" and id=$templateId";
        }
        $row = $GLOBALS['db']->getRow("select * from fanwe_cangku_pandian_mb $where");


        $inventoryAmount = 0;
        $ccAmount = 0;
        $profitAmount = 0;
        $lossAmount = 0;
        $dd_detail = [];
        foreach (unserialize($row['accept_goods']) as $key=>$item) {
            $value = $GLOBALS['db']->getRow("select * from fanwe_cangku_menu where mid=".$item['id']);
//var_dump($item);
            $typeName = parent::get_dc_current_supplier_cate($item['cate_id']);
            if (!empty($typeName)){
                $dd_detail[$key]['skuTypeName'] = $typeName['name'];
            }else{
                $dd_detail[$key]['skuTypeName'] = '<span style="color:red">顶级分类</span>';
            }
            $dd_detail[$key]['skuId'] = $value['mid'];
            $dd_detail[$key]['skuTypeId'] = $item['cate_id'];
            $dd_detail[$key]['skuCode'] = $value['mbarcode'];
            $dd_detail[$key]['skuName'] = $value['mname'];
            $dd_detail[$key]['uom'] = $value['unit'];
            $dd_detail[$key]['price'] = $item['price'];
            $dd_detail[$key]['inventoryQty'] = $value['mstock'];
            $dd_detail[$key]['realTimeInventory'] = $value['mstock'];
            $dd_detail[$key]['ccQty'] = $value['mstock'];
            $dd_detail[$key]['qtyDiff'] = 0;
            $dd_detail[$key]['amountDiff'] = 0;
            $dd_detail[$key]['remarks'] = '';
            $dd_detail[$key]['ccAmount'] = $value['mstock']*$item['price'];
            $dd_detail[$key]['relTimeAmount'] = $value['mstock']*$item['price'];
            $dd_detail[$key]['alreadyData'] = 1;
            $dd_detail[$key]['remarks'] ='';
            $dd_detail[$key]['djid'] = $_REQUEST['id'];
            $inventoryAmount +=  $dd_detail[$key]['inventoryQty'];
            $ccAmount +=  $dd_detail[$key]['ccAmount'];
        }

//        $return['flag'] = null;
//        $return['exception'] = null;
//        $return['refresh'] = false;
//        $return['success'] = true;
//        $return['message'] = '';
//        $return['result'] = $dd_detail;
        $return['inventoryAmount'] = $inventoryAmount;
        $return['ccAmount'] = $ccAmount;
        if($ccAmount>0){
            $return['profitAmount'] = $ccAmount;
            $return['lossAmount'] = 0;
        }else{
            $return['profitAmount'] = 0;
            $return['lossAmount'] = $ccAmount;
        }



        $return['details'] = $dd_detail;
        echo json_encode($return);exit;
    }

    //保存盘点单
    public function count_task_saving_ajax()
    {
        init_app_page();
        $warehouseId = $_REQUEST['warehouseId'];
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $warehouseId = $_REQUEST['warehouseId'];

        //判断仓库下是否有商品
        $csql = "select * from fanwe_cangku_menu fcm inner join fanwe_dc_menu fdm on fdm.id=fcm.mid  where cid=$warehouseId";
        $clist = $GLOBALS['db']->getAll($csql);
        if(count($clist) == 0){
            $return['flag'] = null;
            $return['exception'] = null;
            $return['refresh'] = false;
            $return['success'] = false;
            $return['message'] = "该仓库下无商品库存，不能进行盘点操作！";
        }
//        $csql2 = "select * from fanwe_cangku_pandian_stat";
//        $clist2 = $GLOBALS['db']->getAll($csql2);


        //查询仓库下的商品，封装商品
        $inventoryAmount = 0;
        $ccAmount = 0;
        $profitAmount = 0;
        $lossAmount = 0;
        $dd_detail = [];
        foreach ($clist as $key=>$item) {
            $typeName = parent::get_dc_current_supplier_cate($item['cate_id']);
            if (!empty($typeName)){
                $dd_detail[$key]['skuTypeName'] = $typeName['name'];
            }else{
                $dd_detail[$key]['skuTypeName'] = '<span style="color:red">顶级分类</span>';
            }
            $dd_detail[$key]['skuId'] = $item['id'];
            $dd_detail[$key]['skuTypeId'] = $item['cate_id'];
            $dd_detail[$key]['skuCode'] = $item['id'];
            $dd_detail[$key]['skuName'] = $item['mname'];
            $dd_detail[$key]['uom'] = $item['unit'];
            $dd_detail[$key]['price'] = $item['mprice'];
            $dd_detail[$key]['inventoryQty'] = $item['stock'];
            $dd_detail[$key]['realTimeInventory'] = $item['stock'];
            $dd_detail[$key]['ccQty'] = $item['stock'];
            $dd_detail[$key]['qtyDiff'] = 0;
            $dd_detail[$key]['amountDiff'] = 0;
            $dd_detail[$key]['remarks'] = '';
            $dd_detail[$key]['ccAmount'] = $item['stock']*$item['mprice'];
            $dd_detail[$key]['relTimeAmount'] = $item['stock']*$item['mprice'];
            $dd_detail[$key]['alreadyData'] = 1;
            $dd_detail[$key]['remarks'] ='';
            $dd_detail[$key]['djid'] = $item['id'];
            $inventoryAmount +=  $dd_detail[$key]['inventoryQty'];
            $ccAmount +=  $dd_detail[$key]['ccAmount'];
        }

        //新增盘点单据
        //如果ID不存在，则自动增加商品进入产品库，返回ID
        $count_data=array(
            "danjuhao"=>time(),
            "cangku_id"=>$warehouseId,
            "datetime"=> date('Y-m-d H:i:s'),
            "isdisable"=>1,
            "supplier_id"=>$supplier_id,
            "slid"=>$slid
        );

        $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_danju", $count_data ,"INSERT");
        $djid = $GLOBALS['db']->insert_id();
//var_dump($clist);die;

        //封装单据详情
        //查询仓库下的商品，封装商品
        $data_stat=array();
        $tongji_data=array();
        foreach ($clist as $key=>$item) {
            if($item['print'] == 4){
                $item['price'] = $item['buyPrice'];
            }else if($item['print'] == 3){
                $item['price'] = $item['sellPrice2'];
            }

            $data_stat[$key]['djid']=$djid;
            $data_stat[$key]['slid']=$slid;
            $data_stat[$key]['mid']=$item['id'];
            $data_stat[$key]['cate_id']=$item['cate_id'];
            $data_stat[$key]['cid']=$item['cid'];
            $data_stat[$key]['mbarcode']=$item['mbarcode'];
            $data_stat[$key]['mname']=$item['mname'];
            $data_stat[$key]['stock']=$item['stock'];
            $data_stat[$key]['mstock']=$item['mstock'];
            $data_stat[$key]['mprice']=$item['price'];
            $data_stat[$key]['unit']=$item['unit'];
            $data_stat[$key]['funit']=$item['funit'];
            $data_stat[$key]['times']=$item['times'];
            $data_stat[$key]['pandianshu']=$item['mstock'];
            $data_stat[$key]['chayishu']=0;
            $data_stat[$key]['chanyijine']=0;
            $data_stat[$key]['memo']='';
            $data_stat[$key]['ctime']=to_date(NOW_TIME,'Y-m-d H:i:s');

            //这块由于JS能力较差，不方便计算。这个可以在页面上通过通过JS计算好后直接上传也可以
            if(intval($_REQUEST['chayishu'][$item])>0){  //盘盈
                $tongji_data['panying']+=0;
            }
            if(intval($_REQUEST['chayishu'][$item])<0){  //盘亏
                $tongji_data['pankui']+=0;
            }

        }

        //插入Stat数据
        foreach ($data_stat as $value){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_stat",$value);
        }

        $list2 = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_pandian_danju  where id=".$djid);
        $data = [];
        //仓库列表
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        //改成一维数据
        $cangku_name=array_column($cangkulist,'name','id');
        //模板列表
        $mobanlist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_pandian_mb where slid=".$slid);
        //改成一维数据
        $moban_name=array_column($mobanlist,'name','id');
        foreach ($list2 as $k=>$v){
            $data['id']=$v['id'];
            $data['ccTaskNo']=$v['danjuhao'];
            $data['warehouseName']=$cangku_name[$v['cangku_id']];
            $data['templateName']=$moban_name[$v['moban_id']];
            $data['profitAmount']=$v['panying'];
            $data['lossAmount']=$v['pankui'];
            $data['inventoryAmount']=$inventoryAmount;
            $data['ccAmount']=$ccAmount;
            $data['updaterName']=$v['edit_user'];
            $data['updateTime']=$v['datetime'];
            if($v['isdisable'] == 1){
                $data['statusName']='已保存';
            }else if($v['isdisable'] == 2){
                $data['statusName']='已确认';
            }else{
                $data['statusName'] = '';
            }

            $data['status']=$v['isdisable'];
            $data['showNote']='';
        }

        $data['details'] = $dd_detail;

        echo json_encode($data);exit;
    }

    //更新盘点单
    public function count_task_edit_saving_ajax()
    {
        init_app_page();
        $warehouseId = $_REQUEST['warehouseId'];
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $warehouseId = $_REQUEST['warehouseId'];
        $djid = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $templateId = $_REQUEST['templateId'];
        $memo = $_REQUEST['remarks'];
//        var_dump($_REQUEST);die;

        //判断仓库下是否有商品
        $clist =$_REQUEST['details'];
        if(count($clist) == 0) {
            $return['flag'] = null;
            $return['exception'] = null;
            $return['refresh'] = false;
            $return['success'] = false;
            $return['message'] = "该模板下无商品库存，不能进行盘点操作！";
        }

        if($type == 2){
            $count_data=array(
                "datetime"=> date('Y-m-d H:i:s'),
                "isdisable"=>2,
                "edit_user"=>$account_info['account_name'],
                "moban_id"=>$templateId,
                "memo"=>$memo
            );
        }else{
            $count_data=array(
                "datetime"=> date('Y-m-d H:i:s'),
                "isdisable"=>1,
                "edit_user"=>$account_info['account_name'],
                "moban_id"=>$templateId,
                "memo"=>$memo
            );
        }
        $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_danju", $count_data ,"update","id=".$djid);

        $dropSql = "delete from fanwe_cangku_pandian_stat where djid=".$djid;
        $r = $GLOBALS['db']->query($dropSql);//删除原来盘点单的详情
//        var_dump($r);
        //封装单据详情
        //查询仓库下的商品，封装商品
        $data_stat=array();
        $tongji_data=array();
        $details = $_REQUEST['details'];
        foreach ($clist as $key=>$item) {
            //查询商品信息
            $ccsql = "select * from fanwe_dc_menu where id=" . $details[$key]['skuId'];
            $item = $GLOBALS['db']->getRow($ccsql);
//            var_dump($details);die;
//            var_dump($item);die;
            $data_stat[$key]['djid'] = $djid;
            $data_stat[$key]['slid'] = $slid;
            $data_stat[$key]['mid'] = $item['id'];
            $data_stat[$key]['cate_id'] = $details[$key]['skuTypeId'];
            $data_stat[$key]['cid'] = $warehouseId;
            $data_stat[$key]['mbarcode'] = $item['barcode'];
            $data_stat[$key]['mname'] = $item['name'];
            $data_stat[$key]['stock'] = $details[$key]['inventoryQty'];
            $data_stat[$key]['mstock'] = $details[$key]['realTimeInventory'];
            $data_stat[$key]['mprice'] = $details[$key]['price'];
            $data_stat[$key]['unit'] = $details[$key]['uom'];
            $data_stat[$key]['funit'] = $item['funit'];
            $data_stat[$key]['times'] = $item['times'];
            $data_stat[$key]['pandianshu'] = $details[$key]['ccQty'];
            $data_stat[$key]['chayishu'] = $details[$key]['qtyDiff'];
            $data_stat[$key]['chanyijine'] = $details[$key]['amountDiff'];
            $data_stat[$key]['memo'] = $details[$key]['remarks'];
            $data_stat[$key]['ctime'] = to_date(NOW_TIME, 'Y-m-d H:i:s');

            //这块由于JS能力较差，不方便计算。这个可以在页面上通过通过JS计算好后直接上传也可以
            if (intval($data_stat[$key]['chayishu']) > 0) {  //盘盈
                $tongji_data['panying'] += $details[$key]['amountDiff'];
            }
            if (intval($data_stat[$key]['chayishu']) < 0) {  //盘亏
                $tongji_data['pankui'] += $details[$key]['amountDiff'];
            }

            //更新库存数量
//            $cangku_data = array(
//                'mstock'=>$data_stat[$key]['pandianshu'],
//                'cid'=>$data_stat[$key]['cate_id'],
//            );
//            $r = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $cangku_data ,"update","id=".$data_stat[$key]['mid']);
//            $pandianshu = $data_stat[$key]['pandianshu'];


//        var_dump($data_stat);
//        var_dump($tongji_data);
            $GLOBALS['db']->autoExecute(DB_PREFIX . "cangku_pandian_danju", $tongji_data, "update", "id=" . $djid);
//        var_dump($GLOBALS['db']->getAll("select * from fanwe_cangku_pandian_danju where id=$djid"));die;
//var_dump($data_stat);die;

        }

        //插入Stat数据
        foreach ($data_stat as $value){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_stat",$value);
        }

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        $return['success'] = true;
        $return['message'] = "保存成功";

        echo json_encode($return);exit;
    }

    //确认单据
    public function count_task_doconfirm()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $where = " where 1=1 ";
        $id = $_REQUEST['id'];
        if($id){
            $where .=" and id=$id";
        }
        $row = $GLOBALS['db']->getRow("select * from fanwe_cangku_pandian_danju $where");
        if($row){
            $data['isdisable'] = 2;
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_danju", $data ,"UPDATE","id=".$id);
            $data_stat = $GLOBALS['db']->getAll("select * from fanwe_cangku_pandian_stat where djid=".$row['id']);
            foreach ($data_stat as $key=>$item) {
                $warehouseId = $data_stat[$key]['cid'];

                $list = $GLOBALS['db']->getAll('select * from fanwe_cangku_menu where mid='.$data_stat[$key]['mid'].' and cid='.$warehouseId);
//            var_dump($list);
                if(count($list)>0&&$warehouseId&&$data_stat[$key]['mid']){
//                    $sql = "update fanwe_cangku_menu set mstock=".intval($data_stat[$key]['pandianshu'])." where mid=".$data_stat[$key]['mid']." and cid=".$warehouseId;
//                    var_dump($sql);die;
                    $sql = "update fanwe_cangku_menu set mstock=mstock+".intval($data_stat[$key]['chayishu'])." where mid=".$data_stat[$key]['mid']." and cid=".$warehouseId;
                    $r = $GLOBALS['db']->query($sql);
                }else{
                    //添加
                    $data_menu=array(
                        "slid"=>$slid,
                        "mid"=>$data_stat[$key]['mid'],
                        "cid"=>$data_stat[$key]['cid'],
                        "cate_id"=>$data_stat[$key]['cate_id'],
                        "mbarcode"=>$data_stat[$key]['mbarcode'],
                        "mname"=>$data_stat[$key]['mname'],
                        "mstock"=>$data_stat[$key]['pandianshu'],
                        "stock"=>$data_stat[$key]['pandianshu'],
                        "minStock"=>$data_stat[$key]['minStock'],
                        "maxStock"=>$data_stat[$key]['maxStock'],
                        "unit"=>$data_stat[$key]['unit'],
                        "funit"=>$data_stat[$key]['funit'],
                        "times"=>$data_stat[$key]['times'],
                        "type"=>$data_stat[$key]['type'],
                        "ctime"=>date('Y-m-d H:i:s',time())
                    );
                    $r = $GLOBALS['db']->autoExecute("fanwe_cangku_menu", $data_menu ,"INSERT");
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+".$data_stat[$key]['pandianshu']." where id=".$data_stat[$key]['mid']);
                }
            }
        }

//            //读取盘点单据详情页面数据
//            $danju_stat=$GLOBALS['db']->getAll("select * from fanwe_cangku_pandian_stat where djid=".$id);
//            foreach($danju_stat as $k=>$v){
//                //取得确认时间的实时库存, 更新到stat表，以备反确认的时候用
//                $sqlstr="slid='$slid' and mid=".$v['mid']." and cid=".$v['cid'];
//                $corrent_stock=$GLOBALS['db']->getOne("select mstock from fanwe_cangku_menu where ".$sqlstr);
//                $corrent_stock_data=array(
//                    "mstock"=>$corrent_stock
//                );
//                $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_stat",$corrent_stock_data,"UPDATE","id=".$v['id']);
//                $change_stock=$v['stock']-$corrent_stock; //创建到确认之前库存的变化
//
//                //更新cangku_menu 仓库ID、菜单ID、门店ID相同的菜品数量
//                //由于这块在提交保存的时候已经确定了肯定是仓库存在的商品，所以这块不在验证cangku_menu中是否存在
//                $data_menu=array(
//                    'mstock'=>round($v['pandianshu']-$change_stock,2),//减去从仓库到确认这段时间内库存的变化
//                    'ctime'=>to_date(NOW_TIME,'Y-m-d H:i:s')
//                );
//
//
//                //更新cangku_menu
//                $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu",$data_menu,"UPDATE",$sqlstr);
//                //更新ｄｃ＿ｍｅｎｕ
//                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+".$v['chayishu']." where id=".$v['mid']);
//
//            }
//        }
        if($r){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    //反确认单据
    public function count_task_udoconfirm()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $where = " where 1=1 ";
        $id = $_REQUEST['id'];
        if($id){
            $where .=" and id=$id";
        }
        $row = $GLOBALS['db']->getRow("select * from fanwe_cangku_pandian_danju $where");
        if($row){
            $data['isdisable'] = 1;
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_danju", $data ,"UPDATE","id=".$id);
            $data_stat = $GLOBALS['db']->getAll("select * from fanwe_cangku_pandian_stat where djid=".$row['id']);
//var_dump($data_stat);die;
            foreach ($data_stat as $key=>$item) {
                $warehouseId = $data_stat[$key]['cid'];

                $list = $GLOBALS['db']->getAll('select * from fanwe_cangku_menu where mid='.$data_stat[$key]['mid'].' and cid='.$warehouseId);
//            var_dump($list);
                if(count($list)>0&&$warehouseId&&$data_stat[$key]['mid']){
//                    $sql = "update fanwe_cangku_menu set mstock=".intval($data_stat[$key]['pandianshu'])." where mid=".$data_stat[$key]['mid']." and cid=".$warehouseId;
//                    var_dump($sql);die;
                    $sql = "update fanwe_cangku_menu set mstock=mstock-".intval($data_stat[$key]['chayishu'])." where mid=".$data_stat[$key]['mid']." and cid=".$warehouseId;
                    $r = $GLOBALS['db']->query($sql);
                }else{
                    //添加
                    $data_menu=array(
                        "slid"=>$slid,
                        "mid"=>$data_stat[$key]['mid'],
                        "cid"=>$data_stat[$key]['cid'],
                        "cate_id"=>$data_stat[$key]['cate_id'],
                        "mbarcode"=>$data_stat[$key]['mbarcode'],
                        "mname"=>$data_stat[$key]['mname'],
                        "mstock"=>$data_stat[$key]['pandianshu'],
                        "stock"=>$data_stat[$key]['pandianshu'],
                        "minStock"=>$data_stat[$key]['minStock'],
                        "maxStock"=>$data_stat[$key]['maxStock'],
                        "unit"=>$data_stat[$key]['unit'],
                        "funit"=>$data_stat[$key]['funit'],
                        "times"=>$data_stat[$key]['times'],
                        "type"=>$data_stat[$key]['type'],
                        "ctime"=>date('Y-m-d H:i:s',time())
                    );
                    $r = $GLOBALS['db']->autoExecute("fanwe_cangku_menu", $data_menu ,"INSERT");
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+".$data_stat[$key]['pandianshu']." where id=".$data_stat[$key]['mid']);
                }
            }
        }

//            //读取盘点单据详情页面数据
//            $danju_stat=$GLOBALS['db']->getAll("select * from fanwe_cangku_pandian_stat where djid=".$id);
//            foreach($danju_stat as $k=>$v){
//                //取得确认时间的实时库存, 更新到stat表，以备反确认的时候用
//                $sqlstr="slid='$slid' and mid=".$v['mid']." and cid=".$v['cid'];
//                $corrent_stock=$GLOBALS['db']->getOne("select mstock from fanwe_cangku_menu where ".$sqlstr);
//                $corrent_stock_data=array(
//                    "mstock"=>$corrent_stock
//                );
//                $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_pandian_stat",$corrent_stock_data,"UPDATE","id=".$v['id']);
//                $change_stock=$v['stock']-$corrent_stock; //创建到确认之前库存的变化
//
//                //更新cangku_menu 仓库ID、菜单ID、门店ID相同的菜品数量
//                //由于这块在提交保存的时候已经确定了肯定是仓库存在的商品，所以这块不在验证cangku_menu中是否存在
//                $data_menu=array(
//                    'mstock'=>round($v['pandianshu']-$change_stock,2),//减去从仓库到确认这段时间内库存的变化
//                    'ctime'=>to_date(NOW_TIME,'Y-m-d H:i:s')
//                );
//
//
//                //更新cangku_menu
//                $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu",$data_menu,"UPDATE",$sqlstr);
//                //更新ｄｃ＿ｍｅｎｕ
//                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+".$v['chayishu']." where id=".$v['mid']);
//
//            }
//        }
        if($r){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    //供应商管理
    public function supplier_ajax(){
        init_app_page();

        //更新供应商字段
//        $GLOBALS['db']->query('alter table fanwe_cangku_gys add gys_code varchar(255)');
//        var_dump($GLOBALS['db']->getAll('select * from fanwe_cangku_gys'));
//        die;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $isdd = $_REQUEST['isDisable'];
        $kw = $_REQUEST['supplierName'];
        $supplierCateId = $_REQUEST['supplierCateId'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);

        $str = "";
        if($kw){
            $str = " and (name like '%$kw%' or gys_code like '%$kw%')";
        }

        if($supplierCateId){
            $str .= " and gys_cate_id=$supplierCateId";
        }
        if($isdd > -1){
            $str .= " and isdisable=$isdd";
        }
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $sql = "SELECT * FROM " . DB_PREFIX . "cangku_gys where slid=$slid $str order by id desc limit ".$limit;
        $sql2 = "SELECT * FROM " . DB_PREFIX . "cangku_gys where slid=$slid  $str order by id desc ";
        $list = $GLOBALS['db']->getAll($sql);
        $records = count($GLOBALS['db']->getAll($sql2));

//        $listcp= $GLOBALS['db']->getAll("SELECT name,gys_id FROM " . DB_PREFIX . "dc_menu where location_id=$slid");

//        print_r($listcp);

        $data=[];
        foreach ($list as $key=>$item) {
            $supplierCateName= parent::get_dc_current_gys_cate($item['gys_cate_id']);
            if(empty($supplierCateName)){
                $supplierCateName = '';
            }else{
                $supplierCateName = $supplierCateName['supplierName'];
            }
            $data[$key]['id']=$item['id'];
            $data[$key]['supplierCode']=$item['id'];
            $data[$key]['supplierName']=$item['name'];
            $data[$key]['taxRate']=$item['tax'];
            $data[$key]['supplierCateName']= $supplierCateName;
            $data[$key]['supplierCode']=$item['gys_code'];
            $data[$key]['isDisable']=$item['isdisable'];
            $data[$key]['updaterName']=$item['edit_user'];
            $data[$key]['updateTime']=date('Y-m-d',$item['edittime']);
        }

        $return['page'] = 1;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $data;
        /* 数据 */
        echo json_encode($return);exit;
    }

    //新增供应商
    public function supplier_add_ajax(){
        init_app_page();

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $data_menu = array(
            'gys_code'=>empty($_REQUEST['supplierCode'])?time():$_REQUEST['supplierCode'],
            'name'=>$_REQUEST['supplierName'],
            'gys_cate_id'=>$_REQUEST['supplierCateId'],
            'tax'=>$_REQUEST['taxRate'],
            'edittime'=>time(),
            'edit_user'=>$account_info['account_name'],
            'isdisable'=>$_REQUEST['isdisable'],
            'slid'=>$slid,
            'id'=>$_REQUEST['id']
        );
        if($_REQUEST['id'] > 0){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_gys", $data_menu ,"UPDATE","id=".$id);
        }else{
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_gys", $data_menu ,"INSERT");
        }
        if($res){
            $return['success'] = true;
            $return['message'] = "保存成功";
        }else{
            $return['success'] = false;
            $return['message'] = "保存失败";
        }

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        /* 数据 */
        echo json_encode($return);exit;
    }

    //部门管理
    public function bumen_ajax(){
        init_app_page();

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $isdd = $_REQUEST['isDisable'];
        $kw = $_REQUEST['bumenName'];
//        $supplierCateId = $_REQUEST['supplierCateId'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);

        $str = "";
        if($kw){
            $str = " and (name like '%$kw%')";
        }

//        if($supplierCateId){
//            $str .= " and gys_cate_id=$supplierCateId";
//        }

           if($isdd > -1){
                $str .= " and isdisable=$isdd";
           }
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_bumen where slid=$slid $str order by id desc limit ".$limit);
        $list2 = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_bumen where slid=$slid $str order by id desc");
        $records = count($list2);

//        $listcp= $GLOBALS['db']->getAll("SELECT name,gys_id FROM " . DB_PREFIX . "dc_menu where location_id=$slid");

//        print_r($listcp);

//        $data=[];
//        foreach ($list as $key=>$item) {
////            $supplierCateName= parent::get_dc_current_gys_cate($item['gys_cate_id']);
////            if(empty($supplierCateName)){
////                $supplierCateName = '';
////            }else{
////                $supplierCateName = $supplierCateName['supplierName'];
////            }
//            $data[$key]['id']=$item['id'];
//            $data[$key]['supplierCode']=$item['id'];
//            $data[$key]['supplierName']=$item['name'];
//            $data[$key]['taxRate']=$item['tax'];
////            $data[$key]['supplierCateName']= $supplierCateName;
//            $data[$key]['supplierCode']=$item['gys_code'];
//            $data[$key]['isDisable']=$item['isdisable'];
//            $data[$key]['updaterName']=$item['edit_user'];
//            $data[$key]['updateTime']=date('Y-m-d',$item['edittime']);
//        }

        $return['page'] = 1;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $list;
        /* 数据 */
        echo json_encode($return);exit;
    }


    //新增部门
    public function bumen_add_ajax(){
        init_app_page();

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $data_menu = array(
            'name'=>$_REQUEST['name'],
            'isdisable'=>$_REQUEST['isdisable'],
            'slid'=>$slid,
            'id'=>$id
        );
//        var_dump($data_menu);die;
        if($id > 0){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_bumen", $data_menu ,"UPDATE","id=".$id);
        }else{
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_bumen", $data_menu ,"INSERT");
        }
        if($res){
            $return['success'] = true;
            $return['message'] = "保存成功";
        }else{
            $return['success'] = false;
            $return['message'] = "保存失败";
        }

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        /* 数据 */
        echo json_encode($return);exit;
    }
    //删除部门
    public function bumen_del_ajax(){
        init_app_page();

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];

        $res=$GLOBALS['db']->query("delete from fanwe_cangku_bumen where id=$id");
        if($res){
            $return['success'] = true;
            $return['message'] = "保存成功";
        }else{
            $return['success'] = false;
            $return['message'] = "保存失败";
        }

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        /* 数据 */
        echo json_encode($return);exit;
    }

    //盘点盈亏表
    public function stock_diff_ajax(){
        init_app_page();
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $warehouseId = $_REQUEST['warehouseId'];
        $taskTemplateIds = $_REQUEST['taskTemplateIds'];

        if (($_REQUEST['confirmDateStart'])|| ($_REQUEST['confirmDateEnd'])){
            $begin_time = strim($_REQUEST['confirmDateStart']);
            $end_time = strim($_REQUEST['confirmDateEnd']);
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);

        $where = "where fcps.slid=$slid";
        if($warehouseId){
            $where .= " and fcps.cid=$warehouseId";
        }
        if($taskTemplateIds){
            $where .= " and fcps.moban_id=$taskTemplateIds";
        }
        if($begin_time_s){
            $where .=" and fcps.ctime > ".$begin_time_s." ";
        }
        if($end_time_s){
            $where .=" and fcps.ctime < ".$end_time_s." ";
        }
        //查询所有单据的商品
        $sql = "select *,sum(fcps.chanyijine) as schanyijine,sum(fcps.pandianshu) as spandianshu,sum(fcps.chayishu) as schayishu from fanwe_cangku_pandian_stat fcps $where GROUP by fcps.mid";
        $list = $GLOBALS['db']->getAll($sql);
        //

//var_dump($list);


        $data = [];
        foreach ($list as $key=>$item) {
            $qtyOverage = 0;
            $amountOverage = 0;
            $qtyLoss = 0;
            $amountLoss = 0;
            if($item['spandianshu'] - $item['schayishu'] > 0&&$item['spandianshu']>$item['mstock']){
                $qtyOverage = $item['spandianshu'] - $item['schayishu'];
                $amountOverage= ($item['spandianshu'] - $item['schayishu'])*$item['price'];
            }else{
                $qtyLoss = $item['schayishu'] - $item['spandianshu'];
                $amountLoss= ($item['spandianshu'] - $item['schayishu'])*$item['price'];
            }

            $data[$key]['id'] = $item['id'];
            $data[$key]['djid'] = $item['djid'];
            $data[$key]['slid'] = $item['slid'];
            $data[$key]['typeId'] = $item['cate_id'];
            $data[$key]['typeName'] = empty(parent::get_dc_current_supplier_cate($item['cate_id']))?'':parent::get_dc_current_supplier_cate($item['cate_id'])['name'];
            $data[$key]['skuCode'] = $item['mid'];
            $data[$key]['cid'] = $item['cid'];
            $data[$key]['mbarcode'] = $item['mbarcode'];
            $data[$key]['skuName'] = $item['mname'];
            $data[$key]['mstock'] = $item['mstock'];
            $data[$key]['price'] = $item['mprice'];
            $data[$key]['uom'] = $item['unit'];
            $data[$key]['funit'] = $item['funit'];
            $data[$key]['times'] = $item['times'];
            $data[$key]['qtyOverage'] = $qtyOverage;
            $data[$key]['amountOverage'] = $amountOverage;
            $data[$key]['qtyLoss'] =$qtyLoss;
            $data[$key]['amountLoss'] = $amountLoss;
            $data[$key]['qtyDiff'] = $item['schayishu'];
            $data[$key]['amountDiff'] = $item['schanyijine'];

            $data[$key]['memo'] = $item['memo'];
            $data[$key]['ctime'] = $item['ctime'];
        }

        $return['page'] = $page;
        $return['records'] = '';
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $data;

        /* 数据 */
        echo json_encode($return);exit;
    }

    //查询单位
    public function basic_unit_ajax(){
        init_app_page();
        $slid = intval($_REQUEST['id'])?intval($_REQUEST['id']):$GLOBALS['account_info']['slid'];;
        $isdd = $_REQUEST['isDisable'];
        $kw = $_REQUEST['name'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        $where="where 1=1";
        $where.=' and location_id='.$slid;

        if($kw){
            $where = " and name like '%$kw%'";
        }
        if(isset($isdd)){
            $where .= " and is_effect=$isdd";
        }
        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "dc_supplier_unit_cate $where order by id desc limit $limit ");
//        var_dump($list);
        $records = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."dc_supplier_unit_cate ".$where);
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        $cangkuArray = array();
        foreach($list as $k=>$v){
            $cangkuArray[$k]['id'] = $v['id'];
            $cangkuArray[$k]['name'] = $v['name'];
            $cangkuArray[$k]['isDisable'] = $v['is_effect'];
        }

        $return['dataList'] = $cangkuArray;
        echo json_encode($return);exit;
    }
    /**
     * 操作单位
     */
    public function basic_unit_edit(){
        init_app_page();
        $slid = intval($_REQUEST['slid'])?intval($_REQUEST['slid']):$GLOBALS['account_info']['slid'];
        $id = intval($_REQUEST['id']);
        $unitArray['location_id'] = $slid;
        $unitArray['name'] = $_REQUEST['name'];
        $unitArray['is_effect'] = $_REQUEST['isDisable'];
//        var_dump($unitArray);die;
        if($id > 0){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_unit_cate", $unitArray ,"UPDATE","id=".$id);
        }else{
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_unit_cate", $unitArray ,"INSERT");
        }

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }


    /**
     * 单位删除
     */
    public function ajax_unit_del()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = $_REQUEST['id'];
        if($id > 0){
            $deleteSQL = "delete from fanwe_dc_supplier_unit_cate WHERE id=".$id;
            $res = $GLOBALS['db']->query($deleteSQL);
            if($res){
                $return['success'] = true;
                $return['message'] = "操作成功";
            }else{
                $return['success'] = false;
                $return['message'] = "操作失败";
            }
            echo json_encode($return);exit;

        }
    }

    /**
     * 报废单列表
     */
    public function outbound_scrap_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        //$slid = $account_info['slid'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        if (($_REQUEST['createDateStart'])|| ($_REQUEST['createDateEnd'])){
            $begin_time = strim($_REQUEST['createDateStart']);
            $end_time = strim($_REQUEST['createDateEnd']);
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);

        //仓库列表
        $cangkulist=$GLOBALS['db']->getAll("select id,name from fanwe_cangku where slid=".$slid);
        //改成一维数据
        $cangku_name=array_column($cangkulist,'name','id');


        $danjuhao = $_REQUEST['danjuhao'];


        $cangku_id = $_REQUEST['warehouseId'];

        $isdisable = $_REQUEST['status'];

        $str="where slid=".$slid;
        if($isdisable > -1){
            $str .=  " and isdisable=".$isdisable;
        }
        if($danjuhao){
            $str .= " and (danjuhao='$danjuhao')";
        }
        if($cangku_id){
            $str .= " and (cangku_id ='$cangku_id'')";
        }
        if($begin_time_s){
            $str .=" and datetime >= '".$begin_time."' ";
        }
        if($end_time_s){
            $str .=" and datetime <= '".$end_time."' ";
        }

        $sql1 = "SELECT * FROM " . DB_PREFIX . "cangku_outbound  $str order by id desc limit $limit";
        $sql2 = "SELECT * FROM " . DB_PREFIX . "cangku_outbound  $str order by id desc";
//        var_dump($sql2);
        $list = $GLOBALS['db']->getAll($sql1);
        $list2 = $GLOBALS['db']->getAll($sql2);

        foreach ($list as $k=>$v){
            $list[$k]['cangku_name']=$cangku_name[$v['cangku_id']];
            $list[$k]['moban_name']=$moban_name[$v['moban_id']];
        }


        /* 数据 */
        $records = count($list2);
        $return['page'] = 1;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $list;

        /* 数据 */
        echo json_encode($return);exit;

    }

    /**
     * 新增报废单
     */
    public function outbound_scrap_add_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $djid = $_REQUEST['$djid'];
        //数组
        $data_danju=array(
            "danjuhao"=>time(), //此单据号可根据日期强制生成，确保唯一性
            "supplier_id"=>$supplier_id,
            "slid"=>$slid,
            "cangku_id"=>$_REQUEST['warehouseId'],
            "edit_user"=>$account_info['account_name'],
            "outbound_num"=>$_REQUEST['planQty'],  //JS算出数量
            "outbound_money"=>$_REQUEST['amountSum'], //JS算出金额
            "memo"=>$_REQUEST['memo'],
            "datetime"=>to_date(NOW_TIME,'Y-m-d H:i:s'),
            "isdisable"=>1  //状态 1 保存  2确认  3反确认  -1 删除
        );

        //存在ID，则更新，否则插入，取到ID
        if($djid){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_outbound",$data_danju,"UPDATE","id='$djid'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_outbound",$data_danju);
            $djid= $GLOBALS['db']->insert_id();
        }

        //插入，取到ID

//        $mid=$_REQUEST['mid'];
        // var_dump($mid);
        //从Stat表中删除不包含的ID
        $GLOBALS['db']->query("delete from ".DB_PREFIX."cangku_outbound_stat where djid=$djid");
        $detail = $_REQUEST['details'];
        //构造数组，填入统计表 由于这个是临时用，没有JS特效，这块帮的比较麻烦，如果使用AJAX的话会相对简单，组成以下的数组就行了
        $data_stat=array();
        $planQty = 0;
        $amountSum = 0;
        foreach ($detail as $key=>$item) {
            $result = $GLOBALS['db']->getRow('select * from fanwe_cangku_menu where cid='.$_REQUEST['warehouseId'].' and mid='.$detail[$key]['skuId']);
            $data_stat[$key]['djid']=$djid;
            $data_stat[$key]['slid']=$slid;
            $data_stat[$key]['mid']= $detail[$key]['skuId'];
            $data_stat[$key]['cate_id']= $detail[$key]['skuTypeId'];
            $data_stat[$key]['cid']= $_REQUEST['warehouseId'];
            $data_stat[$key]['mbarcode']= $detail[$key]['skuCode'];
            $data_stat[$key]['mname']= $detail[$key]['skuName'];
            $data_stat[$key]['stock']= $result['stock'];
            $data_stat[$key]['mstock']= $result['mstock'];
            $data_stat[$key]['mprice']= $detail[$key]['price'];
            $data_stat[$key]['unit']= $result['unit'];
            $data_stat[$key]['funit']= $result['funit'];
            $data_stat[$key]['times']= $result['times'];
            $data_stat[$key]['out_num']= $detail[$key]['planQty'];//报废数量
            $data_stat[$key]['out_money']= $detail[$key]['amount'];  //报废金额
            $data_stat[$key]['out_reason']= $detail[$key]['reasonId']; //报废原因
            $data_stat[$key]['memo']= $detail[$key]['memo'];
            $data_stat[$key]['ctime']=to_date(NOW_TIME,'Y-m-d H:i:s');
            $planQty+= $detail[$key]['planQty'];//报废数量
            $amountSum+=$detail[$key]['amount'];  //报废金额


//            //库存扣减
//            $data = array(
//                'mstock'=>  $result['mstock'] - $detail[$key]['planQty'],
//            );
//
//            $GLOBALS['db']->autoExecute('fanwe_cangku_menu',$data,'update','id='. $result['id']);
        }

        //数组
        $data_danju=array(
            "id"=>$djid,
            "outbound_num"=>$planQty,  //JS算出数量
            "outbound_money"=>$amountSum, //JS算出金额
        );

        //存在ID，则更新，否则插入，取到ID
        if($djid){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_outbound",$data_danju,"UPDATE","id='$djid'");
        }
        //插入Stat数据
        foreach ($data_stat as $value){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_outbound_stat",$value);
        }



        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }


    /**
     * 确认报废单，库存扣减
     */
    public function outbound_scrap_doconfirm(){
        //插入，取到ID
        init_app_page();
        $djid = $_REQUEST['id'];
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        //单据详情
        $sql = "select * from fanwe_cangku_outbound_stat where djid=".$djid;
        $detail = $GLOBALS['db']->getAll($sql);

//        var_dump($detail);die;
        foreach ($detail as $key=>$item) {
            $result = $GLOBALS['db']->getRow('select * from fanwe_cangku_menu where cid='.$item['cid'].' and mid='.$item['mid']);

            //库存扣减
            $data = array(
                'mstock'=>  $result['mstock'] - $item['out_num'],
            );

            $res = $GLOBALS['db']->autoExecute('fanwe_cangku_menu',$data,'update','id='. $result['id']);
        }

        //更新单据状态
        $sql = "update fanwe_cangku_outbound set isdisable=2 where id=$djid";
        $res = $GLOBALS['db']->query($sql);
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;

    }

    /**
     * 反确认报废单
     */
    /**
     * 确认报废单，库存扣减
     */
    public function outbound_scrap_withdraw(){
        //插入，取到ID
        init_app_page();
        $djid = $_REQUEST['id'];
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        //单据详情
        $sql = "select * from fanwe_cangku_outbound_stat where djid=".$djid;
        $detail = $GLOBALS['db']->getAll($sql);

//        var_dump($detail);die;
        foreach ($detail as $key=>$item) {
            $result = $GLOBALS['db']->getRow('select * from fanwe_cangku_menu where cid='.$item['cid'].' and mid='.$item['mid']);

            //库存扣减
            $data = array(
                'mstock'=>  $result['mstock'] + $item['out_num'],
            );

            $res = $GLOBALS['db']->autoExecute('fanwe_cangku_menu',$data,'update','id='. $result['id']);
        }

        //更新单据状态
        $sql = "update fanwe_cangku_outbound set isdisable=1 where id=$djid";
        $res = $GLOBALS['db']->query($sql);
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;

    }


    /**
     * 查询库存信息
     */
    public function basic_inventoryWarning_selectSingleSku(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        //仓库ID
        $cangku_id=$_REQUEST['warehouseId'];

        $list=$GLOBALS['db']->getAll("select fcm.*,fdc.cate_id as cate_id from fanwe_cangku_menu fcm left join fanwe_dc_menu fdc on fcm.mid=fdc.id where slid=".$slid." and cid=".$cangku_id);
        $data = [];
        foreach ($list as $key=>$item) {
            $data[$key]['skuId']=$item['mid'];
            $data[$key]['skuName']=$item['mname'];
            $data[$key]['skuCode']=$item['mbarcode'];
            $data[$key]['uom']=$item['unit'];
            $data[$key]['funit']=$item['funit'];
            $data[$key]['times']=$item['times'];
            $data[$key]['price']=$item['price'];
            $data[$key]['pinyin']=$item['pinyin'];
            $data[$key]['skuTypeId']= $item['cate_id'];
            $data[$key]['skuTypeName']=$this->get_dc_supplier_menu($item['cate_id']);
            $data[$key]['lowerInventory']=$item['minStock'];
            $data[$key]['safetyInventory']=$item['safeStock'];
            $data[$key]['upperInventory']=$item['maxStock'];
        }
        /* 数据 */
//        $records = count($list);
//        $return['page'] = 1;
//        $return['records'] = $records;
//        $return['total'] = ceil($records);
//        $return['status'] = true;
//        $return['resMsg'] = null;
//        $return['dataList'] = $data;

        /* 数据 */
        echo json_encode($data);exit;

    }

    /**
     * 保存库存预警设定
     */
    public function basic_inventoryWarning_saveSingle(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        //仓库ID
        $cangku_id=$_REQUEST['warehouseId'];

        //取到ID

        $details=$_REQUEST['details'];

        //构造数组，填入统计表 由于这个是临时用，没有JS特效，这块帮的比较麻烦，如果使用AJAX的话会相对简单，组成以下的数组就行了
        $data_stat=array();
        $res =false;
//        var_dump($details);die;
        foreach ($details as $key=>$item) {
            $data_stat['minStock']=$item['lowerInventory'];
            $data_stat['safeStock']=$item['safetyInventory'];
            $data_stat['maxStock']=$item['upperInventory'];
            $check_ising=$GLOBALS['db']->getRow("select id from fanwe_cangku_menu where slid=".$slid." and cid=".$cangku_id." and mid=".$item['skuId']);
//            var_dump($check_ising);
            if($check_ising){//存在，UPDate
                $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu",$data_stat,"UPDATE","id=".$check_ising['id']);
//                var_dump($res);
            }else{
                //Insert
                $data_stat['slid']=$slid;
                $data_stat['mid']=$item;
                $data_stat['cid']=$cangku_id;
                $data_stat['mbarcode']=$item['skuCode'];
                $data_stat['cate_id']=$item['skuTypeId'];
                $data_stat['mname']=$item['skuName'];
                $data_stat['unit']=$item['uom'];
                $data_stat['ctime']=to_date(NOW_TIME,"Y-m-d H:i:s");
                $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu",$data_stat);
            }
        }

        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    /**
     * 报废、退回原因设定
     */
    public function basic_reason_saving(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $content = $_REQUEST['content'];
        $id = $_REQUEST['id'];
        $reasonType = $_REQUEST['reasonType'];
//        var_dump($GLOBALS['db']->query("TRUNCATE table fanwe_basic_reason"));
//        var_dump($GLOBALS['db']->query("CREATE TABLE `fanwe_basic_reason`(`id` int(11) NOT NULL AUTO_INCREMENT ,`content` text,`reasonType` int(11) DEFAULT NULL,`slid` int(11) DEFAULT NULL,`supplier_id` int(11) DEFAULT NULL,`created` int(11) DEFAULT NULL,PRIMARY KEY(`id`))"));
            if($id > 0){
            $data=array(
                'content'=>$content
            );
            $res=$GLOBALS['db']->autoExecute('fanwe_basic_reason',$data,'update','id='.$id);

        }else{
            $data=array(
                'content'=>$content,
                'reasonType'=>$reasonType,
                'slid'=>$slid,
                'supplier_id'=>$supplier_id,
                'created'=>time()
            );
           $res= $GLOBALS['db']->autoExecute('fanwe_basic_reason',$data,'INSERT');

        }

        if($res){
            $return['success'] = true;
            $return['data'] = $data;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }

    /**
     * 删除报废、退回原因设定
     */
    public function basic_reason_del(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        if($id > 0){
            $res=$GLOBALS['db']->query('delete from fanwe_basic_reason where id='.$id);
        }
        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;
    }


    //商品配方设定
    public function ajax_skuBom_index()
    {
        init_app_page();
//        var_dump($GLOBALS['db']->getAll('select * from fanwe_cangku_log limit 1'));

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        //$slid = $account_info['slid'];
        $slid = $account_info['slid'];;
        $cate_id = $_REQUEST['cate_id']?intval($_REQUEST['cate_id']):'';
        $mid = $_REQUEST['codeOrName']?$_REQUEST['codeOrName']:'';
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        //预制、现制、半成品   1 2 6
        $print = ' and (';
        $arr = [];
        if($_REQUEST['wmTypeArray1']){
            array_push($arr,$_REQUEST['wmTypeArray1']);
        }
        if($_REQUEST['wmTypeArray2']){
            array_push($arr,$_REQUEST['wmTypeArray2']);
        }
        if($_REQUEST['wmTypeArray3']){
            array_push($arr,$_REQUEST['wmTypeArray3']);
        }

        $arrStr = '';
        foreach ($arr as $key=>$item) {
            if($key==0){
                $arrStr .= $item;
            }else{
                $arrStr .= ",".$item;
            }
        }

        $print .= ' a.print in ('.$arrStr.')';
        $print .= ')';
        if(empty($arrStr)){
            $print ='';
        }
        $sqlstr="where 1=1 and a.name <> '' $print and a.location_id=$slid ";
//        var_dump($sqlstr);
        if($cate_id){ //配送中心
            $sqlstr.=' and a.cate_id='.$cate_id;
        }

        if($mid){
            $sqlstr .=" and (a.pinyin like '%".$mid."%' or a.id  like '%".$mid."%' or a.barcode like '%".$mid."%' or a.name like '%".$mid."%' )";
        }
        if (($_REQUEST['confirmDateStart'])|| ($_REQUEST['confirmDateEnd'])){
            $begin_time = strim($_REQUEST['confirmDateStart']);
            $end_time = strim($_REQUEST['confirmDateEnd']);
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);
        if($begin_time_s){
            $sqlstr .=" and a.ctime > ".$begin_time_s." ";
        }
        if($end_time_s){
            $sqlstr .=" and a.ctime < ".$end_time_s." ";
        }

        $GLOBALS['tmpl']->assign("cate_id", $cate_id);
        $GLOBALS['tmpl']->assign("mid", $mid);

        //分类
        $conditions .= " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
        $conditions .= " and location_id=".$slid;
        $sqlsort = " select id,name,is_effect,sort,wcategory,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
        $sqlsort.=$conditions . " order by sort desc";

        $listsort = array();
        $wsublist = array();
        $wmenulist = $GLOBALS['db']->getAll($sqlsort);
        foreach($wmenulist as $wmenu)
        {
            if($wmenu['wcategory'] != '0') $wsublist[$wmenu['wcategory']][] = $wmenu;
        }
        foreach($wmenulist as $wmenu0)
        {
            if($wmenu0['wcategory'] == '0')
            {
                $listsort[] = $wmenu0;

                foreach($wsublist[$wmenu0['id']] as $wmenu1)
                {
                    $listsort[] = $wmenu1;
                    foreach($wsublist[$wmenu1['id']] as $wmenu2)
                    {
                        $listsort[] = $wmenu2;
                        foreach($wsublist[$wmenu2['id']] as $wmenu3)
                        {
                            $listsort[] = $wmenu3;
                        }
                    }
                }
            }
        }
        $GLOBALS['tmpl']->assign("sortlist", $listsort);


        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;


//        $sql="select a.id,a.name,a.cate_id,a.image,a.barcode,a.print,a.unit,b.name as cname from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id  ".$sqlstr." order by a.id desc limit ".$limit;
        $sql="select a.id,a.name,a.cate_id,a.image,a.barcode,a.print,a.unit,b.name as cname from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id  ".$sqlstr." order by a.id desc limit ".$limit;

        $sqlc="select count(a.id) from ".DB_PREFIX."dc_menu a left join ".DB_PREFIX."dc_supplier_menu_cate b on a.cate_id=b.id ".$sqlstr." order by a.id desc";

        $total = $GLOBALS['db']->getOne($sqlc);
        $page = new Page($total,$page_size);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        $list=$GLOBALS['db']->getAll($sql);

        $peifang_info=$GLOBALS['db']->getAll("select id,menu_id,datetime from fanwe_cangku_peifang where slid=".$slid);
        $peifang=array();
        foreach ($peifang_info as $ke=>$ve){
            $peifang[$ve['menu_id']]=$ve;
        }


        foreach($list as $k=>$v){
            $v['pfdate']=to_date($v[$peifang[$v['id']]['datetime']],"Y-m-d H:i:s");
            if(!empty($peifang[$v['id']])){
                $v['pf_status']=1;
            }else{
                $v['pf_status']=0;
            }
            $v['kclx']=$this->kcnx[$v['print']];
            $list[$k]=$v;

        }
        $arr = [];
        foreach ($list as $key=>$item) {
            if($item['print'] != 3){
                $price =$item['buyPrice'];
            }else{
                $price =$item['price'];
            }
            $arr[$key]['id'] =$item['id'];
            $arr[$key]['skuTypeName'] =$item['kclx'];
            $arr[$key]['wmTypeName'] =$item['cname'];
            $arr[$key]['skuCode'] =$item['id'];
            $arr[$key]['skuName'] =$item['name'];
            $arr[$key]['uom'] =$item['unit'];
            $arr[$key]['updateTime'] =$item['pfdate'];
            $arr[$key]['reckonPrice'] = $price;
            $arr[$key]['statusName'] =$item['pf_status']?'已保存':'未编辑';
            $arr[$key]['status'] =$item['pf_status'];
        }

        $return['page'] = $_REQUEST['page'];
        $return['records'] = $total;
        $return['total'] = ceil($total/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $arr;

        echo json_encode($return);exit;
    }

    /**
     *  保存配方
     */
    public function ajax_skuBom_add(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $menu_id=$_REQUEST['menu_id'];
        //配方ID
        $pfid=$_REQUEST['pfid']?intval($_REQUEST['pfid']):0;
        //配方数组
        $data_peifang=array(
            "menu_id"=>$menu_id,
            "slid"=>$slid,
            "gusuanchengben"=>$_REQUEST['bomReckonPrice'],//估算成本
            "datetime"=>NOW_TIME,
            "num"=>intval($_REQUEST['baseNum'])//数量
        );
        //存在配方ID，则更新，否则插入，取到配方ID
        if($pfid){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang",$data_peifang,"UPDATE","id='$pfid'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang",$data_peifang);
            $pfid= $GLOBALS['db']->insert_id();
        }

        //插入配方，取到配方ID

        $mid=$_REQUEST['details'];
        // var_dump($mid);
        //构造数组，填入统计表 由于这个是临时用，没有JS特效，这块帮的比较麻烦，如果使用AJAX的话会相对简单，组成以下的数组就行了
        $data_stat=array();
        foreach ($mid as $key=>$item) {
            $data_stat[$key]['mid']=$mid[$key]['skuId'];
            $data_stat[$key]['cate_id']=$mid[$key]['skuTypeId'];
            $data_stat[$key]['cate_name']=$mid[$key]['skuTypeName'];
            $data_stat[$key]['skuName']=$mid[$key]['skuName'];
            $data_stat[$key]['pfid']=$pfid;
            $data_stat[$key]['menu_id']=$menu_id;
            $data_stat[$key]['gusuan']=$mid[$key]['reckonPriceStr'];
            $data_stat[$key]['num_j']=$mid[$key]['netQtyStr'];
            $data_stat[$key]['chupinliu']=$mid[$key]['yieldRateStr'];
            $data_stat[$key]['num_m']=$mid[$key]['qty'];
            $data_stat[$key]['unit']=$mid[$key]['uom'];
            $data_stat[$key]['gusuanjine']=$mid[$key]['reckonAmount'];
            $data_stat[$key]['datetime']=NOW_TIME;
        }
        // var_dump($data_stat);
        //更新配方里的data_json字段
        $data_json=array('data_json'=>serialize($data_stat));

        $res = $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang",$data_json,"UPDATE","menu_id='$menu_id'");

        //删除该配方的所有信息
        $GLOBALS['db']->query("delete from fanwe_cangku_peifang_stat where pfid=".$pfid);
        //根据数组，依次插入数据库，这种方法效率比较低，临时使用
        foreach ($data_stat as $value){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_peifang_stat",$value);
        }
        if($res){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "操作失败";
        }
        echo json_encode($return);exit;


    }

    /**
     * 生产模板
     */
    public function product_moban_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        //$slid = $account_info['slid'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);

        $isdd = $_REQUEST['isdd'];
        $moban_keywords = $_REQUEST['moban_keywords'];
        $menu_keywords = $_REQUEST['menu_keywords'];
        $accept_location = $_REQUEST['accept_location'];

        $str="where slid=$slid ";

        if($moban_keywords){
            $str .= "and (name='$moban_keywords' or code='$moban_keywords')";
        }
        if($menu_keywords){
            $str .= "and (accept_goods like '%$menu_keywords%')";
        }
        if($accept_location){
            $str .= "and (accept_location like '%$accept_location%')";
        }



        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_product_mb  $str order by id desc limit $limit ");
        $list2 = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "cangku_product_mb  $str order by id  desc ");
        $data = [];
        foreach ($list as $key=>$item) {
            $data[$key]['id'] = $item['id'];
            $data[$key]['code'] = $item['code'];
            $data[$key]['name'] = $item['name'];
            $data[$key]['updaterName'] = $item['edit_user'];
            $data[$key]['updateTime'] = $item['datetime'];
            $data[$key]['isDisable'] = $item['isdisable'];
            $data[$key]['status'] = $item['isdisable'];
        }

        /* 数据 */
        $records = count($list2);
        $return['page'] = 1;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        $return['dataList'] = $data;

        /* 数据 */
        echo json_encode($return);exit;
    }

    //新增生产模板
    public function product_saving_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        //模板模板ID
        $mbid=$_REQUEST['mbid']?intval($_REQUEST['mbid']):0;
        //数组
        $data_moban=array(
            "edit_user"=>$account_info['account_name'],
            "supplier_id"=>$supplier_id,
            "slid"=>$slid,
            "code"=>empty($_REQUEST['code'])?time():$_REQUEST['code'],
            "name"=>$_REQUEST['name'],
            "accept_location"=>serialize($_REQUEST['accept_location']),
            "memo"=>$_REQUEST['memo'],
            "datetime"=>to_date(NOW_TIME,'Y-m-d H:i:s'),
            "isdisable"=>1
        );
        //存在ID，则更新，否则插入，取到ID
        if($mbid){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_product_mb",$data_moban,"UPDATE","id='$mbid'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_product_mb",$data_moban);
            $mbid= $GLOBALS['db']->insert_id();
        }

        //插入配方，取到配方ID

        $mid=$_REQUEST['templateDetails'];
        // var_dump($mid);
        //构造数组，填入统计表 由于这个是临时用，没有JS特效，这块帮的比较麻烦，如果使用AJAX的话会相对简单，组成以下的数组就行了
        $data_stat=array();
        foreach ($mid as $key=>$item) {
            $data_stat[$key]['id']=$item['id'];
            $data_stat[$key]['mid']=$item['mid'];
            $data_stat[$key]['cate_id']=$item['skuTypeId'];
            $data_stat[$key]['cname']=$item['skuTypeName'];
            $data_stat[$key]['name']=$item['skuName'];
            $data_stat[$key]['unit']=$item['uom'];
            $data_stat[$key]['price']=$item['price'];
            $exceptShopStr = $item['exceptShopStr'];
            $regStr = parent::get_tag_data($exceptShopStr);
            $regStr = str_replace("\"","",$regStr);
            if($regStr){
                $feiS = explode(",",$regStr[0]);
                $fei_slid=$feiS;
            }else{
                $fei_slid='';
            }
            $data_stat[$key]['fei_slid']=json_encode($fei_slid);
        }

        //更新配方里的data_json字段
        $data_json=array('accept_goods'=>serialize($data_stat));
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_product_mb",$data_json,"UPDATE","id='$mbid'");

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($res){//成功
            $return['success'] = true;
            $return['message'] = '保存成功';
        }else{

            $return['success'] = false;
            $return['message'] = '保存失败';
        }

        echo json_encode($return);exit;
    }

    /**
     * 生产入库列表ajax
     */
    public function basic_product_index_ajax(){
//        $r = $GLOBALS['db']->getAll("select * from fanwe_cangku_log where type=2");
//        var_dump($r);die;
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';
        $warehouseId = $_REQUEST['warehouseId']?intval($_REQUEST['warehouseId']):'99';


        if (($_REQUEST['begin_time'])|| ($_REQUEST['end_time'])){
            $begin_time = strim($_REQUEST['begin_time']);
            $end_time = strim($_REQUEST['end_time']);
        }else{	 //默认为当月的
            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);
//        if($type == 1){
//            $sqlstr="where a.gys is null";
//        }else{
//            $sqlstr="where 1=1";
//        }
        $sqlstr="where ywsort=-6";
        $sqlstr.=' and ( a.slid='.$location_id.')';

        if($begin_time_s){
            $sqlstr .=" and a.ctime > ".$begin_time_s." ";
        }
        if($end_time_s){
            $sqlstr .=" and a.ctime < ".$end_time_s." ";
        }
        if ($type !=99 ){
            $sqlstr .=" and a.type = ".$type." ";
        }
        if ($warehouseId !=99 ){
            $sqlstr .=" and a.cid = ".$warehouseId." ";
        }
        if ($ywsortid !=99 ){
            $sqlstr .=" and a.ywsort = ".$ywsortid." ";
        }
        if($_REQUEST['danjuhao'] !=""){
            $sqlstr .=" and a.danjuhao like '%".$_REQUEST['danjuhao']."%' ";
        }

//        $sqlstr .=" and f.print <> 4";
//        $sql2 = "select * from fanwe_cangku_log limit 1";
//        var_dump($GLOBALS['db']->getRow($sql2));
        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlrecords="select count(a.id) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";
//        var_dump($sql);
        $return = array();
        $records = $GLOBALS['db']->getOne($sqlrecords);
        $list = $GLOBALS['db']->getAll($sql);
//        var_dump($list);die;
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;

        foreach($list as $k=>$v){
            $v['ctime']=to_date($v['ctime'],'m-d H:i:s');
            $v['detail']=unserialize($v['dd_detail']);

            if ($v['type']==1){
                $v['type_show']	='入库';
                $v['gonghuo_show']	='供货人';
            }else{
                $v['type_show']	='出库';
                $v['gonghuo_show']	='收货人';
            }
            $v['ywsort']=$this->ywsort[$v['ywsort']];
            if(!empty($v['gys'])){
                if($type == 1){
                    $v['ywsort']='直拨入库';

                }else{
                    $v['ywsort']='直拨出库';

                }
            }
            $v['gonghuo']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gonghuoren']);
            $v['gys']=parent::get_gonghuoren_name($supplier_id,$location_id,$v['gys']);
            $list[$k]=$v;
        }
        $return['dataList'] = $list;
        echo json_encode($return);exit;
    }

    //入库单反确认
    public function go_down_withdraw(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $disabled = 1;
        $id = $_REQUEST['id'];
        $sql = "update fanwe_cangku_log set isdisable=$disabled where id=$id";
        $res = $GLOBALS['db']->query($sql);

        //todo 需要新增一条入库记录
        //查询入库记录
        $sql2 = "select * from fanwe_cangku_log where id=$id";
        $res2 = $GLOBALS['db']->getRow($sql2);
        $detail = unserialize($res2['dd_detail']);
        $cid = $res2['cid'];
        //更新仓库
        $bumen = $res2['gonghuoren'];
        $gys = $res2['gys'];
        $amount = 0;//总金额
//        var_dump($detail);die;
        foreach($detail as $k=>$v){
            if (intval($v['mid'])==0){
                continue;
            }
            $mid=$v['mid'];

            $sqlstr="where slid=$slid and mid=$mid and cid=$cid";
            $order_num=floatval($v['num']);

            $cate_id=$v['cate_id'];
            $unit_type=intval($v['unit_type']);
            if ($unit_type==1){  //使用的是副单位
                $order_num=$order_num*$v['times']; //换算成主单位
            }

            //存在的话更新数量
            if ($_REQUEST['type']==1){ //入库
                $check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstr);
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
            }else{ //出库
                $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
            }

            //
            if ($_REQUEST['type']==1){ //入库
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$order_num where id=".$mid);
            }else{
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+$order_num where id=".$mid);
            }

            $amount += $order_num*$v['price'];
        }

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($res){//成功
            $return['success'] = true;
            $return['message'] = '保存成功';
        }else{

            $return['success'] = false;
            $return['message'] = '保存失败';
        }

        echo json_encode($return);exit;
    }

    //出入库单确认
    public function go_down_doconfirm(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $disabled = 2;
        $id = $_REQUEST['id'];

        //todo 需要新增一条入库记录
        //查询入库记录
        $sql2 = "select * from fanwe_cangku_log where id=$id";
        $res2 = $GLOBALS['db']->getRow($sql2);
        $detail = unserialize($res2['dd_detail']);
        $cid = $res2['cid'];
        //更新仓库
        $bumen = $res2['gonghuoren'];
        $gys = $res2['gys'];
        $amount = 0;//总金额
//        var_dump($detail);die;
        foreach($detail as $k=>$v){
            if (intval($v['mid'])==0){
                continue;
            }
            $mid=$v['mid'];

            //0805 查询本店的ID 根据商品条码
            if($v['barcode'] !="")  {
                $mid=$GLOBALS['db']->getOne("select id from fanwe_dc_menu where location_id='".$slid."' and (barcode='".$v['barcode']."')");
            }else{
                $mid=$GLOBALS['db']->getOne("select id from fanwe_dc_menu where location_id='".$slid."' and (name='".$v['name']."')");
            }
            if (!$mid){
                //如果ID不存在，则自动增加商品进入产品库，返回ID
                $dc_menu_data=array(
                    "location_id"=>$slid,
                    "supplier_id"=>$supplier_id,
                    "barcode"=>$v['barcode'],
                    "name"=>$v['name'],
                    "cate_id"=>$v['cate_id'],
                    "price"=>floatval($v['price']),
                    "unit"=>$v['unit'],
                    "funit"=>$v['funit'],
                    "times"=>$v['times'],
                    "type"=>$v['type']
                );

                $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $dc_menu_data ,"INSERT");
                $mid = $GLOBALS['db']->insert_id();
            }

//            $cid=$GLOBALS['db']->getOne("select cid from fanwe_cangku_bangding_cangku where slid=$slid and mid=$mid"); //取得仓库ID
//            if(!$cid){
//                $cid=$GLOBALS['db']->getOne("select id from fanwe_cangku where slid=$slid and isdisable=1 order by id asc limit 1");//取得仓库ID
//            }

            $sqlstr="where slid=$slid and mid=$mid and cid=$cid";
            $order_num=floatval($v['num']);

            $cate_id=$v['cate_id'];
            $unit_type=intval($v['unit_type']);
            if ($unit_type==1){  //使用的是副单位
                $order_num=$order_num*$v['times']; //换算成主单位
            }

            //存在的话更新数量
            if ($_REQUEST['type']==1){ //入库
                $check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstr);
                if($check){
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,stock=stock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
                }else{
                    //添加
                    $data_menu=array(
                        "slid"=>$slid,
                        "mid"=>$mid,
                        "cid"=>$cid,
                        "cate_id"=>$v['cate_id'],
                        "mbarcode"=>$v['barcode'],
                        "mname"=>$v['name'],
                        "mstock"=>$order_num,
                        "stock"=>$order_num,
                        "minStock"=>10,
                        "maxStock"=>10000,
                        "unit"=>$v['unit'],
                        "funit"=>$v['funit'],
                        "times"=>$v['times'],
                        "type"=>$v['type'],
                        "ctime"=>to_date(NOW_TIME)
                    );
                    $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $data_menu ,"INSERT");
                }

                $data_gys=array(
                    "slid"=>$slid,
                    "mid"=>$mid,
                    "cid"=>$cid,
                    "cate_id"=>$cate_id,
                    "mbarcode"=>$v['mbarcode'],
                    "mname"=>$v['mname'],
                    "stock"=>$order_num,
                    "gonghuoren"=>$bumen,
                    "gys"=>$gys,
                    "unit"=>$v['unit'],
                    "funit"=>$v['funit'],
                    "times"=>$v['times'],
                    "type"=>$v['type'],
                    "price"=>floatval($v['price']),
                    "ctime"=>to_date(NOW_TIME)
                );
                $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu_gys", $data_gys ,"INSERT");
            }else{ //出库

                $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);

            }

            //
            if ($_REQUEST['type']==1){ //入库
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+$order_num where id=".$mid);
            }else{
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$order_num where id=".$mid);
            }

            $amount += $order_num*$v['price'];
        }

        //采购入库出库单
        //验收入库单url封装
//        if(!empty($bumen)){
//            //采购扣减库存，部门领料实际已经将商品出库
//            $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
//            $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
//
//            $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$order_num where id=".$mid);
//        }

        //更新单据状态
        $sql = "update fanwe_cangku_log set isdisable=$disabled where id=$id";
        $res = $GLOBALS['db']->query($sql);
        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;



        if($res){//成功
            $return['success'] = true;
            $return['message'] = '保存成功';
        }else{

            $return['success'] = false;
            $return['message'] = '保存失败';
        }

        echo json_encode($return);exit;
    }

    /**
     * 删除入库单
     */
    public function go_down_delete_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $sql = "select * from fanwe_cangku_log where id=$id";
        $res = $GLOBALS['db']->getRow($sql);
        if(!empty($res['isdisable']) && $res['isdisable'] == 2){

            //查询入库记录
            $sql2 = "select * from fanwe_cangku_log where id=$id";
            $res2 = $GLOBALS['db']->getRow($sql2);
            $detail = unserialize($res2['dd_detail']);
            $cid = $res2['cid'];
            //更新仓库
            $bumen = $res2['gonghuoren'];
            $gys = $res2['gys'];
            $amount = 0;//总金额

            foreach($detail as $k=>$v){
                if (intval($v['mid'])==0){
                    continue;
                }
                $mid=$v['mid'];

                $sqlstr="where slid=$slid and mid=$mid and cid=$cid";
                $order_num=floatval($v['num']);

                $cate_id=$v['cate_id'];
                $unit_type=intval($v['unit_type']);
                if ($unit_type==1){  //使用的是副单位
                    $order_num=$order_num*$v['times']; //换算成主单位
                }

                //存在的话更新数量
                if ($_REQUEST['type']==1){ //入库
                    $check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstr);
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
                }else{ //出库
                    $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
                }

                //
                if ($_REQUEST['type']==1){ //入库
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$order_num where id=".$mid);
                }else{
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+$order_num where id=".$mid);
                }

                $amount += $order_num*$v['price'];
            }
        }
        $sql = "delete from fanwe_cangku_log where id=$id";
        $res = $GLOBALS['db']->query($sql);

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($res){//成功
            $return['success'] = true;
            $return['message'] = '保存成功';
        }else{

            $return['success'] = false;
            $return['message'] = '保存失败';
        }

        echo json_encode($return);exit;
    }

    //根据模板id获得生产单信息
    public function product_template_info()
    {
        init_app_page();

        $where = " where 1=1 ";
        $templateId = $_REQUEST['templateId'];
        $warehouseId = $_REQUEST['warehouseId'];
        if($templateId){
            $where .=" and id=$templateId";
        }
        $row = $GLOBALS['db']->getRow("select * from fanwe_cangku_product_mb $where");


        $inventoryAmount = 0;
        $ccAmount = 0;
        $profitAmount = 0;
        $lossAmount = 0;
        $dd_detail = [];
        foreach (unserialize($row['accept_goods']) as $key=>$item) {
            $value = $GLOBALS['db']->getRow("select * from fanwe_cangku_menu where cid=".$warehouseId." and mid=".$item['id']);
//var_dump($item);
            $typeName = parent::get_dc_current_supplier_cate($item['cate_id']);
            if (!empty($typeName)){
                $dd_detail[$key]['skuTypeName'] = $typeName['name'];
            }else{
                $dd_detail[$key]['skuTypeName'] = '<span style="color:red">顶级分类</span>';
            }
            $dd_detail[$key]['id'] = $value['id'];
            $dd_detail[$key]['skuId'] = $value['mid'];
            $dd_detail[$key]['skuTypeId'] = $value['cate_id'];
            $dd_detail[$key]['skuCode'] = $value['mbarcode'];
            $dd_detail[$key]['skuName'] = $value['mname'];
            $dd_detail[$key]['uom'] = $value['unit'];
            $dd_detail[$key]['price'] = $item['price'];
            $dd_detail[$key]['inventoryQty'] = $value['mstock'];
            $dd_detail[$key]['actualQty'] = 0;
            $dd_detail[$key]['realTimeInventory'] = $value['mstock'];
            $dd_detail[$key]['ccQty'] = $value['mstock'];
            $dd_detail[$key]['qtyDiff'] = 0;
            $dd_detail[$key]['amountDiff'] = 0;
            $dd_detail[$key]['remarks'] = '';
            $dd_detail[$key]['amount'] = 0;
            $dd_detail[$key]['relTimeAmount'] = 0;
            $dd_detail[$key]['alreadyData'] = 1;
            $dd_detail[$key]['remarks'] ='';
            $dd_detail[$key]['djid'] = $_REQUEST['id'];
            $dd_detail[$key]['skuConvert'] = '';
            $dd_detail[$key]['skuConvertOfStandard'] = '';
            $dd_detail[$key]['standardPrice'] = '';
            $dd_detail[$key]['standardUnitId'] = '';
            $dd_detail[$key]['standardUnitName'] = '';
            $dd_detail[$key]['standardInventoryQty'] =$value['mstock'];

            $inventoryAmount +=  $dd_detail[$key]['inventoryQty'];
            $ccAmount +=  $dd_detail[$key]['ccAmount'];
        }

//        $return['flag'] = null;
//        $return['exception'] = null;
//        $return['refresh'] = false;
//        $return['success'] = true;
//        $return['message'] = '';
//        $return['result'] = $dd_detail;
        $return['inventoryAmount'] = $inventoryAmount;
        $return['amount'] = $ccAmount;
        if($ccAmount>0){
            $return['profitAmount'] = $ccAmount;
            $return['lossAmount'] = 0;
        }else{
            $return['profitAmount'] = 0;
            $return['lossAmount'] = $ccAmount;
        }



        $return['details'] = $dd_detail;
        echo json_encode($return);exit;
    }

    /**
     * 删除报废单
     */
    public function outbound_scrap_del(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['id'];
        $sql = "select * from fanwe_cangku_outbound where id=$id";
        $res = $GLOBALS['db']->getRow($sql);
        if(!empty($res['isdisable']) && $res['isdisable'] == 2){

            //查询入库记录
            $sql = "select * from fanwe_cangku_outbound_stat where djid=".$id;
            $detail = $GLOBALS['db']->getAll($sql);
            //更新仓库
            $amount = 0;//总金额

            foreach($detail as $k=>$v){
                if (intval($v['mid'])==0){
                    continue;
                }
                $mid=$v['mid'];
                $cid=$v['cid'];
                $sqlstr="where slid=$slid and mid=$mid and cid=$cid";
                $order_num=floatval($v['out_num']);

                $cate_id=$v['cate_id'];
                $unit_type=intval($v['unit_type']);
                if ($unit_type==1){  //使用的是副单位
                    $order_num=$order_num*$v['times']; //换算成主单位
                }

                //存在的话更新数量
                $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);

                //
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+$order_num where id=".$mid);


                $amount += $order_num*$v['price'];
            }
        }
        $sql = "delete from fanwe_cangku_outbound where id=$id";
        $sql2 = "delete from fanwe_cangku_outbound_stat where djid=$id";
        $res = $GLOBALS['db']->query($sql);
        $res = $GLOBALS['db']->query($sql2);

        $return['flag'] = null;
        $return['exception'] = null;
        $return['refresh'] = false;
        if($res){//成功
            $return['success'] = true;
            $return['message'] = '保存成功';
        }else{

            $return['success'] = false;
            $return['message'] = '保存失败';
        }

        echo json_encode($return);exit;
    }
}