<?php
/*
 * mongodb 数据库类
 * */
namespace core\library;

class mongodb implements \core\library\db{
    public $mongo;//实例化mongodb数据库对象
    private static $_dbMap=[];//数据库链接对象库
    private static $_instance;//单例返回的数据库链接对象
    private $_bulk;//new \MongoDB\Driver\BulkWrite() 对象
    private $_writeConcern;//new MongoDB\Driver\WriteConcern() 对象

    /*
     * 无法使用 new 来实例化对象
     * */
    private function __construct($key='default'){
        $c = config('default');
        $c = $c[$c['config']]['mongo'][$key];
        //p($c);
        try{
            $this->mongo = new \MongoDB\Driver\Manager($c);
        }catch(\MongoDB\Driver\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }
    }/*__construct() end*/

    /*
     * 无法克隆对象
     * */
    private function __clone(){}

    /*
     * 生成 \MongoDB\Driver\BulkWrite() 对象
     * return object
     * */
    final private function createBulkWriteObj(){
        if( isset($this->_bulk)===FALSE ){
            $this->_bulk = new \MongoDB\Driver\BulkWrite();
        }
        return $this->_bulk;
    }/*createBulkWriteObj() end*/

    /*
     * 生成 \MongoDB\Driver\WriteConcern() 对象
     * return object
     * */
    final private function createWriteConcernObj(){
        if( isset($this->_writeConcern)===FALSE ){
            $this->_writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);
        }
        return $this->_writeConcern;
    }/*createWriteConcernObj() end*/

    /*
     * 单例模式创建数据库对象
     * string $key mongo数据配置下标
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
     * 查询集合数据
     * string $table 表名 dbName.collection
     * array $filter 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * array $options 选项
     * $options = [
     *  'skip'=>0,//跳过查询数量
     *  'limit'=>10,//限制查询条数
     *  'sort'=>['_id'=>-1],//排序
     *  'projection'=>['col'=>0],//设置查询结果不输出的column内容
     * ]
     * return array
     * */
    public function select($table='', $filter=[], $options=[]){
        try{
            $query = new \MongoDB\Driver\Query($filter, $options);
            $res = $this->mongo->executeQuery($table, $query);
            $res = iterator_to_array($res);
            $res = json_decode(json_encode($res), true);//将stdClass Object转array
            //p($res);
        }catch(\MongoDB\Driver\Exception $e){
                \core\library\log::write($e->getMessage());
                \core\library\error::show(404, $e->getMessage());
                die;
        }
        return $res;
    }/*select() end*/

    /*
     * 写入数据到集合
     * string $table 表名 dbName.collection
     * array $data 写入集合数据 [key1=>val1, key2=>val2]
     * return int
     * */
    public function insert($table='', $data=[]){
        try{
            $bulk = $this->createBulkWriteObj();
            $bulk->insert($data);
            $writeConcern = $this->createWriteConcernObj();
            $writeResult = $this->mongo->executeBulkWrite($table, $bulk, $writeConcern);
        }catch(\MongoDB\Driver\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }

        return $writeResult->getInsertedCount();
    }/*insert() end*/

    /*
     * 更新集合数据
     * string $table 表名 dbName.collection
     * array $data 更新数据 [set1=>val1, set2=>val2]
     * array $where 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * array $options 选项
     * $options['multi'=>FALSE], FALSE:更新匹配文档;TRUE:更新文档不存在则创建文档
     * $options['upsert'=>FALSE]:如果过滤器不匹配现有文档,则插入一条文档(FALSE不插入)
     * return int
     * */
    public function update($table='', $data=[], $where=[], $options=['multi'=>FALSE, 'upsert'=>FALSE]){
        try{
            $bulk = $this->createBulkWriteObj();
            $bulk->update($where, ['$set'=>$data], $options);
            $writeConcern = $this->createWriteConcernObj();
            $bulkResult = $this->mongo->executeBulkWrite($table, $bulk, $writeConcern);
        }catch(\MongoDB\Driver\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show(404, $e->getMessage());
            die;
        }

        return $bulkResult->getUpsertedCount();
    }/*update() end*/

    /*
     * 删除集合数据
     * string $table 表名 DBName.collection
     * array $where 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * array $options 选项 FALSE:删除所有匹配的文档,TRUE:删除第一个匹配的文档
     * return int
     * */
    public function delete($table='', $where=[], $options=['limit'=>FALSE]){
        try{
            $bulk = $this->createBulkWriteObj();
            $writeConcern = $this->createWriteConcernObj();
            $bulk->delete($where, $options);
            $bulkResult = $this->mongo->executeBulkWrite($table, $bulk, $writeConcern);
        }catch(\MongoDB\Driver\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show($e->getMessage());
            die;
        }

        return $bulkResult->getDeletedCount();
    }/*delete() end*/

    /*
     * 查询集合记录总和
     * string $table 表名 DBName.collection
     * array $filter 条件 [key1=>val1, key2=>['$gt'=>val2]]
     * return int
     * */
    public function count($table='', $filter=[]){
        $name = explode('.',$table);
        try{
            $command = new \MongoDB\Driver\Command([
                'count'=>$name[1],//collectionName
                'query'=>$filter //查询条件
            ]);
            $cursor = $this->mongo->executeCommand($name[0], $command)->toArray();
            //p($cursor);
        }catch(\MongoDB\Driver\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show($e->getMessage());
            die;
        }

        return $cursor[0]->n;
    }/*count() end*/
    
    /*
    * 原子操作-修改并返回修改后的结果, 没有就写入.
    */
    public function findAndModify($table='', $filter = [], $update = []){
        $name = explode('.', $table);
        try{            
            $command = new \MongoDB\Driver\Command([                
                'findAndModify'=> $name[1], //collectionName
                'query' => $filter, //查询条件
                'update'=> $update,
                'new' => true,
                'upsert'=> true
            ]);
            $response = $this->mongo->executeCommand($name[0], $command)->toArray();
        }catch(\MongoDB\Driver\Exception $e){
            \core\library\log::write($e->getMessage());
            \core\library\error::show($e->getMessage());
            die;
        }
        if ( count($response) ) {
            return $response;
        } else {
            return $response[0];
        }
    }/*findAndModify() end*/
}//mongodb{} end
