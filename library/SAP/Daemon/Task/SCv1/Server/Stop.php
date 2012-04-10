<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 5:52 PM
 */
namespace SAP\Daemon\Task\SCv1\Server;
use Daemon\Task;

class Stop extends Task\AbstractSynchronousTask
{
	protected function _init()
	{
		if (!isset($this->_data['server_identifier'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid server_identifier given');
		}
	}

	protected function _run()
	{
		$pid = $this->_getPidForServer();
		if ($pid === null || !$this->_isProcessWithPidRunning($pid)) {
			$this->_setResult(array(
				'success' => false,
				'message' => 'Server is not running',
			));
			return;
		}

		$this->_stopServer($pid);
		$this->_setResult(array(
			'success' => true,
			'message' => 'Successfully stopped server',
		));
	}

	/**
	 * @return int|null
	 */
	protected function _getPidForServer()
	{
		$pathToPidFile = $this->_getPathToPidFile();
		if (!file_exists($pathToPidFile)) {
			return null;
		}

		$pid = file_get_contents($pathToPidFile);
		return !empty($pid) ? (int)$pid : null;
	}

	/**
	 * @return string
	 */
	protected function _getPathToPidFile()
	{
		return realpath(APPLICATION_PATH . '/../pids') . '/' . $this->_data['server_identifier'] . '.pid';
	}

	/**
	 * @param int $pid
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	protected function _isProcessWithPidRunning($pid)
	{
		if (!is_int($pid)) {
			throw new \InvalidArgumentException('expected an int');
		}

		$shellCommand = sprintf('ps --pid %d --no-heading', $pid);
		$result = exec($shellCommand, $output);
		return !empty($result);
	}

	/**
	 * @param $pid
	 * @throws \InvalidArgumentException
	 */
	protected function _stopServer($pid)
	{
		if (!is_int($pid)) {
			throw new \InvalidArgumentException('expected an int');
		}

		$shellCommand = sprintf('kill %d', $pid);
		exec($shellCommand);
		unlink($this->_getPathToPidFile());
	}
}
