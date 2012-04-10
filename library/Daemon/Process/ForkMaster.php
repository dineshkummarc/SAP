<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 12:01 PM
 */
namespace Daemon\Process;
use Daemon\Message;

class ForkMaster extends AbstractProcess
{
	protected $_pids = array(
		'workers' => array(),
	);

	protected function _init()
	{
		$pid = pcntl_fork();
		if ($pid !== 0) {
			return;
		}

		$pidFile = $this->_config->get('pid_file');
		if (file_exists($pidFile) && '' != exec('ps -p `cat ' . $pidFile . '` --no-heading')) {
			trigger_error('Process running with PID ' . file_get_contents($pidFile), E_USER_NOTICE);
			exit(0);
		}

		file_put_contents($pidFile, getmypid());

		setproctitle('Daemon: ForkMaster');
		$this->_forkChilds();
		$this->_monitorChildProcesses();
	}

	protected function _forkChilds()
	{
		$this->_forkQueueManager();
		$this->_forkWorkers();
	}

	protected function _forkQueueManager()
	{
		$pid = pcntl_fork();
		if ($pid !== 0) {
			$this->_pids['queueManager'] = $pid;
			$this->log('forked queueManager pid: %d', $pid);
			return;
		}

		new QueueManager($this->_config);
		// we should never get here
		exit;
	}

	protected function _forkWorkers()
	{
		$nrOfWorkersToFork = $this->_config->get('worker_count');
		if (!is_numeric($nrOfWorkersToFork)) {
			throw new \InvalidArgumentException('worker_count has to be a number');
		}

		for ($i = 0; $i < $nrOfWorkersToFork; $i++) {
			$this->_forkWorker();
		}
	}

	protected function _forkWorker()
	{
		$pid = pcntl_fork();
		if ($pid !== 0) {
			$this->_pids['workers'][] = $pid;
			$this->log('forked worker pid: %d', $pid);
			return;
		}

		new Worker($this->_config);
		// we should never get here
		exit;
	}

	protected function _monitorChildProcesses()
	{
		$this->_initSignalHandler();
		declare(ticks = 1) ;

		while (true) {
			$pidOfDiedChild = pcntl_waitpid(0, $status, WNOHANG);
			if ($pidOfDiedChild > 0) {
				if ($this->_pids['queueManager'] === $pidOfDiedChild) {
					$this->_forkQueueManager();
				} elseif (false !== ($key = array_search($pidOfDiedChild, $this->_pids['workers']))) {
					unset($this->_pids['workers'][$key]);
					$this->_forkWorker();
				} else {
					$msg = sprintf('unknown pid %d received in forkmaster pid of died child', $pidOfDiedChild);
					throw new \RuntimeException($msg);
				}
			} elseif ($pidOfDiedChild === -1) {
				$this->log('An error occurred while waiting for dead childs');
			}

			usleep(5000);
		}
	}

	protected function _initSignalHandler()
	{
		pcntl_signal(SIGTERM, array($this, 'signalHandler'));
	}

	public function signalHandler($signalNumber)
	{
		switch ($signalNumber) {
			case SIGTERM:
				$this->log('received signal %d initiate shutdown', $signalNumber);
				$this->_initShutdown();
				break;
			default:
				$this->log('received signal %d do nothing', $signalNumber);
		}
	}

	protected function _initShutdown()
	{
		$this->_sendShutdownMessageToQueueManager();

		while (true) {
			$pidOfDiedChild = pcntl_waitpid(0, $status);
			if ($pidOfDiedChild > 0) {
				if (isset($this->_pids['queueManager']) && $this->_pids['queueManager'] === $pidOfDiedChild) {
					unset($this->_pids['queueManager']);
				} elseif (false !== ($key = array_search($pidOfDiedChild, $this->_pids['workers']))) {
					unset($this->_pids['workers'][$key]);
				} else {
					$msg = sprintf('unknown pid %d received in forkmaster pid of died child', $pidOfDiedChild);
					throw new \RuntimeException($msg);
				}
			}

			if (count($this->_pids) === 1 && count($this->_pids['workers']) === 0) {
				$this->log('all childs killed. exit');
				unlink($this->_config->get('pid_file'));
				exit;
			}
		}
	}

	protected function _sendShutdownMessageToQueueManager()
	{
		$context = new \ZMQContext();
		$socket = $context->getSocket(\ZMQ::SOCKET_REQ);

		$queueManagerSocket = $this->_config->get('sockets.queueManager');
		$socket->connect($queueManagerSocket);

		$zmsg = new \ZMQ\Zmsg($socket);
		$msg = new Message\Shutdown();
		$zmsg->body_set(serialize($msg));
		$zmsg->send();

		$this->log('sent shutdown message to queueManager');
	}
}
