<?php


namespace Tracker\Model;

use Think\Model\AdvModel;

class OrderReceiverArabicModel extends AdvModel {
	protected $tablePrefix = '';
	protected $tableName = 'AWBARABIC';//收件人信息，
	protected $connection = array(
		'db_type' => 'sqlsrv',
		'db_user' => 'query',
		'db_pwd' => 'p@ssw0rd',
		'db_host' => '47.90.46.77',
		'db_port' => 1433,
		'db_name' => 'Integra',
		'db_charset' => 'SQL_Latin1_General_CP1_CI_AS',
	);

	/*
		功能：AWBARABIC(OrderReceiverArabicModel)表插入数据
		参数：
			@param:arr $order;
			@return:string $res
	*/
	public function updateData($order,$updateSign=false){
		$exist = $this->where(array('AWBNo' => $order['awbno'],))->find();		
		if(!$exist){
			//组合AWBPrintSeq数据
			$data =$this->compileOrderReceiverArabicData($order);
			//执行数据插入操作
			try{
				$rs = $this->data($data)->add();
				if (!$rs) {
					$return = ReturnData(false,'插入AWBARABIC表错误:' . $this->getDbError(),'','');
				}else{
					$return = ReturnData(true,'插入AWBARABIC表成功!','','');
				}
			}catch(\Exception $e){
				$return = ReturnData(false,'插入AWBARABIC表异常:' . $e->getMessage(),'','');
			}
		}else{
			if($updateSign){
				//组合AWBPrintSeq数据
				$data =$this->compileOrderReceiverArabicData($order);
				//执行数据插入操作
				try{
					$rs = $this->where(array('AWBNo' => $order['awbno']))->data($data)->save();
					if (!$rs) {
						$return = ReturnData(false,'更新AWBARABIC表错误:' . $this->getDbError(),'','');
					}else{
						$return = ReturnData(true,'更新AWBARABIC表成功!','','');
					}	
				}catch(\Exception $e){
					$return = ReturnData(false,'更新AWBARABIC表异常:' . $e->getMessage(),'','');
				}				
			}else{
				$return = ReturnData(false,'FFC信息已经存在!','','');
			}
		}
		return $return;
	}

	/*
		功能：组合AWBARABIC(OrderReceiverArabicModel)表插入数据
		参数：
			@param:arr $order 原订单信息;
			@return:arr $data
	*/
	public function compileOrderReceiverArabicData($order){
		$data = array(
			'AWBNo' => $order['awbno'],
			'Consignee' => $order['consigneename'],
			'ConsigneeName' => $order['consigneename'],
			'ConsigneeAddress1' => $order['consigneeaddress1'],
			'ConsigneeAddress2' => $order['consigneeaddress2'],
		);	
		return $data;
	}
}