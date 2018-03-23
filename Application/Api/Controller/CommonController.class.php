<?php
/**
 +----------------------------------------------------------
 * API公共类
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-01-18 13:49:30
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Api\Controller;
use Think\Controller;
class CommonController extends Controller{
	/*
	 +----------------------------------------------------------
	 * API验证
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
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
}