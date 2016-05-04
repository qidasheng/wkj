<?php namespace Sys\Db;

interface DbInterface
{

    public function init();

    public function connect($dbAlias = '', $tableName = '');

    public function db();

    public function master();

    public function slave();

    public function add($data, $ignore = false);

    public function del($where, $where_data = array());

    public function update($data, $where, $where_data = array());

    public function findOne($sql, array $data = array(), $fetch_index = false);

    public function findAll($sql, array $data = array(), $fetch_index = false);

    public function exec($sql, array $data = array());
}

