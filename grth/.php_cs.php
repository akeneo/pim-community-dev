<?php

return PhpCsFixer\Config::create()
    ->setRules(
        [
            '@PSR2' => true,
            'linebreak_after_opening_tag' => true,
            'ordered_imports' => true,
            'method_argument_space' => [
                'ensure_fully_multiline' => false
            ],
        ]
    )
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->in(__DIR__ . '/src')
    );
