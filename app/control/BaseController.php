<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2018/2/8
 * Time: 15:58
 */
namespace app\control;
use core\Controller;

class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (isset($_SESSION['usr']) && $_SESSION['usr_login_time'] + 3600 * 24 >= time()) {

        } else {
            $this->redirect("index.php/Privilege/login");
        }
    }
}