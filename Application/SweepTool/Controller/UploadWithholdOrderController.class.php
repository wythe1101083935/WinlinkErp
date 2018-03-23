<?php
/**
 +----------------------------------------------------------
 * 扣单导入
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-24 14:13:43
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace SweepTool\Controller;
use Common\Controller\CommonController;
class UploadWithholdOrderController extends CommonController{
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

			$queries = nl2br($postData['awbno']);
			$queries = explode('<br />',$queries);
			$queries = array_filter($queries,function(&$v){
				if($v=trim($v)){
					return true;
				}else{
					return false;
				}
			});	
			if(count($queries)){//按照订单号			
				$where['awbno'] = array('IN', $queries);//设置搜索条件
			 	$queries = implode($queries, ',');
			 	$orderAwbno = "INSTR(',".$queries.",',CONCAT(',',awbno,','))";//设置排序规则
			}
		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = M('Xawb','scan_')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = M('Xawb','scan_')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
	/*
	 +----------------------------------------------------------
	 * 导入订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function upload(){
		$queries = nl2br($_POST['awbno']);
		$queries = explode('<br />',$queries);
		$queries = array_filter($queries,function(&$v){
			if($v=trim($v)){
				if(empty(M('Xawb','scan_')->where(array('awbno'=>$v))->find())){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		});		
		if(count($queries)){
			$i = 0;
			foreach ($queries as $key => $val) {
				$res = M('Xawb','scan_')->data(array('awbno'=>$val))->add();
				if($res){
					$i++;
				}
			}
			if($res){
				$return = ReturnData(true,'增加'.$i.'个扣单成功！');
			}else{
				$return = ReturnData(false,'增加失败！','','');
			}			
		}else{
			$return = ReturnData(false,'输入不能为空或需要导入的扣单已存在！','','');
		}	
		/*3.返回数据*/
		$this->ajaxReturn($return);
	}

	/*
	 +----------------------------------------------------------
	 * 清空扣单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function deleteAll(){
		$res = M('Xawb','scan_')->where(1)->delete();
		if($res){
			$return = ReturnData(true,'已全部清空！');
		}else{
			$return = ReturnData(false,'清空失败！扣单已经为空！');
		}
		$this->ajaxReturn($return);
	}
}