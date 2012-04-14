<?php
/**
 * User: peaceman
 * Date: 4/14/12
 * Time: 11:29 PM
 */
namespace Daemon\MessageHandler\Worker;
use Daemon\MessageHandler;

class Heartbeat extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Worker\Heartbeat
	 */
	protected $_message;

	public function handle()
	{
		$workerAddress = $this->_message->getWorkerAddress();

		if (!$this->_queueManager->isKnownWorker($workerAddress)) {
			$this->_queueManager->addWorker($workerAddress);
		}
	}
}
 
