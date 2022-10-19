<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
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
    $builder->only(
        [
            'Webmozart\Assert\Assert',
            'Akeneo\Platform\Syndication\Domain',
        ],
    )->in('Akeneo\Platform\Syndication\Application'),
    $builder->only(
        [
            // TODO: fix this
            'Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Syndication\Domain'),
    $builder->only(
        [
            'Symfony\Component',
            'Symfony\Contracts',
            'Akeneo\Tool',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Webmozart\Assert\Assert',
            'Doctrine\DBAL\Connection',

            'Akeneo\Platform\Syndication\Application',
            'Akeneo\Platform\Syndication\Domain',

            'Akeneo\AssetManager\Infrastructure\PublicApi',
            'Akeneo\Channel\Component\Query\PublicApi',
            'Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter\QualityScoreMultiLocalesFilter',
            'Akeneo\Pim\Automation\DataQualityInsights\PublicApi',
            'Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue',
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',
            'Akeneo\Pim\TableAttribute\Domain\Value\Table',
            'Akeneo\ReferenceEntity\Infrastructure\PublicApi',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',

            // TODO: fix this:
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductsQueryValidator',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductModelsQueryValidator',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent',
            'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException',
            'Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException',
            'Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetGroupAssociationsByProductUuids',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductUuids',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductUuids',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductModelQuantifiedAssociationsByProductUuids',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductUuids',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory',
            'Akeneo\Pim\Enrichment\Component\Product\Query',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQueryHandler',
            'GuzzleHttp\Client',
            'GuzzleHttp\Psr7\Request',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Doctrine\DBAL\Types',
            'Exception',
            'Ramsey\Uuid\Uuid',
            'Ramsey\Uuid\UuidInterface',
            'Elasticsearch\Common\Exceptions',
            'Psr\Log\LoggerInterface',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\BundlesDataCollector',
        ],
    )->in('Akeneo\Platform\Syndication\Infrastructure'),
];

return new Configuration($rules, $finder);
