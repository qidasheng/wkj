<?php namespace Sys\Core;

use Sys\Http;
use Sys\Routing;

class Application {
    private $request;
    private $pathInfoArr = array();
    private $controllerName = '';
    private $controllerDir = '';
    private $appConfig = array();
    private $routeInfo = array();
    public function __construct(Request $request = null) {
	$this->request = $request ? $request : $this->createRequest();	
    }

    protected function createRequest() {
	return forward_static_call(array('\Sys\Http\Request', 'createRequest'));
    }   

    public function getRequest() {
            return $this->request;
    }

    private function setControllerDir($dir) {
        $this->controllerDir = $dir;
    }

    public function setAppConfig($appConfig) {
	$this->appConfig = $appConfig;
        if (isset($this->appConfig['controllerDir'])) {
		$this->setControllerDir($this->appConfig['controllerDir']);
	}
	return $this;
    }

    private function route() {
	$route = new \Sys\Routing\Route($this->request);
	$ruleConfig = isset($this->appConfig['routeRule']) ? $this->appConfig['routeRule'] : array();
	$this->routeInfo = $route->setRule($ruleConfig)->getRouteInfo();
	return $this;
    }

    private function dispatch() {
        list($controllerName, $controllerFile) = $this->routeInfo;
	$controllerFile = $this->controllerDir.DIRECTORY_SEPARATOR.$controllerFile; 
	if (!file_exists($controllerFile)) {
		 throw new \Exception('Controller file '.$controllerFile.' not found!');
        }
        include_once($controllerFile);
        $controllerClass = new \ReflectionClass($controllerName);
        $controllerObj = $controllerClass->newInstance();
        //$controllerClass->getMethod('run')->invoke($controllerObj);
        $controllerObj->setApp($this)->run();
    }

    public function run($env) {
        $this->setAppConfig($env)
             ->route()
             ->dispatch();
    }
}
