<?php
/*
 +----------------------------------------------------------
 * 获取firstflight轨迹,主要功能：获取firstflight轨迹并更新
 +----------------------------------------------------------
 * @param  ;
 +----------------------------------------------------------
 * @return json{status:bool,msg:string,data:mix,code:int}
 +----------------------------------------------------------
*/
namespace Tracker\Model;
class FirstFlightTrackerModel extends NetModel{
	/*连接api用户名*/
	private $username = '500096';

	/*连接api密码*/
	private $password = 'w96!l7k';

	/*
	 +----------------------------------------------------------
	 * 通过api获取firstflight轨迹数据
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getTracker($awbno){
		$data = array(
			"AccountNo"=>$this->username,
			"AccountPWD"=>$this->password,
			"TrackingNo"=>$awbno			
		);
		$res = $this->HttpRequest('http://xmlpi.firstflightme.com/FirstFlightService.svc/QueryData','json','post',$data);
		$res = $this->formatterTracker($res,$awbno);
		return $res;
	}
	/*
	 +----------------------------------------------------------
	 * 格式化返回数据
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function formatterTracker($res,$awbno){
		if($res['status']){
			if($res['data']['code'] == '-1'){
				$return = ReturnData(false,$res['data']['description'],'','');
			}else{
				$data = array();
				foreach ($res['data']['trackList'] as $key => $val) {
						$data[$key]['awbno'] 	= 	$awbno;
						$s = strpos($val['Transdate'],'(')+1;
						$e = strpos($val['Transdate'],'+');
						$date = substr($val['Transdate'],$s,($e-$s-3));
						$dateTimeStr = date('Y-m-d',$date).' '.$val['TransTime'].':00';
						$dateTimeStr = mb_convert_encoding($dateTimeStr,'utf-8',mb_detect_encoding($dateTimeStr));
						$dateTime = strtotime($dateTimeStr);
						$data[$key]['time'] 	= 	$dateTime;
						$data[$key]['status']	= 	$val['Status'];
						$data[$key]['location'] = 	$val['Location'];
						$data[$key]['remarks'] 	= 	$val['Remarks'];
						$data[$key]['to'] 		= 	$val['DeliveredTo'];
					}	
				$return = ReturnData(true,$res['data']['description'],$data);
			}
		}else{
			$return = $res;
		}
		return $return;
	}
}