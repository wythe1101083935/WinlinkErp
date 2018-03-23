<?php
/**
 +----------------------------------------------------------
 * 异常订单管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-08 09:50:41
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace OrderFollowUp\Controller;
use Common\Controller\CommonController;
class ExceptionOrderController extends CommonController{
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
		$this->getExceptionStatus();
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 错误类型
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getExceptionStatus(){
		$exception = M('WinlinkTracker')->field('sort,code')->group('code,sort')->select();
		foreach ($exception as $key => $val) {
			$exceptionStatus[] = array(
				'name'=>$val['sort'],
				'range'=>$val['code']
			);
		}
    	$this->assign('exceptionStatus',$exceptionStatus);	
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
			$where['userid'] = $this->getClient();

		/*3.查看字段权限*/

		/*4.搜索判断*/
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
			if($postData['exceptionStatus']) {//按照异常类型
				$where['exception_code'] =$postData['exceptionStatus'];
			}
			if($postData['orderStatus']){//按照订单是否签收
				//$action = $postData['orderStatus']-1>0 ? 'NEQ' :'EQ';
				//$where['status'] = array($action,'POD');
				$where['exception_status'] = $postData['orderStatus']-1;
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
			$data = D('ViewExceptionOrderList')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewExceptionOrderList')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data,'test'=>$where));	
	}
}