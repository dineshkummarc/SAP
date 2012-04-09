<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:26 PM
 */
namespace Daemon\Message\Task; use Daemon\Message;

class Add extends Message\AbstractMessage
{
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'task',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	public function getTask()
	{
		return $this->task;
	}
}
