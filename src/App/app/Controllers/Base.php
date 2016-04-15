<?php
class BaseController {
	protected $app;
	protected $request;
	public function setApp($app) {
		$this->app = $app;
		$this->request = $app->getRequest();
		return $this;
	}
	
        public function run() {
                echo "@".date('Y-m-d H:i:s')."\n";
        }
}
