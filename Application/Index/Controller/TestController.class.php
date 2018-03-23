<?php
namespace Index\Controller;
class TestController{
	public function index(){
		$res = M('WinlinkTracker')->where(array('id'=>1))->find();
				
		$res1 = M('TrackerInfo')->where(array('remarks'=>$res['remarks']))->select();
		dump($res1);
	}
}