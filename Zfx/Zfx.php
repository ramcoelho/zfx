<?php

require APPLICATION_PATH.'Zfx/Table.php';
require APPLICATION_PATH.'Zfx/Column.php';

class Zfx
{
	protected $_tables;
	protected $_zf_executable;
	
	public function __construct($zf_executable)
	{
		$this->_zf_executable = $zf_executable;
	}
	public function getClassFromPhysical($physical)
	{
		$class = '';
		$parts = explode('_', $physical);
		foreach($parts as $part) {
			$class .= ucfirst($part);
		}
		return($class);
	}
	public function generateMappersFromTemplate($template)
	{
		$ftemplate = file_get_contents($template);
		preg_match_all('/\[forcolumns\](.*)\[endforcolumns\]/msU', $ftemplate, $loops);
		$replace_these = $loops[0];
		$loop_templates = $loops[1];
		$is_last_pattern = '/\[last\?([^:])*:([^]])*\]/';
		 
		foreach($this->_tables as $table) {
			$loops_replaced = array();
			$_ftemplate = $ftemplate;
			
			$table_name = $table->getName();
			$class = $this->getClassFromPhysical($table_name);
			$columns = $table->fetchColumns();
			$primary_name = $table->getPrimaryKey();
			$primary_setter_name = 'set'.$this->getClassFromPhysical($primary_name);
			$primary_getter_name = 'get'.$this->getClassFromPhysical($primary_name);
			$_ftemplate = 
				str_replace('[class]', $class,
				str_replace('[primary_name]', $primary_name,
				str_replace('[primary_getter_name]', $primary_getter_name,
				str_replace('[primary_setter_name]', $primary_setter_name,
				$_ftemplate
			))));
			foreach($columns as $column) {
				$is_last = (next($columns) === false);
				$column_name = $column->getName();
				$setter_name = 'set'.$this->getClassFromPhysical($column_name);
				$getter_name = 'get'.$this->getClassFromPhysical($column_name);

				foreach($loop_templates as $loop_id => $loop_template) {
					$replaced = 
						str_replace('[column_name]', $column_name,
						str_replace('[getter_name]', $getter_name,
						str_replace('[setter_name]', $setter_name,
						$loop_template
					)));
					
					$is_last_replacement = ($is_last?'\1':'\2');
					$replaced = preg_replace($is_last_pattern, $is_last_replacement, $replaced);
					if (!isset($loops_replaced[$loop_id])) {
						$loops_replaced[$loop_id] = '';
					}
					$loops_replaced[$loop_id] = $loops_replaced[$loop_id].$replaced;
				}
			}
			foreach($replace_these as $id => $replace_this)	{
				$_ftemplate = str_replace($replace_this, $loops_replaced[$id], $_ftemplate);
			}
			$file_name = 'application/models/'.$class.'Mapper.php';
			if (!file_exists($file_name)) {
				file_put_contents($file_name, $_ftemplate);
			}

		}
		return($this);
	}
	public function runZfControllers()
	{
		$zf_executable = $this->_zf_executable;
		foreach($this->_tables as $table) {
			$table_name = $table->getName();
			$controller_name = $this->getClassFromPhysical($table_name);
			`$zf_executable create controller "$controller_name"`;
		}
		return($this);
	}
	public function runZfDbTables()
	{
		$zf_executable = $this->_zf_executable;
		foreach($this->_tables as $table) {
			$table_name = $table->getName();
			$model_name = $this->getClassFromPhysical($table_name);
			`$zf_executable create db-table "$model_name" "$table_name"`;
		}
		return($this);
	}
	public function runZfModels()
	{
		$zf_executable = $this->_zf_executable;
		foreach($this->_tables as $table) {
			$table_name = $table->getName();
			$model_name = $this->getClassFromPhysical($table_name);
			`$zf_executable create model "$model_name"`;
		}
		return($this);
	}
	public function createModelsAutoproperties($template)
	{
		$zf_executable = $this->_zf_executable;
		$file_name = 'application/models/Autopropeties.php';
		if (!file_exists($file_name)) {
			`$zf_executable create model "Autoproperties"`;
			copy($template, $file_name);
		}
		return($this);
	}
	public function extendModelsFromAutoproperties()
	{
		foreach($this->_tables as $table) {
			$table_name = $table->getName();
			$model_name = $this->getClassFromPhysical($table_name);
			
			$original = 'class Application_Model_'.$model_name;
			$add = ' extends Application_Model_Autoproperties';
			$extended = 'class Application_Model_'.$model_name.$add;
			
			$file_name = 'application/models/'.$model_name.'.php';
			$fmodel = file_get_contents($file_name);
			$fmodel = str_replace($original, $extended, $fmodel);
			$fmodel = str_replace($add.$add, $add, $fmodel);
			file_put_contents($file_name, $fmodel);
		}
		return($this);
	}
	public function runZfMappers()
	{
		$zf_executable = $this->_zf_executable;
		foreach($this->_tables as $table) {
			$table_name = $table->getName();
			$model_name = $this->getClassFromPhysical($table_name).'Mapper';
			$file_name = 'application/models/'.$model_name.'.php';
			if (!file_exists($file_name)) {
				`$zf_executable create model "$model_name"`;
				unlink($file_name);
			}
		}
		return($this);
	}
	public function getTablesFromZendDb($db)
	{
		$table_list = $db->listTables();
		foreach($table_list as $table_name) {
			$table = new Table($table_name);
			$this->_tables[$table_name] = $table;
			$column_list = $db->describeTable($table_name);
			foreach($column_list as $column_name => $attributes) {
				if ($attributes['PRIMARY']) {
					$table->setPrimaryKey($column_name);
				}
				$table->addColumn(new Column($column_name));
			}
		}
		return($this);
	}
}
