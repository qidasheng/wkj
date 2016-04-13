<?php
class Sys {
    private $pathInfoArr = array();
    private $controllerName = '';
    private $controllerPath = '';
    	 	
    public function __construct() {

    }


    public function init($env) {
	if (isset($env['controllerPath'])) {
		$this->controllerPath = $env['controllerPath'];
	}
        return $this;
    }

    
    public function router() {
	//print_r($_SERVER);
	$pathInfo = $_SERVER['PATH_INFO'];
	$this->pathInfoArr = explode('/', str_replace('_', '/', trim($pathInfo, '/')));
        return $this;
    }

    public function dispatch() {
        $pathInfoArr = array_map('ucfirst', $this->pathInfoArr);	
	$controllerFile = $this->controllerPath.'/'.implode(DIRECTORY_SEPARATOR, $pathInfoArr).'.php'; 
	$this->controllerName = implode('', $pathInfoArr).'Controller';
	//echo $controllerFile." -> ".$this->controllerName;
	if (!file_exists($controllerFile)) {
		//thow new Exception('404!');
		exit('404!');
	}
	include_once($controllerFile);
	if (class_exists($this->controllerName) && method_exists($this->controllerName,'run')) {
		$controllerObj = new $this->controllerName();
		$controllerObj->run();
	} else {
		exit('Controller not found!');
	}
        return $this;
    }

    public function run($env) {
	$this->init($env)
	     ->router()
	     ->dispatch();
    }

}
