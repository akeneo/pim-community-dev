# 1.8

## BC breaks

- Change method `fetchAll` of `Pim\Component\Connector\Processor\BulkMediaFetcher` to use a `Pim\Component\Catalog\Model\ProductValueCollectionInterface` instead of an `Doctrine\Common\Collections\ArrayCollection`
- Remove method `markIndexedValuesOutdated` of `Pim\Component\Catalog\Model\ProductInterface` and `Pim\Component\Catalog\Model\AbstractProduct` 
- Remove classes `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\MetricBaseValuesSubscriber` and `Pim\Bundle\CatalogBundle\EventSubscriber\ORM\MetricBaseValuesSubscriber`
- Remove service `pim_catalog.event_subscriber.metric_base_values`
- Change the constructor of `Pim\Component\Catalog\Model\AbstractMetric` to replace `id` by `family`, `unit`, `data`, `baseUnit` and `baseData` (strings)
- Change the constructor of `Pim\Component\Catalog\Factory\MetricFactory` to add `Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter` and `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\MetricDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Converter\MetricConverter` to add `Pim\Component\Catalog\Factory\MetricFactory`
- Remove method `setId`, `getId`, `setValue`, `getValue`, `setBaseUnit`, `setUnit`, `setBaseData`, `setData` and `setFamily` from `Pim\Component\Catalog\Model\MetricInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\MetricInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to add `Pim\Component\Catalog\Factory\PriceFactory`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\PricesDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface` and replace `"Pim\Component\Catalog\Model\ProductPrice"` `Pim\Component\Catalog\Factory\PriceFactory`
- Add a new argument `$amount` (string) in `Pim\Component\Catalog\Builder\ProductBuilderInterface::addPriceForCurrency()`
- Remove methods `setId`, `getId`, `setValue`, `getValue`, `setCurrency` and `setData` from `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add a new argument `$data` to `AddProductValue` and `CreateProductValue` methods of `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `addPriceForCurrencyWithData` and `removePricesNotInCurrency` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
