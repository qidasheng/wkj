<?php

class Data_Product extends \Sys\Db\MysqlDb
{

    protected $dbAlias = 'test';
    protected $tableName = 'product';

    public function getProductList($uid)
    {
        return $this->master()->findAll('select * from ' . $this->tableName . ' where uid = ? limit 3', array($uid));
    }

} 
