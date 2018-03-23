<?php
/**
 +----------------------------------------------------------
 * 报价管理	
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-01 11:57:26
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Financial\Controller;
use Common\Controller\CommonController;
class QuotationManagerController extends CommonController{
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
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = M('Quotation')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = M('Quotation')->where($where)->count();
		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
	/*
	 +----------------------------------------------------------
	 * 
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function indexQuotationTransport($id){
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 报价渠道列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function XHRQuotationTransport(){
		/*1.获取post传入数据,content-type不是multipart/form-data*/
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	
		/*4.搜索判断*/
			$where = array();
			$where['quotation_id'] = $postData['id'];
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = M('RuleFee')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();	
			$count = M('RuleFee')->where($where)->count();
		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));		
	}
	/*
	 +----------------------------------------------------------
	 * 用户报价
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function indexUserQuotation(){
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 用户报价数据
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function XHRUserQuotation(){
		/*1.获取post传入数据,content-type不是multipart/form-data*/
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	
			$where['rule_id'] = $postData['rule_id'];
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];
		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = M('ViewRuleFeeUser')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();	
			$count = M('ViewRuleFeeUser')->where($where)->count();
		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));		
	}
	/*
	 +----------------------------------------------------------
	 * 报价详情
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function indexQuotationDetail(){
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 报价详情数据
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function XHRQuotationDetail(){
	   $rows = M('RuleFeeData')->where(array('rule_id'=>I('post.rule_id')))->order('id')->select();
	  // $count = M('RuleFeeData')->where(array('rule_id'=>I('post.rule_id')))->count();	
	   $this->ajaxReturn(array('code'=>0,'msg'=>'','count'=>count($rows),'data'=>$rows));
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
	public function getTransportMode(){
		$res = D('Order/CountryCode')->getTransportModeList();
		$return = array();
		foreach ($res as $key => $val) {
			$return[$val['transportMode']] = $val;
		}
		$this->ajaxReturn($return);
	}

	/*
	 +----------------------------------------------------------
	 * 修改报价详情
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateQuotationDetail(){
		$where['id'] = I('post.id');
		$res = M('RuleFeeData')->where($where)->setField(I('post.field'),I('post.value'));
		if($res){
			$return = ReturnData(true,'已修改！');
		}else{
			$return = ReturnData(false,'未修改！');
		}
		$this->ajaxReturn($return);
	}
}