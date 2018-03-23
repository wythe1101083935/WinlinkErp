<?php 
namespace System\Model;
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
		//array('username','','账号已存在！',0,'unique',3),	
		//array('email','','邮箱已存在！',0,'unique',3),		
		//array('customer_tel','','手机号已存在！',0,'unique',3),
	);
	/*自动处理*/
	/*protected $_auto = array(
		array('password','md5',3,'function')
	);*/
	/*
	 +----------------------------------------------------------
	 * 增加用户
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return $afectRows
	 +----------------------------------------------------------
	*/
	public function insertUser($data){
		if($this->create($data)){
			$this->password = md5($this->password);
			$user_id = $this->add();
			M('RoleUser')->data(array('role_id'=>$data['role_id'],'user_id'=>$user_id))->add();//增加角色
			foreach ($data as $key => $val) {
				if(substr($key,0,9)=='rule_fee_'){
					M('RuleFeeUser')->data(array(
						'user_id'=>$user_id,
						'rule_id'=>$val,
						'standard_v_param'=>$data['v_param_'.$val],
						'standard_v_status'=>$data['v_status_'.$val]
					))->add();
				}
			}
			return array('status'=>true);
		}else{
			return array('status'=>false,'errorMsg'=>$this->getError());
		}
	}

	/*
	 +----------------------------------------------------------
	 * 修改用户
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return afectRows
	 +----------------------------------------------------------
	*/
	public function updateUser($data){
		$sign = 1 ;
		if(!$data['password']){
			$sign = 0;
			unset($data['password']);
			unset($data['repassword']);
		}
		if($this->create($data)){
			if($sign){
				$this->password = md5($this->password);
			}
			$this->save();
			M('RoleUser')->where(array('user_id'=>$data['id']))->setField('role_id',$data['role_id']);//更改角色
			M('RuleFeeUser')->where(array('user_id'=>$data['id']))->delete();
			$this->insertRuleFeeUser($data);
			return array('status'=>true);
		}else{
			return array('status'=>false,'errorMsg'=>$this->getError());
		}
	}
	/*
	 +----------------------------------------------------------
	 * 增加用户报价资料关系表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function insertRuleFeeUser($data){
		foreach ($data as $key => $val) {
			if(substr($key,0,9)=='rule_fee_'){
				M('RuleFeeUser')->data(array(
					'user_id'=>$data['id'],
					'rule_id'=>$val,
					'standard_v_param'=>$data['v_param_'.$val],
					'standard_v_status'=>$data['v_status_'.$val]
				))->add();
			}
		}
	}
}
