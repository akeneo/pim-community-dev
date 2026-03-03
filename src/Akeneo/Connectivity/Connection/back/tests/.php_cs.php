<?php

use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;

/** @var PhpCsFixer\Config $config */
$config = require __DIR__ . '/../../../../../../.php_cs.php';

$rules = $config->getRules();

$rules['native_function_invocation'] = [
    'include' => [NativeFunctionInvocationFixer::SET_INTERNAL],
    'strict' => false,
];

$config
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setCacheFile('var/php_cs_connectivity.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/../')
    );

return $config;
