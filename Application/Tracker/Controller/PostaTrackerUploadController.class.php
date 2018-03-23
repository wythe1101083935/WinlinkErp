<?php
/**
 +----------------------------------------------------------
 * wythe,开发平台生成控制器模板
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:alt+t
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Controller;
use Common\Controller\CommonController;
class PostaTrackerUploadController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * createShipmentStatus index
	 +----------------------------------------------------------
	 * @param  void;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function index(){
		$this->display();
	}

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
		$postaAwb = nl2br(I('post.awbnos'));
		$postaAwb = explode('<br />',$postaAwb);
		$postaAwb = array_filter($postaAwb,function(&$v){
			if($v=trim($v)){
				return true;
			}else{
				return false;
			}
		});	
		if(count($postaAwb)){
			$return = D('PostaTracker')->pushTracker($postaAwb);							
		}else{
			$return = ReturnData(false,'没有输入订单！','','');
		}			
		$this->ajaxReturn($return);
	} 

	/*
	 +----------------------------------------------------------
	 * pushManifest Index
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function indexManifest(){
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * pushManifest
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function pushManifest(){
		set_time_limit(0);
		$return = D('PostaTracker')->pushManifest(I('post.headerData',I('post.bodyData')));
		$this->ajaxReturn($return);
	}

	/*
	 +----------------------------------------------------------
	 * special_shipment_creation
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function specialShipmentCreation(){
		D('PostaTracker')->specialShipmentCreation();
	}

	public function test(){
		libxml_disable_entity_loader(false);
		$client = D('PostaTracker')->createSoapClient('http://xmlpi.firstflightme.com/FirstFlightService.svc?wsdl');
		dump($client->__getFunctions());
		dump($client->__getTypes());
	}

	public function test1(){
		D('FirstFlightTracker')->getTrackerNew();
	}

}