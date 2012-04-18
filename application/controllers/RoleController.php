<?php
/**
 * User: peaceman
 * Date: 4/18/12
 * Time: 9:50 PM
 */
class RoleController extends \SAP\Controller\Action
{
	public function indexAction()
	{
		$roleMapper = $this->_getRoleMapper();
		$roles = $roleMapper->fetchAll();
		$this->view->assign('roles', $roles);
	}

	protected function _getRoleMapper()
	{
		static $roleMapper;
		if ($roleMapper === null) {
			$roleMapper = new Application_Model_RoleMapper;
		}

		return $roleMapper;
	}

	public function createAction()
	{
		$roleCreateForm = new Application_Form_RoleCreate;

		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ($request->isPost() && $roleCreateForm->isValid($request->getPost())) {
			$model = new Application_Model_Role;
			$model->setFromArray($roleCreateForm->getValues());
			$this->_getRoleMapper()->save($model);

			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully created role with id %d', $model->getId()));
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		$this->view->assign('form', $roleCreateForm);
	}

	public function editAction()
	{
		$roleModel = $this->_getRoleModelFromRequestOrRedirectToListing();
		$roleEditForm = new Application_Form_RoleEdit(array('model' => $roleModel));

		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ($request->isPost() && $roleEditForm->isValid($request->getPost())) {
			$roleModel->setFromArray($roleEditForm->getValues());
			$this->_getRoleMapper()->save($roleModel);

			$this->getFlashMessenger()->addSuccessMessage(sprintf('updated role %s', $roleModel->getName()));
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		$this->view->assign('form', $roleEditForm);
		$this->view->assign('role', $roleModel);
	}

	protected function _getRoleModelFromRequestOrRedirectToListing()
	{
		$id = (int)$this->_getParam('id');
		$roleModel = $this->_getRoleMapper()->find($id);

		if (!$roleModel) {
			$this->getFlashMessenger()->addErrorMessage(sprintf('couldnt find role with id %d', $id));
			$this->_redirect($this->url('index'), array('exit' => true));
		}

		return $roleModel;
	}

	public function deleteAction()
	{
		$roleModel = $this->_getRoleModelFromRequestOrRedirectToListing();

		$deleted = $this->_getRoleMapper()->delete(array('id = ?' => $roleModel->getId()));
		if ($deleted) {
			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully deleted role %s', $roleModel->getName()));
			$this->_redirect($this->url('index'), array('exit' => true));
		}
	}
}
