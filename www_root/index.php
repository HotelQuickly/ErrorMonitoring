<?php

// Set up Debug Mode
$debugArray = array();
if (is_file('setup-debug-mode.php')) {
	require 'setup-debug-mode.php';
} else {
	$debugArray = array();
}

// Set up Maintenance
$maintenance = false;
if (is_file('setup-maintenance.php')) {
	require 'setup-maintenance.php';
} else {
	$maintenance = false;
}
if ($maintenance && !in_array($_SERVER['REMOTE_ADDR'], $debugArray)) {
	require '.maintenance.html';
	exit;
}

// absolute filesystem path to this web root
define('WWW_DIR', __DIR__);

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// load bootstrap file
require APP_DIR . '/bootstrap.php';