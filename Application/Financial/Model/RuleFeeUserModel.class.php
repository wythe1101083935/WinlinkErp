<?php
/**
 +----------------------------------------------------------
 * 用户对应的应收报价，派送成本
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-01-25 14:18:49
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Model;
use Think\Model;
class RuleFeeUserModel extends Model{
	/*
	 +----------------------------------------------------------
	 * 修改用户报价规则
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateUserRule($id){
		$where['id'] = $id;
		if(empty($this->where($where)->find())){
			if(I('post.field') !='rule_id'){
				$return = ReturnData(false,'请先添加规则！');
			}else{
				$data['user_id'] = I('post.user_id');
				$data['rule_id'] = I('post.value');
				$res = $this->data($data)->add();
				if($res){
					$return = ReturnData(true,'已修改！',$res);
				}else{
					$return = ReturnData(false,'修改错误！服务器异常！');
				}
			}
		}else{
			$res = $this->where($where)->setField(I('post.field'),I('post.value'));
			if($res){
				$return = ReturnData(true,'已修改！');
			}else{
				$return = ReturnData(fale,'未修改！');
			}
		}
		return $return;
	}
}