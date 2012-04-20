<?php
/**
 * User: peaceman
 * Date: 4/20/12
 * Time: 8:14 PM
 */
class Application_Model_ServerType extends \SAP\Model\AbstractModel
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