<?php

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline'
        ],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'yoda_style' => false,
    ))
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/..')
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->name('*.php')
    );
