<?php
/**
 +----------------------------------------------------------
 * 结算单
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-08 10:54:17
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class ReceivableBatchController extends CommonController{
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
		/*4.搜索判断*/
			$where = array();
			if($postData['status']){
				$where['batch_status'] = $postData['status']-1;
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('ViewBatch')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			foreach ($data as $key => &$val) {
				$val['batch_debit'] = round($val['batch_receivable'] - $val['batch_receivable_already'],2);
			}
			$count = D('ViewBatch')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}

	/*
	 +----------------------------------------------------------
	 * 结算
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function receivable(){
		$this->ajaxReturn(D('BatchReceivable')->insert(I('post.')));
	}
	/*
	 +----------------------------------------------------------
	 * 结算参数
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function addReceivable(){
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 查看结算详情index
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function indexReceivableDetail(){
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 查看结算详情XHR
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function XHRReceivableDetail(){
		/*1.获取post传入数据,content-type不是multipart/form-data*/
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	
		/*4.搜索判断*/
			$where['batch_id'] = $postData['batch_id'];
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('ViewBatchReceivable')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('ViewBatchReceivable')->where($where)->count();
		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
	/*
	 +----------------------------------------------------------
	 * 查看订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function billOrder(){
		$where['batch_id'] = I('get.batch_id');
		$awbnos = M('Bill')->where($where)->getField('awbno',true);
		$awbnos = implode("\r\n",$awbnos);
		$this->getDays();//时间列表
		$this->getAwbnoStatus();//状态列表
		$this->assign('awbnos',$awbnos);
		$this->display('BillOrder/index');		
	}

	/*
	 +----------------------------------------------------------
	 * 更新订单信息
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function update(){
		$this->ajaxReturn(D('Batch')->update(I('post.batch_id')));
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
	public function discount(){
		$this->ajaxReturn(D('Batch')->discount(I('post.batch_id'),I('post.discount')));
	}
}