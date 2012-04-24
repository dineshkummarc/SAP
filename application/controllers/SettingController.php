<?php
/**
 * User: peaceman
 * Date: 4/24/12
 * Time: 10:30 PM
 */
class SettingController extends \SAP\Controller\Action
{
	public function defaultStreamSettingsAction()
	{
		$defaultStreamSettings = $this->_getServerSettingMapper()->fetchAll();
		$this->view->assign('settings', $defaultStreamSettings);
	}

	public function editDefaultStreamSettingAction()
	{
		$serverSettingModel = $this->_getSettingModelFromRequestOrRedirectToListing();
		$editServerSettingForm = new Application_Form_ServerSettingEdit(array('model' => $serverSettingModel));

		/** @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ($request->isPost() && $editServerSettingForm->isValid($request->getPost())) {
			$serverSettingModel->setFromArray($editServerSettingForm->getValues());
			$this->_getServerSettingMapper()->save($serverSettingModel);

			$this->getFlashMessenger()->addSuccessMessage(sprintf('successfully edited default stream setting (%s)', $serverSettingModel->getName()));
			$this->_redirect($this->url('default-stream-settings'), array('exit' => true));
		}

		$this->view->assign(array(
			'form' => $editServerSettingForm,
			'setting' => $serverSettingModel,
		));
	}

	protected function _getSettingModelFromRequestOrRedirectToListing()
	{
		$id = $this->_getParam('id');
		$settingModel = $this->_getServerSettingMapper()->find($id);

		if (!$settingModel) {
			$this->getFlashMessenger()->addErrorMessage(sprintf('unknown setting with id %d', $id));
			$this->_redirect($this->url('default-stream-settings'), array('exit' => true));
		}

		return $settingModel;
	}

	protected function _getServerSettingMapper()
	{
		static $serverSettingMapper;
		if ($serverSettingMapper === null) {
			$serverSettingMapper = new Application_Model_ServerSettingMapper;
		}

		return $serverSettingMapper;
	}
}