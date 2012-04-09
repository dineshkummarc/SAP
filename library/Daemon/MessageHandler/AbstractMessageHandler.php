<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:07 PM
 */
namespace Daemon\MessageHandler; use Daemon\Process, Daemon\Message;

abstract class AbstractMessageHandler
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
	 * @var \ZMQ\Zmsg
	 */
	protected $_zmsg;

	/**
	 * @var \Daemon\Message\AbstractMessage
	 */
	protected $_message;

	/**
	 * @param \Daemon\Config $config
	 * @param \Daemon\Process\QueueManager $queueManager
	 */
	public function __construct(\Daemon\Config $config, Process\QueueManager $queueManager)
	{
		$this->_config = $config;
		$this->_queueManager = $queueManager;
	}

	/**
	 * @param \Daemon\Process\QueueManager $queueManager
	 */
	public function setQueueManager(Process\QueueManager $queueManager)
	{
		$this->_queueManager = $queueManager;
	}

	/**
	 * @param \ZMQ\Zmsg $zmsg
	 * @param \Daemon\Message\AbstractMessage $message
	 */
	public function setMessage(\ZMQ\Zmsg $zmsg, Message\AbstractMessage $message)
	{
		$this->_zmsg = $zmsg;
		$this->_message = $message;
	}

	abstract public function handle();

	public function log()
	{
		$arguments = func_get_args();
		call_user_func_array(array($this->_queueManager, 'log'), $arguments);
	}
}
