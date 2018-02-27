<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2018/2/8
 * Time: 16:10
 */
namespace app\control;

use core\Controller;
use core\lib\DbMysqli;

class PrivilegeController extends Controller
{
    public function index() {

    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usr = $_POST['usrname'];
            $pass = $_POST['usrpass'];
            $is_remind = empty($_POST['remind']) ? null : $_POST['remind'];

            $db = new DbMysqli();
            $res = $db->where(array('user' => $usr, 'pass' => md5(md5($pass))))->find('admin');
            if (1 == count($res)) {
                $_SESSION['usr'] = $usr;
                $_SESSION['usr_login_time'] = time();

                $this->redirect("index.php/Index/index");
            } else {
                $this->display('login');
            }
        } else {
            $this->display('login');
        }
    }
}