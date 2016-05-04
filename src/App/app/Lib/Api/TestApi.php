<?php namespace Api;

class TestApi extends \Sys\Http\ApiAbstract
{
    protected $domain = "http://api.test.com";
    protected $connectTimeout = 3000;
    protected $timeout = 3000;
    protected $authType = 'source';  //source_key,source,header,cookier,param,basic
    protected $authData = array();

    public function init()
    {
        $this->setDefaultAuth();
    }

    public function setDefaultAuth()
    {
        $this->setAuth('param', array('key' => 88888888));
    }

    public function users_show($data, $returnType = 'json')
    {
        return $this->get('/users/list', $data, $returnType);
    }

    public function getShortUrl($data, $returnType = 'json')
    {
        $this->setAuth('basic', array('user' => "test", "pwd" => '123'));
        return $this->get('/users/add', $data, $returnType);
    }
}
