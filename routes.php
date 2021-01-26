<?php

$router->get('/', function () {
    return view('home');
});

$router->get('/about', [AboutController::class, 'index']);
