<?php
/**
 +----------------------------------------------------------
 * 已出库订单管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-05 10:11:11
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Order\Controller;
use Common\Controller\CommonController;
class OrderController extends CommonController{
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
			$where['userid'] = $this->getClient();
		/*3.查看字段权限*/

		/*4.搜索判断*/		
			if($postData['country']){//按照国家
				$where['consigneecountry'] = $postData['country'];
			}
			$queries = nl2br($postData['awbnos']);
			$queries = explode('<br />',$queries);
			$queries = array_filter($queries,function(&$v){
				if($v=trim($v)){
					return true;
				}else{
					return false;
				}
			});	
			if(count($queries) && $postData['noType']){//按照订单号			
				$where[$postData['noType']] = array('IN', $queries);//设置搜索条件
			 	$queries = implode($queries, ',');
			 	$orderAwbno = "INSTR(',".$queries.",',CONCAT(',',".$postData['noType'].",','))";//设置排序规则
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
			if($postData['timeUpdate']){//更新时间
				$limitTime = explode('~',$postData['timeUpdate']);
				foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				$where['update_time'] = array('BETWEEN',$limitTime);
			}
			if($postData['orderStatus']){
				$where['status_flag'] = array($postData['orderStatus']['action'],$postData['orderStatus']['range']);
			}
			$test = 0;
			if($postData['noPut'] == 1){
				$already = D('Tracker/TrackerInfo')->alreadyPutOrder();
				$alreadyPut = $already['data'];
				$map['awbno'] = array('NOT IN',$alreadyPut);
				$where['_complex'] = $map;
				$test = count($alreadyPut);
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
				$postData['pageSize'] = 6000;
			}		
			$data = D('ViewOrderList')->field("id,time,username,awbno,weight,userrefid,cweight,refcode,shipperref,transer_fee,cost_fee,status_flag,finish_time,update_time,finish_remarks,(finish_time-time) as total_finish_days,
		(case when finish_time-time>0 then finish_time-time else ".time()."-time end) as total_now_days")->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewOrderList')->where($where)->count();
		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data,'test'=>$test));	
	}

	/*
	 +----------------------------------------------------------
	 * 更新轨迹
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateTracker($awbno){
		$res = D('Tracker/WinlinkTracker')->updateAuto($awbno,false,true,true);
		$this->ajaxReturn($res);
	}

}