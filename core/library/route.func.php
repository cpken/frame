<?php
/*
 * 框架路由器
 * */
namespace core\library;

class route{
    public $model;
    public $ctrl;
    public $action;

    public function __construct(){
        //$_SERVER['REQUEST_URI'] = '/frame/home/index/index';//REQUEST_URI
        if( isset($_SERVER['REQUEST_URI'])&&$_SERVER['REQUEST_URI']!='/' ){
            $path = $_SERVER['REQUEST_URI'];
            $url = explode('/', trim($path, '/'));
            if(DOMAIN){
                array_shift($url);
            }
            $this->analysis($url);
        }else{
            $c = config('default');
            $c = $c[$c['config']]['route'];

            $this->model = $c['model'];
            $this->ctrl = $c['ctrl'];
            $this->action = $c['action'];
        }
    }/*__construct() end*/

    /*
     * 解析路由器
     * array $url
     * */
    public function analysis($url=[]){
        if( $url[0]=='index.php' ){
            array_shift($url);
        }
        $c = config('default');
        $c = $c[$c['config']]['route'];

        if( isset($url[0]) ){
            $this->model = $url[0];
            array_shift($url);
        }else{
            $this->model = $c['model'];
        }
        if( isset($url[0]) ){
            $this->ctrl = $url[0];
            array_shift($url);
        }else{
            $this->ctrl = $c['ctrl'];
        }
        if( isset($url[0]) ){
            $this->action = $url[0];
            array_shift($url);
        }else{
            $this->action = $c['action'];
        }
        while( isset($url[0]) ){
            if( isset($url[1]) ){
                $_GET[$url[0]] = $url[1];
                array_shift($url);
            }
            array_shift($url);
        }
        //p($_GET);
    }/*analysis() end*/
}//route{} end
