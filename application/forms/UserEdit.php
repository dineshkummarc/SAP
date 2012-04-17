<?php
/**
 * User: peaceman
 * Date: 4/17/12
 * Time: 11:49 PM
 */
class Application_Form_UserEdit extends Application_Form_UserCreate
{
	public function init()
	{
		parent::init();

		$submitButton = $this->getElement('send');
		$submitButton->setLabel('Update user');
	}
}
