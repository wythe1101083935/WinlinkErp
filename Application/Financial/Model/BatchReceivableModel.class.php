<?php
/**
 +----------------------------------------------------------
 * wythe,开发平台生成模型模板
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:alt+t
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Model;
use Think\Model;
class BatchReceivableModel extends Model{

	/*
	 +----------------------------------------------------------
	 * 增加操作
	 +----------------------------------------------------------
	 * @param  mix;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,code:int,data:mix}
	 +----------------------------------------------------------
	*/
	public function insert($data){
		/*判断结算是否已经结束*/
		$status = M('Batch')->where(array('batch_id'=>$data['batch_id']))->getField('batch_status');
		if($status){
			return ReturnData(false,'该结算单已经结算完毕！');
		}else{
			$total = $this->overReceivable($data['batch_id']);
			if($total['total'] < ($total['addTotal']+$data['receivable_account'])){
				return ReturnData(false,'此次结算后超过总结算费用！');
			}else{
				$data['receivable_time'] = time();
				$data['action_user_id'] = session(C('USER_AUTH_KEY'));
				$res = $this->data($data)->add();	
				$nowTotal = $this->overReceivable($data['batch_id']);
				if(round($nowTotal['total']-$nowTotal['addTotal']) == 0){
					M('Batch')->where(array('batch_id'=>$data['batch_id']))->setField('batch_status',1);
				}	
				if($res){
					return ReturnData(true,'结算成功！');
				}else{
					return ReturnData(false,'结算失败！系统错误！','','');
				}				
			}
		}
	}

	public function overReceivable($batch_id){
		$where['batch_id'] = $batch_id;
		$total = M('Batch')->where($where)->getField('batch_receivable');
		$addTotal = $this->where($where)->getField('sum(receivable_account)');
		return array('total'=>$total,'addTotal'=>$addTotal);
	}

}