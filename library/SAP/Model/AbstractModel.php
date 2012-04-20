<?php
/**
 * User: peaceman
 * Date: 4/16/12
 * Time: 9:50 PM
 */
namespace SAP\Model;

class AbstractModel
{
	/**
	 * @var array
	 */
	private $_data = array();

	/**
	 * Iterates over the given array and calls set methods
	 *
	 * @param array $array
	 */
	public function setFromArray(array $array)
	{
		foreach ($array as $key => $value) {
			$methodName = $this->_generateSetMethodNameForProperty($key);
			if (!method_exists($this, $methodName)) {
				$msg = sprintf('Couldnt find method %s on object of type %s', $methodName, get_class($this));
				throw new \InvalidArgumentException($msg);
			}

			$this->$methodName($value);
		}
	}

	/**
	 * Splits the given key by the delimiter _, converts the first
	 * character of each word to upper case and returns all parts
	 * concatenated.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _generateSetMethodNameForProperty($key)
	{
		static $delimiter = '_';

		if (!is_string($key)) {
			$msg = sprintf('expected a string got %s', $key);
			throw new \InvalidArgumentException($msg);
		}

		$keyParts = explode($delimiter, $key);
		return 'set' . implode('', array_map('ucfirst', $keyParts));
	}

	/**
	 * Return the internal data structure
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_data;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->_get('id');
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		return $this->_set('id', $id);
	}

	/**
	 * Saves a value in the internal data structure
	 *
	 * Should only be called from set* methods
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	protected function _set($name, $value)
	{
		$this->_data[$name] = $value;
	}

	/**
	 * @param string $name
	 * @param string|\DateTime $value
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	protected function _setDate($name, $value)
	{
		if ($value !== null) {
			if (is_string($value)) {
				$value = new \DateTime($value);
			}

			if (!$value instanceof \DateTime) {
				throw new \InvalidArgumentException('expected a string or a datetime object');
			}

			$value = $value->format('Y-m-d H:i:s');
		}

		$this->_set($name, $value);
	}

	/**
	 * Returns a value from the internal data structure
	 *
	 * Should only be called from get* methods
	 *
	 * @param string $name
	 * @return mixed
	 */
	protected function _get($name)
	{
		return isset($this->_data[$name]) ? $this->_data[$name] : null;
	}

	/**
	 * @param string $name
	 * @return \DateTime
	 */
	protected function _getDate($name)
	{
		$value = $this->_get($name);
		if ($value === null) {
			return null;
		}

		$value = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
		return $value !== false ? $value : null;
	}

	protected function _setSerialized($name, $unserializedData)
	{
		$this->_set($name, serialize($unserializedData));
	}

	protected function _getSerialized($name)
	{
		return unserialize($this->_get($name));
	}

	public function preUpdate() {}
	public function postUpdate() {}
	public function preSave() {}
	public function postSave() {}
}
