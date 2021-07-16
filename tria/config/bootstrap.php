<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = new Dotenv(true);

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (is_array($env = @include dirname(__DIR__) . '/.env.local.php')) {
    $dotenv->populate($env);
} else {
    $path = dirname(__DIR__) . '/.env';
    $dotenv->loadEnv($path);
}

$_SERVER += $_ENV;
