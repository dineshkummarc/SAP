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

if ($argc < 2 || !in_array($argv[1], array('start', 'stop'))) {
	el(sprintf('usage: %s start|stop', $argv[0]));
	exit(1);
} else {
	$mode = $argv[1];
}

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

$serverConfig = array(
	'MaxUser' => 10,
	'Password' => 'lulz',
	'PortBase' => 8000,
	'LogFile' => 'none',
	'RealTime' => 0,
	'ScreenLog' => 0,
	'ShowLastSongs' => 10,
);

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_DEALER);
$socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, uniqid());
$socket->connect($config->get('sockets.queueManager'));

if ($mode === 'start') {
	$msg = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\SCv1\Start(array(
			'server_config' => $serverConfig,
			'server_identifier' => 1,
		)),
	));
} elseif ($mode === 'stop') {
	$msg = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\SCv1\Stop(array(
			'server_identifier' => 1,
		)),
	));
}


$zmsg = new \ZMQ\Zmsg($socket);
$zmsg->body_set(serialize($msg));
$zmsg->send();

$zmsg->recv();
$response = unserialize($zmsg->body());
var_dump($response);
