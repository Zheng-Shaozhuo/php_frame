<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/3
 * Time: 23:30
 */
namespace app\control;

use core\lib\DbMysqli;

class IndexController extends BaseController
{
    public function index()
    {
        $db = new DbMysqli();
        $blog_list = $db->field('blog.bid, title, alias, tags, author, isTop, sort, status, isComment, blog.createAt, classify.name classifyname')->table('blog')
            ->join('blog2classify', array('bid' => 'bid'))
            ->join('classify', 'blog2classify.cid = classify.cid')
            ->order('bid', 'desc')->select();
        $this->assign('blog_list', $blog_list);
        $this->assign('blogStatus', array(11 => '公开', 22 => '草稿', 88 => '审核', 99 => '禁止'));
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

    public function loginout() {
        session_destroy();
        $this->redirect("index.html");
    }
}