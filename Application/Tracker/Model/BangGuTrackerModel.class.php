<?php
/**
 +----------------------------------------------------------
 * 客户轨迹，banggu
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-03-19 09:28:12
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Model;
class BangGuTrackerModel extends NetModel{

	/*用户名*/
	private $username = 'codservice';

	/*密码*/
	private $password = 'lZxT65tKSBBqLU8FOZiHgEcj';

	/*banggu对应轨迹id*/
	private $statusFormatter = array(
		"POD"=>37,
		"RTO"=>38,
		"DS"=>38
	);

	/*
	 +----------------------------------------------------------
	 * 推送部分订单完成信息到棒谷
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function post($awbno){
		$status = M('Bill')->where(array('awbno'=>$awbno))->getField('status_flag');
		if(isset($this->statusFormatter[$status])){
			$url = sprintf('https://erpapi.banggood.cn/OrderService/OrderService.svc/UpdateSignStatus?status=%s&traceID=%s', $this->statusFormatter[$status], $awbno);
			$res = $this->HttpRequest($url,'json','get',array(),array(
				'User: '.$this->username,
				'PassWord: ' . $this->password,
			));
			if($res['status']){
				if($res['data']['IsSuccess']){
					$return = ReturnData(true,'psotToBangGu Success');
				}else{
					$return = ReturnData(false,$res['data']['Message'],'','');
				}
			}else{	
				$return = $res;
			}
		}else{
			$return = ReturnData(true,'PostBangGu do not need');
		}
		return $return;
	}
}