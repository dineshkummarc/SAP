<?php
return array(
	'pid_file' => '/tmp/sapDaemon.pid',
	'stdout_file' => 'std.out',
	'log_file' => 'daemon.log',
	'worker_count' => 2,
	'queueManager' => array(
		'pollTimeout' => 1000,
	),
	'sockets' => array(
		'queueManager' => 'ipc:///tmp/sap-daemon.ipc',
	)
);
