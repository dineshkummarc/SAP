<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:57 PM
 */
namespace Daemon\Task;

abstract class AbstractSynchronousTask extends AbstractTask
{
	/**
	 * @var array
	 */
	private $_result;

	/**
	 * @var string
	 */
	protected $_returnAddress;

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
}
