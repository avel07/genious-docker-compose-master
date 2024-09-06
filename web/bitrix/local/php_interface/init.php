<?php

// composer
require_once __DIR__ . "/../../bitrix/vendor/autoload.php";

// dotenv если запустили не через docker
if (file_exists(__DIR__ . "/../../../../.env")) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../../../../");
    $dotenv->load();
}

// events
include_once "events.php";
