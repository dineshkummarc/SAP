<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 4:25 PM
 */
 
class Application_Form_UserLogin extends Zend_Form
{
	public function init()
	{
		$usernameField = new \SAP\Form\Element\Text('username');
		$usernameField->setLabel('Username')
			->setRequired(true)
			->setAllowEmpty(false)
			->setDescription('Your personal username');

		$passwordField = new Zend_Form_Element_Password('password');
		$passwordField->setLabel('Password')
			->setRequired(true)
			->setAllowEmpty(false)
			->setDescription('Your personal password');

		$submitButton = new Zend_Form_Element_Submit('login');
		$submitButton->setLabel('Login');

		$this->addElements(array(
			$usernameField,
			$passwordField,
			$submitButton,
		));
	}
}
