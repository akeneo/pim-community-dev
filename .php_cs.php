<?php

$branch = getenv('TRAVIS_BRANCH');
$phpVersion = getenv('TRAVIS_PHP_VERSION');

printf('Current branch inspected : %s' . PHP_EOL, $branch);

$versionsConfig = [
    '5.5' => [
        'directories' => [
            __DIR__ . '/spec',
            __DIR__ . '/features',
        ],
        'fixers'      => [
            '-visibility',
        ]
    ],
    '5.6' => [
        'directories' => [
            __DIR__ . '/src'
        ],
        'fixers'      => [],
    ],
];

$finder = Symfony\CS\Finder\DefaultFinder::create()->files();

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

if (in_array($branch, ['master', 'HEAD'])) {
    if (!array_key_exists($phpVersion, $versionsConfig)) {
        return null;
    }
    $finder->name('*.php');
    foreach ($versionsConfig[$phpVersion]['directories'] as $directory) {
        printf('Directory %s parsed' . PHP_EOL, $directory);
        $finder->in($directory);
    }
    foreach ($versionsConfig[$phpVersion]['fixers'] as $fixer) {
        $fixers[] = $fixer;
    }
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
    ->fixers($fixers)
    ->finder($finder);
