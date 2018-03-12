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
                $config = Config::RC('db', false);
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
        return $this->getFullTable($this->_table);
    }

    /*
     * 选择当前表
     */
    public function table($table) {
        $this->_table = $table;
        return $this;
    }

    /*
     * 获取完整表名
     */
    private function getFullTable($table) {
        return isset($this->_tableprefix) ? $this->_tableprefix . '_' . $table : $table;
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
        if (empty($this->_datas['join'])) {
            return isset($this->_fields) ? $this->_fields : '*';
        } else {
            $fields = '*';
            if (isset($this->_fields)) {
                $params = explode(',', $this->_fields);
                $fields = implode(',', array_map(function($v) {
                    $tps = explode('.', trim($v));
                    if (2 == count($tps)) {
                        return 'vg_' . $this->getFullTable($tps[0]) . '.' . $tps[1];
                    }
                    return $v;
                }, $params));
            }
            return $fields;
        }
    }

    /*
     * 设置查询字段
     */
    public function field($fields = '*') {
        $this->_fields = $fields;
        return $this;
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
        return $this->query($sql);
    }

    /*
     * 获取指定表中所有信息
     */
    public function db_getTableDesc($table) {
        $sql = "desc $table";
        return $this->query($sql);
    }

    /*
     * 获取指定表的字段详情
     */
    public function db_selectTableFields($table) {
        $res = $this->db_getTableDesc($this->getFullTable($table));
        $fields = null;
        if (isset($res) && is_array($res)) {
            $fields = implode(',', Functions::vg_array_column($res, 'Field'));
        }
        return $fields;
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
        if (empty($this->_datas['join'])) {
            $this->_datas['join'] = '';
        }
        return $option;
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
     * 表达式查询where处理函数
     */
    public function where($where) {
        $this->_options['where'] = self::options_handle($where);
        return $this;
    }

    /*
     * 表达式查询limit处理函数
     */
    public function limit($limit) {
        $this->_options['limit'] = self::options_handle($limit);
        return $this;
    }

    /*
     * 表达式查询order处理函数
     */
    public function order($order, $type = 'desc') {
        $this->_options['order'] = $order;
        $this->_options['order_type'] = $type;
        return $this;
    }

    /*
     * 表达式查询order处理函数
     */
    public function join($table, $on = null, $type = 'left') {
        $join_table = $this->getFullTable($table);
        $alias_join_table = 'vg_' . $join_table;
        $master_table = $this->getFullTable($this->_table);
        $alias_master_table = 'vg_' . $master_table;
        if (is_array($on)) {
            $arrOn = array();
            foreach ($on as $k => $v) {
                array_push($arrOn, $alias_master_table . ".$k = $alias_join_table.$v");
            }
            $ons = implode(' and ', $arrOn);
        } elseif (is_string($on)) {
            if (2 == substr_count($on, 'vg_')) {
                $ons = $on;
            } elseif (2 == count($params = explode('=', $on))) {
                $ons = 'vg_' . $this->getFullTable(trim($params[0])) . ' = ' . 'vg_' . $this->getFullTable(trim($params[1]));
            } else {
                $ons = '1 = 1';
            }
        } else {
            $ons = '1 = 1';
        }
        $joins = "$type join $join_table $alias_join_table on $ons";
        $this->_datas['join'] = isset($this->_datas['join']) ? $this->_datas['join'] . " $joins" : $alias_master_table . " $joins";
        return $this;
    }

    /*
     * 查询表达式参数处理函数
     */
    public function options_handle($param) {
        if (is_numeric($param)) {
            $option = $param;
        } elseif (is_string($param) && !empty($param) && !is_numeric($param)) {
            $params = explode(',', $param);
            $option = implode(' and ', $params);
        } elseif (is_array($param) && !empty($param)) {
            $arr = array();

            foreach ($param as $key => $value) {
                $tip = "$key = '$value'";
                array_push($arr, $tip);
            }
            $option = implode(' and ', $arr);
        } else {
            return false;
        }
        return $option;
    }

    /*
     * 根据查询表达式查询数据
     */
    public function find($table = null) {
        self::limit(1);
        return self::select($table)[0];
    }

    /*
     * 根据查询表达式查询数据
     */
    public function select($table = null) {
        if (isset($table)) {
            $this->_table = $table;
        }

        $option = self::option();
        $sql = 'select ' . $this->getField() .' from ' . $this->getTable() . ' ' . $this->_datas['join'] . ' ' . $option;
        return $this->query($sql);
    }

    /*
     * 数据处理函数
     */
    public function data($data = array()) {
        $values = array();
        $fields = array();
        $tip = 0;
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
        $this->_datas['fields'] = $fields[0];
        $this->_datas['values'] = implode(',', $values);
        return $this;
    }

    /*
     * 数据新增
     */
    public function add() {
        $fields = $this->_datas['fields'];
        $values = $this->_datas['values'];
        $sql = 'insert into ' . $this->getTable() . $fields . ' values' . $values;
        return $this->query($sql);
    }

    /*
     * 数据更新
     */
    public function save($data = null) {
        $tip = array();
        $updates = '';
        if (is_array($data) && isset($data)) {
            foreach ($data as $key => $value) {
                array_push($tip, "$key = $value");
            }
            $updates = implode(',', $tip);
        } elseif (is_string($data) && false != strpos($data, '=')) {
            $updates = $data;
        } else {
            return false;
        }

        $sql = 'update ' . $this->getTable() . ' set ' . $updates . ' where ' . $this->_options['where'];
        return $this->exec($sql);
    }

    /*
     * 数据删除
     */
    public function delete() {
        $sql = 'delete from ' . $this->getTable() . ' where ' . $this->_options['where'];
        return $this->exec($sql);
    }

    /*
     * 事务开始
     */
    public function startTrans() {
        /* 子类实现 */
    }

    /*
     * 事务提交
     */
    public function commitTrans() {
        /* 子类实现 */
    }

    /*
     * 事务回退
     */
    public function rollbackTrans() {
        /* 子类实现 */
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
        return null;
    }

    /*
     * 关闭连接
     */
    public function close() {
        /* 子类实现 */
    }

    function __destruct() {
//        mysqli_close($this->_dbInstance);
    }
}
