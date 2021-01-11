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
            //Needed to access to the criteria codes. To remove after refactoring
            'Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation',

            //External dependencies
            'Ramsey\Uuid\Uuid',
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\DataQualityInsights\Domain',

            //External dependencies
            'Psr\Log\LoggerInterface',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Webmozart\Assert\Assert',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Application'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\DataQualityInsights\Domain',
            'Akeneo\Pim\Automation\DataQualityInsights\Application',

            //Akeneo external bounded contexts :

            //Enrichment computing
            'Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator',

            //Consistency computing
            'Akeneo\Pim\Structure\Component\AttributeTypes',

            //Bundle installation
            'Akeneo\Platform\Bundle\InstallerBundle\Event',
            'Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository',
            'Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher',
            'Akeneo\Tool\Component\Console\CommandLauncher',
            'Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface',
            'Akeneo\UserManagement\Bundle\Security\SystemUserToken',
            'Akeneo\UserManagement\Component\Model\UserInterface',

            //Subscribers for product updates
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface',
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
            'Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository',
            'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'Akeneo\Tool\Component\Batch\Job\JobInterface',

            //Subscribers for attribute updates
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',

            //Subscribers for attribute group updates
            'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',

            //Connector / (Tasklets, job parameters)
            'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',

            //Necessary for the Completeness calculation
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale',
            'Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask',
            'Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks',
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',

            //Datagrid filters, columns, sorting and ES indexation needs
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorte',
            'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelProperties',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions',
            'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            'Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration',
            'Oro\Bundle\DataGridBundle\Event\BuildBefore',
            'Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface',
            'Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration',
            'Oro\Bundle\DataGridBundle\Datagrid\RequestParameters',
            'Oro\Bundle\DataGridBundle\Extension\AbstractExtension',
            'Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration',
            'Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface',
            'Oro\Bundle\FilterBundle\Grid\Extension\Configuration',
            'Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface',
            'Oro\Bundle\FilterBundle\Filter\ChoiceFilter',
            'Oro\Bundle\FilterBundle\Filter\FilterUtility',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface',
            'Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource',
            'Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource',


            //Necessary for the particular command EvaluatePendingCriteriaCommand
            'Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager',
            'Akeneo\Tool\Component\Batch',
            'Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage',
            'Doctrine\ORM\EntityManager',
            'Symfony\Component\Process',

            //Necessary for the Dashboard
            'Akeneo\Tool\Component\Classification\Model\CategoryInterface',
            'Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface',

            //Security
            'Oro\Bundle\SecurityBundle\SecurityFacade',

            //External dependencies
            'Doctrine\DBAL',
            'Doctrine\Persistence\ObjectRepository',
            'Doctrine\ORM\Query\Expr',
            'League\Flysystem\MountManager',
            'Psr\Log\LoggerInterface',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\Console',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\EventDispatcher',
            'Symfony\Component\Filesystem',
            'Symfony\Component\Finder',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Security',
            'Symfony\Component\Validator\Constraints',
            'Symfony\Contracts\EventDispatcher',
            'Symfony\Component\Form\FormFactoryInterface',
            'GuzzleHttp\ClientInterface',
            'GuzzleHttp\Exception',
            'Mekras\Speller',
            'League\Flysystem',
            'Webmozart\Assert\Assert',

            // Common Dependencies
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Tool\Component\StorageUtils\Cache\LRUCache',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
