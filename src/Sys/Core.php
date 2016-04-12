<?php
class Sys {
	
    public function __construct() {

    }

    public function init() {
        return $this;
    }

    
    public function router() {
        return $this;
    }

    public function dispatch() {
	$controller_test_a =  new controller_test_a();
	$controller_test_a->index();
        return $this;
    }

    public function run() {
	$this->init()
	     ->router()
	     ->dispatch();
    }

}
