<?php 
 return array (
  'Akeneo\\Channel\\Component\\Query\\GetChannelCodeWithLocaleCodesInterface' => 'Akeneo\\Channel\\Component\\Query\\PublicApi\\GetChannelCodeWithLocaleCodesInterface',
  'Akeneo\\Connectivity\\Connection\\Application\\Audit\\Command\\UpdateProductEventCountCommand' => 'Akeneo\\Connectivity\\Connection\\Application\\Audit\\Command\\UpdateDataSourceProductEventCountCommand',
  'Akeneo\\Connectivity\\Connection\\Application\\Audit\\Command\\UpdateProductEventCountHandler' => 'Akeneo\\Connectivity\\Connection\\Application\\Audit\\Command\\UpdateDataSourceProductEventCountHandler',
  'Akeneo\\Connectivity\\Connection\\Application\\Audit\\Query\\CountDailyEventsByConnectionQuery' => 'Akeneo\\Connectivity\\Connection\\Application\\Audit\\Query\\GetErrorCountPerConnectionQuery',
  'Akeneo\\Connectivity\\Connection\\Domain\\Audit\\Persistence\\Query\\SelectPeriodEventCountsQuery' => 'Akeneo\\Connectivity\\Connection\\Domain\\Audit\\Persistence\\Query\\SelectPeriodEventCountPerConnectionQuery',
  'Akeneo\\Connectivity\\Connection\\Domain\\Audit\\Model\\HourlyInterval' => 'Akeneo\\Connectivity\\Connection\\Domain\\ValueObject\\HourlyInterval',
  'Akeneo\\Connectivity\\Connection\\Infrastructure\\Audit\\AggregateProductEventCounts' => 'Akeneo\\Connectivity\\Connection\\Infrastructure\\Audit\\AggregateAuditData',
  'Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ViolationNormalizer' => 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ConstraintViolationNormalizer',
  'Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\JobExecutionNormalizer' => 'Akeneo\\Platform\\Bundle\\ImportExportBundle\\Normalizer\\InternalApi\\JobExecutionNormalizer',
  'Akeneo\\Tool\\Bundle\\MeasureBundle\\Controller\\ExternalApi\\MeasureFamilyController' => 'Akeneo\\Tool\\Bundle\\MeasureBundle\\Controller\\ExternalApi\\LegacyMeasureFamilyController',
  'Akeneo\\Tool\\Bundle\\MeasureBundle\\Exception\\UnknownMeasureException' => 'Akeneo\\Tool\\Bundle\\MeasureBundle\\Exception\\MeasurementFamilyNotFoundException',
  'Akeneo\\Tool\\Bundle\\MeasureBundle\\Exception\\UnknownFamilyMeasureException' => 'Akeneo\\Tool\\Bundle\\MeasureBundle\\Exception\\UnitNotFoundException',
  'Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\WritableDirectory' => 'Akeneo\\Tool\\Component\\StorageUtils\\Validator\\Constraints\\WritableDirectory',
  'Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\WritableDirectoryValidator' => 'Akeneo\\Tool\\Component\\StorageUtils\\Validator\\Constraints\\WritableDirectoryValidator',
);