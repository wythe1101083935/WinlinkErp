<?php
/**
 +----------------------------------------------------------
 * 计算派送成本
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-08 13:41:13
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class CacuDeliveryCostController extends CommonController{
	/*
	 +----------------------------------------------------------
	 *首页展示页
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
	 * 上传体积,同时计算应收，派送成本和关税
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateVolume(){
		$val = $_POST['data'];
 		/*获取计算所需的其它参数*/
 		//$awbView = M('Awb')->field('transportMode as transportmode,ConsigneeCity as consigneecity,InAmt as inamt, ValueOfShipment as goodsvalue')->where(array('awbno'=>$val['awbno']))->find();//直接拿出申报价值
		$awbView = M('ViewBillValue')->field('transportmode,consigneecity,InAmt as inamt,sum(orderInsvalue) as goodsvalue')->where(array('awbno'=>$val['awbno']))->group('awbno,transportmode,consigneecity,inamt')->find();//计算出申报价值
		$val['transport_code'] = $awbView['transportmode'];//获取渠道代码
		$val['inamt'] = floatval($awbView['inamt']);//到付金额
		$val['consigneecity'] = $awbView['consigneecity'];
		$val['allValue'] = floatval($awbView['goodsvalue']);
		$val['cweight'] = M('Bill')->where(array('awbno'=>$val['awbno']))->getField('cweight');//获取实际重
		if(!$val['vl'] || !$val['vw'] || !$val['vh']){//若没有体积参数，设置为0
			$val['vl'] = 0;
		}
		$vweightPro = $val['vl']*$val['vw']*$val['vh'];//记录体积重，尚未除以6000或5000
		if($val['vweight'] > 0){
			$costVweight = $val['vweight'];
		}else{
			$costVweight = 0;
		}
		$user_id = M('Awb')->where(array('awbno'=>$val['awbno']))->getField('accountno');//获取用户id
		/*订单表写入体积参数--应收体积--应付体积*/
		M('Awb')->where(array('awbno'=>$val['awbno']))->data(array(
			'specifiction'=>$val['vl'].','.$val['vw'].','.$val['vh'],
			'cost_specifiction'=>$val['vl'].','.$val['vw'].','.$val['vh']
		))->save();
		/*计算应付*/ 	
		$cost_fee = D('RuleFeeData')->cacuRuleFee($user_id,$val['transport_code'],1,$val['cweight'],$vweightPro,$val['consigneecity'],$costVweight);
		/*写入数据库*/
		$res = D('RuleFeeData')->updateBillFee(array('cost_fee'=>$cost_fee),$val['awbno']);
		/*返回数据*/
		$res['awbno'] = $val['awbno'];
		$this->ajaxReturn($res);
	}
}