<?php
require_once 'core/page.php';

class ajaxSettingsModule extends KizBaseModule
{
    function __construct()
    {
        parent::__construct();
        global_run();

    }

    /**
     * 做法管理列表功能
     */
    public function dish_cookingway_ajax(){
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $slid = $_REQUEST['id'] ? intval($_REQUEST['id']) : $account_info['slid'];
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
}