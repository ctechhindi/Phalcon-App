<?php

$router = $di->getRouter();

// Default route
$router->add('/', ['controller' => 'index', 'action' => 'index']);

// Define your routes here
$router->add('/user/login', ['controller' => 'user', 'action' => 'login']);
$router->add('/user/login/submit', ['controller' => 'user', 'action' => 'loginSubmit']);
$router->add('/user/register', ['controller' => 'user', 'action' => 'register']);
$router->add('/user/register/submit', ['controller' => 'user', 'action' => 'registerSubmit']);
$router->add('/user/profile', ['controller' => 'user', 'action' => 'profile']);
$router->add('/user/logout', ['controller' => 'user', 'action' => 'logout']);

// Article Routes
$router->add('/article/create', ['controller' => 'article', 'action' => 'create']);
$router->add('/article/create/submit', ['controller' => 'article', 'action' => 'createSubmit']);
$router->add('/article/manage', ['controller' => 'article', 'action' => 'manage']);
$router->add('/article/edit', ['controller' => 'article', 'action' => 'edit']);
$router->add('/article/edit/submit', ['controller' => 'article', 'action' => 'editSubmit']);
$router->add('/article/delete', ['controller' => 'article', 'action' => 'delete']);


$router->handle();
