<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 3:45 PM
 */

ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'foo.err');

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
	'Requests'
));
$autoloader->suppressNotFoundWarnings(true);

$config = require 'config.php';
$config = new \Daemon\Config($config);

$downloadBundleId = $argv[1];

new \SAP\Daemon\Process\Downloader($config, $downloadBundleId);
