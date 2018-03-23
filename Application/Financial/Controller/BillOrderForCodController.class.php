<?php
/**
 +----------------------------------------------------------
 * COD结算管理
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
class BillOrderForCodController extends CommonController{
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
				$limitTime = explode('~',$postData['timeFinish']);
				foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				$where['finish_time'] = array('BETWEEN',$limitTime);
			}
			if($postData['cod_status']){//结算
				$where['cod_status'] = $postData['cod_status']-1;
			}
			if($postData['cod_ncnd_status']){//收款
				$where['cod_ncnd_status'] = $postData['cod_ncnd_status']-1;
			}
			if($postData['status_flag']==1){
				$where['status_flag'] = 'POD';
			}elseif($postData['status_flag']==2){
				$where['status_flag'] = array('NOT IN','POD,DS,RTO,CTO,ARTO');
			}elseif($postData['status_flag']==3){
				$where['status_flag'] = array('IN','DS,RTO,CTO,ARTO');
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
			if(isset($orderAwbno)){
				$order = $orderAwbno;
			}

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 10000;
			}
			$data = D('ViewBillOrderForCod')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewBillOrderForCod')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}

	/*出单、结算,出单、付款确认*/
	public function confirmCod(){
		if(IS_POST){	
			if($_POST['type']=='cod_1'){//结算出单确认
				$data = array('cod_status'=>1,'cod_1_time'=>time());
				$where['cod_status'] = 0;
				$msg = '结算出单确认';
			}elseif($_POST['type']=='cod_9'){//结算确认
				$data = array('cod_status'=>9,'cod_9_time'=>time());
				$where['cod_status'] = 1;
				$msg = '结算确认';
			}elseif($_POST['type']=='ncnd_cod_1'){//付款出单确认
				$data = array('cod_ncnd_status'=>1,'cod_ncnd_1_time'=>time());
				$where['cod_ncnd_status'] =0;
				$msg = '付款出单确认';
			}elseif($_POST['type']=='ncnd_cod_9'){//付款确认
				$data = array('cod_ncnd_status'=>9,'cod_ncnd_9_time'=>time());
				$where['cod_ncnd_status'] = 1;
				$msg = '付款确认';
			}else{
				$this->ajaxReturn(ReturnData(false,'未知错误！'));
			}
			$where['id'] = array('IN',I('post.data'));
			$res = M('Bill')->where($where)->data($data)->save(); 
			if($res){
				$this->ajaxReturn(ReturnData(true,$msg.'成功！'));
			}else{
				$this->ajaxReturn(ReturnData(false,$msg.'失败！已确认或尚未出单！'));
			}
			
			$this->ajaxReturn($_POST);
		}
	}
}