<?php

// +----------------------------------------------------------------------

// | Fanweo2o商业系统 最新版V3.03.3285  含4个手机APP。

// +----------------------------------------------------------------------

// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.

// +----------------------------------------------------------------------

// | 淘宝购买地址：https://shop36624490.taobao.com/

// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';

class indexModule extends BizBaseModule

{

    

	public function index()

	{	

	    //获取权限

	    $biz_account_auth = get_biz_account_auth();
        array_push($biz_account_auth,'inventory');//暂时手动加入新仓库权限

        if(empty($biz_account_auth)){

		    app_redirect(url("biz","user#login"));

		}else{

		    $jump_url = url("kiz","index#index");

		    app_redirect($jump_url);

		}

	}

	

	

}

?>