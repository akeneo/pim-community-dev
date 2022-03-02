<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            // Onboarder coupling

            // PIM coupling

            // External dependencies coupling
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\OnboarderSerenity\Domain'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain\Supplier',

            // PIM coupling

            // External dependencies coupling
        ],
    )->in('Akeneo\OnboarderSerenity\Application'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain\Supplier',
            // PIM coupling
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',
            'Symfony\Component\HttpKernel\DependencyInjection\Extension',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
        ],
    )->in('Akeneo\OnboarderSerenity\Infrastructure'),
];

return new Configuration($rules, $finder);
