<?php

use MongoDB\Client;
use Settings\DBSettings;

define('ROOT', realpath(__DIR__ . '/../') . '/');

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/settings.php';

$app = new \Klein\Klein();

$connection = new Client("mongodb://" . DBSettings::$db_host . ":" . DBSettings::$db_port);

App\Routes::init($app, $connection);

$app->dispatch();
