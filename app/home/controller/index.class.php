<?php

namespace app\home\controller;

class index extends \core\core{
    public function index(){

        /*$assign = [
            'title'=>'网站首页',
        ];
        $this->assign($assign);
        $this->display('index');*/

        $m = \core\library\mongo::getInstance();
        $m->close();
    }/*index() end*/
}//index{} end