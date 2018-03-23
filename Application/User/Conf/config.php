<?php
return array(
  /*2.路径配置*/
  'DB_TYPE'               =>  'mysql',  
  'DB_HOST'               =>  'rm-bp14i6m14913604m5o.mysql.rds.aliyuncs.com',
  'DB_NAME'               =>  'wl_ffl',
  'DB_USER'               =>  'winlink',   
  'DB_PWD'                =>  'Xuzhonghai00',
  'DB_PORT'               =>  '3306', 
  'DB_PREFIX'             =>  'crm_', 
  'DB_CHARSET'            =>  'utf8',
  'TMPL_PARSE_STRING'=>array(
     '__HOME__'=>__ROOT__.'/index.php/'.'Home', 
     '__ORDER__'=>__ROOT__.'/index.php/'.'Order', 
     '__SYSTEM__'=>__ROOT__.'/index.php/'.'System', 
     '__STORE__'=>__ROOT__.'/index.php/'.'Store',
     '__TRACKER__'=>__ROOT__.'/index.php/'.'Tracker',
     '__USER__'=>__ROOT__.'/index.php/'.'User',
     '__CSS__'=>__ROOT__.'/'.APP_NAME.'/'.MODULE_NAME.'/View/Public/css', //当前模块CSS
     '__JS__'=>__ROOT__.'/'.APP_NAME.'/'.MODULE_NAME.'/View/Public/js',  //当前模块JS
     '__IMG__'=>__ROOT__.'/'.APP_NAME.'/'.MODULE_NAME.'/View/Public/img', //当前模块image
  ),
);