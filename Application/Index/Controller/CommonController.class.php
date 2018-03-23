<?php
/**
 +----------------------------------------------------------
 * 命令行处理公共文件
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-03-16 09:09:12
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Index\Controller;
use Think\Controller;
class CommonController extends Controller{
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

    private function output($message) {
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
    public function outPutMsgForCli($res,$pre=''){
        if($res['status']){
            $this->logging($pre.': Process '.$res['msg']);
        }else{
            $this->error($pre.': Process '.$res['msg']);
        }
    }	
}