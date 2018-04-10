<?php
/*
 * 框架入口文件
 * */

//项目根目录
define('BASE_PATH', str_replace('\\', '/', __DIR__));
define('APP_PATH', BASE_PATH.'/app');
define('CORE_PATH', BASE_PATH.'/core');
define('STATIC_PATH', BASE_PATH.'/static');
define('VENDOR_PATH', BASE_PATH.'/vendor');
define('DEBUG', true);

if( DEBUG ){
    ini_set('display_errors', 'On');
}else{
    ini_set('display_errors', 'Off');
}

require_once CORE_PATH.'/common/common.func.php';//加载框架公共函数
require_once CORE_PATH.'/core.func.php';//加载框架核心类

spl_autoload_register('\core\core::autoload');
\core\core::run();