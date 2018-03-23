<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\Rbac;
class LoginController extends Controller{
	/*
		功能：登陆页面
		参数：
		@return :登陆页面
	*/
	public function index(){
		$this->display();
	}
	/*
		功能：登陆验证
		参数：
			@param:string username
			@param:string userpass
			@return:成功返回1 失败返回错误码-*
	*/
	public function loginCheck(){
	    	$userName = I('post.username');
	    	$userPwd = md5(I('post.userpass'));
	        $where['username'] = $userName;
	        $where['password'] = $userPwd;
	    	$userInfo = M('User')->where($where)->find();
	    	if(empty($userInfo)){
	    		session('isLogin',0);
	    		$return = ReturnData(false,'用户名或密码错误！','','0');
	    	}elseif(!$userInfo['status']){
	    		$return = ReturnData(false,'该用户尚未启用！','','1');
	    	}else{
	    		session('isLogin','1');
	    		session(C('USER_AUTH_KEY'),$userInfo['id']);
	    		session('ROLE_ID',M('RoleUser')->where(array('user_id'=>$userInfo['id']))->getField('role_id'));
	    		session('loginInfo',$userInfo); 
	    		if($userInfo['username']=='admin'){
	    			session(C('ADMIN_AUTH_KEY'),$userInfo['id']);
	    		}
	    		$whereMenu['role_id'] = session('ROLE_ID');
	    		session('NewmenuList',M('NewAccess')->where($whereMenu)->getField('node_id',true));
	    		session('menuList',M('Access')->where($whereMenu)->getField('node_id',true));
	    		Rbac::saveAccessList();
	    		$return = ReturnData(true,'登陆成功！',$_SESSION);
	    	}	
	    $this->ajaxReturn($return);
	}
	/*
	 +----------------------------------------------------------
	 * 无权限界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function noAuthor(){
		dump($_SESSION);
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 退出登录
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function logout(){
		session(null);
		session('[destroy]'); 
		$this->redirect('index');
	}
}