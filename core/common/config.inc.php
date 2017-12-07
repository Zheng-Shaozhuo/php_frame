<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2017/12/4
 * Time: 19:59
 */
namespace core\common;

class config{
    private function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /*
     * 读取配置文件
     * $name 配置文件名, 默认格式为 .config.php
     * $isModule 是否需要读取模块配置文件
     */
    public static function RC($name, $isModule = false) {
        if ($isModule) {
            $path = MODULE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . "$name.config.php";
            if (is_file($path)) {
                return include_once $path;
            }
        }

        $path = ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . "$name.config.php";
        if (is_file($path)) {
            return include_once $path;
        }
        else {
            exit("$name.config.php file not found, Please check your input, Thanks.");
        }
    }
}