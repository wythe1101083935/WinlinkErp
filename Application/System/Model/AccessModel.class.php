<?php
/**
 +----------------------------------------------------------
 * 角色权限
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-01-26 10:02:25
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
 namespace System\Model;
 use Think\Model;
 class AccessModel extends Model{
 	protected $tableName = 'new_access';
 	/*
 	 +----------------------------------------------------------
 	 * 编辑角色权限
 	 +----------------------------------------------------------
 	 * @param  ;
 	 +----------------------------------------------------------
 	 * @return json{status:bool,msg:string,data:mix,code:int}
 	 +----------------------------------------------------------
 	*/
 	public function updateRoleAuth($data){
		$accessData = $data['auth'];
		//先删除所有角色权限信息
		$where['role_id'] = $data['id'];
		$res2 = $this->where($where)->delete();
		//增加新的角色权限信息
		foreach ($accessData as $key => $val) {
			$res3 = $this->data(array('role_id'=>$data['id'],'node_id'=>$val))->add();
		} 
		if($res3){
			$return = ReturnData(true,'权限编辑成功！','','');
		}else{
			$return = ReturnData(false,'权限编辑失败！','','');
		}
		return $return;
 	}
 }