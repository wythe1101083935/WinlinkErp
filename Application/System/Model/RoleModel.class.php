<?php
namespace System\Model;
use Think\Model;
class RoleModel extends Model{
	/*
	 +----------------------------------------------------------
	 * 增加角色
	 +----------------------------------------------------------
	 * @param  $data;
	 +----------------------------------------------------------
	 * @return $afectRows
	 +----------------------------------------------------------
	*/
	public function insert($data){
		$res = $this->data($data)->add();
		if($res){
			$return = ReturnData(true,'增加角色成功！',$res,'');
		}else{
			$return = ReturnData(false,'增加角色失败！','','');
		}
		return $return;
		
	}
	/*
	 +----------------------------------------------------------
	 * 修改角色
	 +----------------------------------------------------------
	 * @param  $data;
	 +----------------------------------------------------------
	 * @return $afectRows
	 +----------------------------------------------------------
	*/
	public function update($data){
		$res = $this->data($data)->save();
		if($res){
			$return = ReturnData(true,'修改角色成功！','','');
		}else{
			$return = ReturnData(false,'角色未修改或修改失败！','','');
		}
		return $return;
	}
}