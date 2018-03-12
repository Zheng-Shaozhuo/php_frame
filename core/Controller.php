<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/1
 * Time: 0:40
 */
namespace core;

class Controller
{
    public $assign = array();
    public function __construct()
    {
        $this->init();
    }

    public function assign($name, $value)
    {
        $this->assign[$name] = $value;
    }

    public function display($file)
    {
        $path = ROOT . MODULE . '/view/' . $file . '.html';
        if (is_file($path))
        {
            extract($this->assign);
            include $path;
        }
    }

    private function init() {
        session_start();
    }

    protected function redirect($url) {
        header('Location:' . HU . '/' . $url);
    }

    protected function post($name = '', $default = null, $filter = '') {
        $_post = $_POST;

        return empty($_post[$name]) ? $default : $_post[$name];
    }

    protected function get($name = '', $default = null, $filter = '') {
        $_get = $_GET;

        return empty($_get[$name]) ? $default : $_get[$name];
    }

    protected function input($key = '', $default = null, $filter = '') {
        if (0 === strpos($key, '?')) {
            $key = substr($key, 1);
            $has = true;
        }

        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            list($method, $key) = explode('.', $key, 2);
            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'route', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
                $key    = $method . '.' . $key;
                $method = 'param';
            }
        } else {
            // 默认为自动判断
            $method = 'param';
        }
    }

    protected function showMsg($url, $msg = '操作成功', $isSuccess = true) {

    }

    protected function goBackMsg($msg) {

    }
}