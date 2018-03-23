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
namespace FinancialStatement\Controller;
use Common\Controller\CommonController;
class ReceivableStatementController extends CommonController{
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
	 * 首页ajax获取数据页
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{rows:array,count:int}
	 +----------------------------------------------------------
	*/
	public function XHRIndex(){
		$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	
		$whereUser['id'] = array('NOT IN','114,92');
		$whereUser['status'] = 1;
		/*whereUser*/
		$clients = M('ViewUserClient')->field('id,username,discount')->where($where)->page($postData['pageNumber'],$postData['pageSize'])->select();
		$count = M('ViewUserClient')->field('id,username,discount')->where($where)->page($postData['pageNumber'],$postData['pageSize'])->count();
		$rate = D('Financial/rate')->getRate();
		$whereBill = array();
		$whereBill['time'] = array('gt',1519833600);
		$allMoney = array(
			'totalTranserFee'=>0,
			'totalDiscountReceivable'=>0,
			'totalConfirmReceivable'=>0,
			'totalAlreadyReceivable'=>0,
			'TotalDebit'=>0

		);
		$whereBatch = array();
		foreach ($clients as $key => &$val) {
			/*打折前应收*/
			if($val['id'] == 49){
				$whereBill['accountno'] = array('IN','49,114');//棒谷两个账号
			}elseif($val['id'] == 89){
				$whereBill['accountno'] = array('IN','89,92');//fodel两个账号
			}else{
				$whereBill['accountno'] = $val['id'];
			}
			$total = M('Bill')->field('sum(transer_fee) as transer_fee,sum(tariff_fee) as tariff_fee')->where($whereBill)->find();
			$unit = M('Bill')->field('transer_fee_unit')->where($whereBill)->select();
			foreach ($unit as $k => $v) {
				if($v['transer_fee_unit']){
					 $val['unit']= $v['transer_fee_unit'];
					break;
				}
			}
			$totalTranserFee = $total['transer_fee'];
			$totalTariffFee = $total['tariff_fee'];
			$val['total_transer_fee'] = $totalTranserFee+$totalTariffFee;
			/*打折后估算应收*/
			$val['discountReceivable'] = $totalTranserFee*$val['discount']+$totalTariffFee;

			/*确认款项*/
			$whereBatch['user_id'] = $val['id'];
			$val['confirmReceivable'] = M('Batch')->where($whereBatch)->getField('sum(batch_receivable)');

			/*已收*/
			$batch_id = M('Batch')->where($whereBatch)->getField('batch_id',true);
			if(!empty($batch_id)){
				$whereReceivable['batch_id'] = array('IN',$batch_id);
			}else{
				$whereReceivable['batch_id'] = 0;
			}
			$val['alreadyReceivable'] = M('BatchReceivable')->where($whereReceivable)->getField('sum(receivable_account)');
			/*欠款*/
			$val['arrearage'] = $val['confirmReceivable']-$val['alreadyReceivable'] ;

			/*累加总数*/
			$allMoney['totalTranserFee'] += $rate[$val['unit'].'2RMB_rate']*$val['total_transer_fee'];
			$allMoney['totalDiscountReceivable'] += $rate[$val['unit'].'2RMB_rate']*$val['discountReceivable'];
			$allMoney['totalConfirmReceivable'] += $rate[$val['unit'].'2RMB_rate']*$val['confirmReceivable'];
			$allMoney['totalAlreadyReceivable'] += $rate[$val['unit'].'2RMB_rate']*$val['alreadyReceivable'];
			$allMoney['totalDebit'] += 	$rate[$val['unit'].'2RMB_rate']*$val['arrearage'];
		}
		$this->ajaxReturn(array('total' => $count, 'rows' => $clients,'totalMoney'=>$allMoney));
	}

}
/*改成使用其它计算方式*/
/*$bill = M('Bill')->field('awbno,transer_fee,transer_fee_unit,tariff_fee,tariff_fee_unit')->where($whereBill)->select();
$totalTranserFee = 0;
$totalTariffFee = 0;
foreach ($bill as $k => $v) {
	$totalTranserFee += $v['transer_fee']*$rate[$v['transer_fee_unit'].'2RMB_rate'];
	$totalTariffFee += $v['tariff_fee']*$rate[$v['tariff_fee_unit'].'2RMB_rate'];
}*/