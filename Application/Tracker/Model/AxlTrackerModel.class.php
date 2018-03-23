<?php
/**
 +----------------------------------------------------------
 * AXL轨迹获取更新
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-03-16 09:23:48
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Model;
class AxlTrackerModel extends NetModel{
	/*AXL用户名*/
	private $username = '11149';

	/*AXL密码*/
	private $password = '11149';

	/*
	 +----------------------------------------------------------
	 * 获取轨迹信息
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
    public function getTracker($awbno) {
        libxml_disable_entity_loader(false);
    	$res = $this->createSoapClient('http://www.wsdl.integraontrack.com/AXLTrack.asmx?WSDL');
    	if($res['status']){
    		$client = $res['data'];
	    	$data = array(
	            'AwbNo'=>$awbno,
	            'AccountNo'=>$this->username,
	            'AccountPWD'=>$this->password
	    	);
	    	$result = $client->LogData($data);	
            if($result === false){
                $return = ReturnData(false,'请求失败');
            }else{
                $return = $this->parseXML($result->LogDataResult->any); 
                $return = $this->formatterTracker($return['NewDataSet']['Table'],$awbno);
            }
    	}else{
    		$return = $res;
    	}
        return $return;
    }	
    /*
     +----------------------------------------------------------
     * 格式化获取的数据
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function formatterTracker($res,$awbno){
        $data = array();
        foreach ($res as $key => $val) {
            $date = substr($val['Transdate'],0,10);
            $dateTimeStr = $date.' '.$val['TransTime'].':00';
            $data[] = array(
                'awbno'       => $awbno,
                'time'      => strtotime($dateTimeStr)+3600*4,
                'status'    => trim($val['Status']),
                'location'  => trim(strtoupper($val['Location'])),
                'remarks'   => trim(ucfirst($val['Remarks'])),
                'to'        => trim($val['DeliveredTo']) ? trim($val['DeliveredTo']) : ''
            );
        }
        if(count($data)){
            $return = ReturnData(true,'AXL Tracker succss',$data);
        }else{
            $return = ReturnData(false,'AXL Tracker is null','','')
        }
        return $return;
    }
}