<?php

use Libraries\Router;

$router = Router::getInstance();

$router->get('/', function () {
    return view('home');
});
