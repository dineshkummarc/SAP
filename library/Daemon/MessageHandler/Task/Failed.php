<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:28 PM
 */
namespace Daemon\MessageHandler\Task; use Daemon\MessageHandler;

class Failed extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Task\Failed
	 */
	protected $_message;

	public function handle()
	{
		$task = $this->_message->getTask();
		$workerAddress = $this->_message->getWorkerAddress();

		$this->_queueManager->flagWorkerAsAvailable($workerAddress);
		$this->_queueManager->removeTaskFromInWorkTasks($task);

		if ($task->canTry()) {
			$this->_queueManager->addNewTask($task);
		} else {
			if ($task instanceof \Daemon\Task\AbstractSynchronousTask) {
				/** @var $task \Daemon\Task\AbstractSynchronousTask */
				$this->_handleSynchronousTask($task);
			}
		}
	}

	/**
	 * @param \Daemon\Task\AbstractSynchronousTask $task
	 */
	protected function _handleSynchronousTask(\Daemon\Task\AbstractSynchronousTask $task)
	{
		$returnAddress = $task->getReturnAddress();
		$this->_queueManager->sendMessageTo($returnAddress, $this->_message);
		$this->log('sent failed message of synchronous task to %s', $returnAddress);
	}
}
