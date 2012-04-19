<?php
/**
 * User: peaceman
 * Date: 4/19/12
 * Time: 9:52 PM
 */
class Application_Model_Server extends \SAP\Model\AbstractModel
{
	public function getName()
	{
		return $this->_get('name');
	}

	public function setName($name)
	{
		$this->_set('name', $name);
	}
}