<?php namespace Sys\Routing;

class Route {
	private $request;
	private $pathInfoArr;
	private $defaultPath = '/index';
	private $ruleConfig = array();
        public function __construct(\Sys\Http\Request $request) {
		$this->request = $request;
        }

	public function setRule($ruleConfig) {
		$this->ruleConfig = $ruleConfig;
		return $this;
	}

	public function getPath() {
		$path = $this->request->getPath();
		return empty($path) ? $this->defaultPath : $path;
	}

	public function setDefaultPath($defaultPath) {
		$this->defaultPath = $defaultPath;
		return $this;
	}
	
	public function isCli() {

	}

	
	public function parseUri() {
	    $pathInfo = $this->getPath();
	    if (!empty($this->ruleConfig)) {
		//@todo 特殊路由支持
	    } else {
	    	$this->pathInfoArr = explode('/', str_replace('_', '/', trim($pathInfo, '/')));
	    }
	    return $this;
	}
	
	public function getRouteInfo() {
	    $this->parseUri();
	    $pathInfoArr = array_map('ucfirst', $this->pathInfoArr);
	    $controllerFile = implode(DIRECTORY_SEPARATOR, $pathInfoArr).'.php';
	    $controllerName = implode('', $pathInfoArr).'Controller';
	    return array($controllerName, $controllerFile);
	}

}
