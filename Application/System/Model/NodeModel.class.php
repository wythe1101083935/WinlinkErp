<?php
/**
 +----------------------------------------------------------
 * Node菜单模型
 +----------------------------------------------------------
 * CODE:10401
 +----------------------------------------------------------
 * TIME:2018-01-24 17:34:16
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace System\Model;
use Think\Model;
class NodeModel extends Model{
	protected $tableName = 'new_node';
	/*
	 +----------------------------------------------------------
	 * insertmenu
	 +----------------------------------------------------------
	 * @param  $data;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function insert($data){
		if($data['pid']==0){//模块
			$data['level'] = 1;
			$data['menu_path'] = '0,';
		}else{//控制器和操作
			$pData = $this->where(array('id'=>$data['pid']))->find();
			$data['level'] = $pData['level']+1;
			$data['menu_path'] = $pData['menu_path'].$data['pid'].',';
		}
		$data['menu_index'] = $data['index'];
		$res = $this->data($data)->add();
		if($res){
			$return = ReturnData(true,'增加menu成功',$res,'100000');
		}else{
			$return = ReturnData(false,'增加menu失败','','10401001');
		}
		return $return;
	}

	/*
	 +----------------------------------------------------------
	 * updatemenu
	 +----------------------------------------------------------
	 * @param  $data;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function update($data){
		$res = $this->data($data)->save();
		if($res){
			$return = ReturnData(true,'更改menu成功',$res,'100000');
		}else{
			$return = ReturnData(false,'更改menu失败或未更改','','10401002');
		}
		return $return;
	}
}
