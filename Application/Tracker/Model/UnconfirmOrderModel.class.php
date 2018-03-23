<?php


namespace Tracker\Model;

use Think\Model\AdvModel;

class UnconfirmOrderModel extends AdvModel {
	protected $tablePrefix = '';
	protected $tableName = 'AWBPrintseq';//订单信息（含收件人信息，不支持阿语）
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
		功能：AWBPrintSeq(UnconfirmOrdermodel)表插入数据
		参数：
			@param:arr $order;
			@return:string $res
	*/
	public function updateData($order,$orderTackle,$orderDetail,$updateSign=false){
		$exist = $this->where(array('AWBNo' => $order['awbno'],))->find();
		if(!$exist){//不存在执行插入操作
			//组合AWBPrintSeq数据
			$data =$this->compileUnconfirmOrderData($order,$orderTackle,$orderDetail);
			//执行数据插入操作
			$res = array();
			try{
				$rs = $this->data($data)->add();
				if (!$rs) {
					$return = ReturnData(false,'插入AwbPrintSeq表错误:' . $this->getDbError(),'','');
				}else{
					$return = ReturnData(true,'插入AwbPrintSeq表成功','','');
				}
			}catch(\Exception $e){
				$return = ReturnData(false,'插入AwbPrintSeq表异常:' . $e->getMessage(),'','');
			}
		}else{//存在根据updateSign执行更新操作
			$res = array();
			if($updateSign){//若真执行更新
				//组合AWBPrintSeq数据
				$data =$this->compileUnconfirmOrderData($order,$orderTackle,$orderDetail);
				//执行数据插入操作		
				try{
					$rs = $this->where(array('AWBNo' => $order['awbno']))->data($data)->save();
					if (!$rs) {
						$return = ReturnData(false,'更新AwbPrintSeq表错误:' . $this->getDbError(),'','');
					}else{
						$return = ReturnData(true,'更新AwbPrintSeq表成功','','');
					}
				}catch(\Exception $e){
					$return = ReturnData(false,'更新AwbPrintSeq表异常:' . $e->getMessage(),'','');
				}
			}else{
				$return = ReturnData(false,'AwbPrintSeq表信息已存在','','');
			}
		}
		return $return;

	}


	/*
		功能：组合AWBPrintSeq(UnconfirmOrdermodel)表插入数据
		参数：
			@param:arr $order 原订单信息;
			@return:arr $data
	*/
	public function compileUnconfirmOrderData($order,$orderTackle,$orderDetail){
		$data = array(
			'AWBNo' => $order['awbno'],
			'ConsMob' => $order['consigneephone'],
			'DOX' => $order['product'],
			'Branch' => 'CZX',
			'BatchNo' => '1002',
			'PickedupBy' => ' ',
			'PickupDate' => date("Y-m-d") . " 00:00:00.000",
			'SAccountNo' => '1002',
			'Origin' => 'CZX',
			'Destination' => $order['destination'],
			'SubCode' => ' ',
			'State' => $order['consigneecity'],
			'ZipCode' =>123456,
			'NoofPieces' => $order['noofpieces'],
			'PaymentMethod' => 'AC',
			'Shipper' => $orderTackle['shipper'],
			'ShipperName' => $orderTackle['shipperName'],
			'ShipperAddress1' => $orderTackle['shipperAdd1'],
			'ShipperAddress2' => ' ',
			'ShipperCity' => 'CZX',
			'ShipperPhone' => $order['shipperphone'],
			'ShipperCountry' => 'CN',
			'ShipperFax' => $order['shipperfax'],
			'Consignee' => $orderTackle['consigneeName'],
			'ConsigneeName' => $orderTackle['consigneeName'],
			'ConsigneeAddress1' => $orderTackle['consigneeAddress1'],
			'ConsigneeAddress2' => ' ',
			'ConsigneeCity' => $order['consigneecity'],
			'ConsigneeCountry' => $order['consigneecountry'],
			'ConsigneeFax' => $order['consigneephone'],
			'GoodsDesc' => $orderDetail['goodsdesc'],
			'Amount' => 0,
			'BillDate' => date("Y-m-d") . " 00:00:00.000",
			'CTAG' => 'Y',
			'AccountNo' => '1002',
			'ExportStatus' => 'Y',
			'PickupTime' => date("H:i"),
			'SpecialInstruct' => $order['customnote'],
			'ConsigneePhone' => $order['consigneephone'],
			'Weight' => floatval($order['weight']),
			'arb' => '0',
			'ServiceType' => $order['servicetype'],
			'InAmt' => floatval($order['inamt']),
			'AgentCode' => $order['consigneecountry'] == "SA" ? "NX" : "FF",
			'MAWBNo' => $order['refcode'],
			'ValueCurrency' => 'USD',
			'ValueOfShipment' => $orderDetail['goodsvalues'],
			'ShipperRef' => $order['shipperref'],
			'AgentWeight' => floatval($order['weight']),
		);
		return $data;		
	}



}