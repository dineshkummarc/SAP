<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:41 PM
 */
namespace Daemon\MessageHandler\Worker\Shutdown; use Daemon\MessageHandler;

class Unexpected extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Worker\Shutdown\Unexpected
	 */
	protected $_message;

	public function handle()
	{
		$this->_queueManager->removeWorker($this->_message->getWorkerAddress());

		$task = $this->_message->getTask();
		$this->_queueManager->removeTaskFromInWorkTasks($task);

		if ($task->canTry()) {
			$this->_queueManager->addNewTask($task);
		} else {
			if ($task->isSynchronous()) {
				/** @var $task \Daemon\Task\AbstractTask */
				$this->_handleSynchronousTask($task);
			}
		}
	}

	protected function _handleSynchronousTask(\Daemon\Task\AbstractTask $task)
	{
		$returnAddress = $task->getReturnAddress();
		$this->_queueManager->sendMessageTo($returnAddress, $this->_message);
	}
}
