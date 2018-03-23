<?php
/**
 +----------------------------------------------------------
 * 客户报价管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:alt+t
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class QuotePriceForClientController extends CommonController{
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
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 首页ajax获取数据页
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{rows:array,count:int}
	 +----------------------------------------------------------
	*/
	public function XHRIndex(){
		/*1.获取post传入数据,content-type不是multipart/form-data*/
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	
		/*2.特殊权限*/
		/*3.查看字段权限*/
		/*4.搜索判断*/
			$where = array();
			if($postData['awbno']=trim($postData['awbno'])){
				$where['username|email|customer_company|customer_name|customer_tel'] = array('like','%'.$postData['awbno'].'%');
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('ViewUserClient')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewUserClient')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
	/*
	 +----------------------------------------------------------
	 * 用户报价页面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function quotePrice($id){	
		$this->getQuotePrice($id);	
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 获取所有报价
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return 
	 +----------------------------------------------------------
	*/
	public function getQuotePrice($id){
		$transportMode = D('Order/CountryCode')->getTransportModeList();//获取渠道代码
		$transportModeTranser = D('Order/CountryCode')->getCodeUserFee($transportMode,0,$id);//获取应收报价列表，带出默认值
		$this->assign('transer',json_encode($transportModeTranser));
		$transportModeCost = D('Order/CountryCode')->getCodeUserFee($transportMode,1,$id);//获取应付报价列表，带出默认值	
		$this->assign('cost',json_encode($transportModeCost));
	}
	/*
	 +----------------------------------------------------------
	 * 修改报价
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateQuotePrice(){
		$res = D('RuleFeeUser')->updateUserRule(I('post.id'));
		$this->ajaxReturn($res);
	}
}