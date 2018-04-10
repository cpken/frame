<?php
/*
 * 错误输出页面
 * */

namespace core\library;

class error{
    /*
     * 错误输出
     * string $file 页面
     * string|array $message 错误信息
     * */
    public static function show($file=null, $message=null)
    {
        require_once BASE_PATH.'/vendor/Twig/Autoloader.php';
        \Twig_Autoloader::register();//注册twig

        $viewFiles = BASE_PATH.'/core/error/';
        $loader = new \Twig_Loader_Filesystem($viewFiles);//传入视图文件夹
        $twig = new \Twig_Environment($loader, array(
            'cache' => BASE_PATH.'/static/twig',//前端缓存目录
            'debug' => true,// 是否开启调试,输出变量会实时更新
        ));//twig设置

        $path = BASE_PATH.'/core/error/'.$file.'.html';
        if( is_file($path) ){
            $template = $twig->loadTemplate($file.'.html');//加载模板文件
            if( is_array($message) ){
                $message = str_replace('\\', '/', json_encode($message,JSON_UNESCAPED_UNICODE));
                $message = str_replace('//', '/', $message);
            }
            $data = [
                'title'=>'错误输出',
                'content'=>$message
            ];
            $template->display($data);//变量输出
        }else{
            $message = '未找到模板文件 '.$path;
            \core\library\log::write($message);
            die($message);
        }
    }/*show() end*/
}//error{} end