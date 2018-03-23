<?php
/**
 +----------------------------------------------------------
 * 从FirstFilght获取轨迹
 +----------------------------------------------------------
 * CODE:232
 +----------------------------------------------------------
 * TIME:2018-01-20 11:45:31
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace CliHandle\Controller;
use CliHandle\Controller\CommonController;
set_time_limit(1000);
// printMsg($msg,$type='process',$sign='')
class FirstFlightTrackerController extends CommonController{
	/*
	 +----------------------------------------------------------
	 * 最多循环访问10次
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function index($awbno){
		$this->printMsg('update Tracker from Tracker of '.$awbno,'start');
			$this->updateRecursion($awbno,10);
		$this->printMsg('update Tracker from Tracker of '.$awbno,'end');
	}

	/*
	 +----------------------------------------------------------
	 * 递归更新
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateRecursion($awbno,$times=10){
		$res = D('Tracker/FirstFlightTracker')->updateToLocal($awbno);
		if(!$res['status'] && $res['code']=='841001' && $times>0){
			$this->updateRecursion($awbno,$times-1);
		}elseif(!$res['status'] && $res['code']=='841001' && $times<=0){
			$this->printMsg('connection error over 10 times','process');
		}else{
			$this->printMsg($res['msg'],'process','RECURSION');
		}
	}

	/*
	 +----------------------------------------------------------
	 * 找到需要更新的订单,且将其写入文件
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return arr $awbnos
	 +----------------------------------------------------------
	*/
	public function writeAwbnos(){
		$where['ext_code'] = array('NOT IN','POD,RTO,ARTO,DS,INF');
		//$orders =  M('ViewOrderLastTracker')->where($where)->getField('awbno',true);
		$orders1 =  M('ViewOrderLastTracker')->where($where)->getField('awbno',true);
		$orders2 =  M('ViewOrderLastTracker')->getField('awbno',true);
		$orders3 =  M('Bill')->getField('awbno',true);
		$ordersTmp = array_diff($orders3,$orders2);
		$orders = array_merge($orders1,$ordersTmp);
		//$orders = array_slice($orders,0,300);
		$rowCount = 0;//记录页记录数
		$count = count($orders);
		//$pages = ceil($count/ASYNC_NUM);
		$this->printMsg('Awbno Write To Txt','start');
		$fp = fopen(APP_ROOT."/Cli/awbnos.bat",'a');
		$page =1;
		fwrite($fp,"@echo off&setlocal enabledelayedexpansion"."\r\n");
		foreach ($orders as $key => $val) {		
			fwrite($fp,":a".$val."\r\n");
			fwrite($fp,'set i=0'."\r\n");
			fwrite($fp,'for /f "delims=" %%i in (\'tasklist^|findstr /i "php.exe"\') do ('."\r\n");
			fwrite($fp,'set /a i+=1'."\r\n");
			fwrite($fp,')'."\r\n");
			fwrite($fp,'if !i! geq '.ASYNC_NUM.' (goto a'.$val.') else (start /B php.exe E:\\WWW\\myjob\\htdocs\\cli.php /CliHandle/FirstFlightTracker/index/awbno/'.$val.' )'."\r\n");
			$rowCount++;
			if($rowCount == ASYNC_NUM or $rowCount == $count ){		
				$this->printMsg('Page '.$page.' writed,Row Count:'.$rowCount);
				$rowCount = 0;
				$page++;
				//fwrite($fp,'@call E:\\WWW\\myjob\\htdocs\\Cli\\awbnos'.$page.'.bat'."\r\n");
				//fwrite($fp,'@cmd /k echo.');
				//fclose($fp);		
				//$fp = fopen(APP_ROOT."/Cli/awbnos".$page.".bat",'a');
				//fwrite($fp,'@echo off'."\r\n");				
			}
		}
		fwrite($fp,'@cmd /k echo.');
		fclose($fp);
		$this->printMsg('Awbno Write To Txt','end');
	}

	public function getOrders(){
		$where['ext_code'] = array('NOT IN','POD,RTO,ARTO,DS,INF');
		return $orders =  M('ViewOrderLastTracker')->where($where)->getField('awbno',true);		
	}
	/*
	 +----------------------------------------------------------
	 * 调用文件，开始脚本
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return 
	 +----------------------------------------------------------
	*/
	public function start(){
		/*1.执行写入操作*/
		//system('php E:\WWW\myjob\htdocs\cli.php CliHandle/FirstFlightTracker/writeAwbnos');
		$orders = $this->getOrders();
		/*2.开启程序执行*/
		$arr= array();
		foreach ($orders as $key => $value) {
			$arr[] = proc_open('php E:\\WWW\\myjob\\htdocs\\cli.php /CliHandle/FirstFlightTracker/index/awbno/'.$value);
			//echo('php E:\\WWW\\myjob\\htdocs\\cli.php /CliHandle/FirstFlightTracker/index/awbno/'.$value);
			//popen('php E:\\WWW\\myjob\\htdocs\\cli.php /CliHandle/FirstFlightTracker/index/awbno/'.$value);
			//echo 'php E:\WWW\myjob\htdocs\cli.php /CliHandle/FirstFlightTracker/index/awbno/'.$value;
		}
		/*sleep(10);
		foreach ($arr as $key => $value) {
			if(!feof($value)){
				$read = fgets($value);
				echo $read;
			}
		}*/
		//php E:\WWW\myjob\htdocs\cli.php /CliHandle/FirstFlightTracker/start
	}
}