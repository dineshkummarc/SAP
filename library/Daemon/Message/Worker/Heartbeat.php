<?php
/**
 * User: peaceman
 * Date: 4/14/12
 * Time: 11:25 PM
 */
namespace Daemon\Message\Worker;
use Daemon\Message;

class Heartbeat extends Message\AbstractMessage
{
	protected function _setContent(array $content)
	{
		static $requirements = array(
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
}
 
