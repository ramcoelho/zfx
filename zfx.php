<?php

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)).'/');

require APPLICATION_PATH.'Zfx/Zfx.php';
require 'Zend/Db.php';
require 'Zend/Config/Ini.php';

$section = 'production';
$template = 'default';

if (isset($argv[2])) {
	$section = $argv[2];
}
if (isset($argv[3])) {
	$template = $argv[3];
}

$config = new Zend_Config_Ini('application/configs/application.ini', $section);
$db = Zend_Db::factory($config->resources->db);

$zfx = new Zfx($argv[1]);
$zfx->getTablesFromZendDb($db)
    ->runZfControllers()
    ->runZfDbTables()
    ->runZfModels()
    ->createModelsAutoproperties(APPLICATION_PATH.'templates/default_autoproperties.php')
    ->extendModelsFromAutoproperties()
    ->runZfMappers()
    ->generateMappersFromTemplate(APPLICATION_PATH.'templates/'.$template.'_mapper.php');

