<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 4:21 PM
 */
 
class UserController extends \SAP\Controller\Action
{
	public function indexAction()
	{

	}

	public function loginAction()
	{
		$auth = $this->_getAuth();
		if ($auth->hasIdentity()) {
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		$userLoginForm = new Application_Form_UserLogin();
		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();

		if ($request->isPost() && $userLoginForm->isValid($request->getPost())) {
			$authAdapter = $this->_getAuthAdapter();

			$authAdapter->setIdentity($request->get('username'));
			$authAdapter->setCredential($request->get('password'));

			$authResult = $auth->authenticate($authAdapter);
			if (!$authResult->isValid()) {
				$messages = $authResult->getMessages();
				foreach ($messages as $message) {
					$this->getFlashMessenger()->addErrorMessage($message, true);
				}
			} else {
				$this->getFlashMessenger()->addSuccessMessage('login successful');
				$this->_redirect($this->url('index'), array('exit' => true));
			}
		}

		$this->view->assign('form', $userLoginForm);
	}

	public function logoutAction()
	{
		$this->_redirectToLoginPageIfNotLoggedIn();

		$auth = $this->_getAuth();
		$auth->clearIdentity();

		$this->_redirect($this->url('index', 'index'), array('exit' => true));
	}

	protected function _redirectToLoginPageIfNotLoggedIn()
	{
		$auth = $this->_getAuth();
		if (!$auth->hasIdentity()) {
			$this->_redirect($this->url('login'), array('exit' => true));
		}
	}

	/**
	 * @return Zend_Auth
	 */
	protected function _getAuth()
	{
		return Zend_Auth::getInstance();
	}

	/**
	 * @return Zend_Auth_Adapter_DbTable
	 */
	protected function _getAuthAdapter()
	{
		$authAdapter = new Zend_Auth_Adapter_DbTable(null, 'user', 'username', 'password', 'SHA1(CONCAT(SHA1(?), SHA1(username)))');
		return $authAdapter;
	}
}
