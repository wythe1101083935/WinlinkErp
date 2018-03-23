<?php
namespace System\Controller;
use Common\Controller\CommonController;
class RoleController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * 角色首页
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function index(){
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 获取角色列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json data{rows:[],total:int}
	 +----------------------------------------------------------
	*/
	public function XHRIndex(){
		$params =  json_decode(str_replace('/', '-', file_get_contents('php://input')), true);
		if($params['keyword']){
			$where['name|remarks'] = array('like','%'.$params['keyword'].'%');
		}else{
			$where  = array();
		}
		$rows = M('Role')->page($params['pageNumber'],$params['pageSize'])->where($where)->order($params['sortName'].' '.$params['sortOrder'])->select();
		$count = M('Role')->count();
		$this->ajaxReturn(array('rows'=>$rows,'total'=>$count));
	}

	/*
	 +----------------------------------------------------------
	 * 增加角色界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function addRole(){
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 增加角色操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function insertRole(){
		$res = D('Role')->insert($_POST);
		$this->ajaxReturn($res);
	}
	/*
	 +----------------------------------------------------------
	 * 修改角色界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	 public function editRole(){
	 	$where['id'] = $_GET['id'];
	 	$nowRole = M('Role')->where($where)->find();
	 	$this->assign('nowRole',$nowRole);
	 	$this->display();
	 }
	/*
	 +----------------------------------------------------------
	 * 修改角色操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateRole(){
		$res = D('Role')->update($_POST);
		$this->ajaxReturn($res);
	}
	/*
	 +----------------------------------------------------------
	 * 编辑角色权限界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function roleAuth(){
		$nowAuth = M('NewAccess')->where(array('role_id'=>$_GET['id']))->getField('node_id',true);
		$menuList = M('NewNode')->field('id,pid,text,url,name,iconcls')->select();
		foreach ($menuList as $key => &$val) {
			if(in_array($val['id'],$nowAuth)){
				$val['checked'] = 'checked';
			}else{
				$val['checked'] = '';
			}
		}
		$this->assign('menuList',json_encode($menuList));
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 编辑角色权限
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateRoleAuth(){
		$res = D('Access')->updateRoleAuth($_POST);
		$this->ajaxReturn($res);
	}
	/*
	 +----------------------------------------------------------
	 * 删除角色,暂不开启
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function delete(){
		$roleID = $_POST['id'];
		$where['id'] = array('IN',$roleID);
		$res = M('Role')->where($where)->delete();
		if($res){
			$return = ReturnData(true,'成功删除选中角色！','','');
		}else{
			$return = ReturnData(fale,'删除失败！系统错误！','','');
		}
		$this->ajaxReturn($return);
	}
}