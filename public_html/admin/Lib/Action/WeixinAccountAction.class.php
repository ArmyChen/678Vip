<?php
// +----------------------------------------------------------------------
// | Fanwe 方维众筹商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class WeixinAccountAction extends CommonAction{
	public function index()
	{
		$config = M("WeixinAccountConf")->where("is_conf=1 and is_effect=1")->order("sort asc")->findAll();
		$this->assign("WEIXIN_TYPE",WEIXIN_TYPE);
		$this->assign("config",$config);
		$this->display();
	}
	
	public function update()
	{
		foreach($_POST as $k=>$v)
		{
			M("WeixinAccountConf")->where("name='".$k."'")->setField("value",strim($v));
		}
		
		rm_auto_cache("weixin_conf");
		
		$this->success("保存成功");
	}

}
?>