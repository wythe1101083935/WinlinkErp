<?php
/**
 +----------------------------------------------------------
 * 批次处理结算信息
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-08 09:53:00
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Model;
use Think\Model;
class BatchModel extends Model{

	/*
	 +----------------------------------------------------------
	 * 增加操作
	 +----------------------------------------------------------
	 * @param  mix;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,code:int,data:mix}
	 +----------------------------------------------------------
	*/
	public function insert($awbnos,$batch_name){
		$where = array('awbno'=>array('IN',$awbnos));
		/*验证单号是否已经有结算批次*/
		$nowData = M('Bill')->field('awbno,accountno,batch_id')->where($where)->select();
		foreach ($nowData as $key => $val) {
			if($val['batch_id']>0){//验证批次
				return ReturnData(false,$val['awbno'].'已经有结算批次！','','');
			}
		}
		$user_id = $nowData[0]['accountno'];
		/*验证单号是否是同一家公司的*/
		$count1 = count($nowData);
		if($user_id == 49 || $user_id == 114){
			$count2 = M('Bill')->where(array(
									   'awbno'=>array('IN',$awbnos),
									   'accountno'=>array('IN','49,114')
									    ))->count();
			$user_id = 49;
		}else if($user_id == 89 || $user_id == 92){
			$count2 = M('Bill')->where(array(
									   'awbno'=>array('IN',$awbnos),
									   'accountno'=>array('IN','89,92')
									    ))->count();
			$user_id = 89;
		}else{
			$count2 = M('Bill')->where(array(
									   'awbno'=>array('IN',$awbnos),
									   'accountno'=>$user_id
									    ))->count();
		}
		if($count1 != $count2){
			return ReturnData(false,'所选订单不都是同一家公司的！');
		}
		/*备注：
			newchic(114)和banggu(49)是同一家公司banggu，特殊情况拥有两个账号	
			Fordel(89)和qianyu(92)是同一家公司fordel，特殊情况拥有两个账号
		*/
		/*直接取出,使用原本的currency*/
		$total = M('Bill')->field('sum(transer_fee) as transer_fee,sum(tariff_fee) as tariff_fee')->where($where)->find();
		$unit = M('Bill')->field('transer_fee_unit,tariff_fee_unit')->where($where)->select();
		$discount = M('User')->where(array('id'=>$user_id))->getField('discount');
		$data = array(
			'batch_name'=>$batch_name,
			'batch_create_time'=>time(),
			'batch_update_time'=>time(),
			'batch_create_user_id'=>session(C('USER_AUTH_KEY')),
			'batch_update_user_id'=>session(C('USER_AUTH_KEY')),
			'batch_total'=>$total['transer_fee'],
			'batch_unit'=>$unit[0]['transer_fee_unit'],
			'batch_total_tariff'=>$total['tariff_fee'],
			'batch_discount'=>$discount,
			'batch_receivable'=>($total['transer_fee']*$discount+$total['tariff_fee']),
			'user_id'=>$user_id
		);
		$this->startTrans();
		$batch_id = $this->data($data)->add();
		$res = M('Bill')->where($where)->setField('batch_id',$batch_id);
		if($batch_id && $res){
			$this->commit();
			return ReturnData(true,'结算批次添加成功');
		}else{
			$this->rollback();
			return ReturnData(false,'系统错误！','','');
		}
	}

	/*
	 +----------------------------------------------------------
	 * 更新结账批次
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function update($batch_id){
		$sign = $this->fixedBatch($batch_id);
		if($sign['status']){
			$sign['status'] = false;
			return $sign;
		}
		$where['batch_id'] = $batch_id;
		$discount = M('Batch')->where($where)->getField('batch_discount');
		$total = M('Bill')->field('sum(transer_fee) as transer_fee,sum(tariff_fee) as tariff_fee')->where($where)->find();
		$batch_total = $this->field('batch_total,batch_total_tariff')->where($where)->find();
		$data['batch_id'] = $batch_id;
		$data['batch_total'] = $total['transer_fee'];
		$data['batch_total_tariff'] = $total['tariff_fee'];
		$data['batch_receivable'] = $total['transer_fee']*$discount+$total['tariff_fee'];
		$data['batch_update_time'] = time();
		$data['batch_update_user_id'] = session(C('USER_AUTH_KEY'));
		$res = $this->data($data)->save();
		if($res){
			return ReturnData(true,'批次已更新!');
		}else{
			return ReturnData(false,'系统错误！','','');
		}			

	}

	/*
	 +----------------------------------------------------------
	 * 打折
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function discount($batch_id,$discount){
		$sign = $this->fixedBatch($batch_id);
		if($sign['status']){
			$sign['status'] = false;
			return $sign;
		}
		$where['batch_id'] = $batch_id;
		$batch_total = $this->field('batch_total,batch_total_tariff')->where($where)->find();	
		$data['batch_discount'] = $discount;
		$data['batch_id'] = $batch_id;
		$data['batch_receivable'] = $batch_total['batch_total']*$discount+$batch_total['batch_total_tariff'];
		$res = $this->data($data)->save();
		if($res){
			return ReturnData(true,'已重新打折');
		}else{
			return ReturnData(false,'打折相同未修改！');
		}
	}

	/*
	 +----------------------------------------------------------
	 * 判断订单是否还可以修改
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function fixed($awbno){
		$batch_id = M('Bill')->where(array('awbno'=>$awbno))->getField('batch_id');
		if($batch_id == 0){//还可以修改
			$return = ReturnData(false,'能修改',$batch_id);
		}else{
			$sign = M('BatchReceivable')->where(array('batch_id'=>$batch_id))->select();
			if(count($sign) < 1){
				$return = ReturnData(false,'能修改',$sign,$batch_id);//还可以修改
			}else{
				$return = ReturnData(true,'此单已经结算！不能修改');//不可以修改
			}
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 判断结账单是否还可以修改
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function fixedBatch($batch_id){
		$sign = M('BatchReceivable')->where(array('batch_id'=>$batch_id))->select();
		if(count($sign) < 1){
			$return = ReturnData(false,'能修改',$sign,$batch_id);//还可以修改
		}else{
			$return = ReturnData(true,'此对账单已经有结算！不能修改');//不可以修改
		}	
		return $return;	
	}
/*用来循环*汇率获取统一RMB总应收,*/
/*$bill = M('Bill')->field('awbno,transer_fee,transer_fee_unit,tariff_fee,tariff_fee_unit')->where($where)->select();
$total['transer_fee'] = 0;
$total['tariff_fee'] = 0;
$rate = D('Rate')->getRate();
$discount = M('Batch')->where($where)->getField('batch_discount');
foreach ($bill as $key => $val) {
	$total['transer_fee'] += $val['transer_fee']*floatval($rate[$val['transer_fee_unit'].'2RMB_rate']);
	$total['tariff_fee'] += $val['tariff_fee']*floatval($rate[$val['tariff_fee_unit'].'2RMB_rate']);
}*/
}