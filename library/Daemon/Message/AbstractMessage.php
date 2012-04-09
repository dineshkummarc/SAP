<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 1:14 PM
 */
namespace Daemon\Message;

abstract class AbstractMessage
{
	protected $_information;

	public function __construct(array $content = null)
	{
		if ($content === null) {
			$content = array();
		}

		$this->_setContent($content);
	}

	protected function _checkRequirements(array $requirements, array $content)
	{
		foreach ($requirements as $requirement) {
			if (!array_key_exists($requirement, $content)) {
				throw new \RuntimeException(sprintf('Missing %s in %s', $requirement, get_class($this)));
			}
		}
	}

	abstract protected function _setContent(array $content);

	public function __get($key)
	{
		if (!isset($this->_information[$key])) {
			return null;
		}

		return $this->_information[$key];
	}
}
