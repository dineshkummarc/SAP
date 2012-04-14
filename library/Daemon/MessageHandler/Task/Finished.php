<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:33 PM
 */
namespace Daemon\MessageHandler\Task;
use Daemon\MessageHandler,
	Daemon\Message\Task;

class Finished extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Task\Finished
	 */
	protected $_message;

	public function handle()
	{
		$task = $this->_message->getTask();
		$workerAddress = $this->_message->getWorkerAddress();

		$this->_queueManager->removeTaskFromInWorkTasks($task);
		$this->_queueManager->flagWorkerAsAvailable($workerAddress);

		if ($task->isSynchronous()) {
			/** @var $task \Daemon\Task\AbstractTask */
			$this->_handleSynchronousTask($task);
		}
	}

	protected function _handleSynchronousTask(\Daemon\Task\AbstractTask $task)
	{
		$returnAddress = $task->getReturnAddress();
		$result = $task->getResult();

		$resultMessage = new Task\Result(array(
			'result' => $result,
		));

		$this->_queueManager->sendMessageTo($returnAddress, $resultMessage);
		$this->log('sent result from synchronous task to %s', $returnAddress);
	}
}
