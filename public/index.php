<?php
use core\App;
use controllers\UserController;

 require_once('../vendor/autoload.php');

$app = new App();

$app->get('/users', [UserController::class, 'profile']);

$app->post('/users', [UserController::class, 'profile']);

$app->run();
