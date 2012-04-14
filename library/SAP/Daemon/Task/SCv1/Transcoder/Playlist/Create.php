<?php
/**
 * User: peaceman
 * Date: 4/14/12
 * Time: 10:45 PM
 */
namespace SAP\Daemon\Task\SCv1\Transcoder\Playlist;
use Daemon\Task;

class Create extends Task\AbstractTask
{
	protected function _init()
	{
		if (!isset($this->_data['transcoder_identifier'])) {
			throw new \InvalidArgumentException('no or invalid transcoder_identifier given');
		}

		if (!isset($this->_data['name']) || !is_string($this->_data['name'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid name given');
		}

		if (!isset($this->_data['playlist']) || !is_array($this->_data['playlist'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid playlist given');
		}
	}

	protected function _run()
	{
		file_put_contents($this->_getPathToPlaylist(), implode(PHP_EOL, $this->_data['playlist']));
	}

	protected function _getPathToPlaylist()
	{
		return realpath(APPLICATION_PATH . '/data/playlists') . '/' . sprintf('%s-%s.lst', $this->_data['transcoder_identifier'], $this->_data['name']);
	}
}
