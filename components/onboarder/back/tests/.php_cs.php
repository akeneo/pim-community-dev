<?php

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@PSR2' => true, // https://www.php-fig.org/psr/psr-2/
            'linebreak_after_opening_tag' => true, // Ensure there is no code on the same line as the PHP open tag.
            'ordered_imports' => true, // Ordering use statements.
            'no_unused_imports' => true, // Unused use statements must be removed.
            'yoda_style' => [
                'always_move_variable' => true, // Whether variables should always be on non assignable side when applying Yoda style.
            ]
        ]
    )
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/')
            ->in(__DIR__ . '/../src')
    );
