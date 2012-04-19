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

	/**
	 * @return Application_Model_Resource[]
	 */
	public function getPermissionsByResource()
	{
		$toReturn = array();

		$permissions = $this->_getPermissionMapper()->fetchAll(array('role_id = ?' => $this->getId()), 'resource_id ASC');
		foreach ($permissions as $permission) {
			/**  @var $permission Application_Model_Permission*/
			$toReturn[$permission->getResourceId()][] = $permission;
		}

		return $toReturn;
	}

	protected function _getPermissionMapper()
	{
		static $permissionsMapper;
		if ($permissionsMapper === null) {
			$permissionsMapper = new Application_Model_PermissionMapper();
		}

		return $permissionsMapper;
	}
}