<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:39 PM
 */
namespace Daemon\MessageHandler\Worker\Shutdown; use Daemon\MessageHandler;

class Regular extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Worker\Shutdown\Regular
	 */
	protected $_message;

	public function handle()
	{
		$workerAddress = $this->_message->getWorkerAddress();
		$this->_queueManager->removeWorker($workerAddress);
		$this->log('removed worker with address %s, because of a regular shutdown', $workerAddress);
	}
}
