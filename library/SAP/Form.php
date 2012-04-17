<?php
/**
 * User: peaceman
 * Date: 4/18/12
 * Time: 12:06 AM
 */
namespace SAP;

class Form extends \Zend_Form
{
	/**
	 * @var \SAP\Model\AbstractModel
	 */
	protected $_model;

	public function getModel()
	{
		return $this->_model;
	}

	public function setModel(\SAP\Model\AbstractModel $model)
	{
		$this->_model = $model;
	}
}
