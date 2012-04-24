<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 6:52 PM
 */
class Application_Form_UserCreate extends \SAP\Form
{
	public function init()
	{
		$exclude = $this->_model !== null ? array('field' => 'id', 'value' => $this->_model->getId()) : null;

		$usernameField = new \SAP\Form\Element\Text('username');
		$usernameField->setLabel('Username')
			->setRequired(true)
			->setAllowEmpty(false)
			->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 16)))
			->addValidator(new Zend_Validate_Alnum())
			->addValidator(new Zend_Validate_Db_NoRecordExists(array('table' => 'user', 'field' => 'username', 'exclude' => $exclude)))
			->setValue($this->_model !== null ? $this->_model->getUsername() : null);

		$emailField = new \SAP\Form\Element\Text('email');
		$emailField->setLabel('Email')
			->setRequired(true)
			->setAllowEmpty(true)
			->addValidator(new Zend_Validate_EmailAddress())
			->addValidator(new Zend_Validate_StringLength(array('max' => 128)))
			->addValidator(new Zend_Validate_Db_NoRecordExists(array('table' => 'user', 'field' => 'email', 'exclude' => $exclude)))
			->setValue($this->_model !== null ? $this->_model->getEmail() : null);

		$submitButton = new Zend_Form_Element_Submit('send');
		$submitButton->setLabel('Create user');

		$this->addElements(array(
			$usernameField,
			$emailField,
			$submitButton,
		));
	}
}
