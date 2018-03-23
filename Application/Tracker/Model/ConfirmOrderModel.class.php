<?php
namespace Tracker\Model;

use Think\Model\AdvModel;

class ConfirmOrderModel extends AdvModel {
	protected $tablePrefix = '';
	protected $tableName = 'AWB'; //订单信息表（确认）
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
		功能：AWB(ConfirmOrder)表插入数据
		参数：
			@param:arr $order;
			@return:string $res
	*/
	public function updateData($order,$orderTackle,$orderDetail,$billTableData,$updateSign=false){
		$exist = $this->where(array('AWBNo' => $order['awbno']))->find();
		if(!$exist){
			//组合AWBPrintSeq数据
			$data =$this->compileConfirmOrderData($order,$orderTackle,$orderDetail,$billTableData);
			$res = array();
			//执行数据插入操作
			try{
				$rs = $this->data($data)->add();
				if (!$rs) {
					$return = ReturnData(false,'插入AWB(ConfirmOrder)表错误:' . $this->getDbError(),'','');
				}else{
					$return = ReturnData(true,'插入AWB表成功!','','');
				}
			}catch(\Exception $e){
				$return = ReturnData(false,'插入AWB(ConfirmOrder)表异常:' . $e->getMessage(),'','');
			}			
		}else{
			if($updateSign){
				//组合AWBPrintSeq数据
				$data =$this->compileConfirmOrderData($order,$orderTackle,$orderDetail,$billTableData);
				$res = array();
				//执行数据插入操作
				try{
					$rs = $this->where(array('AWBNo' => $order['awbno']))->data($data)->save();
					if (!$rs) {
						$return = ReturnData(false,'更新AWB(ConfirmOrder)表错误:' . $this->getDbError(),'','');
					}else{
						$return = ReturnData(true,'更新AWB成功','','');
					}
				}catch(\Exception $e){
					$return = ReturnData(false,'更新AWB(ConfirmOrder)表异常:' . $e->getMessage(),'','');
				}				
			}else{
				$return = ReturnData(false,'AWB表信息已存在','','');
			}
		}
		return $return;
	}

	/*
		功能：组合AWB(ConfirmOrder)表插入数据
		参数：
			@param:arr $order,$orderTackle,$orderDetail,$billTableData 原订单信息;
			@return:arr $data
	*/
	public function compileConfirmOrderData($order,$orderTackle,$orderDetail,$billTableData){	
		$data = array(
			'AWBNo' => $order['awbno'],
			'BillDate' => date("Y-m-d") . " 00:00:00.000",
			'AccountNo' => '1002',
			'SubCode' => ' ',
			'ShipperRef' => $order['shipperref'],
			'Origin' => 'CZX',
			'Destination' => $order['destination'],
			'DOX' => $order['product'],
			'NoofPieces' => $order['noofpieces'],
			'Weight' => floatval($billTableData['cweight']),
			'PaymentMethod' => 'AC',
			'Dimension' => ' ',
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
			'ConsigneePhone' => $order['consigneephone'],
			'GoodsDesc' => $orderDetail['goodsdesc'],
			'SpecialInstruct' => $order['customnote'],
			'Amount' => 0,
			'Tariff' => 0,
			'CTAG' => 'Y',
			'ATAG' => 'N',
			'MTAG' => 'N',
			'STAG' => 'N',
			'GTAG' => 'N',
			'DTAG' => 'N',
			'UTAG' => 'N',
			'PTAG' => 'N',
			'Route' => ' ',
			'Courier' => ' ',
			'DeliverySheetPrinted' => ' ',
			'AWBType' => ' ',
			'EXPR1' => ' ',
			'EXPR2' => ' ',
			'EXPR3' => ' ',
			'Department' => ' ',
			'BookingRefNo' => ' ',
			'ValueOfShipment' => $orderDetail['goodsvalues'],
			'ValueCurrency' => 'USD',
			'DSNo' => ' ',
			'DSNoBarCode' => ' ',
			'PickupDate' => date("Y-m-d") . " 00:00:00.000",
			'PickupTime' => date("H:i"),
			'DelivStatus' => ' ',
			'RouteAllocatedDate' => null,
			'ServiceType' => $order['servicetype'],
			'Cancelled' => 'N',
			'Branch' => 'CZX',
			'AgentCode' => $order['consigneecountry'] == "SA" ? "NX" : "FF",
			'RcptNo' => ' ',
			'ExportStatus' => 'Y',
			'AgentWeight' => floatval($billTableData['cweight']),
			'InvoicedYN' => 'N',
			'PickedupBy' => ' ',
			'DsRowNo' => 0,
			'State' => $order['consigneecity'],
			'ZipCode' => 123456,
			'RRCAWBNo' => ' ',
			'Processed' => 'N',
			'Rcvd' => ' ',
			'ConsID' => ' ',
			'MobileOrEmail' => ' ',
			'branch1' => ' ',
			'DSRP' => 'N',
			'arb' => 0,
			'MAWBNo' => $order['refcode'],
			'MPS' => 0,
			'ms' => 0,
			'BatchNo' => '1002',
			'Cod' => 0,
			'COP' => 0,
			'FAgent' => ' ',
			'ConsMob' => $order['consigneephone'],
			'ConsMail' => ' ',
			'SAccountNo' => '1002',
			'Mode' => ' ',
			'RunBr' => ' ',
			'ExpBr' => 0,
			'BBatch' => ' ',
			'InsRef' => ' ',
			'InsProv' => ' ',
			'InsValue' => 0,
			'OthChrg' => 0,
			'InAmt' => floatval($order['inamt']),
		);		
		return $data;
	}
}