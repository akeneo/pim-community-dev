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

            // symfony dependencies
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Extension\Extension',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',

        ]
    )->in('Akeneo\PerformanceAnalytics\Infrastructure'),
];

return new Configuration($rules, $finder);
