<?php
namespace Home\Model;
use Think\Model;
class DeskModel extends Model{
	/*
	 +----------------------------------------------------------
	 * 增加系统个人桌面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function insertDesk($id){
        $data['user_id'] = session('uid');
        $data['node_id'] = $id;
        if(empty($this->where($data)->find())){/*判断是否已经存在该图标*/
            $res = $this->data($data)->add(); 
            if($res){
                $newDesk = M('NewNode')->where(array('id'=>$id))->find();
                $return = ReturnData(true,'添加桌面图标成功！',array('url'=>__ROOT__.'/index.php/'.$newDesk['url'],
                                                                     'iconcls'=>$newDesk['iconcls'],
                                                                     'menu_img'=>$newDesk['menu_img'],
                                                                     'text'=>$newDesk['text'],
                                                                     'desk_id'=>$res
                                                                    ),'');
            }else{
                $return = ReturnData(false,'添加桌面失败！系统错误！','','020301001');
            }
        }else{
            $return = ReturnData(false,'桌面已存在!','','');
        } 
        return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 删除桌面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function deleteDesk($id){
        $where['desk_id'] = $id;
        if(!empty($this->where($where)->find())){/*判断是否已经存在该图标*/
            $res = $this->where($where)->delete(); 
            if($res){
                $return = ReturnData(true,'删除桌面图标成功！','','');
            }else{
                $return = ReturnData(false,'删除桌面失败！系统错误！','','020301002');
            }
        }else{
            $return = ReturnData(false,'桌面已删除!请刷新再试！','','020301003');
        } 
        return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 获取桌面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getDesk(){
        $where['user_id'] = session('uid');
        $desk = $this->where($where)->select();//获取所有的桌面图标
        $where['node_id'] = array('neq',0);
        $where['level'] = 2;
        $node_id = $this->where($where)->getField('node_id',true);
        if(empty($node_id)){
            return array();
        }
        $deskList1 = M('NewNode')->where(array('id'=>array('IN',$node_id)))->select();//获取系统个人桌面
        $arr = array();
        foreach ($deskList1 as $key => $val) {
            $arr[$val['id']] = $val;
        }
        foreach ($desk as $key => &$val) {//将系统桌面并入个人桌面
            if($val['node_id']==0){//个人桌面
                $val['url'] = $val['desk_url'];
                $val['text'] = $val['desk_name'];
                $val['menu_img'] = $val['desk_img'] ? $val['desk_img'] : 'demo.png';
            }else{//系统桌面
                $val['iconcls'] = $arr[$val['node_id']]['iconcls'];
                $val['url'] = __ROOT__.'/index.php/'.$arr[$val['node_id']]['url'];
                $val['text'] = $arr[$val['node_id']]['text'];
                $val['menu_img'] = $arr[$val['node_id']]['menu_img'];
            }
        }
        return ReturnData(true,'Fixed Success',$desk,'');		
	}
	/*
	 +----------------------------------------------------------
	 * 增加外部桌面
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function insertExternalDesk($data){
		$data['user_id']  = session(C('USER_AUTH_KEY'));
		$data['node_id'] = 0;
		$res = $this->data($data)->add();
		if($res){
			$returnData['text'] = $data['desk_name'];
			$returnData['url'] = $data['desk_url'];
			$returnData['menu_img'] = $data['desk_img'] ? $data['desk_img'] : 'demo.png';
			$returnData['desk_id'] = $res;
			$return = ReturnData(true,'桌面已添加！',$returnData,'');
		}else{
			$return = ReturnData(false,'添加失败！系统错误！','','');
		}
		return $return;
	}


}