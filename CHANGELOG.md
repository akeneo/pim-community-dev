# master

## Bug fixes

- PIM-9486: System Information sections Registered bundles and PHP extensions repeat a high number of times
- PIM-9514: Fix check on API completness for product model
- PIM-9408: Fix attribute group's updated_at field udpate
- TIP-1513: Environment variables declared in the env were not loaded when using a compiled .env file
- PIM-9274: Fix Yaml reader to display the number of lines read for incorrectly formatted files
- TIP-1406: Add a tag to configure a DIC service based on a feature flag
- PIM-9133: Fix product save when the user has no permission on some attribute groups
- Fixes memory leak when indexing product models with a lot of product models in the same family
- PIM-9119: Fix missing warning when using mass edit with parent filter set to empty
- PIM-9114: fix errors on mass action when the parent filter is set to empty
- PIM-9110: avoid deadlock error when loading product and product models in parallel with the API
- PIM-9113: Locale Specific attribute breaks product grid
- PIM-9157: Fix performance issue when loading the data of a product group
- PIM-9163: total_fields limit of elasticsearch should be configurable
- PIM-9197: Make queries in InMemoryGetAttributes case insensitive
- PIM-9213: Fix tooltip hover on Ellipsis for Family Name on creating product
- PIM-9184: API - Fix dbal query group by part for saas instance
- PIM-9289: Display a correct error message when deleting a group or an association
- PIM-9327: PDF generation header miss the product name when the attribute used as label is localizable 
- PIM-9324: Fix product grid not loading when asset used as main picture is deleted
- PIM-9356: Fix external api endpoint for products with invalid quantified associations
- PIM-9357: Make rules case-insensitive so it complies with family and attribute codes
- PIM-9362: Adapt System Information twig file for a clear and a correct display of the number of API connections
- PIM-9360: Fix PHP Warning raised in PriceComparator
- PIM-9370: Fixes page freezing with a big number of attribute options
- PIM-9391: Filter empty prices and measurement values
- PIM-9407: Fix glitch in family variant selector if the family variant has no label
- PIM-9425: Fix inaccurate attribute max characters
- PIM-9443: Do not cache extensions.json
- PIM-9454: Fix scalar value type check in PQB filters
- PIM-9460: Fix performance issue on export
- PIM-9461: Fix display of multiselect fields with a lot of selected options
- PIM-9466: Fix selection counter in datagrid
- GITHUB-12578: Fix trailing zeros when formatting numbers
- PIM-9440: Fix locked MySQL tables during removing DQI evaluations without product
- PIM-9476: Fix locale selector behavior on the product edit form when the user doesn't have permissions to edit attributes
- PIM-9478: Allow the modification of the identifier on a variant product
- PIM-9481: Fix the list of product models when trying to get them by family variant
- GITHUB-12899: Fix error shown when importing product models with the same code
- PIM-9491: Translate product grid filters in user additional settings
- PIM-9494: Fix the performances of attribute-select-filter on long lists of AttributeOptions
- PIM-9496: Change date format in the locale it_IT from dd/MM/yy to dd/MM/yyyy
- PIM-9519: Fix translation key for datagrid search field
- PIM-9517: Fix locale selector default value on localizable attributes in product exports

## New features

- MET-197: Add possibility to define that an association type is two way & automatically create inversed association when association type is two way
- MET-14: Measurements (or metrics) are now stored in database
- AOB-277: Add an acl to allow a role member to view all job executions in last job execution grids, job tracker and last operations widget.
- RAC-54: Add a new type of associations: Association with quantity
- RAC-123: Add possibility to export product/product model with labels instead of code

## Improvements

- CLOUD-1959: Use cloud-deployer 2.2 and terraform 0.12.25
- PIM-9306: Enhance catalog volume monitoring count queries for large datasets
- API-1140: Be able to get attributes searching by a list of attribute codes
- API-1225: Be able to get attributes searching by updated date
- API-1226: Be able to get attributes searching by a list of attribute types
- PIM-9368: Allow minimum translation progress of 70% instead of 80%
- PIM-9398: Add a primary key on connection table
- PIM-9371: Disable save button when user creation form is not ready to submit
- RAC-178: When launching a job, the notification contains a link to the job status
- PIM-9485: Change ACL name “Remove a product model” to “Remove a product model (including children)”

# Technical Improvements

- TIP-1233: Upgrade to php7.4
- CPM-38: Upgrade Symfony to 4.4.15
- TIP-152: Upgrade ElasticSearch to 7.10.0

## Classes

## BC breaks

- API-1140: Change $criteria format from `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\AttributeRepository`
    the new format is `[property: [['operator' => (string), 'value' => (mixed)]]]`.

### Codebase

- Change constructor of `Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader` to
    - add `Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController` to
    - add `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface $productEditDataFilter`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController` to
    - add `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface $productEditDataFilter`
- Change constructor of `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetricValidator` to
    - remove `array $measures`
    - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $provider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\MeasureFamilyController` to
    - remove `array $measures`
    - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $legacyMeasurementProvider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Controller\MeasuresController` to
    - remove `array $measures`
    - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $provider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter` to
    - remove `array $config`
    - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $provider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager` to
     - remove `array $config`
     - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $legacyMeasurementProvider`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter` to
    - remove `Akeneo\Tool\Component\Localization\TranslatorProxy $translatorProxy`
    - add `Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface $measurementFamilyRepository`
    - add `Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository $baseCachedObjectRepository`
    - add `Psr\Log\LoggerInterface $logger`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\GroupNormalizer` to
    - add `Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers`
- Change constructor of `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute` to
    - add `(string) $defaultMetricUnit`    
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor` to add `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult $result`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid\FetchProductAndProductModelRows` to add `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetFactory $productAndProductsModelDocumentTypeFacetFactory`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows` to
    - add `?int $totalProductCount`
    - add `?int $totalProductModelCount`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderWithSearchAggregatorFactory` to make not nullable the third parameter `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator $searchAggregator`
- Change `Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager` to remove method `setMeasureConfig(array $config)`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\DependencyInjection\Configuration`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\AreaFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\BinaryFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\CaseBoxFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\DecibelFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\DurationFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\ElectricChargeFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\EnergyFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\FrequencyFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\IntensityFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\LengthFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\PowerFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\PressureFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\ResistanceFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\SpeedFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\TemperatureFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\VoltageFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\VolumeFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\WeightFamilyInterface`
- Rename `Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException` as `Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException`
- Rename `Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownMeasureException` as `Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\RefreshProductCommand` to
    - replace `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface $productSaver` by `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $productSaver`
    - replace `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface $productModelSaver` by `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $productModelSaver`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\RemoveCompletenessForChannelAndLocaleCommand` to
    - replace `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface $channelSaver` by `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $channelSaver`
- Add `getChannels()` and `getLabel()` methods in `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface` interface
- Change `addFieldSorter()` method of `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface` to return `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface`
- The `Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface` interface now also extends `Doctrine\Common\Persistence\ObjectRepository` interface
- Rename the `$objectFilter` property in `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\CategoryController` to `$collectionFilter`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\SqlGetConnectorProducts` to replace `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository` by `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface $attributeRepository`
- Change `Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface` to use `Akeneo\UserManagement\Component\Model\UserInterface` instead of `Symfony\Component\Security\Core\User\UserInterface`
- Change `Akeneo\Pim\Enrichment\Component\Product\Connector\Step\MassEditStep::setCleaner()` to take `Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit\TemporaryFileCleaner $cleaner` as first argument instead of `Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface $cleaner`
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductNormalizer::normalizeAssociations()` to make the first argument not optional
- Change `Akeneo\Pim\Enrichment\Component\Product\Model\Group::getTranslation()` to return null or an instance of `Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslationInterface`
- Change `Akeneo\Pim\Enrichment\Component\Category\Model\Category::getTranslation()` to return null or an instance of `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslationInterface`
- Change `Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard\CommentNormalizer` to implements `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
    - the `setSerializer()` method and the `$serializer` property are removed
    - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\CollectionNormalizer` to implements `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
    - the `setSerializer()` method and the `$serializer` property are removed
    - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ValueNormalizer` to implements `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
    - the `setSerializer()` method and the `$serializer` property are removed
    - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductModelNormalizer` to implements `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
    - the `setSerializer()` method and the `$serializer` property are removed
    - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductNormalizer` to implements `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
    - the `setSerializer()` method and the `$serializer` property are removed
    - the `setNormalizer()` method and the `$normalizer` property are added
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ViolationNormalizer` class, it is replaced by `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ConstraintViolationNormalizer`
- Change `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface` to add `getId()` and `getIdentifier()` methods

### CLI commands

The following CLI commands have been deleted:

### Services

- Update `pim_catalog.validator.constraint.valid_metric` to use `akeneo_measure.provider.measurement_provider`
- Update `akeneo_measure.measure_converter` to use `akeneo_measure.provider.measurement_provider`
- Update `akeneo_measure.manager` to use `akeneo_measure.provider.measurement_provider`
- Update `akeneo_measure.controller.rest.measures` to use `akeneo_measure.provider.measurement_provider`
- Update `legacy_pim_api.controller.measure_family` to use `akeneo_measure.provider.measurement_provider`
- Rename `pim_api.controller.measure_family` to  `legacy_pim_api.controller.measure_family`
- Remove parameter `akeneo_measure.measures_config`
