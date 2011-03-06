<?php

class Table
{
	protected $_name;
	protected $_columns;
	protected $_primary_key;
	
	public function __construct($name)
	{
		$this->_columns = array();
		$this->_name = $name;
	} 
	public function getName()
	{
		return($this->_name);
	}
	public function setName($name)
	{
		$this->_name = $name;
	}
	public function fetchColumns()
	{
		return($this->_columns);
	}
	public function addColumn($column)
	{
		$this->_columns[] = $column;
	}
	public function setPrimaryKey($column_name)
	{
		$this->_primary_key = $column_name;
	}
	public function getPrimaryKey()
	{
		return($this->_primary_key);
	}
}
