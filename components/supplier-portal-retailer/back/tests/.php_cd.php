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
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Domain'),

    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',

            // Supplier Portal Supplier coupling

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

            // PIM coupling
            'Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandler',
            'Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandler',
            'Akeneo\Tool\Component\Batch\Event\EventInterface',
            'Akeneo\Tool\Component\Batch\Event\InvalidItemEvent',
            'Akeneo\Tool\Component\Batch\Item\FileInvalidItem',
            'Akeneo\Tool\Component\Batch\Item\InvalidItemException',
            'Akeneo\Tool\Component\Batch\Item\ItemReaderInterface',
            'Akeneo\Tool\Component\Batch\Job\ExitStatus',
            'Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface',
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Batch\Model\Warning',
            'Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface',
            'Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException',
            'Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory',
            'Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory',
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
            'OpenSpout\Common\Entity\Row',
            'OpenSpout\Common\Exception\IOException',
            'OpenSpout\Writer\Exception\WriterNotOpenedException',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Types\Types',
            'League\Flysystem',
            'Psr\Log\LoggerInterface',
            'Symfony',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Infrastructure'),


    // tests
    $builder->only(
        [
            // Supplier Portal Retailer coupling
            'Akeneo\SupplierPortal\Retailer\Domain',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\Install\AddSupplierPortalJobs',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\Storage',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\DeleteUnknownSupplierDirectoriesInGCSBucket',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Encoder\SuppliersEncoder',
            'Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder',
            'Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder',

            // Supplier Portal Supplier coupling

            // PIM coupling
            'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'Akeneo\Tool\Component\Batch\Model\JobExecution',
            'Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory',
            // External dependencies coupling
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
            'Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder',
            'Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder',

            // Supplier Portal Supplier coupling

            // PIM coupling
            'Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification',
            'Akeneo\Platform\Bundle\NotificationBundle\Notifier',
            'Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker',
            'Akeneo\Tool\Component\FileStorage\FilesystemProvider',
            'Akeneo\Test\Acceptance\User\InMemoryUserRepository',
            'Akeneo\UserManagement\Component\Model\User',

            // External dependencies coupling
            'League\Flysystem\Filesystem',
            'League\Flysystem\UnableToCheckFileExistence',
            'League\Flysystem\UnableToCreateDirectory',
            'League\Flysystem\UnableToReadFile',
            'League\Flysystem\UnableToWriteFile',
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
            'Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFile',
            'Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFileHandler',
            'Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileForSupplier',
            'Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileHandlerForSupplier',
            'Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier',
            'Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplierHandler',
            'Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository',
            'Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder',
            'Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder',

            // Supplier Portal Supplier coupling

            // PIM coupling

            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
        ],
    )->in('Akeneo\SupplierPortal\Retailer\Test\Acceptance'),
    $builder->forbids(['Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read'])->in('Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write'),
    $builder->forbids(['Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write'])->in('Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read'),
    $builder->forbids(['Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read'])->in('Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write'),
    $builder->forbids(['Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write'])->in('Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read'),
    $builder->forbids(['Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read', 'Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read'])->in('Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write'),
    $builder->forbids(['Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write', 'Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write'])->in('Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read'),
];

return new Configuration($rules, $finder);
