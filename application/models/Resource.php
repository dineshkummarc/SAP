<?php
/**
 * User: peaceman
 * Date: 4/18/12
 * Time: 10:00 PM
 */
class Application_Model_Resource extends \SAP\Model\AbstractModel
{
	public function setName($name)
	{
		$this->_set('name', $name);
	}

	public function getName()
	{
		return $this->_get('name');
	}
}