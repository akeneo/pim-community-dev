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
            // External dependencies coupling
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\SupplierPortal\Domain'),

    $builder->only(
        [
            // Supplier Portal coupling
            'Akeneo\SupplierPortal\Domain',

            // Onboarder retailer coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded',

            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            // External dependencies coupling
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Contracts\EventDispatcher\EventDispatcherInterface',
        ],
    )->in('Akeneo\SupplierPortal\Application'),

    $builder->only(
        [
            // Supplier Portal coupling
            'Akeneo\SupplierPortal\Domain',
            'Akeneo\SupplierPortal\Application',
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Psr\Log\LoggerInterface',
            'Symfony',
            'Twig\Environment',
            'Swift_Mailer',
        ],
    )->in('Akeneo\SupplierPortal\Infrastructure'),


    // tests
    $builder->only(
        [
            // Supplier Portal coupling
            'Akeneo\SupplierPortal\Domain',
            'Akeneo\SupplierPortal\Infrastructure',
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Symfony',
        ],
    )->in('Akeneo\SupplierPortal\Test\Integration'),

    $builder->only(
        [
            // Supplier Portal coupling
            'Akeneo\SupplierPortal\Domain',
            'Akeneo\SupplierPortal\Application',
            'Akeneo\SupplierPortal\Infrastructure',
            // Onboarder retailer coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded',
            'Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier',
            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            // External dependencies coupling
            'PHPUnit\Framework',
            'Psr\Log\NullLogger',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Twig\Environment',
            'Symfony\Component\HttpFoundation\Request',
            'Symfony\Component\HttpFoundation\Response',
            'Symfony\Component\Security\Http\Event\LogoutEvent',
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface',

        ],
    )->in('Akeneo\SupplierPortal\Test\Unit'),

    $builder->only(
        [
            // Supplier Portal coupling
            'Akeneo\SupplierPortal\Domain',
            'Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Exception\InvalidPassword',
            'Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\UpdatePassword',
            'Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\UpdatePasswordHandler',
            'Akeneo\SupplierPortal\Infrastructure\Authentication',
            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\FakeFeatureFlag',
            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
        ],
    )->in('Akeneo\SupplierPortal\Test\Acceptance'),
];

return new Configuration($rules, $finder);
