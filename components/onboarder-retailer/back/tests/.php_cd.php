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
    )->in('Akeneo\OnboarderSerenity\Retailer\Domain'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain',

            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            // External dependencies coupling
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Constraint',
        ],
    )->in('Akeneo\OnboarderSerenity\Retailer\Application'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain',
            'Akeneo\OnboarderSerenity\Retailer\Application',
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
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            // External dependencies coupling
            'Box\Spout\Common\Exception\IOException',
            'Box\Spout\Common\Type',
            'Box\Spout\Writer\Common\Creator\WriterEntityFactory',
            'Box\Spout\Writer\Common\Creator\WriterFactory',
            'Box\Spout\Writer\Exception\WriterNotOpenedException',
            'Doctrine\DBAL\Connection',
            'Psr\Log\LoggerInterface',
            'Ramsey\Uuid\Uuid',
            'Symfony',
            'Twig\Environment',
            'Swift_Mailer',
        ],
    )->in('Akeneo\OnboarderSerenity\Retailer\Infrastructure'),


    // tests
    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain',
            'Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Encoder\SuppliersEncoder',
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
    )->in('Akeneo\OnboarderSerenity\Retailer\Test\Integration'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain',
            'Akeneo\OnboarderSerenity\Retailer\Application',
            'Akeneo\OnboarderSerenity\Retailer\Infrastructure',
            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker',
            // External dependencies coupling
            'PHPUnit\Framework',
            'Psr\Log\NullLogger',
            'Ramsey\Uuid\Uuid',
            'Symfony\Component\EventDispatcher\EventDispatcher',
            'Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Twig\Environment',
        ],
    )->in('Akeneo\OnboarderSerenity\Retailer\Test\Unit'),

    $builder->only(
        [
            // Onboarder coupling
            'Akeneo\OnboarderSerenity\Retailer\Domain',
            'Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Exception\InvalidPassword',
            'Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\UpdatePassword',
            'Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\UpdatePasswordHandler',
            'Akeneo\OnboarderSerenity\Retailer\Application\Supplier',
            'Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier',
            'Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication',
            // PIM coupling
            'Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\FakeFeatureFlag',
            // External dependencies coupling
            'Behat',
            'PHPUnit\Framework',
            'Ramsey\Uuid\Uuid',
        ],
    )->in('Akeneo\OnboarderSerenity\Retailer\Test\Acceptance'),
];

return new Configuration($rules, $finder);
