<?php
/*
 +----------------------------------------------------------
 * 关税计算模型
 +----------------------------------------------------------
 * @param  ;
 +----------------------------------------------------------
 * @return json{status:bool,msg:string,data:mix,code:int}
 +----------------------------------------------------------
*/
namespace Financial\Model;
use Think\Model;
class TariffModel extends Model{
	/*
	 +----------------------------------------------------------
	 * 获取关税计算常数
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getTariff(){
 		$arr = $this->select();
 		$tariff = array();
 		foreach ($arr as $key => $val) {
 			$tariff[$val['tariff_mode']][$val['tariff_field']] = $val['tariff_value']; 
 		}
 		return $tariff;
	}
	/*
	 +----------------------------------------------------------
	 * 计算关税
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
 	public function cacuTariff($transport_code,$cweight,$vweightPro,$inamt,$value,$unit='RMB'){
 		$TariffCacuList = $this->getTariff();
 		$rate = D('Financial/Rate')->getRate();
 		$weight = $cweight > $vweightPro/6000 ? $cweight : $vweight/6000;
 		if($transport_code == '5002' or $transport_code == '6002' or $transport_code == '1000' ){//沙特
 			$rule = $TariffCacuList[5002];
 			$codAmount = $value*floatval($rate['USD2SAR_rate']) > $inamt ? $value*floatval($rate['USD2SAR_rate']) : $inamt;
 			if($codAmount>floatval($rule['standard'])*floatval($rate['AED2SAR_rate'])){
	 			$TotalValue1 = $codAmount+floatval($rule['freight']);
	 			$Insurance = $TotalValue1 * floatval($rule['insurance_rate']);
	 			$WeightFee = $weight/2;
	 			$TotalValue2 = $TotalValue1 + $Insurance + $WeightFee;
	 			$Fob_fee = $TotalValue2 * $rule['fob_rate'];
	 			$TotalValue3 = $TotalValue2 + $Fob_fee;
	 			$CustomDuty = $TotalValue3 * $rule['tax_rate'];
	 			$TotalValueVat = $TotalValue2 + $CustomDuty;
	 			$Vat = $TotalValueVat * $rule['vat_rate'];
	 			$res = $CustomDuty + $Vat; 				
 			}else{
 				$res = $codAmount*$rule['vat_rate'];
 			}
 			
 			$res = round(floatval($rate[$rule['unit'].'2'.$unit.'_rate']) * $res,3);
 			$return = ReturnData(true,'计算成功',array('price'=>$res,'unit'=>$unit));

 		}elseif($transport_code == '5001' or $transport_code == '5000' or $transport_code == '1001'){//阿联酋
 			$rule = $TariffCacuList[5000];
 			$Amount = $value * floatval($rate['USD2AED_rate']);
 			if($Amount>floatval($rule['standard'])){
 				$CustomDuty = floatval($rule['registration_charges']) +
 					   floatval($rule['knowledge_dirham']) +
 					   floatval($rule['archive_charges']) +
 					   floatval($rule['admin_charges']) + $Amount*floatval($rule['tax_rate']);
 				$TotalValue = $Amount + $CustomDuty - floatval($rule['archive_charges']);
 				$Vat = $TotalValue * floatval($rule['vat_rate']);
 				$res = $CustomDuty + $Vat;
 			}else{
 				$res = $Amount * floatval($rule['vat_rate']);
 			}
 			$res = round(floatval($rate[$rule['unit'].'2'.$unit.'_rate']) * $res,3);
 			$return = ReturnData(true,'计算成功',array('price'=>$res,'unit'=>$unit));
 		}else{
 			$return = ReturnData(false,'没有对应关税的渠道代码!');
 		}
 		return $return;
 	}
}