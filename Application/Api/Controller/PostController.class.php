<?php
namespace Api\Controller;
use Think\Controller;

class PostController extends Controller {
	public function index() {
		// 解析JSON
		$postJsonString = trim(file_get_contents('php://input'));
		$requestMethod = trim($_SERVER['REQUEST_METHOD']) == 'POST' ? true : false;
		$contentType = strtolower($_SERVER['CONTENT_TYPE']);
		if (!stristr($contentType, 'application/json')) {
			return $this->error('请求的ContentType只能为application/json类型', 400);
		}
		if ($requestMethod === false) {
			return $this->error('请求的方法只能为POST', 405);
		}
		if (!$postJsonString) {
			return $this->error('空白的JSON数据提交', 400);
		}
		// 解析开始
		$json = json_decode($postJsonString, true);
		if (json_last_error()) {
			// 回报
			return $this->error($this->jsonError(json_last_error()), 400);
		}
		// 寻找授权Keys
		if (!array_key_exists('keys', $json)) {
			return $this->error('错误的授权码字符串', 400);
		}
		$keys = $json['keys'];
		$keysExist = M('Keys')->where(array('keys_value' => trim($keys)))->find();
		if (!$keysExist) {
			return $this->error('指定的授权码不存在', 400);
		}
		if ($keysExist['keys_ip']) {
			$allowIPs = explode(',', $keysExist['keys_ip']);
			if (!in_array(get_client_ip(), $allowIPs)) {
				return $this->error('请求的IP不在容许的范围内', 400);
			}
		}
		if (!array_key_exists('data', $json)) {
			return $this->error('提交的处理数据不能为空');
		}
		$errorMsg = array();
		$loop = 1;
		$success = 0;
		$db = M('Awb');
		$info = M('TrackerInfo');
		$feeDb = M('Bill');
		foreach ($json['data'] as $key => $value) {
			$item = $db->where(array(
				'awbno' => $key,
				'RefCode'=>$key,
				'userRefId'=>$key,
				'_logic'=>'or'
			))->find();
			/*if (is_numeric($key)) {//RefCode和userRefId可能为纯数字
				$errorMsg[$key] = '第' . $loop . '条数据单号格式错误';
				$loop++;
				continue;
			}*/
			if (!$item) {
				$errorMsg[$key] = '单号' . $key . '不存在';
				$loop++;
				continue;
			}
			foreach ($value as $record) {
				$recordStatus = $info->where(array(
					'awbno' => $item['awbno'],
					'status' => $record['code'],
					'location' => $record['location']
				))->find();
				if (!$recordStatus) {
					// 添加新的纪录
					$info->data(array(
						'awbno' => $item['awbno'],
						'create_time' => strtotime($record['time']),
						'status' => $record['code'],
						'location' => $record['location'],
						'remarks' => $record['remark'],
						'api_id' => $keysExist['keys_names']
					))->add();
				}
				if (in_array($record['code'], array('POD', 'RTO', 'DS'))) {
					$db->where(array('awbno' => $item['awbno']))->data(array(
						'status_flag' => $record['code'],
						'last_date' => time(),
						'last_check' => time(),
					))->save();
					$fee = M()->query(sprintf('SELECT * FROM showservicefee WHERE awb_number = "%s";', $item['awbno']));
					if (count($fee)) {
						$feeId = $feeDb->where(array('awbno' => $item['awbno']))->find();
						if (!$feeId) {
							continue;
						}
						$feeDb->where(array(
							'id' => $feeId['id'],
						))->data(array(
							'service_fee' => $fee['service_fee'],
							'ysje' => $fee['transer_fee'] + $fee['service_fee'],
						))->save();
					}
				}
			}
			$success++;
		}
		if (count($errorMsg)) {
			return $this->error('发生如下的错误:' . implode(',', $errorMsg));
		}
		echo json_encode(array(
			'status' => true,
			'processed' => $success,
		));
	}

	protected function error($message, $code = 200) {
		// 发送响应头
		$this->sendHeader($code);
		echo json_encode(array(
			'status' => false,
			'processed' => 0,
			'message' => $message,
		));
		return false;
	}

	protected function sendHeader($code) {
		$messageArray = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Move Temporarily',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			449 => 'Retry With',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended',
			600 => 'Unparseable Response Headers',
		);
		$message = $messageArray[intval($code)];
		header(sprintf('HTTP/1.1 %s %s', $code, $message));
		return true;
	}

	protected function jsonError($code) {
		$messageArray = array(
			'JSON_ERROR_NONE' => '没有错误发生',
			'JSON_ERROR_DEPTH' => '到达了最大堆栈深度',
			'JSON_ERROR_STATE_MISMATCH' => '无效或异常的 JSON',
			'JSON_ERROR_CTRL_CHAR' => '控制字符错误，可能是编码不对',
			'JSON_ERROR_SYNTAX' => '语法错误',
			'JSON_ERROR_UTF8' => '异常的 UTF-8 字符，也许是因为不正确的编码。',
			'JSON_ERROR_RECURSION' => 'One or more recursive references in the value to be encoded',
			'JSON_ERROR_INF_OR_NAN' => 'One or more NAN or INF values in the value to be encoded',
			'JSON_ERROR_UNSUPPORTED_TYPE' => '指定的类型，值无法编码。',
			'JSON_ERROR_INVALID_PROPERTY_NAME' => '指定的属性名无法编码',
			'JSON_ERROR_UTF16' => '畸形的 UTF-16 字符，可能因为字符编码不正确',
		);
		$messageKey = array_keys($messageArray);
		if (is_numeric($code)) {
			$message = $messageArray[$messageKey[$code]];
		} else {
			$message = $messageArray[$code];
		}
		return $message;
	}
}