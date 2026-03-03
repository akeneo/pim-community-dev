<?php

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@PSR2' => true,
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
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->notName('*EndToEnd.php')
            ->name('*.php')
    );
