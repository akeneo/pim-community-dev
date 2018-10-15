<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PSR2' => true,
            '@Symfony' => true,
            '@PHP71Migration' => true,
            '@PHP71Migration:risky' => true,
            'array_syntax' => ['syntax' => 'short'],
            'binary_operator_spaces' => [
                'align_double_arrow' => false,
                'align_equals' => false,
            ],
            'concat_space' => ['spacing' => 'one'],
            'linebreak_after_opening_tag' => true,
            'no_unreachable_default_argument_value' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ordered_class_elements' => true,
            'ordered_imports' => true,
            'phpdoc_add_missing_param_annotation' => true,
            'phpdoc_align' => ['align' => 'left'],
            'phpdoc_order' => true,
        ]
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/../../')
    );
