<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route,
	Nette\Application\Routers\RouteList,
	Nette\Diagnostics\Debugger,
	\Nette\Forms\Container;

date_default_timezone_set('UTC');

// Load Nette Framework or autoloader generated by Composer
require __DIR__ . '/../vendor/autoload.php';

// Enable Nette Debugger for error visualisation & logging
Debugger::$logDirectory = __DIR__ . '/../log';
Debugger::$strictMode = TRUE;
Debugger::$email = 'it@hotelquickly.com';

// $debugArray is defined in setup-debug-mode.php
if (function_exists('isDebugMode') AND isDebugMode($debugArray) == true) {
	Debugger::enable(Debugger::DEVELOPMENT);
} else {
	Debugger::enable();
}
// Configure application
$configurator = new \Nette\Configurator();
if (function_exists('isDebugMode') AND isDebugMode($debugArray) == true) {
	$configurator->setDebugMode(true);
}

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon', false);
$configurator->addConfig(__DIR__ . '/config/config.local.neon', false);

$container = $configurator->createContainer();

if ($configurator->isDebugMode()) {
	define('ENVIRONMENT', 'DEVELOPMENT');
} else {
	define('ENVIRONMENT', 'PRODUCTION');
	$container->application->catchExceptions = true;
}

// Configure and run the application!
$container->application->run();
