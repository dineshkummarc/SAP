<?php
/**
 * User: peaceman
 * Date: 4/19/12
 * Time: 9:16 PM
 */
class Application_Model_Permission extends \SAP\Model\AbstractModel
{
	protected $_resource;
	protected $_role;

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

	public function getResource()
	{
		if ($this->_resource === null) {
			$this->_resource = $this->_getResourceMapper()->find($this->getResourceId());
		}

		return $this->_resource;
	}

	public function getRole()
	{
		if ($this->_role === null) {
			$this->_role = $this->_getResourceMapper()->find($this->getRoleId());
		}

		return $this->_role;
	}

	protected function _getResourceMapper()
	{
		static $resourceMapper;
		if ($resourceMapper === null) {
			$resourceMapper = new Application_Model_ResourceMapper();
		}

		return $resourceMapper;
	}

	protected function _getRoleMapper()
	{
		static $roleMapper;
		if ($roleMapper === null) {
			$roleMapper = new Application_Model_RoleMapper();
		}

		return $roleMapper;
	}
}