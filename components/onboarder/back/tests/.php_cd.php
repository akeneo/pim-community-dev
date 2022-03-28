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
    // src
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
            'Akeneo\OnboarderSerenity\Domain',

            // PIM coupling

            // External dependencies coupling
            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ],
    )->in('Akeneo\OnboarderSerenity\Application'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain',
            'Akeneo\OnboarderSerenity\Application',
            // PIM coupling
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Ramsey\Uuid\Uuid',
            'Symfony',
        ],
    )->in('Akeneo\OnboarderSerenity\Infrastructure'),


    // tests
    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain',
            // PIM coupling
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Ramsey\Uuid\Uuid',
            'Symfony',
        ],
    )->in('Akeneo\OnboarderSerenity\Test\Integration'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain',
            'Akeneo\OnboarderSerenity\Application\Supplier',
            'Akeneo\OnboarderSerenity\Infrastructure\Supplier',
            // PIM coupling
            // External dependencies coupling
            'PHPUnit\Framework',
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\OnboarderSerenity\Test\Unit'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain',
            'Akeneo\OnboarderSerenity\Application\Supplier',
            'Akeneo\OnboarderSerenity\Infrastructure\Supplier',
            // PIM coupling
            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\OnboarderSerenity\Test\Acceptance'),
];

return new Configuration($rules, $finder);
