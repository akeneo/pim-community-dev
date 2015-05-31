<?php

$finder = \Symfony\CS\Finder\DefaultFinder::create()->files();

$fixers = [
    '-concat_without_spaces',
    '-empty_return',
    '-multiline_array_trailing_comma',
    '-phpdoc_short_description',
    '-single_quote',
    '-trim_array_spaces',
    '-operators_spaces',
    '-unary_operators_spaces',
    '-unalign_equals',
    '-unalign_double_arrow',
    'newline_after_open_tag',
    'ordered_use',
    'phpdoc_order'
];

exec('git show --name-only --oneline --pretty="format:" --diff-filter=AMR | grep -v ^spec/', $diff);
$diff = array_filter($diff);

foreach ($diff as $filename) {
    printf('Parsed file : %s' .PHP_EOL, $filename);
}

$finder->append($diff);

return \Symfony\CS\Config\Config::create()
    ->fixers($fixers)
    ->finder($finder);
