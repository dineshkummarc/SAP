<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:36 PM
 */
namespace Daemon\Message\Task; use Daemon\Message;

class Finished extends Message\AbstractMessage
{
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
	 * @return \Daemon\Task\AbstractTask
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * @return string
	 */
	public function getWorkerAddress()
	{
		return $this->workerAddress;
	}
}
