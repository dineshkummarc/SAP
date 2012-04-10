<?php
/**
 * User: peaceman
 * Date: 4/10/12
 * Time: 10:19 PM
 */
namespace SAP\Config\Writer;

class ScTrans extends \Zend_Config_Writer_FileAbstract
{
	public function render()
	{
		$configArray = $this->_config->toArray();
		$toReturn = '';

		foreach ($configArray as $key => $value) {
			$toReturn .= $key . '=' . $value . PHP_EOL;
		}

		return $toReturn;
	}
}
