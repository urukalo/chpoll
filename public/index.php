<?php

require_once __DIR__ . '/../vendor/autoload.php';

$bootstrapApp = new \urukalo\CH\bootstrapApp(new \Slim\Slim());

$bootstrapApp->connectToTwig();
$db = $bootstrapApp->connectToDatabase(new \Illuminate\Database\Capsule\Manager());
$bootstrapApp->connectAuth((new \Cartalyst\Sentinel\Native\Facades\Sentinel())->getSentinel());

$bootstrapApp->loadRoutes();
//$bootstrapApp->initRules();

$bootstrapApp->runApp();

