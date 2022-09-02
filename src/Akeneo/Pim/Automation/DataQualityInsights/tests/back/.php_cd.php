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
            //External dependencies
            'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface'
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\DataQualityInsights\Domain',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            //External dependencies
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Application'),

    $builder->only(
        [
            'Webmozart\Assert\Assert',
            'Akeneo\Pim\Automation\DataQualityInsights\Domain',
            'Akeneo\Pim\Automation\DataQualityInsights\Application',

            //Akeneo external bounded contexts :

            //AssetManager
            'Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAssetAttributesWithMediaInfoInterface',
            'Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType',
            'Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute',
            'Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute',

            //Completeness
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks',
            //Bundle installation
            'Akeneo\Platform\Bundle\InstallerBundle\Event',

            //Subscribers for product updates
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface',
            'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface',
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Akeneo\Tool\Component\Batch\Job\JobInterface',

            //Subscribers for attribute updates
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',

            //Subscribers for locale updates
            'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',

            //Connector / (Tasklets, job parameters)
            'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',

            //Datagrid filters, columns, sorting and ES indexation needs
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface',
            'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            'Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration',
            'Oro\Bundle\DataGridBundle\Event\BuildBefore',
            'Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface',
            'Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration',
            'Oro\Bundle\DataGridBundle\Datagrid\RequestParameters',
            'Oro\Bundle\DataGridBundle\Extension\AbstractExtension',
            'Oro\Bundle\FilterBundle\Grid\Extension\Configuration',
            'Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface',
            'Oro\Bundle\FilterBundle\Filter\ChoiceFilter',
            'Oro\Bundle\FilterBundle\Filter\FilterUtility',
            'Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface',
            'Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface',

            //Attribute group grid
            'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

            //Necessary for the particular command EvaluatePendingCriteriaCommand
            'Akeneo\Tool\Component\Batch',

            //Necessary for the Dashboard
            'Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface',
            'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface',

            //Necessary for the dictionary
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',

            //External dependencies
            'Doctrine\DBAL',
            'Doctrine\ORM\Query\Expr',
            'Doctrine\ORM\QueryBuilder',
            'Psr\Log\LoggerInterface',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\Console',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\EventDispatcher',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Validator\Constraints',
            'Symfony\Component\Form\FormFactoryInterface',
            'Symfony\Contracts\Translation',
            'PhpSpellcheck\Spellchecker\Aspell',
            'PhpSpellcheck\MisspellingInterface',
            'PhpSpellcheck\Spellchecker\SpellcheckerInterface',
            'League\Flysystem',
            'Ramsey\Uuid\Uuid',
            'Ramsey\Uuid\UuidInterface',

            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Tool\Component\FileStorage\FilesystemProvider',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
