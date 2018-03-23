<?php
/**
 +----------------------------------------------------------
 * 菜单管理
 +----------------------------------------------------------
 * CODE:10301
 +----------------------------------------------------------
 * TIME:2018-01-24 15:28:51
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace System\Controller;
use Common\Controller\CommonController;
class MenuController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * 菜单首页
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function index(){
		$menuList = M('NewNode')->field('id,pid,text,url,name,iconcls')->select();
		$this->assign('menuList',json_encode($menuList));
		$this->display();
	}

	/*
	 +----------------------------------------------------------
	 * 增加menu界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function add($id){
		$this->assign('id',$id);
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 修改menu界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function edit($id){
		$editData = M('NewNode')->where(array('id'=>$id))->find();
		$this->assign('editData',$editData);
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 增加menu操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function insert(){
		$res = D('Node')->insert($_POST);
		$this->ajaxReturn($res);
	}
	/*
	 +----------------------------------------------------------
	 * 修改menu操作
	 +----------------------------------------------------------
	 * @param  $_POST;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function update(){
		$res = D('Node')->update($_POST);
		$this->ajaxReturn($res);
	}
	/*
	 +----------------------------------------------------------
	 * 禁用
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return 1,0
	 +----------------------------------------------------------
	*/
	public function disabled(){
		$res = M('NewNode')->where('id='.$_GET['id'])->setField('status',0);
		if($res){
			$return = ReturnData(true,'禁用成功！');
		}else{
			$return = ReturnData(false,'禁用失败！');
		}
		$this->ajaxReturn($return);
	}
	/*
	 +----------------------------------------------------------
	 * 删除menu
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function delete($id){
		if(M('NewNode')->where(array('pid'=>$id))->find()){
			$return = ReturnData(false,'尚有子节点！');
		}else{
			$res = M('NewNode')->where(array('id'=>array('IN',$id)))->delete();
			if($res){
				$return = ReturnData(true,'删除成功！');
			}else{
				$return = ReturnData(false,'删除失败！后台错误');
			}			
		}

		$this->ajaxReturn($return);
	}
	/*
	 +----------------------------------------------------------
	 * 增加控制器页面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function addController(){ 
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 生成控制器
	 +----------------------------------------------------------
	 * @param  string $ControllerName;
	 * @param  string $ModelName;
	 * @param  string $;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function createController($ModuleName,$ControllerName,$ModelName){
		C('TMPL_L_DELIM','{<{');
		C('TMPL_R_DELIM','}>}');
		$this->assign('ModuleName',$ModuleName); 
		$this->assign('ControllerName',$ControllerName);
		$this->assign('ModelName',$ModelName);
		$controllerContent = $this->fetch();
		/*2.生成控制器文件*/	
		if(file_exists(APP_PATH.$ModuleName.'/'.'Controller/'.$ControllerName.'Controller.class.php')){
			$return = ReturnData(false,$ControllerName.'控制器已经存在！','','');
		}else{
			file_put_contents(APP_PATH.$ModuleName.'/'.'Controller/'.$ControllerName.'Controller.class.php','<?php'."\n".$controllerContent);
			$return = ReturnData(true,'--Create '.$ControllerName.'Controller success--','','');
		}	
		$this->ajaxReturn($return);	
	}
	/*
	 +----------------------------------------------------------
	 * 增加模型界面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function addModel(){
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 生成模型
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function createModel($ModuleName,$ModelName){
		C('TMPL_L_DELIM','{<{');
		C('TMPL_R_DELIM','}>}');
		$this->assign('ModuleName',$ModuleName); 
		$this->assign('ModelName',$ModelName);
		$ModelContent = $this->fetch();
		/*2.生成模型文件*/	
		if(file_exists(APP_PATH.$ModuleName.'/'.'Model/'.$ModelName.'Model.class.php')){
			$return = ReturnData(false,$ControllerName.'模型已经存在！','','');
		}else{
			file_put_contents(APP_PATH.$ModuleName.'/'.'Model/'.$ModelName.'Model.class.php','<?php'."\n".$ModelContent);
			$return = ReturnData(true,'--Create '.$ModelName.'Model success--','','');
		}
		$this->ajaxReturn($return);
	}

}