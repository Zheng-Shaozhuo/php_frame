<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/3
 * Time: 23:30
 */
namespace app\control;

class IndexController extends BaseController
{
    public function index()
    {
        $this->display('blist');
    }

    public function addblog() {
        $this->display('addblog');
    }

    public function classify() {
        $this->display('classify');
    }

    public function tags() {
        $this->display('tags');
    }
}