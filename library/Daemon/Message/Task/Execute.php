<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:29 PM
 */
namespace Daemon\Message\Task; use Daemon\Message;

class Execute extends Message\AbstractMessage
{
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'task',
		);

		$this->_checkRequirements($requirements, $content);
		if (!($content['task'] instanceof \Daemon\Task\AbstractTask)) {
			throw new \InvalidArgumentException('task has to be type of \Daemon\Task\AbstractTask');
		}

		$this->_information = $content;
	}

	public function getTask()
	{
		return $this->task;
	}
}
