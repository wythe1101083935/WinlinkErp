<?php
/**
 +----------------------------------------------------------
 * awb订单信息表
 +----------------------------------------------------------
 * CODE:
 +----------------------------------------------------------
 * TIME:2018-02-06 09:23:01
 +----------------------------------------------------------
 * author:wythe(汪志虹)
 +----------------------------------------------------------
 */
namespace Order\Model;
use Think\Model;
class AwbModel extends Model{
	protected $_validate = array(
		array('weight',"/^[0-9.]+$/",'重量只能为数字！'),  	 //weight //客户重量
		array('product','require','不能为空！'),   	 //product //包裹类型 DOX 文件 XPS 包裹
		array('noofpieces',"/^[0-9]+$/",'数量只能为整数！'),  //noofpieces //包裹数量
		//array('Consignee','require','收件人公司不能为空！'),    //Consignee //收件人公司
		array('ConsigneeName','require','收件人姓名不能为空！'),   //ConsigneeName //收件人姓名
		array('ConsigneePhone',"/^[0-9]{6,}$/",'电话格式不正确！只能为纯数字'),  //ConsigneePhone //收件人电话 
		array('ConsigneeCountry','require','国家不能为空！'),  //ConsigneeCountry //收件人国家
		array('ConsigneeCity','require','城市不能为空！'),  //ConsigneeCity //收件人城市
		array('ConsigneeAddress1','require','地址不能为空！'),  //ConsigneeAddress1 //地址
		//array('ConsigneeTel',/^[0-9.]*/,'不能为空！'),   //ConsgineeTel
		array('postcode',"require",'邮编不能为空！'),   //postcode //收件人邮编
		//array('customnote','require','不能为空！'),   //customnote //备注
		array('ServiceType','require','付款类型不能为空！'),   //ServiceType //NCND到付 NOR预付
		array('InAmt','/^[0-9.]+$/','代收金额只能为数字！'),   //InAmt //代收金额
		array('RefCode','require','不能为空！'),  
		array('transportMode','require','不能为空！'),  
		array('WithBattery','require','是否带电不能为空！'),  //battery //是否带电  1带电 0不带电
		//array('userRefId','require','不能为空！'), //ShipperRef //原单号  
    );
    protected $_map = array(
    	'battery'=>'WithBattery',
    	'ShipperRef'=>'userRefId',
    );
	/*
	 +----------------------------------------------------------
	 * 确认订单,将is_confirm改为1，若已经大于1的不修改
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function confirmOrder($awbnos){
		$where['awbno'] = array('IN',$awbnos);
		$where['is_confirm'] = 0;
		$res = $this->where($where)->setField('is_confirm',1);
		if($res){
			$return = ReturnData(true,'已确认！','','');
		}else{
			$return = ReturnData(false,'已经确认！','','');
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 获取订单详情
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function getOrderDetail($awbno){
		$row = $this->field('id,weight,product,noofpieces,Consignee,Destination as Destination,ConsigneeName,ConsigneePhone,ConsigneeCountry,ConsigneeCity,ConsigneeAddress1,ConsigneeTel,postcode,ServiceType,InAmt,RefCode,transportMode,WithBattery,userRefId,customnote,is_confirm')->where(array('awbno'=>$awbno))->find();
		$formData = $row;
		$formData['battery'] = $row['withbattery'];
		$formData['ShipperRef'] = $row['userrefid'];
		$formData['Consignee'] = $formData['consignee'];
		$formData['ConsigneeName'] = $formData['consigneename'];
		$formData['ConsigneePhone'] = $formData['consigneephone'];
		$formData['ConsigneeCountry'] = $formData['consigneecountry'];
		$formData['ConsigneeCity'] = $formData['consigneecity'];
		$formData['ConsigneeAddress1'] = $formData['consigneeaddress1'];
		$formData['postcode'] = $formData['postcode'];
		$formData['ServiceType'] = $formData['servicetype'];
		$formData['ConsigneeTel'] = $formData['consigneetel'];
		$formData['InAmt'] = $formData['inamt'];
		if(preg_match('/^5.*$/',$formData['transportmode'])){
			$formData['FromCountry'] = 'CZX';
		}else{
			$formData['FromCountry'] = 'DXB';
		}
		$tableData = M('Invoice')->field('id as orderId,orderName as invoice_name,orderWeight as invoice_weight,orderPcs as invoice_pcs,orderDescription as invoice_description,orderInsvalue as invoice_insvalue,orderHscod as invoice_hscod')->where(array('awbid'=>$row['id']))->select();
		foreach ($tableData as $key => &$val) {
			$val['id'] = $key;
		}
		return array('formData'=>$formData,'tableData'=>$tableData);
	}
	/*
	 +----------------------------------------------------------
	 * 创建单个订单,API1,用于单个创建订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/	
	public function createOrder($Data){
		$formData = $Data['formData'];
		$invoiceData = $Data['tableData'];
		//判断是否是客户新建
		if(isset($formData['user']) && $formData['user']){
			$userid = $formData['user'];
		}else{
			$userid = session(C('USER_AUTH_KEY'));
		}
		$userRaw = M("User")->where(array('id' =>$userid))->find();
		$auth = base64_encode($userRaw['appid'] . ':' . md5($userRaw['appid'] . $userRaw['appkey']));//获取验证号
		/*渠道代码验证*/
		$modeList = D('CountryCode')->getTransportMode();
		$key = sprintf('%s-%s', strtoupper($formData['FromCountry']), strtoupper($formData['ConsigneeCountry']));
		$transportModeValue = $modeList[$key];
		//$return  = ReturnData(false,'ceshi',$data,$modeList);
		if (!$transportModeValue) {
			$return = ReturnData(false,'所选国家没有匹配的运输渠道','','');
		}else{
			$data = array(
				"remark" => $formData['customnote'],
				"shipperRef" => $formData['ShipperRef'],
				"transportMode" => $transportModeValue,
				"is_confirm" => false,
				"cargoInfo" => array(
					"ncndAmt" => isset($formData['InAmt']) ? floatval($formData['InAmt']) : 0,
					"noofPieces" => intval($formData['noofpieces']),
					"packageType" => $formData['product'],
					"service" => $formData['ServiceType'],
					"weight" => floatval($formData['weight']),
					"battery" => $formData['battery'] ? true : false,
				),
				"consigneeInfo" => array(
					"address" => $formData['ConsigneeAddress1'],
					"address2" => $formData['ConsigneeAddress1'],
					"company" => $formData['Consignee'],
					"contact" => $formData['ConsigneeName'],
					"destCode" => $formData['ConsigneeCity'],
					"mobile" => $formData['ConsigneePhone'],
					"tel" => $formData['ConsigneeTel'],
					"zipCode" => $formData['postcode'],
					"country" => $formData['ConsigneeCountry'],
				),
				"invoiceInfo" => array(),
			);
			foreach ($invoiceData as $key => $val) {
				$data["invoiceInfo"][] = array(
					"invoiceHSCod" => $val['invoice_hscod'],
					"invoiceInsValue" => floatval($val['invoice_insvalue']),
					"invoiceName" => $val['invoice_name'],
					"invoicePCS" => intval($val['invoice_pcs']),
					"invoiceWeight" => floatval($val['invoice_weight']),
					"invoiceDescription" => trim($val['invoice_description']),
				);
			}
			$res = HttpRequest('wlapi.winlinklogistics.com/wuliu/prebook','json','post',$data,array('type'=>1,'User-Auth'=>$auth));
			if($res['status'] != false){
				$return = ReturnData(true,'处理成功!'.$res['orderId'],$res['orderId'],$res);
			}else{
				$return = ReturnData(false,'新增失败，系统错误：'.(!array_key_exists('message', $res) ? '未知错误, 原始错误:' . $res : $res['message']),$res,'');
			}
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 创建单个订单,API2,用于另一种形式的订单创建，使用渠道代码创建
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function createOrderForTransportMode($Data){
		$formData = $data['formData'];
		$invoiceData = $data['tableData'];
		//判断是否是客户新建
		if(isset($formData['user']) && $formData['user']){
			$userid = $formData['user'];
		}else{
			$userid = session(C('USER_AUTH_KEY'));
		}
		$userRaw = M("User")->where(array('id' =>$userid))->find();
		$auth = base64_encode($userRaw['appid'] . ':' . md5($userRaw['appid'] . $userRaw['appkey']));//获取验证号
		/*货物详情处理*/
		$values = explode(',', str_replace('，', ',', $Data['ValueOfShipment']));
		$hscode = explode(',', str_replace('，', ',', $Data['Retail_Code']));		
		$pcs = explode(',', str_replace('，', ',', $Data['PCS']));
		$name = explode(',', str_replace('，', ',', $Data['GoodsDesc']));
		$weights = explode(',', str_replace('，', ',', $Data['weight']));
		$description = explode(',', str_replace('，', ',', $Data['Description']));
		if(count($hscode) != count($values) || count($values) != count($values) || count($pcs) != count($weights) || count($weights) != count($description) || count($description) != count($name)){
			$return = ReturnData(false,sprintf('货物数据条数不正确：GoodsDesc:%s条;PCS:%s条;Retail Code:%s条;ValueOfShipment:%s条;Weight:%s条;Description:%s条',count($name),count($pcs),count($hscode),count($values),count($weights),count($description)),'','');
		}else{
			/*组合货物详情*/
			foreach ($values as $key => $vx){
				/*验证货物参数是否正确*/
				$checkRes = $this->checkValue(array(
					array('name'=>'Value','type'=>'number','value'=>$vx,'key'=>$key+1),
					array('name'=>'Retail Code','type'=>'noSpecial','value'=>$hscode[$key]),
					array('name'=>'GoodsPcs','type'=>'number','value'=>$pcs[$key]),
					array('name'=>'weight','type'=>'number','value'=>$weights[$key]),
					array('name'=>'GoodsDesc','type'=>'required','value'=>$name[$key]),
				));
				if(!$checkRes['status']){
					return $checkRes;    //此处不分开，直接返回
				}
				/*货物信息*/
				$invoices[] = array(
					"invoiceHSCod" => $hscode[$key],
					"invoiceInsValue" => floatval($vx),
					"invoiceName" => $name[$key],
					"invoicePCS" => $pcs[$key],
					"invoiceWeight" => floatval($weights[$key]),
					"invoiceDescription" => trim($description[$key])
				);
			}
			/*组合发送信息*/	
			$data = array(
				"remark" => $Data['customnote'],
				"shipperRef" => $Data['ShipperRef'],
				"transportMode" => $Data['transportMode'],
				"cargoInfo" => array(
					"ncndAmt" => floatval($Data['InAmt']),
					"noofPieces" => intval($Data['noofpieces']),
					"packageType" => $Data['product'],
					"service" => $Data['ServiceType'],
					"weight" => $Data['TotalWeight'],
					"battery" => $Data['WithBattery'] == 1 ? true : false,
				),
				"consigneeInfo" => array(
					"address" => $Data['ConsigneeAddress1'],
					"address2" => $Data['ConsigneeAddress2'],
					"company" => $Data['Consignee'],
					"contact" => $Data['ConsigneeName'],
					"destCode" => $Data['ConsigneeCity'],
					"mobile" => $Data['ConsigneePhone'],
					"tel" => $Data['ConsigneeTel'],
					"zipCode" => $Data['Zipcode'],
					"country" => $Data['Destination'],
				),
				"invoiceInfo" => $invoices,
			);
			$res = HttpRequest('http://wlapi.winlinklogistics.com/wuliu/delivery','json','post',$data,array('type'=>1,'User-Auth'=>$auth));
			if($res['status'] != false){
				$return = ReturnData(true,'处理成功!'.$res['orderId'],$res['orderId'],$res);
			}else{
				$return = ReturnData(false,'新增失败：'.(!array_key_exists('message', $res) ? '未知错误, 原始错误:' . $res : $res['message']),$res,'');
			}
		}		
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * api获取订单的pdf链接
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function pdfLink($awbno){
		set_time_limit(0);
		$awbno = explode(',',$awbno);
		$uid = session(C("USER_AUTH_KEY"));
		$userRaw = M("User")->where(array('id' => $uid))->find();
		if (!$userRaw['appid']) {
			$userRaw = M("User")->where(array('id' => 1))->find();
		}
		$auth = base64_encode($userRaw['appid'] . ':' . md5($userRaw['appid'] . $userRaw['appkey']));
		$order = array('orderSN' => $awbno);
		$dataJson = HttpRequest('http://wlapi.winlinklogistics.com/wuliu/response','json','post',array('orderSN'=>$awbno),array('type'=>1,'User-Auth'=>$auth));
		foreach ($dataJson['info'] as $key => $id) {
			$filePDFArray[$key] = $id['pdf'];
		}	
		return $filePDFArray;	
	}
	/*
	 +----------------------------------------------------------
	 * 更新并确认订单
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function updateOrder($data){
		$formData = $data['formData'];
		$tableData = $data['tableData'];
		foreach ($tableData as $key => $val) {
			$formData['orderName'][$key] = $val['invoice_name'];
			$formData['orderWeight'][$key] = $val['invoice_weight'];
			$formData['orderPcs'][$key] = $val['invoice_pcs'];
			$formData['orderDescription'][$key] = $val['invoice_description'];
			$formData['orderInsvalue'][$key] = $val['invoice_insvalue'];
			$formData['orderHscod'][$key] = $val['invoice_hscod'];
			if(isset($val['orderid']) && $val['orderid']){
				$formData['orderId'][$key] = $val['orderid'];
			}
		}
		$formData['awb_id'] = $formData['id'];
		$formData['userRefId'] = $formData['ShipperRef'];
		$formData['WithBattery'] = $formData['battery'];
		$formData['ConsgineeTel'] = $formData['ConsigneeTel'];

		$url = 'http://wlapi.winlinklogistics.com/wuliu/append?id='.$formData['id'].'&confirm=1';
		
		$super = M('User')->where(array('id' => 1))->find();
		$auth = base64_encode($super['appid'] . ':' . md5($super['appid'] . $super['appkey']));	
		$res = HttpRequest($url,'json','post',$formData,array('type'=>1,'User-Auth'=>$auth));
		if($res['status']){
			$return = ReturnData(true,'订单已更新并确认！');
		}else{
			$return = ReturnData(false,$res['message'],$formData,$tableData);
		}
		return $return;		
	}
	/*
	 +----------------------------------------------------------
	 * 验证字段，用于第一个API验证
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	public function checkFields($data){
		$res = $this->create($data);
		if($res){
			//if()判断渠道代码
			$return = ReturnData(true,'Sccess');
		}else{
			$return = ReturnData(false,$this->getError(),'','');
		}
		return $return;
	}
	/*
	 +----------------------------------------------------------
	 * 验证货物详情，用于第二个API
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	private function checkValue($data){
		$msg = '';
		foreach ($data as $key => $val) {
			if($val['type'] == 'required'){
				if(!$val['value']){
					$msg .= sprintf('第%s个逗号前的%s不能为空！',$data[0]['key'],$val['name']);
				}else{
					$msg .= '';
				}
			}elseif($val['type'] == 'number'){
				if(!is_numeric($val['value'])){
					$msg .= sprintf('第%s个逗号前的%s只能为数字！',$data[0]['key'],$val['name']);
				}else{
					$msg .= '';
				}
			}elseif($val['type'] = 'noSpecial'){
				if(!preg_match('/^[-0-9a-zA-Z]+$/',$val['value'])){
					$msg .= sprintf('第%s个逗号前的%s不能含除字母和数字外的特殊字符！',$data[0]['key'],$val['name']);
				}else{
					$msg .= '';
				}
			}else{
				$msg .= '';
			}
		}
		if($msg == ''){
			$return = ReturnData(true);
		}else{
			$return = ReturnData(false,$msg,'','');
		}
		return $return;
	}
}
?>