<?php

/*
 * Point to application autoload to resolve dependency on OroSecurityBundle
 * Change to __DIR__ . '/../vendor/autoload.php' after custom composer repository
 * will be configured.
 */
$file = __DIR__ . '/../../../../../vendor/autoload.php';

if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $file;
