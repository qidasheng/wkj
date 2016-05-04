<?php namespace Sys\Http;

class Request
{
    public function __construct()
    {

    }

    public function getUri()
    {
        return $this->server('REQUEST_URI');
    }

    public function getPath()
    {
        return $this->server('PATH_INFO');

    }

    public function getQuerySring()
    {
        return $this->server('QUERY_STRING');
    }


    public function isAjax()
    {
        return $this->server('X-Requested-With') == 'XMLHttpRequest';
    }

    public function getIp()
    {
        return $this->server('REMOTE_ADDR');
    }

    public function getEnv($name, $key = '', $default = NULL)
    {
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

    public function setEnv($name, $key, $val = '')
    {
        $data = array();
        switch ($name) {
            case 'GET':
                $_GET[$key] = $val;
                break;
            case 'POST':
                $_POST[$key] = $val;
                break;
            case 'SERVER':
                $_SERVER[$key] = $val;
                break;
            case 'COOKIE':
                $_COOKIE[$key] = $val;
                break;
            case 'SESSION':
                $_SESSION[$key] = $val;
                break;
        }
        return true;
    }


    public function query($key = '', $default = NULL)
    {
        return $this->getEnv('GET', $key, $default);

    }

    public function form($key = '', $default = NULL)
    {
        return $this->getEnv('POST', $key, $default);

    }

    public function server($key = '', $default = NULL)
    {
        return $this->getEnv('SERVER', $key, $default);

    }

    public function cookie($key = '', $default = NULL)
    {
        return $this->getEnv('COOKIE', $key, $default);

    }

    public function session($key = '', $default = NULL)
    {
        return $this->getEnv('SESSION', $key, $default);

    }

    public function all($key = '', $default = NULL)
    {
        $data = array_merge($this->getEnv('GET'), $this->getEnv('POST'));
        return empty($key) ? $data : (isset($data[$key]) ? $data[$key] : $default);
    }


    public static function createRequest()
    {
        return new self();
    }

}
