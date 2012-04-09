<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:39 PM
 */
namespace Daemon\Message\Task; use Daemon\Message;

class Result extends Message\AbstractMessage
{
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'result',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	public function getResult()
	{
		return $this->_information['result'];
	}
}
