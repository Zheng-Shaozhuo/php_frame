<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2017/12/11
 * Time: 16:57
 */
namespace core\lib;

class DbPDO extends Db
{
    protected static $_dbInstance = null;

    public function __construct($host = null, $user = null, $pass = null, $dbname = null, $port = 3306, $charset = 'uft8', $prefix = '')
    {
        parent::__construct($host, $user, $pass, $dbname, $port, $charset, $prefix);
        if (is_null(self::$_dbInstance)) {
            $_dbConn = $this->getConnParams($this->_host, $this->_dbname, $this->_charset, $this->_user, $this->_pass);
            if ($_dbConn['flag']) {
                self::$_dbInstance = $_dbConn['conn'];
            } else {
                $this->_error = $_dbConn['error'];
                $this->_errno = $_dbConn['errno'];
                self::$_dbInstance = null;
            }
        } else {
            if (self::$_dbInstance->inTransaction()) {
                $_dbConn = $this->getConnParams($this->_host, $this->_dbname, $this->_charset, $this->_user, $this->_pass);
                if ($_dbConn['flag']) {
                    self::$_dbInstance = $_dbConn['conn'];
                } else {
                    $this->_error = $_dbConn['error'];
                    $this->_errno = $_dbConn['errno'];
                    self::$_dbInstance = null;
                }
            }
        }
    }

    /*
     * 获取链接
     */
    protected function getConnParams($_host, $_dbname, $_charset, $_user, $_pass) {
        $dsn = 'mysql:host=' . $_host . ';dbname=' . $_dbname . ';charset=' . $_charset;
        try {
            $conn = new \PDO($dsn, $_user, $_pass);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            return array('conn' => null, 'flag' => false, 'errno' => $e->getCode(), 'error' => $e->getMessage());
        }

        return array('conn' => $conn, 'flag' => true, 'errno' => -1, 'error' => '');
    }

    /*
     * 选择数据库
     */
    public function database($dbname) {
        $_dbConn = $this->getConnParams($this->_host, $dbname, $this->_charset, $this->_user, $this->_pass);
        if ($_dbConn['flag']) {
            self::$_dbInstance = $_dbConn['conn'];
            $this->_dbname = $dbname;
        }

        return $this;
    }

    /*
     * 数据库用户更换
     */
    public function db_changeUser($user, $pass) {
        $_dbConn = $this->getConnParams($this->_host, $this->_dbname, $this->_charset, $user, $pass);
        if ($_dbConn['flag']) {
            self::$_dbInstance = $_dbConn['conn'];
            $this->_user = $user;
            $this->_pass = $pass;
        }
        return $this;
    }

    /*
     * 事务开始
     */
    public function startTrans() {
        return self::$_dbInstance->beginTransaction();
    }

    /*
     * 事务提交
     */
    public function commitTrans() {
        return self::$_dbInstance->commit();
    }

    /*
     * 事务回退
     */
    public function rollbackTrans() {
        return self::$_dbInstance->rollback();
    }

    /*
     * SQL查询
     */
    public function query($sql, $type = 'all', $resultmode = MYSQLI_STORE_RESULT) {
        $res = null;
        if (is_string($sql)) {
            $this->_lastquery = $sql;
            try {
                $stmt = self::$_dbInstance->prepare($sql);
                $stmt->execute();

                if ($stmt) {
                    if ('all' == $type) {
                        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    } else if ('single' == $type) {
                        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
                    } else if ('count' == $type) {
                        $res = $stmt->rowCount();
                    } else {
                        $res = array();
                    }

                    $stmt = null;
                } else {
                    $res = array();
                }
            } catch (\PDOException $e) {
                $this->_errno = $e->getCode();
                $this->_error = $e->getMessage();
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
        try {
            $stmt = self::$_dbInstance->prepare($sql);
            $stmt->execute();
            $res = $stmt->rowCount() > 0 ? true : false;
            $stmt = null;
            return $res;
        } catch (\PDOException $e) {
            $this->_errno = $e->getCode();
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /*
     * 关闭连接
     */
    public function close() {
        self::$_dbInstance = null;
    }

}
