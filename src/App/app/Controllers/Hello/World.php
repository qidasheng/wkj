<?php

class HelloWorldController extends BaseController
{
    public function __init()
    {
        $this->checkRule = array(
            'uid' => "num|0,10|*|10|uid参数非法",
            'type' => "enum|0,1,2|NULL|2|类型只能为1,2,3",
            'name' => "string|10,30|*|10|用户名参数非法",
            'url' => "custom|url|NULL|http://www.baidu.com|url不是合法的url",
            'test' => "string|0,100|NULL|qidassheng|test参数",
        );
    }


    public function run()
    {
        $curlObj = new \Sys\Http\Curl('http://www.baidu.com/');
        $curlObj->set_connect_timeout(3000);
        $curlObj->set_timeout(3000);
        $curlObj->set_method("GET");
        $res = $curlObj->send();
        $result = $curlObj->get_response_content();


        $TestApiObj = new \Api\TestApi();
        $userInfo = $TestApiObj->setDebug(true)->users_show(array('uid' => 1));


        //不需要建立model、data类也可以直接操作数据库
        $db = new \Sys\Db\MysqlDb(Registry::get('app')->getConf('db'));
        $info = $db->connect('test')->findOne('select * from test where id = ? limit 1', array(1));

        $data['code'] = '100000';
        $data['msg'] = '操作成功';
        $data['db'] = $info;
        $data['curl'] = json_decode($result, true);
        $data['api'] = $userInfo;
        $this->displayJson($data);
    }
}

