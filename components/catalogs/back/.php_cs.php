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

$rules['single_quote'] = [
    'strings_containing_single_quote_chars' => true,
];

$rules['cast_spaces'] = [
    'space' => 'single',
];

$rules['binary_operator_spaces'] = [
    'default' => 'single_space',
];

$rules['trailing_comma_in_multiline'] = [
    'elements' => ['arguments', 'arrays', 'match', 'parameters'],
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
