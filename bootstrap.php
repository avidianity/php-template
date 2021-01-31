<?php

use Models\Model;

$configs = require_once __DIR__ . '/config.php';

$_ENV['CONFIGS'] = $configs;

importRecursive(['interfaces', 'traits']);

require_once __DIR__ . '/models/Model.php';

// Recursively import all php files in given folders
// NEVER IMPORT THE VIEWS FOLDER, use view() instead
importRecursive([
    'libraries',
    'controllers',
    'models',
    'relations',
]);

// Create database connection
$pdo = require_once __DIR__ . '/pdo.php';

// set default connection to finish setup
Model::setConnection($pdo);
