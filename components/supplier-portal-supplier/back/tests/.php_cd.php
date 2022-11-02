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

            // External dependencies coupling
            'Ramsey\Uuid\Uuid',
            'Symfony\Contracts\EventDispatcher\EventDispatcherInterface',
            'Psr\Log\LoggerInterface',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Domain'),

    $builder->only(
        [
            // Supplier Portal retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded',
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted',

            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',

            // PIM coupling

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
            'Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI',

            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Application',

            // PIM coupling
            'Akeneo\Tool\Component\FileStorage\StreamedFileResponse',

            // External dependencies coupling
            'Doctrine\DBAL\Connection',
            'Symfony',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Infrastructure'),


    // tests
    $builder->only(
        [
            // Supplier Portal retailer coupling

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
            'Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted',

            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Application',
            'Akeneo\SupplierPortal\Supplier\Infrastructure',

            // PIM coupling

            // External dependencies coupling
            'PHPUnit\Framework',
            'Psr\Log\NullLogger',
            'Symfony\Component\HttpFoundation\Request',
            'Symfony\Component\HttpFoundation\Response',
            'Symfony\Component\Security\Http\Event\LogoutEvent',
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Test\Unit'),

    $builder->only(
        [
            // Supplier Portal supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain',
            'Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\Exception\InvalidPassword',
            'Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\UpdatePassword\UpdatePassword',
            'Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\UpdatePassword\UpdatePasswordHandler',
            'Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication',

            // PIM coupling

            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
        ],
    )->in('Akeneo\SupplierPortal\Supplier\Test\Acceptance'),
    $builder->forbids(['Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Read'])->in('Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write'),
    $builder->forbids(['Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write'])->in('Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Read'),
];

return new Configuration($rules, $finder);
