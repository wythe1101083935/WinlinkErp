<?php
/**
 +----------------------------------------------------------
 * Posta轨迹:
 +----------------------------------------------------------
 * CODE:842
 +----------------------------------------------------------
 * TIME:2018-01-19 11:28:50
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Model;
class PostaTrackerModel extends NetModel{

	/*
	 +----------------------------------------------------------
	 * 获取posta的轨迹
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getTracker($awbno){
		/*http://www.postaplus.net/APIService/PostaWebClient.svc?wsdl*/
		/*http://168.187.136.18:8095/APIService/PostaWebClient.svc?wsdl*/
		//$awbno = M('Awb')->where(array('awbno'=>$awbno))->getField('refCode');
		$refcode = '100027017141';
		$awbno = M('Awb')->where(array('RefCode'=>$refcode))->getField('awbno');
		dump($awbno);
		$res = $this->createSoapClient('http://www.postaplus.net/APIService/PostaWebClient.svc?wsdl');
		if($res['status']){
			$client = $res['data'];
			$data = array(
                "CodeStation" => 'DXB',
                "UserName" => 'CLD5012',
                "Password" => '123456',
                "ShipperAccount" => 'CLD5012',
                "AirwaybillNumber" => $refcode,
                "Reference1" =>'',
                "Reference2" =>'',
			);
			$result = $client->Shipment_Tracking($data);
            if($result === false){
                $return = ReturnData(false,'请求失败');
            }else{
                $return = $result->Shipment_TrackingResult->TRACKSHIPMENT; 
                $return = $this->formatterTracker($return,$awbno);
            }
		}else{
			$return = $res;
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 格式化轨迹
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function formatterTracker($res,$awbno){
		foreach ($res as $key => $val) {
			$dateTime = substr($val->DateTime,6,4).'-'.substr($val->DateTime,3,2).'-'.substr($val->DateTime,0,2).substr($val->DateTime,10);
			$data[] = array(
                'awbno'       => $awbno,
                'time'      => strtotime($dateTime),
                'status'    => $val->Event,
                'location'  => $val->Location,
                'remarks'   => $val->EventName.''.$val->Note,
                'to'        => ''
			);
		}
		if(count($data)){
			$return = ReturnData(true,'',$data);
		}else{
			$return = ReturnData(false,'posta no tracker');
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 向Posta推送轨迹
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function pushTracker($postaAwb){
		$tmpData =array();
		$i = 0;
		foreach ($postaAwb as $key => $val) {
			//$tmp = $val;
			$tmpData[$i]=array(
		        	'Connote'=>$val,
		        	'EventCode'=>'AS',
		        	'EventNote'=>'arrived at origin facility',
		        	'RackNumber'=>'',
		        	'RequestSequence'=>' '.$i,
		        	'Weight'=>0.00,
			);
			$i++;
		}
        $data = array(
        	'UserInfo'=>array(
        		'APIKey'=>'OZr7JqxkXTMVaVJaCnbvrpFM3zsDhukqT9zTWgJhUO4=',
        		'Password'=>'0147369258',
        		'UserName'=>'WINLINK'
        	),
        	'SE'=>$tmpData
        );	
        $res = $this->connectionPush($data);
        return($this->_formatterCreateShipmentMsg($postaAwb,$res));	
	}
	/*
	 +----------------------------------------------------------
	 * 发送soap请求
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function connectionPush($data){
        libxml_disable_entity_loader(false);
		try{	
			$client = new \SoapClient('http://www.postaplus.net/APIService/AgentServices.svc?wsdl',array(
				'soap_version'=>SOAP_1_1,
			));
			/*$client = new \SoapClient('http://168.187.136.18:8095/APIService/AgentServices.svc?wsdl',array(
				'soap_version'=>SOAP_1_1
			));*/
			$result = $client->createShipmentStatus($data);				
			if(is_object($result)){
				$result = object2array($result);
				$return =  ReturnData(true,'connection success',$result['CreateShipmentStatusResult'],array('send'=>$data,'response'=>$result),'10000');
			}else{
				$return = ReturnData(false,'connection error',$result,'842001');
			}	
		}catch(\Exception $e){
			$result = $e->getMessage();
			$return = ReturnData(false,'else error',$result,'842002');
		}
		return $return;			
	}
	/*
	 +----------------------------------------------------------
	 * 解析返回数据--createShipments
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function _formatterCreateShipmentMsg($postaAwb,$res){
		$rows = array();	
		if($res['status']){					
			$i = 0;
			$resmsg= $res['data']['Responses'];
			if(!isset($resmsg[0])){
				$resmsg[0] = $resmsg;
			}
			foreach ($postaAwb as $key => $val) {
				$rows[$i]['awbno'] = $val;
				$rows[$i]['status'] = $resmsg[$i]['ResponseResult'] == 'TRUE' ? 1 : 0;
				$rows[$i]['msg'] = $resmsg[$i]['ResponseResult'];
				$i++;
			}	
		}else{
			foreach ($postaAwb as $key => $val) {
				$rows[$i]['awbno'] = $val;
				$rows[$i]['status'] =0;
				$rows[$i]['msg'] = '系统请求失败！';
			}	
		}	
		return ReturnData($res['status'],$res['msg'],$rows,$res['code']);	
	}
	/*
	 +----------------------------------------------------------
	 * 推送manifest信息
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return false
	 +----------------------------------------------------------
	*/
	public function pushManifest($dataHead,$dataBody){
 		$data = $this->manifestDataFrommater($dataHead,$dataBody);
       	return ($this->connectionManifest($data));		
	}
	/*
	 +----------------------------------------------------------
	 * 推送,soap
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function connectionManifest($data){
        libxml_disable_entity_loader(false);
		try{	
			$client = new \SoapClient('http://www.postaplus.net/APIService/AgentServices.svc?wsdl',array(
				'soap_version'=>SOAP_1_1,
			));
			/*$client = new \SoapClient('http://168.187.136.18:8095/APIService/AgentServices.svc?wsdl',array(
				'soap_version'=>SOAP_1_1
			));*/
			$result = $client->Manifest($data);		
			if(is_object($result)){
				$result = object2array($result);
				//dump($result);
				$return =  ReturnData(true,$result['ManifestResult'],$result['ManifestResult'],array('send'=>$data,'response'=>$result));
			}else{
				$return = ReturnData(false,'connection error',$result,'842003');
			}	
		}catch(\Exception $e){
			$result = $e->getMessage();
			$return = ReturnData(false,'else error',$result,'842004');
		}
		return $return;			
	}

	/*
	 +----------------------------------------------------------
	 * manifest数据格式化
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return arr $data
	 +----------------------------------------------------------
	*/
	private function manifestDataFrommater($dataHead,$dataBody){
        $data = array(
        	'UserInfo'=>array(
        		'APIKey'=>'OZr7JqxkXTMVaVJaCnbvrpFM3zsDhukqT9zTWgJhUO4=',
        		'Password'=>'0147369258',
        		'UserName'=>'WINLINK'
        	),
        	'SM'=>array(
				"CarrierCode"=>$dataHead['CarrierCode'],
				"DeptDate"=>$dataHead["DeptDate"],
				"ExpectedDate"=>$dataHead["ExpectedDate"],
				"Flight1"=>$dataHead["Flight1"],
				"Flight2"=>$dataHead["Flight2"],
				"FromStation"=>'CHN',
				"Hub"=>$dataHead["Hub"],
				"LoadType"=>$dataHead["LoadType"],
				"MA"=>$dataBody,
				"MAWB"=>$dataHead["MAWB"],
				"ManifestNo"=>'',  //$dataHead["ManifestNo"]
				"NumberOfBags"=>$dataHead["NumberOfBags"],
				"Remarks"=>$dataHead["Remarks"],
				"ShippingMode"=>$dataHead["ShippingMode"],
				"ToStation"=>$dataHead['ToStation'],
				"Weight"=>$dataHead["Weight"]
        	)
        );	
        return $data;	
	}
	/*
	 +----------------------------------------------------------
	 * specialShipmentCreation
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function specialShipmentCreation(){
		$data = array(
			//"AppointmentDate"=>'dateTime',
			//"AppointmentFromTime"=>'string',
			//"AppointmentToTime"=>'string',
			"CashOnDelivery"=>0,
			"CashOnDeliveryCurrency"=>'KWD',
			"CodeCurrency"=>'KWD',
			"CodeService"=>'SRV3',
			"CodeShippmentType"=>'SHPT2',
			"ClientInfo"=>array(
				"CodeStation"=>'CHN',
				"Password"=>'123456',
				"ShipperAccount"=>'CLD5012',
				"UserName"=>'CLD5012'
			),	
			"ConnoteContact"=>array(
				'SHIPPER'=>array(
					'EMAIL'=>'shipperemail@yahoo.com'				
				),
				'RECEIVER'=>array(
					'EMAIL'=>'receiveremail@yahoo.com',
					'TELEPHONE'=>'12345678',
					'MOBILE'=>'12345678',
					'WHATSAPP'=>'12345678'
				)		
			),
			"ConnoteDescription"=>'Two sample products',
			"ConnoteInsured"=>'N',
			"ConnoteNotes"=>array(
					'NOTE1'=>'NOTE 1',
					'NOTE2'=>'NOTE 2'
			),
			"ConnotePerformaInvoice"=>array(
                0=>array(
                    'CODE_HS'=>'NA',
                    'CODE_PACKAGE_TYPE'=>'PCKT2',
                    'DESCRIPTION'=>'TEA',
                    'ORIGIN_COUNTRY_CODE'=>'ARE',
                    'QUANTITY'=>'1',
                    'UNIT_RATE'=>'1'),
                1=>array(
                    'CODE_HS'=>'NA',
                    'CODE_PACKAGE_TYPE'=>'PCKT2',
                    'DESCRIPTION'=>'SWEET CORN, UNCOOKED OR COOKED',
                    'ORIGIN_COUNTRY_CODE'=>'ARE',
                    'QUANTITY'=>'1',
                    'UNIT_RATE'=>'10')
                
            ),
			"ConnotePieces"=>1,
			"ConnoteProhibited"=>'N',
			"ConnoteRef"=>array(
				'REFERENCE1'=>'ORDERNO',
				'REFERENCE2'=>''
			),
			"Consignee"=>array(
				'SHIPPER'=>array(							
					'FULL_ADDRESS'=>'Shipper Address in Dubai',
					'FROM_AREA'=>'NA',
					'FROM_CITY'=>'CITY415680',
					'COUNTRY_CODE'=>'ARE',
					'PHONE_NO'=>'12345678',
					'SHIPPER_NAME'=>'Mr.Shipper',
					'PIN_CODE'=>'NA',
					'FROM_PROVINCE'=>'DU',
					'FROM_TELEPHONE'=>'12345678',
					'REMARKS'=>'Example request from API class'
			 	),
				'RECEIVER'=>array(				
		            'COMPANY_NAME'=>'Mr. Receiver Company',
					'TO_ADDRESS'=>'Receiver Address in Kuwait',
					'TO_AREA'=>'NA',
					'TO_CITY'=>'CITY2',
					'TO_CIVIL_ID'=>'NA',
					'TO_CODE_COUNTRY'=>'KWT',
					'TO_CODE_SECTOR'=>'NA',
					'TO_DESIGNATION'=>'Receiver Designation',
					'TO_MOBILE'=>'12345678',
					'TO_NAME'=>'Mr. ToName',
					'TO_TELEPHONE'=>'12345678',
					'TO_PIN_CODE'=>'NA',
					'TO_PROVINCE_CODE'=>'KW'
				)		
			),
			"CostShipment"=>4,
			"ItemDetails"=>array(
		            0=>array(
						'HEIGHT'=>0,
						'LENGTH'=>0,
						'WIDTH'=>0,
						'SCALE_WEIGHT'=>0,
						'WEIGHT'=>0.5
					),
		            1=>array(
						'HEIGHT'=>0,
						'LENGTH'=>0,
						'WIDTH'=>0,
						'SCALE_WEIGHT'=>0,
						'WEIGHT'=>1.5
					)
			),
			"NeedPickUp"=>'N',
			"NeedRoundTrip"=>'N',
			//"PayMode"=>'string'
		);
		$this->connectionSpeicalShipmentCreation($data);
	}
	/*
	 +----------------------------------------------------------
	 * soap推送
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function connectionSpeicalShipmentCreation($data){
        libxml_disable_entity_loader(false);
		try{		
			/*$client = new \SoapClient('http://168.187.136.18:8095/APIService/PostaWebClient.svc?wsdl',array(
				'soap_version'=>SOAP_1_1
			));*/
			$res = $this->createSoapClient('http://www.postaplus.net/APIService/PostaWebClient.svc?singleWsdl');
			if($res['status']){
				$client = $res['data'];
				//dump($client->__getFunctions());
				//dump($client->__getTypes());
				$result = $client->Special_Shipment_Package($data);	
				$request = $client->__getLastRequestHeaders();
			    $response  = $client->__getLastResponse();	
				if(is_object($result)){
					$result = object2array($result);
					dump($result);
					//$return =  ReturnData(true,$result['ManifestResult'],$result['ManifestResult'],array('send'=>$data,'response'=>$result));
				}else{
					dump($result);
					//$return = ReturnData(false,'connection error',$result,'842003');
				}	
			}else{
				$return  = $res;
			}

		}catch(\Exception $e){
			$result = $e->getMessage();
			$return = ReturnData(false,'else error',$result,'');
			dump($return);
		}
        dump($request);
        dump($response);
		//return $return;				
	}
}
/*manifest Data Formatter*/
/*	
$data = array(
        	'UserInfo'=>array(
        		'APIKey'=>'OZr7JqxkXTMVaVJaCnbvrpFM3zsDhukqT9zTWgJhUO4=',
        		'Password'=>'0147369258',
        		'UserName'=>'WINLINK'
        	),
        	'SM'=>array(
				"CarrierCode"=>"CARR44",
				"DeptDate"=>"2018-02-28",
				"ExpectedDate"=>"2018-02-28",
				"Flight1"=>"7421",
				"Flight2"=>"123",
				"FromStation"=>"CHN",
				"Hub"=>"N",
				"LoadType"=>"NA",
				"MA"=>array(
					array(
						"Airwaybill"=>"100000694886",
						"BagSerial"=>"4886"
					),
				),
				"MAWB"=>"201802284886PM",
				"ManifestNo"=>"",
				"NumberOfBags"=>"1",
				"Remarks"=>"123",
				"ShippingMode"=>"FREIGHT",
				"ToStation"=>"QAT",
				"Weight"=>"0.5"
        	)
        );
*/