<?php
/**
 +----------------------------------------------------------
 * 关税常熟及汇率维护
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-09 09:33:30
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class TariffController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * view
	 +----------------------------------------------------------
	 * @param  ;
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
		/*1.获取post传入数据,content-type不是multipart/form-data*/
		
		$rate = M('Rate')->select();
		$tariff5000 = M('Tariff')->where(array('tariff_mode'=>5000))->select();
		$tariff5002 = M('Tariff')->where(array('tariff_mode'=>5002))->select();	
		$arr = array();
		$i = 0;
		while($rate[$i] || $tariff5000[$i] || $tariff5002[$i]){
			$arr[$i]['id1'] = $rate[$i]['rate_id'];
			if($rate[$i]['rate_from'])$arr[$i]['name1'] = $rate[$i]['rate_from'].'To'.$rate[$i]['rate_to'];
			$arr[$i]['value1'] = $rate[$i]['rate_value'];
			$arr[$i]['id2'] = $tariff5000[$i]['tariff_id'];
			$arr[$i]['name2'] = $tariff5000[$i]['tariff_name'];
			$arr[$i]['value2'] = $tariff5000[$i]['tariff_value'];
			$arr[$i]['id3'] =  $tariff5002[$i]['tariff_id'];
			$arr[$i]['name3'] = $tariff5002[$i]['tariff_name'] ;
			$arr[$i]['value3'] = $tariff5002[$i]['tariff_value'] ;
			$i++;
		}		

			//$data = D('ViewFinancialStatementForClient')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			//$count = D('ViewFinancialStatementForClient')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('code'=>'','msg'=>'','count' => count($arr), 'data' => $arr));	
	}
	/*
	 +----------------------------------------------------------
	 * 将获取的数据写入数据库
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function update(){
		if(I('post.type') == 'tariff'){
			$res = M('Tariff')->where(array('tariff_id'=>$_POST['id']))->setField('tariff_value',$_POST['value']);
			if($res){
				$return = ReturnData(true,'修改'.$_POST['name'].'变为"'.$_POST['value'].'"成功！');
			}else{
				$return = ReturnData(false,'修改失败！');
			}
		}elseif(I('post.type') == 'rate'){
			$res = M('Rate')->where(array('rate_id'=>$_POST['id']))->setField('rate_value',$_POST['value']);
			if($res){
				$return = ReturnData(true,'修改'.$_POST['name'].'变为"'.$_POST['value'].'"成功！');
			}else{
				$return = ReturnData(false,'修改失败！');
			}			
		}
		$this->ajaxReturn($return);
	}
}