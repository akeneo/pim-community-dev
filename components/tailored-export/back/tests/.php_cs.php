<?php

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@PSR2' => true,
            'linebreak_after_opening_tag' => true,
            'ordered_imports' => true,
            'method_argument_space' => [
                'on_multiline' => 'ensure_fully_multiline'
            ],
            'no_unused_imports' => true,
        ]
    )
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->in(__DIR__ . '/')
            ->in(__DIR__ . '/../src')
    );
