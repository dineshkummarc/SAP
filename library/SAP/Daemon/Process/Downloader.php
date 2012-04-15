<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 1:02 AM
 */
namespace SAP\Daemon\Process;
use Daemon\Process\AbstractProcess;

class Downloader extends AbstractProcess
{
	/**
	 * @var \Daemon\Config
	 */
	protected $_config;
	protected $_downloadBundleId;
	protected $_downloadList;
	protected $_socketToWorker;
	protected $_socketToQueueManager;
	protected $_context;
	protected $_currentDownloadId;

	public function __construct(\Daemon\Config $configuration, $downloadBundleId, $downloadList)
	{
		$this->_config = $configuration;
		$this->_downloadBundleId = $downloadBundleId;
		$this->_downloadList = $downloadList;
	}

	protected function _init()
	{
		$this->_initSockets();
		$this->_checkAndFilterDownloads();
		$this->_startDownloads();
		exit;
	}

	protected function _initSockets()
	{
		$context = new \ZMQContext();
		$socket = $context->getSocket(\ZMQ::SOCKET_PAIR);
		$socket->bind(sprintf('ipc:///tmp/downloader-%d.ipc', $this->_downloadBundleId));
		$this->_socketToWorker = $socket;

		$socket = $context->getSocket(\ZMQ::SOCKET_DEALER);
		$socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, sprintf('downloader/%s', uniqid()));
		$socket->connect($this->_config->get('sockets.queueManager'));
		$this->_socketToQueueManager = $socket;

		$this->_context = $context;
	}

	protected function _checkAndFilterDownloads()
	{
		$failedChecks = $this->_checkDownloads();
		if (!empty($failedChecks)) {
			$this->_filterDownloads($failedChecks);
		}
	}

	protected function _checkDownloads()
	{
		$failedChecks = array();

		foreach ($this->_downloadList as $downloadId => $url) {
			try {
				$response = \Requests::head($url);
				if (!$response->success) {
					$failedChecks[$downloadId] = 'unknown reason';
				}
			} catch (\Exception $e) {
				$failedChecks[$downloadId] = $e->getMessage();
			}
		}

		$message = new \SAP\Daemon\Message\Download\CheckResult(array(
			'failedChecks' => $failedChecks,
		));
		$zmsg = new \ZMQ\Zmsg($this->_socketToWorker);
		$zmsg->body_set(serialize($message));
		$zmsg->send();

		return $failedChecks;
	}

	protected function _filterDownloads($failedChecks)
	{
		foreach ($failedChecks as $downloadId => $reason) {
			unset($this->_downloadList[$downloadId]);
		}
	}

	protected function _startDownloads()
	{
		$hooks = new \Requests_Hooks();
		$hooks->register('curl.before_send', array($this, 'setCurlCallbackFunction'));

		foreach ($this->_downloadList as $id => $url) {
			$targetFile = $this->_getTargetFilePath($id, $url);
			$options = array(
				'filename' => $targetFile,
				'hooks' => $hooks,
			);

			$this->_currentDownloadId = $id;
			\Requests::get($url, array(), $options);
		}
	}

	public function setCurlCallbackFunction($curlHandle)
	{
		$options = array(
			CURLOPT_NOPROGRESS => false,
			CURLOPT_PROGRESSFUNCTION => array($this, 'curlProgress'),
		);

		curl_setopt_array($curlHandle, $options);
	}

	public function curlProgress($curlHandle, $fileHandle, $length)
	{
		$message = new \Daemon\Message\Task\Add(array(
			'task' => new \SAP\Daemon\Task\Download\UpdateStatus(array(
				'download_id' => $this->_currentDownloadId,
				'length' => $length,
			))
		));

		$zmsg = new \ZMQ\Zmsg($this->_socketToQueueManager);
		$zmsg->body_set(serialize($message));
		$zmsg->send();
	}

	protected function _getTargetFilePath($downloadId, $url)
	{
		return realpath(APPLICATION_PATH . '/data/music') . sprintf('/%d_%s', $downloadId, pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME));
	}
}
