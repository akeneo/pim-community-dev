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
            'Symfony\Component\Validator\ConstraintViolationList',
        ],
    )->in('Akeneo\OnboarderSerenity\Application'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Domain',
            'Akeneo\OnboarderSerenity\Application',
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
            'Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface',
            'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            // External dependencies coupling
            'Box\Spout\Common\Type',
            'Box\Spout\Writer\Common\Creator\WriterEntityFactory',
            'Box\Spout\Writer\Common\Creator\WriterFactory',
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
            'Akeneo\OnboarderSerenity\Infrastructure\Supplier\Encoder\SuppliersEncoder',
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
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
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
