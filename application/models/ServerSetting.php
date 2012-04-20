<?php
/**
 * User: peaceman
 * Date: 4/19/12
 * Time: 10:02 PM
 */
class Application_Model_ServerSetting extends \SAP\Model\AbstractModel
{
	protected $_serverType;

	public function getName()
	{
		return $this->_get('name');
	}

	public function setName($name)
	{
		$this->_set('name', $name);
	}

	public function getDefaultValue()
	{
		return $this->_getSerialized('default_value');
	}

	public function setDefaultValue($value)
	{
		$this->_setSerialized('default_value', $value);
	}

	public function getServerTypeId()
	{
		return $this->_get('server_type_id');
	}

	public function setServerTypeId($serverTypeId)
	{
		$this->_set('server_type_id', $serverTypeId);
	}

	public function getServerType()
	{
		if ($this->_serverType === null) {
			$this->_serverType = $this->_getServerTypeMapper()->find($this->getServerTypeId());
		}

		return $this->_serverType;
	}

	protected function _getServerTypeMapper()
	{
		static $serverTypeMapper;
		if ($serverTypeMapper) {
			$serverTypeMapper = new Application_Model_ServerTypeMapper;
		}

		return $serverTypeMapper;
	}
}