<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:34 PM
 */
namespace Daemon\Message\Task; use Daemon\Message;

class Failed extends Message\AbstractMessage
{
	/**
	 * @param array $content
	 */
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'task',
			'exception',
			'workerAddress',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	/**
	 * @return \Daemon\Task\AbstractTask
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}

	/**
	 * @return string
	 */
	public function getWorkerAddress()
	{
		return $this->workerAddress;
	}
}
