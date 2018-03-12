<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2018/2/28
 * Time: 14:00
 */
return array(
    'login' => array('privilege/login'),
    'loginout' => array('index/loginout'),
    'index' => array('index/index'),
    'classify' => array('index/classify'),
    'tags' => array('index/tags'),
    'addblog' => array('index/addblog', array('method' => 'get')),
    'msort' => array('blog/msort'),

    'add_blog' => array('blog/add', array('method' => 'post')),
    'modifyblog' => array('blog/modify', array('method' => 'get')),
    'update_blog' => array('blog/update', array('method' => 'post')),
);