<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
        ],
    )->in('Akeneo\Platform\Installer\Domain'),

    $builder->only(
        [
        ],
    )->in('Akeneo\Platform\Installer\Application'),

    $builder->only(
        [
            'Symfony\Component',
            'Akeneo\Platform\Installer\Application',
        ],
    )->in('Akeneo\Platform\Installer\Infrastructure'),
];

return new Configuration($rules, $finder);
