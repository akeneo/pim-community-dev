<?php

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@Symfony' => true,
            'method_argument_space' => [
                'on_multiline' => 'ensure_fully_multiline'
            ],
            'yoda_style' => false,
            'phpdoc_align' => false,
            'phpdoc_separation' => [],
        ]
    )
    ->setCacheFile('var/php_cs.performance_analytics.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/')
            ->in(__DIR__ . '/../src')
    );
