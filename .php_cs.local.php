<?php

$finder = \Symfony\CS\Finder\DefaultFinder::create()->files();
$fixers = require __DIR__ . '/.php_cs-fixers.php';

$finder->name('*.php')
    ->in(__DIR__ . '/features')
    ->in(__DIR__ . '/src');

return \Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers($fixers)
    ->finder($finder);
