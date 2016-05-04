<?php

class Registry
{
    private static $_instance = NULL;
    private $_pool = array();

    public static function getInstance()
    {
        if (self::$_instance == NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    public static function get($name)
    {
        return self::getInstance()->getVal($name);
    }

    public static function set($name, $val)
    {
        self::getInstance()->setVal($name, $val);
    }

    public function getVal($name)
    {
        return isset($this->_pool[$name]) ? $this->_pool[$name] : NULL;
    }

    public function setVal($name, $val)
    {
        $this->_pool[$name] = $val;
    }
}

