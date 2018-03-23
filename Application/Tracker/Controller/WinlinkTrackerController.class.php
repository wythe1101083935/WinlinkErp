<?php
/**
 +----------------------------------------------------------
 * 轨迹管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-13 14:38:26
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Controller;
use Common\Controller\CommonController;
class WinlinkTrackerController extends CommonController{
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
			if($postData['keyword']){
				$where['names|code|remarks|city'] = array('like','%'.$postData['keyword'].'%');
			}

		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('Tracker')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('Tracker')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}

	/*
	 +----------------------------------------------------------
	 * 增加界面
	 +----------------------------------------------------------
	 * @param  void;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function add(){
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 修改界面
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function edit(){
		$eidtData = M('Tracker')->where(array('id'=>I('get.id')))->find();
		$this->assign('editData',$eidtData);
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 增加操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix}
	 +----------------------------------------------------------
	*/
	public function insert(){
		$res = M('Tracker')->data(I('post.'))->add();
		if($res){
			$return = ReturnData(true,'增加成功！');
		}else{
			$return = ReturnData(false,'增加失败!系统错误,请联系管理员！',I('post.'));
		}
		$this->ajaxReturn($return);
	}

	/*
	 +----------------------------------------------------------
	 * 修改操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix}
	 +----------------------------------------------------------
	*/
	public function update(){
		$res = M('Tracker')->data(I('post.'))->save();
		if($res){
			$return = ReturnData(true,'修改成功！');
		}else{
			$return = ReturnData(false,'未作修改！');
		}
		$this->ajaxReturn($return);
	}

	/*
	 +----------------------------------------------------------
	 * 删除操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix}
	 +----------------------------------------------------------
	*/
	public function delete(){
		$res = M('Tracker')->where(array('id'=>array('IN',I('post.id'))))->delete();
		if($res){
			$return =ReturnData(true,'删除成功！');
		}else{
			$return = ReturnData(false,'删除失败!请联系管理员！');
		}
		$this->ajaxReturn($return);
	}
}