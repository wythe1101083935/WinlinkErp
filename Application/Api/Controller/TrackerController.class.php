<?php
/**
 +----------------------------------------------------------
 * 轨迹同步更新
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-03-20 09:27:22
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Api\Controller;
class TrackerController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * 直接调用更新轨迹模型更新
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function index(){
		$postJsonString = trim(file_get_contents('php://input'));
		$json = json_decode($postJsonString, true);//解析json数据
		$awbno = $json['data'];
		$res = D('Tracker/WinlinkTrackerUpdate')->updateAuto($awbno,false,true,true);
		$this->ajaxReturn($res);
	}
}