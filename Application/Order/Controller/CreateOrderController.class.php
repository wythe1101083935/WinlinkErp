<?php
/**
 +----------------------------------------------------------
 * 新建订单
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-24 14:55:46
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Order\Controller;
use Common\Controller\CommonController;
class CreateOrderController extends CommonController{
	/*
	 +----------------------------------------------------------
	 *首页展示页
	 +----------------------------------------------------------
	 * @param  void;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function index(){
		$this->assign('user',M('ViewUserClient')->field('id,username')->select());
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 新建订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function createOrder(){
		$postData = I('post.');
		$res = $this->check($postData['formData']);
		if($res['status']){
			$return = D('Awb')->createOrder($postData);
		}else{
			$return = $res;
		}
		//$return = ReturnData(true,'测试','CNB5063601','');
		$this->ajaxReturn($return);
	}
	/*
	 +----------------------------------------------------------
	 * 验证字段
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function check($data=false){
		$sign = 0;
		if(!$data){
			$sign = 1;
			$data = I('post.');
		}
		$res =  D('Awb')->checkFields($data);
		if($sign){
			$this->ajaxReturn($res);
		}else{
			return $res;
		}
	}
	/*
	 +----------------------------------------------------------
	 * 国家和城市列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getCountryJson(){
		$this->ajaxReturn(D('CountryCode')->getCountryJson());		
	}
}