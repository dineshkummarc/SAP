<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:49 PM
 */
namespace Daemon\Message\Worker\Shutdown; use Daemon\Message;

class Unexpected extends Message\AbstractMessage
{
	/**
	 * @param array $content
	 */
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'task',
			'workerAddress',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	/**
	 * @return string
	 */
	public function getWorkerAddress()
	{
		return $this->workerAddress;
	}

	/**
	 * @return \Daemon\Task\AbstractTask
	 */
	public function getTask()
	{
		return $this->task;
	}
}
