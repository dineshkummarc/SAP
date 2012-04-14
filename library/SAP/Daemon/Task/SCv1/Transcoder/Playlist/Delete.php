<?php
/**
 * User: peaceman
 * Date: 4/14/12
 * Time: 10:56 PM
 */
namespace SAP\Daemon\Task\SCv1\Transcoder\Playlist;
use Daemon\Task;

class Delete extends Task\AbstractTask
{
	protected function _init()
	{
		if (!isset($this->_data['transcoder_identifer']) || !is_string($this->_data['transcoder_identifier'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid transcoder_identifier given');
		}

		if (!isset($this->_data['name']) || !is_string($this->_data['name'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid name given');
		}
	}

	protected function _run()
	{
		$pathToPlaylist = $this->_getPathToPlaylist();
		if (file_exists($pathToPlaylist)) {
			unlink($pathToPlaylist);
		}
	}

	protected function _getPathToPlaylist()
	{
		return realpath(APPLICATION_PATH . '/data/playlists') . '/' . sprintf('%s-%s.lst', $this->_data['transcoder_identifier'], $this->_data['name']);
	}
}
 
