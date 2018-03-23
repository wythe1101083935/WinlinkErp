<?php
namespace Index\Controller;
class ChangeTrackerController extends CommonController{
    public function change(){
     set_time_limit(0);
    	//$this->logging('test');
      $awbnos = M('TrackerInfo')->field('distinct awbno')->where(array('tracker_id'=>array('exp','is null')))->select();
      foreach ($awbnos as $key => $val) {
           $sql = 'update crm_tracker_info ti set tracker_id = (
                            select t.id from crm_tracker as  t
                            where upper(ti.location) = upper(t.city)
                            and upper(ti.`status`) = upper(t.`code`)
                        limit 1
                    )
                   where ti.awbno = "'.$val['awbno'].'"';
            $erows = M()->execute($sql);
            $this->logging($val['awbno'].':'.$erows);
      }
    }
}