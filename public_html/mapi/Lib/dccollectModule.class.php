<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class dccollectModule extends MainBaseModule
{

	/**
	 *
	 * 会员中心收藏列表页
	 *
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_dcorder&r_type=2&page=1
	 * 输入：
	 * page:int 当前的页数，没输入些参数时，默认为第一页
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * collect_list:array:array, 收藏列表，结构如下,其中link_id为收藏表中的收藏id,删除时，传过来这个id
	 * [collect_list] => Array
        (
            [0] => Array
                (
                    [id] => 36
                    [name] => 豆花新语（博美斯邦店）
                    [preview] => ./public/attachment/201507/14/18/55a4de37349f8.jpg
                    [avg_point] => 4.5000
                    [dp_count] => 2
                    [add_time] => 1437336338
                    [link_id] => 2
                    [url] => /dco2o/index.php?ctl=dcbuy&lid=36
                )

            [1] => Array
                (
                    [id] => 44
                    [name] => 惠文便利店
                    [preview] => ./public/attachment/201507/15/14/55a6000300f4d.jpg
                    [avg_point] => 5.0000
                    [dp_count] => 0
                    [add_time] => 1437336337
                    [link_id] => 1
                    [url] => /dco2o/index.php?ctl=dcbuy&lid=44
                )

        )

	 
	 */
	
	public function index()
	{
		global_run();
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$page = intval($GLOBALS['request']['page']);

			if($page==0){
				$page = 1;
			}	
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
	
			$user_id = $GLOBALS['user_info']['id'];
			require_once APP_ROOT_PATH."system/model/dc.php";
			//获取会员收藏店铺列表
			$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
			$list=$GLOBALS['db']->getAll("select sl.id ,sl.name,sl.preview,sl.avg_point,sl.dp_count ,sl.is_dc , sl.dc_buy_count, sl.is_reserve ,  sc.add_time,sc.id as link_id from ".DB_PREFIX."supplier_location as sl left join ".DB_PREFIX."dc_location_sc as sc on sl.id= sc.location_id where sc.user_id=".$user_id." and (sl.is_dc=1 or sl.is_reserve=1) order by sc.add_time desc limit ".$limit);
			$total=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where user_id=".$user_id);
			$idarr=array();
			foreach($list as $k=>$v){
				$list[$k]['url']=url('index','dcdetail',array('lid'=>$v['id']));
				$idarr[]=$v['id'];
				$list[$k]['preview']=get_abs_img_root(get_spec_image($v['preview'],140,85,1));
			}
			
			$page_total = ceil($total/$page_size);
			$page_title='收藏店铺';
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
			$root['page_title']=$page_title;
			$root['collect_list']=$list;
			output($root);
		}	

	}
	
	
	
	
	/**
	 * 	 会员中心删除收藏
	 * 
	 * 	  输入：
	 *  id:int 配送地址id

	 *  
	 *  输出：
	 * user_login_status:[int]   0表示未登录   1表示已登录
	 * info:[string] 未登录状态的提示信息，已登录时无此项
	 * status:[int] 删除的结果  1表示成功   0表示失败

   
	 */	
	public function del()
	{
		$root = array();		
			
		$user_data = $GLOBALS['user_info'];		
		$user_id = intval($user_data['id']);
		$id = intval($GLOBALS['request']['id']);
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){			
			$root['user_login_status'] = $user_login_status;	
		}else{
			$root['user_login_status'] = 1;
			$root['status']=0;
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_location_sc where id=".$id);
			if($GLOBALS['db']->affected_rows() >0)
			{
				$root['status']=1;
			}			
		}	
		output($root);		
	}	
	
	

}
?>