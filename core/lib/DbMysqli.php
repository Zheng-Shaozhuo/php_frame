<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2017/12/11
 * Time: 16:57
 */
namespace core\lib;

class DbMysqli extends Db
{
    protected static $_dbInstance = null;
    public function __construct($host = null, $user = null, $pass = null, $dbname = null, $port = 3306, $charset = 'uft8', $prefix = '')
    {
        if (!isset(self::$_dbInstance)) {
            parent::__construct($host, $user, $pass, $dbname, $port, $charset, $prefix);

            $_dbConn = $this->getConn();
            if ($_dbConn['flag']) {
                self::$_dbInstance = $_dbConn['conn'];
                if (!mysqli_set_charset(self::$_dbInstance, $this->_charset)) {
                    $this->_errno = mysqli_errno(self::$_dbInstance);
                    $this->_error = mysqli_error(self::$_dbInstance);
                }
            } else {
                $this->_error = $_dbConn['error'];
                $this->_errno = $_dbConn['errno'];
                self::$_dbInstance = null;
            }
        }
    }

    /*
     * 获取链接
     */
    protected function getConn() {
        $conn = mysqli_connect($this->_host, $this->_user, $this->_pass, $this->_dbname);
        if (!$conn) {
            return array('conn' => null, 'flag' => false, 'errno' => mysqli_connect_errno(), 'error' => mysqli_connect_error());
        }
        return array('conn' => $conn, 'flag' => true, 'errno' => -1, 'error' => '');
    }

    /*
     * 选择数据库
     */
    public function database($dbname) {
        if (mysqli_select_db(self::$_dbInstance, $dbname)) {
            $this->_dbname = $dbname;
        }
        return $this;
    }

    /*
     * 数据库用户更换
     */
    public function db_changeUser($user, $pass) {
        if (mysqli_change_user(self::$_dbInstance, $user, $pass, $this->_dbname)) {
            $this->_user = $user;
            $this->_pass = $pass;
        }
        return $this;
    }

    /*
     * 事务开始
     */
    public function startTrans() {
        return mysqli_begin_transaction(self::$_dbInstance);
    }

    /*
     * 事务提交
     */
    public function commitTrans() {
        return mysqli_commit(self::$_dbInstance);
    }

    /*
     * 事务回退
     */
    public function rollbackTrans() {
        return mysqli_rollback(self::$_dbInstance);
    }

    /*
     * SQL查询
     */
    public function query($sql, $type = 'all', $resultmode = MYSQLI_STORE_RESULT) {
        $res = null;
        if (is_string($sql)) {
            $this->_lastquery = $sql;

            $querys = mysqli_query(self::$_dbInstance, $sql, $resultmode);
            if ($querys) {
                if ('all' == $type) {
                    if ($querys instanceof \mysqli_result) {
                        while ($row = $querys->fetch_assoc()) {
                            $res[] = $row;
                        }
                    } else {
                        $res = array();
                    }
                } else if ('single' == $type) {
                    if ($querys instanceof \mysqli_result) {
                        $res = $querys->fetch_assoc();
                    } else {
                        $res = array();
                    }
                } else if ('count' == $type) {
                    $res = $querys->num_rows;
                } else {
                    $res = array();
                }

                $mysqli_result_codes = array('select', 'show', 'describe', 'desc', 'explain');
                $sql_type = strtolower(explode(' ', $sql)[0]);
                if (in_array($sql_type, $mysqli_result_codes)) {
                    mysqli_free_result($querys);
                }
            } else {
                $this->_errno = mysqli_errno(self::$_dbInstance);
                $this->_error = mysqli_error(self::$_dbInstance);
            }

            return $res;
        } else {
            $this->_errno = -1;
            $this->_error = 'query命令不合法, 为 [' . gettype($sql) . '] 类型, 请检查!';
        }
        return $res;
    }

    /*
     * SQL执行
     */
    public function exec($sql) {
        $flag = false;
        if ($stmt = mysqli_prepare(self::$_dbInstance, $sql)) {
            $flag = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        return $flag;
    }

    /*
     * 关闭连接
     */
    public function close() {
        return mysqli_close(self::$_dbInstance);
    }

}
