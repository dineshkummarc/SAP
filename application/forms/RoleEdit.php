<?php
/**
 * User: peaceman
 * Date: 4/18/12
 * Time: 10:32 PM
 */
class Application_Form_RoleEdit extends Application_Form_RoleCreate
{
	public function init()
	{
		parent::init();

		$this->getElement('submit')
			->setLabel('Edit user');
	}
}