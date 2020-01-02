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
            'Ramsey\Uuid\Uuid',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\DataQualityInsights\Domain',

            //External dependencies
            'Psr\Log\LoggerInterface',
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
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
            'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'Akeneo\Tool\Component\Batch\Job\JobInterface',

            //Connector / (Tasklets, job parameters)
            'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',

            //Necessary for GetNonRequiredAttributesMasksQuery
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale',

            //Datagrid filters, columns, sorting and ES indexation needs
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorte',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty',
            'Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelProperties',
            'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            'Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration',
            'Oro\Bundle\DataGridBundle\Event\BuildBefore',
            'Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface',
            'Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface',
            'Oro\Bundle\FilterBundle\Grid\Extension\Configuration',
            'Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface',
            'Oro\Bundle\FilterBundle\Filter\ChoiceFilter',

            //External dependencies
            'Doctrine\DBAL',
            'Doctrine\Common\Persistence\ObjectRepository',
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
            'GuzzleHttp\ClientInterface',
            'GuzzleHttp\Exception',
            'Mekras\Speller',
        ]
    )->in('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
