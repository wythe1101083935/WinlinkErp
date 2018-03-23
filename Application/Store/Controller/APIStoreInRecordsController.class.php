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
use Think\Controller;
class APIStoreInRecordsController extends Controller{
	public function _initialize(){
		$postJsonString = trim(file_get_contents('php://input'));
		$requestMethod = trim($_SERVER['REQUEST_METHOD']) == 'POST' ? true : false;
		$contentType = strtolower($_SERVER['CONTENT_TYPE']);
		$headers = getallheaders();
		$json = json_decode($postJsonString, true);//解析json数据
		$userAuth = $headers['User-Auth'];
		if (!stristr($contentType, 'application/json')) {
			$return = array('status'=>false,'code'=>'100301','msg'=>'请求的ContentType只能为application/json类型!');
		}elseif ($requestMethod === false) {
			$return = array('status'=>false,'code'=>'100302','msg'=>'请求的方法只能为POST!');
		}elseif (!$postJsonString) {
			$return = array('status'=>false,'code'=>'100303','msg'=>'空白的JSON数据提交!');
		}elseif (json_last_error()) {
			$return = array('status'=>false,'code'=>'100304','msg'=>$this->jsonError(json_last_error()));
		}elseif (!array_key_exists('User-Auth', $headers)) {
			$return = array('status'=>false,'code'=>'100305','msg'=>'未找到授权码！');
		}elseif ($userAuth!='winlinkStore1101'){
			$return = array('status'=>false,'code'=>'100306','msg'=>'指定的授权码不存在！');
		}elseif(!array_key_exists('data',$json)){
			$return = array('status'=>false,'code'=>'100307','msg'=>'未找到指定的处理数据！data键名');
		}else{
			$return = array('status'=>true);
		}
		if(!$return['status']){
			$this->ajaxReturn($return);
			exit();
		}		
	}
/*
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