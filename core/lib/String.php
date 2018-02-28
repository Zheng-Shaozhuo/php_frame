<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2018/2/9
 * Time: 16:58
 */
namespace core\lib;

class String
{
    function __construct(){
    }

    public static function randString($type = 1, $len = 4) {
        $str = "";
        switch ($type) {
            case 1:
                $str = join('', range(0, 9));
                break;
            case 2:
                $str = join('', array_merge(range('a', 'z'), range('A', 'Z')));
                break;
            case 3:
                $str = join('', array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9)));
                break;
            default:
                break;
        }

        $str = $len > strlen($str) ? str_repeat($str, ceil($len / strlen($str))) : $str;
        return substr(str_shuffle($str), 0, $len);
    }

    public static function getUniString() {
        return md5(uniqid(microtime(true), true));
    }

    public static function getFileExtension($filename) {
        $params = explode('.', $filename);
        if (2 == count($params)) {
            return strtolower(end($params));
        }
        return null;
    }

    public static function getFileNameRemoveExtension($filename) {
        $params = explode('.', $filename);
        if (2 == count($params)) {
            return strtolower(reset($params));
        }
        return null;
    }

    public static function getRewriteValue($param) {
        $params = explode('.', $param);
        if (2 == count($params)) {
            return substr(strtolower(reset($params)), 1);
        }
        return null;
    }
}