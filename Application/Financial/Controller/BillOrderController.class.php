<?php
/**
 +----------------------------------------------------------
 * 账单管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-06 17:39:00
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class BillOrderController extends CommonController{
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
		$this->getDays();//时间列表
		$this->getAwbnoStatus();//状态列表
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
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	

		/*2.特殊权限*/
			$where['userid'] = $this->getClient();
		/*3.查看字段权限*/

		/*4.搜索判断*/
			if($postData['country']){//按照国家
				$where['consigneecountry'] = $postData['country'];
			}
			if($postData['awbno']){//按照订单号
				$queries = nl2br($postData['awbno']);
				$queries = explode('<br />',$queries);
				$queries = array_filter($queries,function(&$v){
					if($v=trim($v)){
						return true;
					}else{
						return false;
					}
				});				
				$where['awbno'] = array('IN', $queries);//设置搜索条件
			 	$queries = implode($queries, ',');
			 	$orderAwbno = "INSTR(',".$queries.",',CONCAT(',',awbno,','))";//设置排序规则
			}
			if($postData['username']){//按照用户
				$where['username'] = array('like','%'.$postData['username'].'%');
			}
			if($postData['timeOut']){//出库时间
				$limitTime = explode('~',$postData['timeOut']);
				if(strpos($limitTime[0],':')){
					foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				}
				$where['time'] = array('BETWEEN',$limitTime);
			}
			if($postData['timeFinish']){//完成时间
				$limitTime = explode('-',$postData['timeFinish']);
				foreach ($limitTime as $key => &$val) {$val = strtotime($val);}
				$where['finish_time'] = array('BETWEEN',$limitTime);
			}
			if($postData['orderStatus']){
				$where['status_flag'] = array($postData['orderStatus']['action'],$postData['orderStatus']['range']);
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
			if(isset($orderAwbno)){
				$order = $orderAwbno;
			}

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 5000;
			}
			$data = D('ViewOrderList')->where($where)->field('id,time,username,awbno,weight,userrefid,cweight,cost_vweight,inamt,bill_cacuweight,vweight,refcode,transer_fee,transer_fee_unit,cost_fee,goods_value,consigneecountry,cost_fee_unit,tariff_fee,tariff_fee_unit,status_flag,finish_time,update_time,cost_cacuweight,consigneecity')->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewOrderList')->where($where)->count();
			$unit = D('Rate')->localCollection();
			foreach ($data as $key => &$val) {
				$val['inamt_unit'] = $unit[$val['consigneecountry']];
			}
		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}

	/*
	 +----------------------------------------------------------
	 * 添加结算单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function addBatch(){
		$awbnos = explode(',',I('post.awbnos'));
		$batch_name = I('post.batch_name');
		$this->ajaxReturn(D('Batch')->insert($awbnos,$batch_name));
	}

	/*
	 +----------------------------------------------------------
	 * 手动更新应收和关税
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function update($field,$row){
		$sign = D('Batch')->fixed($row['awbno']);
		/*if($sign['status']){
			$return = ReturnData(false,'此订单已经有结算！不能修改','','');
		}else{*/
			$res = M('Bill')->where(array('id'=>$row['id']))->setField($field,$row[$field]);
			if($res){
				$return =ReturnData(true,'修改成功！');
			}else{
				$return = ReturnData(false,'修改失败！',$row,$field);
			}
		/*}*/
		$this->ajaxReturn($return);
	}
}