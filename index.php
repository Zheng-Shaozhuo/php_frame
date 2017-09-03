<?php
/**
 * Created by PhpStorm.
 * User: Zheng
 * Date: 2017/9/1
 * Time: 0:27
 */
define('ROOT', str_replace('\\', '/', __DIR__) . '/');
define('CORE', ROOT . 'core/');
define('COMMON', ROOT . 'common/');
define('MODULE', 'app');

define('DEBUG', true);
if (DEBUG) {
    ini_set('display_errors', 'On');
} else{
    ini_set('display_errors', 'Off');
}

include CORE . 'frame.php';
\core\frame::init();