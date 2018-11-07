<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',

            // Akeneo external bounded contexts
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Pim\Enrichment\Component',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',

            // Akeneo external bounded contexts
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Pim\Enrichment\Component',
            'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',

            // Events
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Application'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Client',

            // TODO: Should be remove APAI-179
            'Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration',
            'Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface',

            // External dependencies
            'Guzzle',
            'Symfony\Component\HttpFoundation\Response',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Client'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\Batch',
            // TODO: should be removed see with JJ and AL
            'Akeneo\Tool\Bundle\BatchBundle',

            // External dependencies
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller',

            // External dependencies
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel\Exception',
            'Symfony\Component\Translation',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Client',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider',
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command',

            // Akeneo external bounded contexts
            'Akeneo\Platform\Bundle\InstallerBundle\Event',

            // External dependencies
            'Symfony\Component\EventDispatcher',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Install'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',

            // Akeneo external bounded contexts
            'Akeneo\Pim\Enrichment\Component',
            'Akeneo\Pim\WorkOrganization\Workflow\Component',
            'Akeneo\Tool\Component\StorageUtils',

            // External dependencies
            'Symfony\Component\EventDispatcher',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',

            // Akeneo external bounded contexts
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Tool\Component\StorageUtils',
            'Oro\Bundle\ConfigBundle\Entity',

            // External dependencies
            'Doctrine',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\StorageUtils',

            // external dependencies
            'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass',
            // TODO: the next line could be removed with lazy-loaded commands
            'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
            'Symfony\Component\Console',
            'Symfony\Component\Config',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Validator',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony'),
];

$config = new Configuration($rules, $finder);

return $config;
