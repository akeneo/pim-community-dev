# Reference Entities public API:

For the Bounded Context Reference Entities, we are following the Hexagonal Architecture (Application/Domain/Infrastructure).

So to permit the other Bounded Context to communicate with us, we created a PublicApi Port in the Infrastructure Layer. 
You will be able to find all the classes you have access to the directory `Akeneo/ReferenceEntity/back/Infrastructure/PublicApi`.

In this Port, we have separated the communication with the others Bounded Context with different Adapters (ex : Analytics, Onboarder...).

## Analytics

- Get the max of attributes average per reference entity for only the localizable : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity\SqlLocalizableOnly`
- Get the max of attributes average per reference entity for only the scopable : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity\SqlScopableOnly`
- Get the max of attributes average per reference entity for the localizable and the scopable : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity\SqlScopableAndLocalizable`
- Get the max volumes average : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxVolumes`
- Get the volume of an axis of limitation : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\CountVolume`
- Get the max number of attributes average per reference entity : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerReferenceEntity`
- Get the max number of records average per reference entity : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfRecordsPerReferenceEntity`
- Get the max number of values average per reference entity : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfValuesPerRecord`
- Get the number of reference entities : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlCountReferenceEntities`

## Onboarder

- Find all record labels : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder\FindAllRecordLabels`
- Find record labels by identifiers : `Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder\FindRecordLabelsByIdentifiers`


## Public Domain Events

We also exposed some of our domain events to be able to listen them outside of our Bounded Context. You can find those events in `Akeneo/ReferenceEntity/back/Domain/Event`.
They are marked with "@api" when they are public.
