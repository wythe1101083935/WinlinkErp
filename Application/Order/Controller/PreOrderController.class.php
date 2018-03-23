<?php
/**
 +----------------------------------------------------------
 * 订单管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-05 17:51:56
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Order\Controller;
use Common\Controller\CommonController;
class PreOrderController extends CommonController{
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
		$this->getAwbnoStatusWinlink();//状态列表
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
			$where['AccountNo'] = $this->getClient();
		/*3.查看字段权限*/

		/*4.搜索判断*/			
			if($postData['timeCreate']){
				$limitTime = explode('~',$postData['timeCreate']);
				foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				$where['billdate'] = array('BETWEEN',$limitTime);
			}
			if($postData['serviceType']){
				$where['ServiceType'] = $postData['serviceType'];
			}
			if($postData['shipperName']){
				$where['shippername'] = array('like','%'.$postData['shipperName'].'%');
			}
			if($postData['country']){
				$where['ConsigneeCountry'] = $postData['country'];
			}
			if($postData['noType'] && $postData['orderNos']){
				$queries = nl2br($postData['orderNos']);
				$queries = explode('<br />',$queries);
				$queries = array_filter($queries,function(&$v){
					if($v=trim($v)){
						return true;
					}else{
						return false;
					}
				});				
				$where[$postData['noType']] = array('IN', $queries);//设置搜索条件
			 	$queries = implode($queries, ',');
			 	$orderAwbno = "INSTR(',".$queries.",',CONCAT(',',".$postData['noType'].",','))";	
			}
			if($postData['orderStatus']){
				$where['is_confirm'] = array($postData['orderStatus']['action'],$postData['orderStatus']['range']);
			}

		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
			if(isset($orderAwbno)){
				$order = $orderAwbno;
			}
		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('Awb')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('Awb')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}

	/*
	 +----------------------------------------------------------
	 * 确认订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function confirm(){
		$this->ajaxReturn(D('Awb')->confirmOrder(I('post.awbnos')));
	}

	/*
	 +----------------------------------------------------------
	 * printQueen
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function printQueen($awbno){
		$filePDFArray = D('Awb')->pdfLink($awbno);
		$this->assign("links", $filePDFArray);
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 获取订单轨迹并展示
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function orderTracker($awbno,$order='desc'){
		$res = D('Tracker/WinlinkTrackerUpdate')->updateAuto($awbno,false,true,true);
		$data = D('Tracker/TrackerInfo')->OrderTracker($awbno,$order);
		$orderParam = array('asc'=>array('START','END','&#xe61a;','desc'),'desc'=>array('END','START','&#xe619;','asc'));
		$tracker = $data['data'];
		$this->assign('order',$orderParam[$order]);
		$this->assign('tracker',$tracker);
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 获取用来打印账单的信息，打印由前端操作
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getOrderInfo(){
		$awbnoInfo = M('Awb')->where(array('awbno'=>I('post.awbno')))->find();
		$awbnoInfo['stringTime'] = date('Y-m-d', $awbnoInfo['billdate']);
		$awbnoInvoice = M('Invoice')->where(array('awbid'=>$awbnoInfo['id']))->select();
		foreach ($awbnoInvoice as $key => &$val) {
			$val['totalvalue'] = $val['orderinsvalue']*$val['orderpcs'];
		}
		if(!empty($awbnoInfo) && !empty($awbnoInvoice)){
			$return = ReturnData(true,'',array('awbnoInfo'=>$awbnoInfo,'awbnoInvoice'=>$awbnoInvoice),'');
		}else{
			$return = ReturnData(false,'订单信息错误！','','');
		}
		$this->ajaxReturn($return);		
	}
	/*
	 +----------------------------------------------------------
	 * 获取订单详情并展示,未确认的可以编辑
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function orderDetail($awbno){
		$res = D('Awb')->getOrderDetail($awbno);
		if($res['formData']['is_confirm']>0){
			$res['status'] = false;
		}else{
			$res['status'] = true;
		}
		$this->assign('AllData',str_replace('\n','<br>',json_encode($res)));
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 更新订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateOrder(){
		$return = D('Awb')->updateOrder(I('post.'));
		$this->ajaxReturn($return);
	}
}