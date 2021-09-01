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
            ->name('*.php')
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->in(__DIR__ . '/vendor/akeneo/pim-community-dev/tests/legacy/features')
            ->in(__DIR__ . '/vendor/akeneo/pim-community-dev/tests/features')
            ->in(__DIR__ . '/vendor/akeneo/pim-community-dev/tests/back/Acceptance')
            ->in(__DIR__ . '/vendor/akeneo/pim-community-dev/src')
    );
