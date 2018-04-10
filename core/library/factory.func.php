<?php
/*
 * 工厂类
 * */
namespace core\library;

abstract class factory{
    /*
     * 生成数据库连接工厂方法
     * string $type 连接数据库类型
     * string $key 数据库配置项
     * return object
     * */
    public function createDB($type='', $key='default'){
        switch($type){
            case 'pdo':
                return \core\library\pdo::getInstance($key);
                break;
            case 'mongo':
                return \core\library\mongo::getInstance($key);
                break;
            case 'mongodb':
                return \core\library\mongodb::getInstance($key);
                break;
            default:
                $message = '不存在该数据库类型!';
                \core\library\log::write($message);
                \core\library\error::show(404, $message);
                die;
        }
    }//createDB() end
}//factory{} end