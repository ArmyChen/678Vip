<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class uc_fxModule extends MainBaseModule
{
    
    
    /**
     * 我的分销接口
     * 输入：
     * page [int] 分页
     * 
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * 

        [user_data] => Array  ：array 用户头部数据
        (
            [user_name] => fanwe    ：string 用户名
            [fx_money] => 10        ：float  总佣金
            [user_avatar] => http://localhost/o2onew/public/avatar/000/00/00/71virtual_avatar_big.jpg       ：string 用户头像    页面上限定 85*85
            [share_mall_qrcode] => http://localhost/o2onew/public/images/qrcode/c8/8a32058ff72bd95e5cb1c7c74e5a80d3.png      ：string 我的小店  二维码图片
            [share_mall_url] =>
            [fx_mall_bg] => http://localhost/o2onew/public/attachment/201506/16/15/nofxm23fs_740x300.jpg    ：string 我的分销背景图片    360*150
        )

        [item] => Array ：array 我分销的商品数据列表
            (
                [0] => Array
                    (
                        [id] => 95  
                        [name] => 0元抽奖
                        [sub_name] => 0元抽奖
                        [icon] => http://localhost/o2onew/public/attachment/201504/03/16/551e4a76556ad_170x170.jpg      :string 商品图片 85*85
                        [current_price] => 0    ：float  当前价格
                        [sale_count] => 10      ：int  销量
                        [sale_total] => 1   ：float    总销售额
                        [sale_balance] => 0  ：float  分销商品获得的总佣金
                        [share_url] => http://localhost/o2onew/index.php?ctl=deal&act=95&r=NzE%3D   ：string 分享的商品连接
                        [ud_is_effect] => 1 ：int 是否上架 1 上架   ，0 下架
                        [ud_type] => 0  ：int 分销商品类型  0用户领取，1为系统分配
                        [end_status] => 0   ：int 商品 结束类型
                    )

     *
     * */
    public function my_fx(){
		$root = array();		
		/*参数初始化*/
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);			

		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){
		    $root['user_login_status'] = $user_login_status;	
		}
		else
		{
			$root['user_login_status'] = $user_login_status;
			$GLOBALS['ref_uid'] = $user_id;
			
			
			//返回会员信息
			$user_data = array();
			$user_data['user_name'] = $user['user_name'];
			$user_data['fx_money'] = round($user['fx_money'],2);
			$user_data['user_avatar'] = get_abs_img_root(get_muser_avatar($user_id,"big"))?get_abs_img_root(get_muser_avatar($user_id,"big")):"";
			$user_data['share_mall_qrcode'] = get_abs_img_root(gen_qrcode(SITE_DOMAIN.wap_url("index","uc_fx#mall",array("r"=>base64_encode($user_id)))));
			$user_data['share_mall_url'] = SITE_DOMAIN.wap_url("index","uc_fx#mall",array("r"=>base64_encode($user_id)));
				
			$user_data['fx_mall_bg'] = $user['fx_mall_bg']?get_abs_img_root(get_spec_image($user['fx_mall_bg'],320,150,1)):SITE_DOMAIN.APP_ROOT."/mapi/image/nofxmallbg.jpg";
			
			$root['user_data'] = $user_data;
			
			
			$u_level = array();
			if($user['fx_level']>0){
			    //取出等级信息
			    $u_level = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."fx_level where id = ".$user['fx_level']);
			
			    //独立出用户等级数据
			    $salarys = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where  fx_level=0 and level_id=".$u_level['id']);
			}
			$u_level = $u_level?array_merge($u_level,$salarys):array();
			
			
			//默认佣金
			$default_salary = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where level_id = 0 and fx_level=0");
			if (empty($u_level)){  //无等级的情况下取默认
			    $u_level = $default_salary;
			}
			
			
			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
    		require_once APP_ROOT_PATH.'system/model/deal.php';
    	
    		$join = " left join ".DB_PREFIX."user_deal as ud on d.id = ud.deal_id ";
            $ext_condition = " d.buy_type <> 1  and d.is_fx = 2 and ud.user_id = ".$user_id;
            $sort_field = " ud.is_effect desc,ud.add_time desc ";
            $append_field = " ,ud.sale_count,ud.sale_total,ud.sale_balance,ud.is_effect as ud_is_effect,ud.type as ud_type ";
            $deal_result  = get_deal_list($limit,array(DEAL_ONLINE,DEAL_HISTORY,DEAL_NOTICE),array(),$join,$ext_condition,$sort_field,$append_field);
    		$deal_list = $deal_result['list'];
    		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d ".$join." where ".$deal_result['condition'],false);
	

			$page_total = ceil($count/$page_size);
			//end 分页
			
			

			
		//获取ID
        foreach ($deal_list as $k=>$v){
            $ids[] = $v['id'];
        }

        //佣金比率
        $deal_salarys = $GLOBALS['db']->getAll('select * from '.DB_PREFIX.'deal_fx_salary where deal_id in('.implode(',', $ids).') and fx_level=0 ');
		foreach ($deal_salarys as $k=>$v){
            if ($v['fx_salary']==0){    //如果商品没设置 分销佣金比率，默认为用户当前的
                $v['fx_salary'] = $u_level['fx_salary'];
                $v['fx_salary_type'] = $u_level['fx_salary_type'];
            }else{
                $v['fx_salary'] = $v['fx_salary'];
            }
            
            $f_deal_salarys[$v['deal_id']] = $v;
        }
        	
        //要返回的字段
        $data = array();
		foreach ($deal_list as $K=>$v){
		    $temp_data['id'] = $v['id'];
		    $temp_data['name'] = msubstr($v['name'],0,40);
		    $temp_data['sub_name'] = $v['sub_name'];
		    $temp_data['icon'] = get_abs_img_root(get_spec_image($v['icon'],85,85,1));
		    $temp_data['current_price'] = round($v['current_price'],2);
		    
		    $deal_salary = $f_deal_salarys[$v['id']]['fx_salary'];
		    $fx_salary_type = $f_deal_salarys[$v['id']]['fx_salary_type'];
		    
// 		    $temp_data['fx_salary'] = $fx_salary_type?round($deal_salary*100,2):round($deal_salary,2);
// 		    $temp_data['fx_salary_type'] = $fx_salary_type;
// 		    $temp_data['fx_salary_money'] = $fx_salary_type?round($deal_salary*$temp_data['current_price'],2):round($deal_salary,2);
		    
		    //用户获得的佣金信息
		    $temp_data['sale_count'] = $v['sale_count'];
		    $temp_data['sale_total'] = round($v['sale_total'],2);
		    $temp_data['sale_balance'] = round($v['sale_balance'],2);
		    
		    $temp_data['share_url'] = SITE_DOMAIN.url("index","deal#".$v['id'],array("r"=>base64_encode($user_id)));
            $temp_data['ud_is_effect'] = $v['ud_is_effect'];
            $temp_data['ud_type'] = $v['ud_type'];

            if($v['end_time']<= NOW_TIME && $v['end_time']!=0){
                $temp_data['end_status'] = 0;   //过期
            }elseif(($v['begin_time']<= NOW_TIME || $v['begin_time']==0) && ($v['end_time']> NOW_TIME || $v['end_time']==0)){
                $temp_data['end_status'] = 1;   //进行中
            }elseif($v['begin_time']> NOW_TIME && $v['begin_time']!=0){
                $temp_data['end_status'] = 2;   //预告
            }
            $data[] = $temp_data;
		}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="我的分销";
		output($root);
	}	
	
	/**
	 * 选取分销商品页面
	 * 输入：
	 * page [int] 分页
	 * fx_seach_key [string] 搜索条件  分销商品名称
	 *
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
	 *
	
	 [item] => Array ：array 我分销的商品数据列表
	 (
    	 [0] => Array
    	 (
        	 [id] => 95
        	 [name] => 0元抽奖
        	 [sub_name] => 0元抽奖
        	 [icon] => http://localhost/o2onew/public/attachment/201504/03/16/551e4a76556ad_170x170.jpg      :string 商品图片 85*85
        	 [current_price] => 0    ：float  当前价格
        	 [fx_salary] => 0.6      ：float  分销佣金比率或者金额
        	 [fx_salary_type] => 1   ：int    分销佣金的类型    0金额 1比率
        	 [fx_salary_money] => 0  ：float  分销商品可以获得的佣金
        	 [end_status] => 0   ：int 商品 结束类型
    	 )
     )
	
	 *
	 * */
	public function deal_fx(){
	    $root = array();
	    /*参数初始化*/
	
	    //检查用户,用户密码
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
	
	        
	        $u_level = array();
	        if($user['fx_level']>0){
	            //取出等级信息
	            $u_level = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."fx_level where id = ".$user['fx_level']);
	            	
	            //独立出用户等级数据
	            $salarys = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where  fx_level=0 and level_id=".$u_level['id']);
	        }
	        $u_level = $u_level?array_merge($u_level,$salarys):array();
	        	
	        	
	        //默认佣金
	        $default_salary = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where level_id = 0 and fx_level=0");
	        if (empty($u_level)){  //无等级的情况下取默认
	            $u_level = $default_salary;
	        }
	        	
	        
	        //分页
	        $page = intval($GLOBALS['request']['page']);
	        $page=$page==0?1:$page;
	
	        $page_size = PAGE_SIZE;
	        $limit = (($page-1)*$page_size).",".$page_size;
	        	
	        require_once APP_ROOT_PATH.'system/model/deal.php';
	         
	        $s_deal_name = strim($GLOBALS['request']['fx_seach_key']);
	        $condition = '';
	        if($s_deal_name !='')
	        {
	            $condition = " and d.name like '%".$s_deal_name."%'";
	        }
	        
	        //获取用户已经分销的数据
	        $cur_deals = $GLOBALS['db']->getAll('select deal_id from '.DB_PREFIX.'user_deal where user_id = '.$user_id);
	        if($cur_deals){
	            foreach ($cur_deals as $v){
	                $not_deal_ids[] = $v['deal_id'];
	            }
	            $condition .= " and d.id not in (".  implode(",", $not_deal_ids).") ";
	        }

            $ext_condition = " d.is_fx=2 and d.buy_type <> 1  ".$condition;

            $orderby =' d.id desc ';
    		$deal_result = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array(),'',$ext_condition,$orderby);
    
    		
	        $deal_list = $deal_result['list'];
	        $count = $GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'deal d where '.$deal_result['condition'],false);
	
	
	        $page_total = ceil($count/$page_size);
	        //end 分页
	        	
	        	
	
	        	
	        //获取ID
	        foreach ($deal_list as $k=>$v){
	            $ids[] = $v['id'];
	        }
	
	        //佣金比率
	        $deal_salarys = $GLOBALS['db']->getAll('select * from '.DB_PREFIX.'deal_fx_salary where deal_id in('.implode(',', $ids).') and fx_level=0 ');
    	    foreach ($deal_salarys as $k=>$v){
                if ($v['fx_salary']==0){    //如果商品没设置 分销佣金比率，默认为用户当前的
                    $v['fx_salary'] = $u_level['fx_salary'];
                    $v['fx_salary_type'] = $u_level['fx_salary_type'];
                }else{
                    $v['fx_salary'] = $v['fx_salary'];
                }
                
                $f_deal_salarys[$v['deal_id']] = $v;
            }
            
	        //要返回的字段
	        $data = array();
	        foreach ($deal_list as $K=>$v){
	            $temp_data['id'] = $v['id'];
	            $temp_data['name'] = msubstr($v['name'],0,40);
	            $temp_data['sub_name'] = $v['sub_name'];
	            $temp_data['icon'] = get_abs_img_root(get_spec_image($v['icon'],85,85,1));
	            $temp_data['current_price'] = round($v['current_price'],2);
	            
	            $deal_salary = $f_deal_salarys[$v['id']]['fx_salary'];
	            $fx_salary_type = $f_deal_salarys[$v['id']]['fx_salary_type'];
	            
	            $temp_data['fx_salary'] = $fx_salary_type?round($deal_salary*100,2):round($deal_salary,2);
	            $temp_data['fx_salary_type'] = $fx_salary_type;
	            $temp_data['fx_salary_money'] = $fx_salary_type?round($deal_salary*$temp_data['current_price'],2):round($deal_salary,2);
	            
	            
	            $temp_data['ud_type'] = $v['ud_type'];
	
	            if($v['end_time']<= NOW_TIME && $v['end_time']!=0){
	                $temp_data['end_status'] = 0;   //过期
	            }elseif(($v['begin_time']<= NOW_TIME || $v['begin_time']==0) && ($v['end_time']> NOW_TIME || $v['end_time']==0)){
	                $temp_data['end_status'] = 1;   //进行中
	            }elseif($v['begin_time']> NOW_TIME && $v['begin_time']!=0){
	                $temp_data['end_status'] = 2;   //预告
	            }
	            $data[] = $temp_data;
	        }
	        	
	        $root['item'] = $data;
	        $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
	
	    }
	
	    $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
	    $root['page_title'].="逛市场";
	    output($root);
	}
	
	/**
	 * 添加我的分销
	 * 输入：
	 * deal_id [int] 商品ID
	 *
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
	 *
	 status   int 状态
	 info     string 消息
	 *
	 * */
	public function add_user_fx_deal(){
	    $root = array();
	    /*参数初始化*/
	    $deal_id = intval($GLOBALS['request']['deal_id']);
	     
	    //检查用户,用户密码
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	     
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
	        require_once APP_ROOT_PATH.'system/model/fx.php';
	        if(add_user_fx_deal($user_id, $deal_id)){
	            output($root,1,"操作成功");
	        }else{
	            output($root,0,"操作失败");
	        }
	    }
	    output($root);
	}
	
	
	/**
     * 修改我的分销状态接口（上架、下架状态）
     * 输入：
     * deal_id [int] 商品ID
     * 
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * 
       status   int 状态
       info     string 消息
     *
     * */
	public function do_is_effect(){
	    $root = array();
	    /*参数初始化*/
	    $deal_id = intval($GLOBALS['request']['deal_id']);
	    
	    //检查用户,用户密码
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	    
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
	        require_once APP_ROOT_PATH.'system/model/fx.php';
	        if(do_is_effect($user_id, $deal_id)){
    	        output($root,1,"操作成功");
    	    }else{
    	        output($root,0,"操作失败");
    	    }
	    }
	    output($root);
	}
	
	/**
	 * 删除我的分销
	 * 输入：
	 * deal_id [int] 商品ID
	 *
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
	 *
    	 status   int 状态
    	 info     string 消息
	 *
	 * */
	public function del_user_deal(){
	    $root = array();
	    /*参数初始化*/
	    $deal_id = intval($GLOBALS['request']['deal_id']);
	    
	    //检查用户,用户密码
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	    
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
    	    require_once APP_ROOT_PATH.'system/model/fx.php';
    	    if(del_user_deal($user_id, $deal_id)){
    	        output($root,1,"操作成功");
    	    }else{
    	        output($root,0,"操作失败");
    	    }
	    }
	    output($root);
	}
    
	

	/**
	 * 我的小店
	 * 输入：
	 * page [int] 分页
	 * type [int] 0团购 1商城
	 *
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
	 *
	 [type] => 0     ：int 0团购 1商城
     [is_why] => 2   ：int 1 自己，2其它登录用户看，3未登录用户看
	 [user_data] => Array  ：array 用户头部数据
	 (
    	 [user_name] => fanwe    ：string 用户名
    	 [user_avatar] => http://localhost/o2onew/public/avatar/000/00/00/71virtual_avatar_big.jpg       ：string 用户头像    页面上限定 85*85
    	 [fx_mall_bg] => http://localhost/o2onew/public/attachment/201506/16/15/nofxm23fs_740x300.jpg    ：string 我的分销背景图片    360*150
	 )
	
	[deal_list] => Array
        (
            [0] => Array
                (
                    [id] => 68
                    [name] => 仅售228元！最高价值446元的希腊之旅套餐A/希腊之旅套餐B2选1，男女不限，提供免费WiFi。
                    [icon_157] => http://localhost/o2onew/public/attachment/201502/25/16/54ed8e6b70b46_314x314.jpg      ：string 商城显示图片
                    [icon_85] => http://localhost/o2onew/public/attachment/201502/25/16/54ed8e6b70b46_170x170.jpg       ：string 团购显示图片
                    [origin_price] => 446   ：float 原价
                    [current_price] => 228  ：float 现价
                )

        )
	
	 *
	 * */
	public function mall(){
	    $root = array();
	    /*参数初始化*/
	    $type = intval($GLOBALS['request']['type']); //0团购类 1商城类
	    $id = $GLOBALS['ref_uid']; //用户推荐ID
	    
	    
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	    
	    $is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
        if($id == $user_id)
        {
            $is_why = 1;
            $home_user_info = $user;
        }
        else
        {
            $is_why = 3;
            $home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
            if($home_user_info){
                $is_why = 2;
            }
        }
        $root['deal_list'] = array();
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	    }
	    
	    //返回会员信息
	    $user_data = array();
	    $user_data['user_name'] = $home_user_info['user_name'];
	    $user_data['user_avatar'] = get_abs_img_root(get_muser_avatar($home_user_info['id'],"big"))?get_abs_img_root(get_muser_avatar($home_user_info['id'],"big")):"";
	    $user_data['fx_mall_bg'] = $user['fx_mall_bg']?get_abs_img_root(get_spec_image($home_user_info['fx_mall_bg'],320,150,1)):SITE_DOMAIN.APP_ROOT."/mapi/image/nofxmallbg.jpg";
	    
	    $root['user_data'] = $user_data;
	    
	        $root['user_login_status'] = $user_login_status;
    	    $root['type'] = $type;
    	    $root['is_why'] = $is_why;
    	    $root['fx_mall_bg'] = $user['fx_mall_bg']?get_abs_img_root(get_spec_image($user['fx_mall_bg'],320,150,1)):SITE_DOMAIN.APP_ROOT."/mapi/image/nofxmallbg.jpg";

    	    //分页
    	    $page = intval($GLOBALS['request']['page']);
    	    $page=$page==0?1:$page;
    	    
    	    $page_size = PAGE_SIZE;
    	    $limit = (($page-1)*$page_size).",".$page_size;
    	    
    		require_once APP_ROOT_PATH."system/model/deal.php";		
    		
    	
    		if($type==0)
    		{
    			$join = " left join ".DB_PREFIX."user_deal as ud on d.id = ud.deal_id and ud.user_id = ".$id." ";
    			$ext_condition = " d.buy_type <> 1 and d.is_shop = 0 and (d.is_fx = 1 or (d.is_fx = 2 and ud.is_effect = 1)) ";
    			$sort_field = " ud.add_time desc ";
    			$deal_result  = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id']),$join,$ext_condition,$sort_field);
    			
    			$deal_list = $deal_result['list'];		
    			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d ".$join." where ".$deal_result['condition'],false);
    
  
    		}
    		else
    		{
    			$join = " left join ".DB_PREFIX."user_deal as ud on d.id = ud.deal_id and ud.user_id = ".$id." ";
    			$ext_condition = " d.buy_type <> 1 and d.is_shop = 1 and (d.is_fx = 1 or (d.is_fx = 2 and ud.is_effect = 1)) ";
    			$sort_field = " ud.add_time desc ";
    			$deal_result  = get_goods_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array(),$join,$ext_condition,$sort_field);
    				
    			$deal_list = $deal_result['list'];
    			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d ".$join." where ".$deal_result['condition'],false);
    		
    		}
    		
    		foreach ($deal_list as $k=>$v){
    		    $temp_data['id'] = $v['id'];
    		    $temp_data['name'] = $v['name'];
    		    $temp_data['icon_157'] = get_abs_img_root(get_spec_image($v['icon'],157,157,1));
    		    $temp_data['icon_85'] = get_abs_img_root(get_spec_image($v['icon'],85,85,1));
    		    $temp_data['origin_price'] = round($v['origin_price'],2);
    		    $temp_data['current_price'] = round($v['current_price'],2);
    		    $deal_list[$k] = $temp_data;
    		}
    		
    		//end 分页
    		$page_total = ceil($count/$page_size);
    		$root['page_title'] = $home_user_info['user_name']."的小店";
    		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
    		$root['deal_list'] = $deal_list?$deal_list:array();
	    
	    output($root);
	}
	
	
	/**
	 * 修改小店背景
	 *
	 * 输入：
	 * $_FILES['file']：头像文件
	 *
	 * 输出：
	 * status: int 0失败 1成功
	 * info:string 信息提示
	 * fx_mall_bg:string  小店背景图片 360*150
	 */
	public function upload_bg()
	{
	    $root = array();
	
	    if($GLOBALS['user_info'])
	    {

	        if($_FILES['file'])
	        {
	            $res = $this->upload_file($_FILES, $GLOBALS['user_info']['id']);
	            if($res['error']==0)
	            {
	               //保存到用户表
	               
	               $GLOBALS['db']->autoExecute(DB_PREFIX."user",array("fx_mall_bg"=>$res['url']),'UPDATE'," id=".$GLOBALS['user_info']['id']);
	               $root['fx_mall_bg'] = $fx_mall_bg_url = get_abs_img_root($res['thumb']['fx_mall_bg']['url']);
	               output($root);
	            }
	            else
	            {
	                output($root,0,$res['message']);
	            }
	        }
	        else
	        {
	            output($root,0,"请上传文件");
	        }
	    }
	    else
	    {
	        output($root,0,"请先登录");
	    }
	
	}
	
	
	public function upload_file($_files,$uid){
	    //上传处理
	    //创建comment目录
	    if (!is_dir(APP_ROOT_PATH."public/comment")) {
	        @mkdir(APP_ROOT_PATH."public/comment");
	        @chmod(APP_ROOT_PATH."public/comment", 0777);
	    }
	    
	    $dir = to_date(NOW_TIME,"Ym");
	    if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/comment/".$dir);
	        @chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
	    }
	    
	    $dir = $dir."/".to_date(NOW_TIME,"d");
	    if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/comment/".$dir);
	        @chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
	    }
	    
	    $dir = $dir."/".to_date(NOW_TIME,"H");
	    if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/comment/".$dir);
	        @chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
	    }
	    
	    $img_result = save_image_upload($_files,"file","comment/".$dir,$whs=array('fx_mall_bg'=>array(360,150,1,0)),0,1);

	    if(intval($img_result['error'])!=0)
	    {
	        return $img_result;
	    }
	    else
	    {
	        if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	        {
	            syn_to_remote_image_server($img_result['file']['url']);
	            syn_to_remote_image_server($img_result['file']['thumb']['preview']['url']);
	        }
	    
	    }
	    
	    $data_result['error'] = 0;
	    $data_result['url'] = $img_result['file']['url'];
	    $data_result['path'] = $img_result['file']['path'];
	    $data_result['name'] = $img_result['file']['name'];
	    $data_result['thumb'] = $img_result['file']['thumb'];
	    
	    return $data_result;
	}
    
    
}

