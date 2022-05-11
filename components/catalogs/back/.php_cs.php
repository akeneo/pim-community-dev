<?php

/** @var PhpCsFixer\Config $config */
$config = require __DIR__ . '/../../../.php_cs.php';

$rules = $config->getRules();

$rules['native_function_invocation'] = [
    'include' => ['@internal'],
    'strict' => false,
];

$rules['php_unit_method_casing'] = [
    'case' => 'camel_case',
];

$config
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setCacheFile('var/php_cs_catalogs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__)
    );

return $config;
