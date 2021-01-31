<?php

require __DIR__ . '/../helpers.php';
require __DIR__ . '/../bootstrap.php';

// parse url
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

require __DIR__ . '/../routes.php';

// Start app
$router->run($uri);
