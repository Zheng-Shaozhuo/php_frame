<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/3
 * Time: 23:30
 */
namespace app\control;

class indexController extends \core\frame
{
    public function index()
    {
        var_dump(__DIR__);
        echo 'index';
    }

    public function admin()
    {
        var_dump(__DIR__);
        echo 'admin';

        var_dump($_GET);
    }

    public function model()
    {
        $m = new \core\lib\model();
        $o = $m->query("select * from user");
        var_dump($o->fetchAll());
    }

    public function view()
    {
        $data = 'Hello ZhengShaozhuo';
        $this->assign('data', $data);
        $this->display('index/index');
    }
}