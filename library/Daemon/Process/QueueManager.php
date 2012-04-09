<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 12:22 PM
 */
namespace Daemon\Process; use Daemon\MessageHandler, Daemon\Task, Daemon\Message, ZMQ\Zmsg;

class QueueManager extends AbstractProcess
{
	/**
	 * @var \Daemon\MessageHandler\Factory
	 */
	protected $_messageHandlerFactory;

	/**
	 * @var array
	 */
	protected $_registeredWorkers = array();

	/**
	 * @var array
	 */
	protected $_availableWorkers = array();

	/**
	 * @var \Daemon\Task\AbstractTask[]
	 */
	protected $_newTasks = array();

	/**
	 * @var \Daemon\Task\AbstractTask[]
	 */
	protected $_inWorkTasks = array();

	/**
	 * @var \ZMQSocket
	 */
	protected $_socket;

	/**
	 * @var bool
	 */
	protected $_shutdownRequested = false;

	protected function _init()
	{
		setproctitle('Daemon: QueueManager');
		$this->_initSockets();
		$this->_initFactories();

		$this->_processingLoop();
		$this->_shutdownProcessingLoop();
		exit;
	}

	protected function _initSockets()
	{
		$bindRouterSocketTo = $this->_config->get('sockets.queueManager');

		$context = new \ZMQContext();
		$routerSocket = $context->getSocket(\ZMQ::SOCKET_ROUTER);
		$routerSocket->bind($bindRouterSocketTo);

		$this->_socket = $routerSocket;
	}

	protected function _initFactories()
	{
		$this->_messageHandlerFactory = new MessageHandler\Factory($this->_config, $this);
	}

	protected function _processingLoop()
	{
		$readable = $writable = array();
		$pollTimeout = $this->_config->get('queueManager.pollTimeout');
		if (!is_numeric($pollTimeout)) {
			throw new \InvalidArgumentException('pollTimeout has to be numeric');
		}

		while (true) {
			$poll = $this->_generatePoll();
			$events = $poll->poll($readable, $writable, $pollTimeout);

			if ($events) {
				foreach ($readable as $socket) {
					$zmsg = new Zmsg($socket);
					$zmsg->recv();

					try {
						$msgHandler = $this->_messageHandlerFactory->getInstance($zmsg);
						$msgHandler->handle();
					} catch (\Exception $e) {
						$this->log($e->getMessage());
					}
				}

				$this->_distributeTasks();

				if ($this->_shutdownRequested) {
					return;
				}
			}
		}
	}

	protected function _shutdownProcessingLoop()
	{
		while (count($this->_registeredWorkers) > 0) {
			$this->_sendShutdownMessageToAllAvailableWorkers();
			$poll = $this->_generatePoll();

			$readable = $writable = array();
			$events = $poll->poll($readable, $writable);
			if ($events) {
				foreach ($readable as $socket) {
					$zmsg = new Zmsg($socket);
					$zmsg->recv();
					$msg = unserialize($zmsg->body());

					$allowedMessages = array(
						'Daemon\Message\Worker\Shutdown\Regular',
						'Daemon\Message\Worker\Shutdown\Unexpected',
						'Daemon\Message\Task\Finished',
						'Daemon\Message\Task\Failed',
					);

					$msgClass = get_class($msg);
					if (!in_array($msgClass, $allowedMessages)) {
						$this->_sendNotAvailableWhileShutdownMessage($zmsg);
						$this->log('received message from type %s while shutdown is running', $msgClass);
						continue;
					}

					$msgHandler = $this->_messageHandlerFactory->getInstance($zmsg);
					$msgHandler->handle();

					$this->log('still %d workers registered', count($this->_registeredWorkers));
				}
			}
		}
	}

	protected function _sendShutdownMessageToAllAvailableWorkers()
	{
		static $alreadySentMessageTo = array();

		foreach ($this->_availableWorkers as $availableWorker) {
			if (in_array($availableWorker, $alreadySentMessageTo)) {
				continue;
			}

			$zmsg = new Zmsg($this->_socket);
			$msg = new Message\Shutdown();
			$zmsg->body_set(serialize($msg));
			$zmsg->wrap($availableWorker);
			$zmsg->send();

			$this->log('sent shutdown message to worker with address %s', $availableWorker);
			$alreadySentMessageTo[] = $availableWorker;
		}
	}

	/**
	 * @param \ZMQ\Zmsg $zmsg
	 */
	protected function _sendNotAvailableWhileShutdownMessage(Zmsg $zmsg)
	{
		$msg = new Message\NotAvailable(array(
			'reason' => 'shutdown',
		));

		$zmsg->body_set(serialize($msg));
		$zmsg->send();
	}

	/**
	 * @return \ZMQPoll
	 */
	protected function _generatePoll()
	{
		$poll = new \ZMQPoll();
		$poll->add($this->_socket, \ZMQ::POLL_IN);
		return $poll;
	}

	protected function _distributeTasks()
	{
		while (!empty($this->_availableWorkers) && !empty($this->_newTasks)) {
			$workerAddress = array_shift($this->_availableWorkers);
			$task = array_shift($this->_newTasks);

			$zmsg = new Zmsg($this->_socket);
			$msg = new Message\Task\Execute(array(
				'task' => $task,
			));

			$zmsg->body_set(serialize($msg));
			$zmsg->wrap($workerAddress);
			$zmsg->send();

			$this->_inWorkTasks[$task->getIdentifier()] = $task;
		}
	}

	/**
	 * @param string $workerAddress
	 */
	public function addWorker($workerAddress)
	{
		$this->_availableWorkers[] = $workerAddress;
		$this->_registeredWorkers[] = $workerAddress;
		$this->log('registered worker with address %s', $workerAddress);
	}

	/**
	 * @param string $workerAddress
	 */
	public function removeWorker($workerAddress)
	{
		$filterFnc = function($value) use ($workerAddress)
		{
			return $value !== $workerAddress;
		};

		$this->_availableWorkers = array_filter($this->_availableWorkers, $filterFnc);
		$this->_registeredWorkers = array_filter($this->_registeredWorkers, $filterFnc);
	}

	/**
	 * @param string $workerAddress
	 * @throws \InvalidArgumentException
	 */
	public function flagWorkerAsAvailable($workerAddress)
	{
		if (!in_array($workerAddress, $this->_registeredWorkers)) {
			throw new \InvalidArgumentException(sprintf('Given workerAddress (%s) is unknown', $workerAddress));
		}

		if (!in_array($workerAddress, $this->_availableWorkers)) {
			$this->_availableWorkers[] = $workerAddress;
		}
	}

	/**
	 * @param \Daemon\Task\AbstractTask $task
	 */
	public function addNewTask(Task\AbstractTask $task)
	{
		$taskIdentifier = $task->getIdentifier();
		$this->_newTasks[$taskIdentifier] = $task;
	}

	/**
	 * @param \Daemon\Task\AbstractTask $task
	 * @throws \RuntimeException
	 */
	public function removeTaskFromInWorkTasks(Task\AbstractTask $task)
	{
		$taskIdentifier = $task->getIdentifier();
		if (!isset($this->_inWorkTasks[$taskIdentifier])) {
			throw new \RuntimeException(sprintf('task (%s) is not in inWorkTasks', $taskIdentifier));
		}

		unset($this->_inWorkTasks[$taskIdentifier]);
	}

	/**
	 * @param string $targetAddress
	 * @param mixed $data
	 */
	public function sendMessageTo($targetAddress, $data)
	{
		$zmsg = new Zmsg($this->_socket);
		$zmsg->body_set(serialize($data));
		$zmsg->wrap($targetAddress);
		$zmsg->send();
	}

	protected function _initShutdown()
	{
		$this->_shutdownRequested = true;
	}

	public function startShutdownProcedure()
	{
		$this->_initShutdown();
	}
}
