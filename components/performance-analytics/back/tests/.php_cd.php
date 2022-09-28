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
    )->in('Akeneo\PerformanceAnalytics\Domain'),

    $builder->only(
        [
            'Webmozart\Assert',
            'Akeneo\PerformanceAnalytics\Domain',
        ]
    )->in('Akeneo\PerformanceAnalytics\Application'),

    $builder->only(
        [
            'Webmozart\Assert',

            'Akeneo\PerformanceAnalytics\Domain',
            'Akeneo\PerformanceAnalytics\Application',

            // symfony dependencies
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Extension\Extension',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\HttpKernel\Exception\BadRequestHttpException',
        ]
    )->in('Akeneo\PerformanceAnalytics\Infrastructure'),
];

return new Configuration($rules, $finder);
