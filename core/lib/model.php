<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/4
 * Time: 0:27
 */
namespace core\lib;

class model extends \PDO
{
    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'mysql';
        $username = 'root';
        $passwd = 'root';
        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname;

        try
        {
            parent::__construct($dsn, $username, $passwd);
        }
        catch (\PDOException $e)
        {
            throw new \PDOException($e->getMessage());
        }

    }

}