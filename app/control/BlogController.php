<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2018/2/26
 * Time: 17:09
 */

namespace app\control;


use core\lib\DbMysqli;

class BlogController extends BaseController
{
    public function add() {
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $data = $this->getPostData();
            $db = new DbMysqli();
            $res = $db->data($data)->table('blog')->add();
            if ($res) {
                $this->showMsg('添加成功');
            } else {
                $this->showMsg('添加失败', false);
            }
        } else {
            $this->goBackMsg('当前接口不支持该方法');
        }
    }

    public function modify() {
        if ('GET' == $_SERVER['REQUEST_METHOD']) {
            $bid = intval($this->get('bid'));
            $db = new DbMysqli();
            $blog = $db->where(array('bid' => $bid))->find('blog');
            if (!empty($blog)) {
                $this->assign('blog', $blog);
                $this->display('addblog');
            } else {
                $this->redirect('index.html');
            }
        }
    }

    public function msort() {
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $bid = intval($this->post('bid'));
            $sort = intval($this->post('sort'));

            $result = array('flag' => false, 'msg' => '');
            if ($bid > 0 && $sort > 0 && $sort < 256) {
                $db = new DbMysqli();
                $res = $db->table('blog')->where(array('bid' => $bid))->save(array('sort' => $sort));
                if ($res) {
                    $result['flag'] = true;
                    $result['msg'] = '更新成功';
                } else {
                    $result['msg'] = '执行失败, 请检查.';
                }
            }
            echo json_encode($result);
            return ;
        }
    }

    public function update() {
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $bid = intval($this->post('ubid'));
            $data = $this->getPostData();
            unset($data['sort']);
            $where = array('bid' => $bid);

            $db = new DbMysqli();
            $res = $db->table('blog')->where($where)->save($data);
            if ($res) {
                $this->showMsg('更新成功');
            } else {
                $this->showMsg('更新失败', false);
            }
        }
    }

    private function getPostData() {
        $title = $this->post('title');
        $content = $this->post('content');
        $tag = $this->post('tag');
        $alias = $this->post('alias');
        $classify = $this->post('classify');
        $status = $this->post('status');
        $author = $this->post('author');
        $time = $this->post('time');
        $isTop = $this->post('isTop');
        $isComment = $this->post('isComment');

        $data = array(
            'title' => "'" . strip_tags($title) . "'",
            'alias' => "'" . strip_tags($alias) . "'",
            'content' => "'" . htmlspecialchars($content) . "'",
            'tags' => "'" . htmlspecialchars($tag) . "'",
            'author' => "'" . strip_tags($author) . "'",
            'isTop' => is_null($isTop) ? 0 : 1,
            'sort' => 255,
            'status' => intval($status),
            'isComment' => is_null($isComment) ? 0 : 1,
            'cid' => intval($classify),
            'updateAt' => time(),
            'createAt' => strtotime($time) > 0 ? strtotime($time) : time());

        return $data;
    }
}