<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class ajaxModule extends KizBaseModule{
    function __construct()
    {
        parent::__construct();
        global_run();

        $ywsort=array(
            "-5"=>"生产退料",
            "-4"=>"退还入库",
            "-3"=>"预配退货",
//            "-2"=>"其他入库",
//            "-1"=>"盘盈",
            "1"=>"盘盈",
            "2"=>"无订单入库",
            "3"=>"要货调入",
            "4"=>"初始库存",
            "6"=>"盘亏",
            "7"=>"无订单出库",
            "8"=>"要货调出",
            "9"=>"退货",
            "10"=>"生产领料",
            "11"=>"借用出库",
            "12"=>"其他出库",
            "13"=>"配送领料",
            "14"=>"品牌销售出库",

        );
        $this->ywsort=$ywsort;
        $this->gonghuoren=array(
            "1"=>"临时客户",
            "2"=>"临时运输商",
            "3"=>"临时供应商",
            "4"=>"领料出库"
        );
    }

    /**
     * 入库列表ajax
     */
    public function go_down_index_ajax(){
        $page_size = $_REQUEST['rows']?$_REQUEST['rows']:20;
        $page = intval($_REQUEST['page']);
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $location_id = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];
        $type = $_REQUEST['type']?intval($_REQUEST['type']):'99';
        $ywsortid = $_REQUEST['ywsortid']?intval($_REQUEST['ywsortid']):'99';

        if (($_REQUEST['begin_time'])|| ($_REQUEST['end_time'])){
            $begin_time = strim($_REQUEST['begin_time']);
            $end_time = strim($_REQUEST['end_time']);
        }else{	 //默认为当月的
            $begin_time=date('Y-m-01', strtotime(date("Y-m-d")))." 0:00:00";
            $end_time=date('Y-m-d', strtotime("$begin_time +1 month -1 day")).' 23:59:59';
        }
        $begin_time_s = strtotime($begin_time);
        $end_time_s = strtotime($end_time);

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
        if ($ywsortid !=99 ){
            $sqlstr .=" and a.ywsort = ".$ywsortid." ";
        }
        if($_REQUEST['danjuhao'] !=""){
            $sqlstr .=" and a.danjuhao like '%".$_REQUEST['danjuhao']."%' ";
        }

        $sql="select a.*,c.name as cname from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc limit ".$limit;
        $sqlrecords="select count(a.id) as tot from ".DB_PREFIX."cangku_log a left join ".DB_PREFIX."cangku c on a.cid=c.id ".$sqlstr." order by a.id desc";

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
            $v['gonghuo']=$this->get_gonghuoren_name($supplier_id,$location_id,$v['gonghuoren']);
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
        if($page==0) $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $where = "where 1 and g.location_id=$slid";
        if($_REQUEST['skuTypeId']){
            $where .= " and g.cate_id=".$_REQUEST['skuTypeId'];
        }
        if($_REQUEST['skuCodeOrName']){
            $where .= " and (g.name like'%".$_REQUEST['skuCodeOrName']."%' or g.barcode like'%".$_REQUEST['skuCodeOrName']."%')";
        }
        $sqlcount = "select count(id) from fanwe_dc_menu g $where";
        $records = $GLOBALS['db']->getOne($sqlcount);
        $sql = "select g.id,g.name as skuName,g.barcode as skuCode,g.unit as uom,g.funit,g.times,g.price,g.pinyin,g.cate_id as skuTypeId,c.name as skuTypeName,g.stock as inventoryQty from fanwe_dc_menu g LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id $where limit $limit";
        $check=$GLOBALS['db']->getAll($sql);

        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_diaobo' ");print_r($table);exit;

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records/$page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        if($check){
            $return['dataList'] = $check;
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
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];

        $dhid = $_REQUEST['asnNoView']?intval($_REQUEST['asnNoView']):'0';

        $sqlcheck="select dd_detail from fanwe_cangku_log where slid=$slid and  danjuhao='$dhid'";
        $isRuku  =	$GLOBALS['db']->getRow($sqlcheck);
        if($isRuku){
            $return['success'] = false;
            $return['message'] = "已经入过库了，请勿重复操作！";
            echo json_encode($return);exit;
        }
        $datailinfo = array();
        foreach($_REQUEST['detail'] as $k=>$v){
            $datailinfo[$k]['mid'] = $v['skuId'];
            $datailinfo[$k]['unit'] = $v['uom'];
            $datailinfo[$k]['funit'] = $v['funit'];
            $datailinfo[$k]['times'] = $v['times'];
            $datailinfo[$k]['yuan_price'] = $v['price'];
            $datailinfo[$k]['name'] = $v['skuName'];
            $datailinfo[$k]['barcode'] = $v['skuCode'];
            $datailinfo[$k]['type'] = $v['type'];
            $datailinfo[$k]['unit_type'] = $v['unit_type'];
            $datailinfo[$k]['price'] = $v['price'];
            $datailinfo[$k]['num'] = $v['inventoryQty'];
            $datailinfo[$k]['zmoney'] = $v['uom'];
            $datailinfo[$k]['memo'] = $v['memo'];
        }

        $dd_detail=serialize($datailinfo);
        $ddbz = $_REQUEST['ddbz']?intval($_REQUEST['ddbz']):'0';

        //if($unit_type==9){$unit_type==0;}
        $datain=$_REQUEST;
        $datain['ctime']= time()+ 60*60*8;
        $datain['dd_detail']=$dd_detail;
        $datain['slid']=$slid;
        $datain['type'] = $_REQUEST['type'];
        $datain['danjuhao'] = $_REQUEST['asnNoView'];
        $datain['ywsort'] = $_REQUEST['senderId'];
        $datain['cid'] = $_REQUEST['warehouseId'];
        $datain['lihuo_user'] = $account_info['account_name'];

        //更新仓库
        $detail=$_REQUEST['details'];

        $amount = 0;//总金额

        foreach($detail as $k=>$v){
            if (intval($v['id'])==0){
                continue;
            }
            $mid=$v['id'];

            //0805 查询本店的ID 根据商品条码
            if($ddbz>0){
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
                        "cate_id"=>$v['skuTypeId'],
                        "price"=>floatval($v['price']),
                        "unit"=>$v['unit'],
                        "funit"=>$v['funit'],
                        "times"=>$v['times'],
                        "type"=>$v['type']
                    );

                    $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $dc_menu_data ,"INSERT");
                    $mid = $GLOBALS['db']->insert_id();
                }
            }

            $cid=$GLOBALS['db']->getOne("select cid from fanwe_cangku_bangding_cangku where slid=$slid and mid=$mid"); //取得仓库ID
            if(!$cid){
                $cid=$GLOBALS['db']->getOne("select id from fanwe_cangku where slid=$slid and isdisable=1 order by id asc limit 1");//取得仓库ID
            }

            $sqlstr="where slid=$slid and mid=$mid and cid=$cid";
            $order_num=floatval($v['actualQty']);
            $cate_id=$v['skuId'];
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
                        "cate_id"=>$v['skuTypeId'],
                        "mbarcode"=>$v['skuCode'],
                        "mname"=>$v['skuName'],
                        "mstock"=>$order_num,
                        "stock"=>$order_num,
                        "minStock"=>10,
                        "maxStock"=>10000,
                        "unit"=>$v['uom'],
                        "funit"=>$v['funit'],
                        "times"=>$v['times'],
                        "type"=>$v['type'],
                        "ctime"=>to_date(NOW_TIME)
                    );
                    $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $data_menu ,"INSERT");
                }

                //写入库商品明细
                $gonghuoren=$GLOBALS['db']->getOne("select gid from fanwe_cangku_bangding_gys where slid=$slid and mid=$mid"); //取得绑定的供应商
                if(!$cid){
                    $gonghuoren='linshi_3'; //临时供应商3
                }

                $data_gys=array(
                    "slid"=>$slid,
                    "mid"=>$mid,
                    "cid"=>$cid,
                    "mbarcode"=>$v['skuCode'],
                    "mname"=>$v['skuName'],
                    "stock"=>$order_num,
                    "gonghuoren"=>$gonghuoren,
                    "unit"=>$v['uom'],
                    "funit"=>$v['funit'],
                    "times"=>$v['times'],
                    "type"=>$v['type'],
                    "price"=>floatval($v['price']),
                    "ctime"=>to_date(NOW_TIME)
                );
                $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu_gys", $data_gys ,"INSERT");
            }else{ //出库
                $check=$GLOBALS['db']->getRow("select mstock from fanwe_cangku_menu ".$sqlstr);
                if($order_num>$check['mstock']){
                    $return['flag'] = null;
                    $return['exception'] = null;
                    $return['refresh'] = false;
                    $return['success'] = false;
                    $return['message'] ="库存不足,非法提交！";
//                    showBizErr("库存不足,非法提交，后果自负！",0,url("biz","cangku#index&id=$slid"));
                }else{//操作减库存
                    $res=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);
                }
            }

            //增加MENU库表
            if ($_REQUEST['type']==1){ //入库
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock+$order_num where id=".$mid);
            }else{
                $res=$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set stock=stock-$order_num where id=".$mid);
            }

            $amount += $order_num*$v['price'];
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


        $datain['zmoney'] = $amount;
        if($res){
            $GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain ,"INSERT");
        }else{
            $return['success'] = false;
            $return['message'] = "查无结果！";
        }
        echo json_encode($return);exit;
    }

    /**
     * 调拨列表ajax
     */
    public function diaobo_list_ajax(){
        init_app_page();
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
        foreach($list as $kl=>$vl){
            $vl['detail']=unserialize($vl['dd_detail']);
            $vl['fromWmName']= $cangku_names[$vl['cid']];
            $vl['toWmName']= $cangku_names[$vl['cidtwo']];
            $vl['transferOrderNo'] = $vl['danjuhao'];
            $vl['updateTime'] = to_date($vl['ctime'],'m-d H:i:s');
            $vl['statusName'] = "";
            $vl['status'] = "";
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
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];

        $datailinfo = array();
        foreach($_REQUEST['details'] as $k=>$v){
            $datailinfo[$k]['mid'] = $v['skuId'];
            $datailinfo[$k]['unit'] = $v['uom'];
            $datailinfo[$k]['funit'] = $v['funit'];
            $datailinfo[$k]['times'] = $v['times'];
            $datailinfo[$k]['yuan_price'] = $v['price'];
            $datailinfo[$k]['name'] = $v['skuName'];
            $datailinfo[$k]['barcode'] = $v['skuCode'];
            $datailinfo[$k]['type'] = $v['type'];
            $datailinfo[$k]['unit_type'] = $v['unit_type'];
            $datailinfo[$k]['price'] = $v['price'];
            $datailinfo[$k]['num'] = $v['inventoryQty'];
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
            //减库
            $sqlstr="where slid=$slid and mid=$mid and cid=$cid";	 //减库条件
            $sqlstrtwo="where slid=$slid and mid=$mid and cid=$cidtwo";	 //加库条件
            $res1=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock-$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstr);

            $check=$GLOBALS['db']->getRow("select * from fanwe_cangku_menu ".$sqlstrtwo);
            if($check){
                $res2=$GLOBALS['db']->query("update ".DB_PREFIX."cangku_menu set mstock=mstock+$order_num,stock=stock+$order_num,ctime='".to_date(NOW_TIME)."' ".$sqlstrtwo);
            }else{
                //添加
                $data_menu=array(
                    "slid"=>$slid,
                    "mid"=>$mid,
                    "cid"=>$cidtwo,
                    "cate_id"=>$v['skuTypeId'],
                    "mbarcode"=>$v['skuCode'],
                    "mname"=>$v['skuTypeName'],
                    "mstock"=>$order_num,
                    "stock"=>$order_num,
                    "minStock"=>10,
                    "maxStock"=>10000,
                    "unit"=>$v['uom'],
                    "funit"=>$v['funit'],
                    "times"=>$v['times'],
                    "type"=>$v['type'],
                    "ctime"=>to_date(NOW_TIME)
                );
                $res2=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_menu", $data_menu ,"INSERT");
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

        if($res1 && $res2){
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_diaobo", $datain);  //写入调拨记录
            //写出库记录
            $datain['ywsort']=5; //仓库调拨
            $datain['gonghuoren']='cangku_'.$cidtwo;
            unset($datain['cidtwo']); //销毁入库的仓库ID
            $datain['type']=2;
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入出库记录
            $datain['cid']=$cidtwo;
            $datain['type']=1;
            $datain['gonghuoren']='cangku_'.$cid;
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku_log", $datain);  //写入入库记录
        }else{
            $return['success'] = false;
            $return['message'] = "查无结果！";
        }
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
        $sortconditions = " where wlevel<4 and supplier_id = ".$supplier_id; // 查询条件
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
            $cangkuArray[$k]['deductionName'] = '';
        }

        $return['dataList'] = $cangkuArray;
        echo json_encode($return);exit;
    }

    function get_gonghuoren_name($supplier_id,$slid,$gonghuoren){

        $linshi=array(
            "1"=>"临时客户",
            "2"=>"临时运输商",
            "3"=>"临时供应商"
        );

        $slidlist=$GLOBALS['db']->getAll("select id,name from fanwe_supplier_location where supplier_id=".$supplier_id);
        $slid_names = array();
        $slid_names = array_reduce($slidlist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $gys_ids=$GLOBALS['db']->getOne("select a.gys_ids from fanwe_deal_city a left join fanwe_supplier_location b on a.id=b.city_id where b.id=".$slid);
        $sql_gys="select id,name from fanwe_supplier_location where id in(".$gys_ids.")";
        $gyslist=$GLOBALS['db']->getAll($sql_gys);


        $city_names = array();
        $city_names = array_reduce($gyslist, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $location_gys=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_gys where slid=".$slid);
        $local_names = array();
        $local_names = array_reduce($location_gys, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $location_bumen=$GLOBALS['db']->getAll("select id,name from fanwe_cangku_bumen where slid=".$slid);
        $local_bumen = array();
        $local_bumen = array_reduce($location_bumen, create_function('$v,$w', '$v[$w["id"]]=$w["name"];return $v;'));

        $gonghuoren_arr=explode('_',$gonghuoren);
        $gonghuoren_type=$gonghuoren_arr[0];
        $gonghuoren_id=$gonghuoren_arr[1];
        if ($gonghuoren_type=='linshi'){
            $gys_name=$linshi[$gonghuoren_id];
        }elseif($gonghuoren_type=='slid'){
            $gys_name=$slid_names[$gonghuoren_id];
        }elseif($gonghuoren_type=='citygys'){
            $gys_name=$city_names[$gonghuoren_id];
        }elseif($gonghuoren_type=='localgys'){
            $gys_name=$local_names[$gonghuoren_id];
        }elseif($gonghuoren_type=='bumen'){
            $gys_name=$local_bumen[$gonghuoren_id];
        }elseif($gonghuoren_type=='other'){
            $gys_name=$GLOBALS['db']->getOne("select name from fanwe_supplier_location where id=".$gonghuoren_id);
        }elseif($gonghuoren_type=='user'){
            $gys_name='存货用户：'.$GLOBALS['db']->getOne("select user_name from fanwe_user where id=".$gonghuoren_id);
        }
        return $gys_name;
    }

    public function dc_cangku_add_ajax(){
        init_app_page();
        $slid = intval($_REQUEST['slid'])?intval($_REQUEST['slid']):$GLOBALS['account_info']['slid'];;
        $cangkuArray['slid'] = $slid;
        $cangkuArray['tel'] = '';
        $cangkuArray['address'] = '';
        $cangkuArray['contact'] = '';
        $cangkuArray['name'] = $_REQUEST['warehouseName'];
        $cangkuArray['isdisable'] = $_REQUEST['isDisable'];
        $cangkuexsit = $GLOBALS['db']->getRow("select name from ".DB_PREFIX."cangku  where slid=".$slid." and name='".$_REQUEST['warehouseName']."'");
        if($cangkuexsit){
            $return['success'] = false;
            $return['message'] = "仓库名已存在！";
            echo json_encode($return);exit;
        }
        $res=$GLOBALS['db']->autoExecute(DB_PREFIX."cangku", $cangkuArray ,"INSERT");
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
            $dc_menu_id = $list[$i][3];//编号
            $num = $list[$i][6];//导入库存
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
        $slid = intval($_REQUEST['slid'])?intval($_REQUEST['slid']):$GLOBALS['account_info']['slid'];;
        $cateArray['is_effect'] = 0;//原料为0
        $cateArray['icon_img'] = '';
        $cateArray['iconcolor'] = '';
        $cateArray['iconfont'] = '';
        $cateArray['name'] = $_REQUEST['typeName'];
        $cateArray['sort'] = $_REQUEST['sort'];
        $cateArray['supplier_id'] = $supplier_id;
        $cateArray['location_id'] = $slid;
        $cateArray['wcategory'] = $_REQUEST['parentId'];//父分类

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
        $slid = $_REQUEST['id']?intval($_REQUEST['id']):$account_info['slid'];

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

        $dc_menu_data=array(
            "location_id"=>$slid,
            "supplier_id"=>$supplier_id,
            "barcode"=>$skuList->barCode,
            "name"=>$skuList->skuName,
            "cate_id"=>$skuList->skuTypeId,
            "unit"=>$unit,
            "funit"=>$funit,
            "times"=>$times,
            "type"=>'',
            "buyPrice"=>$skuPrice->purchasePrice,
            "price"=>$skuPrice->price,
            "customerPrice"=>$skuPrice->costPrice,
            "sellPrice2"=>$skuPrice->balancePrice
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
}