<?php

// Fetch configuration for database
$config = config('database');

// Create connection string
$dsn = $config['driver'] . ':' . 'dbname=' . $config['name'];
$dsn .= ';host=' . $config['host'];

// Create instance
$pdo = new PDO($dsn, $config['username'], $config['password']);

// Throw exceptions on any SQL error
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Emulate prepared statements in the database
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

return $pdo;
