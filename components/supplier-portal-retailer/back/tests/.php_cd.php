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
            // Supplier Portal Retailer coupling

            // Supplier Portal Supplier coupling

            // PIM coupling

            // External dependencies coupling
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Domain'),

    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',

            // Supplier Portal Supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded',

            // External dependencies coupling
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Constraint',
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Application'),

    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',
            'Akeneo\SupplierPortal\Retailer\Application',

            // Supplier Portal Supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage',

            // PIM coupling
            'Akeneo\Tool\Component\Batch\Event\EventInterface',
            'Akeneo\Tool\Component\Batch\Event\InvalidItemEvent',
            'Akeneo\Tool\Component\Batch\Item\FileInvalidItem',
            'Akeneo\Tool\Component\Batch\Item\InvalidItemException',
            'Akeneo\Tool\Component\Batch\Item\ItemReaderInterface',
            'Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface',
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Batch\Model\Warning',
            'Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface',
            'Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',
            'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker',
            'Akeneo\Tool\Component\FileStorage\StreamedFileResponse',
            'Akeneo\Tool\Component\FileStorage\FilesystemProvider',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification',
            'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',
            'Akeneo\UserManagement\Component\Model\User',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

            // External dependencies coupling
            'Box\Spout\Common\Exception\IOException',
            'Box\Spout\Common\Type',
            'Box\Spout\Writer\Common\Creator\WriterEntityFactory',
            'Box\Spout\Writer\Common\Creator\WriterFactory',
            'Box\Spout\Writer\Exception\WriterNotOpenedException',
            'Doctrine\DBAL\Connection',
            'Psr\Log\LoggerInterface',
            'Symfony',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Infrastructure'),


    // tests
    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Encoder\SuppliersEncoder',
            'Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage',

            // Supplier Portal Supplier coupling

            // PIM coupling
            'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'Akeneo\Tool\Component\Batch\Model\JobExecution',
            // External dependencies coupling
            'Box\Spout\Common\Type',
            'Box\Spout\Reader\Common\Creator\ReaderFactory',
            'Doctrine\DBAL\Connection',
            'Ramsey\Uuid\Uuid',
            'Symfony',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Test\Integration'),

    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',
            'Akeneo\SupplierPortal\Retailer\Application',
            'Akeneo\SupplierPortal\Retailer\Infrastructure',

            // Supplier Portal Supplier coupling
            'Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile',
            'Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded',

            // PIM coupling
            'Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification',
            'Akeneo\Platform\Bundle\NotificationBundle\Notifier',
            'Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker',
            'Akeneo\Test\Acceptance\User\InMemoryUserRepository',
            'Akeneo\UserManagement\Component\Model\User',

            // External dependencies coupling
            'PHPUnit\Framework',
            'Psr\Log\NullLogger',
            'Psr\Log\Test\TestLogger',
            'Ramsey\Uuid\Uuid',
            'Symfony\Component\EventDispatcher\EventDispatcher',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Test\Unit'),

    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',
            'Akeneo\SupplierPortal\Retailer\Application\Supplier',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier',
            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Test\Acceptance'),
];

return new Configuration($rules, $finder);
