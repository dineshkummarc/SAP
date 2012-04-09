<?php
/**
 * User: peaceman
 * Date: 4/9/12
 * Time: 11:55 AM
 */
namespace Daemon;

class Config
{
	/**
	 * @var array
	 */
	protected $_configuration;

	/**
	 * @param array $configuration
	 */
	public function __construct(array $configuration)
	{
		$this->_configuration = $configuration;
	}

	/**
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get($key = null, $default = null)
	{
		return $this->getOption($key, $default);
	}

	/**
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function getOption($key = null, $default = null)
	{
		if ($key !== null) {
			return $this->_getConfigValueByKey($key, $default);
		}

		return $this->_configuration;
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function _getConfigValueByKey($key, $default)
	{
		$keys = explode('.', $key);
		$data = $this->_configuration;

		foreach ($keys as $key) {
			if (!is_array($data) || !array_key_exists($key, $data)) {
				return $default;
			}

			$data = $data[$key];
		}

		return $data;
	}
}
 
