<?php
require_once 'core/pinyin.php';
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
        $wmTypes = $_REQUEST['wmTypes'];
        $warehouseId = $_REQUEST['warehouseId'];

        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $where = "where  g.location_id=$slid";

        //库存商品
        $where .= " and (( g.is_effect = 0 and g.is_stock = 1 and g.is_delete = 1) or (g.is_delete = 1))";

        if (!empty($wmTypes)) {
            $where .= " and g.print in (" . $wmTypes . ")";//筛选库存类型
        } else {
            $where .= " and g.print <> 1";//库存类型不等于现制商品
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
        $sql = "select *,g.id as mmid,g.name as skuName,g.barcode as skuCode,g.unit as uom,g.funit,g.times,g.price,g.pinyin,g.cate_id as skuTypeId,c.name as skuTypeName,g.stock as inventoryQty from fanwe_dc_menu g  LEFT join fanwe_dc_supplier_menu_cate c on c.id=g.cate_id $where limit $limit";
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
            $data[$key]['skuName'] = $item['skuName'];
            $data[$key]['skuCode'] = $item['skuCode'];
            $data[$key]['uom'] = $item['uom'];
            $data[$key]['wmType'] = $item['print'];
            $data[$key]['funit'] = $item['funit'];
            $data[$key]['times'] = $item['times'];
            $data[$key]['price'] = $item['price'];
            $data[$key]['pinyin'] = $item['pinyin'];
            $data[$key]['reckonPrice'] = $price;
            $data[$key]['reckonPriceStr'] = $price;
            $data[$key]['skuTypeId'] = $item['skuTypeId'];
            $data[$key]['yieldRateStr'] = $item['chupinliu'];
            $data[$key]['skuTypeName'] = $item['skuTypeName'];
            $data[$key]['inventoryQty'] = empty($result) ? 0 : $result['mstock'];


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
}