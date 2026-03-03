<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@Symfony' => true,
            'linebreak_after_opening_tag' => true,
            'ordered_imports' => true,
            'method_argument_space' => [
                'on_multiline' => 'ensure_fully_multiline',
            ],
            'no_unused_imports' => true,
            'trailing_comma_in_multiline' => [
                'elements' => ['arrays', 'arguments', 'parameters'],
            ],
            'phpdoc_align' => [
                'align' => 'left',
            ],
            'php_unit_method_casing' => ['case' => 'snake_case'],
        ],
    )
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/Acceptance')
            ->in(__DIR__ . '/Integration')
    );
