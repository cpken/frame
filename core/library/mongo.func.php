<?php
/*
 * mongo 数据库类
 * */
namespace core\library;
class mongo implements \core\library\db{
    private static $_dbMap=[];//数据库链接对象库
    private static $_instance;//单例返回的数据库链接对象
    public $mongo;//实例化mongodb数据库对象
    private $_opt = ['ORDER', 'SKIP', 'LIMIT', 'LIKE'];

    /*
     * 无法直接 new 该对象
     * */
    private function __construct($key='default'){
        $c = config('default');
        $c = $c[$c['config']]['mongo'][$key];
        try{
            $this->mongo = new \MongoClient($c);
            if( $this->mongo->connect()===false ){
                throw new \Exception('[mongo]数据库连接失败 '.$c);
            }
        }catch(\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
    }/*__construct() end*/

    private function __clone(){}/*__clone() end*/

    public static function getInstance($key='default'){
        if( isset(self::$_dbMap[$key])===false ){
            self::$_dbMap[$key] = $key;
            self::$_instance = new self($key);
        }
        return self::$_instance;
    }/*getInstance() end*/

    /*
     * 设置 dbName.Collection
     * string $table dbName.Collection
     * return object
     * */
    public function getTableObj($table=''){
        $table = explode('.', $table);
        $bool = false;
        if( isset($table[0])===false ){
            $message = '[mongo]不存在该数据库: '.$table[0];
            $bool = true;
        }
        if( isset($table[1])===false ){
            $message = '[mongo]不存在该表: '.$table[1];
            $bool = true;
        }

        if($bool){
            \core\library\log::write($message);
            \core\library\error::show($message);
            die;
        }
        return $this->mongo->selectDB($table[0])->selectCollection($table[1]);
    }/*getTable() end*/

    /*
     * 查询数据操作
     * string $table dbName.Collection
     * array $options 条件选项
     * [key1=>val1, key2=>['$gt'=>val2], 'LIMIT'=>2, 'ORDER'=>['_id'=>-1]]
     * array $fields 返回结果的字段,0不返回,1返回
     * int $type 0:多条记录查询,1:单条记录查询
     * return array
     * */
    public function select($table='', $options=[], $fields=[], $type=0){
        $table = $this->getTableObj($table);

        $query = array_diff_key($options, array_flip($this->_opt));
        $limit = 0;
        $skip = 0;
        $sort = [];
        if( isset($options['LIMIT']) ){
            $limit = $options['LIMIT'];
        }
        if( isset($options['SKIP']) ){
            $skip = $options['SKIP'];
        }
        if( isset($options['ORDER']) ){
            $sort = $options['ORDER'];
        }

        try{
            if($type>0){
                $res = $table->findOne($query, $fields);
            }else{
                $res = $table->find($query, $fields)->limit($limit)->skip($skip)->sort($sort);
            }
            $res = iterator_to_array($res);
        }catch(\Exception $e){
            $message = '[mongo]查询数据失败, '.$e->getMessage();
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }
        //p($res);
        return $res;
    }/*select() end*/

    /*
     * 写入数据操作
     * string $table dbName.Collection
     * array $data 写入数据
     * return boolean TRUE写入成功,FALSE写入失败
     * */
    public function insert($table='', $data=[]){
        $table = $this->getTableObj($table);
        if( !is_array($data)||empty($data) ){
            $message = '[mongo]未定义写入数据';
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        try{
            $res = $table->insert($data);
        }catch(\Exception $e){
            $message = '[mongo]数据写入失败, '.$e->getMessage();
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        return is_null($res['err']);
    }/*insert() end*/

    /*
     * 更新数据操作
     * string $table dbName.Collection
     * array $data 更新数据 [key=>val]
     * array $where 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * array $options 选项
     * multiple true:更新所有匹配文档;false:更新一条匹配文档
     * return int 返回影响的记录条数
     * */
    public function update($table='', $data=[], $where=[], $options=['multiple'=>true]){
        $table = $this->getTableObj($table);

        try{
            $data = [ '$set'=>$data ];
            $res = $table->update($where, $data, $options);
        }catch(\Exception $e){
            $message = '[mongo]数据更新失败, '.$e->getMessage();
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }

        return $res['n'];
    }/*update() end*/

    /*
     * 删除数据操作
     * string $table dbName.Collection
     * array $where 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * array $options 选项
     * justOne false:更新所有匹配的数据;true:更新一条匹配数据
     * return int 返回受影响的记录条数
     * */
    public function delete($table='', $where=[], $options=['justOne'=>false]){
        $table = $this->getTableObj($table);
        try{
            $res = $table->remove($where, $options);
        }catch(\Exception $e){
            $message = '[mongo]删除数据失败, '.$e->getMessage();
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }
        return $res['n'];
    }/*delete() end*/

    /*
     * 统计集合总记录行数
     * string $table dbName.Collection
     * array $where 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * int $limit 限制条数
     * int $skip 跳过记录条数
     * return int 返回统计数量
     * */
    public function count($table='', $where=[], $limit=0, $skip=0){
        $table = $this->getTableObj($table);

        try{
            $count = $table->count($where, $limit, $skip);
        }catch(\Exception $e){
            $message = '[mongo]统计集合错误, '.$e->getMessage();
            \core\library\log::write($message);
            \core\library\error::show(404, $message);
            die;
        }
        return $count;
    }/*count() end*/

    /*
     * 关闭mongodb数据库链接
     * return void
     * */
    public function close(){
        try{
            $bool = $this->mongo->close();
            if( $bool===false ){
                throw new \Exception();
            }
        }catch(\Exception $e){
            $message = '[mongo]未建立数据库链接, ';
            \core\library\log::write($message.$e->getMessage());
            \core\library\error::show(404, $message.$e->getMessage());
            die;
        }
    }/*close() end*/
}//mongo{} end
