<?php
/**
 +----------------------------------------------------------
 * crm通用函数库
 +----------------------------------------------------------
 * @func:;
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */

/*
 +----------------------------------------------------------
 * crm通用http请求，用来获取api数据
 +----------------------------------------------------------
 * @param string $url ;
 +----------------------------------------------------------
 * @param string $type ;
 +----------------------------------------------------------
 * @param arr $data http请求的参数;
 +----------------------------------------------------------
 * @param arr $header array('type'=>可以选择默认的两种类型，如果选择了1，需要传递另一个参数确定User-Auth) ;
 +----------------------------------------------------------
 * @return arr  $data
 +----------------------------------------------------------
*/
function HttpRequest($url,$dataType='json',$type='get',$data=array(),$headerSgin=array('type'=>0)){
		/*1.设置API链接HTTp请求参数*/
		$data=json_encode($data);
		if($headerSgin['type'] == 0){	//普通请求	
			$header = array(
					'Content-Type:application/json',
					'Content-Lenth:'.strlen($data),
					'Expect:',
				);
		}elseif($headerSgin['type'] == 1){//带有User-Auth参数的请求
			$header = array(
					'Content-Type:application/json',
					'Content-Lenth:'.strlen($data),
					'User-Auth:'.$headerSgin['User-Auth'],
					'Expect:',
				);			
		}else{//自定义头信息
			$header = $headerSign['header'];					
		}	
		//curl_setopt($curl, , 1)
		/*2.设置http协议参数,并获取数据*/
		$ch = curl_init();
		curl_setopt_array($ch,array(
			CURLOPT_URL=>$url,
			CURLOPT_HEADER=>false,
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_POSTFIELDS=>$data,
			CURLOPT_HTTPHEADER=>$header,
			CURLOPT_SSL_VERIFYHOST=>false,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_FOLLOWLOCATION=>true,
			CURLOPT_HTTP_VERSION=>CURL_HTTP_VERSION_1_1
		));
		if($type == 'post'){
			curl_setopt(CURLOPT_CUSTOMREQUEST,$type);
		}
		$data = curl_exec($ch);
		/*3.返回数据*/
		if($dataType == 'json'){
			return json_decode($data,true);	
		}elseif($dataType == 'html'){
			return $data;
		}elseif($dataType == 'xml'){
			$xmlstring = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA); 			 
			$val = json_decode(json_encode($xmlstring),true); 	 
			return $val; 
			//return simplexml_load_file($data)
		}else{
			return $data;
		}
}
/*
 +----------------------------------------------------------
 * 返回数据标准格式函数
 +----------------------------------------------------------
 * @param  ;
 +----------------------------------------------------------
 * @return json{status:bool,msg:string,data:mix,code:int}
 +----------------------------------------------------------
*/
function ReturnData($status,$msg,$data=0,$code=10000){
	$res['status'] = $status;
	$res['msg'] = $msg;
	$res['data'] = $data;
	$res['code'] = $code;
	return $res;
}

/*
 +----------------------------------------------------------
 * 返回数据相加
 +----------------------------------------------------------
 * @param  ;
 +----------------------------------------------------------
 * @return json{status:bool,msg:string,data:mix,code:int}
 +----------------------------------------------------------
*/
function ReturnDataAdd($return1,$return2){
	return ReturnData($return1['status'] && $return2['status'],$return1['msg'].'|'.$return2['msg'],array($return1,$return2),20000);
}