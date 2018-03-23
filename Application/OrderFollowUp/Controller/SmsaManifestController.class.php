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
namespace OrderFollowUp\Controller;
use Common\Controller\CommonController;
class SmsaManifestController extends CommonController{
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
		/*1.获取post传入数据,content-type不是multipart/form-data*/
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	

		/*2.特殊权限*/

		/*3.查看字段权限*/

		/*4.搜索判断*/
			$where = array();
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
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
			/*如果是按照订单号查询，按照订单输入顺序排序*/
			if(isset($orderAwbno)){
				$order = $orderAwbno;
			}
		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}		
			$data = D('ViewExportOrder')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewOrderList')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}

}