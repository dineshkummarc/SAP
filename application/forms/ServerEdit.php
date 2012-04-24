<?php
/**
 * User: peaceman
 * Date: 4/20/12
 * Time: 9:13 PM
 */
class Application_Form_ServerEdit extends Application_Form_ServerCreate
{
	/**
	 * @var Application_Model_Server
	 */
	protected $_model;

	public function init()
	{
		parent::init();

		$this->getElement('server_type_id')->setAttrib('disabled', 'disabled');
		$submitElement = $this->getElement('submit');
		$this->removeElement('submit');

		$this->addSubForm(new Application_Form_ServerSettings(array('model' => $this->_model)), 'settings');
		$submitElement->setLabel('Edit server');
		$this->addElement($submitElement);
	}

	public function isValid($data)
	{
		$data['server_type_id'] = $this->_model->getServerTypeId();
		return parent::isValid($data);
	}
}