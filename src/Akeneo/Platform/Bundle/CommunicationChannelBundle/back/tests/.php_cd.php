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
            'Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain',
        ]
    )->in('Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Application'),
];

$config = new Configuration($rules, $finder);

return $config;
