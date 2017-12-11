<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2017/12/4
 * Time: 20:29
 */
namespace core\lib;

use core\common\Config;
use core\common\Functions;

abstract class Db
{
    protected $_charset;
    protected $_dsn;
    protected $_host;
    protected $_user;
    protected $_pass;
    protected $_port;
    protected $_dbname;
    protected $_table;
    protected $_fields;
    protected $_tableprefix;
    protected $_lastquery;
    protected $_error;
    protected $_errno;

    protected static $_dbInstance = null;
    protected $_datas       = array();  // 数据信息
    protected $_options     = array();  // 查询表达式参数
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
    public function __construct($host = null, $user = null, $pass = null, $dbname = null, $port = 3306, $charset = 'uft8', $prefix = '') {
        if (!isset(self::$_dbInstance)) {
            if (!isset($host) || !isset($user) || !isset($pass) || !isset($dbname)) {
                $config = Config::RC('db', true);
                $this->_charset     = $config['charset'];
                $this->_host        = $config['hostname'];
                $this->_user        = $config['username'];
                $this->_pass        = $config['password'];
                $this->_dbname      = $config['database'];
                $this->_port        = $config['hostport'];
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
        }
    }

    /*
     * 获取数据库链接
     */
    protected function getConn() {
        /* 子类实现 */
    }

    /*
     * 获取当前数据表名
     */
    public function getTable() {
        return $this->_table;
    }

    /*
     * 选择当前表
     */
    public function table($table) {
        $this->_table = $table;
    }

    /*
     * 获取当前数据库名
     */
    public function getDatabase() {
        return $this->_dbname;
    }

    /*
     * 选择数据库
     */
    public function database($dbname) {
        /* 子类实现 */
    }

    /*
     * 获取查询字段
     */
    public function getField() {
        return isset($this->_fields) ? $this->_fields : '*';
    }

    /*
     * 设置查询字段
     */
    public function field($fields = '*') {
        $this->_fields = $fields;
    }
    
    /*
     * 返回错误信息
     */
    public function error() {
        return $this->_error;
    }

    /*
     * 返回错误编号
     */
    public function errno() {
        return $this->_errno;
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
        $this->_datas[$name] = $value;
    }

    /*
     * 获取数据对象值
     */
    public function __get($name) {
        return isset($this->_datas[$name]) ? $this->_datas[$name] : null;
    }

    /*
     * 检测数据对象值
     */
    public function __isset($name) {
        return isset($this->_datas[$name]);
    }

    /*
     * 销毁数据对象值
     */
    public function __unset($name) {
        unset($this->_datas[$name]);
    }

    /*
     * 解决类中不存在方法，待实现
     */
    public function __call($name, $arguments) {
        // TODO: Implement __call() method.
    }

    /*
     * 数据库用户更换
     */
    public function db_changeUser($user, $pass) {
        /* 子类实现 */
    }

    /*
     * 获取数据库中所有的表名
     */
    public function db_getTables() {
        $sql = 'show tables';
        $rows = $this->query($sql);
        return $rows;
    }

    /*
     * 获取指定表中所有信息
     */
    public function db_getTableDesc($table) {
        $sql = "desc $table";
        $rows = $this->query($sql);
        return $rows;
    }

    /*
     * 获取指定表的字段详情
     */
    public function db_selectTableFields($table) {
        $res = $this->db_getTableDesc($table);
        $fields = null;
        if (isset($res) && is_array($res)) {
            $fields = implode(',', Functions::vg_array_column($res, 'Field'));
        }
        return $fields;
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
        $options = $this->_options;
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
     * $type all、single、count
     */
    public function query($sql) {
        /* 子类实现 */
        return null;
    }

    /*
     * SQL执行
     */
    public function exec($sql) {
        /* 子类实现 */
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
//        mysqli_close($this->_dbInstance);
    }
}
