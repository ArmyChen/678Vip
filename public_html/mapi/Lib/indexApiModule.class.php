<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class indexApiModule extends MainBaseApiModule
{
	
	
	/**
	 * wap版首页接口
	 * 输入：
	 * 无
	 * 
	 * 输出：
	 * advs: array 首页广告
	 * 结构如下
	 * Array
       (
            [0] => Array
                (
                    [id] => 21 [int] 广告的ID
                    [name] => 商品明细 [string] 广告名称
                    [img] => http://localhost/o2onew/public/attachment/sjmapi/5451eb7862ae7.jpg [string] 广告图片 640x360
                    [data] => Array [array] 以key->value方式存储的内容 用于url参数组装
                        (
                            [url] => http://www 
                        )

                    [ctl] => url [string] 定义的ctl
                )
       )
       
       
     * advs2: array 首页中部广告
	 * 结构如下
	 * Array
       (
            [0] => Array
                (
                    [id] => 21 [int] 广告的ID
                    [name] => 商品明细 [string] 广告名称
                    [img] => http://localhost/o2onew/public/attachment/sjmapi/5451eb7862ae7.jpg [string] 广告图片 640x360
                    [data] => Array [array] 以key->value方式存储的内容 用于url参数组装
                        (
                            [url] => http://www 
                        )

                    [ctl] => url [string] 定义的ctl
                )
       )
     
      
	 * indexs: array 首页菜单
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 71 [int] 菜单ID
                    [name] => 9.9包邮 [string] 菜单名称
                    [icon_name] => [string] 菜单图标 
                    [color] => #39b778 [string] 菜单颜色
                    [data] => Array [array] 以key->value方式存储的内容 用于url参数组装
                        (
                            [cate_id] => 
                        )

                    [ctl] => tuan [string] 定义的ctl
               )
       )
	 * supplier_list:array 首页商家
	 * 结构如下
	 * Array
        (
            [0] => Array
                array 
                (
                  'preview' => 'http://192.168.3.148/fwshop/public/attachment/201601/26/14/56a712fcd906a_184x164.jpg' [string]  商家图片 92x82
                  'preview_v1' => 'http://192.168.3.148/fwshop/public/attachment/201601/26/14/56a712fcd906a_360x260.jpg' [string] 商家图片 180x130
                  'id' => '39'  [int] 商家编号
                  'is_verify' => '1' [int] 是否认证
                  'avg_point' => '3.7000' [float] 评均分
                  'address' => '嵊州市东南路68号' [string]  地址
                  'name' => '一心宾馆' [string]  名称
                  'distance' => ''  [float]  距离
                  'xpoint' => 120.82651 [float] 门店所在经度
                  'ypoint' => 29.587781 [float] 门店所在纬度
                  'tel' => '0591-3485246' [string] 电话
                  'dealcate_name' => NULL [string] 分类名称
                  'area_name' => NULL [string] 地区名称
                )
                
                
	$store['preview_v1']=get_abs_img_root(get_spec_image($v['preview'], 180, 130,1));
        )
	 * deal_list:array 首页团购
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 团购ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 团购名称
                    [sub_name] => 镜片代金券 [string] 团购短名称
                    [brief] => 【36店通用】明视眼镜 [string] 团购简介
                    [buy_count] => 1 [int] 销量
                    [current_price] => 75 [float] 现价
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 团购图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [auto_order] => 1 [int] 免预约 0:否 1:是
                    [is_lottery] => 1 [int] 是否抽奖 0:否 1:是
                    [distance]	=>	[float] 有地理定位时的离当前地的距离(米)
                    [xpoint] => [float] 团购所在经度
                    [ypoint] => [float] 团购所在纬度
                    [is_today] => [int] 是否为今日团购 0否 1是
                    [promotes_desc] [string] 优惠描述
                )
       )
	 * supplier_deal_list:array 首页商城商品
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 商品ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 商品名称
                    [sub_name] => 镜片代金券 [string] 商品短名称
                    [brief] => 【36店通用】明视眼镜 [string] 商品简介
                    [buy_count] => 1 [int] 销量
                    [current_price] => 75 [float] 现价
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 商品图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [is_refund] => 1 [int] 是否随时退 0:否 1:是
                )
       )
	 * event_list:array 活动列表
	 * 结构如下
	 * Array
       (
            [0] => Array
                (
                    [id] => 4 [int] 活动ID
                    [name] => 贵安温泉自驾游 [string] 活动名称
                    [icon] => http://localhost/o2onew/public/attachment/201502/26/14/54eec33c40e99_600x364.jpg [string] 活动图片 300x182
                    [submit_begin_time_format] => 2015-02-01 14:54:53 [string] 格式化活动报名开始时间
                    [submit_end_time_format] => 2020-02-26 14:54:55 [string] 格式化活动报名结束时间
                    [sheng_time_format] => 06天04小时50分 [string] 活动报名剩余时间
                )
       )
	 * youhui_list:array 优惠列表
	 * Array
        (
            [0] => Array
                (
                    [id] => 23 [int] 优惠券ID
                    [name] => 华莱士30元抵用券 [string] 优惠券名称
                    [list_brief] => 华莱士30元抵用券 [string] 优惠券列表简介
                    [icon] => http://localhost/o2onew/public/attachment/201502/26/11/54ee8fc5497f9_140x85.jpg [string] 优惠券图片 140x85
                    [down_count] => 4 [int] 下载量
                    [begin_time] => 2015-02-01至2020-02-26 [string] 时间
                )
       )
	 * page_title:string 页面标题
	 * mobile_btns_download:string 手机下载链接
	 * 
	 */
	public function wap()
	{
		global $is_app;
		$root = array();
		$root['return'] = 1;
		
		$city_id = $GLOBALS['city']['id'];
		$city_name =  $GLOBALS['city']['name'];
		
		$root['city_id'] = $city_id;
		$root['city_name'] = $city_name;
		$adv_list = $GLOBALS['cache']->get("WAP_INDEX_ADVS_".intval($city_id));
		
		//广告列表
		if($adv_list===false)
		{		
			if($is_app)
			{
				$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '0' and position=0 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
				$advs = $GLOBALS['db']->getAll($sql);
				if(empty($advs))
				{
					$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '1' and position=0 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
					$advs = $GLOBALS['db']->getAll($sql);
				}
			}			
			else
			{
				$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '1' and position=0 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
				$advs = $GLOBALS['db']->getAll($sql);
			}
				
				
			$adv_list = array();
			foreach($advs as $k=>$v)
			{
				$adv_list[$k]['id'] = $v['id'];
				$adv_list[$k]['name'] = $v['name'];
				$adv_list[$k]['img'] = get_abs_img_root(get_spec_image($v['img'], 750, 325,1));  //首页顶部广告图片规格为 宽: 750px 高: 325px
				$adv_list[$k]['type'] = $v['type'];
				$adv_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$adv_list[$k]['ctl'] = $v['ctl'];
			}
			$GLOBALS['cache']->set("WAP_INDEX_ADVS_".intval($city_id),$adv_list,300);
		}
		$root['advs'] = $adv_list?$adv_list:array();
		
        // 广告2
		$adv_list2 = $GLOBALS['cache']->get("WAP_INDEX_ADVS2_".intval($city_id));
		if($adv_list2===false)
		{
			if($is_app)
			{
				$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '0' and position=3 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
				$advs2 = $GLOBALS['db']->getAll($sql);
				if(empty($advs2))
				{
					$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '1' and position=3 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
					$advs2 = $GLOBALS['db']->getAll($sql);
				}
			}
			else
			{
				$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '1' and position=3 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
				$advs2 = $GLOBALS['db']->getAll($sql);
			}
		   
		    $adv_list2 = array();
		    foreach($advs2 as $k=>$v)
		    {
		        $adv_list2[$k]['id'] = $v['id'];
		        $adv_list2[$k]['name'] = $v['name'];
		        $adv_list2[$k]['img'] =  get_abs_img_root(get_spec_image($v['img'], 750, 140,1)); //首页中部广告图片规格为 宽: 750px 高: 140px
		        $adv_list2[$k]['type'] = $v['type'];
		        $adv_list2[$k]['data'] = $v['data'] = unserialize($v['data']);
		        $adv_list2[$k]['ctl'] = $v['ctl'];
		    }
		    $GLOBALS['cache']->set("WAP_INDEX_ADVS2_".intval($city_id), $adv_list2, 300);
		}
		$root['advs2'] = $adv_list2?$adv_list2:array();
		
		//$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
		//$root['get_domain'] = $domain;
		//return output($root);
		
		//首页菜单列表
		$indexs_list = $GLOBALS['cache']->get("WAP_INDEX_INDEX_".intval($city_id));
		if($indexs_list===false)
		{
			if($is_app)
			{
				$indexs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_index where status = 1 and mobile_type = 0 and city_id in (0,".intval($city_id).") order by sort asc");
				if(empty($indexs))
					$indexs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_index where status = 1 and mobile_type = 1 and city_id in (0,".intval($city_id).") order by sort asc");
			}
			else
			$indexs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_index where status = 1 and mobile_type = 1 and city_id in (0,".intval($city_id).") order by sort asc");
			$indexs_list = array();
			foreach($indexs as $k=>$v)
			{
				$indexs_list[$k]['id'] = $v['id'];
				$indexs_list[$k]['name'] = $v['name'];
				$indexs_list[$k]['icon_name'] = $v['vice_name'];//图标名 http://fontawesome.io/icon/bars/
				$indexs_list[$k]['color'] = $v['desc'];//颜色
				$indexs_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$indexs_list[$k]['ctl'] = $v['ctl'];
			}
				
				
			$GLOBALS['cache']->set("WAP_INDEX_INDEX_".intval($city_id),$indexs_list,300);
		}
		
		$root['indexs'] = $indexs_list?$indexs_list:array();
		
		
		
		
		//推荐商家
		if(!$GLOBALS['m_config']['close_index_supplier'])
		{
			$indexs_supplier = $GLOBALS['cache']->get("WAP_INDEX_SUPPLIER_".intval($city_id));
			
			if($indexs_supplier === false)
			{
				
				require_once APP_ROOT_PATH."system/model/supplier.php";
				
				$result = get_location_list(15,$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id,"supplier_id"=>0)," ", " is_recommend=1");
				
				$indexs_supplier_rs = $result['list'];
				foreach($indexs_supplier_rs as $k=>$v){
					$indexs_supplier[$k] = format_store_list_item($v);
				}
					
				$GLOBALS['cache']->set("WAP_INDEX_SUPPLIER_".intval($city_id),$indexs_supplier,300);
			}
		}
		$root['supplier_list'] = $indexs_supplier?$indexs_supplier:array();
		
		//推荐团购
		if(!$GLOBALS['m_config']['close_index_tuan'])
		{
			$indexs_deal = $GLOBALS['cache']->get("WAP_INDEX_DEAL_".intval($city_id));
			if($indexs_deal === false)
			{
				
				require_once APP_ROOT_PATH."system/model/deal.php";
				$result = get_deal_list(10,$type=array(DEAL_ONLINE),$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id),""," d.is_recommend = 1 and d.is_shop = 0 ");
				$indexs_deal_rs = $result['list'];     
			
				// 获取1个全站优惠
				$sql = " select id,description from ".DB_PREFIX."promote where type = '0'  order by id desc limit 0,1";
				$promotes = $GLOBALS['db']->getRow($sql);
				 
				
				$indexs_deal = array();
				foreach($indexs_deal_rs as $k=>$v){
					$indexs_deal[$k] = format_deal_list_item($v);
					if ($indexs_deal[$k]['allow_promote'] == 1) {
					    $indexs_deal[$k]['promotes_desc'] = $promotes['description'];
					}
				}
					
				$GLOBALS['cache']->set("WAP_INDEX_DEAL_".intval($city_id),$indexs_deal,300);
			}
		}
		$root['deal_list'] = $indexs_deal?$indexs_deal:array();
		
		//推荐商品
		if(!$GLOBALS['m_config']['close_index_shop'])
		{
			$indexs_supplier_deal = $GLOBALS['cache']->get("WAP_INDEX_SUPPLIER_DEAL_".intval($city_id));
			if($indexs_supplier_deal === false)
			{
	
				require_once APP_ROOT_PATH."system/model/deal.php";
				$result = get_goods_list(10,$type=array(DEAL_ONLINE,DEAL_NOTICE),$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0),""," d.is_recommend = 1 and d.buy_type <> 1 and d.is_shop = 1 ");
				$indexs_supplier_deal_rs = $result['list'];
					
				foreach($indexs_supplier_deal_rs as $k=>$v){
					$indexs_supplier_deal[$k]=format_deal_list_item($v);
				}
				$GLOBALS['cache']->set("WAP_INDEX_SUPPLIER_DEAL_".intval($city_id),$indexs_supplier_deal,300);
			}
		}
		$root['supplier_deal_list'] = $indexs_supplier_deal?$indexs_supplier_deal:array();
		
		
		// 推荐分类
		if(!$GLOBALS['m_config']['close_index_cate']){
		    $indexs_cate = $GLOBALS['cache']->get("WAP_INDEX_CATE_".intval($city_id));
		    if($indexs_cate === false){
		        require_once APP_ROOT_PATH."system/model/deal.php";
		        $result = get_cate_list('8');
		        foreach ($result['list'] as $k=>$v){
		            $result['list'][$k]['icon_img'] = get_abs_img_root(get_spec_image($v['icon_img'], 168, 130,1));
		        }
		        $indexs_cate = $result['list'];
		        $GLOBALS['cache']->set("WAP_INDEX_CATE_".intval($city_id), $indexs_cate, 300);
		    }
		}
		$root['cate_list'] = $indexs_cate?$indexs_cate:array();
		
		//推荐活动
		if(!$GLOBALS['m_config']['close_index_event'])
		{
			$indexs_event = $GLOBALS['cache']->get("WAP_INDEX_EVENT_".intval($city_id));
			if($indexs_event === false)
			{
				require_once APP_ROOT_PATH."system/model/event.php";
				$result = get_event_list(10,$type=array(EVENT_NOTICE,EVENT_ONLINE),$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id),""," is_recommend=1 ");
			
				$indexs_event_rs = $result['list'];
					
				foreach($indexs_event_rs as $k=>$v){
					$indexs_event[$k] = format_event_list_item($v);
				}
				$GLOBALS['cache']->set("WAP_INDEX_EVENT_".intval($city_id),$indexs_event,300);		
			}
		}
		$root['event_list'] = $indexs_event?$indexs_event:array();
		
		
		//推荐优惠券
		if(!$GLOBALS['m_config']['close_index_youhui'])
		{
			$youhui_list=$GLOBALS['cache']->get("WAP_YOUHUI_LIST_".intval($city_id));
			if($youhui_list === false){
	
				require_once APP_ROOT_PATH."system/model/youhui.php";
				$result = get_youhui_list(10,$type=array(YOUHUI_NOTICE,YOUHUI_ONLINE),$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id)," "," is_recommend = 1 ");
				$youhui_list_rs = $result['list'];
				
				$youhui_list = array();
				foreach($youhui_list_rs as $k=>$v){
					$youhui_list[$k] = format_youhui_list_item($v);
				}
			
				$GLOBALS['cache']->set("WAP_YOUHUI_LIST_".intval($city_id),$youhui_list,300);
			}
		}
		$root['youhui_list'] = $youhui_list?$youhui_list:array();
		
		//推荐位
		$root['zt_html'] = load_zt();
		
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="首页";
		$root['mobile_btns_download'] = url("index","app_download");
		
		return woutput($root);
	}
	
	
	/**
	 * app版首页接口
	 * 输入：
	 * 无
	 * 
	 * 输出：
	 * advs: array 首页广告
	 * 结构如下
	 * Array
       (
            [0] => Array
                (
                    [id] => 21 [int] 广告的ID
                    [type]	=>	[int] 广告类型
                    [name] => 商品明细 [string] 广告名称
                    [img] => http://localhost/o2onew/public/attachment/sjmapi/5451eb7862ae7.jpg [string] 广告图片 640x360
                    [data] => Array [array] 以key->value方式存储的内容 用于url参数组装
                        (
                            [url] => http://www 
                        )

                    [ctl] => url [string] 定义的ctl
                )
       )
	 * indexs: array 首页菜单
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 71 [int] 菜单ID
                    [type]	=>	[int] 广告类型
                    [name] => 9.9包邮 [string] 菜单名称
                    [icon_name] => [string] 菜单图标 
                    [color] => #39b778 [string] 菜单颜色
                    [img] => [string]菜单图 160x160
                    [data] => Array [array] 以key->value方式存储的内容 用于url参数组装
                        (
                            [cate_id] => 
                        )

                    [ctl] => tuan [string] 定义的ctl
               )
       )
	 * supplier_list:array 首页商家
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [preview] => http://localhost/o2onew/public/attachment/201502/25/14/54ed67b2cd14b_194x118.jpg [string] 商家图片 194x118
                    [id] => 21 [int] 商家编号
                    [name] => 桥亭活鱼小镇（万象城店） [string] 商家名称
                )
        )
	 * deal_list:array 首页团购
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 团购ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 团购名称
                    [sub_name] => 镜片代金券 [string] 团购短名称
                    [brief] => 【36店通用】明视眼镜 [string] 团购简介
                    [buy_count] => 1 [int] 销量
                    [current_price] => 75 [float] 现价
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 团购图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [auto_order] => 1 [int] 免预约 0:否 1:是
                    [is_lottery] => 1 [int] 是否抽奖 0:否 1:是
                    [distance]	=>	[float] 有地理定位时的离当前地的距离(米)
                    [xpoint] => [float] 团购所在经度
                    [ypoint] => [float] 团购所在纬度
                    [is_today] => [int] 是否为今日团购 0否 1是
                )
       )
	 * supplier_deal_list:array 首页商城商品
	 * 结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 商品ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 商品名称
                    [sub_name] => 镜片代金券 [string] 商品短名称
                    [brief] => 【36店通用】明视眼镜 [string] 商品简介
                    [buy_count] => 1 [int] 销量
                    [current_price] => 75 [float] 现价
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 商品图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [is_refund] => 1 [int] 是否随时退 0:否 1:是
                )
       )
	 * event_list:array 活动列表
	 * 结构如下
	 * Array
       (
            [0] => Array
                (
                    [id] => 4 [int] 活动ID
                    [name] => 贵安温泉自驾游 [string] 活动名称
                    [icon] => http://localhost/o2onew/public/attachment/201502/26/14/54eec33c40e99_300x182.jpg [string] 活动图片 300x182
                    [submit_begin_time_format] => 2015-02-01 14:54:53 [string] 格式化活动报名开始时间
                    [submit_end_time_format] => 2020-02-26 14:54:55 [string] 格式化活动报名结束时间
                    [sheng_time_format] => 06天04小时50分 [string] 活动报名剩余时间
                )
       )
	 * youhui_list:array 优惠列表
	 * Array
        (
            [0] => Array
                (
                    [id] => 23 [int] 优惠券ID
                    [name] => 华莱士30元抵用券 [string] 优惠券名称
                    [list_brief] => 华莱士30元抵用券 [string] 优惠券列表简介
                    [icon] => http://localhost/o2onew/public/attachment/201502/26/11/54ee8fc5497f9_140x85.jpg [string] 优惠券图片 140x85
                    [down_count] => 4 [int] 下载量
                    [begin_time] => 2015-02-01至2020-02-26 [string] 时间
                )
       )
	 * page_title:string 页面标题
	 * mobile_btns_download:string 手机下载链接
	 * 
	 */
	public function index()
	{
		
		$root = array();
		$root['return'] = 1;
	
		$city_id = $GLOBALS['city']['id'];
		$city_name =  $GLOBALS['city']['name'];
	
		$root['city_id'] = $city_id;
		$root['city_name'] = $city_name;
		$adv_list = $GLOBALS['cache']->get("MOBILE_INDEX_ADVS_".intval($city_id));
	
		//广告列表
		if($adv_list===false)
		{
			$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '0' and  position=0 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
			$advs = $GLOBALS['db']->getAll($sql);
	
	
			$adv_list = array();
			foreach($advs as $k=>$v)
			{
				$adv_list[$k]['id'] = $v['id'];
				$adv_list[$k]['name'] = $v['name'];
				$adv_list[$k]['img'] = get_abs_img_root($v['img']);  //首页广告图片规格为 宽: 640px 高: 240px
				$adv_list[$k]['type'] = $v['type'];
				$adv_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$adv_list[$k]['ctl'] = $v['ctl'];
			}
			$GLOBALS['cache']->set("MOBILE_INDEX_ADVS_".intval($city_id),$adv_list,300);
		}
		$root['advs'] = $adv_list?$adv_list:array();

		//首页菜单列表
		$indexs_list = $GLOBALS['cache']->get("MOBILE_INDEX_INDEX_".intval($city_id));
		if($indexs_list===false)
		{
			$indexs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_index where status = 1 and mobile_type = 0 and city_id in (0,".intval($city_id).") order by sort asc");
			$indexs_list = array();
			foreach($indexs as $k=>$v)
			{
				$indexs_list[$k]['id'] = $v['id'];
				$indexs_list[$k]['name'] = $v['name'];
				$indexs_list[$k]['img'] = get_abs_img_root($v['img']);  //菜单图 160x160
				$indexs_list[$k]['icon_name'] = $v['vice_name'];//图标名 http://fontawesome.io/icon/bars/
				$indexs_list[$k]['color'] = $v['desc'];//颜色
				$indexs_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$indexs_list[$k]['ctl'] = $v['ctl'];
				$indexs_list[$k]['type'] = $v['type'];
			}
	
	
			$GLOBALS['cache']->set("MOBILE_INDEX_INDEX_".intval($city_id),$indexs_list,300);
		}
	
		$root['indexs'] = $indexs_list?$indexs_list:array();
	
	
	
	
		//推荐商家
		if(!$GLOBALS['m_config']['close_index_supplier'])
		{
			$indexs_supplier = $GLOBALS['cache']->get("WAP_INDEX_SUPPLIER_".intval($city_id));
		
			if($indexs_supplier === false)
			{
					
				require_once APP_ROOT_PATH."system/model/supplier.php";
					
				$result = get_location_list(3,$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id,"supplier_id"=>0)," ", " is_recommend=1");
					
				$indexs_supplier_rs = $result['list'];
				foreach($indexs_supplier_rs as $k=>$v){
					$indexs_supplier[$k] = format_store_list_item($v);
				}
		
				$GLOBALS['cache']->set("WAP_INDEX_SUPPLIER_".intval($city_id),$indexs_supplier,300);
			}
		}
		$root['supplier_list'] = $indexs_supplier?$indexs_supplier:array();
	
		//推荐团购
		if(!$GLOBALS['m_config']['close_index_tuan'])
		{
			$indexs_deal = $GLOBALS['cache']->get("WAP_INDEX_DEAL_".intval($city_id));
			if($indexs_deal === false)
			{
					
				require_once APP_ROOT_PATH."system/model/deal.php";
				$result = get_deal_list(10,$type=array(DEAL_ONLINE,DEAL_NOTICE),$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id),""," d.is_recommend = 1 and d.buy_type <> 1 and d.is_shop = 0 ");
				$indexs_deal_rs = $result['list'];
		
				$indexs_deal = array();
				foreach($indexs_deal_rs as $k=>$v){
					$indexs_deal[$k] = format_deal_list_item($v);
				}
		
				$GLOBALS['cache']->set("WAP_INDEX_DEAL_".intval($city_id),$indexs_deal,300);
			}
		}
		$root['deal_list'] = $indexs_deal?$indexs_deal:array();
	
		//推荐商品
		if(!$GLOBALS['m_config']['close_index_shop'])
		{
			$indexs_supplier_deal = $GLOBALS['cache']->get("WAP_INDEX_SUPPLIER_DEAL_".intval($city_id));
			if($indexs_supplier_deal === false)
			{
		
				require_once APP_ROOT_PATH."system/model/deal.php";
				$result = get_goods_list(10,$type=array(DEAL_ONLINE,DEAL_NOTICE),$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0),""," d.is_recommend = 1 and d.buy_type <> 1 and d.is_shop = 1 ");
				$indexs_supplier_deal_rs = $result['list'];
		
				foreach($indexs_supplier_deal_rs as $k=>$v){
					$indexs_supplier_deal[$k]=format_deal_list_item($v);
				}
				$GLOBALS['cache']->set("WAP_INDEX_SUPPLIER_DEAL_".intval($city_id),$indexs_supplier_deal,300);
			}
		}
		$root['supplier_deal_list'] = $indexs_supplier_deal?$indexs_supplier_deal:array();
	
	
	
		//推荐活动
		if(!$GLOBALS['m_config']['close_index_event'])
		{
			$indexs_event = $GLOBALS['cache']->get("WAP_INDEX_EVENT_".intval($city_id));
			if($indexs_event === false)
			{
				require_once APP_ROOT_PATH."system/model/event.php";
				$result = get_event_list(10,$type=array(EVENT_NOTICE,EVENT_ONLINE),$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id),""," is_recommend=1 ");
		
				$indexs_event_rs = $result['list'];
		
				foreach($indexs_event_rs as $k=>$v){
					$indexs_event[$k] = format_event_list_item($v);
				}
				$GLOBALS['cache']->set("WAP_INDEX_EVENT_".intval($city_id),$indexs_event,300);
			}
		}
		$root['event_list'] = $indexs_event?$indexs_event:array();
	
	
		//推荐优惠券
		if(!$GLOBALS['m_config']['close_index_youhui'])
		{
			$youhui_list=$GLOBALS['cache']->get("WAP_YOUHUI_LIST_".intval($city_id));
			if($youhui_list === false){
		
				require_once APP_ROOT_PATH."system/model/youhui.php";
				$result = get_youhui_list(10,$type=array(YOUHUI_NOTICE,YOUHUI_ONLINE),$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>$city_id)," "," is_recommend = 1 ");
				$youhui_list_rs = $result['list'];
					
				$youhui_list = array();
				foreach($youhui_list_rs as $k=>$v){
					$youhui_list[$k] = format_youhui_list_item($v);
				}
		
				$GLOBALS['cache']->set("WAP_YOUHUI_LIST_".intval($city_id),$youhui_list,300);
			}
		}
		$root['youhui_list'] = $youhui_list?$youhui_list:array();
	
	
		//推荐位
		$root['zt_html'] = load_zt();
	
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="首页";
		
		
		return woutput($root);
	}
	
	
	/**
	 * wap版首页全部菜单接口
	 * 输入：
	 * 无
	 *
	 * 输出：	
	 * indexs: array 首页菜单
	 * 结构如下
	 * Array
	 (
		 [0] => Array
		 (
			 [id] => 71 [int] 菜单ID
			 [name] => 9.9包邮 [string] 菜单名称
			 [icon_name] => [string] 菜单图标
			 [color] => #39b778 [string] 菜单颜色
			 [data] => Array [array] 以key->value方式存储的内容 用于url参数组装
			 (
				 [cate_id] =>
			 )
			
			 [ctl] => tuan [string] 定义的ctl
		 )
	 )
	 * 
	 * page_title:string 页面标题
	 *
	 */	
	
	
	public function more()
	{
		$root = array();
		$root['return'] = 1;
		
		$city_id = $GLOBALS['city']['id'];
		$city_name =  $GLOBALS['city']['name'];
		
		
		
		//首页菜单列表
		$indexs_list = $GLOBALS['cache']->get("WAP_INDEX_INDEX_ALL_".intval($city_id));
		if($indexs_list===false)
		{
			$indexs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_index where status = 1 and mobile_type = 1 and city_id in (0,".intval($city_id).") order by sort asc");
			$indexs_list = array();
			foreach($indexs as $k=>$v)
			{
				$indexs_list[$k]['id'] = $v['id'];
				$indexs_list[$k]['name'] = $v['name'];
				$indexs_list[$k]['icon_name'] = $v['vice_name'];//图标名 http://fontawesome.io/icon/bars/
				$indexs_list[$k]['color'] = $v['desc'];//颜色
				$indexs_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$indexs_list[$k]['ctl'] = $v['ctl'];
			}
				
				
			$GLOBALS['cache']->set("WAP_INDEX_INDEX_ALL_".intval($city_id),$indexs_list,300);
		}
		
		$root['indexs'] = $indexs_list?$indexs_list:array();
		
		
		
		
		$root['page_title'] = $GLOBALS['m_config']['program_title'];
		return woutput($root);
	}
	
	
}
?>