<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert',
        ]
    )->in('Akeneo\Pim\TableAttribute\Domain'),
    $builder->only(
        [
            'Webmozart\Assert',
            'Akeneo\Pim\TableAttribute\Domain',
            'Ramsey\Uuid\Uuid',

            // symfony dependencies
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\Form\AbstractType',
            'Symfony\Component\Validator',
            'Symfony\Component\Serializer',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',
            'Symfony\Component\HttpFoundation',
            'Symfony\Contracts',
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            'Symfony\Component\Form\FormBuilderInterface',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            'Twig\Environment',

            // doctrine
            'Doctrine\Common\EventSubscriber',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\FetchMode',
            'Doctrine\DBAL\Types',
            'Doctrine\ORM\EntityManagerInterface',
            'Doctrine\ORM\Event\LifecycleEventArgs',
            'Doctrine\ORM\Events',

            // pim dependencies
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Tool\Component\StorageUtils\Cache',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface',
            'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException',
            'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Akeneo\Tool\Component\Connector\Exception',
            'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker',
            'Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor',
            'Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory',
            'Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface',
            'Akeneo\Tool\Component\Batch',
            'Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface',
            'Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface',
            'Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',
            'Akeneo\Tool\Component\Localization\LanguageTranslator',
            'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
            'Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ActivatedLocale',
            'Akeneo\Platform\Bundle\InstallerBundle\Event',
            'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface',
            'Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection',
            'Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection',
            'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue',
            'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType',
            'Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\AbstractValueDataNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AbstractAttributeCopier',
            'Akeneo\Pim\Enrichment\Component\Product\Query',
            'Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator',
            'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder',
            'Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType',
            'Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface',
            'Oro\Bundle\FilterBundle\Filter\AbstractFilter',
            'Oro\Bundle\FilterBundle\Filter\FilterUtility',
            'Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility',
            'Oro\Bundle\PimFilterBundle\Form\Type\UnstructuredType',
            'Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository',
            'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface',
            'Akeneo\Tool\Component\Localization\LabelTranslatorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',

            // Reference Entity
            'Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier',
            'Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface',
            'Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent',
            'Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent',
            'Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode',
            'Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface',

            // Measurements
            'Akeneo\Tool\Bundle\MeasureBundle\PublicApi',
            'Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException',
            'Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException',
            'Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily',
            'Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode',
            'Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface',
            'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',
            'Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand',
            'Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand',
        ]
    )->in('Akeneo\Pim\TableAttribute\Infrastructure'),
];

return new Configuration($rules, $finder);
