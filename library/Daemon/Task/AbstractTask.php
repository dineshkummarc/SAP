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
	 * @var array
	 */
	protected $_result;

	/**
	 * @var string
	 */
	protected $_returnAddress;

	/**
	 * @var bool
	 */
	protected $_isSynchronous = false;

	/**
	 * @var \ZMQContext
	 */
	protected $_context;

	/**
	 * @var \Daemon\Process\Worker
	 */
	protected $_process;

	/**
	 * @param array|null $data
	 * @param bool|null $synchronous
	 */
	final public function __construct(array $data = null, $synchronous = null)
	{
		$this->_identifier = uniqid();
		$this->_data = $data;

		if ($synchronous !== null) {
			$this->_isSynchronous = $synchronous;
		}

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

	/**
	 * @return array
	 * @throws \RuntimeException
	 */
	public function getResult()
	{
		if ($this->_result === null) {
			throw new \RuntimeException('result is null');
		}

		return $this->_result;
	}

	/**
	 * @param array $data
	 */
	protected function _setResult(array $data)
	{
		$this->_result = $data;
	}

	/**
	 * @param string $returnAddress
	 */
	public function setReturnAddress($returnAddress)
	{
		$this->_returnAddress = $returnAddress;
	}

	/**
	 * @return string
	 */
	public function getReturnAddress()
	{
		return $this->_returnAddress;
	}

	/**
	 * @return bool
	 */
	public function isSynchronous()
	{
		return $this->_isSynchronous;
	}

	/**
	 * @param \ZMQContext $context
	 */
	public function setContext(\ZMQContext $context)
	{
		$this->_context = $context;
	}

	public function setProcess(\Daemon\Process\Worker $process)
	{
		$this->_process = $process;
	}
}
