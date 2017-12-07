<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2017/12/4
 * Time: 20:29
 */
namespace core\lib;

use core\common\config;

class Db
{
    protected $msg = array();       /* 提示消息数组 */
    protected $tableName = '';      /* 表名, 自动获取 */
    protected $filedList = array(); /* 表字段结构, 自动获取 */
    protected $dbcfgname = null;    /* 配置文件名 */

    private $_charset;
    private $_dsn;
    private $_host;
    private $_user;
    private $_pass;
    private $_port;
    private $_dbname;
    private $_table;
    private $_tableprefix;
    private $_dbInstance;

    protected $_error;
    protected $data         = array();  // 数据信息
    protected $options      = array();  // 查询表达式参数
    protected $_validate    = array();  // 自动验证定义
    protected $_auto        = array();  // 自动完成定义
    protected $_map         = array();  // 字段映射定义
    protected $_scope       = array();  // 命名范围定义

    /* 链式操作方法列表 */
    protected $methods      = array('strict', 'order', 'alias', 'having', 'group', 'lock', 'distinct', 'auto',
                                'filter', 'validate', 'result', 'token', 'index', 'force');

    /*
     * 初始化函数
     */
    public function __construct($host, $user, $pass, $dbname, $port = 3306, $charset = 'uft8', $prefix = '') {
        if (!isset($host) || !isset($user) || !isset($pass) || !isset($dbname)) {
            $config = \core\common\config::RC('db', true);
            $this->_charset     = $config['charset'];
            $this->_host        = $config['hostname'];
            $this->_user        = $config['username'];
            $this->_pass        = $config['password'];
            $this->_dbname      = $config['database'];
            $this->_port        = $config['port'];
            $this->_tableprefix = $config['prefix'];
        } else {
            $this->_charset     = $charset;
            $this->_host        = $host;
            $this->_user        = $user;
            $this->_pass        = $pass;
            $this->_dbname      = $dbname;
            $this->_port        = $port;
            $this->_tableprefix = $prefix;
        }
        $this->_dsn = 'mysql:host=' . $this->_host . ';dbname=' . $this->_dbname;

        $_dbObj = new \mysqli($this->_host, $this->_user, $this->_pass, $this->_dbname, $this->_port);
        if ($_dbObj->connect_errno) {
            $this->_error = $_dbObj->connect_error;
            return false;
        } else {
            $this->_dbInstance = $_dbObj;
            return $this;
        }
    }

    /*
     * 获取当前数据表名
     */
    public function getTableName() {
        return $this->_table;
    }

    /*
     * 获取当前数据库名
     */
    public function getDbName() {
        return $this->_dbname;
    }

    /*
     * 返回错误信息
     */
    public function error() {
        return $this->_error;
    }

    /*
     * 回调方法, 初始化模型
     */
    protected function _initialize() {
    }

    /*
     * 设置数据对象值
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /*
     * 获取数据对象值
     */
    public function __get($name) {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /*
     * 检测数据对象值
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /*
     * 销毁数据对象值
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /*
     * 解决类中不存在方法，待实现
     */
    public function __call($name, $arguments) {
        // TODO: Implement __call() method.
    }

    /*
     * 选择数据库
     */
    public function db_select($dbname) {
        $db_select = mysqli_select_db($this->_dbInstance, $dbname);
        if ($db_select) {
            $this->_dbname = $dbname;
            $_dbObj = new \mysqli($this->_host, $this->_user, $this->_pass, $this->_dbname);
            if ($_dbObj->connect_errno) {
                $this->_error = $_dbObj->connect_error;
                return false;
            } else {
                $this->_dbInstance = $_dbObj;
                return $this;
            }
        } else {
            $this->_error = mysqli_error($this->_dbInstance);
            return false;
        }
    }

    /*
     * 数据库用户更换
     */
    public function db_changeUser($user, $pass) {
        $db_change_user = mysqli_change_user($this->_dbInstance, $user, $pass, $this->_dbname);
        if ($db_change_user) {
            $this->_user = $user;
            $this->_pass = $pass;
            $_dbObj = new \mysqli($this->_host, $this->_user, $this->_pass, $this->_dbname);
            if ($_dbObj->connect_errno) {
                $this->_error = $_dbObj->connect_error;
                return false;
            } else {
                $this->_dbInstance = $_dbObj;
                return $this;
            }
        } else {
            $this->_error = mysqli_error($this->_dbInstance);
            return false;
        }
    }

    /*
     * 获取数据库中所有的表名
     */
    public function db_getTables() {
        $sql = 'show tables';
        $db_tables = mysqli_query($this->_dbInstance, $sql);
        if ($db_tables) {
            $num_rows = $db_tables->num_rows;
            $msg_tables = array('count' => $num_rows, 'tables' => array());

            for ($i = 0; $i < $num_rows; $i++) {
                $row = $db_tables->fetch_assoc();
                $key = 'Tables_in_in' . $this->_dbname;
                array_push($msg_tables['tables'], $row['$key']);
            }
            mysqli_free_result($db_tables);
            return $msg_tables;

        } else {
            mysqli_free_result($db_tables);
            return false;
        }
    }

    /*
     * 获取指定表中所有信息
     */
    public function db_selectTable($table) {
        $sql = 'select * from '. $table;
        $db_table = mysqli_query($this->_dbInstance, $sql);
        if ($db_table) {
            $this->_table = $table;
            $msg_table = self::query_handle($db_table);
            mysqli_free_result($db_table);
            return $msg_table;
        } else {
            mysqli_free_result($db_table);
            return false;
        }
    }

    /*
     * 获取指定表的字段详情
     */
    public function db_selectTableFields($table) {
        $sql = 'show fields from ' . $table;
        $db_fields = mysqli_query($this->_dbInstance, $sql);
        if ($db_fields) {
            $this->_table = $table;
            $msg_fields = self::query_handle($db_fields);
            mysqli_free_result($db_fields);
            return $msg_fields;
        } else {
            mysqli_free_result($db_fields);
            return false;
        }
    }

    /*
     * 获取数据表中指定字段信息
     */
    public function getField($field) {
        $fields = self::param_handle($field);
        $count = count($fields);
        for ($i = 0; $i < $count; $i++) {
            $index = $fields[$i];
            $sql = 'select ' . $index . ' from ' . $this->_table;
            $res = mysqli_query($this->_dbInstance, $sql);
            $msg_fields[$index] = self::query_handle($res);
        }
    }

    /*
     * 传入参数处理
     */
    protected function param_handle($param) {
        if (is_string($param) && !empty($param)) {
            $params = explode(',', $param);
        } elseif (is_array($param) && !empty($param)) {
            $params = $param;
        } else {
            return false;
        }
        return $params;
    }

    /*
     * mysqli_query 结果处理
     */
    protected function query_handle($obj) {
        $res = array();
        for ($i = 0; $obj->num_rows; $i++) {
            $row = $obj->fetch_assoc();
            array_push($res, $row);
        }

        return $res;
    }

    /*
     * 查询表达式参数处理函数
     */
    public function options_handle($param) {
        if (is_numeric($param)) {
            $option = $param;
        } elseif (is_string($param) && !empty($param) && !is_numeric($param)) {
            $params = explode(',', $param);
            $count = count($params);
            $option = implode(' and ', $params);
        } elseif (is_array($param) && !empty($params)) {
            $params = $param;
            $count = count($params);
            $arr = array();

            foreach ($params as $key => $value) {
                $tip = "$key = $value";
                array_push($arr, $tip);
            }
            $option = implode(' and ', $arr);
        } else {
            return false;
        }
        return $option;
    }

    /*
     * 查询表达式$option处理函数
     */
    protected function option() {
        $options = $this->options;
        $option = '';
        if (isset($options['where'])) {
            $option .= 'where ' . $options['where'] . ' ';
        }
        if (isset($options['order'])) {
            $option .= 'order by ' . $options['order'] . ' ' . $options['order_type'] . ' ';
        }
        if (isset($options['limit'])) {
            $option .= 'limit ' . $options['limit'];
        }
        return $option;
    }

    /*
     * 根据查询表达式查询数据
     */
    public function find() {
        $option = self::option();
        $sql = 'select * from ' . $this->_table . ' ' . $option;
        $db_find = mysqli_query($this->_dbInstance, $sql);
        $msg = self::query_handle($db_find);
        return $msg;
    }

    /*
     * 表达式查询where处理函数
     */
    public function where($where) {
        $this->options['where'] = self::options_handle($where);
        return $this;
    }

    /*
     * 表达式查询limit处理函数
     */
    public function limit($limit) {
        $this->options['limit'] = self::options_handle($limit);
        return $this;
    }

    /*
     * 表达式查询order处理函数
     */
    public function order($order, $type = 'desc') {
        $this->options['order'] = $order;
        $this->options['order_type'] = $type;
        return $this;
    }

    /*
     * 数据处理函数
     */
    public function data($data = array()) {
        $values = array();
        $fields = array();
        if (is_array($data) && isset($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $tip = 1;
                    array_push($values, '(' . implode(',', array_values($value)) . ')');
                    array_push($fields, '(' . implode(',', array_keys($value)) . ')');
                } else {
                    $tip = 0;
                }
            }
        } else {
            return false;
        }

        if (!$tip) {
            array_push($values, '(' . implode(',', array_values($data)) . ')');
            array_push($fields, '(' . implode(',', array_keys($data)) . ')');
        }
        $this->data['fields'] = $fields[0];
        $this->data['values'] = implode(',', $values);
    }

    /*
     * 数据新增
     */
    public function add() {
        $fields = $this->data['fields'];
        $values = $this->data['values'];
        $sql = 'insert into ' . $this->_table . $fields . ' values' . $values;
        $res = mysqli_query($this->_dbInstance, $sql);
        return $res;
    }

    /*
     * 数据更新
     */
    public function save($data = null) {
        $tip = array();
        if (is_array($data) && isset($data)) {
            foreach ($data as $key => $value) {
                array_push($tip, "$key = $value");
            }
        } elseif (is_string($data) && false != strpos($data, '=')) {
            $msg_set = $data;
        } else {
            return false;
        }

        $msg_set = implode(',', $tip);
        $sql = 'update ' . $this->_table . ' set ' . $msg_set . ' where ' . $this->options['where'];
        $res = mysqli_query($this->_dbInstance, $sql);
        return $res;
    }

    /*
     * 数据删除
     */
    public function delete() {
        $sql = 'delete from ' . $this->_table . ' where ' . $this->options['where'];
    }

    /*
     * SQL查询
     */
    public function query($sql) {
        if (is_string($sql)) {
            $db_query = mysqli_query($this->_dbInstance, $sql);
            return $db_query;
        } else {
            return null;
        }
    }

    /*
     * 关闭连接
     */
    public function close() {
        $close = mysqli_close($this->_dbInstance);
        if ($close) {
            return true;
        } else {
            return false;
        }
    }

    function __destruct() {
        mysqli_close($this->_dbInstance);
    }
}
