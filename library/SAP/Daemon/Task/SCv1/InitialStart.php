<?php
/**
 * User: peaceman
 * Date: 4/10/12
 * Time: 10:44 PM
 */
namespace SAP\Daemon\Task\SCv1;
use Daemon\Task,
	Daemon\Message;

class InitialStart extends Task\AbstractTask
{
	protected function _run()
	{
		$this->_startServersWithAvailableConfiguration();
		$this->_startTranscodersWithAvailableConfiguration();
	}

	protected function _startServersWithAvailableConfiguration()
	{
		$configurationFiles = $this->_getServerConfigurationFiles();
		foreach ($configurationFiles as $configurationFile) {
			$serverIdentifier = $this->_getServerIdentifierFromServerConfigurationFilename($configurationFile);
			$startServerMessage = new Message\Task\Add(array(
				'task' => new \SAP\Daemon\Task\SCv1\Server\Start(array(
					'server_identifier' => $serverIdentifier,
					'server_config_file' => $configurationFile,
				)),
			));

			$this->_messagesToQueueManager[] = $startServerMessage;
		}
	}

	protected function _getServerConfigurationFiles()
	{
		$configurationPath = $this->_getConfigurationPath();
		if (!is_dir($configurationPath)) {
			throw new \RuntimeException(sprintf('%s is no directory', $configurationPath));
		}

		return glob($configurationPath . '/sc_serv-*.ini');
	}

	protected function _getConfigurationPath()
	{
		return realpath(APPLICATION_PATH . '/configs/streams');
	}

	protected function _getServerIdentifierFromServerConfigurationFilename($configurationFilename)
	{
		$filename = pathinfo($configurationFilename, PATHINFO_FILENAME);
		return str_replace('.ini', '', $filename);
	}

	protected function _startTranscodersWithAvailableConfiguration()
	{
		$configurationFiles = $this->_getTranscoderConfigurationFiles();
		foreach ($configurationFiles as $configurationFile) {
			$transcoderIdentifier = $this->_getTranscoderIdentifierFromServerConfigurationFilename($configurationFile);
			$startTranscoderMessage = new Message\Task\Add(array(
				'task' => new \SAP\Daemon\Task\SCv1\Transcoder\Start(array(
					'transcoder_identifier' => $transcoderIdentifier,
					'transcoder_config_file' => $configurationFile,
				)),
			));

			$this->_messagesToQueueManager[] = $startTranscoderMessage;
		}
	}

	protected function _getTranscoderConfigurationFiles()
	{
		$configurationPath = $this->_getConfigurationPath();
		if (!is_dir($configurationPath)) {
			throw new \RuntimeException(sprintf('%s is no directory', $configurationPath));
		}

		return glob($configurationPath . '/sc_trans-*.ini');
	}

	protected function _getTranscoderIdentifierFromServerConfigurationFilename($configurationFilename)
	{
		$filename = pathinfo($configurationFilename, PATHINFO_FILENAME);
		return str_replace('.ini', '', $filename);
	}
}
