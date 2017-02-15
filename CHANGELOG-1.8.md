# 1.8

## BC breaks

- Remove method `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove class `Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser.php`
- Remove service and parameter: `pim_pim_versioning.update_guesser.product_value` and `pim_versioning.update_guesser.product_value.class`
- Add method `setValues` and `setIdentifier` to `Pim\Component\Catalog\Model\ProductInterface`
- Remove method `setNormalizedData` from `Pim\Component\Catalog\Model\ProductInterface`
- Change method `fetchAll` of `Pim\Component\Connector\Processor\BulkMediaFetcher` to use a `Pim\Component\Catalog\Model\ProductValueCollectionInterface` instead of an `Doctrine\Common\Collections\ArrayCollection`
- Remove method `markIndexedValuesOutdated` from `Pim\Component\Catalog\Model\ProductInterface` and `Pim\Component\Catalog\Model\AbstractProduct` 
- Remove classes `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\MetricBaseValuesSubscriber` and `Pim\Bundle\CatalogBundle\EventSubscriber\ORM\MetricBaseValuesSubscriber`
- Remove service `pim_catalog.event_subscriber.metric_base_values`
- Change the constructor of `Pim\Component\Catalog\Model\AbstractMetric` to replace `id` by `family`, `unit`, `data`, `baseUnit` and `baseData` (strings)
- Change the constructor of `Pim\Component\Catalog\Factory\MetricFactory` to add `Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter` and `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\MetricDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Converter\MetricConverter` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface`
- Remove method `setId`, `getId`, `setValue`, `getValue`, `setBaseUnit`, `setUnit`, `setBaseData`, `setData` and `setFamily` from `Pim\Component\Catalog\Model\MetricInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\MetricInterface`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\PricesDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface` and replace `"Pim\Component\Catalog\Model\ProductPrice"` `Pim\Component\Catalog\Factory\PriceFactory`
- Add a new argument `$amount` (string) in `Pim\Component\Catalog\Builder\ProductBuilderInterface::addPriceForCurrency()`
- Remove methods `setId`, `getId`, `setValue`, `getValue`, `setCurrency` and `setData` from `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add a new argument `$data` to `addProductValue` method of `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `createProductValue`, `addProductValue`, `addPriceForCurrencyWithData` and `removePricesNotInCurrency` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove classes `Pim\Component\Catalog\Updater\Setter\TextAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\MetricAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\BooleanAttributeSetter`,
    `Pim\Component\Catalog\Updater\Setter\DateAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\NumberAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\SimpleSelectAttributeSetter`,
    `Pim\Component\Catalog\Updater\Setter\MultiSelectAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\PriceCollectionAttributeSetter`, `Pim\Component\ReferenceData\Updater\Setter\ReferenceDataSetter`,
    `Pim\Component\ReferenceData\Updater\Setter\ReferenceDataCollectionSetter`
- Add `Pim\Component\Catalog\Updater\Setter\AttributeSetter`
- Remove classes `Pim\Component\Catalog\Updater\Copier\SimpleSelectAttributeCopier`, `Pim\Component\Catalog\Updater\Copier\MultiSelectAttributeCopier` and `Pim\Component\Catalog\Updater\Copier\PriceCollectionAttributeCopier`
- Rename class `Pim\Component\Catalog\Updater\Copier\BaseAttributeCopier` in `Pim\Component\Catalog\Updater\Copier\AttributeCopier`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder` to remove `Pim\Component\Catalog\Validator\AttributeValidatorHelper`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder` to remove `Pim\Component\Catalog\Validator\AttributeValidatorHelper`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AttributeCopier`,`Pim\Component\Catalog\Updater\Copier\MetricAttributeCopier` and `Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier`
    to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as third argument
- Remove methods `addPriceForCurrency` and `addMissingPrices` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `getId`, `setId`, `getProduct`, `getEntity`, `setProduct`, `setEntity`, `addOption`, `addPrice`, `removePrice`, `RemoveOption`, `addData` and `isRemovable` from `Pim\Component\Catalog\Model\ProductValueInterface` and `Pim\Component\Catalog\Model\AbstractProductValue`
- Change the constructor of `Pim\Component\Catalog\Manager\ProductTemplateMediaManager` to add `Pim\Component\Catalog\Factory\ProductValueFactory` as third argument
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover` to replace `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` by `Pim\Component\Catalog\Factory\ProductValueFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover` to add `Pim\Component\Catalog\Factory\ProductValueFactory` as third argument
- Change the constructor of `Pim\Component\Catalog\Model\AbstractProductValue` to add `Pim\Component\Catalog\Model\AttributeInterface`, `channel` (string), `locale` (string), `data` (mixed)
- Remove methods `setData`, `setText`, `setDecimal`, `setOptions`, `setOption`, `setPrices`, `setPrice`, `setBoolean`, `setVarchar`, `setMedia`, `setMetric`, `setScope`, `setLocale`, `setDate` and `setDatetime` from `Pim\Component\Catalog\Model\ProductValueInterface`
    and make them protected in `Pim\Component\Catalog\Model\AbstractProductValue`
- Change the constructor of `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\PricesDenormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as third parameter
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\FamilyFilter` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\GroupsFilter` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Remove useless class `Pim\Component\Catalog\Validator\ConstraintGuesser\IdentifierGuesser`
- Remove useless service and parameter `pim_catalog.validator.constraint_guesser.identifier` and `pim_catalog.validator.constraint_guesser.identifier.class`
