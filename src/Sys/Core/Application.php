<?php namespace Sys\Core;

use Sys\Http;
use Sys\Routing;
use \Registry as Registry;

class Application
{
    private $request;
    private $pathInfoArr = array();
    private $controllerName = '';
    private $controllerDir = '';
    private $configDir = '';
    private $appConfig = array();
    private $routeInfo = array();
    private $reg = NULL;

    public function __construct(Request $request = null)
    {
        $this->request = $request ? $request : $this->createRequest();
        Registry::set('app', $this);
    }

    protected function createRequest()
    {
        return forward_static_call(array('\Sys\Http\Request', 'createRequest'));
    }

    public function getRequest()
    {
        return $this->request;
    }

    private function setControllerDir($dir)
    {
        $this->controllerDir = $dir;
    }

    private function setConfigDir($dir)
    {
        $this->configDir = $dir;
    }

    public function getConf($path)
    {
        $config = array();
        if (empty($path)) {
            return $config;
        }
        $regKey = 'conf_' . $path;
        if (Registry::get($regKey) != NULL) {
            return Registry::get($regKey);
        }
        $pathArr = explode('.', $path, 2);
        $configFilePhp = $this->configDir . DIRECTORY_SEPARATOR . $pathArr[0] . '.php';
        if (file_exists($configFilePhp)) {
            $configPhp = include($configFilePhp);
            $config = array_merge($config, $configPhp);
        }
        $configFileIni = $this->configDir . DIRECTORY_SEPARATOR . $pathArr[0] . '.ini';
        if (file_exists($configFileIni)) {
            $configIni = parse_ini_file($configFileIni);
            $config = array_merge($config, $configIni);
        }
        if (!empty($pathArr[1])) {
            $nodeArr = explore('.', $pathArr[1]);
            $configTmp = $config;
            foreach ($nodeArr as $node) {
                isset($configTmp[$node]) && $configTmp = $configTmp[$node];
            }
            $config = $configTmp;
        }
        Registry::set($regKey, $config);
        return $config;
    }

    public function setAppConfig($appConfig)
    {
        $this->appConfig = $appConfig;
        if (isset($this->appConfig['appDir'])) {
            $this->setControllerDir($this->appConfig['appDir'] . DIRECTORY_SEPARATOR . "Controllers");
            $this->setConfigDir($this->appConfig['appDir'] . DIRECTORY_SEPARATOR . "Config");
        }
        return $this;
    }

    private function route()
    {
        $route = new \Sys\Routing\Route($this->request);
        $ruleConfig = isset($this->appConfig['routeRule']) ? $this->appConfig['routeRule'] : array();
        $this->routeInfo = $route->setRule($ruleConfig)->getRouteInfo();
        return $this;
    }

    private function dispatch()
    {
        list($controllerName, $controllerFile) = $this->routeInfo;
        $controllerFile = $this->controllerDir . DIRECTORY_SEPARATOR . $controllerFile;
        if (!file_exists($controllerFile)) {
            throw new \Exception('Controller file ' . $controllerFile . ' not found!');
        }
        include_once($controllerFile);
        $controllerClass = new \ReflectionClass($controllerName);
        $controllerObj = $controllerClass->newInstance();
        //$controllerClass->getMethod('run')->invoke($controllerObj);
        $controllerObj->setApp($this)->_run();
    }

    public function run($env)
    {
        $this->setAppConfig($env)
            ->route()
            ->dispatch();
    }
}
