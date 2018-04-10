<?php
/*
 * pdo 数据库类
 * 支持多数据库同时连接
 * 防sql注入
 * */
namespace core\library;
class pdo implements \core\library\db{
    private $_db;//数据库链接对象
    private static $_instance;//单例返回的数据库链接对象
    private static $_dbMap=[];//数据库链接对象库
    private $_opt = ['LIMIT','ORDER','LIKE','GROUP','MATCH'];//where条件选项

    //无法使用 new 来创建该对象
    private function __construct($key='default'){
        try{
            $c = config('default');
            $c = $c[$c['config']]['pdo'][$key];
            $this->_db = new \PDO($c['dns'], $c['user'], $c['pwd']);
        }catch(\PDOException $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
    }/*__construct() end*/

    /*
     * 防止克隆
     * return void
     * */
    private function __clone(){}/*__clone() end*/

    /*
     * 单例模式创建数据库对象
     * string $key pdo数据配置下标
     * return object
     * */
    public static function getInstance($key='default'){
        if( isset(self::$_dbMap[$key])===false ){
            self::$_dbMap[$key] = $key;
            self::$_instance = new self($key);
        }
        return self::$_instance;
    }/*getInstance() end*/

    /*
     * 校验表名是否存在
     * string $table 表名
     * return void
     * */
    public function checkTable($table=NULL){
        if( is_string($table)===false||empty($table) ){
            $message = '[PDO]未定义表名';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }
    }/*checkTable() end*/

    /*
     * 查询数据操作
     * string $table 表名
     * string $columns 列 'id,name,age'
     * array $options 条件选项 ['id'=>['>',10]]
     * return array
     * */
    public function select($table='', $columns='', $options=[]){
        $this->checkTable($table);
        if( is_string($columns)===false||empty($columns) ){
            $message = '[PDO]未定义查询列';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }
        $sql = "SELECT {$columns} FROM {$table}";
        $where = array_diff_key($options, array_flip($this->_opt));
        $param = [];
        $i = 1;
        if( is_array($where)&&!empty($where) ){
            $sql .= ' WHERE ';
            foreach($where as $key=>$val){
                if( is_array($val) ){
                    $sql .= "{$key}{$val[0]}? AND ";
                    $param[$i] = $val[1];
                }else{
                    $sql .= "{$key}=? AND ";
                    $param[$i] = $val;
                }
                $i++;
            }
            $sql = rtrim($sql, ' AND ');
        }

        if( isset($options['ORDER']) ){
            //$options['ORDER'] = 'ORDER BY id DESC';
            $sql .= ' ORDER BY '.$options['ORDER'];
        }
        if( isset($options['LIMIT']) ){
            //$options['LIMIT'] = ' LIMIT 2';
            $sql .= ' LIMIT '.$options['LIMIT'];
        }
        //p($sql);

        try{
            $sth = $this->_db->prepare($sql);
            foreach($param as $key=>$val){
                $sth->bindValue($key, $val);
            }
            $sth->execute();
            $res = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }catch(\PDOException $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
        //p($res);
        return $res;
    }/*select() end*/

    /*
     * 写入数据操作
     * string $table 表名
     * array $data 写入数据 [col1=>val1, col2=>val2]
     * return int
     * */
    public function insert($table='', $data=[]){
        $this->checkTable($table);
        if( is_array($data)===false||empty($data) ){
            $message = '[PDO]未定义写入数据';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        $sql = 'INSERT INTO '.$table;
        $cols = '';
        $vals = '';
        $param = [];
        $i = 1;
        foreach($data as $key=>$val){
            $cols .= $key.',';
            $vals .= "?,";
            $param[$i] = $val;
            $i++;
        }
        $cols = rtrim($cols, ',');
        $vals = rtrim($vals, ',');
        $sql .= "({$cols}) VALUES({$vals})";
        //p($sql);

        try{
            $sth = $this->_db->prepare($sql);
            foreach($param as $key=>$val){
                $sth->bindValue($key, $val);
            }
            $sth->execute();
            $count = $sth->rowCount();
            //p($count);
        }catch(\PDOException $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
        return $count;
    }/*insert() end*/

    /*
     * 更新操作
     * string $table 表名
     * array $data 更新数据 [col1=>val1, col2=>val2]
     * array $options 条件选项 ['id'=>['>',10]]
     * return int
     * */
    public function update($table='', $data=[], $options=[]){
        $this->checkTable($table);
        if( is_array($data)===false||empty($data) ){
            $message = '[PDO]未定义更新数据';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        //sql语句拼接
        $sql = "UPDATE {$table} SET ";
        $set = '';
        $param = [];
        $i = 1;
        foreach($data as $key=>$val){
            $set .= $key.'=?,';
            $param[$i] = $val;
            $i++;
        }
        $sql .= rtrim($set, ',');
        $where = array_diff_key($options, array_flip($this->_opt));

        if( is_array($where)&&!empty($where) ){
            $sql .= ' WHERE ';
            foreach($where as $key=>$val){
                if( is_array($val) ){
                    $sql .= "{$key}$val[0]? AND ";
                    $param[$i] = $val[1];
                }else{
                    $sql .= "{$key}=? AND ";
                    $param[$i] = $val;
                }
                $i++;
            }
            $sql = rtrim($sql, ' AND ');
        }
        if( isset($options['LIMIT']) ){
            $sql .= ' LIMIT '.$options['LIMIT'];
        }
        //p($sql);

        try{
            $sth = $this->_db->prepare($sql);
            foreach($param as $key=>$val){
                $sth->bindValue($key, $val);
            }
            $sth->execute();
            $count = $sth->rowCount();
            //p($count);
        }catch(\PDOException $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
        return $count;
    }/*update() end*/

    /*
     * 删除数据操作
     * string $table 表名
     * array $options 条件选项
     * return int
     * */
    public function delete($table='', $options=[]){
        $this->checkTable($table);
        $sql = 'DELETE FROM '.$table;

        $where = array_diff_key($options, array_flip($this->_opt));
        $param = [];
        $i = 1;
        if( is_array($where)&&!empty($where) ){
            $str = ' WHERE ';
            foreach($where as $key=>$val){
                if( is_array($val) ){
                    $str .= "{$key}$val[0]? AND ";
                    $param[$i] = $val[1];
                }else{
                    $str .= "{$key}=? AND ";
                    $param[$i] = $val;
                }
                $i++;
            }
            $str = rtrim($str, ' AND ');
            $sql .= $str;
        }
        if( isset($options['LIMIT']) ){
            $sql .= ' LIMIT '.$options['LIMIT'];
        }
        //p($sql);

        try{
            $sth = $this->_db->prepare($sql);
            foreach($param as $key=>$val){
                $sth->bindValue($key, $val);
            }
            $sth->execute();
            $count = $sth->rowCount();
            //p($count);
        }catch(\PDOexception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
        return $count;
    }/*delete() end*/

    /*
     * 统计行的数量
     * string $table 表名
     * string $pattern sql聚合函数查询[avg, count, max, min, sum]
     * string $columns 列
     * array $options 条件选项
     * return int
     * */
    public function schemaQuery($table='', $pattern='', $columns='', $options=[]){
        $this->checkTable($table);

        $pattern = strtoupper($pattern);
        $patternArr = ['AVG', 'COUNT', 'MAX', 'MIN', 'SUM'];
        if( in_array($pattern, $patternArr)===false ){
            $message = '[PDO]未定义聚合函数查询方法';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        if( is_string($columns)===false||empty($columns) ){
            $message = '[PDO]未定义查询列';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        $sql = "SELECT {$pattern}({$columns}) FROM {$table}";

        $where = array_diff_key($options, array_flip($this->_opt));
        $param = [];
        $i = 1;
        if( is_array($where)&&!empty($where) ){
            $str = ' WHERE ';
            foreach($where as $key=>$val){
                if( is_array($val) ){
                    $str .= "{$key}{$val[0]}? AND ";
                    $param[$i] = $val[1];
                }else{
                    $str .= "{$key}=? AND ";
                    $param[$i] = $val;
                }
                $i++;
            }
            $sql .= rtrim($str, ' AND ');
        }
        //p($sql);

        $sth = $this->_db->prepare($sql);
        foreach($param as $key=>$val){
            $sth->bindValue($key, $val);
        }
        $sth->execute();
        $res = $sth->fetch(\PDO::FETCH_NUM);
        //p($res[0]);
        return $res[0];
    }/*count() end*/

    /*
     * 设置属性
     * int $attribute
     * mixed $value
     * return Boolean
     * */
    public function setAttribute($attribute=NULL, $value=NULL){
        try{
            $attr = $this->_db->setAttribute($attribute, $value);
        }catch(\PDOException $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
        return $attr;
    }/*setAttribute() end*/
}//pdo{} end
