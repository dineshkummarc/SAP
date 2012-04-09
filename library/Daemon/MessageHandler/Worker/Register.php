<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:37 PM
 */
namespace Daemon\MessageHandler\Worker; use Daemon\MessageHandler;

class Register extends MessageHandler\AbstractMessageHandler
{
	/**
	 * @var \Daemon\Message\Worker\Register
	 */
	protected $_message;

	public function handle()
	{
		$this->_queueManager->addWorker($this->_message->getWorkerAddress());
	}
}
