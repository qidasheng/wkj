<?php

class Data_Hello_World
{

    public function get_data()
    {
        $dbConfig = Registry::get('app')->getConf('db');
        $db = new \Sys\Db\MysqlDb($dbConfig);
        $data = $db->connect('test', 'hello')->findAll('select * from test where ? limit 2', array(1));
        return $data;
    }
}

