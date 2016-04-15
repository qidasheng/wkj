<?php
define('ROOT',  str_replace("\\", '/',dirname(dirname(__FILE__))));
require ROOT.'/vendor/autoload.php';
$app = new \Sys\Core\Application();
$app->run(
	array(
		'controllerDir' => ROOT.'/app/Controllers',
		//'routeRule' => array (
		//	'rewrite' => array (),
		//	'regx' => array (),
		//),
	)
);
