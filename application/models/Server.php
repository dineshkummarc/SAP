<?php
/**
 * User: peaceman
 * Date: 4/19/12
 * Time: 9:52 PM
 */
class Application_Model_Server extends \SAP\Model\AbstractModel
{
	/**
	 * @var Application_Model_ServerType
	 */
	protected $_serverType;

	/**
	 * @var Application_Model_ServerSetting2Server[]
	 */
	protected $_serverSettings;

	public function getName()
	{
		return $this->_get('name');
	}

	public function setName($name)
	{
		$this->_set('name', $name);
	}

	public function getServerTypeId()
	{
		return $this->_get('server_type_id');
	}

	public function setServerTypeId($serverTypeId)
	{
		$this->_set('server_type_id', $serverTypeId);
	}

	/**
	 * @return Application_Model_ServerType
	 */
	public function getServerType()
	{
		if ($this->_serverType === null) {
			$this->_serverType = $this->_getServerTypeMapper()->find($this->getServerTypeId());
		}

		return $this->_serverType;
	}

	/**
	 * @param string $name
	 * @return Application_Model_ServerSetting2Server|null
	 * @throws InvalidArgumentException
	 */
	public function getServerSettingByName($name)
	{
		if (!is_string($name)) {
			throw new InvalidArgumentException('expected a string got ' . gettype($name));
		}

		$allServerSettings = $this->getServerSettings();
		foreach ($allServerSettings as $serverSetting) {
			if ($serverSetting->getSetting()->getName() === $name) {
				return $serverSetting;
			}
		}

		return null;
	}

	/**
	 * @return Application_Model_ServerSetting2Server[]
	 */
	public function getServerSettings()
	{
		if ($this->_serverSettings === null) {
			$serverSettings = $this->_getServerSetting2ServerMapper()->fetchAll(array(
				'server_id = ?' => $this->getId()
			));

			$allPossibleServerSettings = $this->_getServerSettingMapper()->fetchAll(array(
				'server_type_id = ? OR server_type_id IS NULL' => $this->getServerTypeId(),
			));
			$allPossibleServerSettingIds = array_map(function($serverSetting) {
				/** @var $serverSetting Application_Model_ServerSetting */
				return $serverSetting->getId();
			}, $allPossibleServerSettings);

			$serverSettingIds = array_map(function($serverSetting) {
				/** @var $serverSetting Application_Model_ServerSetting2Server */
				return $serverSetting->getSettingId();
			}, $serverSettings);

			$missingServerSettingIds = array_diff($allPossibleServerSettingIds, $serverSettingIds);
			foreach ($missingServerSettingIds as $missingServerSettingId) {
				$serverSetting = new Application_Model_ServerSetting2Server();
				$serverSetting->setFromArray(array(
					'server_id' => $this->getId(),
					'setting_id' => $missingServerSettingId,
				));

				$serverSettings[] = $serverSetting;
			}

			$this->_serverSettings = $serverSettings;
		}

		return $this->_serverSettings;
	}

	protected function _getServerSettingMapper()
	{
		static $serverSettingMapper;
		if ($serverSettingMapper === null) {
			$serverSettingMapper = new Application_Model_ServerSettingMapper();
		}

		return $serverSettingMapper;
	}

	/**
	 * @return Application_Model_ServerSetting2ServerMapper
	 */
	protected function _getServerSetting2ServerMapper()
	{
		static $serverSetting2ServerMapper;
		if ($serverSetting2ServerMapper === null) {
			$serverSetting2ServerMapper = new Application_Model_ServerSetting2ServerMapper;
		}

		return $serverSetting2ServerMapper;
	}

	/**
	 * @return Application_Model_ServerTypeMapper
	 */
	protected function _getServerTypeMapper()
	{
		static $serverTypeMapper;
		if ($serverTypeMapper === null) {
			$serverTypeMapper = new Application_Model_ServerTypeMapper;
		}

		return $serverTypeMapper;
	}
}