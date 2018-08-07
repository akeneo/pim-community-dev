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
        'Doctrine\Common',
    ])->in('Akeneo\EnrichedEntity\Application'),
    $builder->only([
        'Akeneo\EnrichedEntity\Application',
        'Akeneo\EnrichedEntity\Domain',
        'Doctrine\DBAL',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'PDO',
        'Pim\Bundle\InstallerBundle',
        'Symfony\Component',
    ])->in('Akeneo\EnrichedEntity\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
