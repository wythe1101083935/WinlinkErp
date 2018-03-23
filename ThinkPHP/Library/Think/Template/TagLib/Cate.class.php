<?php
namespace Think\Template\TagLib;
use Think\Template\TagLib;
class Cate extends TagLib {

    // 标签定义
    protected $tags = array(
		'list' => array('attr' => 'model,field,sql','level' => 3),
		'name' => array('attr' => 'model,where,field','close'=>0),
    );
	public function _name($attr) {
		$str='<?php
			echo M("'.$attr["model"].'")->where("id=".$vo["'.$attr["where"].'"])->getField("'.$attr["field"].'");
		?>';
		return $str;
    }

	public function _list($attr, $content){
		$str ='<?php 
		$_list=M("'.$attr["model"].'")->where("'.$attr["sql"].'")->field("'.$attr["field"].'")->select();
			foreach($_list as $v):
		?>';
		$str.=$content;
		$str.='<?php endforeach;?>';
		return $str; 
	}
}

?>