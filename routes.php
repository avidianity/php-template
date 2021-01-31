<?php

use Libraries\Router;
use Models\User;

$router = Router::getInstance();

$router->get('/user', function () {
    $user1 = User::create(['username' => 'avidian', 'password' => 'my-pass']);
    $user2 = User::create(['username' => 'avidian', 'password' => 'my-pass']);

    echo dump($user1);
    echo dump($user2);

    User::deleteMany([$user1->id, $user2->id]);

    echo dump(User::find([$user1->id, $user2->id]));


    $user3 = User::create(['username' => 'avidian', 'password' => 'my-pass']);

    $user3->update(['username' => 'doe']);

    echo dump($user3);
});
