<?php

$router = $di->getRouter();

// Default route
$router->add('/', ['controller' => 'index', 'action' => 'index']);

// Define your routes here
$router->add('/user/login', ['controller' => 'user', 'action' => 'login']);
$router->add('/user/login/submit', ['controller' => 'user', 'action' => 'loginSubmit']);
$router->add('/user/register', ['controller' => 'user', 'action' => 'register']);
$router->add('/user/register/submit', ['controller' => 'user', 'action' => 'registerSubmit']);

// Set 404 paths
$router->notFound(['controller' => 'index', 'action' => 'route404']);

$router->handle();
