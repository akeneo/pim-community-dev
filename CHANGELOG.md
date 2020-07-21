# master

## Bug fixes

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

## New features

- MET-197: Add possibility to define that an association type is two way & automatically create inversed association when association type is two way
- MET-14: Measurements (or metrics) are now stored in database
- AOB-277: Add an acl to allow a role member to view all job executions in last job execution grids, job tracker and last operations widget.
- RAC-54: Add a new type of associations: Association with quantity

## Improvements

- CLOUD-1959: Use cloud-deployer 2.2 and terraform 0.12.25
- PIM-9306: Enhance catalog volume monitoring count queries for large datasets

# Technical Improvements

## Classes

## BC breaks

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
