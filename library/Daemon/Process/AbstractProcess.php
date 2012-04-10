<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 11:41 AM
 */
namespace Daemon\Process;

abstract class AbstractProcess
{
	/**
	 * @var \Daemon\Config
	 */
	protected $_config;

	/**
	 * @param \Daemon\Config $configuration
	 */
	public function __construct(\Daemon\Config $configuration)
	{
		$this->_config = $configuration;
		$this->_initStdio();
		$this->_init();
	}

	protected function _initStdio()
	{
		$stdoutFile = $this->_config->get('stdout_file');
		if (file_exists($stdoutFile) && !is_writable($stdoutFile)) {
			throw new \RuntimeException('Couldnt write to stdout file ' . $stdoutFile);
		}

		if (is_resource(STDIN)) {
			fclose(STDIN);
		}

		if (is_resource(STDERR)) {
			fclose(STDERR);
		}

		if (is_resource(STDOUT)) {
			fclose(STDOUT);
		}

		$STDIN = fopen('/dev/null', 'r');
		$STDOUT = fopen($stdoutFile, 'ab');
		$STDERR = fopen($stdoutFile, 'ab');
	}

	/**
	 * @param string $msg
	 * @throws \RuntimeException
	 */
	public function log($msg)
	{
		static $filename;
		if ($filename === null) {
			$filename = $this->_config->get('log_file');
			if (!is_string($filename)) {
				throw new \RuntimeException('expected a string');
			}
		}

		$arguments = func_get_args();
		if (count($arguments) > 1) {
			array_shift($arguments);
			$msg = vsprintf($msg, $arguments);
		}

		$dateTime = new \DateTime();
		$msg = sprintf('%s | %d | %s | %s' . PHP_EOL, $dateTime->format('c'), getmypid(), get_class($this), $msg);
		file_put_contents($filename, $msg, FILE_APPEND | LOCK_EX);
	}

	abstract protected function _init();

	abstract protected function _initShutdown();
}
 
