<?php
/**
 +----------------------------------------------------------
 * 公共类
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-03-03 16:59:45 update
 +----------------------------------------------------------
 * author:Wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Common\Controller;
use Think\Controller;
use Org\Util\Rbac;
class CommonController extends Controller{
	protected $_customer = array(90,91,95,115,127,155);
	public function _initialize(){
		if(!session('isLogin') && !session('?isLogin')){
			$this->redirect('Home/Login/index');
		}

		/*权限验证*/
		/*if(C('USER_AUTH_ON')){  
            Rbac::AccessDecision()||$this->redirect('/'.__ROOT__.'/index.php/Home/login/noAuthor');  
        }*/
	}
 	/*
    +----------------------------------------------------------
    * 获取出库时间列表
    +----------------------------------------------------------
    * @param  ;
    +----------------------------------------------------------
    * @return arr
    +----------------------------------------------------------
 	*/
 	public function getDays(){
 		$timeDays = array();
 		$start = strtotime(date('Y-m-d',time()).' 12:00:00')-24*3600;
 		$end = strtotime(date('Y-m-d',time()).' 12:00:00');
 		$timeDays[1] = array('id'=>1,'name'=>'今天','startTime'=>$end,'endTime'=>time());
 		$timeDays[2] = array('id'=>2,'name'=>'昨天','startTime'=>$start,'endTime'=>$end);
 		for ($i=3; $i <= 7; $i++) { 
 			$end = $start;
 			$start = $start - 24*3600;
 			$timeDays[$i] = array('id'=>$i,'name'=>date('Y-m-d',$start),'startTime'=>$start,'endTime'=>$end);
 		}
 		$this->assign('timeDays',$timeDays);		
 	}
 	/*
	 +----------------------------------------------------------
	 * 获取订单类型
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
 	*/
	public function getAwbnoStatus(){
		$orderStatus = array();
		$orderStatus[] = array('statusName'=>'所有','action'=>'like','range'=>'%%');
		$orderStatus[] = array('statusName'=>'已签收','action'=>'IN','range'=>'POD');
		$orderStatus[] = array('statusName'=>'运输中','action'=>'IN','range'=>'C,M,AF,AHK,SDB,SHK,ADB,DF,PL,FD,SD,D,WC,SFF,SPT,SAX,AQA,AEG');
		$orderStatus[] = array('statusName'=>'销毁','action'=>'IN','range'=>'DS');
		$orderStatus[] = array('statusName'=>'转单','action'=>'IN','range'=>'CTO');
		$orderStatus[] = array('statusName'=>'退回','action'=>'IN','range'=>'RTO,ARTO');
		$orderStatus[] = array('statusName'=>'其他','action'=>'NOT IN','range'=>'INF,C,M,AF,AHK,SDB,SAX,ADB,DF,PL,FD,SD,D,WC,SFF,AQA,POD,DS,CTO,RTO,ARTO,SPT,SHK,AEG');
		$this->assign('orderStatus',$orderStatus);	
	}

	/*
	 +----------------------------------------------------------
	 * 获取订单类型
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getAwbnoStatusWinlink(){
		$orderStatus = array();
		$orderStatus[] = array('statusName'=>'全部','action'=>'like','range'=>'%%');
		$orderStatus[] = array('statusName'=>'确认订单','action'=>'eq','range'=>1);
		$orderStatus[] = array('statusName'=>'未确认订单','action'=>'eq','range'=>0);
		$orderStatus[] = array('statusName'=>'入库订单','action'=>'eq','range'=>6);
		$orderStatus[] = array('statusName'=>'出库订单','action'=>'eq','range'=>7);
		$orderStatus[] = array('statusName'=>'退回订单','action'=>'eq','range'=>8);
		$this->assign('orderStatus',$orderStatus);			
	}

	/*
	 +----------------------------------------------------------
	 * 检测字段权限
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	protected function getClient(){
    	if(!in_array(session('ROLE_ID'), array(1, 4, 5,18,7))) {
			$userId = session('loginInfo')['id'];
			if(session('uid') == 128){
				$userId = array('in',$this->_customer);
			}
		}else{
			$userId = array('like','%%');
		}
		return 	$userId;
	}

}