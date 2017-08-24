<?php

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'no_extra_consecutive_blank_lines' => true,
        'blank_line_before_statement' => [
            'statements' => ['return']
        ],
        'method_argument_space' => [
            'ensure_fully_multiline' => false
        ],
    ))
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->notName('*TestCase.php')
            ->in(__DIR__ . '/features')
            ->in(__DIR__ . '/src')
    );
