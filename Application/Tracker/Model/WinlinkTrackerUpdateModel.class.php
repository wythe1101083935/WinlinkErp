<?php
/**
 +----------------------------------------------------------
 * winlink轨迹发生更新,由于当前轨迹订单表结构不合逻辑，后期需要更改，直接改此文件
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:alt+t
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Tracker\Model;
class WinlinkTrackerUpdateModel extends NetModel{
	/*用于标记是否需要更新异常轨迹*/
	private $updateExceptionSign = true;

	/*用于保存异常轨迹*/
	private $winlinkTracker = false;

	/*结束状态标记*/
	private $overStatus = array('POD');

	/*保存是否是命令行模式*/
	protected $isCli = false;

	/*保存订单号*/
	protected $awbno = '';

	/*轨迹更新api*/
    private $api = array(
        array('tracker_code'=>'SFF','model'=>'Tracker/FirstFlightTracker'),
        array('tracker_code'=>'SAX','model'=>'Tracker/AxlTracker'),
    );

    /*向客户推送轨迹*/
    private $apiClient = array(
    	array('client'=>'49,114','name'=>'banggu','model'=>'Tracker/BangGuTracker')
    );
	/*排序函数*/
	private function sortByTimeAsc($a,$b){
        return $a['time'] - $b['time'];
	}
	/*
	 +----------------------------------------------------------
	 * 更新bill表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function updateBill($pop){
		$where = array(
			'awbno'=>$pop['awbno'],
			'status_flag'=>$pop['status']
		);
		$isHas = M('Bill')->where($where)->count();
		if($isHas){
			$return = ReturnData(true,'update bill tracker not need');
		}else{
	        $data = array(
	            'status_flag' => $pop['status'],
	            'update_time' => time(),
	            'finish_time' => in_array($pop['status'], $this->overStatus) ? $pop['time'] : 0,
	            'finish_remarks' => $pop['remarks']
	        );  
	        $res = M('Bill')->where(array('awbno'=>$pop['awbno']))->data($data)->save();
	        if($res){
	        	$return = ReturnData(true,'update bill success');
	        }else{
	        	$return = ReturnData(false,$pop['awbno'].'update bill error','','');
	        }	
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 判断有没有这条轨迹,若没有,插入到轨迹表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function updateTracker($val){
    	$whereIsHas = array(
    		'awbno'=>$val['awbno'],
    		'create_time'=>$val['time'],
    		'status'=>$val['status']
    	);
    	$isHas = M('TrackerInfo')->where($whereIsHas)->count();
    	if($isHas < 1){
    		$data = array(
                'awbno'=>$val['awbno'],
                'status'=>$val['status'],
                'create_time'=>$val['time'],
                'location'=>$val['location'],
                'remarks'=>$val['remarks'],
                'delivered_to'=>$val['to']
    		);
    		$res = M('TrackerInfo')->data($data)->add();
    		if($res){
    			$return = ReturnData(true,'insert one tacker success');
    		}else{
    			$return = ReturnData(false,'insert one tarcker error','','');
    		}
    	}else{
    		$return = ReturnData(true,'tracker not need update');
    	}
    	return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 获取异常轨迹列表
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return arr $data
	 +----------------------------------------------------------
	*/
	public function getWinlinkTracker(){
        $tracker =  M('WinlinkTracker')->select();
        $data = array();
        foreach ($tracker as $key => $val) {
            $data[$val['remarks']] = $val;
        }
        return $data;
	}
	/*
	 +----------------------------------------------------------
	 * 处理异常轨迹
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function updateExceptionTracker($val,$status){
		if($this->winlinkTracker == false){
			$this->winlinkTracker = $this->getWinlinkTracker();
		}
	    if(isset($this->winlinkTracker[$val['remarks']]) && $this->updateSign){
	    	$res = M('OrderTrackerHandle')->where(array('awbno'=>$val['awbno']))->count();
	    	/*如果不存在异常，则新增*/
	    	if(!$res){
	    		$data = array(
	                'awbno'=>$val['awbno'],
	                'time'=>$val['time'],
	                'status'=>$status,
	                'create_time'=>time(),
	                'tracker_id'=>$winlinkTracker[$val['remarks']]['id']
	    		);
	    		$res1 = M('OrderTrackerHandle')->data($data)->add();
	    		if($res1){
	    			$return = ReturnData(true,'insert one Exception tracker to order');
	    		}else{
	    			$return = ReturnData(false,'insert Exception tracker error','','');
	    		}
	    	/*存在异常,覆盖更新*/
	    	}else{    		
	    		$data = array(
		                'status'=>$status,
		                'time'=>$val['time'],
		                'tracker_id'=>$winlinkTracker[$val['remarks']]['id'],
		    		);
		    	$res2 = M('OrderTrackerHandle')->where(array('awbno'=>$val['awbno']))->data($data)->save();
		    	if($winlinkTracker[$val['remarks']]['code']==444){//如果更新了此种异常，不再更新异常
		    		$this->updateSign = false;
		    	}
		    	if($res2){
		    		$return = ReturnData(true,'update Exception tracker');
		    	}    		
	    	}
	    }else{
	    	$return = ReturnData(true,'Not Exception tracker');
	    }
	    return $return;		
	}
	/*
	 +----------------------------------------------------------
	 * 同步到winlink,参数为轨迹
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateTrackerToWinlink($res){
    	$this->outPutMsgForCli($res);
    	if($res['status']){
    		$tracker = $res['data'];
    		/*1.轨迹按时间逆序排序*/
    		if(count($tracker) >= 2){
    			usort($tracker,array($this,'sortByTimeAsc'));
    		}
    		/*2.获取时间最靠近的一条轨迹*/
    		$pop = $tracker[count($tracker)-1];
    		/*3.判断订单是否已经完成*/
    		$status = $pop['status'] == 'POD' ? 1:0;//1 已签收 0 未完成
    		/*4.更新bill表*/
    		$res1 = $this->updateBill($pop);
			$this->outPutMsgForCli($res1);
	        /*5.更新轨迹表*/
	        foreach ($tracker as $key => $val) {
	        	/*(1).判断有没有这条轨迹,若没有,插入到轨迹表*/
	        	$res2 = $this->updateTracker($val);
	        	$this->outPutMsgForCli($res2,$key+1);
	        	/*(2).判断轨迹是否是异常轨迹,若是,判断是否需要插入到异常处理表*/
	        	$res3 = $this->updateExceptionTracker($val,$status);
	        	$this->outPutMsgForCli($res3,$key+1);
	        }
	        if($res1['status'] && $res2['status']){
	        	$return = ReturnData(true,'update tarcker success');
	        }else{
	        	$return = ReturnData(false,'update tracker error',$res1['code'].'--'.$res2['code']);
	        }
    	}else{
    		$return = $res;
    	}      
        return $return;
	}

	/*
	 +----------------------------------------------------------
	 * 主函数，自动从定义的api中抓取轨迹，参数为单号
	 +----------------------------------------------------------
	 * @param  $awbno 单号;
	 * @param  $isCli bool  true(命令行模式输出) false(执行模式返回);
	 * @param  $update bool  true(开启拉取轨迹更新) false(关闭拉取轨迹);
	 * @param  $post bool  true(开启推送轨迹) false(关闭推送轨迹);
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateAuto($awbno,$isCli,$update=true,$post=true){
		$this->awbno = $awbno;
		$this->isCli = $isCli;  
        /*1.从上游获取轨迹*/
        if($update){
        	$where['awbno'] = $awbno;
	        foreach ($this->api as $key => $val) {       
	            $where['status'] = $val['tracker_code'];
	            $count2 = M('TrackerInfo')->where($where)->count();
	            if($count2){
	            	$res = D($val['model'])->getTracker($awbno);
	                $return = $this->updateTrackerToWinlink($res);
	                $this->outPutMsgForCli($return);
	                break;
	            }                
	        }        	
        }
       
        /*2.向下游推送轨迹*/
        if($post){
	        $whereClient['awbno'] = $awbno;
	        foreach ($this->apiClient as $key => $val) {
	        	$whereClient['acountno'] = array('IN',$val['client']);
	        	$count3 = M('Bill')->where($whereClient)->count();
	        	if($count3){
	        		$return1 = D($val['model'])->post($awbno);
	        		$this->outPutMsgForCli($return1);
	        		if(isset($return)){
	        			$return = ReturnDataAdd($return,$return1);
	        		}else{
	        			$return = $return1;
	        		}	
	        		break;
	        	}
	        }
        }

        return $return;
	}
    /*
     +----------------------------------------------------------
     * 输出函数
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function logging($message) {
        $this->output('[Logging] '.$message);
    }
    public function halt($message) {
        $this->output('[Halt] '.$message);
        exit();
    }
    public function error($message) {
        $this->output('[Error] '.$message);
    }
    public function output($message){
    	//echo $message.'<br>';
        echo '[', date('Y-m-d H:i:s'), '] [PHP] ', $message, "\r\n";
    }
	/*
	 +----------------------------------------------------------
	 * 使用命令行更新时,输出提示信息
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function outPutMsgForCli($res,$key=0){
		if($this->isCli){
			if($res['status']){
				$this->logging($this->awbno.':'.($key ? $key : '').' Process '.$res['msg']);
			}else{
				$this->error($this->awbno.':'.($key ? $key : '').' Process '.$res['msg']);
			}
		}
	}	
}