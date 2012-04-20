<?php
/**
 * User: peaceman
 * Date: 4/20/12
 * Time: 8:55 PM
 */
class Application_Form_ServerCreate extends \SAP\Form
{
	/**
	 * @var array|null
	 */
	protected $_serverTypes;

	public function init()
	{
		$nameField = new Zend_Form_Element_Text('name');
		$nameField->setLabel('Name')
			->setRequired(true)
			->setAllowEmpty(false)
			->addValidator(new Zend_Validate_StringLength(array('max' => 64)))
			->setValue($this->_model !== null ? $this->_model->getName() : null);

		$typeField = new Zend_Form_Element_Select('server_type_id');
		$typeField->setLabel('Server Type')
			->setRequired(true)
			->setAllowEmpty(false)
			->setMultiOptions($this->getServerTypes())
			->setValue($this->_model !== null ? $this->_model->getServerTypeId() : null);

		$submitButton = new Zend_Form_Element_Submit('submit');
		$submitButton->setLabel('Create server');

		$this->addElements(array(
			$nameField,
			$typeField,
			$submitButton,
		));
	}

	public function setServerTypes(array $serverTypes)
	{
		$this->_serverTypes = $serverTypes;
	}

	public function getServerTypes()
	{
		return $this->_serverTypes;
	}

	public function isValid($data)
	{
		$data['server_type_id'] = $this->_model->getServerTypeId();
		return parent::isValid($data);
	}
}