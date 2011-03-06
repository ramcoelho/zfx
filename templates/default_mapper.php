<?php

class Application_Model_[class]Mapper
{
	protected $_dbTable;

	public function setDbTable($dbTable)
	{
		if (is_string($dbTable)){
			$dbTable = new $dbTable();
		}
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}

	public function getDbTable()
	{
		if (null === $this->_dbTable) {
			$this->setDbTable('Application_Model_DbTable_[class]');
		}
		return $this->_dbTable;
	}

	public function save(Application_Model_[class] $model)
	{
		$data = array([forcolumns]
			'[column_name]' => $model->[getter_name]()[last?:,][endforcolumns]
		);

		if (null === ($[primary_name] = $model->[primary_getter_name]())) {
			unset($data['[primary_name]']);
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array('[primary_name] = ?' => $[primary_name]));
		}
	}
	public function find($[primary_name], Application_Model_[class] $model)
	{
		$result = $this->getDbTable()->find($[primary_name]);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();
		$model[forcolumns]->[setter_name]($row->[column_name])[last?;:]
			[endforcolumns]
	}

	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_[class]();
			$entry[forcolumns]->[setter_name]($row->[column_name])[last?;:]
				[endforcolumns]
			$entries[] = $entry;
		}
		return $entries;
	}
}
