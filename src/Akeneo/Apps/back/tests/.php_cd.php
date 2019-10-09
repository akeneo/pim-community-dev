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
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',

            'Symfony\Component\Filesystem\Exception\IOException',
            'Symfony\Component\Filesystem\Filesystem',
            'Symfony\Component\Finder\Finder',

            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',

        ]
    )->in('Akeneo\Apps\Infrastructure\Install'),

    $builder->only(
        [
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\HttpKernel\DependencyInjection\Extension',
        ]
    )->in('Akeneo\Apps\Infrastructure\Symfony'),
];

$config = new Configuration($rules, $finder);

return $config;
