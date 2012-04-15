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
	'Requests'
));
$autoloader->suppressNotFoundWarnings(true);

$config = require 'config.php';
$config = new \Daemon\Config($config);

new \Daemon\Process\ForkMaster($config);

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_DEALER);
$socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, uniqid());
$socket->connect($config->get('sockets.queueManager'));

$message = new \Daemon\Message\Task\Add(array(
	'task' => new \SAP\Daemon\Task\SCv1\InitialStart(),
));

$zmsg = new \ZMQ\Zmsg($socket);
$zmsg->body_set(serialize($message));
$zmsg->send();
