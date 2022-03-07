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
            ],
            'protected_to_private' => true,
            'cast_spaces' => true,
            'class_reference_name_casing' => true,
            'no_blank_lines_after_class_opening' => true,
            'no_whitespace_in_blank_line' => true,
            'return_type_declaration' => [
                'space_before' => 'none',
            ],
            'single_quote' => [
                'strings_containing_single_quote_chars' => true,
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
