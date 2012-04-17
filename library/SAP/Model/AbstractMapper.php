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
	public function setDbTable($dbTable)
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
		if (null === ($id = $model->getId())) {
			$data = $model->toArray();
			unset($data['id']);
			$model->preSave();

			$id = $this->getDbTable()->insert($data);
			$model->setId($id);

			$model->postSave();
		} else {
			$model->preUpdate();
			$data = $model->toArray();

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
		$model->setFromArray($data->toArray());
		return $model;
	}

	public function delete($where)
	{
		return $this->getDbTable()->delete($where);
	}

	/**
	 * @param string|null $order
	 * @param int|null $count
	 * @param int|null $offset
	 * @return array
	 */
	public function fetchAll($order = null, $count = null, $offset = null)
	{
		$result = $this->getDbTable()->fetchAll(null, $order, $count, $offset);
		$toReturn = array();

		foreach ($result as $row) {
			/** @var $row \Zend_Db_Table_Row_Abstract */
			/** @var $tmpModel \SAP\Model\AbstractModel */
			$tmpModel = new $this->_modelClass;
			$tmpModel->setFromArray($row->toArray());

			$toReturn[] = $tmpModel;
		}

		return $toReturn;
	}
}
