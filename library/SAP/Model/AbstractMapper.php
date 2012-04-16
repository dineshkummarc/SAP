<?php
/**
 * User: peaceman
 * Date: 4/16/12
 * Time: 10:17 PM
 */
namespace SAP\Model;

class AbstractMapper
{
	protected $_dbTable;
	protected $_dbTableClass;
	protected $_modelClass;

	/**
	 * @param \Zend_Db_Table_Abstract $dbTable
	 * @return AbstractMapper
	 * @throws \Exception
	 */
	public function setDbTable(\Zend_Db_Table_Abstract $dbTable)
	{
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}

		if (!$dbTable instanceof \Zend_Db_Table_Abstract) {
			throw new \Exception('Invalid table data gateway provided');
		}

		$this->_dbTable = $dbTable;
		return $this;
	}

	/**
	 * @return \Zend_Db_Table_Abstract
	 */
	public function getDbTable()
	{
		if ($this->_dbTable === null) {
			$this->setDbTable($this->_dbTableClass);
		}

		return $this->_dbTable;
	}

	/**
	 * @param AbstractModel $model
	 */
	public function save(\SAP\Model\AbstractModel $model)
	{
		$data = $model->toArray();

		if (null === ($id = $model->getId())) {
			unset($data['id']);
			$model->preSave();
			$this->getDbTable()->insert($data);
			$model->postSave();
		} else {
			$model->preUpdate();
			$this->getDbTable()->update($data, array('id = ?' => $id));
			$model->postUpdate();
		}
	}

	/**
	 * @param int $id
	 * @return AbstractModel
	 */
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) === 0) {
			return;
		}

		$data = $result->current();
		/** @var $model AbstractModel */
		$model = new $this->_modelClass();
		$model->setFromArray($data);
		return $model;
	}
}
