<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 2:25 AM
 */
namespace SAP\Daemon\Task\Download;
use Daemon\Task\AbstractTask;

class UpdateStatus extends AbstractTask
{
	protected function _init()
	{
		if (!isset($this->_data['download_id']) || !is_int($this->_data['download_id'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid download_id given');
		}

		if (!isset($this->_data['already_downloaded'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no already_downloaded given');
		}

		if (!isset($this->_data['full_size'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no full_size given');
		}
	}

	protected function _run()
	{
		$this->_updateInDatabase();
		$this->_pushToNodeJs();
	}

	protected function _updateInDatabase()
	{
		//TODO implement
	}

	protected function _pushToNodeJs()
	{
		//TODO implement
	}
}
