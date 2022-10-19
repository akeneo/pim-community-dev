<?php

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@PSR2' => true, // https://www.php-fig.org/psr/psr-2/
            'linebreak_after_opening_tag' => true, // Ensure there is no code on the same line as the PHP open tag.
            'no_unused_imports' => true, // Unused use statements must be removed.
            'yoda_style' => [
                'equal' => true,
                'identical' => true,
                'less_and_greater' => true,
            ],
            'protected_to_private' => true, // Converts protected variables and methods to private where possible.
            'cast_spaces' => true, // A single space should be between cast and variable.
            'class_reference_name_casing' => true, // When referencing a class it must be written using the correct casing.
            'no_blank_lines_after_class_opening' => true, // There should be no empty lines after class opening brace.
            'no_whitespace_in_blank_line' => true, // Remove trailing whitespace at the end of blank lines.
            'return_type_declaration' => [
                'space_before' => 'none', // There should be no space before colon
            ],
            'single_quote' => [
                'strings_containing_single_quote_chars' => false, // Convert double quotes to single quotes for simple strings.
            ],
            'trailing_comma_in_multiline' => [
                'elements' => [
                    'arrays',
                    'arguments',
                    'parameters',
                ],
            ], // Multi-line arrays, arguments list and parameters list must have a trailing comma.
        ]
    )
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/')
            ->in(__DIR__ . '/../src')
    );
