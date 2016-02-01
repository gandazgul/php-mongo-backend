<?php

use MongoDB\Client;

define('ROOT', realpath(__DIR__ . '/../') . '/');

require_once ROOT . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(ROOT);
$dotenv->load();

$app = new \Klein\Klein();

$connection = new Client("mongodb://" . getenv('DB_HOST') . ":" . getenv('DB_PORT'));

App\Routes::init($app, $connection);

$app->dispatch();