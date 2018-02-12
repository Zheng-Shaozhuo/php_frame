<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/1
 * Time: 1:10
 */
namespace core\lib;

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
        } else {
            $this->control = 'index';
            $this->action = 'index';
        }
    }
}