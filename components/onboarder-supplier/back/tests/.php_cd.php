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
            'Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface',
        ],
    )->in('Akeneo\OnboarderSerenity\Supplier\Domain'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Supplier\Domain',

            // Onboarder retailer coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded',

            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            // External dependencies coupling
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Constraint',
        ],
    )->in('Akeneo\OnboarderSerenity\Supplier\Application'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Supplier\Domain',
            'Akeneo\OnboarderSerenity\Supplier\Application',
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Psr\Log\LoggerInterface',
            'Symfony',
            'Twig\Environment',
            'Swift_Mailer',
        ],
    )->in('Akeneo\OnboarderSerenity\Supplier\Infrastructure'),


    // tests
    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Supplier\Domain',
            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Symfony',
        ],
    )->in('Akeneo\OnboarderSerenity\Supplier\Test\Integration'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Supplier\Domain',
            'Akeneo\OnboarderSerenity\Supplier\Application',
            'Akeneo\OnboarderSerenity\Supplier\Infrastructure',
            // Onboarder retailer coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded',
            'Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier',
            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            // External dependencies coupling
            'PHPUnit\Framework',
            'Psr\Log\NullLogger',
            'Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Twig\Environment',
        ],
    )->in('Akeneo\OnboarderSerenity\Supplier\Test\Unit'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Supplier\Domain',
            'Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Exception\InvalidPassword',
            'Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\UpdatePassword',
            'Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\UpdatePasswordHandler',
            'Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication',
            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\FakeFeatureFlag',
            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
        ],
    )->in('Akeneo\OnboarderSerenity\Supplier\Test\Acceptance'),
];

return new Configuration($rules, $finder);
