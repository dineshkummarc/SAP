<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 2:17 AM
 */
namespace SAP\Daemon\Message\Download;
use Daemon\Message;

class CheckResult extends Message\AbstractMessage
{
	protected function _setContent(array $content)
	{
		static $requirements = array(
			'failedChecks',
		);

		$this->_checkRequirements($requirements, $content);
		$this->_information = $content;
	}

	public function hasNotReachableDownloads()
	{
		return !empty($this->_information['failedChecks']);
	}

	public function getNotReachableDownloadsWithReason()
	{
		return $this->_information['failedChecks'];
	}
}
