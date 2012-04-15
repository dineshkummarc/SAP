<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 1:02 AM
 */
namespace SAP\Daemon\Process;
use Daemon\Process\AbstractProcess,
	ZMQ\Zmsg;

class Downloader extends AbstractProcess
{
	protected $_downloadBundleId;
	protected $_downloadList;
	protected $_socketToWorker;
	protected $_socketToQueueManager;
	protected $_context;
	protected $_currentDownloadId;

	public function __construct(\Daemon\Config $configuration, $downloadBundleId)
	{
		$this->_downloadBundleId = $downloadBundleId;
		parent::__construct($configuration);
	}

	protected function _init()
	{
		setproctitle('Daemon: downloader');
		$this->_initSockets();
		$this->_getDownloadListFromWorker();
		$this->_checkAndFilterDownloads();
		$this->_startDownloads();
		exit;
	}

	protected function _getDownloadListFromWorker()
	{
		$zmsg = new Zmsg($this->_socketToWorker);
		$zmsg->recv();

		$this->_downloadList = unserialize($zmsg->body());
		$this->log('received downloadlist from worker, nr of elements: %d', count($this->_downloadList));
	}

	protected function _initSockets()
	{
		$this->log('starting to initialize sockets');
		$context = new \ZMQContext();
		$socket = $context->getSocket(\ZMQ::SOCKET_PAIR);
		$socket->connect(sprintf('ipc:///tmp/downloader-%d.ipc', $this->_downloadBundleId));
		$this->_socketToWorker = $socket;

		$socket = $context->getSocket(\ZMQ::SOCKET_DEALER);
		$socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, sprintf('downloader/%s', uniqid()));
		$socket->connect($this->_config->get('sockets.queueManager'));
		$this->_socketToQueueManager = $socket;

		$this->_context = $context;
		$this->log('initialized sockets');
	}

	protected function _checkAndFilterDownloads()
	{
		$failedChecks = $this->_checkDownloads();
		if (!empty($failedChecks)) {
			$this->log('found failed checks');
			$this->_filterDownloads($failedChecks);
		}
	}

	protected function _checkDownloads()
	{
		$this->log('start to check downloads');
		$failedChecks = array();

		foreach ($this->_downloadList as $downloadId => $url) {
			try {
				$this->log('check %s', $url);
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

		$this->log('send message to worker');
		$zmsg = new \ZMQ\Zmsg($this->_socketToWorker);
		$zmsg->body_set(serialize($message));
		$zmsg->send();

		$this->log('finished downloads-check');

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
		$this->log('start downloads');
		$hooks = new \Requests_Hooks();
		$hooks->register('curl.before_send', array($this, 'setCurlCallbackFunction'));

		foreach ($this->_downloadList as $id => $url) {
			$this->log('download %s', $url);
			$targetFile = $this->_getTargetFilePath($id, $url);
			$options = array(
				'filename' => $targetFile,
				'hooks' => $hooks,
				'timeout' => pow(2, 64),
			);

			$this->_currentDownloadId = $id;
			\Requests::get($url, array(), $options);
			$this->log('finished downloading %s', $url);
		}
		$this->log('finished all downloading going to die now');
	}

	public function setCurlCallbackFunction($curlHandle)
	{
		$this->log('setting callback function');
		$options = array(
			CURLOPT_NOPROGRESS => false,
			CURLOPT_PROGRESSFUNCTION => array($this, 'curlProgress'),
			CURLOPT_BUFFERSIZE => 1024 * 512,
		);

		curl_setopt_array($curlHandle, $options);
	}

	public function curlProgress($fullSize, $alreadyDownloaded)
	{
		if ($fullSize > 0) {
			$this->log('progress: %d%%', ($alreadyDownloaded / $fullSize) * 100);
		} else {
			$this->log('fullsize is %d', $fullSize);
		}

		$message = new \Daemon\Message\Task\Add(array(
			'task' => new \SAP\Daemon\Task\Download\UpdateStatus(array(
				'download_id' => $this->_currentDownloadId,
				'already_downloaded' => $alreadyDownloaded,
				'full_size' => $fullSize,
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
