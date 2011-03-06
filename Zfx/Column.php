<?php

class Column
{
	protected $_name;
	
	public function __construct($name)
	{
		$this->setName($name);
	}
	public function getName()
	{
		return($this->_name);
	}
	public function setName($name)
	{
		$this->_name = $name;
	}
}
