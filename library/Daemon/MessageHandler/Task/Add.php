<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:25 PM
 */
namespace Daemon\MessageHandler\Task; use Daemon\MessageHandler;

class Add extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Task\Add
	 */
	protected $_message;

	public function handle()
	{
		$task = $this->_message->getTask();
		if ($task instanceof \Daemon\Task\AbstractSynchronousTask) {
			/** @var $task \Daemon\Task\AbstractSynchronousTask */
			$task->setReturnAddress($this->_zmsg->address());
			$this->log('received synchronous task with return address %s', $this->_zmsg->address());
		}

		$this->_queueManager->addNewTask($task);
	}
}
