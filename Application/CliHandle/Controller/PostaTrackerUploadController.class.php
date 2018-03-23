<?php
/**
 +----------------------------------------------------------
 * 向post批量推送轨迹
 +----------------------------------------------------------
 * CODE:233
 +----------------------------------------------------------
 * TIME:2018-01-20 11:45:09
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace CliHandle\Controller;
use CliHandle\Controller\CommonController;
set_time_limit(1000);
//printMsg($msg,$sign='',$type='msg')
class PostaTrackerUploadController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * createShipmentStatus
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function createShipmentStatus(){
			$postaAwb = array('100025020564');
			$res = D('Tracker/PostaTracker')->pushTracker($postaAwb);		
			if($res['status']){
				$this->printMsg('Request Success:'.$res['msg'],'process','I');
				if($res['data']['Responses']['Response'] == 'TRUE'){
					$this->printMsg('push Success:'.$res['data']['Responses']['ResponseMessage'],'process','II');
				}else{
					$this->printMsg('push Fialure:'.$res['data']['Responses']['ResponseResult'],'process','II');
				}
			}else{
				$this->printMsg('Request Failure:'.$res['msg'].'--'.$res['data'],'process',$res['code']);
			}
	}
	/*
	 +----------------------------------------------------------
	 * manifest
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function pushManifest($postaAwb){
		$this->printMsg('push Posta Tracker '.$postaAwb,'start');
			//$res = D('Tracker/PostaTracker')->pushTracker($postaAwb);
			$res = D('Tracker/PostaTracker')->pushManifest();
			if($res['status']){
				$this->printMsg('Request Success:'.$res['msg'],'process','I');
				$this->printMsg('push maniFest:'.$res['data'],'process','II');
			}else{
				$this->printMsg('Request Failure:'.$res['msg'].'--'.$res['data'],'process',$res['code']);
			}
		$this->printMsg('push Posta Tracker '.$postaAwb,'end');
	}	


}