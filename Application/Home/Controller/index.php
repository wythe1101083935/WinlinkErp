<?php
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
	$error = false;
	$error_fields = "";
	$request_params = array();
	$request_params = $_REQUEST;
	// Handling PUT request params
	if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
		$app = \Slim\Slim::getInstance();
		parse_str($app->request()->getBody(), $request_params);
	}
	foreach ($required_fields as $field) {
		if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
			$error = true;
			$error_fields .= $field . ', ';
		}
	}

	if ($error) {
		// Required field(s) are missing or empty
		// echo error json and stop the app
		$response = array();
		$app = \Slim\Slim::getInstance();
		$response["error"] = true;
		$response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
		echoRespnse(400, $response);
		$app->stop();
	}
}

/**
 * Validating email address
 */
function validateEmail($email) {
	$app = \Slim\Slim::getInstance();
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$response["error"] = true;
		$response["message"] = 'Email address is not valid';
		echoRespnse(400, $response);
		$app->stop();
	}
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
	$app = \Slim\Slim::getInstance();
	// Http response code
	$app->status($status_code);

	// setting response content type to json
	$app->contentType('application/json');
	// 跨域权限设置
	$app->response->headers->set('Access-Control-Allow-Origin', '*');

	echo json_encode($response);
}

/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function () use ($app) {
	// check for required params
	verifyRequiredParams(array('name', 'email', 'password'));

	$response = array();

	// reading post params
	$name = $app->request->post('name');
	$email = $app->request->post('email');
	$password = $app->request->post('password');

	// validating email address
	validateEmail($email);

	$db = new DbHandler();
	$res = $db->createUser($name, $email, $password);

	if ($res == USER_CREATED_SUCCESSFULLY) {
		$response["error"] = false;
		$response["message"] = "You are successfully registered";
		echoRespnse(201, $response);
	} else if ($res == USER_CREATE_FAILED) {
		$response["error"] = true;
		$response["message"] = "Oops! An error occurred while registereing";
		echoRespnse(200, $response);
	} else if ($res == USER_ALREADY_EXISTED) {
		$response["error"] = true;
		$response["message"] = "Sorry, this email already existed";
		echoRespnse(200, $response);
	}
});

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function () use ($app) {
	// check for required params
	verifyRequiredParams(array('email', 'password'));

	// reading post params
	$email = $app->request()->post('email');
	$password = $app->request()->post('password');
	$response = array();

	$db = new DbHandler();
	// check for correct email and password
	if ($db->checkLogin($email, $password)) {
		// get the user by email
		$user = $db->getUserByEmail($email);

		if ($user != NULL) {
			$response["error"] = false;
			$response['name'] = $user['name'];
			$response['email'] = $user['email'];
			$response['apiKey'] = $user['api_key'];
			$response['createdAt'] = $user['created_at'];
		} else {
			// unknown error occurred
			$response['error'] = true;
			$response['message'] = "An error occurred. Please try again";
		}
	} else {
		// user credentials are wrong
		$response['error'] = true;
		$response['message'] = 'Login failed. Incorrect credentials';
	}

	echoRespnse(200, $response);
});

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
	// Getting request headers
	$headers = apache_request_headers();
	$response = array();
	$app = \Slim\Slim::getInstance();

	// Verifying Authorization Header
	if (isset($headers['authorization'])) {
		$db = new DbHandler();

		// get the api key
		$api_key = $headers['authorization'];

		// validating api key
		if (!$db->isValidApiKey($api_key)) {
			// api key is not present in users table

			$response["error"] = true;
			$response["message"] = "Access Denied. Invalid Api key";
			echoRespnse(401, $response);
			$app->stop();
		} else {
			global $user_id;
			// get user primary key id
			$user = $db->getUserId($api_key);
			if ($user != NULL) {
				$user_id = $user["id"];
			}

		}
	} else {
		// api key is missing in header

		$response["error"] = true;
		$response["message"] = "Api key is misssing";
		echoRespnse(400, $response);
		$app->stop();
	}
}

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/tasks', 'authenticate', function () use ($app) {
	// check for required params
	verifyRequiredParams(array('task'));

	$response = array();
	$task = $app->request->post('task');

	global $user_id;
	$db = new DbHandler();

	// creating new task
	var_dump($user_id);
	$task_id = $db->createTask($user_id, $task);

	if ($task_id != NULL) {
		$response["error"] = false;
		$response["message"] = "Task created successfully";
		$response["task_id"] = $task_id;
	} else {
		$response["error"] = true;
		$response["message"] = "Failed to create task. Please try again";
	}
	echoRespnse(201, $response);
});

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/tasks', 'authenticate', function () {
	global $user_id;
	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getAllUserTasks($user_id);

	$response["error"] = false;
	$response["tasks"] = array();

	// looping through result and preparing tasks array
	while ($task = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["id"] = $task["id"];
		$tmp["task"] = $task["task"];
		$tmp["status"] = $task["status"];
		$tmp["createdAt"] = $task["created_at"];
		array_push($response["tasks"], $tmp);
	}

	echoRespnse(200, $response);
});

/**
 * Listing single task of particual user
 * method GET
 * url /tasks/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/tasks/:id', 'authenticate', function ($task_id) {
	global $user_id;
	$response = array();
	$db = new DbHandler();

	// fetch task
	$result = $db->getTask($task_id, $user_id);

	if ($result != NULL) {
		$response["error"] = false;
		$response["id"] = $result["id"];
		$response["task"] = $result["task"];
		$response["status"] = $result["status"];
		$response["createdAt"] = $result["created_at"];
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "The requested resource doesn't exists";
		echoRespnse(404, $response);
	}
});

/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/tasks/:id', 'authenticate', function ($task_id) use ($app) {
	// check for required params
	verifyRequiredParams(array('task', 'status'));

	global $user_id;
	$task = $app->request->put('task');
	$status = $app->request->put('status');

	$db = new DbHandler();
	$response = array();

	// updating task
	$result = $db->updateTask($user_id, $task_id, $task, $status);
	if ($result) {
		// task updated successfully
		$response["error"] = false;
		$response["message"] = "Task updated successfully";
	} else {
		// task failed to update
		$response["error"] = true;
		$response["message"] = "Task failed to update. Please try again!";
	}
	echoRespnse(200, $response);
});

/**
 * Deleting task. Users can delete only their tasks
 * method DELETE
 * url /tasks
 */

/**
 *用于浏览器直接访问
 */
$app->get('/', function () use ($app) {
	echoRespnse(201, 'helloword');
});

$app->get('/order2', function () use ($app) {

	$response = array();
	$awbno = $app->request->get('awbno');
	HttpRequest('http://api.winlinklogistics.com/WinlinkErp/index.php/Api/Tracker/index','post','json',array('data'=>$awbno),array('User-Auth:winlinkStore1101'));
	$sql = 'CALL showLogs(?);';
	$dsn = 'mysql:dbname=wl_ffl;host=rm-bp14i6m14913604m5o.mysql.rds.aliyuncs.com';
	$user = 'winlink';
	$pwd = 'Xuzhonghai00';
	$dbh = new \PDO($dsn, $user, $pwd, array(
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';",
	));

	if (!$awbno) {
		$requestRef = $app->request->get('17no');
		if ($requestRef) {
			$sqlCheck = 'SELECT awbno FROM wl_ffl.crm_awb WHERE ShipperRef = \'' . $requestRef . '\';';
			$stmt = $dbh->query($sqlCheck);
			$rows = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!$rows || count($rows) <= 0) {
				$response["error"] = 1;
				$response["message"] = '获取轨迹数据失败';
				$response["awbno"] = $awbno ? $awbno : $requestRef;
				$response["DeliveredTo"] = '';
				$response["Destination"] = '';
				$response["Weight"] = '0.00';
				$response['data'] = array();
				echoRespnse(200, $response);
				exit();
			}
			$awbno = $rows['awbno'];
		}
	}
	try {
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array($awbno));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows)) {
			$data = array();
			$loop = 0;
			$datas = array();
			foreach ($rows as $row) {
				$data[] = array(
					"TransDate" => $row['trans_date'],
					"TransTime" => $row['trans_time'],
					"Location" => $row['location'],
					"status" => trim($row['status']),
					"DeliveredTo" => in_array(trim($row['status']), array('POD', 'RTO', 'DS')) ? $row['to'] : '',
					"Remarks" => $row['remarks'],
					"Weight" => $row['weight'],
					"Destination" => $row['city'],
				);
				$loop++;

				if (in_array(trim($row['status']), array('POD', 'RTO', 'DS'))) {
					$datas['country_name'] = $row['country_name'];
					$datas['weight'] = $row['weight'];
					$datas['en_name'] = $row['to'];
				} else {
					$datas['country_name'] = '';
					$datas['weight'] = $row['weight'];
					$datas['en_name'] = '';
				}
			}
			$response["error"] = 0;
			$response["message"] = '获取轨迹数据成功';
			$response["awbno"] = $awbno;
			$response["DeliveredTo"] = $datas['en_name'];
			$response["Destination"] = $datas['country_name'];
			$response["Weight"] = $datas['weight'];
			$sql = 'SELECT * FROM crm_awb WHERE awbno = ?;';
			$stmt = $dbh->prepare($sql);
			$stmt->execute(array($awbno));
			$info = $stmt->fetch();
			if ($info['ConsigneeCountry'] == 'SA') {
				$response["ph"] = '966 11 4633999';
				$response['RefCode'] = $info['RefCode'];
				$response["tracker"] = 'http://www.smsaexpress.com';
			} 
				elseif ($info['ConsigneeCountry'] == 'OM') {
				$response["ph"] = '968 24810462';
				$response['RefCode'] = '';
				$response["tracker"] = 'http://www.firstflightme.com';
			}
			elseif ($info['ConsigneeCountry'] == 'BH') {
				$response["ph"] = '17000323';
				$response['RefCode'] = '';
				$response["tracker"] = 'http://www.firstflightme.com';
			}
			elseif ($info['ConsigneeCountry'] == 'KWT') {
				$response["ph"] = '965 1881881';
				$response['RefCode'] = $info['RefCode'];
				$response["tracker"] = 'http://www.postaplus.com';
			}
			elseif ($info['ConsigneeCountry'] == 'QA') {
				$response["ph"] = '974 44702300';
				$response['RefCode'] = '';
				$response["tracker"] = 'http://www.firstflightme.com';
			}
			elseif ($info['ConsigneeCountry'] == 'EG') {
				$response["ph"] = '16231';
				$response['RefCode'] = '';
				$response["tracker"] = 'http://www.firstflightme.com';
			}
				else {
				$response["ph"] = '971 600 54 54 56';
				$response['RefCode'] = '';
				$response["tracker"] = 'http://www.firstflightme.com';
			}
			$response['data'] = $data;
		} else {
			$response["error"] = 1;
			$response["message"] = '获取轨迹数据失败';
			$response["awbno"] = $awbno;
			$response["DeliveredTo"] = '';
			$response["Destination"] = '';
			$response["Weight"] = '0.00';
			$response['data'] = array();
		}
	} catch (PDOException $e) {
		$response["error"] = 1;
		$response["message"] = $e->getMessage();
		$response["awbno"] = $awbno;
		$response["DeliveredTo"] = '';
		$response["Destination"] = '';
		$response["Weight"] = '0.00';
		$response['data'] = array();
	}
	echoRespnse(200, $response);
});

$app->get('/order', function () use ($app) {

	$response = array();
	$awbno = $app->request->get('awbno');
/*          $conn1 =new mysqli("localhost","root","root");
var_dump($conn1);*/

	try
	{
		$con = odbc_connect('DRIVER={SQL Server};SERVER=47.90.46.77;DATABASE=Integra', 'query', 'p@ssw0rd');

	} catch (Exception $e) {

		$response["error"] = 1;
		$response["message"] = '连接数据库失败请联系管理员';
		echoRespnse(400, $response);
		$app->stop();
	}

	if (!$con) {

		$response["error"] = 1;
		$response["message"] = '连接数据库失败';
		echoRespnse(400, $response);
		$app->stop();
		die(print_r(sqlsrv_errors(), true));
	}
	$result = odbc_do($con, "select *  from statuslog('$awbno')");
	if (!odbc_fetch_row($result)) {
		$response["error"] = 1;
		$response["message"] = '无订单信息';
		echoRespnse(400, $response);
		$app->stop();
	}
	do {
		$tmp = array();
		//$tmp['awbno'] = odbc_result($result1, "awbno");
		$tmp['TransDate'] = date("Y-m-d", strtotime(odbc_result($result, "TransDate")));
		$tmp['TransTime'] = date("H:i:s", strtotime(odbc_result($result, "TransTime")));
		$tmp['Location'] = odbc_result($result, "Location");
		$tmp['status'] = odbc_result($result, "status");
		$tmp['DeliveredTo'] = odbc_result($result, "DeliveredTo");
		$tmp['Remarks'] = odbc_result($result, "Remarks");
		$tmp['Weight'] = odbc_result($result, "Weight");
		$tmp['Destination'] = odbc_result($result, "Destination");
		$reData[] = $tmp;
	} while (odbc_fetch_row($result));

	odbc_close($con);
	$response["error"] = 0;
	$response["message"] = '查询成功';
	$response["awbno"] = $awbno;
	$response["DeliveredTo"] = $tmp['DeliveredTo'];
	$response["Destination"] = $tmp['Destination'];
	$response["Weight"] = $tmp['Weight'];
	$response['data'] = $reData;

	//$result = $db->getQuery();

	echoRespnse(200, $response);
});
	/*
	 +----------------------------------------------------------
	 * crm通用http请求，用来获取api数据
	 +----------------------------------------------------------
	 * @param string $url ;
	 +----------------------------------------------------------
	 * @param string $type ;
	 +----------------------------------------------------------
	 * @param arr $data http请求的参数;
	 +----------------------------------------------------------
	 * @param arr $header array('type'=>可以选择默认的两种类型，如果选择了1，需要传递另一个参数确定User-Auth) ;
	 +----------------------------------------------------------
	 * @return arr  $data
	 +----------------------------------------------------------
	*/
	function HttpRequest($url,$dataType='json',$type='get',$data=array(),$header=array()){
			/*1.设置API链接HTTp请求参数*/
			$data=json_encode($data);
			$headerDefault = array(
				'Content-Type:application/json',
				'Content-Lenth:'.strlen($data),
				'Expect:',
			);
			$header = array_merge($headerDefault,$header);
			/*2.设置http协议参数,并获取数据*/
			$ch = curl_init();
			curl_setopt_array($ch,array(
				CURLOPT_URL=>$url,
				CURLOPT_HEADER=>false,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_CUSTOMREQUEST=>strtoupper($type),
				CURLOPT_POSTFIELDS=>$data,
				CURLOPT_HTTPHEADER=>$header,
				CURLOPT_SSL_VERIFYHOST=>false,
				CURLOPT_SSL_VERIFYPEER=>false,
				CURLOPT_FOLLOWLOCATION=>true,
				CURLOPT_HTTP_VERSION=>CURL_HTTP_VERSION_1_1
			));
			try{
				$data = curl_exec($ch);
			}catch(Exception $e){
				$msg = $e->getError();
			}	
			/*3.返回数据*/
			if(isset($msg)){
				return ReturnData(false,$msg,'','');
			}elseif($dataType == 'json'){
				return ReturnData(true,'',json_decode($data,true));	
			}elseif($dataType == 'html'){
				return ReturnData(true,'',$data);
			}elseif($dataType == 'xml'){
				$xmlstring = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA); 			 
				$val = json_decode(json_encode($xmlstring),true); 
				return ReturnData(true,'',$val);
			}
	}
	/*
	 +----------------------------------------------------------
	 * 返回数据标准格式函数
	 +----------------------------------------------------------
	 * @param  ;
	 +----------------------------------------------------------
	 * @return json{status:bool,msg:string,data:mix,code:int}
	 +----------------------------------------------------------
	*/
	function ReturnData($status,$msg,$data=0,$code=10000){
		$res['status'] = $status;
		$res['msg'] = $msg;
		$res['data'] = $data;
		$res['code'] = $code;
		return $res;
	}
$app->run();
?>