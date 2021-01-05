<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert',
        ]
    )->in('Akeneo\Pim\Automation\TaskScheduling\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\TaskScheduling\Domain',
        ]
    )->in('Akeneo\Pim\Automation\TaskScheduling\Application'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\TaskScheduling\Domain',
            'Akeneo\Pim\Automation\TaskScheduling\Application',
        ]
    )->in('Akeneo\Pim\Automation\TaskScheduling\Infrastructure'),
];

return new Configuration($rules, $finder);
