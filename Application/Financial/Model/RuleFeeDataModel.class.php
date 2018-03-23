<?php
/**
 +----------------------------------------------------------
 * 应收以及派送费用计算规则
 +----------------------------------------------------------
 * CODE:下一次进行错误编码
 +----------------------------------------------------------
 * TIME:2018-01-25 10:59:48
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Model;
use Think\Model;
class RuleFeeDataModel extends Model{
	/*
	 +----------------------------------------------------------
	 * 核心计算
	 +----------------------------------------------------------
	 * @param  $rule_id;
	 * @param  $weight;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
 	private function cacuProcess($rule_id,$weight){
 		$where['rule_id'] = $rule_id;
 		$weight = floatval($weight);
 		$where['fee_min'] = array('lt',$weight);
 		$where['_string'] = "fee_max >=".$weight." or fee_max='$'";
 		$result = M('RuleFeeData')->field('step,fee_max,fee_price as priceValue')->where($where)->find();
		if (!$result || count($result) <= 0) {
			$return = ReturnData(false,'没有对应重量的计算规则',array('price'=>0,'cacuWeight'=>0),'');
		}else{
			$priceStr = $result['pricevalue'];
			if (stristr($priceStr, '{{w}}')) {
				// 计算步进
				if ($result['step'] > 0) {
					$param = $weight - floor($weight);
					if ($param > 0) {
						$weight = ($weight - floor($weight)) > $result['step'] ? ceil($weight) : floor($weight) + $result['step'];
					}
				}
				eval("\$price = ".str_replace('{{w}}', $weight, $priceStr).";");			
				$return = ReturnData(true,'计算成功',array('price'=>floatval($price),'cacuWeight'=>$weight));
			} else {
				$return = ReturnData(true,'计算成功',array('price'=>floatval($priceStr),'cacuWeight'=>$result['fee_max']));
			}			
		}
		return $return;
 	}
	/*
	 +----------------------------------------------------------
	 * 根据参数寻找对应的计算规则，并进行计算
	 +----------------------------------------------------------
	 * @param $user_id ;
	 * @param $trancportMode ;
	 * @param $status,0应收,1应付 ;
	 * @param $cweight 实际重 ;
	 * @param $vweightPro 体积(不是体积重) ;
	 * @param $consigneecity 城市(派送成本需判断是否是特殊类型) ;
	 * @param $costWeight 成本直接上传体积重 ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
 	public function cacuRuleFee($user_id,$trancportMode,$status,$cweight,$vweightPro,$consigneecity='',$costVweight=0){
 		//当$status = 1(计算派送房成本)时，判断订单是否是特殊类型
 		if($status && $trancportMode == '5001' && ($consigneecity == 'AL AIN' or $consigneecity == 'AJMAN' or $consigneecity == 'ALAIN')){
 			$trancportMode = '5000';
 		}
 		$where['user_id'] = $user_id;
 		$where['code'] = $trancportMode;
 		$where['status'] = $status;
 		$rule = M('ViewRuleFee')->where($where)->find();//获取计算规则
 		$rule_id = $rule['rule_id'];
 		if(empty($rule)){
 			$return = ReturnData(false,'该用户没有对应的计算规则！渠道代码：'.$trancportMode);
 		}else{
 			if($rule['standard_v_status']){
 				$vweight = $vweightPro/$rule['standard_v_param'];
 				if($costVweight>0 && $status){
 					$vweight = $costVweight;
 				}
 				$weight = $cweight > $vweight ? $cweight : $vweight;
 			}else{
 				$vweight = 0;
 				$weight = $cweight;
 			} 		
 			$res = $this->cacuProcess($rule_id,$weight);
			if($res['status']){
				$return = ReturnData(true,$res['msg'],array('price'=>$res['data']['price'],'vweight'=>$vweight,'weight'=>$weight,'unit'=>$rule['unit'],'cacuWeight'=>$res['data']['cacuWeight']));
			}else{
				$return = ReturnData(false,$res['msg'],array('price'=>$res['data']['price'],'vweight'=>$vweight,'weight'=>$weight,'unit'=>$rule['unit'],'cacuWeight'=>0));
			}
 		}
 		return $return;
 	}
 	/*
 	 +----------------------------------------------------------
 	 * 更新bill账单财务信息
 	 +----------------------------------------------------------
 	 * @param  ;
 	 +----------------------------------------------------------
 	 * @return json{status:bool,msg:string,data:mix,code:int}
 	 +----------------------------------------------------------
 	*/
 	public function updateBillFee($fee,$awbno){
 		$data = array();
 		/*应收信息*/
 		if(isset($fee['transer_fee']) && $fee['transer_fee']['status']){
 			$data['vweight'] = $fee['transer_fee']['data']['vweight'];//体积重
 			//$data['weight'] = $fee['transer_fee']['data']['weight'];//大的重量
 			$data['bill_cacuweight'] = $fee['transer_fee']['data']['cacuWeight'];//计算重
 			$data['transer_fee'] = $fee['transer_fee']['data']['price'];
 			$data['transer_fee_unit'] = $fee['transer_fee']['data']['unit'];
 		}
 		/*派送成本信息*/
 		if(isset($fee['cost_fee']) && $fee['cost_fee']['status']){
 			$data['cost_vweight'] = $fee['cost_fee']['data']['vweight'];
 			//$data['cost_weight'] = $fee['cost_fee']['data']['weight'];
 			$data['cost_cacuweight'] = $fee['cost_fee']['data']['cacuWeight'];
 			$data['cost_fee'] = $fee['cost_fee']['data']['price'];
 			$data['cost_fee_unit'] = $fee['cost_fee']['data']['unit'];
 		}
 		/*关税信息*/
 		if(isset($fee['tariff_fee']) && $fee['tariff_fee']['status']){
 			$data['tariff_fee'] = $fee['tariff_fee']['data']['price'];
 			$data['tariff_fee_unit'] = $fee['tariff_fee']['data']['unit'];
 		}
 		/*数据表更新*/
 		if(!empty($data)){
			$res = M('Bill')->where(array('awbno'=>$awbno))->data($data)->save();
 		}else{
 			$res = false;
 		}
 		$msg = ($fee['transer_fee']['msg'] ? '应收：'.$fee['transer_fee']['msg'].'|' : '').($fee['cost_fee']['msg'] ? '应付：'.$fee['cost_fee']['msg'].'|' : '').($fee['tariff_fee']['msg'] ? '关税：'.$fee['tariff_fee']['msg'].'|' :'');//连接所有返回信息 		
 		if($res){
 			$return = ReturnData(true,$msg,'','100000');
 		}else{
 			if($fee['transer_fee']['status'] || $fee['cost_fee']['status'] || $fee['tariff_fee']['status']){
 				$return = ReturnData(true,$msg.'Notice','','');
 			}else{
 				$return = ReturnData(false,$msg,'','123456');	
 			}		
 		}
 		return $return;
 	}
}