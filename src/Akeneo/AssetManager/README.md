# Asset Families public API:

For the Bounded Context Asset Families, we are following the Hexagonal Architecture (Application/Domain/Infrastructure).

So to permit the other Bounded Context to communicate with us, we created a PublicApi Port in the Infrastructure Layer. 
You will be able to find all the classes you have access to the directory `Akeneo/AssetFamily/back/Infrastructure/PublicApi`.

In this Port, we have separated the communication with the others Bounded Context with different Adapters (ex : Analytics, Onboarder...).

## Analytics

- Get the max of attributes average per asset family for only the localizable : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlLocalizableOnly`
- Get the max of attributes average per asset family for only the scopable : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlScopableOnly`
- Get the max of attributes average per asset family for the localizable and the scopable : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlScopableAndLocalizable`
- Get the max volumes average : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxVolumes`
- Get the volume of an axis of limitation : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\CountVolume`
- Get the max number of attributes average per asset family : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerAssetFamily`
- Get the max number of assets average per asset family : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily`
- Get the max number of values average per asset family : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfValuesPerAsset`
- Get the number of asset families : `Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlCountAssetFamilies`

## Onboarder

- Find all asset labels : `Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder\FindAllAssetLabels`
- Find asset labels by identifiers : `Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder\FindAssetLabelsByIdentifiers`


## Public Domain Events

We also exposed some of our domain events to be able to listen them outside of our Bounded Context. You can find those events in `Akeneo/AssetFamily/back/Domain/Event`.
They are marked with "@api" when they are public.
