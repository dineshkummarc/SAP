<?php
/**
 * User: peaceman
 * Date: 4/18/12
 * Time: 9:51 PM
 */
class Application_Model_Role extends \SAP\Model\AbstractModel
{
	public function setName($name)
	{
		$this->_set('name', $name);
	}

	public function getName()
	{
		return $this->_get('name');
	}

	public function setServerId($serverId)
	{
		$this->_set('server_id', $serverId);
	}

	public function getServerId()
	{
		return $this->_get('server_id');
	}
}