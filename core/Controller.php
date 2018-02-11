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
}