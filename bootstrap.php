<?php

use Models\Model;

$configs = require_once __DIR__ . '/config.php';

$_ENV['CONFIGS'] = $configs;

// Recursively import all php files in given folders
// NEVER IMPORT THE VIEWS FOLDER, use view() instead
importRecursive([
    'interfaces',
    'traits',
    'libraries',
    'controllers',
    'models',
]);

// Create database connection
$pdo = require_once __DIR__ . '/pdo.php';

// set default connection to finish setup
Model::setConnection($pdo);
