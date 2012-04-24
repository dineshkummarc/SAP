<?php
/**
 * User: peaceman
 * Date: 4/19/12
 * Time: 10:18 PM
 */
class Application_Model_ServerSetting2Server extends \SAP\Model\AbstractModel
{
	public function getSettingId()
	{
		return $this->_get('setting_id');
	}

	public function setSettingId($settingId)
	{
		$this->_set('setting_id', $settingId);
	}

	/**
	 * @return Application_Model_ServerSetting
	 */
	public function getSetting()
	{
		$serverSetting = $this->_getServerSettingMapper()->find($this->getSettingId());
		return $serverSetting;
	}

	public function getServerId()
	{
		return $this->_get('server_id');
	}

	public function setServerId($serverId)
	{
		$this->_set('server_id', $serverId);
	}

	public function getServer()
	{
		$server = $this->_getServerMapper()->find($this->getServerId());
		return $server;
	}

	public function getValue()
	{
		return $this->_getSerialized('value');
	}

	public function setValue($value)
	{
		$this->_setSerialized('value', $value);
	}

	protected function _getServerSettingMapper()
	{
		static $serverSettingMapper;
		if ($serverSettingMapper === null) {
			$serverSettingMapper = new Application_Model_ServerSettingMapper;
		}

		return $serverSettingMapper;
	}

	protected function _getServerMapper()
	{
		static $serverMapper;
		if ($serverMapper === null) {
			$serverMapper = new Application_Model_ServerMapper;
		}

		return $serverMapper;
	}
}