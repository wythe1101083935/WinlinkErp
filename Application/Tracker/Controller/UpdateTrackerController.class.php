<?php
/**
 +----------------------------------------------------------
 * 批量更新轨迹
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-23 08:43:44
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Controller;
use Think\Controller;
class UpdateTrackerController extends Controller{
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
		$this->assign('tracker', M('Tracker')->where(array('type'=>0))->order('id DESC')->select());
		$this->display();
	}

	public function updateTracker(){
		set_time_limit(0);
		//ini_set('max_execution_time', '0');//设置内存变量不超时
		/*若有post数据传入*/
		if (IS_POST){
			/*1.处理传入数据*/
				/*将批量单号分开*/
				$awbnos = I('post.awbnos');
				if(M('Awb')->where(array('awbno'=>$awbnos))->getField('is_confirm')==7){
					/*获取其它数据*/
					$time = strtotime(I('post.time'));
					$remarks = M('Tracker')->where(array('id'=>I('post.remarks')))->find();
				/*2.本地数据库进行修改*/
					$res = D('TrackerInfo')->trackerInfoMultiUpdate($awbnos,$remarks,$time);
					$return['localDbStatus'] = $res['status'];						
					$return['localDbMsg'] = $res['msg'];						
				/*3.香港服务器远程同步*/
				 if (strtoupper($remarks['code']) == 'SFF' ) {
	                  $res = $this->updateSFFAwb($awbnos);
	                  $return['netDbStatus'] = $res['status'];
	                  $return['netDbMsg'] = $res['msg'];          
	                }else{
	                  $return ['netDbStatus'] = true;
	                  $return['netDbMsg'] = '非SFF,不推送面单信息';
	                }	
				}else{
					$return['localDbStatus'] = false;						
					$return['localDbMsg'] = '订单未出库！';
	                $return['netDbStatus'] = false;
	                $return['netDbMsg'] = '订单未出库！';
				}
				$return['awbno'] = $awbnos;
			/*4.返回处理信息*/
				$this->ajaxReturn($return);
		}
	}

	/*
		功能：更新香港服务器数据库sqlserver信息，主要更新以下三个表：
              1.AWBPrintSeq(UnconfirmOrderModel):未确认订单信息表,（含收件人信息，字符集不支持阿拉伯语）
              2.AWB(ConfirmOrder) 已确认订单信息表
              3.AWBARABIC(OrderReceiverArabic) 已确认订单信息表
		参数：
			@param:arr $awbnos;
			@return:arr $res
	*/
	public function updateSFFAwb($val){
		/*1.查找订单信息、订单明细信息，处理订单信息与订单明细信息*/
			$order = M('Awb')->where(array('awbno'=>$val))->find();
			$orderTackle['shipper'] = allow2Text($order['shipper']);
			$orderTackle['shipperName'] = allow2Text($order['shippername']);
			$orderTackle['shipperAdd1'] = allow2Text($order['shipperaddress1']);
			$orderTackle['shipperAdd2'] = allow2Text($order['shipperaddress2']);
			$orderTackle['consignee'] = allow2Text($order['consignee']);
			$orderTackle['consigneeName'] = allow2Text($order['consigneename']);
			$orderTackle['consigneeAddress1'] = allow2Text($order['consigneeaddress1']);
			$orderDetail = M('Invoice')->field('GROUP_CONCAT(orderName) as goodsDesc,SUM(orderInsvalue) as goodsValues')->where(array('awbid'=>$order['id']))->find();
			$orderDetail['goodsDesc'] = preg_replace('\s+','',$orderDetail['goodsDesc']);
			//$orderDetail['goodsDesc'] = preg_replace('\s+','',$orderDetail['goodsDesc']);
			$billTableData= M('Bill')->where(array('awbno' => $order['awbno']))->find();
		 if(!empty($order) && !empty($billTableData)){
			/*2.更新AWBPrintSeq(UnconfirmOrdermodel)表*/
				$res1 = D('UnconfirmOrder')->updateData($order,$orderTackle,$orderDetail);	 
			/*3.更新AWBARABIC(OrderReceiverArabic)已确认订单信息表*/
				$res2 = D('OrderReceiverArabic')->updateData($order);
			/*4.更新AWB(ConfirmOrder)已确认订单信息表*/
				$res3 = D('ConfirmOrder')->updateData($order,$orderTackle,$orderDetail,$billTableData);
				if($res1['status'] && $res2['status'] && $res3['status']){
					$return = ReturnData(true,'向FFC推送信息成功！');
				}else{
					$return = ReturnData(false,'向FFC推送信息失败！失败代码：'.$res1['code'].':'.$res1['msg'].$res2['code'].':'.$res2['msg'].$res3['code'].':'.$res3['msg'],'','');
				}
		 }else{
		 	$return = ReturnData(false,'未找到本地订单，本地订单表数据为空','','');
		 }	
		return $return;
	}
}