<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

$bootstrapApp = new \urukalo\CH\bootstrapApp(new \Slim\Slim());

$bootstrapApp->connectToViewEngine();
$db = $bootstrapApp->connectToDatabase();

$bootstrapApp->loadRoutes();

$bootstrapApp->runApp();
