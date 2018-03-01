<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/1
 * Time: 0:27
 */

define('ROOT', str_replace('\\', DIRECTORY_SEPARATOR, __DIR__) . DIRECTORY_SEPARATOR);
define('CORE', ROOT . 'core' . DIRECTORY_SEPARATOR);
define('COMMON', ROOT . 'common' . DIRECTORY_SEPARATOR);
define('MODULE', 'app');
define('HU', 'http://' . $_SERVER['HTTP_HOST']);
define('ASSETS', HU . DIRECTORY_SEPARATOR . DOMAIN . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view');

define('DEBUG', true);
if (DEBUG) {
    ini_set('display_errors', 'On');
} else{
    ini_set('display_errors', 'Off');
}

include CORE . 'Frame.php';
\core\frame::init();