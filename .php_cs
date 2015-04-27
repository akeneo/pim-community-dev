<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/features');

return Symfony\CS\Config\Config::create()
    ->fixers([
        '-concat_without_spaces',
        '-empty_return',
        '-multiline_array_trailing_comma',
        '-phpdoc_short_description',
        '-single_quote',
        '-trim_array_spaces',
        '-operators_spaces',
        '-unary_operators_spaces',
        'newline_after_open_tag',
        'ordered_use',
        'phpdoc_order'
    ])
    ->finder($finder);
