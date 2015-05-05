<?php

$branch = exec('git rev-parse --abbrev-ref HEAD');

$finder = Symfony\CS\Finder\DefaultFinder::create()->files();

if (in_array($branch, ['master', 'HEAD'])) {
    $finder
        ->name('*.php')
        ->in(__DIR__ . '/src')
        ->in(__DIR__ . '/features');
} else {
    if (is_int(getenv('TRAVIS_PULL_REQUEST'))) {
        exec('git diff ' . getenv('TRAVIS_COMMIT_RANGE') . ' --name-only --diff-filter=AMR | grep -v ^spec/', $diff);
    } else {
        exec('git show --name-only --oneline --pretty="format:" --diff-filter=AMR | grep -v ^spec/', $diff);
        $diff = array_filter($diff);
    }
    $finder->append($diff);
}

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
        '-unalign_equals',
        '-unalign_double_arrow',
        'newline_after_open_tag',
        'ordered_use',
        'phpdoc_order'
    ])
    ->finder($finder);
