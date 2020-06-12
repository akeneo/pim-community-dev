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
            'Symfony\Component',
        ]
    )->in('Akeneo\Platform\CommunicationChannel\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
