<?php
/**
 +----------------------------------------------------------
 * 主页
 +----------------------------------------------------------
 * CODE:020301
 +----------------------------------------------------------
 * TIME:2018-01-24 14:31:04
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Home\Controller;
use Common\Controller\CommonController;
class IndexController extends CommonController {
    /*
     +----------------------------------------------------------
     * 主页获取菜单和桌面回复
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function index(){
        /*获取菜单*/
        $menuList = $this->getMenuList();
        $this->assign('menuList',$menuList);
        /*获取桌面*/
        $deskList = D('Desk')->getDesk();
        $this->assign('deskList',$deskList['data']);
        /*获取展示汇率信息*/
        $rate = M('Rate')->limit(5)->select();
        foreach ($rate as $key => &$val) {
            $val['url']='http://www.xe.com/zh-CN/currencyconverter/convert/?Amount=1&From='.$val['rate_from'].'&To='.$val['rate_to'];

            //$val['url']='http://www.xe.com/zh-CN/currencyconverter/convert/?Amount=1&From=AED&To=RMB';
        }
        $this->assign('rate',$rate);
    	$this->display();
    }  
    /*
     +----------------------------------------------------------
     * 即时通讯
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function messageIndex(){
        $this->display();
    }
    /*
     +----------------------------------------------------------
     * 增加个人外部桌面
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return view
     +----------------------------------------------------------
    */
    public function addExternalDesk(){
        $this->display();
    }
    /*
     +----------------------------------------------------------
     * 增加个人外部桌面操作
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function insertExternalDesk(){
        $return = D('Desk')->insertExternalDesk($_POST);
        $this->ajaxReturn($return);
    }
    /*
     +----------------------------------------------------------
     * 增加系统个人桌面
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function addDesk($id){
        $return = D('Desk')->insertDesk($id);
        $this->ajaxReturn($return);      
    }
    /*
     +----------------------------------------------------------
     * 删除系统个人桌面
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function deleteDesk($id){
        $return = D('Desk')->deleteDesk($id);
        $this->ajaxReturn($return);      
    }
    /*
     +----------------------------------------------------------
     * 获取菜单
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return arr $data
     +----------------------------------------------------------
    */
    private function getMenuList(){
        $menuList = M('NewNode')->where(array('id'=>array('IN',session('NewmenuList'))))->order('concat(menu_path,id),menu_index')->select();
        foreach ($menuList as $key => &$val) {
            $val['iconCls'] = $val['iconcls'];
            $val['url'] = __ROOT__.'/index.php/'.$val['url'];
        }
        return $menuList;
    }
    /*
     +----------------------------------------------------------
     * 个人信息页面
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function userInfo(){
        /*获取用户信息*/
        $eidtData = M('User')->where(array('id'=>session(C('USER_AUTH_KEY'))))->find();
        $this->assign('editData',$eidtData); 
        $this->display();
    }
    /*
     +----------------------------------------------------------
     * 修改密码界面
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return view
     +----------------------------------------------------------
    */
    public function indexChangePass(){
        $this->display();
    }
    /*
     +----------------------------------------------------------
     * 修改密码
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function changePass(){
       $oldpass =  M('User')->where(array('id'=>session(C('USER_AUTH_KEY'))))->getField('password');
       if(md5(I('post.old_pass')) == $oldpass){
            $data['id'] = session(C('USER_AUTH_KEY'));
            $data['password'] = I('post.new_pass');
            $data['repassword'] = I('post.new_repass');
            $return = D('User/User')->update($data);
       }else{
        $return = ReturnData(false,'原密码错误！','','');
       }
       $this->ajaxReturn($return);
    }
    /*
     +----------------------------------------------------------
     * 重置API界面
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return view
     +----------------------------------------------------------
    */
    public function indexResetAPI(){
        $this->display();
    }
    /*
     +----------------------------------------------------------
     * 重置API
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function resetAPI(){

    }
}