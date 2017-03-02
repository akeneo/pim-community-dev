# 1.8

## BC breaks

- Rename class `Pim\Component\Catalog\Completeness\Checker\ChainedProductValueCompleteChecker`  to `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker`
- Change the method `isComplete` of `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to make `Pim\Component\Catalog\Model\ChannelInterface` and `Pim\Component\Catalog\Model\LocaleInterface` mandatory.
- Change the method `supportsValue` of `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to add `Pim\Component\Catalog\Model\ChannelInterface` and `Pim\Component\Catalog\Model\LocaleInterface`.
- Remove class `Pim\Component\Catalog\Completeness\Checker\EmptyChecker`
- Remove classes `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AbstractEntityDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AssociationDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\CategoryDenormalizer`, 
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\FamilyDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\GroupDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AssociationDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValueDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValuesDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\BaseValueDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionsDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\PricesDenormalizer`
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\MetricDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\DateTimeDenormalizer` and `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\FileDenormalizer`
- Remove service parameters: `pim_serializer.denormalizer.flat.family.class`, `pim_serializer.denormalizer.flat.category.class`, `pim_serializer.denormalizer.flat.group.class`, `pim_serializer.denormalizer.flat.association.class`,
    `pim_serializer.denormalizer.flat.product_value.class`, `pim_serializer.denormalizer.flat.product_values.class`, `pim_serializer.denormalizer.flat.base_value.class`, `pim_serializer.denormalizer.flat.attribute_option.class`,
    `pim_serializer.denormalizer.flat.attribute_options.class`, `pim_serializer.denormalizer.flat.prices.class`, `pim_serializer.denormalizer.flat.metric.class`, `pim_serializer.denormalizer.flat.datetime.class`
    and `pim_serializer.denormalizer.flat.file.class`
- Remove method `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Rename method `getEligibleProductIdsForVariantGroup` to `getEligibleProductsForVariantGroup` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`. And returns a `Akeneo\Component\StorageUtils\Cursor\CursorInterface`.
- Remove methods `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
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
- Add a new argument `$amount` (string) to `addPriceForCurrency` method of `Pim\Component\Catalog\Builder\ProductBuilderInterface`
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
- Change the constructor of `Pim\Component\Catalog\Manager\ProductTemplateMediaManager` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` by `Pim\Component\Catalog\Factory\ProductValueFactory`
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
- Change the constructor of `Pim\Component\Catalog\Builder\ProductTemplateBuilder` to remove first argument `Symfony\Component\Serializer\Normalizer\NormalizerInterface`, second argument `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`, and last argument `%pim_catalog.entity.product.class%`
- Change the constructor of `Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer` to remove `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\ProductTemplateUpdater` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as second argument
- Remove third argument `$locale` from `addAttributes` method of `Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface`
- Make protected the method `setValues` in `Pim\Component\Catalog\Updater\VariantGroupUpdater`
