<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:42 PM
 */
namespace Daemon\Message\Worker; use Daemon\Message;

class Register extends Message\AbstractMessage
{
	/**
	 * @param array $content
	 */
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
