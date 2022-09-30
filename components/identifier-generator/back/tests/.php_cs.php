<?php
return (new PhpCsFixer\Config())
    ->setRules(array(
        '@PSR2' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline'
        ],
        'no_unused_imports' => true,
        'blank_line_before_statement' => true,
        '@PSR12' => true
    ))
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->notName('*Spec.php')
            ->in(__DIR__.'/../')
    );
