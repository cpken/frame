<?php
/*
 * 框架核心类
 * */

namespace core;

class core{
    private static $_classMap=[];
    private $_overloading=[];//被重载的数据保存于此
    public $assign=[];
    private static $_viewModel;//视图模型
    private static $_viewCtrl;//控制器模型

    /*
     * 运行框架
     * */
    final public static function run(){
        //加载系统配置默认常量
        $c = config('default');
        $c = $c[$c['config']]['constant'];
        if( is_array($c) ){
            foreach( $c as $key=>$val ){
                if( !empty($val) ){
                    define(strtoupper($key), $val);
                }
            }
        }

        //路由解析加载响应控制器方法
        $route = new \core\library\route();
        self::$_viewModel = $route->model;
        self::$_viewCtrl = $route->ctrl;

        $file = APP_PATH.'/'.$route->model.'/controller/'.$route->ctrl.'.class.php';
        if( is_file($file)===false ){
            $message = '未找到类文件'.$file;
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }
        include_once $file;
        $ctrl = '\app\\'.$route->model.'\controller\\'.$route->ctrl;
        $action = $route->action;
        $obj = new $ctrl();
        $obj->$action();
    }/*run() end*/

    /*
     * 自动加载控制器
     * string $class 类名
     * */
    final public static function autoload($class){
        if( isset(self::$_classMap[$class]) ){
            return;
        }
        $file = BASE_PATH.'/'.$class.'.func.php';
        $file = str_replace('\\', '/', $file);

        if( is_file($file) ){
            include_once $file;
            self::$_classMap[$class] = $class;
        }
    }/*autoload() end*/

    /*属性重载-start*/
    /*
     * 在给不可访问属性赋值时,__set()会被调用
     * string $name 变量名
     * mixed $value 变量值
     * return void
     * */
    public function __set($name=NULL, $value=NULL){
        $this->_overloading[$name] = $value;
    }/*__set() end*/

    /*
     * 读取不可访问属性的值时,__get()会被调用
     * string $name 变量名
     * return mixed
     * */
    public function __get($name=NULL){
        return $this->_overloading[$name];
    }/*__get() end*/

    /*
     * 当对不可访问属性调用isset()或empty()时,__isset()会被调用
     * string $name 变量名
     * return Boolean
     * */
    public function __isset($name=NULL){
        return isset($this->_overloading[$name]);
    }/*__isset() end*/

    /*
     * 当对不可访问属性调用unset()时,__unset()会被调用
     * string $name 变量名
     * return void
     * */
    public function __unset($name=NULL){
        unset($this->_overloading[$name]);
    }/*__unset() end*/
    /*属性重载-end*/

    /*方法重载-start*/
    /*
     * 在对象中调用一个不可访问方法时,__call()会被调用
     * string $name
     * array $arguments
     * return mixed
     * */
    public function __call($name=NULL, $arguments=[]){
        $message = $name.'() 为私有方法,不可直接调用';
        \core\library\log::write($message);
        \core\library\error::show(404, $message);
        die;
    }/*__call() end*/

    /*
     * 在静态上下文中调用一个不可访问方法时,__callStatic()会被调用
     * string $name
     * array $arguments
     * return mixed
     * */
    public static function __callStatic($name=NULL, $arguments=[]){
        $message = $name.'() 为静态私有方法,不可直接调用';
        \core\library\log::write($message);
        \core\library\error::show(404, $message);
        die;
    }/*__call() end*/
    /*方法重载-end*/

    /*
     * 页面赋值解析
     * string|array $key
     * string $val
     * return void
     * */
    public function assign($key=NULL, $val=NULL){
        if( is_array($key) ){
            try{
                foreach($key as $key=>$val){
                    $this->assign[$key] = $val;
                }
            }catch(\Exception $e){
                \core\library\log::write($e->getMessage());
                \core\library\error::show(404, $e->getMessage());
                die;
            }
        }else{
            try{
                if( is_string($val)===false ){
                    throw new \Exception('val类型只能为string');
                    return;
                }
                $this->assign[$key] = $val;
            }catch(\Exception $e){
                \core\library\log::write($e->getMessage());
                \core\library\error::show(404, $e->getMessage());
                die;
            }
        }
    }/*assign() end*/

    /*
     * 显示视图模板
     * string|array $file 视图文件
     * return void
     * */
    public function display($file=NULL){
        require_once VENDOR_PATH.'/Twig/Autoloader.php';
        \Twig_Autoloader::register();//注册twig

        $templateDir1 = APP_PATH.'/'.self::$_viewModel.'/view';
        $templateDir2 = APP_PATH.'/'.self::$_viewModel.'/view/'.self::$_viewCtrl;
        $loader = new \Twig_Loader_Filesystem([$templateDir1, $templateDir2]);//传入视图文件夹
        $twig = new \Twig_Environment($loader, array(
            'cache' => STATIC_PATH.'/twig',//前端缓存目录
            'debug' => DEBUG,// 是否开启调试,输出变量会实时更新
        ));//twig设置

        if( is_array($file) ){
            while( isset($file[0]) ){
                $template = $twig->loadTemplate($file[0].'.html');//加载模板文件
                $template->display($this->assign);//变量输出
                array_shift($file);
            }
        }else{
            $template = $twig->loadTemplate($file.'.html');//加载模板文件
            $template->display($this->assign);//变量输出
        }
    }/*display() end*/
}//core{} end