<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:13 PM
 */
namespace Daemon\MessageHandler; use Daemon\Process, Daemon\Message;

class Factory
{
	/**
	 * @var \Daemon\Config
	 */
	protected $_config;

	/**
	 * @var \Daemon\Process\QueueManager
	 */
	protected $_queueManager;

	/**
	 * @var \Daemon\MessageHandler\AbstractMessageHandler[]
	 */
	protected $_messageHandlerCache = array();

	/**
	 * @param \Daemon\Config $config
	 * @param \Daemon\Process\QueueManager $queueManager
	 */
	public function __construct(\Daemon\Config $config, Process\QueueManager $queueManager)
	{
		$this->_config = $config;
		$this->_queueManager = $queueManager;
	}

	public function getInstance(\ZMQ\Zmsg $zmsg)
	{
		$message = unserialize($zmsg->body());
		$class = get_class($message);
		$handlerClass = str_replace('Message', 'MessageHandler', $class);

		if (!isset($this->_messageHandlerCache[$handlerClass])) {
			if (false === class_exists($handlerClass)) {
				throw new \UnexpectedValueException(sprintf('invalid handlerClass %s', $handlerClass));
			}

			$handler = new $handlerClass($this->_config, $this->_queueManager);
			$this->_messageHandlerCache[$handlerClass] = $handler;
		}

		$this->_messageHandlerCache[$handlerClass]->setMessage($zmsg, $message);
		return $this->_messageHandlerCache[$handlerClass];
	}
}
