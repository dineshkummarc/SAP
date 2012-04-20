<?php
/**
 * User: peaceman
 * Date: 4/20/12
 * Time: 9:13 PM
 */
class Application_Form_ServerEdit extends Application_Form_ServerCreate
{
	public function init()
	{
		parent::init();

		$this->getElement('server_type_id')->setAttrib('disabled', 'disabled');
		$this->getElement('submit')->setLabel('Edit server');
	}
}