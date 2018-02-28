<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/1
 * Time: 1:10
 */
namespace core\lib;

use core\common\Config;

class route
{
    public $control;
    public $action;
    public function __construct()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos(strtolower($uri), 'index.php') > 0) {
            $uri = trim(str_replace('/index.php', null, $uri), '/');
            $params = explode('/', $uri);

            if (is_null($uri) || '' == $uri)
            {
                $this->control = 'index';
                $this->action = 'index';
            }
            else if (isset($params[0]))
            {
                $this->control = $params[0];
                unset($params[0]);
                if (isset($params[1]))
                {
                    $this->action = $params[1];
                    unset($params[1]);
                }
                else
                {
                    $this->action = 'index';
                }

                $count = count($params);
                $i = 2;
                while ($i < $count)
                {
                    if (isset($params[$i + 1]))
                    {
                        $_GET[$params[$i++]] = $params[$i++];
                    }
                    else
                    {
                        break;
                    }
                }
            }
        } elseif ('/index.php' . $_SERVER['REQUEST_URI'] == $_SERVER['PHP_SELF']) {
            $routes = Config::RC('route', true);
            $target = $_SERVER['REQUEST_URI'];
            $res = $this->getRewriteParam($routes, $target);
            if ($res['state']) {
                $this->control = $res['control'];
                $this->action = $res['action'];
            }
        } else {
            $this->control = 'index';
            $this->action = 'index';
        }
    }

    private function getRewriteParam($routes, $target) {
        $state = true;
        $control = null;
        $action = null;
        if (empty($routes) || empty($target)) {
            return array('state' => $state, 'control' => $control, 'action' => $action);
        }

        $target = String::getRewriteValue($target);
        foreach ($routes as $k => $v) {
            if ($target == strtolower($k)) {
                foreach ($v as $tk => $tv) {
                    if (0 == $tk) {
                        $params = explode('/', $tv);
                        if (2 == count($params)) {
                            $control = $params[0];
                            $action = $params[1];
                        } else {
                            $state = false;
                        }
                    } else {
                        if (1 == count($tv)) {
                            $key = array_keys($tv)[0];
                            $value = array_values($tv)[0];
                            switch (strtolower($key)) {
                                case 'method':
                                    if ($_SERVER['REQUEST_METHOD'] != strtoupper($value)) {
                                        $state = false;
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    }

                    if (!$state) {
                        $control = null;
                        $action = null;
                        break;
                    }
                }
            } else {
                continue;
            }
        }
        return array('state' => $state, 'control' => $control, 'action' => $action);
    }
}