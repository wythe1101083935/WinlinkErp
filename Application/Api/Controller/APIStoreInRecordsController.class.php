<?php
/**
 +----------------------------------------------------------
 * 迪拜仓库入库单（退回单记录）
 +----------------------------------------------------------
 * @func:  func(param,);
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */

namespace Store\Controller;
use Store\CommonController;
class APIStoreInRecordsController extends CommonController{
/*传入数据格式
{
	"data":["CNB3295254"]
}
*/
	public function handleStoreInRecords(){
		$postJsonString = trim(file_get_contents('php://input'));
		$postJson = json_decode($postJsonString,true);
		$awbnos = $postJson['data'];
		foreach($awbnos as $key => $value){
			$res = $this->getReturnTracker($value);
		}
		if($res['status']){
			$this->ajaxReturn(ReturnData(true,'Put Tracker Success'));
		}else{
			$this->ajaxReturn(ReturnData(false,'Put Tracker Error'));
		}
	}
	private function getReturnTracker($awbno){
		//增加一条退回到迪拜仓库的轨迹
		$res1 = M('TrackerInfo')->data(array(
			'awbno'=>$awbno,
			'create_time'=>time(),
			'status'=>'ARTO',
			'location'=>'DUBAI',
			'remarks'=>'Return,Arrived Dubai store'
		))->add();
		//更新bill表的轨迹
		/*$res2 = M('Bill')->where(array('awbno'=>$awbno))->data(array(
			'status_flag'=>'ARTO',
			'finish_remarks'=>'Return,Arrived Dubai store',
			'finish_time'=>time()
		))->save();*/
		if($res1){
			return ReturnData(true,'增加成功!');
		}else{
			return ReturnData(false,'增加失败!');
		}
	}
}