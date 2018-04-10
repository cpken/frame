<?php
/*
 * 数据库接口类
 * */
namespace core\library;

interface db{
    /*
     * 查询操作
     * */
    public function select();

    /*
     * 写入操作
     * */
    public function insert();

    /*
     * 更新操作
     * */
    public function update();

    /*
     * 删除数据操作
     * */
    public function delete();
}//db{} end