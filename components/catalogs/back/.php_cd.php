<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    // Domain layer should only use classes from Domain
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
        ]
    )->in('Akeneo\Catalogs\Domain'),

    // Application layer should only use classes from Domain or Application
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
        ]
    )->in('Akeneo\Catalogs\Application'),

    // Infrastructure layer can use anything, but we track used dependencies anyway to detect changes
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
            'Akeneo\Catalogs\Infrastructure',

            'Symfony\Component',
        ]
    )->in('Akeneo\Catalogs\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
