<?php namespace Sys\Http;

class Request {
	public function __construct() {

	}

	public function getUri() {
		
		return $this->server('REQUEST_URI');
	}

	public function getPath() {
		
		return $this->server('PATH_INFO');
	}

	public function getQuerySring() {
		
		return $this->server('QUERY_STRING');
	}


	public function isAjax() {
		return $this->server('X-Requested-With') == 'XMLHttpRequest';
	}

	public function getIp() {
		return $this->server('REMOTE_ADDR');
	}

	public function env($name, $key = '', $default = NULL) {
		$data = array();
		switch ($name) {
		    case 'GET':
		        $data = $_GET;
		        break;
		    case 'POST':
		        $data = $_POST;
		        break;
		    case 'SERVER':
		        $data = $_SERVER;
		        break;
		    case 'COOKIE':
		        $data = $_COOKIE;
		        break;
		    case 'SESSION':
		        $data = $_SESSION;
		        break;
		}
		return empty($key) ? $data : (isset($data[$key]) ? $data[$key] : $default);
	
	}
	public function query($key = '', $default = NULL) {
		return $this->env('GET', $key, $default);

	}
	public function form($key = '', $default = NULL) {
		return $this->env('POST', $key, $default);

	}
	public function server($key = '', $default = NULL) {
		return $this->env('SERVER', $key, $default);

	}
	public function cookie($key = '', $default = NULL) {
		return $this->env('COOKIE', $key, $default);

	}
	public function session($key = '', $default = NULL) {
		return $this->env('SESSION', $key, $default);

	}
	public function all() {
		return array_merge($this->env('GET'), $this->env('POST'));
	}


	public static function createRequest() {
		return new self();
    	}

}
