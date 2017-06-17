<?php
require_once 'core/page.php';
require_once APP_ROOT_PATH."system/model/dc.php";


class ajaxSettingsModule extends KizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();

    }

    public function create(){
//        标签管理
//        $sql = "CREATE TABLE `fanwe_supplier_category` (
//  `id` int(11) NOT NULL AUTO_INCREMENT,
//  `supplierCode` varchar(255) DEFAULT NULL,
//  `supplierName` varchar(255) DEFAULT NULL,
//  `createTime` varchar(255) DEFAULT NULL,
//  `updateTime` varchar(255) DEFAULT NULL,
//  `state` int(11) NOT NULL DEFAULT '0',
//  `remark` varchar(5000) DEFAULT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=MyISAM DEFAULT CHARSET=utf8;
//";
//        $sql = "CREATE TABLE `fanwe_dish_goods_tag` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(255) DEFAULT NULL,  `sort` int(11) DEFAULT NULL,  `created` int(11) DEFAULT NULL,  `update` int(11) DEFAULT NULL,  `is_effect` int(11) DEFAULT NULL,  `location_id` int(11) DEFAULT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $sql = "show columns from fanwe_goods_extends";
        $res = $GLOBALS['db']->getAll($sql);
        var_dump($res);die;


    }
    /**
     * 做法管理列表功能
     */
    public function dish_cookingway_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows'] ? $_REQUEST['rows'] : 20;
        $page = intval($_REQUEST['page']);
        $propertyTypeId = $_REQUEST['propertyTypeId'];

        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $where = "where  g.location_id=$slid";

        $sql = "select * from fanwe_dc_supplier_taste where id=".$propertyTypeId;
        $flavors = $GLOBALS['db']->getRow($sql);
        $data = [];
        $records = 0;
        if(!empty($flavors)){
            $rows = json_decode($flavors['flavor']);
            $records = count($rows);

            foreach ($rows as $k => $v) {
                $data[$k]['id'] = $k+1;
                $data[$k]['mainId'] = $propertyTypeId;
                $data[$k]['name'] = urldecode($v->name);
                $data[$k]['price'] = $v->price;
            }
        }
        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records / $page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        if (count($flavors) > 0) {
            $return['dataList'] = $data;
        } else {
            $return['status'] = false;
            $return['resMsg'] = "查无结果！";
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 做法删除
     */
    public function dish_cookingway_ajax_del()
    {
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['propertyId'];//主id
        $key = intval($_REQUEST['key']) - 1;

        if ($id > 0) {
            $sql = "select * from fanwe_dc_supplier_taste where id=".$id;
            $row = $GLOBALS['db']->getRow($sql);
            if ($row['shops'] != 'null' && !empty($row['shops'])) {
                $return['success'] = false;
                $return['message'] = "操作失败，该分类已经关联商品";
            } else {
                $rows = json_decode($row['flavor']);
                foreach ($rows as $k=>$item) {
                    if($k != $key){
                        $arr[]=array("name"=>$item->name,"price"=>$item->price);

                    }
                }
                $row['flavor'] = json_encode($arr);

                $res = $GLOBALS['db']->autoExecute("fanwe_dc_supplier_taste",$row,'UPDATE',"id=".$id);
                if ($res) {
                    $return['success'] = true;
                    $return['records'] = count($arr);
                    $return['message'] = "操作成功";
                } else {
                    $return['success'] = false;
                    $return['message'] = "操作失败";
                }
            }
            echo json_encode($return);
            exit;

        }
    }

    /**
     * 新增做法校验
     */
    public function checkMoreThan10Type(){
        $return['success'] = true;
        $return['message'] = "操作成功";
        echo json_encode($return);
        exit;
    }
    /**
     * 做法名称校验
     */
    public function checkCookingWayName(){
        $return['success'] = true;
        $return['message'] = "操作成功";
        echo json_encode($return);
        exit;
    }

    /**
     * 新增保存做法分类
     */
    public function cookingWayTypeSaveOrUpdate(){
        init_app_page();
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $location_id = $account_info['slid'];

        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $switchbox = intval($_REQUEST['way']);
        $id = intval($_REQUEST['id']);

        /*业务逻辑部分*/
        $root['status'] = 0;
        $root['info'] = "";


        if(empty($id)&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_taste where name='".$name."' and location_id = ".$location_id)){
            $return['success'] = false;
            $return['message'] = "做法分类名称重复";
        }


        $data = array();
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['switchbox'] = $switchbox;

        $data['is_effect'] = 1;
        $data['location_id'] = $location_id;

        if($id>0){
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$data,"UPDATE","id=".$id)){
                $return['success'] = true;
                $return['message'] = "修改成功";
            }else{
                $return['success'] = false;
                $return['message'] = "修改失败";
            }
        }else{
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$data)){
                $return['success'] = true;
                $return['message'] = "添加成功";

            }else{
                $return['success'] = false;
                $return['message'] = "添加失败";
            }
        }

        echo json_encode($return);
        exit;
    }

    /**
     * 禁用做法分类
     */
//    public function lockCookingWay(){
//        init_app_page();
//        /*初始化*/
//        $account_info = $GLOBALS['account_info'];
//        $location_id = $account_info['slid'];
//        $id = intval($_REQUEST['id']);
//        $data = array();
//        $data['is_effect'] = 0;
//        $data['id'] = $id;
//
//        if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$data,"UPDATE","id=".$id)){
//            $return['success'] = true;
//            $return['message'] = "修改成功";
//        }else{
//            $return['success'] = false;
//            $return['message'] = "修改失败";
//        }
//    }

    /**
     * 启用禁用做法分类
     */
    public function lockOrUnlockCookingWayType(){
        init_app_page();
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $location_id = $account_info['slid'];
        $id = intval($_REQUEST['id']);
        $isEffect = intval($_REQUEST['enabledFlag']);
        $data = array();
        $data['is_effect'] = $isEffect;
        $data['id'] = $id;
        if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$data,"UPDATE","id=".$id)){
            $return['success'] = true;
            $return['message'] = "修改成功";
        }else{
            $return['success'] = false;
            $return['message'] = "修改失败";
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 删除做法分类
     */
    public function propertyGroupDelete(){
        init_app_page();
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $location_id = $account_info['slid'];
        $id = intval($_REQUEST['propertyTypeId']);
        $sql = "select * from fanwe_dc_supplier_taste where id=".$id;
        $r = $GLOBALS['db']->getRow($sql);
        if(empty($r['flavor']) || $r['flavor'] == 'null' && empty($r['shops']) || $r['shops'] == 'null'){
            if ($GLOBALS['db']->query("delete from  fanwe_dc_supplier_taste where id=".$id)){
                $return['success'] = true;
                $return['message'] = "删除成功";
            }else{
                $return['success'] = false;
                $return['message'] = "删除失败";
            }
        }else
        {
            $return['success'] = false;
            $return['message'] = "做法分类下有做法，删除失败";
        }
        echo json_encode($return);
        exit;
    }

    public function cookingWaySaveOrUpdate(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $location_id = $account_info['slid'];
        $id = intval($_REQUEST['id']);
        $name = trim($_REQUEST['name']);
        $price = floatval($_REQUEST['reprice']);
        $sql = "select * from fanwe_dc_supplier_taste where id=".$id;
        $row = $GLOBALS['db']->getRow($sql);

        if(!empty($row)){
            $flavor = json_decode($row['flavor']);
            $arr = [];
            $strs=array("name"=>$name,"price"=>$price);
            foreach ($flavor as $k=>$item) {
                    $arr[]=array("name"=>$item->name,"price"=>$item->price);
            }
            array_push($arr,$strs);
            $row['flavor'] = json_encode($arr);
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_taste",$row,"UPDATE","id=".$id)){
                $return['success'] = true;
                $return['message'] = "修改成功";
            }else{
                $return['success'] = false;
                $return['message'] = "修改失败";
            }

        }else{
            $return['success'] = false;
            $return['message'] = "分类不存在";
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 单位查询列表
     */
    public function dish_unit_query_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows'] ? $_REQUEST['rows'] : 20;
        $page = intval($_REQUEST['page']);

        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $where = "where location_id=$slid";

        $sql = "select * from fanwe_dc_supplier_unit_cate $where limit $limit";
        $sql2 = "select id from fanwe_dc_supplier_unit_cate $where";

        $rows = $GLOBALS['db']->getAll($sql);
        $records = count($GLOBALS['db']->getAll($sql2));

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records / $page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        if ($records > 0) {
            $return['dataList'] = $rows;
        } else {
            $return['status'] = false;
            $return['resMsg'] = "查无结果！";
        }
        echo json_encode($return);
        exit;
    }

//    public function dish_unit_checkUsed(){
//        init_app_page();
//        $return['status'] = true;
//        $return['resMsg'] = "操作成功";
//        echo json_encode($return);
//        exit;
//
//    }

    /**
     * 单位删除功能
     */
    public function dish_unit_checkUsed(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = intval($_REQUEST['id']);
        $sql = "select * from fanwe_dc_supplier_unit_cate where id=".$id;
        $row = $GLOBALS['db']->getRow($sql);

        if(!empty($row)){
            if ($GLOBALS['db']->query("delete from fanwe_dc_supplier_unit_cate where id=".$id)){
                $return['success'] = true;
                $return['message'] = "删除成功";
            }else{
                $return['success'] = false;
                $return['message'] = "删除失败";
            }

        }else{
            $return['success'] = false;
            $return['message'] = "单位不存在";
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 单位锁定功能
     */
    public function dish_unit_lock(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = intval($_REQUEST['id']);
        $sql = "select * from fanwe_dc_supplier_unit_cate where id=".$id;
        $row = $GLOBALS['db']->getRow($sql);


        if(!empty($row)){
            if($row['is_effect']){
                $row['is_effect'] = 0;
            }else{
                $row['is_effect'] = 1;
            }
            if ($GLOBALS['db']->autoExecute("fanwe_dc_supplier_unit_cate",$row,"update","id=".$id)){
                $return['success'] = true;
                $return['message'] = "操作成功";
            }else{
                $return['success'] = false;
                $return['message'] = "操作失败";
            }

        }else{
            $return['success'] = false;
            $return['message'] = "单位不存在";
        }
        echo json_encode($return);
        exit;
    }

    public function do_save_unit_cate_ajax(){
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*活出参数*/
        $location_id = $account_info['slid'];
        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $is_effect = intval($_REQUEST['is_effect']);
        $id = intval($_REQUEST['id']);




        $data = array();
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['is_effect'] = $is_effect;
        $data['supplier_id'] = $supplier_id;
        $data['location_id'] = $location_id;
        if($id > 0){
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_unit_cate",$data,"update","id=".$id)){
                $return['success'] = true;
                $return['message'] = "修改成功";
            }else{
                $return['success'] = false;
                $return['message'] = "修改失败";
            }
        }else{
            /*业务逻辑部分*/

            if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_unit_cate where name='".$name."' and location_id = ".$location_id)){
                $return['success'] = false;
                $return['message'] = "单位名称重复";
                echo json_encode($return);
                exit;
            }
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_unit_cate",$data)){
                $return['success'] = true;
                $return['message'] = "添加成功";
            }else{
                $return['success'] = false;
                $return['message'] = "添加失败";
            }
        }

        echo json_encode($return);
        exit;

    }

    public function dish_category_list_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['page_pageSize'] ? $_REQUEST['page_pageSize'] : 20;
        $page = intval($_REQUEST['page_currentPage']);
        $parentId = intval($_REQUEST['parentId']);

        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $rows = parent::goods_category_two_ajax($parentId,$limit);
        $rows2 = parent::goods_category_two_ajax($parentId);

        $records = count($rows2);
        $data = [];
        foreach ($rows as $k=>$row) {
            $data[$k]['id'] = $row['id'];
            $data[$k]['typeCode'] = $row['id'];
            $data[$k]['brandIdenty'] = $row['id'];
            $data[$k]['name'] = $row['name'];
            $data[$k]['parentId'] = $row['wcategory'];
            $data[$k]['sort'] = $row['sort'];
            $data[$k]['statusFlag'] = $row['is_effect'];
            $data[$k]['enabledFlag'] = $row['is_effect'];

        }
//var_dump($rows)
        $return['brandId'] = $parentId;
        $return['currentPage'] = $page;
        $return['pageSize'] = $page_size;
        $return['startRow'] = 0;
        $return['subRows'] = 0;
        $return['totalPage'] = ceil($records / $page_size);
        $return['totalRows'] = $records;
        if ($records > 0) {
//            $return['items'] = $rows;
            $return['items'] = $data;
        } else {
            $return['items'] = [];
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 新增大类
     */
    public function dish_category_type_add_ajax(){
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*活出参数*/
        $location_id = $account_info['slid'];
        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $is_effect = 1;
        $id = intval($_REQUEST['id']);


        $data = array();
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['wcategory'] = 0;
        $data['pid'] = 0;
        $data['wlevel'] = 0;
        $data['is_effect'] = $is_effect;
        $data['supplier_id'] = $supplier_id;
        $data['location_id'] = $location_id;
        if($id > 0){
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",$data,"update","id=".$id)){
                $return['success'] = true;
                $return['message'] = "修改成功";
            }else{
                $return['success'] = false;
                $return['message'] = "修改失败";
            }
        }else{
            /*业务逻辑部分*/
//var_dump($data);die;
            if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_menu_cate where name='".$name."' and location_id = ".$location_id)){
                $return['success'] = false;
                $return['message'] = "大类名称重复";
                echo json_encode($return);
                exit;
            }
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",$data)){
                $return['success'] = true;
                $return['message'] = "添加成功";
            }else{
                $return['success'] = false;
                $return['message'] = "添加失败";
            }
        }

        echo json_encode($return);
        exit;

    }

    /**
     * 新增中类
     */
    public function dish_category_add_ajax(){
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*活出参数*/
        $location_id = $account_info['slid'];
        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $is_effect = 1;
        $id = intval($_REQUEST['id']);
        $parentId = intval($_REQUEST['parentId']);

        $data = array();
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['wcategory'] = $parentId;
        $data['pid'] = $parentId;
        $data['wlevel'] = 1;
        $data['is_effect'] = $is_effect;
        $data['supplier_id'] = $supplier_id;
        $data['location_id'] = $location_id;
        if($id > 0){
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",$data,"update","id=".$id)){
                $return['success'] = true;
                $return['message'] = "修改成功";
            }else{
                $return['success'] = false;
                $return['message'] = "修改失败";
            }
        }else{
            /*业务逻辑部分*/
//var_dump($data);die;
            if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_menu_cate where name='".$name."' and location_id = ".$location_id)){
                $return['success'] = false;
                $return['message'] = "中类名称重复";
                echo json_encode($return);
                exit;
            }
            if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",$data)){
                $return['success'] = true;
                $return['message'] = "添加成功";
            }else{
                $return['success'] = false;
                $return['message'] = "添加失败";
            }
        }

        echo json_encode($return);
        exit;

    }

    /**
     * 删除大类
     */
    public function dish_category_deleteType(){
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*活出参数*/
        $location_id = $account_info['slid'];
        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $is_effect = 1;
        $id = intval($_REQUEST['id']);


        $data = array();
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['wcategory'] = 0;
        $data['pid'] = 0;
        $data['wlevel'] = 0;
        $data['is_effect'] = $is_effect;
        $data['supplier_id'] = $supplier_id;
        $data['location_id'] = $location_id;
        if($id > 0){
            $row = parent::goods_category_two_ajax($id);
            if(count($row) > 0){
                $return['success'] = false;
                $return['message'] = "大类下有中类，删除失败";

            }else{

                if ($GLOBALS['db']->query("delete from  fanwe_dc_supplier_menu_cate where id=".$id)){
                    $return['success'] = true;
                    $return['message'] = "删除成功";
                }else{
                    $return['success'] = false;
                    $return['message'] = "删除失败";
                }
            }


        }else{

            $return['success'] = false;
            $return['message'] = "删除失败";
        }

        echo json_encode($return);
        exit;

    }

    /**
     * 删除中类
     */
    public function dish_category_delete(){
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*活出参数*/
        $location_id = $account_info['slid'];
        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $is_effect = 1;
        $id = intval($_REQUEST['id']);



        if($id > 0){
            $row = parent::goods_category_one_ajax($id);
            if ($GLOBALS['db']->query("delete from  fanwe_dc_supplier_menu_cate where id=".$id)){
                $return['success'] = true;
                $return['message'] = "删除成功";
            }else{
                $return['success'] = false;
                $return['message'] = "删除失败";
            }
        }else{

            $return['success'] = false;
            $return['message'] = "删除失败";
        }

        echo json_encode($return);
        exit;

    }

    public function dish_category_checkBeforeDelete(){
        $return['success'] = true;
        $return['type'] = "inclass";

        echo json_encode($return);
        exit;
    }

    public function dish_queryDishes(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id'] ? intval($_REQUEST['id']) : $account_info['slid'];
        $page_size = $_REQUEST['rows'] ? $_REQUEST['rows'] : 20;
        $page = intval($_REQUEST['page']);
        $wmTypes = $_REQUEST['wmType'];
        $warehouseId = $_REQUEST['warehouseId'];

        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $where = "where  g.location_id=$slid";
//        $where .=" and g.is_effect = 0";//是否显示在终端
//        $where .= " and g.is_stock = 1 ";//是否是库存商品
//        $where .=" and g.is_delete = 1";//是否删除

        //库存商品
        $where .= " and (( g.is_effect = 1 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1))";

        if ($wmTypes > -1) {
            $where .= " and g.print in (" . $wmTypes . ")";//筛选库存类型
        } else {
            $where .= " and g.print >= 1 and g.print <=3";//库存类型不等于现制商品
        }


        if ($_REQUEST['skuTypeId']) {
            $where .= " and g.cate_id=" . $_REQUEST['skuTypeId'];
        }
        if ($_REQUEST['skuCodeOrName']) {
            $where .= " and (g.name like '%" . $_REQUEST['skuCodeOrName'] . "%'";
            $where .= " or g.barcode like '%" . $_REQUEST['skuCodeOrName'] . "%'";
            $where .= " or g.id like '%" . $_REQUEST['skuCodeOrName'] . "%' or g.pinyin like '%" . $_REQUEST['skuCodeOrName'] . "%' )";
        }

//        var_dump($where);
        $sqlcount = "select count(id) from fanwe_dc_menu g $where";
        $records = $GLOBALS['db']->getOne($sqlcount);
        $sql = "select *,g.id as mmid,g.name as skuName,g.barcode as skuCode,g.unit as uom,g.funit,g.times,g.price,g.pinyin,g.cate_id as skuTypeId,c.name as skuTypeName,g.stock as inventoryQty,g.is_effect as status from fanwe_dc_menu g  LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id $where  order by g.id desc limit $limit";
        $check = $GLOBALS['db']->getAll($sql);
//var_dump($check);
        $data = [];
        foreach ($check as $key => $item) {
            $sql2 = "select * from fanwe_cangku_menu where cid=" . $warehouseId . " and mid=" . $item['mmid'];
            $result = $GLOBALS['db']->getRow($sql2);
            if ($item['print'] != 3) {
                $price = $item['buyPrice'];
            } else {
                $price = $item['price'];
            }
            $data[$key]['id'] = $item['mmid'];
            $data[$key]['name'] = $item['skuName'];
            $data[$key]['dishCode'] = $item['skuCode'];
            $data[$key]['unit'] = $item['uom'];
            $data[$key]['wmType'] = $item['print'];
            $data[$key]['funit'] = $item['funit'];
            $data[$key]['times'] = $item['times'];
            $data[$key]['price'] = $item['price'];
            $data[$key]['logo'] = empty($item['image']) ? null : $item['image'];
            $data[$key]['pinyin'] = $item['pinyin'];
            $data[$key]['reckonPrice'] = $price;
            $data[$key]['reckonPriceStr'] = $price;
            $data[$key]['skuTypeId'] = $item['skuTypeId'];
            $data[$key]['yieldRateStr'] = $item['chupinliu'];
            $data[$key]['inClassName'] = $item['skuTypeName'];
            $data[$key]['inventoryQty'] = empty($result) ? 0 : $result['mstock'];
            $data[$key]['is_effect'] =$item['status'];


        }
        //$table =  $check=$GLOBALS['db']->getAll("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='fanwe_cangku_diaobo' ");print_r($table);exit;

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records / $page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        if ($check) {
            $return['dataList'] = $data;
        } else {
            $return['status'] = false;
            $return['resMsg'] = "查无结果！";
        }
        echo json_encode($return);
        exit;
    }

    public function queryDishAndAttribute(){
        $id = $_REQUEST['op'];
        $attr = parent::get_supplier_cate_unit_row();
        $dish = parent::getDcMenuInfoByMid($id);
//var_dump($dish);
        $dishPropertyType =[];
        $dishAndAttribute =[];
        foreach ($attr as $k => $v) {
            $dishPropertyType[$k]['id'] = $v['id'];
            $dishPropertyType[$k]['name'] = $v['name'];
            if(!empty($id)){
                if($dish['unit'] == $v['name']){
                    $dishAndAttribute['id'] = $v['id'];
                    $dishAndAttribute['name'] = $v['name'];

                }
            }
        }


        $dishPropertyTypes = json_encode($dishPropertyType);
        $dishAndAttributes = json_encode($dishAndAttribute);
        echo '{"dishPropertyTypes":[{"serverCreateTime":"2016-12-02 14:15:58","serverUpdateTime":"2017-05-11 18:48:41","creatorId":99999999,"creatorName":"admin","updatorId":1,"updatorName":"","statusFlag":1,"id":1,"name":"默认","aliasName":"默认","propertyKind":4,"sort":1000,"brandIdenty":1,"enabledFlag":1,"dishPropertys":[],"dishProperties":'.$dishPropertyTypes.'}],"dishAndAttributes":'.$dishAndAttributes.'}';
        exit;
    }

    public function queryRevelanceSetting(){
        $id = $_REQUEST['op'];
        $dishExtends = parent::getDcMenuExtendsByMid($id);
//        var_dump($GLOBALS['db']->getAll("show COLUMNs from fanwe_goods_extends"));
        $revel = parent::get_supplier_cate_tag_row();
        $labelCount = [];
        foreach ($revel as $k => $v) {
            $labelCount[$k]['id'] = $v['id'];
            $labelCount[$k]['name'] = $v['name'];
            if(!empty($id)){
                if(!empty($dishExtends)){
                    if(strpos($dishExtends['mtags'],$v['name']) > 0){
                        $cookingWayTypesAndCount[$k]['isChecked'] = true;
                    }else{
                        $cookingWayTypesAndCount[$k]['isChecked'] = false;
                    }
                }else{
                    $cookingWayTypesAndCount[$k]['isChecked'] = false;
                }
            }
        }
        $type1 = parent::get_supplier_cate_row();
        $cookingWayTypesAndCount = [];
        foreach ($type1 as $k2 => $v2) {
            $cookingWayTypesAndCount[$k2]['id'] = $v2['id'];
            $cookingWayTypesAndCount[$k2]['name'] = $v2['name'];
            $type2 = json_decode($v2['flavor']);
            $dish = [];
            foreach ($type2 as $k3 => $v3) {
                $dish[$k3]['id'] = $v3->id;
                $dish[$k3]['name'] = urldecode($v3->name);
                $dish[$k3]['reprice'] = $v3->price;
                if(!empty($id)){
                    if(!empty($dishExtends)){
                        if(strpos($dishExtends['mdishs'],$v3['name']) > 0){
                            $cookingWayTypesAndCount[$k2]['isChecked'] = true;
                        }else{
                            $cookingWayTypesAndCount[$k2]['isChecked'] = false;
                        }
                    }else{
                        $cookingWayTypesAndCount[$k2]['isChecked'] = false;
                    }
                }

            }
            $cookingWayTypesAndCount[$k2]['dishProperties'] = $dish;
            $cookingWayTypesAndCount[$k2]['count'] = count($dish);
            $cookingWayTypesAndCount[$k2]['checkedCount'] = 0;
            $cookingWayTypesAndCount[$k2]['isCheckedAll'] = false;

        }
        $labelC = count($labelCount);
        $labelCounts = json_encode($labelCount);
        $cookingWayTypesAndCounts = json_encode($cookingWayTypesAndCount);
        echo '{"condimentCount":{"dishId":null,"brandIdenty":12566,"propertyKind":null,"type":null,"childDishType":null,"id":null,"name":null,"count":15,"checkedCount":0,"statusFlag":null,"dishProperties":null,"condiments":[{"serverCreateTime":"2016-12-23 17:46:14","serverUpdateTime":"2017-05-10 18:38:53","creatorId":88889037781,"creatorName":"刘静","updatorId":88889037781,"updatorName":"刘静","statusFlag":1,"id":1870307,"dishTypeId":null,"dishCode":null,"type":2,"name":"珍珠","aliasName":"珍珠11","shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":1.00,"weight":null,"sort":10,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"0aa6d8fa61ff41d6bd4e91f946cd42c9","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2016-12-23 17:46:23","serverUpdateTime":"2016-12-23 17:46:23","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"id":1870309,"dishTypeId":null,"dishCode":null,"type":2,"name":"红豆","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":1.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"ca934c2a3d7345789f573918c3d4f636","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2016-12-23 17:46:32","serverUpdateTime":"2016-12-23 17:46:32","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"id":1870311,"dishTypeId":null,"dishCode":null,"type":2,"name":"寒天","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":1.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"12525f9f99654dd99fa93aa9437c5349","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2016-12-23 17:46:42","serverUpdateTime":"2016-12-23 17:46:42","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"id":1870312,"dishTypeId":null,"dishCode":null,"type":2,"name":"爱玉","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":1.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"b5deeaa651b146adb929460ac1389bad","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2016-12-23 17:46:50","serverUpdateTime":"2016-12-23 17:46:50","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"id":1870314,"dishTypeId":null,"dishCode":null,"type":2,"name":"燕","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":1.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"3b9f87e5734f4209a77b731a35ce459c","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-04-08 15:23:14","serverUpdateTime":"2017-04-08 15:23:14","creatorId":88888977520,"creatorName":"蔡骏","updatorId":88888977520,"updatorName":"蔡骏","statusFlag":1,"id":2708930,"dishTypeId":null,"dishCode":null,"type":2,"name":"加鸡蛋","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":1.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"81aa3850b8bc4ab4814ec71804e9ede6","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-04-12 16:38:10","serverUpdateTime":"2017-04-12 16:38:10","creatorId":88888977520,"creatorName":"蔡骏","updatorId":88888977520,"updatorName":"蔡骏","statusFlag":1,"id":2750317,"dishTypeId":null,"dishCode":null,"type":2,"name":"可乐","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":6.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"6a0532de22944e7cbe95802a679772ca","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-11 18:43:49","serverUpdateTime":"2017-05-11 18:43:49","creatorId":88889037781,"creatorName":"刘静","updatorId":88889037781,"updatorName":"刘静","statusFlag":1,"id":3124183,"dishTypeId":null,"dishCode":null,"type":2,"name":"芝士","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":4.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"922e2fc6041843aea34103846e0f5db3","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-23 20:33:45","serverUpdateTime":"2017-05-23 20:33:45","creatorId":88889044869,"creatorName":"邱志云","updatorId":88889044869,"updatorName":"邱志云","statusFlag":1,"id":3263014,"dishTypeId":null,"dishCode":null,"type":2,"name":"巴旦木","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":3.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"6aed207fa7a6488a87d909a4ce72d87f","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-23 20:34:03","serverUpdateTime":"2017-05-23 20:34:03","creatorId":88889044869,"creatorName":"邱志云","updatorId":88889044869,"updatorName":"邱志云","statusFlag":1,"id":3263016,"dishTypeId":null,"dishCode":null,"type":2,"name":"奥利奥碎","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":3.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"b350588091544ddaab063da77d033508","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-23 20:34:19","serverUpdateTime":"2017-05-23 20:34:19","creatorId":88889044869,"creatorName":"邱志云","updatorId":88889044869,"updatorName":"邱志云","statusFlag":1,"id":3263020,"dishTypeId":null,"dishCode":null,"type":2,"name":"华夫脆","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":5.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"c0ed6f74a5eb4198b5b55a794b82ccbf","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-27 18:05:06","serverUpdateTime":"2017-05-27 18:05:06","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"id":3316458,"dishTypeId":null,"dishCode":null,"type":2,"name":"芸豆","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":5.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"6c665d11692d45d39842327fb5a12abd","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-28 07:09:51","serverUpdateTime":"2017-05-28 07:09:51","creatorId":88889044869,"creatorName":"邱志云","updatorId":88889044869,"updatorName":"邱志云","statusFlag":1,"id":3319662,"dishTypeId":null,"dishCode":null,"type":2,"name":"鸡蛋","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":2.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"6c26d9ea2cce4c6fac9304848fbf3235","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null},{"serverCreateTime":"2017-05-30 14:05:22","serverUpdateTime":"2017-05-30 14:05:22","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"id":3345141,"dishTypeId":null,"dishCode":null,"type":2,"name":"椰果","aliasName":null,"shortName":null,"aliasShortName":null,"dishNameIndex":null,"barcode":null,"unitId":null,"marketPrice":2.00,"weight":null,"sort":1000,"brandIdenty":12566,"dishDesc":null,"videoUrl":null,"wmType":null,"saleType":1,"dishIncreaseUnit":1.0,"isSingle":1,"isDiscountAll":1,"isSendOutside":1,"isChangePrice":null,"isOrder":null,"stepNum":1.0,"minNum":0,"maxNum":0,"uuid":"74522f76010e49b6850191b4c77b55c0","enabledFlag":1,"skuKey":null,"hasStandard":null,"dishQty":1,"boxQty":1,"templatesOther":null,"imageUrl":null,"imageSize":null,"unitName":null,"imageName":null,"imageSuffixes":null,"ruleName":null,"source":null,"propertyNames":null,"isChecked":null}],"isCheckedAll":false},"labelCount":{"dishId":null,"brandIdenty":12566,"propertyKind":null,"type":null,"childDishType":null,"id":null,"name":null,"count":'.$labelC.',"checkedCount":0,"statusFlag":null,"dishProperties":'.$labelCounts.',"condiments":null,"isCheckedAll":false},"memoCount":{"dishId":null,"brandIdenty":12566,"propertyKind":null,"type":null,"childDishType":null,"id":null,"name":null,"count":8,"checkedCount":0,"statusFlag":null,"dishProperties":[{"serverCreateTime":"2016-12-23 17:47:12","serverUpdateTime":"2016-12-23 17:47:12","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":143972,"propertyTypeId":null,"propertyKind":3,"name":"正常冰","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"fec1fef292314de381ec0b42a6a5c661","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2016-12-23 17:47:21","serverUpdateTime":"2016-12-23 17:47:21","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":143973,"propertyTypeId":null,"propertyKind":3,"name":"少冰","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"11d1bc4ab5244342a12cbc0422f2428f","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2016-12-23 17:48:06","serverUpdateTime":"2016-12-23 17:48:06","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":143974,"propertyTypeId":null,"propertyKind":3,"name":"半冰","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"329d07ff497c41499e086fb29feaad31","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2016-12-23 17:48:14","serverUpdateTime":"2016-12-23 17:48:14","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":143975,"propertyTypeId":null,"propertyKind":3,"name":"微冰","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"bfd4b4ba30944eada372ec112d058778","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2017-01-04 11:41:05","serverUpdateTime":"2017-01-04 11:41:05","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":153333,"propertyTypeId":null,"propertyKind":3,"name":"蒜香","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"067789ead7904724acfa24d581c07cea","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2017-01-04 11:41:26","serverUpdateTime":"2017-01-04 11:41:26","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":153334,"propertyTypeId":null,"propertyKind":3,"name":"黄油焗","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"c00d2cadb01f4a4092f41d89a63749c8","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2017-01-04 11:41:36","serverUpdateTime":"2017-01-04 11:41:36","creatorId":88888981819,"creatorName":"admin","updatorId":88888981819,"updatorName":"admin","statusFlag":1,"dishBrandPropertyId":null,"id":153335,"propertyTypeId":null,"propertyKind":3,"name":"粉丝蒜蓉","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"3b5f85bb78dc476d963cb42943211dd7","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2},{"serverCreateTime":"2017-05-11 18:45:06","serverUpdateTime":"2017-05-11 18:45:06","creatorId":88889037781,"creatorName":"刘静","updatorId":88889037781,"updatorName":"刘静","statusFlag":1,"dishBrandPropertyId":null,"id":232144,"propertyTypeId":null,"propertyKind":3,"name":"多冰","ruleTypeName":null,"aliasName":"","reprice":0.0,"sort":1000,"brandIdenty":12566,"uuid":"034ac610f547404593aed2a30213e6ee","isCure":2,"enabledFlag":1,"dishId":null,"baseName":null,"isChecked":null,"isDefault":2}],"condiments":null,"isCheckedAll":false},"cookingWayTypesAndCount":'.$cookingWayTypesAndCounts.'}';
        exit;
    }

    public function queryDishTypes(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $id = $_REQUEST['dishId'];
        $dish = parent::getDcMenuInfoByMid($id);
        //分类
        $sortconditions = " where is_effect = 1 and  wlevel<4 and supplier_id = " . $supplier_id; // 查询条件
        $sortconditions .= " and location_id=" . $slid;
        $sqlsort = " select id,name,is_effect,sort,wcategory,wcategory as pid,wlevel from " . DB_PREFIX . "dc_supplier_menu_cate ";
        $sqlsort .= $sortconditions . " order by sort desc";

        $wmenulist = $GLOBALS['db']->getAll($sqlsort);

//        $listsort = toFormatTree($wmenulist, "name");

        $data = [];
        $num = -1;
        foreach ($wmenulist as $k => $v) {
            if($v['wlevel'] == 0){
                $num++;

                $data[$num]['serverCreateTime'] = time();
                $data[$num]['serverUpdateTime'] = time();
                $data[$num]['creatorId'] = '';
                $data[$num]['creatorName'] = '';
                $data[$num]['updatorId'] = '';
                $data[$num]['updatorName'] = '';
                $data[$num]['statusFlag'] = 1;
                $data[$num]['id'] =  $v['id'];
                $data[$num]['parentId'] =  $v['wcategory'];
                $data[$num]['typeCode'] =  $v['id'];
                $data[$num]['name'] = $v['name'];
                $data[$num]['aliasName'] = $v['name'];
                $data[$num]['sort'] =  $v['sort'];
                $data[$num]['dishTypeDesc'] = '';
                $data[$num]['isOrder'] = 1;
                $data[$num]['uuid'] = time();
                $data[$num]['brandIdenty'] =  $v['id'];
                $data[$num]['isCure'] = 0;
                $data[$num]['enabledFlag'] = 1;

                $sql2 = "select * from fanwe_dc_supplier_menu_cate where wcategory=".$v['id'];
                $sG = $GLOBALS['db']->getAll($sql2);
                $data2=[];
                $data[$num]['middleDishBrandTypes']=[];
                foreach ($sG as $k2 => $v2) {
                    $data2[$k2]['serverCreateTime'] = time();
                    $data2[$k2]['serverUpdateTime'] = time();
                    $data2[$k2]['creatorId'] = '';
                    $data2[$k2]['creatorName'] = '';
                    $data2[$k2]['updatorId'] = '';
                    $data2[$k2]['updatorName'] = '';
                    $data2[$k2]['statusFlag'] = 1;
                    $data2[$k2]['id'] =  $v2['id'];
                    $data2[$k2]['parentId'] =  $v2['pid'];
                    $data2[$k2]['typeCode'] =  $v2['id'];
                    $data2[$k2]['name'] = $v2['name'];
                    $data2[$k2]['aliasName'] = $v2['name'];
                    $data2[$k2]['sort'] =  $v2['sort'];
                    $data2[$k2]['dishTypeDesc'] = $v2['sort'];
                    $data2[$k2]['isOrder'] = 1;
                    $data2[$k2]['uuid'] = time();
                    $data2[$k2]['brandIdenty'] =  $v2['id'];
                    $data2[$k2]['isCure'] = 0;
                    $data2[$k2]['enabledFlag'] = 1;
                    if($dish['cate_id'] == $v2['id']){
                        $data2[$k2]['isChecked'] =  true;
                        $data[$num]['isChecked'] = true;
                    }else{
                        $data2[$k2]['isChecked'] =  false;
                    }

                    $data[$num]['middleDishBrandTypes'][$k2] = $data2[$k2];
                }
            }

        }

        echo json_encode($data);
        exit;
    }

    public function uptokenStr(){
        $upToken = $this->get_update_token();
        echo '{"uptoken":"'.$upToken.'"}';

//        echo '{"uptoken":"gHckET3ezRBakrmBQVQRb8SLVHbQYlna1MnLQelM:f3HcPPdFvDRVhs75z8_kbg1xOhU=:eyJzY29wZSI6ImtyeS1vZmZpY2lhbCIsImRlYWRsaW5lIjoxNDk2ODIyNTIwfQ=="}';
        exit;
    }

    /**
     * 标签管理列表
     */
    public function dish_tag_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $account_info['slid'];
        $page_size = $_REQUEST['rows'] ? $_REQUEST['rows'] : 20;
        $page = intval($_REQUEST['page']);
        $name = trim($_REQUEST['name']);

        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $where = "where location_id=$slid";
        if($name){
            $where .= " and name like '%$name%'";
        }
        $sql = "select * from fanwe_dish_goods_tag $where limit $limit";
        $sql2 = "select id from fanwe_dish_goods_tag $where";

        $rows = $GLOBALS['db']->getAll($sql);
        $records = count($GLOBALS['db']->getAll($sql2));

        $return['page'] = $page;
        $return['records'] = $records;
        $return['total'] = ceil($records / $page_size);
        $return['status'] = true;
        $return['resMsg'] = null;
        if ($records > 0) {
            $return['dataList'] = $rows;
        } else {
            $return['status'] = false;
            $return['resMsg'] = "查无结果！";
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 新增标签
     */
    public function dish_tag_add_ajax(){
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*活出参数*/
        $location_id = $account_info['slid'];
        $name = strim($_REQUEST['name']);
        $sort = intval($_REQUEST['sort']);
        $is_effect = intval($_REQUEST['isDisable']);
        $id = intval($_REQUEST['id']);




        $data = array();
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['is_effect'] = $is_effect;
        $data['location_id'] = $location_id;
        if($id > 0){
            if ($GLOBALS['db']->autoExecute("fanwe_dish_goods_tag",$data,"update","id=".$id)){
                $return['success'] = true;
                $return['message'] = "修改成功";
            }else{
                $return['success'] = false;
                $return['message'] = "修改失败";
            }
        }else{
            /*业务逻辑部分*/

            if($GLOBALS['db']->getOne("select count(*) from fanwe_dish_goods_tag where name='".$name."' and location_id = ".$location_id)){
                $return['success'] = false;
                $return['message'] = "标签名称重复";
                echo json_encode($return);
                exit;
            }
            if ($GLOBALS['db']->autoExecute("fanwe_dish_goods_tag",$data)){
                $return['success'] = true;
                $return['message'] = "添加成功";
            }else{
                $return['success'] = false;
                $return['message'] = "添加失败";
            }
        }

        echo json_encode($return);
        exit;
    }

    /**
     * 标签删除功能
     */
    public function dish_tag_checkUsed(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = intval($_REQUEST['id']);
        $sql = "select * from fanwe_dish_goods_tag where id=".$id;
        $row = $GLOBALS['db']->getRow($sql);

        if(!empty($row)){
            if ($GLOBALS['db']->query("delete from fanwe_dish_goods_tag where id=".$id)){
                $return['success'] = true;
                $return['message'] = "删除成功";
            }else{
                $return['success'] = false;
                $return['message'] = "删除失败";
            }

        }else{
            $return['success'] = false;
            $return['message'] = "单位不存在";
        }
        echo json_encode($return);
        exit;
    }

    /**
     * 标签锁定功能
     */
    public function dish_tag_lock(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = intval($_REQUEST['id']);
        $sql = "select * from fanwe_dish_goods_tag where id=".$id;
        $row = $GLOBALS['db']->getRow($sql);


        if(!empty($row)){
            if($row['is_effect']){
                $row['is_effect'] = 0;
            }else{
                $row['is_effect'] = 1;
            }
            if ($GLOBALS['db']->autoExecute("fanwe_dish_goods_tag",$row,"update","id=".$id)){
                $return['success'] = true;
                $return['message'] = "操作成功";
            }else{
                $return['success'] = false;
                $return['message'] = "操作失败";
            }

        }else{
            $return['success'] = false;
            $return['message'] = "单位不存在";
        }
        echo json_encode($return);
        exit;
    }

    //common function
    public function get_hanzi()
    {
        $pinyin = new pinyin();
        if ($_REQUEST['name']) {
            $result = $pinyin->pinyin1($_REQUEST['name']);
            $data['firstName'] = $result;
            echo json_encode($data);
            exit;
        }
    }

//{dishTypeId: "3112", dishCode: "", name: "国防观", barcode: "23123", dishNameIndex: "GFG",…}
//attributes
//:
//[{id: "143977", propertyKindId: "4", propertyTypeId: "19548"}]
//0
//:
//{id: "143977", propertyKindId: "4", propertyTypeId: "19548"}
//id
//:
//"143977"
//propertyKindId
//:
//"4"
//propertyTypeId
//:
//"19548"
//barcode
//:
//"23123"
//boxQty
//:
//"1"
//condiments
//:
//[]
//cookingWays
//:
//[]
//deleteImage
//:
//1
//dishCode
//:
//""
//dishDesc
//:
//"12312"
//dishIncreaseUnit
//:
//""
//dishNameIndex
//:
//"GFG"
//dishQty
//:
//"1"
//dishTypeId
//:
//"3112"
//imageName
//:
//"preview.jpg"
//imageSize
//:
//"5004"
//imageSuffixes
//:
//".jpg"
//imageUrl
//:
//"http://or63agv72.bkt.clouddn.com/o_1bi44l0u01o201bqj12hnf6l16i38.jpg?imageMogr2/auto-orient/strip/gravity/NorthWest/quality/90/thumbnail/960x720!"
//isChangePrice
//:
//2
//isDiscountAll
//:
//"1"
//isOrder
//:
//"1"
//isSendOutside
//:
//"1"
//isSingle
//:
//"1"
//labels
//:
//[]
//marketPrice
//:
//"1"
//memos
//:
//[]
//name
//:
//"国防观"
//richDesc
//:
//"<p>3123</p>"
//saleType
//:
//2
//sort
//:
//""
//stepNum
//:
//""
//templates
//:
//[{id: "1"}]
//0
//:
//{id: "1"}
//id
//:
//"1"
//unitId
//:
//"67729"
//wmType
//:
//"2"
    public function saveOrUpdateDishBrand(){
        init_app_page();
        /* 基本参数初始化 */
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];

        /*获取参数*/
        $id = intval($_REQUEST['op']);

        $input = file_get_contents('php://input');
        $object = json_decode($input);
        $name = $object->name;
        $sname = $object->sname;
        $dishDesc = $object->dishDesc;
        $imageUrl = $object->imageUrl;
        $dishTypeId = $object->dishTypeId;
        $unitId = $object->unitId;
        $barcode = $object->barcode;
        $richDesc = $object->richDesc;
        $saleType = $object->saleType;
        $marketPrice = $object->marketPrice;
        $dishQty = $object->dishQty;
        $dishNameIndexx = $object->dishNameIndex;
        $wmType = $object->wmType;
        $sort = $object->sort;
//        $name = $object->name;
//        $name = $object->name;
        $tichengmoney = 0;
        $ticheng_style = 0;
        $times = 0;
        $ticheng_style = 0;
        $ticheng_style = 0;
        $ticheng_style = 0;


        $location_id = intval($account_info['slid']);
        $data['name'] = strim($name);

        //检查重名
        $sql="select count(id) from ".DB_PREFIX."dc_menu where location_id=".$location_id." and name='".$data['name']."'";
        $check = $GLOBALS['db']->getOne($sql);
        if($check>1){
            $root['success'] = 0;
            $root['message'] = "名称有重复！";
        }



        $data['fu_title'] = $sname;
        $data['m_desc'] = $richDesc;
        $data['cate_id'] = intval($dishTypeId);
//        $data['funit'] = $unitId;
//        $data['tichengmoney'] = $tichengmoney;
//        $data['ticheng_style'] = $ticheng_style;
//        $data['times'] =$times;
        $data['orderid'] = intval($sort);
        $data['is_effect_enable'] = 1;
        $data['is_stock'] = 0;
        $data['is_stock_enable'] = 0;
        $data['image'] = $imageUrl;
        $data['price'] = $marketPrice;
//        $data['tags'] = implode(",", $_REQUEST['tags']);
        $data['is_effect'] = 1;
        //2016.4.24 枫叶增加
//        $data['isdazhe'] = intval($_REQUEST['isdazhe']);

        $data['barcode'] = $barcode;
//        $data['buyPrice'] = floatval($_REQUEST['buyPrice']);
//
//        $data['customerPrice'] = floatval($_REQUEST['customerPrice']);
//        $data['sellPrice2'] = floatval($_REQUEST['sellPrice2']);
        $data['unit'] = $unitId;
        $data['pinyin'] = $dishNameIndexx;

//        $data['company'] = strim($_REQUEST['company']);
//        $data['productionDate'] = strim($_REQUEST['productionDate']);
//        $data['shelfLife'] = strim($_REQUEST['shelfLife']);
//        $data['maxStock'] = intval($_REQUEST['maxStock']);
//
//        $data['minStock'] = intval($_REQUEST['minStock']);
//        $data['biaoqian'] = strim($_REQUEST['biaoqian']);
        $data['print'] = $wmType;
        $data['info'] = $dishDesc;
        /* 业务逻辑部分 */
        if (!in_array($location_id, $account_info['location_ids'])){
            $root['status'] = 0;
            $root['info'] = "没有权限添加/修改该门店的菜单";
        }

        $location_info = $GLOBALS['db']->getRow("select xpoint,ypoint from ".DB_PREFIX."supplier_location where id=".$location_id);
        $data['location_id'] = $location_id;
        $data['supplier_id'] = $supplier_id;
        $data['xpoint'] = $location_info['xpoint'];
        $data['ypoint'] = $location_info['ypoint'];

        /*获取标签中文,同步函数*/

        if($data['cate_id']==0 || $data['print']=="" || $data['price']<0){
            $root['success'] = 0;
            $root['message'] = "注意红色为必填字段！";
            echo json_encode($root);
            exit;
        }
//        var_dump($data);
//        exit;
        if($id>0){
            $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data,"UPDATE","id=".$id);

            $data = [];
            //保存扩展信息
            $data['mid'] = $id;
            $data['start'] = $_REQUEST['dishIncreaseUnit'];
            $data['increase'] = $_REQUEST['stepNum'];;
            $data['box'] = $_REQUEST['boxQty'];;
            $data['boxs'] = $_REQUEST['dishQty'];;
            $data['is_one'] = $_REQUEST['isSingle'];;
            $data['is_cut'] = $_REQUEST['isDiscountAll'];;
            $data['is_send'] = $_REQUEST['isSendOutside'];;
            $data['is_reprice'] = $_REQUEST['isChangePrice'];;
            $data['is_dish'] = $_REQUEST['isOrder'];;
            $data['mtags'] = serialize($_REQUEST['labels']);
            $data['mdishs'] = serialize($_REQUEST['cookingWays']);

            $GLOBALS['db']->autoExecute(DB_PREFIX."goods_extends","update",$data,"id=".$id);

            syn_supplier_location_menu_match($id);
            $root['message'] = "修改成功";
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data);
            $id = $GLOBALS['db']->insert_id();

            $data = [];
            //保存扩展信息
            $data['mid'] = $id;
            $data['start'] = $_REQUEST['dishIncreaseUnit'];
            $data['increase'] = $_REQUEST['stepNum'];;
            $data['box'] = $_REQUEST['boxQty'];;
            $data['boxs'] = $_REQUEST['dishQty'];;
            $data['is_one'] = $_REQUEST['isSingle'];;
            $data['is_cut'] = $_REQUEST['isDiscountAll'];;
            $data['is_send'] = $_REQUEST['isSendOutside'];;
            $data['is_reprice'] = $_REQUEST['isChangePrice'];;
            $data['is_dish'] = $_REQUEST['isOrder'];;
            $data['mtags'] = serialize($_REQUEST['labels']);
            $data['mdishs'] = serialize($_REQUEST['cookingWays']);

            $GLOBALS['db']->autoExecute(DB_PREFIX."goods_extends",$data);


            syn_supplier_location_menu_match($id);
            $root['message'] = "添加成功";
        }

        if($data['is_effect']==1) {
            $this->caipinpush($location_id);
        }

        $root['success'] = 1;

        echo json_encode($root);
        exit;
    }

    public function checkBarcode(){
        echo "true";
        exit;
    }

    /**
     * 锁定菜单
     */
    public function clock_dish_ajax(){
        init_app_page();
        /*初始化*/
        $account_info = $GLOBALS['account_info'];
        $location_id = $account_info['slid'];
        $id = intval($_REQUEST['id']);
        $sql = "select * from fanwe_dc_menu where id=".$id;
        $r = $GLOBALS['db']->getRow($sql);
        if($r['is_effect'] == 1){
            $r['is_effect'] = 0;
        }else{
            $r['is_effect'] = 1;
        }
        if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$r,"UPDATE","id=".$id)){
            $return['success'] = true;
            $return['message'] = "操作成功";
        }else{
            $return['success'] = false;
            $return['message'] = "修改失败";
        }
        $sql = "select * from fanwe_dc_menu where id=".$id;
        echo json_encode($return);
        exit;
    }

    /**
     * 删除菜单
     */
    public function delete_dish_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $id = intval($_REQUEST['id']);
        $sql = "select * from fanwe_dc_menu where id=".$id;
        $row = $GLOBALS['db']->getRow($sql);


        if(!empty($row)){
            if($row['buy_count'] > 0){
                $return['success'] = false;
                $return['message'] = "商品已经卖出无法删除";
            }else{
                if ($GLOBALS['db']->query("delete from fanwe_dc_menu where id=".$id)){
                    $return['success'] = true;
                    $return['message'] = "删除成功";
                }else{
                    $return['success'] = false;
                    $return['message'] = "删除失败";
                }
            }

        }else{
            $return['success'] = false;
            $return['message'] = "商品不存在";
        }
        echo json_encode($return);
        exit;
    }

    function caipinpush($location_id){


        require_once APP_ROOT_PATH."openApi/gl/hy_tool.class.php";

        $list=$GLOBALS['db']->getAll("select appid from fanwe_app where slid='$location_id' order by loginTime desc ");
        foreach($list as $kc=>$vc){
            $channelIdlist[]=$vc['appid'];
        }

        $type = "cmd";


        $description=array('code'=>'1001');
        $message = array (
            'title' => '提示',
            'description' =>'有菜品更新' ,
            'custom_content'=>$description
        );

        $ht = new HyTool ();
        $ht->sendMessage ($channelIdlist,$type,$message);
        $ht->sendMessage_NEW ($channelIdlist,$type,$message);


    }
}