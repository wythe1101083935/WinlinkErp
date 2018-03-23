<?php
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('Require PHP > 5.3.0 !');
header("Content-type:text/html;charset=utf-8");
define('APP_PATH',dirname(__FILE__).'/Application/');
define('APP_NAME','Application');
define('APP_ROOT', __DIR__);
define('APP_DEBUG',true);
//define('COM_URL','http://'.$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]);
require dirname(__FILE__).'./ThinkPHP/ThinkPHP.php';