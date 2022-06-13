<?php

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
            ]
        ]
    )
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/../src')
    );
