<?php
/**
 * User: peaceman
 * Date: 4/10/12
 * Time: 8:56 PM
 */
namespace SAP\Daemon\Task\SCv1\Transcoder;
use Daemon\Task;

class Start extends Task\AbstractTask
{
	/**
	 * @var bool
	 */
	protected $_isSynchronous = true;

	protected function _init()
	{
		if (!isset($this->_data['transcoder_identifier'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid transcoder_identifier given');
		}

		if (!isset($this->_data['transcoder_config_file'])
			&& (!isset($this->_data['transcoder_config'])
			|| !is_array($this->_data['transcoder_config'])))
		{
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('no or invalid transcoder_config given');
		}

		if (isset($this->_data['transcoder_config_file'])
			&& !file_exists($this->_data['transcoder_config_file']))
		{
			$this->_maxTries = 0;
			throw new \InvalidArgumentException(sprintf('transcoder configuration file %s doesnt exist', $this->_data['transcoder_config_file']));
		}
	}

	protected function _run()
	{
		if ($this->_checkTranscoderIsAlreadyRunning()) {
			$result = array(
				'success' => false,
				'message' => 'Transcoder is already running',
			);
			$this->_setResult($result);
			return;
		}

		if (!isset($this->_data['transcoder_config_file'])) {
			$this->_updateTranscoderConfigurationFile();
		}

		$this->_startTranscoderWithConfigurationFile();
		$this->_setResult(array(
			'success' => true,
			'message' => 'Successfully started transcoder',
		));
	}

	/**
	 * @return bool
	 */
	protected function _checkTranscoderIsAlreadyRunning()
	{
		$pathToPidFile = $this->_getPathToPidFile();

		if (!file_exists($pathToPidFile)) {
			return false;
		}

		$pid = file_get_contents($pathToPidFile);
		return $this->_isProcessWithPidRunning((int)$pid);
	}

	protected function _getPathToPidFile()
	{
		return realpath(APPLICATION_PATH . '/../pids') . '/' . $this->_data['transcoder_identifier'] . '.pid';
	}

	/**
	 * @param int $pid
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	protected function _isProcessWithPidRunning($pid)
	{
		if (!is_int($pid)) {
			throw new \InvalidArgumentException('exepected an int');
		}

		$shellCommand = sprintf('ps --pid %d --no-heading', $pid);
		$result = exec($shellCommand, $output);
		return !empty($result);
	}

	protected function _updateTranscoderConfigurationFile()
	{
		$config = $this->_data['transcoder_config'];

		$configWriter = new \SAP\Config\Writer\ScTrans();

		$config = new \Zend_Config($config);
		$configWriter->write($this->_getTranscoderConfigurationFilePath(), $config);
	}

	/**
	 * @return string
	 */
	protected function _getTranscoderConfigurationFilePath()
	{
		return realpath(APPLICATION_PATH . '/configs/streams') . '/' . $this->_data['transcoder_identifier'] . '.ini';
	}

	/**
	 * @throws \RuntimeException
	 */
	protected function _startTranscoderWithConfigurationFile()
	{
		$shellCommand = sprintf(
			'%s %s > /dev/null 2>&1 & echo $!',
			$this->_getPathToScTrans(),
			$this->_getTranscoderConfigurationFilePath()
		);

		$result = exec($shellCommand, $output);
		if (!is_numeric($result)) {
			$msg = sprintf('failed to start %s with configuration file %s', $this->_getPathToScTrans());
			throw new \RuntimeException($msg);
		}

		$this->_writePidFile((int)$result);

		$this->_setResult(array(
			'success' => true,
			'message' => 'Transcoder started succesfully',
			'shell_command' => $shellCommand,
			'result' => $result,
			'output' => $output,
		));
		file_put_contents('/tmp/abc', $shellCommand, FILE_APPEND);
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function _getPathToScTrans()
	{
		$filePath = realpath(APPLICATION_PATH . '/../binary') . '/sc_trans';
		if (!file_exists($filePath)) {
			throw new \RuntimeException('couldnt find sc_trans binary');
		}

		return $filePath;
	}

	/**
	 * @param int $pid
	 * @throws \InvalidArgumentException
	 */
	protected function _writePidFile($pid)
	{
		if (!is_int($pid)) {
			throw new \InvalidArgumentException('expected an int');
		}

		file_put_contents($this->_getPathToPidFile(), $pid, LOCK_EX);
	}
}
