<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:00 PM
 */
namespace Daemon\Process; use Daemon\Message, ZMQ\Zmsg;

class Worker extends AbstractProcess
{
	/**
	 * @var \ZMQSocket
	 */
	protected $_socketToQueueManager;

	/**
	 * @var string
	 */
	protected $_identity;

	/**
	 * @var \Daemon\Task\AbstractTask|null
	 */
	protected $_currentTask;

	/**
	 * @var \ZMQContext
	 */
	protected $_context;

	protected function _init()
	{
		$this->_identity = 'worker/' . uniqid();
		setproctitle(sprintf('Daemon: %s', $this->_identity));
		$this->_initSocket();
		$this->_registerAtQueueManager();

		register_shutdown_function(array($this, 'shutdown'));
		$this->_processingLoop();
	}

	protected function _initSocket()
	{
		$queueManagerSocket = $this->_config->get('sockets.queueManager');
		if (!is_string($queueManagerSocket)) {
			throw new \InvalidArgumentException('socket definitions have to be from type string');
		}

		$context = new \ZMQContext();
		$socket = $context->getSocket(\ZMQ::SOCKET_DEALER);
		$socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, $this->_identity);
		$socket->connect($queueManagerSocket);

		$this->_socketToQueueManager = $socket;
		$this->_context = $context;
	}

	protected function _registerAtQueueManager()
	{
		$zmsg = new Zmsg($this->_socketToQueueManager);
		$msg = new Message\Worker\Register(array(
			'workerAddress' => $this->_identity,
		));

		$zmsg->body_set(serialize($msg));
		$zmsg->send();
	}

	protected function _initShutdown()
	{
		exit;
	}

	protected function _processingLoop()
	{
		$poll = new \ZMQPoll();
		$poll->add($this->_socketToQueueManager, \ZMQ::POLL_IN);
		$pollTimeout = $this->_config->get('worker.pollTimeout');

		while (true) {
			$readable = $writable = array();
			$events = $poll->poll($readable, $writable, $pollTimeout);

			if ($events) {
				foreach ($readable as $socket) {
					$zmsg = new Zmsg($socket);
					$zmsg->recv();

					$msg = unserialize($zmsg->body());
					if ($msg instanceof \Daemon\Message\Shutdown) {
						$this->log('received shutdown message');
						$this->_initShutdown();
						return;
					}

					try {
						/** @var $task \Daemon\Task\AbstractTask */
						$task = $msg->task;
						$this->_currentTask = $task;

						$this->log('starting to execute %s', get_class($task));
						$task->setContext($this->_context);
						$task->setConfig($this->_config);
						$task->setProcess($this);
						$task->run();

						$this->_currentTask = null;
						$this->log('execution of %s finished', get_class($task));

						$response = new Message\Task\Finished(array(
							'task' => $task,
							'workerAddress' => $this->_identity,
						));

						$this->_responseToQueueManager($zmsg, $response);

						if ($task->hasMessagesToQueueManager()) {
							$responseMessages = $task->getMessagesToQueueManager();
							foreach ($responseMessages as $responseMessage) {
								$this->_responseToQueueManager($zmsg, $responseMessage);
							}
						}
					} catch (\Exception $e) {
						$response = new Message\Task\Failed(array(
							'task' => $task,
							'exception' => $e,
							'workerAddress' => $this->_identity,
						));

						$this->_responseToQueueManager($zmsg, $response);
					}
				}
			}

			$this->_sendHeartbeat();
		}
	}

	protected function _sendHeartbeat()
	{
		$zmsg = new Zmsg($this->_socketToQueueManager);
		$message = new \Daemon\Message\Worker\Heartbeat(array(
			'workerAddress' => $this->_identity,
		));

		$this->_responseToQueueManager($zmsg, $message);
	}

	protected function _responseToQueueManager(Zmsg $zmsg, Message\AbstractMessage $msg)
	{
		$zmsg->body_set(serialize($msg))->send();
	}

	public function shutdown()
	{
		$zmsg = new Zmsg($this->_socketToQueueManager);

		if (null !== $this->_currentTask) {
			$msg = new Message\Worker\Shutdown\Unexpected(array(
				'task' => $this->_currentTask,
				'workerAddress' => $this->_identity,
			));
		} else {
			$msg = new Message\Worker\Shutdown\Regular(array(
				'workerAddress' => $this->_identity,
			));
		}

		$this->_responseToQueueManager($zmsg, $msg);
	}
}
