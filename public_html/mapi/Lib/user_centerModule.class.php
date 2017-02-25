<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
class user_centerModule extends MainBaseModule {
	
	/**
	 * 会员中心首页接口
	 *
	 * 输入:
	 *
	 * 输出:
	 * user_login_status:int 0表示未登录 1表示已登录 2表示临时登录
	 * login_info:string 未登录状态的提示信息，已登录时无此项
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * uid:int 71 会员id
	 * user_name:string fanwe 会员名
	 * user_money_format:string ¥9973.2会员账户余额
	 * user_avatar:string http://localhost/o2onew/public/avatar/000/00/00/71virtual_avatar_big.jpg 会员头像图路径
	 * user_score: int 会员积分
	 * user_score_format:string 会员积分格式化
	 * not_pay_order_count:int 未付款订单数
	 * wait_dp_count: int 待点评数量
	 */
	public function index() {
		$root = array ();
		$user_data = $GLOBALS ['user_info'];
		$user_login_status = check_login ();
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
		} else {
			$root ['user_login_status'] = $user_login_status;
			$root ['page_title'] = $GLOBALS ['m_config'] ['program_title'] ? $GLOBALS ['m_config'] ['program_title'] . " - " : "";
			$root ['page_title'] .= "会员中心";
			$root ['uid'] = $user_data ['id'] ? $user_data ['id'] : 0;
			$root ['user_name'] = $user_data ['user_name'] ? $user_data ['user_name'] : 0;
			$root ['user_money_format'] = format_price ( $user_data ['money'] ) ? format_price ( $user_data ['money'] ) : ""; // 用户金额
			$root ['user_score'] = intval ( $user_data ['score'] );
			$root ['user_score_format'] = format_score ( $user_data ['score'] );
			$root ['user_avatar'] = get_abs_img_root ( get_muser_avatar ( $user_data ['id'], "big" ) ) ? get_abs_img_root ( get_muser_avatar ( $user_data ['id'], "big" ) ) : "";
			$user_id = $user_data ['id'];
			$avatar_url_1 = $GLOBALS ['db']->getRow ( "select avatar,mobile from " . DB_PREFIX . "user where id = " . $user_id );
			$avatar_url = $avatar_url_1['avatar'];
			if (strstr ( $avatar_url, "qlogo.cn" )) {
				$root ['user_avatar'] = $avatar_url;
			}
			$root ['mobile'] = $avatar_url_1['mobile'];
			$coupon_count = $GLOBALS ['db']->getOne ( "select count(*) from " . DB_PREFIX . "deal_coupon where user_id = " . $user_id . " and is_delete = 0 and is_valid = 1 " );
			$root ['coupon_count'] = $coupon_count;
			$youhui_count = $GLOBALS ['db']->getOne ( "select count(*) from " . DB_PREFIX . "youhui_log as yl left join " . DB_PREFIX . "youhui as yh on yh.id = yl.youhui_id where yl.user_id=$user_id " );
			$root ['youhui_count'] = $youhui_count;
			// 待点评
			$root ['wait_dp_count'] = $GLOBALS ['db']->getOne ( "select count(*) from " . DB_PREFIX . "deal_order_item as doi LEFT JOIN " . DB_PREFIX . "deal_order as do on do.id = doi.order_id where do.type=0 and do.user_id=" . $user_id . " and do.order_status=1 and do.pay_status=2 and doi.consume_count>0 and doi.dp_id =0" );
			require_once APP_ROOT_PATH . "system/model/deal_order.php";
			$order_table_name = get_user_order_table_name ( $user_id );
			$not_pay_order_count = $GLOBALS ['db']->getOne ( "select count(*) from " . $order_table_name . " where user_id = " . $user_id . " and type = 0 and is_delete = 0 and pay_status <> 2" );
			$root ['not_pay_order_count'] = $not_pay_order_count;
		}
		output ( $root );
	}
	
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月23日 下午5:59:43
	 * @描述 获取用户信息
	 */
	public function info() {
		$root = array ();
		$user_data = $GLOBALS ['user_info'];
		$user_login_status = check_login ();
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			$userId = $user_data ['id'];
			$userBean = $GLOBALS ['db']->getAll ( "select * from " . DB_PREFIX . "user where id = " . $userId );
			if (count ( $userBean ) == 1) {
				$userBean [0] ["avatar"] = get_abs_img_root ( $userBean [0] ["avatar"] );
				$root ['data'] = $userBean [0];
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "用户不存在" );
			}
		}
	}
	
	/**
	 * 修改用户
	 */
	public function modifyInfo() {
		$root = array ();
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'] ["modifyUser"];
			
			$sql = "update " . DB_PREFIX . "user set id = id";
			
			/* 更新头像 */
			if (isset ( $req ["avatar"] ) && strlen ( $req ["avatar"] ) > 0) {
				$sql .= ",avatar = '" . $req ["avatar"] . "' ";
			}
			
			/* 更新昵称 */
			if (isset ( $req ["nick_name"] ) && strlen ( $req ["nick_name"] ) > 0) {
				$sql .= ",nick_name = '" . $req ["nick_name"] . "' ";
			}
			
			/* 个性签名 */
			if (isset ( $req ["user_sign"] )) {
				$sql .= ",user_sign = '" . $req ["user_sign"] . "' ";
			}
			
			/* 性别 */
			if (isset ( $req ["sex"] ) && strlen ( $req ["sex"] ) > 0) {
				$sql .= ",sex = " . $req ["sex"];
			}
			
			/* 出生日期 */
			if (isset ( $req ["byear"] ) && strlen ( $req ["byear"] ) > 0) {
				$sql .= ",byear = " . $req ["byear"] . ",bmonth = " . $req ["bmonth"] . ",bday = " . $req ["bday"];
			}
			
			/* 情感状态 */
			if (isset ( $req ["love_state"] ) && strlen ( $req ["love_state"] ) > 0) {
				$sql .= ",love_state = " . $req ["love_state"];
			}
			
			/* 地区省 */
			if (isset ( $req ["province_id"] )) {
				$sql .= ",province_id = " . $req ["province_id"];
				$sql .= ",province = (select name from " . DB_PREFIX . "region_conf where id = " . $req ['province_id'] . ")";
			}
			
			/* 地区市 */
			if (isset ( $req ["city_id"] )) {
				$sql .= ",city_id = " . $req ["city_id"];
				$sql .= ",city = (select name from " . DB_PREFIX . "region_conf where id = " . $req ['city_id'] . ")";
			}
			
			/* 更换背景 */
			if (isset ( $req ["home_bg"] ) && strlen ( $req ["home_bg"] ) > 0) {
				$sql .= ",home_bg = '" . $req ["home_bg"] . "' ";
			}
			
			$sql .= " where id = " . $userId;
			
			if ($GLOBALS ['db']->query ( $sql )) {
				output ( "", 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
	
	/**
	 * 匹配环信好友昵称和头像，备注
	 */
	public function match_hy_user() {
		$root = array ();
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'];
			
			$hy_names = $req ["hy_names"];
			
			$sql = "select IFNULL(nick_name,user_name) nick_name,hy_name,avatar from " . DB_PREFIX . "user where hy_name in ($hy_names)";
			$result_list = $GLOBALS ['db']->getAll ( $sql );
			if ($result_list) {
				
				$data_list = array ();
				foreach ( $result_list as $v ) {
					$v ['avatar'] = get_abs_img_root ( $v ['avatar'] );
					$data_list [] = $v;
				}
				$root [data] = $data_list;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
	
	/**
	 * 获取关注的用户
	 */
	public function get_follow_user() {
		$root = array ();
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'];
			
			$sql = "select fu.id,fu.user_name,fu.nick_name,fu.avatar  from fanwe_user_focus as fuf left join " . DB_PREFIX . "user as fu on fuf.focused_user_id = fu.id where fuf.focus_user_id = $userId";
			$result_list = $GLOBALS ['db']->getAll ( $sql );
			if ($result_list) {
				
				$data_list = array ();
				foreach ( $result_list as $v ) {
					$v ['avatar'] = get_abs_img_root ( $v ['avatar'] );
					$data_list [] = $v;
				}
				$root [data] = $data_list;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
	
	/**
	 * 获取粉丝用户
	 */
	public function get_fans_user() {
		$root = array ();
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'];
			
			$sql = "select fu.id,fu.user_name,fu.nick_name,fu.avatar  from " . DB_PREFIX . "user_focus as fuf left join " . DB_PREFIX . "user as fu on fuf.focus_user_id = fu.id where fuf.focused_user_id = $userId";
			$result_list = $GLOBALS ['db']->getAll ( $sql );
			if ($result_list) {
				
				$data_list = array ();
				foreach ( $result_list as $v ) {
					$v ['avatar'] = get_abs_img_root ( $v ['avatar'] );
					$data_list [] = $v;
				}
				$root [data] = $data_list;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
	
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月23日 下午5:23:26
	 * @描述 搜索好友
	 */
	public function search_friend() {
		$root = array ();
		
		$req = $GLOBALS ['request'];
		
		$key = $req ["key"];
		if (isset ( $key )) {
			$sql = "select id,user_name,nick_name,avatar,mobile,email,hy_name from " . DB_PREFIX . "user where user_name = '$key' OR mobile = '$key' OR email = '$key'";
			$result_list = $GLOBALS ['db']->getAll ( $sql );
			if ($result_list) {
				
				$data_list = array ();
				foreach ( $result_list as $v ) {
					$v ['avatar'] = get_abs_img_root ( $v ['avatar'] );
					$data_list [] = $v;
				}
				$root [data] = $data_list;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		} else {
			output ( "", 1, "没有搜索条件" );
		}
	}
	
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月23日 下午6:00:22
	 * @描述 查找好友是查询好友信息
	 */
	public function friend_info() {
		$root = array ();
		
		$req = $GLOBALS ['request'];
		
		$hy_name = $req ["hy_name"];
		$user_name = $req ["user_name"];
		$user_id = $req ["user_id"];
		
		$where = "";
		if (isset ( $user_name )) {
			$where = "user_name = '$user_name'";
		} elseif (isset ( $hy_name )) {
			$where = "hy_name = '$hy_name'";
		} elseif (isset ( $user_id )) {
			$where = "id = '$user_id'";
		} else {
			output ( "", 1, "没有搜索条件" );
			return;
		}
		
		$sql = "select id,user_name,nick_name,avatar,hy_name from " . DB_PREFIX . "user where $where";
		$result_list = $GLOBALS ['db']->getAll ( $sql );
		if (count ( $result_list ) > 0) {
			
			$data = $result_list [0];
			$data ['avatar'] = get_abs_img_root ( $data ['avatar'] );
			
			// 去获取发表过的最后3张图片
			$top_img = $GLOBALS ['db']->getAll ( "select path from fanwe_topic_image where user_id = " . $data ["id"] . " order by topic_id desc limit 3" );
			
			$imgs = array ();
			foreach ( $top_img as $v ) {
				$imgs [] = get_abs_img_root ( $v ["path"] );
			}
			
			$data ["imgs"] = $imgs;
			$root [data] = $data;
			output ( $root, 0, "成功" );
		} else {
			output ( "", 1, "失败" );
		}
	}
	
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月25日 下午3:31:24
	 * @描述 获取该用户所有的会员卡
	 */
	public function user_card_list() {
		$root = array ();
		if (isDebug ()) {
			$out = array ();
			$out ["slid"] = "门店id";
			$out ["name"] = "门店名";
			$out ["preview"] = "门店头图";
			$root ["out"] = $out;
			
			output ( $root );
		}
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			
			$sql = "select fnu.id,fsl.name,fsl.preview from " . DB_PREFIX . "n1_users as fnu left join " . DB_PREFIX . "supplier_location as fsl on fnu.slid = fsl.id where fnu.user_id = $userId";
			$result_list = $GLOBALS ['db']->getAll ( $sql );
			if ($result_list) {
				
				$data_list = array ();
				foreach ( $result_list as $v ) {
					$v ['preview'] = get_abs_img_root ( $v ['preview'] );
					$data_list [] = $v;
				}
				$root [data] = $data_list;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
	
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月25日 下午3:31:24
	 * @描述 获取会员卡详情
	 */
	public function user_card_detail() {
		$root = array ();
		if (isDebug ()) {
			$out = array ();
			$out ["slid"] = "门店id";
			$out ["name"] = "门店名";
			$out ["preview"] = "门店头图";
			$root ["out"] = $out;
			
			output ( $root );
		}
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'];
			
			/* 门店id */
			$n1_id = $req ["n1_id"];
			
			/* 获取表名sql */
			$tb_name_sql = "select concat('fanwe_user_',supplier_id,'_',if(isZhiying,slid,0),'_info') from (select fnu.supplier_id,fnu.slid,fsl.isZhiying from fanwe_n1_users as fnu left join fanwe_supplier_location as fsl ON fnu.slid = fsl.id where fnu.id = $n1_id ) nt";
			
			/* 得到表名 */
			$tb_name = $GLOBALS ['db']->getOne ( $tb_name_sql );
			
			$sql = "select fui.*,fsl.name,fsl.preview from $tb_name as fui left join fanwe_supplier_location as fsl on fsl.id = fui.slid where fui.user_id = $userId";
			
			$rs = $GLOBALS ['db']->getRow ( $sql );
			if ($rs) {
				
				$rs ['preview'] = get_abs_img_root ( $rs ['preview'] );
				$root [data] = $rs;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}

	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月25日 下午3:31:24
	 * @描述 查询用户的消费记录
	 */
	public function user_card_consume_list() {
		$root = array ();
		if (isDebug ()) {
				
			/* debug模式输出参数 */
			$out = array ();
			$root ["out"] = $out;
			output ( $root );
		}
	
		$user_data = $GLOBALS ['user_info'];
	
		$user_login_status = check_login ();
	
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
				
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'];
				
			/* 当前页码 */
			$page = intval ( $req ['page'] );
				
			/* 门店id */
			$slid = intval ( $req ['slid'] );
				
			/* 分页 */
			$page = $page == 0 ? 1 : $page;
			$page_size = PAGE_SIZE;
			$limit = (($page - 1) * $page_size) . "," . $page_size;
				
			/* 查询数据 */
			$sql = "select OrderId,ActualPrice,CreateTime,Status from xusers_posordermain where user_id = '$userId' and slid = $slid order by id desc limit $limit";
				
			/* 查询数据总数 */
			$sqlCount = "select count(*) from xusers_posordermain where user_id = '$userId' and slid = $slid";
				
			$rs = $GLOBALS ['db']->getAll ( $sql );
				
			$count = $GLOBALS ['db']->getOne ( $sqlCount );
				
			// 得到总页数
			$page_total = ceil ( $count / $page_size );
			if ($rs) {
				$root [data] = $rs;
				$root ['page'] = array (
						"page" => $page,
						"page_total" => $page_total,
						"page_size" => $page_size,
						"data_total" => $count
				);
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月25日 下午3:31:24
	 * @描述 查询用户的消费详情
	 */
	public function user_card_consume_detail() {
		$root = array ();
		if (isDebug ()) {
			
			/* debug模式输出参数 */
			$out = array ();
			$root ["out"] = $out;
			output ( $root );
		}
		
		$user_data = $GLOBALS ['user_info'];
		
		$user_login_status = check_login ();
		
		if ($user_login_status != LOGIN_STATUS_LOGINED) {
			$root ['user_login_status'] = $user_login_status;
			output ( $root, 1, "没有登录" );
		} else {
			
			$userId = $user_data ['id'];
			$req = $GLOBALS ['request'];
			
			/* 订单号 */
			$onum = $req ['onum'];
			if(!isset($onum)){
				output ( $root, 1, "参数错误" );
			}
			
			/* 查询数据 */
			$sql = "select otj.pnum,otj.pmoney,fdm.name,fdm.image from orders_tj as otj left join fanwe_dc_menu as fdm on otj.pid = fdm.id where otj.onum = '$onum'";
			$rs = $GLOBALS ['db']->getAll ( $sql );
			if ($rs) {

				$data_list = array ();
				foreach ( $rs as $v ) {
					$v ['image'] = get_abs_img_root ( $v ['image'] );
					$data_list [] = $v;
				}
				
				$root [data] = $data_list;
				output ( $root, 0, "成功" );
			} else {
				output ( "", 1, "失败" );
			}
		}
	}
}
?>