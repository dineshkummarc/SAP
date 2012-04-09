<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 2:22 PM
 */
namespace Daemon\MessageHandler;

class Shutdown extends AbstractMessageHandler
{
	public function handle()
	{
		$this->_queueManager->startShutdownProcedure();
	}
}
