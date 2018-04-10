<?php
/*
 * 日志存储类
 * */

namespace core\library;

class log{
    /*
     * 加载默认配置
     * */
    public static function init(){
        $c = config('default');
        return $c[$c['config']]['log'];
    }/*init() end*/

    /*
     * 写入日志
     * string $message 日志内容
     * string $name 日志重命名
     * return void
     * */
    public static function write($message='', $name=''){
        $c = self::init();
        $class = '\core\library\driver\\'.$c['driver'].'Driver';
        $class::insertLog($message, $name, $c['path']);
    }/*write() end*/
}//log{} end