<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 6:21 PM
 */
namespace SAP\Controller;

class Action extends \Zend_Controller_Action
{
	/**
	 * @var \SAP\Controller\Action\Helper\FlashMessenger
	 */
	protected $_flashMessenger;

	/**
	 * @var \Zend_Controller_Action_Helper_Url
	 */
	protected $_url;

	/**
	 * @return \SAP\Controller\Action\Helper\FlashMessenger
	 */
	public function getFlashMessenger()
	{
		if ($this->_flashMessenger === null) {
			$this->_flashMessenger = $this->getHelper('flashMessenger');
		}

		return $this->_flashMessenger;
	}

	/**
	 * @return string
	 */
	public function url()
	{
		if ($this->_url === null) {
			$this->_url = $this->getHelper('url');
		}

		return call_user_func_array(array($this->_url, 'direct'), func_get_args());
	}
}
