<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 3:45 PM
 */

ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('display_errors', 1);

if (!function_exists('setproctitle')) {
	function setproctitle($title) {}
}

function e($v) { echo $v; }
function el($v) { e($v . PHP_EOL); }

defined('APPLICATION_PATH') ||
	define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PATH . '/../library'),
)));

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace(array(
	'Daemon',
	'ZMQ',
	'SAP',
));
$autoloader->suppressNotFoundWarnings(true);

$config = require 'config.php';
$config = new \Daemon\Config($config);

$pidFile = $config->get('pid_file');
if (file_exists($pidFile) && '' != exec('ps -p `cat ' . $pidFile . '` --no-heading')) {
	trigger_error('Process running with PID ' . file_get_contents($pidFile), E_USER_NOTICE);
	exit(0);
}

file_put_contents($pidFile, getmypid());

new \Daemon\Process\ForkMaster($config);
