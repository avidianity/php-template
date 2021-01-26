<?php

function view($path)
{
    if (!file_exists(__DIR__ . '/views/' . $path . '.php')) {
        throw new LogicException($path . ' does not exist in views.');
    }
    return require_once __DIR__ . '/views/' . $path . '.php';
}

function asset($path)
{
    return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $path;
}
