<?php

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline'
        ],
    ))
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/..')
            ->exclude('tests')
            ->name('*.php')
    );
