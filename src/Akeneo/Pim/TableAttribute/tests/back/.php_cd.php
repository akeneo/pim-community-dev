<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert',
        ]
    )->in('Akeneo\Pim\TableAttribute\Domain'),
    $builder->only(
        [
            // symfony bundle config
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\Config\FileLocator',

            'Akeneo\Pim\Structure\Component',
        ]
    )->in('Akeneo\Pim\TableAttribute\Infrastructure'),
];

return new Configuration($rules, $finder);
