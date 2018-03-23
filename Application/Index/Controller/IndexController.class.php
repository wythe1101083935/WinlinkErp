<?php
/**
 +----------------------------------------------------------
 * wythe,开发平台生成控制器模板
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-15 15:44:56
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Index\Controller;
class IndexController extends CommonController{
    /*记录写入行数*/
    private $rowCount = 0;
	/*
	 +----------------------------------------------------------
	 * cli启动
	 +----------------------------------------------------------
	 * @param  $id 单号;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function index($id){
       $res1 = D('Tracker/WinlinkTrackerUpdate')->updateAuto($id,true,true,true);
	}
    /*
     +----------------------------------------------------------
     * 写入单号到文件
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
	public function write(){
        $rows = 0;
        if (file_exists(APP_ROOT.'/data.txt')){
            unlink(APP_ROOT.'/data.txt');
        }
        $where['status_flag'] = array('NOT IN','POD,DS,CTO,RTA');
        //$where['status_flag'] = array('IN','POD,RTO');
        //$time = time()-3600*24*30;//只要最多90天前出库的订单
       // $where['time'] = array('gt',$time);
        $total = M('Bill')->where($where)->count();
        // 200条写入一次
        $loop = ceil($total / 200);
        $per = 200;
        for ($i = 1; $i <= $loop; $i++) {
            $data = M('Bill')->where($where)->page($i,$per)->getField('awbno',true);
            $this->chunk($data, $i);
        }
        $this->logging('Total Rows is : '.$total);
        $this->logging('Total Page is : '.$loop);
	}
    /*
     +----------------------------------------------------------
     * 写入data.txt文件
     +----------------------------------------------------------
     * @param  ;
     +----------------------------------------------------------
     * @return json{status:bool,msg:string,data:mix,code:int}
     +----------------------------------------------------------
    */
    public function chunk($data, $page)
    {
        $fp = fopen(APP_ROOT.'/data.txt', 'a');
        foreach ($data as $awbno) {
            fwrite($fp, $awbno."\r\n");
            $this->rowCount++;
        }
        fclose($fp);
        $this->logging('Page '.str_pad($page, 3, 0, STR_PAD_LEFT).' Processed');
    }
}