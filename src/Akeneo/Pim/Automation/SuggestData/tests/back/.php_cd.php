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
            // TODO remove all links by reference
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            // TODO public constant
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',

            'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',
            'Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException',

            // Events
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',

            // TODO remove all links by reference
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            // TODO relationship between bounded context (query data though repository)
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
            'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
            'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',
            'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
            // TODO public constant
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Application'),

    $builder->only(
        [
            // External dependencies
            'Guzzle',
            'Symfony\Component\HttpFoundation\Response',
            'Psr\Http\Message\ResponseInterface',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Client'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\Batch',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',
            // TODO: should be removed see with JJ and AL
            'Akeneo\Tool\Bundle\BatchBundle',

            // External dependencies
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\Validator\Constraints\Collection',
            'Symfony\Component\Validator\Constraints\NotBlank',

            // TODO relationship between bounded context (query data though repository)
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',

            // External dependencies
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel\Exception',
            'Symfony\Component\Translation',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
            'Akeneo\Pim\Automation\SuggestData\Application',
            'Akeneo\Pim\Automation\SuggestData\Infrastructure\Client',

            // TODO public constant
            'Akeneo\Pim\Structure\Component\AttributeTypes',

            // TODO remove all links by reference
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',
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

            'Akeneo\Pim\WorkOrganization\Workflow\Component',
            'Akeneo\Tool\Component\StorageUtils',

            // External dependencies
            'Symfony\Component\EventDispatcher',

            // TODO remove all links by reference
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',

            // TODO relationship between bounded context (query data though repository)
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\StorageUtils',

            // External dependencies
            'Doctrine',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\SuggestData\Domain',

            // Akeneo external bounded contexts
            'Akeneo\Tool',

            // external dependencies
            'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass',
            // TODO: the next line could be removed with lazy-loaded commands
            'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
            'Symfony\Component',

            // TODO remove all links by reference
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',

            // TODO public constant
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony'),
];

$config = new Configuration($rules, $finder);

return $config;
