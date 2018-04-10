<?php
/*
 * 框架默认系统配置文件
 * */
return [
    'config'=>'office',//默认配置项
    //可以设置多个配置大类,方便多配置切换
    'office'=>[
        //框架默认常量
        'constant'=>[
            'domain'=>true,//是否有二级目录
        ],
        //log配置
        'log'=>[
            'driver'=>'file',//日志驱动方式,首字母要大写,其他小写
            'path'=>STATIC_PATH.'/log/',//日志目录
        ],
        //路由配置
        'route'=>[
            'model'=>'home',//模型
            'ctrl'=>'index',//控制器
            'action'=>'index' //方法
        ],
        //pdo数据库配置
        'pdo'=>[
            'default'=>[
                'user'=>'root',
                'pwd'=>'root',
                'dns'=>'mysql:host=localhost;dbname=test'
            ],
        ],
        //mongo数据库配置
        'mongo'=>[
            'default'=>'mongodb://127.0.0.1:27017',
        ],
    ],
    'home'=>[],
];