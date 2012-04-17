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

	public function createAction()
	{
		$userCreateForm = new Application_Form_UserCreate();
		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();

		if ($request->isPost() && $userCreateForm->isValid($request->getPost())) {
			$formValues = $userCreateForm->getValues();
			$userModel = new Application_Model_User();
			$userModel->setFromArray($formValues);

			$randomPassword = $this->_generateRandomPassword();
			$userModel->setAndSaltPassword($randomPassword);

			$userMapper = $this->_getUserMapper();
			$userMapper->save($userModel);

			$this->getFlashMessenger()->addSuccessMessage(sprintf('created new user with id %d and password %s', $userModel->getId(), $randomPassword));
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		$this->view->assign('form', $userCreateForm);
	}

	public function editAction()
	{
		$userModel = $this->_getUserModelFromRequestOrRedirectToListing();
		$userEditForm = new Application_Form_UserEdit(array('model' => $userModel));

		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ($request->isPost() && $userEditForm->isValid($request->getPost())) {
			$userModel->setFromArray($userEditForm->getValues());
			$this->_getUserMapper()->save($userModel);

			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully updated user %s', $userModel->getUsername()));
			$this->_redirect($this->url('list'), array('exit' => true));
		}

		$this->view->assign('form', $userEditForm);
		$this->view->assign('user', $userModel);
	}

	public function deleteAction()
	{
		$userModel = $this->_getUserModelFromRequestOrRedirectToListing();
		$deleted = $this->_getUserMapper()->delete(array('id = ?' => $userModel->getId()));
		if ($deleted) {
			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully deleted user %s', $userModel->getUsername()));
			$this->_redirect($this->url('list'), array('action' => 'exit'));
		}
	}

	/**
	 * @return Application_Model_User
	 */
	protected function _getUserModelFromRequestOrRedirectToListing()
	{
		$id = (int)$this->_getParam('id');
		$userMapper = $this->_getUserMapper();
		$userModel = $userMapper->find($id);

		if (!$userModel) {
			$this->getFlashMessenger()->addErrorMessage(sprintf('unknown user with id %d', $id));
			$this->_redirect($this->url('list'), array('exit' => true));
		}

		return $userModel;
	}

	public function listAction()
	{
		$userMapper = $this->_getUserMapper();
		$users = $userMapper->fetchAll();

		$this->view->assign('users', $users);
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

	/**
	 * @return Application_Model_UserMapper
	 */
	protected function _getUserMapper()
	{
		static $userMapper;

		if ($userMapper === null) {
			$userMapper = new Application_Model_UserMapper();
		}

		return $userMapper;
	}

	/**
	 * origin:
	 * http://www.laughing-buddha.net/php/password
	 *
	 * @param int $length
	 * @return string
	 */
	protected function _generateRandomPassword($length = 10)
	{
		// start with a blank password
		$password = "";

		// define possible characters - any character in this string can be
		// picked for use in the password, so if you want to put vowels back in
		// or add special characters such as exclamation marks, this is where
		// you should do it
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

		// we refer to the length of $possible a few times, so let's grab it now
		$maxlength = strlen($possible);

		// check for length overflow and truncate if necessary
		if ($length > $maxlength) {
			$length = $maxlength;
		}

		// set up a counter for how many characters are in the password so far
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $length) {

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength - 1), 1);

			// have we already used this character in $password?
			if (!strstr($password, $char)) {
				// no, so it's OK to add it onto the end of whatever we've already got...
				$password .= $char;
				// ... and increase the counter by one
				$i++;
			}

		}

		// done!
		return $password;
	}
}
