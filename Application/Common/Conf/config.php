<?php
return array(
	/*1.系统设置*/
	//'DEFAULT_M_LAYER'       =>  'Model', // 默认的模型层名称
	//'DEFAULT_C_LAYER'       =>  'Controller', // 默认的控制器层名称
	//'DEFAULT_V_LAYER'       =>  'View', // 默认的视图层名称
	//'DEFAULT_LANG'          =>  'zh-cn', // 默认语言
	//'DEFAULT_THEME'         =>  '', // 默认模板主题名称
	//'DEFAULT_MODULE'        =>  'Home',  // 默认模块
	//'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
	//'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	//'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码
	//'DEFAULT_TIMEZONE'      =>  'PRC',  // 默认时区
	//'DEFAULT_AJAX_RETURN'   =>  'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
	//'DEFAULT_JSONP_HANDLER' =>  'jsonpReturn', // 默认JSONP格式返回的处理方法
	//'DEFAULT_FILTER'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数...
  /*数据库配置*/
  'DB_TYPE'               =>  'mysql',  
  'DB_HOST'               =>  'rm-bp14i6m14913604m5o.mysql.rds.aliyuncs.com',//正式服务器
  //'DB_HOST'               =>  'localhost',//测试服务器
  'DB_NAME'               =>  'wl_ffl',
  'DB_USER'               =>  'winlink',   
  'DB_PWD'                =>  'Xuzhonghai00',
  //'DB_PWD'                =>  '123456',
  'DB_PORT'               =>  '3306', 
  'DB_PREFIX'             =>  'crm_', 
  'DB_CHARSET'            =>  'utf8',
  'DB_CONFIG_STORE'       =>array(
    'DB_TYPE'     => 'mysql',                 
    'DB_HOST'     => 'xdm333620302.my3w.com',            
    'DB_NAME'     => 'xdm333620302_db',                  
    'DB_USER'     => 'xdm333620302',                  
    'DB_PWD'      => 'Xuzhonghai00',                  
    'DB_PORT'     => '3306',                  
    'DB_PREFIX'   => 'tp_', 
    'DB_CHARSET'  =>  'utf8'
  ),
  'TMPL_PARSE_STRING'=>array(
    '__HOME__'=>__ROOT__.'/index.php/Home', 
    '__ORDER__'=>__ROOT__.'/index.php/Order',
    '__SYSTEM__'=>__ROOT__.'/index.php/System', 
    '__STORE__'=>__ROOT__.'/index.php/Store',
    '__TRACKER__'=>__ROOT__.'/index.php/Tracker',
    '__USER__'=>__ROOT__.'/index.php/User',
    '__FINANCIAL__'=>__ROOT__.'/index.php/Financial',
    '__DISPATCHTOFOREIGN__'=>__ROOT__.'/index.php/DispatchToForeign',
    '__ORDERFOLLOWUP__'=>__ROOT__.'/index.php/OrderFollowUp',
    '__SWEEPTOOL__'=>__ROOT__.'/index.php/SweepTool',
    ),
	/*3.权限验证*/
	'RBAC_DB_DSN'  => array(
		'DB_TYPE'               =>  'mysql',  
		'DB_HOST'               =>  'rm-bp14i6m14913604m5o.mysql.rds.aliyuncs.com',
		'DB_NAME'               =>  'wl_ffl',
		'DB_USER'               =>  'winlink',   
		'DB_PWD'                =>  'Xuzhonghai00',
		'DB_PORT'               =>  '3306', 
		'DB_PREFIX'             =>  'crm_', 
		'DB_CHARSET'            =>  'utf8',
	),//RBAC表所在数据库
    //'RBAC_SUPERADMIN'=>'admin',  
    //'ADMIN_AUTH_KEY' =>'crm_admin', //超级管理员识别  
    //'USER_AUTH_ON' =>true,  //是否开启权限验证  
    //'USER_AUTH_TYPE' =>1,   //验证类型(1:登录时验证2:时时验证)  
    'USER_AUTH_KEY' =>'uid', //用户验证识别号  
    //'NOT_AUTH_ACTION' =>'index', // 无需验证的动作方法  
    //'NOT_AUTH_MODULE' =>'Index', //无需验证的控制器  
    'RBAC_ROLE_TABLE' =>'crm_role',//角色表名称  
    'RBAC_USER_TABLE' => 'crm_role_user',//用户与角色的中间表  
    'RBAC_ACCESS_TABLE' =>'crm_access',//权限表  
    'RBAC_NODE_TABLE' =>'crm_node',//节点表  
    //'URL_HTML_SUFFIX' =>'',
    /**/
 		'TAGLIB_BUILD_IN'    =>    'cx,cate',
        'FROM_COUNTRY'       =>    array('CZX' => '中国', 'DXB' => '迪拜'),
        /*渠道代码*/
       'TRANSPORT_MODE'     => array(
            'CZX-AE1' => '5000',
            'CZX-AE2' => '5001',
            'DXB-AE1' => '6000',
            'DXB-AE2' => '6001',
            'CZX-SA' => '5002',
            'DXB-SA' => '6002',
            'CZX-OM' => '5003',
            'DXB-OM' => '6003',
            'CZX-BH' => '5004',
            'DXB-BH' => '6004',
            'CZX-QA' => '5005',
            'DXB-QA' => '6005',
            'CZX-QA' => '5006',
            'DXB-QA' => '6006',
            'CZX-EG' => '5007',
            'DXB-EG' => '6007',
            'CZX-IR' => '5008',
            'DXB-IR' => '6008',
        ) 
);