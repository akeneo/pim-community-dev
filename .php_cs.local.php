<?php

$finder = \Symfony\CS\Finder\DefaultFinder::create()->files();

$fixers = [
    '-concat_without_spaces',
    '-empty_return',
    '-multiline_array_trailing_comma',
    '-phpdoc_short_description',
    '-single_quote',
    '-trim_array_spaces',
    '-unary_operators_spaces',
    'align_equals',
    'align_double_arrow',
    'newline_after_open_tag',
    'ordered_use',
    'phpdoc_order',
];

$finder->name('*.php')
    ->in(__DIR__ . '/features')
    ->in(__DIR__ . '/src');

return \Symfony\CS\Config\Config::create()
    ->fixers($fixers)
    ->finder($finder);
