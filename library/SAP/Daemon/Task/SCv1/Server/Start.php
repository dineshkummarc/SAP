<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 5:52 PM
 */

namespace SAP\Daemon\Task\SCv1\Server;
use Daemon\Task;

class Start extends Task\AbstractSynchronousTask
{
	protected function _init()
	{
		if (!isset($this->_data['server_identifier'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid server_identifier given');
		}

		if (!isset($this->_data['server_config_file'])
			&& (!isset($this->_data['server_config'])
			|| !is_array($this->_data['server_config'])))
		{
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid server_config given');
		}

		if (isset($this->_data['server_config_file'])
			&& !file_exists($this->_data['server_config_file']))
		{
			$this->_maxTries = 0;
			throw new \InvalidArgumentException(sprintf('server configuration file %s doesnt exist', $this->_data['server_config_file']));
		}
	}

	protected function _run()
	{
		if ($this->_checkServerIsAlreadyRunning()) {
			$result = array(
				'success' => false,
				'message' => 'Server is already running',
			);
			$this->_setResult($result);
			return;
		}

		if (!isset($this->_data['server_config_file'])) {
			$this->_updateServerConfigurationFile();
		}

		$this->_startServerWithConfigurationFile();
	}

	protected function _checkServerIsAlreadyRunning()
	{
		$pathToPidFile = $this->_getPathToPidFile();

		if (!file_exists($pathToPidFile)) {
			return false;
		}

		$pid = file_get_contents($pathToPidFile);
		return $this->_processWithPidIsRunning((int)$pid);
	}

	/**
	 * @param int $pid
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	protected function _processWithPidIsRunning($pid)
	{
		if (!is_int($pid)) {
			throw new \InvalidArgumentException('expected an int');
		}

		$shellCommand = sprintf('ps --pid %d --no-heading', $pid);
		$result = exec($shellCommand, $output);
		return !empty($result);
	}

	protected function _updateServerConfigurationFile()
	{
		$config = $this->_data['server_config'];

		$configWriter = new \SAP\Config\Writer\ScTrans();

		$config = new \Zend_Config($config);
		$configWriter->write($this->_getServerConfigurationFilePath(), $config);
	}

	protected function _startServerWithConfigurationFile()
	{
		$shellCommand = sprintf(
			'%s %s > /dev/null 2>&1 & echo $!',
			$this->_getPathToScServ(),
			$this->_getServerConfigurationFilePath()
		);

		$result = exec($shellCommand, $output);
		if (!is_numeric($result)) {
			$msg = sprintf('failed to start %s with configuration file %s', $this->_getPathToScServ(), $this->_getServerConfigurationFilePath());
			throw new \RuntimeException($msg);
		}

		$this->_writePidFile((int)$result);

		$this->_setResult(array(
			'success' => true,
			'message' => 'Server started successfully',
			'shell_command' => $shellCommand,
			'result' => $result,
			'output' => $output,
		));
	}

	/**
	 * @return string
	 */
	protected function _getServerConfigurationFilePath()
	{
		return realpath(APPLICATION_PATH . '/configs/streams') . '/' . $this->_data['server_identifier'] . '.ini';
	}

	/**
	 * @return string
	 */
	protected function _getPathToScServ()
	{
		$filePath = realpath(APPLICATION_PATH . '/../binary') . '/sc_serv';
		if (!file_exists($filePath)) {
			throw new \RuntimeException('couldnt find sc_serv binary');
		}

		return $filePath;
	}

	/**
	 * @param int $pid
	 */
	protected function _writePidFile($pid)
	{
		file_put_contents($this->_getPathToPidFile(), $pid, LOCK_EX);
	}

	/**
	 * @return string
	 */
	protected function _getPathToPidFile()
	{
		return realpath(APPLICATION_PATH . '/../pids') . '/' . $this->_data['server_identifier'] . '.pid';
	}
}
