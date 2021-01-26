<?php

require '../helpers.php';
require '../Router.php';


// recursively import all controllers if any
$dir = new DirectoryIterator(__DIR__ . '/../controllers');

foreach ($dir as $file) {
    $filename = $file->getFilename();
    if (in_array($filename, ['..', '.'])) {
        continue;
    }
    $path = __DIR__ . '/../controllers/' . $filename;
    if (file_exists($path)) {
        require_once $path;
    }
}

// parse url
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$router = new Router();

require __DIR__ . '/../routes.php';

$router->run($uri);
