<?php

class BaseController
{
    protected $app;
    protected $request;
    protected $checkRule = array();

    public function setApp($app)
    {
        $this->app = $app;
        $this->request = $app->getRequest();
        return $this;
    }

    public function __init()
    {

    }


    public function paramsCheck()
    {
        if (!empty($this->checkRule)) {
            $validatorObj = new \Sys\Validation\Validator();
            $validatorObj->check($this->request->all(), $this->checkRule);
            $defaultArr = $validatorObj->getDefaults();
            if (!empty($defaultArr)) {
                foreach ($defaultArr as $key => $val) {
                    $this->request->setEnv('GET', $key, $val);
                }
            }
        }
        return true;

    }

    public function _run()
    {
        try {
            //调用初始化过程
            $this->__init();

            //参数校验
            $this->paramsCheck();

            //@todo check login

            //运行程序方法
            $this->run();
        } catch (Exception $e) {
            //print_r($e);
            //@todo add log
            $this->__handleException($e);
        }
        return true;
    }


    public function __handleException($e)
    {
        //echo "异常信息：". $e->getCode() . '->' .$e->getMessage();
        $this->displayJson(array('code' => $e->getCode(), 'msg' => $e->getMessage()));
    }


    public function displayJson($data)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $jsonp = $this->request->all('callback');
        if (!is_null($jsonp)) {
            header('Content-type: text/javascript');
            $jsonData = $jsonp . '(' . json_encode($data) . ')';
        } else {
            header('Content-type: application/json');
            $jsonData = json_encode($data);
        }
        echo $jsonData;
    }
}
