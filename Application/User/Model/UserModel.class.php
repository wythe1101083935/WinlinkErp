<?php
/**
 +----------------------------------------------------------
 * 用户表
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-07 14:06:56
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace User\Model;
use Think\Model;
class UserModel extends Model{
	/*字段映射*/
	protected $_map = array(
		'customer_tel'=>'Customer_Tel',
		'customer_company'=>'Customer_Company',
		'customer_name'=>'Customer_Name',
		'customer_fax'=>'Customer_Fax',
		'customer_add1'=>'Customer_Add1',
		'customer_add2'=>'Customer_Add2',
		'customer_add3'=>'Customer_Add3',
		'customer_add4'=>'Customer_Add4',
		'customer_zipcode'=>'Customer_Zipcode',
	);
	/*自动验证*/
	protected $_validate = array(
		array('username','require','账号不能为空！'),
		array('email','email','邮箱格式不正确！'),
		array('Customer_Tel','require','联系号码不能为空！'),
		array('Customer_Tel','/^1[34578]\d{9}$/ims','手机号码格式不正确！'),
		array('Customer_Company','require','公司名不能为空！'),
		array('Customer_Name','require','寄件人姓名不能为空！'),
		//array('Customer_Add1','require','地址不能为空！'),
		array('Customer_Add4','require','详细地址不能为空！'),	
		//array('customer_fax','require','传真号不能为空！'),
		array('password','require','密码不能为空！'),
		array('password','/^\w{6,20}$/','密码至少6位至多20位！'),
		array('repassword','password','两次输入的密码不一致！',0,'confirm',3),
		array('username','','账号已存在！',0,'unique',3),	
		//array('email','','邮箱已存在！',0,'unique',3),		
		//array('customer_tel','','手机号已存在！',0,'unique',3),
	);
	/*
	 +----------------------------------------------------------
	 * 增加操作
	 +----------------------------------------------------------
	 * @param  mix;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,code:int,data:mix}
	 +----------------------------------------------------------
	*/
	public function insert($data){
		if($this->create($data)){
			$this->password = md5($this->password);
			$res1 = $this->add();
			$user_id = $res1;
			$res2 = M('RoleUser')->data(array('role_id'=>$data['role_id'],'user_id'=>$user_id))->add();//增加角色
			if($res1 && $res2){
				$return = ReturnData(true,'Add User Success!');
			}else{
				$return = ReturnData(false,'Database Error! Please Contact the System Manager!','','');
			}
		}else{
			$return = ReturnData(false,$this->getError(),'','');
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 更新操作
	 +----------------------------------------------------------
	 * @param  mix;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,code:int,data:mix}
	 +----------------------------------------------------------
	*/
	public function update($data){
		$sign = 1 ;//监控是否更改密码
		if(!$data['password']){
			$sign = 0;
			unset($data['password']);
			unset($data['repassword']);
		}
		if($this->create($data)){
			if($sign){
				$this->password = md5($this->password);
			}
			$res1 = $this->save();
			if(isset($data['role_id'])){
				$res2 = M('RoleUser')->where(array('user_id'=>$data['id']))->setField('role_id',$data['role_id']);//更改角色
			}else{
				$res2 = true;
			}
			if($res1 || $res2){
				$return = ReturnData(true,'修改成功!');
			}else{
				$return = ReturnData(false,'没有信息更改!','','');
			}
		}else{
			$return = ReturnData(false,$this->getError(),'','');
		}
		return $return;
	}

	/*
	 +----------------------------------------------------------
	 * 删除操作
	 +----------------------------------------------------------
	 * @param  mix;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,code:int,data:mix}
	 +----------------------------------------------------------
	*/
	public function delete($data){
		$res = $this->where(array('id'=>array('IN'=>$data)))->delete();
		if($res){
			$return = ReturnData(true,'删除成功！');
		}else{
			$return = RetrunData(false,'删除失败！系统错误！','','');
		}
		return $return;
	}
}