<?php
/**
 +----------------------------------------------------------
 * 汇率管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-01-30 13:54:03
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Model;
use Think\Model;
class RateModel extends Model{
	/*
	 +----------------------------------------------------------
	 * 获取汇率
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return 
	 +----------------------------------------------------------
	*/
	public function getRate(){
 		$arr = $this->select();
 		$rate = array();
 		foreach ($arr as $key => $val) {
 			$rate[$val['rate_from'].'2'.$val['rate_to'].'_rate'] = $val['rate_value'];
 		}	
 		return $rate;
	}

	/*
	 +----------------------------------------------------------
	 * 货币单位
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return arr
	 +----------------------------------------------------------
	*/
	public function localCollection(){
		$data = '[
		    {
		        "country": "QA",
		        "unit": "QAR",
		        "rate": 3.6411
		    },
		    {
		        "country": "OM",
		        "unit": "OMR",
		        "rate": 0.385
		    },
		    {
		        "country": "BH",
		        "unit": "BHD",
		        "rate": 0.377
		    },
		    {
		        "country": "US",
		        "unit": "USD",
		        "rate": 1
		    },
		    {
		        "country": "SA",
		        "unit": "SAR",
		        "rate": 3.75
		    },
		    {
		        "country": "AE",
		        "unit": "AED",
		        "rate": 3.67
		    },
			{
		        "country": "AE1",
		        "unit": "AED",
		        "rate": 3.67
		    },
			{
		        "country": "AE2",
		        "unit": "AED",
		        "rate": 3.67
		    },
		    {
		        "country": "IR",
		        "unit": "IRR",
		        "rate": 32321
		    },
		    {
		        "country": "KWT",
		        "unit": "KWD",
		        "rate": 0.3042
		    },
		    {
		        "country": "EG",
		        "unit": "EGP",
		        "rate": 18.01
		    }
		]';
		$data = json_decode($data,true);
		$res = array();
		foreach ($data as $key => $val) {
			$res[$val['country']] = $val['unit'];
		}
		return $res;
	}
}