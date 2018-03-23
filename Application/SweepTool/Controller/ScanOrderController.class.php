<?php
/**
 +----------------------------------------------------------
 * 已扫订单
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-24 14:20:45
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace SweepTool\Controller;
use Common\Controller\CommonController;
class ScanOrderController extends CommonController{
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
			if($postData['scantime']){//出库时间
				$limitTime = explode('~',$postData['scantime']);
				//foreach ($limitTime as $key => &$val) {$val = date('Y-m-d H:i:s',strtotime($val));}
				
				$where['scantime'] = array('BETWEEN',$limitTime);
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = M('Awb','scan_')->field('scantime,shipperref,packageno')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = M('Awb','scan_')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
}