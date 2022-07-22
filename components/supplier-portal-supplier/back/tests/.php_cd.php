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
            // Supplier Portal retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code',

            // External dependencies coupling
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Domain'),

    $builder->only(
        [
            // Supplier Portal retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded',

            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',

            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry',

            // External dependencies coupling
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Contracts\EventDispatcher\EventDispatcherInterface',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Application'),

    $builder->only(
        [
            // Supplier Portal retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code',

            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Application',

            // PIM coupling
            'Akeneo\Tool\Component\FileStorage\FilesystemProvider',

            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Psr\Log\LoggerInterface',
            'Symfony',
            'Twig\Environment',
            'Swift_Mailer',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Infrastructure'),


    // tests
    $builder->only(
        [
            // Supplier Portal retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code',
            
            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Infrastructure',

            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Symfony',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Test\Integration'),

    $builder->only(
        [
            // Supplier Portal retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded',
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier',

            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Application',
            'Akeneo\SupplierPortal\Supplier\Infrastructure',

            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry',

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
    )->in('Akeneo\SupplierPortal\Supplier\Test\Unit'),

    $builder->only(
        [
            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Exception\InvalidPassword',
            'Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\UpdatePassword',
            'Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\UpdatePasswordHandler',
            'Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication',

            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Test\Acceptance'),
];

return new Configuration($rules, $finder);
