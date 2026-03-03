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
        'declare_strict_types' => true,
        '@PER' => true,
        'no_extra_blank_lines' => true,
        'trailing_comma_in_multiline' => true,
        'void_return' => true,
        'native_function_invocation' => [
            'include' => ['@all'],
        ],
        'no_spaces_around_offset' => true,
        'no_useless_return' => true,
        'array_indentation' => true,
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line'
        ],
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'operator_linebreak' => [
            'only_booleans' => true
        ],
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true
        ]
    ))
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->notName('*Spec.php')
            ->in(__DIR__.'/../')
    );
