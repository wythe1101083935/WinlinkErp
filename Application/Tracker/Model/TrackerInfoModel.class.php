<?php
/**
 +----------------------------------------------------------
 * 批量写入本地轨迹，批量向FFC推送轨迹
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:alt+t
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Model;
use Think\Model;
class TrackerInfoModel extends Model{

	/*
		功能：批量增加轨迹
		参数：
			@param:arr $awbnos;
			@param:string $remarks;
			@param:string $time;
		@return:成功返回1 失败返回0
	*/
	public function trackerInfoMultiUpdate($v,$remarks,$time){
		/*1.开启事务*/
		$this->startTrans();
			/*增加轨迹*/
			$newTrackerInfo['awbno'] = $v; 
			$newTrackerInfo['create_time'] = $time; 
			$newTrackerInfo['status'] = $remarks['code']; 
			$newTrackerInfo['location'] = $remarks['city']; 
			$newTrackerInfo['remarks'] = $remarks['remarks']; 
			$newTrackerInfo['delivered_to'] = ''; 
			$newTrackerInfo['api_id'] = 0; 
			$newTrackerInfo['operation_id'] = $_GET['uid']; 
			$res1= $this->trackerInfoInsert($newTrackerInfo);	
			/*更新bill表的最新轨迹*/
			$newRemarks['finish_remarks'] = $remarks['remarks'];
			$newRemarks['status_flag'] = $remarks['code'];
			$newRemarks['update_time'] = time();	
			if($remarks['code'] == 'POD'){
				$newRemarks['finish_time'] = $time;
			}		
			$res2 = M('Bill')->where(array('awbno'=>$v))->data($newRemarks)->save();
		/*2.判断是否有写入操作，没有写入的数据有哪些列出来*/
		if($res1 && $res2){
			$this->commit();
			$return = ReturnData(true,'本地写入成功！');
		}else{
			$this->rollback();
			if(!$res1){
				$return = ReturnData(true,'已有本条轨迹','','');
			}elseif(!$res2){
				$return = ReturnData(false,'订单没有出库信息！','','');
			}else{
				$return = ReturnData(false,'未知错误！请联系管理员！','','');
			}
		}
		return $return;
	}

	/*
		功能：增加或修改单条轨迹
		参数：
			@param:string $awbno
			@return:返回影响行数
	*/
	public function trackerInfoInsert($newTrackerInfo){
		$this->create($newTrackerInfo);
		unset($newTrackerInfo['create_time']);
		unset($newTrackerInfo['operation_id']);
		if($this->where($newTrackerInfo)->count()){/*如果要增加的轨迹已经存在*/
			return $this->where($newTrackerInfo)->save();
		}else{			
			return $this->add();
		}
	}
	public function alreadyPutOrder(){
		$res = M('Bill')->join('crm_tracker_info on crm_tracker_info.awbno = crm_bill.awbno')->field('crm_bill.awbno')->where('crm_tracker_info.status in ("SFF","SAX")')->select();
		$data = array();
		foreach ($res as $key => $val) {
			$data[] = $val['awbno'];
		}
		return ReturnData(true,'',$data);
	}
	/*
	 +----------------------------------------------------------
	 * 获取面单轨迹
	 +----------------------------------------------------------
	 * @param  string $awbno 单号;
	 * @param  string $order 排序，asc desc(默认);
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function orderTracker($awbno,$order){
		$res = M('TrackerInfo')->where(array('awbno'=>$awbno))->select();
		$exceptionOrder = D('Tracker/WinlinkTrackerUpdate')->getWinlinkTracker();
		usort($res,array($this,$order));
		foreach ($res as $key => &$val) {
			$val['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
		}
		foreach ($res as $key => &$val) {
			$val['date'] = substr($val['create_time'],0,10);
			$val['time'] = substr($val['create_time'],11,5);
		}
		$arr = array();
		$i = 0;
		foreach ($res as $key => $value) {
			if($value['date'] != $arr[$i]['date']){
				$i++;
				$arr[$i]['date'] = $value['date'];
				isset($exceptionOrder[$value['remarks']]) ? $value['exception'] = 1 : $value['exception']=0	;		
				$arr[$i]['detail'][] = $value;
			}else{
				isset($exceptionOrder[$value['remarks']]) ? $value['exception'] = 1 : $value['exception']=0	;	
				$arr[$i]['detail'][] = $value;
			}
		}
		return ReturnData(true,'Success',$arr);
	}
	private function asc($a,$b){//顺序排序
		return $a['create_time'] > $b['create_time'] ? true : false;
	}
	private function desc($a,$b){//倒序排序-默认
		return $a['create_time'] > $b['create_time'] ? false : true;
	}
}