<?php
/*
 * 框架公共函数库
 * */

/*
 * 格式化打印code
 * mixed $val
 * */
function p($val=''){
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}//p() end

/*
 * 加载系统配置文件
 * 配置目录 CORE_PATH/config/default.inc.php
 * string $fileName 配置文件名
 * */
function config($fileName=''){
    $file = CORE_PATH.'/config/'.$fileName.'.inc.php';
    if( is_file($file) ){
        $inc = include $file;
        return $inc;
    }else{
        $message = '未找到[配置文件] '.$file;
        \core\library\log::write($message);
        \core\library\error::show(404, $message);
        die;
    }
}//config() end