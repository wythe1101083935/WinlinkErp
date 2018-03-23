<?php
/**
 +----------------------------------------------------------
 * 用户管理
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-02-07 13:59:24
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace User\Controller;
use Common\Controller\CommonController;
class UserController extends CommonController{
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
				$where['username'] = array('like','%'.$postData['keyword'].'%') ;
			}

		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}
			$data = D('User')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			$count = D('User')->where($where)->count();

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
		$role = M('Role')->field('id,name')->select();
		$this->assign('role',$role);
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
		$role = M('Role')->field('id,name')->select();
		$this->assign('role',$role);
		$eidtData = M('User')->where(array('id'=>$_GET['id']))->find();
		$this->assign('editData',$eidtData);
		$this->assign('nowRoleId',M('RoleUser')->where(array('user_id'=>$_GET['id']))->getField('role_id'));
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
		$res = D('User')->insert($_POST);
		$this->ajaxReturn($res);
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
		$res = D('User')->update($_POST);
		$this->ajaxReturn($res);
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
		$res = D('User')->delete($_POST);
		$this->ajaxReturn($res);
	}
}