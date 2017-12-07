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

define('DEBUG', true);
if (DEBUG) {
    ini_set('display_errors', 'On');
} else{
    ini_set('display_errors', 'Off');
}

include CORE . 'frame.php';
\core\frame::init();