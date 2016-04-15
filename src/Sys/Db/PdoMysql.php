<?php namespace Sys\Db;

use \PDO as PDO;

class PdoMysql {
    static private $mode_auto = 0;
    static private $mode_read = 1;
    static private $mode_write = 2;

    /**
     * 读库配置项
     *
     * @var array
     */
    protected $read_config = array();

    /**
     * 写库配置项
     *
     * @var array
     */
    protected $write_config = array();

    /**
     * 读库PDO实例
     *
     * @var PDO
     */
    protected $read_inst;

    /**
     * 写库PDO实例
     *
     * @var PDO
     */
    protected $write_inst;

    /**
     * 最后一次使用的PDO实例
     *
     * @var PDO
     */
    protected $last_inst;

    /**
     * 当前读写模式
     *
     * @var int
     */
    protected $mode = 0;

    /**
     * 默认别名
     *
     * @var string
     */
    protected $alias = 'Undefined';

    /**
     * 配置项数组可用key值
     *
     * @var array
     */
    static protected $config_keys = array('host', 'port', 'name', 'user', 'pass', '*attr', 'charset');

    /**
     * 实现DB的configure接口
     *
     * @param string $alias  实例别名
     * @param array $config  配置项
     */
    public function configure($alias, $config){
        $this->alias = $alias;
        $read_configs = $write_configs = array();
        foreach ($config as $k => $v){
            self::check_config_format($v);

            if(strpos($k, 'read') !== false){
                $read_configs[] = $v;
            }
            if(strpos($k, 'write') !== false){
                $write_configs[] = $v;
            }
        }

        $read_configs AND $this->read_config = $read_configs[array_rand($read_configs)];
        $write_configs AND $this->write_config = $write_configs[array_rand($write_configs)];
        if(!$this->read_config && !$this->write_config){
            throw new \Exception('Must define at least one db for "' . $this->alias . '"!');
        }
    }

    /**
     * 强制使用读库
     *
     * 该方法可以使用链式调用继续访问当前对象。
     *
     * @return Comm_Db_PdoMysql
     */
    public function set_read(){
        $this->mode = 1;
        return $this;
    }

    /**
     * 强制使用写库
     *
     * 该方法可以使用链式调用继续访问当前对象。
     *
     * @return Comm_Db_PdoMysql
     */
    public function set_write(){
        $this->mode = 2;
        return $this;
    }

    /**
     * 根据sql语句自行判断
     *
     * 该方法可以使用链式调用继续访问当前对象。
     *
     * @return Comm_Db_PdoMysql
     */
    public function set_auto(){
        $this->mode = 0;
        return $this;
    }

    /**
     * 执行一个sql语句并返回影响行数。
     *
     * 如果在insert或者replace语句后需要获取 last insert id 请使用last_insert_id()方法
     *
     * @param string $sql	sql语句。不能为select语句
     * @param array $data
     * @throws Comm_Exception_Program
     * @throws \Exception
     */
    public function exec($sql, array $data = NULL){
        $statement = $this->execute_sql($sql, $data);

        return $statement->rowCount();
    }

    /**
     * 执行提供的select语句并返回结果集。
     *
     * @param string $sql	sql语句。只能为select语句
     * @param array $data
     * @param bool $fetch_index  结果集是否使用下标数字方式
     * @return array
     */
    public function fetch_all($sql, array $data = NULL, $fetch_index = false){
        $verb = self::extract_sql_verb($sql);
        if($verb !== 'select'){
            throw new \Exception('Can not fetch on a non-select sql');
        }

        $statement = $this->execute_sql($sql, $data);

        return $statement->fetchAll($fetch_index ? PDO::FETCH_NUM : PDO::FETCH_ASSOC);
    }

    /**
     * PDO同名方法封装
     *
     * @see PDO::prepare()
     * @param string $sql
     * @return PDOStatement
     */
    public function prepare($sql){
        $pdo = $this->get_inst($this->detect_sql_type($sql));
        $args = func_get_args();
        return call_user_func_array(array($pdo, 'prepare'), $args);
    }

    /**
     * PDO同名方法封装
     *
     * @param string $sql
     * @return PDOStatement
     */
    public function query($sql){
        $pdo = $this->get_inst($this->detect_sql_type($sql));
        $args = func_get_args();
        return call_user_func_array(array($pdo, 'query'), $args);
    }

    /**
     * PDO::getAttribute() 方法的重命名封装版
     * @param int $attribute
     * @return mixed
     */
    public function get_attribute($attribute){
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
    }

    /**
     * PDO::setAttribute() 方法的重命名封装版
     *
     * @param int $attribute
     * @param mixed $value
     */
    public function set_attribute($attribute, $value){
        $this->attributes[$attribute] = $value;
    }

    public function close(){
        $this->read_inst = NULL;
        $this->write_inst = NULL;
        $this->last_inst = NULL;
    }

    public function __call($func, $args){
        //Convert do_something() style to dosomething()
        $func = str_replace('_', '', strtolower($func));
        //Because of class method name is case insensitive in PHP, so, this simple
        //    process is enough and fast.

        $mode = self::$mode_auto;
        if(in_array($func, array('lastinsertid', 'begintransaction', 'intransaction', 'commit', 'rollback'))){
            $mode = self::$mode_write;
        }
        return call_user_func_array(array($this->get_inst($mode), $func), $args);
    }

    /**
     * Simple function to replicate PHP 5 behaviour
     */
    public static function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    /**
     * 执行一个sql并返回PDOStatement对象和执行结果。
     *
     * @param string $sql
     * @param array $data
     * @return mixed
     */
    protected function execute_sql($sql, array $data = NULL){
        $time_start = self::microtime_float();
        $statement = $this->prepare($sql);
        /* @var $statement PDOStatement */
        if($data){
            $result = $statement->execute($data);
        }else{
            $result = $statement->execute();
        }
        $time_end = self::microtime_float();
        $time = $time_end - $time_start;
        if (false) {
            //避免再出状况，捕获下异常
            try {
                $msg = sprintf('[TIME:%s]   [SQL:%s]    [DATA:%s]', number_format($time, 8, '.', ''), $sql, is_array($data) ? implode(',', $data) : $data);
                //Tool_Log::write_log('SQL', $msg);
            } catch (Exception $e) {

            }
        }
        if(!$result){
            $error = $statement->errorInfo();
            if(is_array($error)){
                $error = implode(',', $error);
            }else{
                $error = strval($error);
            }
            throw new \Exception($error);
        }

        return $statement;
    }

    /**
     * 根据指定的类型获取pdo实例。
     *
     * @param int $mode
     * @return PDO
     */
    protected function get_inst($mode){
        if($mode === self::$mode_auto){
            if(null === $this->last_inst){
                //default read, unless set write mode
                $this->last_inst = $this->get_inst(
                    $this->mode === self::$mode_write
                        ? self::$mode_write
                        : self::$mode_read
                );
            }
            return $this->last_inst;
        }
        if($mode === self::$mode_read){
            if(null === $this->read_inst){
                if(!$this->read_config){
                    return $this->get_inst(self::$mode_write);
                }
                $this->read_inst = $this->get_pdo($this->read_config);
            }
            $this->last_inst = $this->read_inst;
            return $this->read_inst;
        }
        if($mode === self::$mode_write){
            if(null === $this->write_inst){
                if(!$this->write_config){
                    throw new \Exception('Writable db must be defined');
                }
                $this->write_inst = $this->get_pdo($this->write_config);
            }
            $this->last_inst = $this->write_inst;
            return $this->write_inst;
        }
    }

    protected function get_pdo($config){
        try{
            if (empty($config['charset'])) {
                $config['charset'] = 'utf8';
            }
            if (version_compare(PHP_VERSION, '5.3.6', '<')) {
                $charset = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$config['charset']);
                $config['options'] = isset($config['options']) ? array_merge($config['options'], $charset) : $charset;
                $inst = new PDO("mysql:dbname={$config['name']};host={$config['host']};port={$config['port']};charset={$config['charset']}", $config['user'], $config['pass'], $config['options']);
            } else {
                $inst = new PDO("mysql:dbname={$config['name']};host={$config['host']};port={$config['port']};charset={$config['charset']}", $config['user'], $config['pass'], isset($config['options']) ? $config['options'] : array());
            }
        }catch (Exception $ex){
            throw new \Exception("mysql:dbname={$config['name']};host={$config['host']};port={$config['port']};" . $ex->getMessage());
        }
        if(!empty($config['attr']) && is_array($config['attr'])){
            foreach ($config['attr'] as $k => $v){
                $inst->setAttribute($k, $v);
            }
        }


        return $inst;
    }

    /**
     * 提取sql语句的动词
     *
     * @param string $sql
     * @return string 动词
     */
    static protected function extract_sql_verb($sql){
        $sql_components = explode(' ', ltrim($sql), 2);
        $verb = strtolower($sql_components[0]);
        return $verb;
    }

    /**
     * 检测sql所需的数据库类型
     * @param string $sql
     * @return ENUM
     */
    static protected function detect_sql_type($sql){
        if(self::extract_sql_verb($sql) === 'select'){
            if(strstr($sql,'for update')){
                return self::$mode_write;
            }
            return self::$mode_auto;
        }
        return self::$mode_write;
    }

    /**
     * 检查配置文件格式是否合格
     *
     * @param array $config
     * @throws Comm_Exception_Program
     */
    protected function check_config_format(array $config){
        $valid_keys = array_fill_keys(self::$config_keys, 0);
        foreach ($config as $k => $v){
            //检查是否是必选或者可选参数。可选参数以*号开头
            if(!isset($valid_keys[$k]) && !isset($valid_keys["*$k"])){
                throw new \Exception('Unused PdoMysql "' . $this->alias . '" config "' . $k . '"');
            }
            unset($valid_keys[$k]);
        }

        if($valid_keys){
            $keys = array_keys($valid_keys);
            //忽略掉可选参数。可选参数以*号开头
            do{
                $key = array_pop($keys);
            }while ($key{0} === '*');
            if($key && $key{0} !== '*'){
                throw new \Exception('Missing PdoMysql "' . $this->alias . '" config value "' . $key . '"');
            }
        }
    }
} 
