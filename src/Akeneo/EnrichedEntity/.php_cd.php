<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\EnrichedEntity',
        'Webmozart\Assert\Assert'
    ])->in('Akeneo\EnrichedEntity\Domain'),
    $builder->only([
        'Akeneo\EnrichedEntity\Domain',
    ])->in('Akeneo\EnrichedEntity\Application'),
    $builder->only([
        'Doctrine\DBAL',
        'Symfony\Component',
        'Akeneo\EnrichedEntity\Application',
        'Akeneo\EnrichedEntity\Domain',
        'Pim\Bundle\InstallerBundle',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
    ])->in('Akeneo\EnrichedEntity\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
