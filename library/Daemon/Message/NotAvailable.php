<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:18 PM
 */
namespace Daemon\Message;

class NotAvailable extends AbstractMessage
{
	/**
	 * @param array $content
	 */
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'reason',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	/**
	 * @return string
	 */
	public function getReason()
	{
		return $this->reason;
	}
}
 
