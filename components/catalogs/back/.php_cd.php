<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    // Domain layer should only use classes from itself and models from the ServiceAPI
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
        ]
    )->in('Akeneo\Catalogs\Domain'),

    // Application layer should only use classes from Domain, ServiceAPI or itself
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
            'Akeneo\Catalogs\ServiceAPI\Model',
            'Akeneo\Catalogs\ServiceAPI\Command',
            'Akeneo\Catalogs\ServiceAPI\Query',
            'Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException',
            'Akeneo\Catalogs\ServiceAPI\Exception\CatalogDoesNotExistException',
        ]
    )->in('Akeneo\Catalogs\Application'),

    // Infrastructure layer can use anything, but we track used dependencies anyway to detect changes
    $builder->only(
        [
            'Akeneo\Catalogs\ServiceAPI',
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
            'Akeneo\Catalogs\Infrastructure',

            'Symfony\Component\Config',
            'Symfony\Component\Console',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\EventDispatcher',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Messenger',
            'Symfony\Component\Routing',
            'Symfony\Component\Security',
            'Symfony\Component\Serializer',
            'Symfony\Component\Validator',
            'Doctrine\DBAL',
            'Ramsey\Uuid\Uuid',
            'Akeneo\Platform\Bundle\InstallerBundle',
            'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
            'Akeneo\Tool\Component\Api',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',
            'Akeneo\Connectivity\Connection\ServiceApi',
            'League\Flysystem\Filesystem',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',

            // used in Persistence\Measurement
            'Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\FindMeasurementFamilies',

            // used in TemporaryEnrichmentBridge
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            'Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface',
            'Symfony\Component\OptionsResolver',

            // @todo replace next ones with the ones from service API when available

            // used in Persistence\Attribute
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
            'Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface',

            // used in Persistence\Catalog\Product
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Query',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch',

            // used in Persistence\Category
            'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface',
            'Akeneo\Category\Infrastructure\Component\Model\CategoryInterface',
            'Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface',
            'Doctrine\Common\Collections\Collection',
            'Akeneo\Category\Api\FindCategoryTrees',
            'Akeneo\Category\Api\CategoryTree',

            // used in Persistence\Channel
            'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
            'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',

            // used in Persistence\Currency
            'Akeneo\Channel\Infrastructure\Component\Repository\CurrencyRepositoryInterface',

            // used in Persistence\Locale
            'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',
            'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

            // used in Persistence\Family
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        ]
    )->in('Akeneo\Catalogs\Infrastructure'),

    // ServiceAPI layer should only use classes from itself, constraints annotations or symfony/messenger
    $builder->only(
        [
            'Akeneo\Catalogs\ServiceAPI',

            // Constraints as Attributes
            'Symfony\Component\Validator\Constraints',
            'Akeneo\Catalogs\Infrastructure\Validation',

            // Message Bus
            'Symfony\Component\Messenger',
        ]
    )->in('Akeneo\Catalogs\ServiceAPI'),
];

$config = new Configuration($rules, $finder);

return $config;
