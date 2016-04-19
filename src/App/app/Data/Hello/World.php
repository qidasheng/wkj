<?php
class Data_Hello_World {

	public function get_data() {
		$dbConfig = array(
		        'PdoMysql'=>array(
		                'test'=>array(
		                        'write'=> array(
		                                'host' => 'localhost',
		                                'port' => '3306',
		                                'name' => 'test',
		                                'user' => 'root',
		                                'pass' => '123456',
		                                'charset' => 'utf8',
		                        ),
		                        'read' => array(
		                                'host' => 'localhost',
		                                'port' => '3306',
		                                'name' => 'test',
		                                'user' => 'root',
		                                'pass' => '123456',
		                                'charset' => 'utf8',
		                        ),
		                ),
		         ),
		);
		$db = new \Sys\Db\MysqlDb($dbConfig);
		$data = $db->connect('test', 'user')->findOne('select * from user where id = ? ', array(1));
		//$data1 = $db->connect('test', 'user')->findAll('select * from user where ? ', array(1));
		//print_r($data);
		//print_r($data1);
		return $data;
	}
}

