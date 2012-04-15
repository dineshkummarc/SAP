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

if ($argc < 2 || !in_array($argv[1], array('start', 'stop', 'download'))) {
	el(sprintf('usage: %s start|stop|download', $argv[0]));
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

$transcoderConfig = array(
	'PlaylistFile' => '/home/trollpanel/lol.lst',
	'ServerIP' => '127.0.0.1',
	'ServerPort' => 8000,
	'Password' => 'lulz',
	'StreamTitle' => 'My Gay Son',
	'StreamURL' => 'http://mygayson.com',
	'Genre' => 'allmighty foolord',
	'LogFile' => 'sc_trans.log',
	'Shuffle' => 1,
	'Bitrate' => 128000,
	'SampleRate' => 44100,
	'Channels' => 2,
	'Quality' => 1,
	'CrossfadeLength' => 8000,
	'UseID3' => 0,
	'Public' => 1,
	'AIM' => null,
	'ICQ' => null,
	'IRC' => null,
);

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

$messages = array();

if ($mode === 'start') {
	$messages[] = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\SCv1\Transcoder\Start(array(
			'transcoder_config' => $transcoderConfig,
			'transcoder_identifier' => 'sc_trans-1',
		)),
	));
	$messages[] = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\SCv1\Server\Start(array(
			'server_config' => $serverConfig,
			'server_identifier' => 'sc_serv-1',
		)),
	));
} elseif ($mode === 'stop') {
	$messages[] = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\SCv1\Transcoder\Stop(array(
			'transcoder_identifier' => 'sc_trans-1',
		)),
	));
	$messages[] = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\SCv1\Server\Stop(array(
			'server_identifier' => 'sc_serv-1',
		)),
	));
} elseif ($mode === 'download') {
	$messages[] = new \Daemon\Message\Task\Add(array(
		'task' => new \SAP\Daemon\Task\Download\Start(array(
			'download_bundle_id' => rand(4, 20),
			'download_list' => array(
				1 => 'ftp://nc23.de/lol.mp3',
				2 => 'http://ns2.n2305.com/Episode%2001_%20Compiled%20by%20Datassette.mp3',
				3 => 'http://ns2.n2305.com/Episode%2003_%20Compiled%20by%20Datassette.mp3',
				4 => 'http://ns2.n2305.com/Episode%2004_%20Compiled%20by%20Com%20Truise.mp3',
				5 => 'http://ns2.n2305.com/Episode%2005_%20Compiled%20by%20Abe%20Mangger.mp3',
			)
		)),
	));
}

$zmsg = new \ZMQ\Zmsg($socket);
foreach ($messages as $message) {
	$zmsg->body_set(serialize($message));
	$zmsg->send();

	$zmsg->recv();
	$response = unserialize($zmsg->body());
	var_dump($response);
}
