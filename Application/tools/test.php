<?php 
/*以下可以整理成常用工具*/
/*
批量修改
*/
    public function test(){
        $sql = 'select t1.awbno,t3.remarks,t3.`status`,t3.create_time from crm_bill t1,(
    select t2.awbno,max(t2.create_time) as create_time , t2.`status` as `status`,t2.remarks as remarks
    from 
    (select * from crm_tracker_info where awbno in("CNB3306190") 
        order by create_time desc) t2
    group by t2.awbno
    ) t3
    where t1.awbno = t3.awbno';
        $res = M()->query($sql);
        foreach ($res as $key => $val) {
            $data['finish_remarks'] = $val['remarks'];
            $data['finish_time'] = $val['create_time'];
            $data['status_flag'] = $val['status'];
            M('Bill')->where(array('awbno'=>$val['awbno']))->data($data)->save();
        }
    }
/*去除-*/
    public function test1(){
        $where['awbno'] = array('IN','CNB3321353,CNB3320788,CNB3320787,CNB3321351,CNB5063455,CNB5063311,CNB3321349,CNB5063317,CNB5063707,CNB5063694,CNB3320789,CNB5063756,CNB3321192,CNB5063309,CNB3320763,CNB3321352,CNB5064163,CNB5063186,CNB3321130,CNB3321126,CNB5063697,CNB5063695,CNB5063497,CNB5063313,CNB5063314,CNB3321128,CNB5064165,CNB5063188,CNB3321348,CNB3321272,CNB3321127,CNB3320786,CNB5063189,CNB5063755,CNB5064232,CNB3320794,CNB5064233,CNB5063409,CNB3320793,CNB5063154,CNB3321347,CNB5063199,CNB5064166,CNB3321025,CNB3320792,CNB5063315,CNB5063408,CNB5063316,CNB5064164,CNB5063187,CNB3321273,CNB3320849,CNB5063310,CNB3321350,CNB3321354,CNB3320791,CNB3320785');
        $res = M('Awb')->field('awbno,ConsigneePhone as consigneephone')->where($where)->select();
        foreach ($res as $key => $val) {
            //$new = '';
            $str1 = substr($val['consigneephone'],'0',3);
            $str2 = substr($val['consigneephone'],'3',2);
            $str3 = substr($val['consigneephone'],'5',3);
            if($str1 == $str3 && $str2 =='00'){
                $new = substr($val['consigneephone'],5);
                M('Awb')->where(array('awbno'=>$val['awbno']))->setField('ConsigneePhone',$new);
                echo $val['consigneephone'];
                echo '--'.$new;
                echo '<br>';
            }
            //$new = str_replace('-','',$val['consigneephone']);
            //M('Awb')->where(array('awbno'=>$val['awbno']))->setField('ConsigneePhone',$new);
            //echo $new;          
        }
    }
    public function addDeskMulti(){
        $user_id = M('ViewUserClient')->getField('id',true);
        foreach ($user_id as $key => $val) {
            $data['user_id'] = $val;
            $data['node_id'] = 136;
            M('Desk')->data($data)->add();
            $data['node_id'] = 142;
            M('Desk')->data($data)->add();
            $data['node_id'] = 144;
            M('Desk')->data($data)->add();
            $data['node_id'] = 146;
            M('Desk')->data($data)->add();
            $data['node_id'] = 169;
            M('Desk')->data($data)->add();
        }
    }
 ?>