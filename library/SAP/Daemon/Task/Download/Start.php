<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 12:34 AM
 */
namespace SAP\Daemon\Task\Download;
use Daemon\Task\AbstractTask,
	ZMQ\Zmsg;

class Start extends AbstractTask
{
	protected $_isSynchronous = true;

	protected function _init()
	{
		if (!isset($this->_data['download_bundle_id']) || !is_int($this->_data['download_bundle_id'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('missing or invalid download_bundle_id');
		}

		if (!isset($this->_data['download_list']) || !is_array($this->_data['download_list'])) {
			$this->_maxTries = 0;
			throw new \InvalidArgumentException('missing or invalid download_list');
		}
	}

	protected function _run()
	{
		$this->_forkDownloader();

		$socketToDownloader = $this->_context->getSocket(\ZMQ::SOCKET_PAIR);
		$socketToDownloader->bind(sprintf('ipc:///tmp/downloader-%d.ipc', $this->_data['download_bundle_id']));
		$zmsg = new Zmsg($socketToDownloader);
		$zmsg->recv();

		/** @var $msg \SAP\Daemon\Message\Download\CheckResult */
		$msg = unserialize($zmsg->body());
		if (!$msg->hasNotReachableDownloads()) {
			$this->_setResult(array(
				'success' => true,
				'message' => 'downloads started successfully',
			));
		} else {
			$this->_setResult(array(
				'success' => false,
				'message' => 'reachability check of downloads failed',
				'not-reachable-downloads' => $msg->getNotReachableDownloadsWithReason(),
			));
		}

		unset($socketToDownloader);
	}

	protected function _forkDownloader()
	{
		$pid = pcntl_fork();
		if ($pid !== 0) {
			return;
		}

		new \SAP\Daemon\Process\Downloader($this->_config, $this->_data['download_bundle_id'], $this->_data['download_list']);
	}
}
