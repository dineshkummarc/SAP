<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:46 PM
 */
namespace Daemon\Message\Worker\Shutdown; use Daemon\Message;

class Regular extends Message\AbstractMessage
{
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'workerAddress',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	public function getWorkerAddress()
	{
		return $this->workerAddress;
	}
}
