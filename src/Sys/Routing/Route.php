<?php namespace Sys\Routing;

class Route
{
    private $request;
    private $pathInfoArr;
    private $defaultPath = '/index';
    private $ruleConfig = array();

    public function __construct(\Sys\Http\Request $request)
    {
        $this->request = $request;
    }

    public function setRule($ruleConfig)
    {
        $this->ruleConfig = $ruleConfig;
        return $this;
    }

    public function getPath()
    {
        $path = $this->request->getPath();
        return empty($path) ? $this->defaultPath : $path;
    }

    public function setDefaultPath($defaultPath)
    {
        $this->defaultPath = $defaultPath;
        return $this;
    }

    public function isCli()
    {
        return isset($_SERVER['SHELL']) || (PHP_SAPI === 'cli') ? TRUE : FALSE;
    }

    private function parseCliParams()
    {
        $cliParams = $this->request->server('argv');
        unset($cliParams[0]);
        if (empty($cliParams)) {
            return false;
        }
        foreach ($cliParams as $param) {
            $paramArr = explode('=', $param, 2);
            $envName = $paramArr[0];
            $envValArr = explode('&', $paramArr[1]);
            if (in_array($paramArr[0], array("GET", "POST"))) {
                if (!empty($envValArr)) {
                    foreach ($envValArr as $keyVal) {
                        $keyValArr = explode('=', $keyVal);
                        $paramArr[2][$keyValArr[0]] = $keyValArr[1];
                    }
                }
            } elseif (in_array($paramArr[0], array("URI", "SERVER"))) {
                if (is_file($paramArr[1])) {
                    $paramArr[2] = parse_ini_file($paramArr[1]);
                }
                if ($paramArr[0] == "URI") {
                    $paramArr[0] = 'SERVER';
                    $paramArr[2]['PATH_INFO'] = $paramArr[1];
                }
            }
            if (!empty($paramArr[2])) {
                foreach ($paramArr[2] as $key => $val) {
                    $this->request->setEnv($paramArr[0], $key, $val);
                }
            }
        }
    }

    public function beforeParseUri()
    {
        if ($this->isCli()) {
            $this->parseCliParams();
        }
        return true;
    }

    public function rewrite($pathInfo, $rewriteConf)
    {
        foreach ($rewriteConf as $pathFrom => $pathTo) {
            if ($pathInfo == trim($pathFrom, '/')) {
                $pathInfo = $pathTo;
            }
        }
        return $pathInfo;
    }

    public function parseUri()
    {
        $this->beforeParseUri();
        $pathInfo = trim($this->getPath(), '/');
        if (!empty($this->ruleConfig)) {
            //@todo 特殊路由支持
            if (isset($this->ruleConfig['rewrite']) && is_array($this->ruleConfig['rewrite'])) {
                $pathInfo = $this->rewrite($pathInfo, $this->ruleConfig['rewrite']);
            }
        }
        $this->pathInfoArr = explode('/', str_replace('_', '/', $pathInfo));
        return $this;
    }


    public function getRouteInfo()
    {
        $this->parseUri();
        $pathInfoArr = array_map('ucfirst', $this->pathInfoArr);
        $controllerFile = implode(DIRECTORY_SEPARATOR, $pathInfoArr) . '.php';
        $controllerName = implode('', $pathInfoArr) . 'Controller';
        return array($controllerName, $controllerFile);
    }

}
