<?php
/**
 +----------------------------------------------------------
 * 渠道代码相关管理
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-02-10 11:06:32
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Order\Model;
class CountryCodeModel{
	public $origin = array('CZX' => '中国', 'DXB' => '迪拜');
	public $transportMode = array(
            'CZX-AE1' => '5000',
            'CZX-AE2' => '5001',
            'DXB-AE1' => '6000',
            'DXB-AE2' => '6001',
            'CZX-SA' => '5002',
            'DXB-SA' => '6002',
            'CZX-OM' => '5003',
            'DXB-OM' => '6003',
            'CZX-BH' => '5004',
            'DXB-BH' => '6004',
            'CZX-QA' => '5005',
            'DXB-QA' => '6005',
            'CZX-QA' => '5006',
            'DXB-QA' => '6006',
            'CZX-EG' => '5007',
            'DXB-EG' => '6007',
            'CZX-IR' => '5008',
            'DXB-IR' => '6008',
        );
	/*
	 +----------------------------------------------------------
	 * 获取城市列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getCityList(){
		$data = HttpRequest('http://wlapi.winlinklogistics.com/wuliu/getCoutryCityList');
		$data = $data['cityList'];
		return $data;
	}
	/*
	 +----------------------------------------------------------
	 * 获取渠道代码列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getTransportModeList(){
		$data = HttpRequest('http://wlapi.winlinklogistics.com/wuliu/getTransportModeList');
		$data = $data['transportModeList'];
		usort($data,function($a,$b){
			return $a['transportMode'] > $b['transportMode'] ? true:false;
		});
		return $data;
	}
	/*
	 +----------------------------------------------------------
	 * 设置国家列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getCountryList(){
		$data = array(
		array("CID"=>"AE1","CName"=>"阿联酋"),
		array("CID"=>"AE2","CName"=>"阿联酋偏远"),
		array("CID"=>"SA","CName"=>"沙特"),
		array("CID"=>"QA","CName"=>"卡塔尔"),
		array("CID"=>"BH","CName"=>"巴林"),
		array("CID"=>"OM","CName"=>"阿曼"),
		array("CID"=>"IR","CName"=>"伊朗"),
		array("CID"=>"EG","CName"=>"埃及"),
		array("CID"=>"KWT","CName"=>"科威特")
		);
		return $data;
	}
	/*
	 +----------------------------------------------------------
	 * 获取国家城市代码表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getCountryJson(){
		$data['CITY'] = $this->getCityList();
		$data['COUNTRY'] = $this->getCountryList();
		return $data;
	}

	/*
	 +----------------------------------------------------------
	 * 渠道代码转换
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getTransportMode(){
		$data = $this->getTransportModeList();
		$arr = array();
		foreach ($data as $key => $val) {
			$arr[$val['fromCode'].'-'.$val['toCode']] = $val['transportMode'];
		}
		return $arr;
	}
	/*
	 +----------------------------------------------------------
	 * 根据渠道代码获取报价列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getCodeUserFee($data,$status,$id){
		foreach ($data as $key => &$val) {
			$trans = M('RuleFee')->where(array('code'=>$val['transportMode'],'status'=>$status))->select();
			$val['transportDetail'] = $trans;
			$val['rule_fee_user_id'] = 0;
			$userFee = M('ViewRuleFee')->where(array('code'=>$val['transportMode'],'status'=>$status,'user_id'=>$id))->find();
			foreach ($val['transportDetail'] as $k => &$v) {
				if($v['id'] == $userFee['rule_id']){
					$v['standard_v_param'] = $userFee['standard_v_param'];
					$v['standard_v_status'] = $userFee['standard_v_status'];
					$v['sign'] = 1;
					$val['rule_fee_user_id'] = $userFee['id'];
				}else{
					$v['sign'] = 0;
					$v['standard_v_param'] = 'NO';
					$v['standard_v_status'] = 'NO';
				}
			}
		}
		return $data;
	}

}