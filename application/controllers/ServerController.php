<?php
/**
 * User: peaceman
 * Date: 4/20/12
 * Time: 8:33 PM
 */
class ServerController extends \SAP\Controller\Action
{
	public function createAction()
	{
		$serverCreateForm = new Application_Form_ServerCreate(array('serverTypes' => $this->_getAllServerTypesAsArray()));

		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ($request->isPost() && $serverCreateForm->isValid($request->getPost())) {
			$serverModel = new Application_Model_Server;
			$serverModel->setFromArray($serverCreateForm->getValues());

			$this->_getServerMapper()->save($serverModel);
			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully added server with id %d', $serverModel->getId()));
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		$this->view->assign('form', $serverCreateForm);
	}

	public function editAction()
	{
		$serverModel = $this->_getServerModelFromRequestOrRedirectToListing();
		$serverEditForm = new Application_Form_ServerEdit(array('model' => $serverModel, 'serverTypes' => $this->_getAllServerTypesAsArray()));

		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ($request->isPost() && $serverEditForm->isValid($request->getPost())) {
			$serverModel->setFromArray($serverEditForm->getValues());
			$this->_getServerMapper()->save($serverModel);
			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully updated server %s', $serverModel->getName()));
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		$this->view->assign(array(
			'server' => $serverModel,
			'form' => $serverEditForm,
		));
	}

	public function indexAction()
	{
		$servers = $this->_getServerMapper()->fetchAll();
		$this->view->assign('servers', $servers);
	}

	/**
	 * @return Application_Model_ServerMapper
	 */
	protected function _getServerMapper()
	{
		static $serverMapper;
		if ($serverMapper === null) {
			$serverMapper = new Application_Model_ServerMapper();
		}

		return $serverMapper;
	}

	/**
	 * @return Application_Model_ServerTypeMapper
	 */
	protected function _getServerTypeMapper()
	{
		static $serverTypeMapper;
		if ($serverTypeMapper === null) {
			$serverTypeMapper = new Application_Model_ServerTypeMapper;
		}

		return $serverTypeMapper;
	}

	/**
	 * @return Application_Model_Server
	 */
	protected function _getServerModelFromRequestOrRedirectToListing()
	{
		$id = $this->_getParam('id');
		$serverModel = $this->_getServerMapper()->find($id);
		if (!$serverModel) {
			$this->getFlashMessenger()->addErrorMessage('unknown server with id %d', $id);
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		return $serverModel;
	}

	/**
	 * @return array
	 */
	protected function _getAllServerTypesAsArray()
	{
		$serverTypes = $this->_getServerTypeMapper()->fetchAll();
		$toReturn = array();

		foreach ($serverTypes as $serverType) {
			/** @var $serverType Application_Model_ServerType */
			$toReturn[$serverType->getId()] = $serverType->getName();
		}

		return $toReturn;
	}
}