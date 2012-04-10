<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:57 PM
 */
namespace Daemon\Task;
use Daemon\Message;

abstract class AbstractTask
{
	/**
	 * @var \Daemon\Config
	 */
	protected $_config;

	/**
	 * @var \Daemon\Message\AbstractMessage[]
	 */
	protected $_messagesToQueueManager;

	/**
	 * @var int
	 */
	protected $_tries = 0;

	/**
	 * @var int
	 */
	protected $_maxTries = 2;

	/**
	 * @var array|null
	 */
	protected $_data;

	/**
	 * @var string
	 */
	protected $_identifier;

	/**
	 * @param array|null $data
	 */
	final public function __construct(array $data = null)
	{
		$this->_identifier = uniqid();
		$this->_data = $data;
		$this->_init();
	}

	final public function run()
	{
		$this->_tries++;
		$this->_run();
	}

	abstract protected function _run();

	protected function _init()
	{
	}

	/**
	 * @param \Daemon\Config $config
	 * @return \Daemon\Task\AbstractTask
	 */
	public function setConfig(\Daemon\Config $config)
	{
		$this->_config = $config;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function canTry()
	{
		return $this->_tries < $this->_maxTries;
	}

	/**
	 * @return bool
	 */
	public function hasMessagesToQueueManager()
	{
		return count($this->_messagesToQueueManager) > 0;
	}

	/**
	 * @return \Daemon\Message\AbstractMessage[]
	 */
	public function getMessagesToQueueManager()
	{
		return $this->_messagesToQueueManager;
	}

	/**
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->_identifier;
	}
}
