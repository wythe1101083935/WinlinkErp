<?php
/**
 +----------------------------------------------------------
 * 航班信息记录表
 +----------------------------------------------------------
 * detail:
 +----------------------------------------------------------
 * TIME:2018-03-10 14:33:36
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace DispatchToForeign\Controller;
use Common\Controller\CommonController;
class FlightInfoController extends CommonController{
	/*
	 +----------------------------------------------------------
	 *首页展示页
	 +----------------------------------------------------------
	 * @param  void;
	 +----------------------------------------------------------
	 * @return view
	 +----------------------------------------------------------
	*/
	public function index(){
		$this->display();
	}
	/*
	 +----------------------------------------------------------
	 * 首页ajax获取数据页
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{rows:array,count:int}
	 +----------------------------------------------------------
	*/
	public function XHRIndex(){
		/*1.获取post传入数据,content-type不是multipart/form-data*/
			$postData = json_decode(str_replace('/', '-', file_get_contents('php://input')), true);	

		/*5.排序*/
			$order = $postData['sortName'].' '.$postData['sortOrder'];

		/*6.查询数据*/
			if(!$postData['pageNumber'] && !$postData['pageSize']){
				$postData['pageNumber'] = 1;
				$postData['pageSize'] = 2500;
			}		
			$data = D('ViewFlightInfo')->where($where)->page($postData['pageNumber'] ,$postData['pageSize'])->order($order)->select();
			array_push($data,array('flight_info_id'=>0));
			$count = D('ViewFlightInfo')->where($where)->count();

		/*7.返回数据*/
			$this->ajaxReturn(array('total' => $count, 'rows' => $data));	
	}
	/*
	 +----------------------------------------------------------
	 * 修改/增加
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function update($flight_info_id,$field,$value){
		//新增
		if($flight_info_id == 0){
			if($field=='flight_info_date'){
				$value = strtotime($value.' 00:00:00');
				$data['flight_info_date'] = $value;
				$data['create_user_id'] = session(C('USER_AUTH_KEY'));
				$data['create_time'] = time();
				$data['update_user_id'] = session(C('USER_AUTH_KEY'));
				$data['update_time'] = time(); 
				$res = M('FlightInfo')->data($data)->add();
				$return = ReturnData(true,'添加成功',$res);
			}else{
				$return =  ReturnData(false,'请先添加时间！');
			}
		//更新
		}else{
			$data['flight_info_id'] = $flight_info_id;
			if($field=='flight_info_date'){
				$value = strtotime($value.' 00:00:00');
			}
			$data[$field] = $value;
			$data['update_time'] = time();
			$data['update_user_id'] = session(C('USER_AUTH_KEY'));
			$res = M('FlightInfo')->data($data)->save();
			if($res){
				$return = ReturnData(true,'修改成功!',false,$value);
			}else{
				$return = ReturnData(false,'未作修改！');
			}
		}
		$this->ajaxReturn($return);
	}
}