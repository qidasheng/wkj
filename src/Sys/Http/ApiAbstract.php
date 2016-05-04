<?php namespace Sys\Http;

abstract class ApiAbstract
{
    protected $curl;
    protected $domain = "http://api.test.com";
    protected $method = "GET";
    protected $connectTimeout = 3000;
    protected $timeout = 3000;
    protected $authSupportType = array('header', 'cookie', 'param', 'basic');
    protected $authType = '';
    protected $authData = array();
    protected $debug = false;

    public function __construct($curl = null)
    {
        $this->curl = !empty($curl) ? $curl : $this->createCurl();
        $this->init();
    }

    public function init()
    {

    }

    protected function createCurl()
    {
        return new \Sys\Http\Curl();
    }

    public function setDebug($debug)
    {
        $this->curl->set_debug($debug);
        $this->debug = $debug;
        return $this;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function setConnectTimeout($timeout)
    {
        $this->connectTimeout = $timeout;
        return $this;
    }

    public function setExecTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setAuth($authType, $authData)
    {
        $this->authType = $authType;
        $this->authData = $authData;
    }

    public function auth()
    {
        if (!in_array($this->authType, $this->authSupportType)) {
            throw new \Exception("authType must is " . explode(',', $this->authSupportType));
        }
        if (!empty($this->authData)) {
            $authFunc = "setAuthBy" . ucfirst(strtolower($this->authType));
            $this->$authFunc($this->authData);
        }
        return $this;
    }

    public function get($uri, $data, $returnType = '')
    {
        $this->setUrl($this->domain . $uri)
            ->setMethod('GET')
            ->setQueryParams($data)
            ->setTimeout($this->connectTimeout, $this->timeout);
        return $this->auth()->returnResult($returnType);

    }

    public function post($uri, $data, $returnType = '')
    {
        $this->setUrl($this->domain . $uri)
            ->setMethod('POST')
            ->setPostParams($data)
            ->setTimeout($this->connectTimeout, $this->timeout);
        return $this->auth()->returnResult($returnType);
    }

    public function returnResult($returnType)
    {
        $data = '';
        if ($returnType == "json") {
            $data = $this->getJson($returnType);
        } else {
            $data = $this->getResponse();
        }
        return $data;

    }

    public function setUrl($url)
    {
        $this->curl->set_url($url);
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        $this->curl->set_method($method);
        return $this;
    }

    public function setQueryParams($params)
    {
        if (empty($params)) {
            return false;
        }
        foreach ($params as $key => $value) {
            $this->curl->add_query_field($key, $value);
        }
        return $this;

    }

    public function setPostParams($params)
    {
        if (empty($params)) {
            return false;
        }
        foreach ($params as $key => $value) {
            $this->curl->add_post_field($key, $value);
        }
        return $this;
    }

    public function setTimeout($connectTime = 3000, $execTime = 3000)
    {
        $this->curl->set_connect_timeout($connectTime);
        $this->curl->set_timeout($execTime);
        return $this;
    }

    public function setAuthByParam($authData)
    {
        if (strtolower($this->method) == 'get') {
            $this->setQueryParams($authData);
        } else {
            $this->setPostParams($authData);
        }
        return $this;
    }

    public function setAuthByBASIC($authData)
    {
        $this->curl->set_basic_info($authData['user'] . ":" . $authData['pwd']);
        return $this;
    }

    public function setAuthByHeader($authData)
    {
        foreach ($authData as $key => $value) {
            $this->curl->add_header($key, $value);
        }
        return $this;
    }

    public function setAuthByCookie($authData)
    {
        foreach ($authData as $key => $value) {
            $this->curl->add_cookie($key, $value);
        }
        return $this;

    }

    public function getResponse()
    {
        $res = $this->curl->send();
        $result = $this->curl->get_response_content();
        return $result;
    }

    public function getJson($assoc = false)
    {
        $content = $this->getResponse();
        $dataArr = json_decode($content, $assoc);
        if ($this->debug) {
            $dataArr['curlDebugInfo'] = $this->getDebugInfo();
        }
        return $dataArr;
    }

    public function getDebugInfo()
    {
        return $this->curl->get_curl_cli();
    }

}
