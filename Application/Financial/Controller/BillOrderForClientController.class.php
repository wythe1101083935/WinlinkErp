<?php
/**
 +----------------------------------------------------------
 * 客户账单管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-08 16:38:20
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class BillOrderForClientController extends CommonController{
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
		$this->getDays();//时间列表
		$this->getAwbnoStatus();//状态列表
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
			if($postData['country']){//按照国家
				$where['consigneecountry'] = $postData['country'];
			}
			$queries = nl2br($postData['awbno']);
			$queries = explode('<br />',$queries);
			$queries = array_filter($queries,function(&$v){
				if($v=trim($v)){
					return true;
				}else{
					return false;
				}
			});	
			if(count($queries)){//按照订单号			
				$where['awbno'] = array('IN', $queries);//设置搜索条件
			 	$queries = implode($queries, ',');
			 	$orderAwbno = "INSTR(',".$queries.",',CONCAT(',',awbno,','))";//设置排序规则
			}
			if($postData['username']){//按照用户
				$where['username'] = array('like','%'.$postData['username'].'%');
			}
			if($postData['timeOut']){//出库时间
				$limitTime = explode('~',$postData['timeOut']);
				if(strpos($limitTime[0],':')){
					foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				}
				$where['time'] = array('BETWEEN',$limitTime);
			}
			if($postData['timeFinish']){//完成时间
				$limitTime = explode('-',$postData['timeFinish']);
				foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				$where['finish_time'] = array('BETWEEN',$limitTime);
			}
			if($postData['orderStatus']){
				$where['status_flag'] = array($postData['orderStatus']['action'],$postData['orderStatus']['range']);
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
			/*如果是按照订单号查询，按照订单输入顺序排序*/
			if(isset($orderAwbno)){
				$order = $orderAwbno;
			}

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('ViewOrderList')->field('id,time,username,awbno,weight,userrefid,cweight,cost_vweight,bill_cacuweight,vweight,refcode,transer_fee,transer_fee_unit,cost_fee,consigneecountry,cost_fee_unit,tariff_fee,tariff_fee_unit,status_flag,finish_time,update_time,cost_cacuweight,consigneecity')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewOrderList')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
}