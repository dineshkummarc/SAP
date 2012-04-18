<?php
/**
 * User: peaceman
 * Date: 4/18/12
 * Time: 10:27 PM
 */
class Application_Form_RoleCreate extends \SAP\Form
{
	public function init()
	{
		$nameField = new Zend_Form_Element_Text('name');
		$nameField->setLabel('Name')
			->setRequired(true)
			->setAllowEmpty(false)
			->addValidator(new Zend_Validate_StringLength(array('max' => 64)))
			->setValue($this->_model !== null ? $this->_model->getName() : null);

		$submitButton = new Zend_Form_Element_Submit('submit');
		$submitButton->setLabel('Create user');

		$this->addElements(array(
			$nameField,
			$submitButton,
		));
	}
}