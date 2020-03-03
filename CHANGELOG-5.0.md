# 5.0.0

## New features

- MET-14: Measurements (or metrics) are now stored in database

## Improvements

# Technical Improvements

## Classes

## BC breaks

### Codebase

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
- Update `pim_api.controller.measure_family` to use `akeneo_measure.provider.measurement_provider`
- Remove parameter `akeneo_measure.measures_config`
