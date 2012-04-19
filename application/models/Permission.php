<?php
/**
 * User: peaceman
 * Date: 4/19/12
 * Time: 9:16 PM
 */
class Application_Model_Permission extends \SAP\Model\AbstractModel
{
	public function getRoleId()
	{
		return $this->_get('role_id');
	}

	public function setRoleId($roleId)
	{
		$this->_set('role_id', $roleId);
	}

	public function getResourceId()
	{
		return $this->_get('resource_id');
	}

	public function setResourceId($resourceId)
	{
		$this->_set('resource_id', $resourceId);
	}

	public function getPermission()
	{
		return $this->_get('permission');
	}

	public function setPermission($permission)
	{
		$this->_set('permission', $permission);
	}
}