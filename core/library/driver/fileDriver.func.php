<?php
/*
 * 日志存储方式:文件
 * */

namespace core\library\driver;

class fileDriver{
    /*
     * 以文件形式写入日志
     * string $message 日志内容
     * string $name 文件冲命名
     * string $path 日志存储目录
     * return void
     * */
    public static function insertLog($message='', $name='', $path=''){
        //创建日志存储路径
        $path = $path.date('Ym');
        if( is_dir($path)===false ){
            mkdir($path, 0777, true);
        }
        //文件名
        if( empty($name)||is_string($name)===false ){
            $name = date('d');
        }
        $filename = $path.'/'.$name.'.txt';

        //日志内容
        if( empty($message) ){
            echo '日志内容不能为空';
            die;
        }

        //将日志写入文件里
        $json = [
            'time'=>date('Y-m-d H:i:s'),
            'content'=>$message
        ];
        $data = str_replace('\\', '/', json_encode($json, JSON_UNESCAPED_UNICODE));
        $data = str_replace('//', '/', $data);
        file_put_contents($filename, $data.PHP_EOL.PHP_EOL, FILE_APPEND);
    }/*insertLog() end*/
}//FileDriver{} end