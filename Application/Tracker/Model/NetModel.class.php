<?php
/*
 +----------------------------------------------------------
 * 网络模型基础类
 +----------------------------------------------------------
 * @param  ;
 +----------------------------------------------------------
 * @return json{status:bool,msg:string,data:mix,code:int}
 +----------------------------------------------------------
*/
namespace Tracker\Model;
class NetModel{

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
	public function HttpRequest($url,$dataType='json',$type='get',$data=array(),$header=array()){
			/*1.设置API链接HTTp请求参数*/
			$data=json_encode($data);
			$headerDefault = array(
				'Content-Type:application/json',
				'Content-Lenth:'.strlen($data),
				'Expect:',
			);
			$header = array_merge($headerDefault,$header);
			/*2.设置http协议参数,并获取数据*/
			$ch = curl_init();
			curl_setopt_array($ch,array(
				CURLOPT_URL=>$url,
				CURLOPT_HEADER=>false,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_CUSTOMREQUEST=>strtoupper($type),
				CURLOPT_POSTFIELDS=>$data,
				CURLOPT_HTTPHEADER=>$header,
				CURLOPT_SSL_VERIFYHOST=>false,
				CURLOPT_SSL_VERIFYPEER=>false,
				CURLOPT_FOLLOWLOCATION=>true,
				CURLOPT_HTTP_VERSION=>CURL_HTTP_VERSION_1_1
			));
			try{
				$data = curl_exec($ch);
				$httpcode = curl_getinfo($data,CURLINFO_HTTP_CODE);
			}catch(Exception $e){
				$msg = $e->getError();
			}	
			/*3.返回数据*/
			if(isset($msg) || $httpcode){
				return ReturnData(false,isset($msg) ? $msg:$httpcode,'','');
			}elseif($dataType == 'json'){
				return ReturnData(true,'',json_decode($data,true));	
			}elseif($dataType == 'html'){
				return ReturnData(true,'',$data);
			}elseif($dataType == 'xml'){
				$xmlstring = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA); 			 
				$val = json_decode(json_encode($xmlstring),true); 
				return ReturnData(true,'',$val);
			}
	}
	/*
	 +----------------------------------------------------------
	 * 新建soap
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
    public function createSoapClient($url){
        $context = stream_context_create(
                        array(
                            'ssl' => array(
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true
                            ),
                            'http'=>array(
                                    'user_agent' => 'PHPSoapClient'
                            )
                        ));
       try{
	        $client = new \SoapClient($url,
	                        array(
	                            'stream_context' => $context,
	                            'trace' => 1,
	                            'soap_version'   => SOAP_1_1,
	                            'style' => SOAP_DOCUMENT,
	                            'encoding' => SOAP_LITERAL,
	                            'cache_wsdl' => WSDL_CACHE_NONE
	                        ));       	
	    }catch(Exeption $e){
	    	$msg = $e->getError();
	    }
	    if(isset($msg)){
	    	$return = ReturnData(false,$msg,'');
	    }else{
	    	$return = ReturnData(true,'',$client);
	    }
        return $return;
    }	
    /*
     +----------------------------------------------------------
     * 格式化xml数据
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    protected function parseXML($data){
		$xmlstring = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring),true);  
		return $val;   	
    }
}