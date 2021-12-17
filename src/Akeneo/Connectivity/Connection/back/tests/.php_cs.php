<?php

/** @var PhpCsFixer\Config $config */
$config = require __DIR__ . '/../../../../../../.php_cs.php';

$config
    ->setCacheFile('var/php_cs_connectivity.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/../')
    );

return $config;
