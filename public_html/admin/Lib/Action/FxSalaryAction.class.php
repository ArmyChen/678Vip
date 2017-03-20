<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: jobinlin
// +----------------------------------------------------------------------

define("FX_SET_TYPE_1",1); //全局分销佣金设置
define("FX_SET_TYPE_2",2); //会员等级佣金设置
define("FX_SET_TYPE_3",3); //分销商品佣金设置
class FxSalaryAction extends CommonAction{
	public function index()
	{
	    $this->assign("title_name","全局分销佣金设置");
	    $this->assign("fx_set_type",FX_SET_TYPE_1);
		$this->display ();
	}

	public function load_fx_level(){
	    $fx_salary_type = intval($_REQUEST['fx_salary_type']);
	    $fx_set_type = intval($_REQUEST['fx_set_type']);
	    $is_init = intval($_REQUEST['is_init']);
	    $deal_id = intval($_REQUEST['deal_id']);
	    
	    if ($fx_set_type ==3 && $deal_id>0){
	        
	        $DealFxSalary = M("DealFxSalary");
	        if($is_init == 1){
    	        $data = $DealFxSalary->where('deal_id='.$deal_id)->select();
    	        if ($data){
    	            foreach ($data as $k=>$v){
    	                $f_data[$v['fx_level']] = $v;
    	            }
    	        }
    	        $fx_salary_type = $data[0]['fx_salary_type'];
	        }
	    }else{
	        $level_id = $fx_set_type==1?0:intval($_REQUEST['level_id']);
	        $FxSalary = M("FxSalary");
	        if($is_init == 1){
	            if($fx_set_type==1 || ($fx_set_type==2 && $level_id>0))
	               $data = $FxSalary->where('level_id='.$level_id)->select();
	        
	            if($data){
	                $fx_salary_type = $data[0]['fx_salary_type'];
	                foreach ($data as $k=>$v){
	                    $f_data[$v['fx_level']] = $v;
	                }
	            }
	        }
	        
	    }
	    
	    if($fx_salary_type==1){
	        $type_html = '<select name="fx_salary_type" ><option value="0" >定额</option><option value="1" selected="selected">比率</option></select>';
	    }else{
	        $type_html = '<select name="fx_salary_type" ><option value="0" selected="selected">定额</option><option value="1">比率</option></select>';
	    }
	    
	    $level_html = '';
	    $type_str = $fx_salary_type==0?'元':'%';
	    
	    if($data){
	        
	        for ($i=0;$i<FX_LEVEL;++$i){
	            $str = $i==0?"分销佣金":$i."级邀请佣金";
	            $s_data = $f_data[$i];
	           
	            $s_data['fx_salary'] = $fx_salary_type?$s_data['fx_salary']*100:$s_data['fx_salary'];
	            $level_html .='<div><span style="text-align:left;width:100px;display: inline-block;*zoom:1;*display:inline;">'.$str.'</span><input type="text" class="textbox" name="fx_salary[]" value="'.round($s_data['fx_salary'],2).'" />&nbsp;'.$type_str.'</div><div class="blank5"></div>';
	        }
	    }else{
	        for ($i=0;$i<FX_LEVEL;++$i){
	            $str = $i==0?"分销佣金":$i."级邀请佣金";
	            $level_html .='<div><span style="text-align:left;width:100px;display: inline-block;*zoom:1;*display:inline;">'.$str.'</span><input type="text" class="textbox" name="fx_salary[]" value="" />&nbsp;'.$type_str.'</div><div class="blank5"></div>';
	        }
	    }
	    
	 
	    $result['type_html'] = $type_html;
	    $result['level_html'] = $level_html;
	    ajax_return($result);
	}
	
	public function level_index(){
	    $model = D ("FxLevel");
	    if (! empty ( $model )) {
	        $this->_list ( $model, $map );
	    }
	    $this->assign("title_name","会员等级佣金设置");
	    $this->assign("fx_set_type",FX_SET_TYPE_2);
	    $this->display();
	}
	
	public function add_level(){
	    $this->assign("title_name","会员等级佣金设置【添加】");
	    $this->assign("fx_set_type",FX_SET_TYPE_2);
	    $this->display();
	}
	
	public function edit_level(){
	    $FxSalary = M("FxSalary");
	    $FxLevel = M("FxLevel");
	    
	    $id = intval($_REQUEST['id']);
	    
	    $level = $FxLevel->where('id='.$id)->find();
	    $level['money'] = round($level['money'],2);
	    
	    $this->assign("level",$level);
	    $this->assign("title_name","会员等级佣金设置【编辑】");
	    $this->assign("fx_set_type",FX_SET_TYPE_2);
	    $this->display();
	}
	
	/**
	 * 分销商品佣金设置
	 */
	public function deal_index(){
	    $model = D ("Deal");
	    if (! empty ( $model )) {
	        $map['is_fx'] = array('gt',0);
	        $this->_list ( $model, $map );
	    }
	    $this->assign("title_name","分销商品佣金设置");
	    $this->assign("fx_set_type",FX_SET_TYPE_3);
	    $this->display();
	}
	
	public function add_deal(){
	    $deal_id = intval($_REQUEST['deal_id']);
	    //分类
	    $cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
	    $cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
	    
	    $fx_deal = M("DealFxSalary")->where('deal_id = '.$deal_id)->find();
	    $is_fx = 0;
        if($deal_id>0){
            $is_fx = intval(M("Deal")->where('id='.$deal_id)->getField('is_fx'));
        }
	    $this->assign("deal_id",$deal_id);
	    $this->assign("is_fx",$is_fx);
	    $this->assign("fx_deal",$fx_deal);
	    $this->assign("cate_tree",$cate_tree);
	    $this->assign("title_name","分销商品佣金设置【添加】");
	    $this->assign("fx_set_type",FX_SET_TYPE_3);
	    $this->display();
	}
	
	public function load_seach_deal(){
	    $deal_id = intval($_REQUEST['deal_id']);
	    $param = array();
	    
	    
	    if(strim($_REQUEST['name'])!='')
	    {
	        $param['name'] = strim($_REQUEST['name']);
	        $map['name'] = array('like','%'.strim($_REQUEST['name']).'%');
	    }

	    if(strim($_REQUEST['supplier_name'])!='')
	    {
	        $param['supplier_name'] = strim($_REQUEST['supplier_name']);
	        if(intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier"))<50000)
	            $sql  ="select group_concat(id) from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['supplier_name'])."%'";
	        else
	        {
	            $kws_div = div_str(trim($_REQUEST['supplier_name']));
	            foreach($kws_div as $k=>$item)
	            {
	                $kw[$k] = str_to_unicode_string($item);
	            }
	            $kw_unicode = implode(" ",$kw);
	            $sql = "select group_concat(id) from ".DB_PREFIX."supplier where (match(name_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
	        }
	        $ids = $GLOBALS['db']->getOne($sql);
	        $map['supplier_id'] = array("in",$ids);
	    }

	    
	    if ($deal_id>0){       //编辑的情况下
	        $map['id'] = array("eq",$deal_id);
	    }else{
	        $map['publish_wait'] = 0;
	        $map['is_fx'] = 0;
	        $map['buy_type'] = 0;
	        $map['is_effect'] = 1;
	        $map['is_delete'] = 0;
	    }
	    

	    /*获取参数*/
	    $page = intval($_REQUEST['p']); //分页
	    $page=$page==0?1:$page;
	    
	    //分页
	    $page_size = 5;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    
	    $model = D ('Deal');
	    $voList = $model->where($map)->order('id desc')->limit($limit)->field('id,name,is_fx,current_price,is_fx')->findAll();

	    $count = $model->where($map)->count();// 查询满足要求的总记录数

	    //分页
	    $page_total = ceil($count/$page_size);

	    if($page_total>0){
	        $page = new Page($count,$page_size);  
	        foreach($param as $key=>$val) {
	            $page->parameter .= "$key=".urlencode($val).'&';
	        }
	        $p  =  $page->show();
	        
	        $this->assign('pages',$p);
	    }
	    $this->assign('deal_id',$deal_id);
	    $this->assign('vo',$voList);
	    $this->display();
	}
	
	/**
	 * 判断保存的不同类型调用不同的方法
	 */
	public function save(){
	    $ajax = intval($_REQUEST['is_ajax']);
	    $fx_set_type = intval($_REQUEST['fx_set_type']);
	    if (method_exists($this,"save_".$fx_set_type)){
	        $fun_name = "save_".$fx_set_type;
	        $result = $this->$fun_name($_REQUEST);
	    }	
	    
	    if ($ajax){
	        ajax_return($result);
	    }else{
	        $this->assign("jumpUrl",$result['jump']);
	        $this->success(L("UPDATE_SUCCESS"));
	    }    
	    
	}

	/**
	 * 全局佣金设置保存
	 * @param array $data
	 */
	public function save_1($data){
	    $FxSalary = M("FxSalary");
	    
	    //删除旧数据
	    $FxSalary->where('level_id=0')->delete();
	    
	    $s_data = array();
	    $fx_salary_type = intval($data['fx_salary_type']);
	    $s_data['fx_salary_type'] = $fx_salary_type;
	    $s_data['level_id'] = 0;
	    foreach ($data['fx_salary'] as $k=>$v){ //分销等级由0开始
	        $ins_data['fx_level'] = $k;
	        $ins_data['fx_salary'] = $fx_salary_type?floatval($v/100):floatval($v);
	        $FxSalary->add(array_merge($s_data,$ins_data));
	    }
	    $result['jump'] = u(MODULE_NAME."/index");
	    return $result;
	    
	}
	public function save_2($data){
	    $FxSalary = M("FxSalary");
	    $FxLevel = M("FxLevel");
	    $level_id = intval($_REQUEST['level_id']);
	    
        $level_data = array();
        $level_data['name'] = strim($_REQUEST['name']); 
        $level_data['money'] = floatval($_REQUEST['money']);
        
        if($level_id>0){
            $FxLevel->where('id='.$level_id)->save($level_data);
        }else{
            $level_id = $FxLevel->data($level_data)->add();
        }
        
	    //删除旧数据
	    $FxSalary->where('level_id='.$level_id)->delete();
	    $fx_salary_type = intval($data['fx_salary_type']);
	    $s_data = array();
	    $s_data['fx_salary_type'] = $fx_salary_type;
	    $s_data['level_id'] = $level_id;
	    foreach ($data['fx_salary'] as $k=>$v){ //分销等级由0开始
	        $ins_data['fx_level'] = $k;
	        $ins_data['fx_salary'] = $fx_salary_type?floatval($v/100):floatval($v);
	        $FxSalary->add(array_merge($s_data,$ins_data));
	    }
	    $result['jump'] = u(MODULE_NAME."/level_index");
	    return $result;
	}
	
	/**
	 * 输入：
	 * $data [array] 提交数据
	 *  Array
        (
            [is_fx] => 0
            [fx_salary_type] => 0
            [fx_salary] => Array
                (
                    [0] => 10
                    [1] => 5
                    [2] => 1
                )
        
            [check_ids] => 124,123
            [fx_set_type] => 3
        )
	 * @return Ambigous <string, mixed>
	 */
	public function save_3($data){
	    $Deal = M("Deal");
	    $DealFxSalary = M("DealFxSalary");

	     
	    $deal_ids = strim($data['check_ids']);
	    $deal_ids_arr = $deal_ids?explode(",", $deal_ids):array();
	    
	    $is_fx = intval($data['is_fx']);
	    $fx_salary_type = intval($data['fx_salary_type']);

	   
	    if(empty($deal_ids_arr)){
	        $result['status'] = 0;
	        $result['info'] = '至少选一个商品';
	    }else{
	        //删除旧配置
	        $DealFxSalary->where('deal_id in('.$deal_ids.')')->delete();
	        foreach ($deal_ids_arr as $k=>$v){
	            $s_data['deal_id'] = $v;
	            foreach ($data['fx_salary'] as $sk=>$sv){
	                $s_data['fx_level'] = $sk;
	                $s_data['fx_salary'] = $fx_salary_type?floatval($sv/100):floatval($sv);
	                $s_data['fx_salary_type'] = $fx_salary_type;
	                $DealFxSalary->data($s_data)->add();
	            }
	        }
	        
	        //更新商品表
	        $Deal->where('id in('.$deal_ids.')')->save(array('is_fx'=>$is_fx));
	        
	        $result['status'] = 1;
	        $result['info'] = '操作成功';
	        $result['jump'] = u(MODULE_NAME."/deal_index");
	    }
	    return $result;
	}
	
	public function del_deal_fx(){
	    $Deal = M("Deal");
	    $DealFxSalary = M("DealFxSalary");
	    
	    
	    //参数
	    $ajax = intval($_REQUEST['ajax']);
	    $id = $_REQUEST ['id'];
	   if (isset ( $id )) {
	        $condition = array ('deal_id' => array ('in', explode ( ',', $id ) ) );
	       
	        $list = $DealFxSalary->where ( $condition )->delete();
	        $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
	        //修改商品表
	        $Deal->where($condition)->save(array('is_fx'=>0));
	        
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