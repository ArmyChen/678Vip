<?php

// +----------------------------------------------------------------------

// | Fanweo2o商业系统 最新版V3.03.3285  含4个手机APP。

// +----------------------------------------------------------------------

// | 购买本程序，请联系QQ：78282385  旺旺名：alert988

// +----------------------------------------------------------------------

// | 淘宝购买地址：https://shop36624490.taobao.com/

// +----------------------------------------------------------------------



class DarenSubmitAction extends CommonAction{



	public function edit() {		

		$id = intval($_REQUEST ['id']);

		$condition['id'] = $id;		

		$vo = M(MODULE_NAME)->where($condition)->find();

		

		$this->assign ( 'vo', $vo );

		$this->display ();

	}

	

	public function update() {

		B('FilterString');

		$submit = M("DarenSubmit")->getById(intval($_REQUEST['id']));

		$user_info = M("User")->getById($submit['user_id']);

		$user_info['is_daren'] = 1;

		$user_info['daren_title'] = strim($_REQUEST['daren_title']);

		M("User")->save($user_info);

		save_log($user_info['user_name']."被设为达人",1);

		$submit['is_publish'] = 1;

		M("DarenSubmit")->save($submit);

		$this->success("审核成功");

	}

	

	

	

	public function foreverdelete() {

		//彻底删除指定记录

		$ajax = intval($_REQUEST['ajax']);

		$id = $_REQUEST ['id'];

		if (isset ( $id )) {

				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );

				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				

				foreach($rel_data as $data)

				{

					$info[] = $data['name'];	

				}

				if($info) $info = implode(",",$info);

				$list = M(MODULE_NAME)->where ( $condition )->delete();	

				//删除相关预览图

//				foreach($rel_data as $data)

//				{

//					@unlink(get_real_path().$data['preview']);

//				}			

				if ($list!==false) {

										

					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);

					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);

				} else {

					save_log($info.l("FOREVER_DELETE_FAILED"),0);

					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);

				}

			} else {

				$this->error (l("INVALID_OPERATION"),$ajax);

		}

	}

	

	

}

?>