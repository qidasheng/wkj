<?php namespace Sys\Db;

class MysqlDb implements DbInterface
{

    protected $dbPoolConfig = array();
    protected $dbPool = array();
    protected $db;
    protected $dbAlias = '';
    protected $tableName = '';

    public function __construct($dbPoolConfig = array())
    {
        $this->dbPoolConfig = !empty($dbPoolConfig) ? $dbPoolConfig : \Registry::get('app')->getConf('db');
        $this->init();
    }

    public function init()
    {
        $configs = $this->dbPoolConfig;
        if (empty($configs)) {
            return array();
        }
        foreach ($configs as $type => $aliases) {
            $class = "\\Sys\Db\\" . $type;
            foreach ($aliases as $alias => $config) {
                $this->dbPool[$alias] = new $class();
                $this->dbPool[$alias]->configure($alias, $config);
            }
        }
        return true;
    }

    public function connect($dbAlias = '', $tableName = '')
    {
        if (!empty($dbAlias)) {
            $this->dbAlias = $dbAlias;
        }
        if (!empty($tableName)) {
            $this->tableName = $tableName;
        }
        if (empty($this->dbPool)) {
            $this->init();
        }
        try {
            $this->db = $this->dbPool[$this->dbAlias];
        } catch (Exception $e) {
            return false;
        }
        return $this;
    }


    public function db()
    {
        if (!is_object($this->db)) {
            $this->connect();
        }
        if (!is_object($this->db)) {
            throw new \Exception('Connect db failed!');
        }
        return $this->db;
    }

    /**
     * 强制操作主
     * @return $this
     */
    public function master()
    {
        $this->db()->set_write();
        return $this;
    }

    /**
     *强制操作从库
     * @return $this
     */
    public function slave()
    {
        $this->db()->set_read();
        return $this;
    }

    /**
     * 查询单条记录
     *
     * @param string $sql sql语句。只能为select语句
     * @param array $data
     * @param bool $fetch_index 结果集是否使用下标数字方式
     * @return array
     */
    public function findOne($sql, array $data = array(), $fetch_index = false)
    {
        $data = $this->db()->fetch_all($sql, $data, $fetch_index);
        if (is_array($data) && isset($data[0])) {
            return $data[0];
        }
        return $data;
    }

    /**
     * 查询多条单条记录
     *
     * @param string $sql sql语句。只能为select语句
     * @param array $data
     * @param bool $fetch_index 结果集是否使用下标数字方式
     * @return array
     */
    public function findAll($sql, array $data = array(), $fetch_index = false)
    {
        $data = $this->db()->fetch_all($sql, $data, $fetch_index);
        return $data;
    }


    /**
     * 插入数据，支持批量插入
     * @param array $data
     * @param bool $ignore 是否使用INSERT IGNORE语法
     * @return int   //单条插入返回last insert id，批量插入返回插入记录数
     */
    public function add($data, $ignore = false)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        //是不是批量插入
        if (is_array($data[0])) {
            $is_batch = true;
        } else {
            $data = array($data);
            $is_batch = false;
        }

        if (empty($data[0]) || !is_array($data[0])) {
            return false;
        }
        $ret = false;

        $ignore_str = $ignore ? 'IGNORE' : '';

        $keys = array_keys($data[0]);
        $sql = 'INSERT ' . $ignore_str . ' INTO ' . $this->tableName . '(' . implode(",", $keys) . ') VALUES ';
        $size = sizeof($keys);
        $params = array();
        for ($i = 0; $i < $size; $i++) {
            $params[] = '?';
        }
        $values = array();
        $values_sql = array();
        foreach ($data as $value) {
            $values = array_merge($values, array_values($value));
            $values_sql[] = '(' . implode(",", $params) . ')';
        }
        $sql .= implode(",", $values_sql);
        //返回插入行数
        $ret = $this->db()->exec($sql, array_values($values));
        //单条插入返回last insert id
        if ($ret && !$is_batch) {
            $ret = $this->db()->last_insert_id(); //注意只适合单条插入！！！
        }

        return $ret;
    }


    /**
     * 删除数据
     * @param $where
     */
    public function del($where, $where_data = array())
    {
        $ret = false;
        if (empty($where)) {
            return $ret;
        }
        $sql = "DELETE FROM " . $this->tableName . " WHERE " . $where;
        $ret = $this->db()->exec($sql, array_values($where_data));

        return $ret;
    }


    /**
     * 更新数据
     * @param $data
     * @param $where
     * @return bool
     */
    public function update($data, $where, $where_data = array())
    {
        $ret = false;
        if (empty($data) || empty($where)) {
            return $ret;
        }

        $sets = '';
        if ($data && !empty($data)) {
            $fields = array_keys($data);
            foreach ($fields as $k => $field) {
                $sets .= $field . ' = ?, ';
            }
            $sets = rtrim($sets, ', ');

            $sql = 'UPDATE ' . $this->tableName . ' SET ' . $sets . ' WHERE ' . $where;
            $ret = $this->db()->exec($sql, array_merge(array_values($data), array_values($where_data)));
        }

        return $ret;
    }


    /**
     * 执行自定义任意SQL
     * @param $sql
     * @param array $data
     * @return mixed
     */
    public function exec($sql, array $data = array())
    {
        if (empty($data)) {
            $data = array();
        }
        return $this->db()->exec($sql, $data);
    }

}

