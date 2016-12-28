# 1.7

## Bug Fixes

- GITHUB-5038: Fixed job name visibility checker to also check additional config
- GITHUB-5062: Fixed unit conversion for ElectricCharge, cheers @gplanchat!
- GITHUB-5294: Fixed infinite loading if no attribute is configured as a product identifier, cheers @gplanchat!
- GITHUB-5337: Fixed Widget Registry. Priority is now taken in account.

## Deprecations

- In the _Product Query Builder_, aka _PQB_, (`Pim\Component\Catalog\Query\ProductQueryBuilderInterface`), filtering products by the following filters is now deprecated: `categories.id`, `family.id`, `groups.id`. 
  Filters `categories`, `family` and `groups` have been introduced and the _PQB_ now uses them by default. The filters `categories.code`, `family.code` and `groups.code` are deprecated. 
  In the next version, the deprecated filters will be removed.
- As it's not needed anymore to convert `codes` to `ids` in order to filter products, `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolver` and `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface` are now deprecated.
- Creating a product value with the ProductBuilder (`Pim\Component\Catalog\Denormalizer\Standard\ProductValueDenormalizer`) using the `createProductValue` function is now deprecated. It is advised to use the ProductValueFactory (`Pim\Component\Catalog\Factory\ProductValueFactory`) instead.

## Functional improvements

- Change the loading message by a more humanized message to share our love.
- Add Energy measure family and conversions cheers @JulienDotDev!
- Complete Duration measure family with week, month, year and related conversions cheers @JulienDotDev!
- Add CaseBox measure family and conversions, cheers @gplanchat!

## Technical improvements

- GITHUB-5380: Add `Pim\Component\User\Model\GroupInterface`
- GITHUB-4696: Ping the server before updating job and step execution data to prevent "MySQL Server has gone away" issue cheers @qrz-io!
- TIP-575: Rename FileIterator classes to FlatFileIterator and changes the reader/processor behavior to iterate over the item's position in the file instead of the item's line number in the file.
- TIP-662: Removed the WITH_REQUIRED_IDENTIFIER option from `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product` as it was not used anymore.
- TIP-667: Introduce a product value factory service to instanciate product values.

## BC breaks
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValueDenormalizer` to add `Pim\Component\Catalog\Factory\ProductValueFactory`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to add `Pim\Component\Catalog\Factory\ProductValueFactory`
- Add `getAllChildrenCodes` to `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` 
- Change the constructor of `Pim\Bundle\FilterBundle\Filter\Product\InGroupFilter` to add `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectCodeResolver`
- Remove WebServiceBundle
- Remove `wsse_secured` firewall in security.yml
- Change the constructor of `Pim\Component\Connector\Writer\File\Yaml\Writer` to add `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`
- Remove useless class `Pim\Component\ReferenceData\Normalizer\Structured\ReferenceDataNormalizer`
- Move all classes in `Pim\Component\Catalog\Denormalizer\Structured\` to `Pim\Component\Catalog\Denormalizer\Standard\`
- Move all classes in `Pim\Component\ReferenceData\Denormalizer\Structured\` to `Pim\Component\ReferenceData\Denormalizer\Standard\`
- Move `Akeneo\Component\Batch\Normalizer\Structured\JobInstanceNormalizer` to `Akeneo\Component\Batch\Normalizer\Standard\JobInstanceNormalizer`
- Rename service `pim_serializer.normalizer.job_instance` to `pim_catalog.normalizer.standard.job_instance`
- Rename service `pim_connector.array_converter.structured.job_instance` to `pim_connector.array_converter.standard.job_instance`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\AssociationTypeNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\AttributeGroupNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\Attribute` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\CategoryNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\ChannelNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\FamilyNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\FileNormalizer` to remove `Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\GroupNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\LocaleNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductValueNormalizer` to remove `Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface`
- Change the constructor of `Pim\Bundle\DashboardBundle\Widget\CompletenessWidget` to add the FQCN `Pim\Bundle\CatalogBundle\Entity\ChannelTranslation` (string)
- Change the constructor of `Pim\Bundle\EnrichBundle\Form\Type\ChannelType` to add `Pim\Bundle\UserBundle\Context\UserContext`
- `Pim\Component\Catalog\Model\ChannelInterface` implements `Akeneo\Component\Localization\Model\TranslatableInterface`
- Add a new argument `$localeCode` (string) in `Pim\Component\Catalog\RepositoryChannelRepositoryInterface::getLabelsIndexedByCode()`
- Add a new argument `$localeCode` (string) in `Pim\Component\Catalog\CompletenessRepositoryInterface::getProductsCountPerChannels()` and `Pim\Component\Catalog\CompletenessRepositoryInterface::getCompleteProductsCountPerChannels()`
- Change the constructor of `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductAssociation` to remove `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver`
- Change the constructor of `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product`. Add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` and `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`. Remove `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterRegistryInterface` and `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor`
- Add method `findDatagridViewBySearch` to the `Pim\Bundle\DataGridBundle\Repository\DatagridViewRepositoryInterface`
- Remove methods `listColumnsAction` and  `removeAction` of the `Pim\Bundle\DataGridBundle\Controller\DatagridViewController`
- Change the constructor of `Pim\Bundle\DataGridBundle\Controller\DatagridViewController` to keep `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` as the only argument
- Change the constructor of `Pim\Bundle\DataGridBundle\Controller\Rest\DatagridViewController`add `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface` and `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\CategoryController` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductCommentController`. Add `Akeneo\Component\Localization\Presenter\PresenterInterface` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Move `Pim\Component\Catalog\Normalizer\Structured\AssociationTypeNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\AssociationTypeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\AttributeGroupNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\AttributeGroupNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\AttributeNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\AttributeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\AttributeOptionNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\AttributeOptionNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\CategoryNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\CategoryNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ChannelNormalizer` to `Pim\Component\Catalog\Normalizer\Structured\ChannelNormalizer`.
- Move `Pim\Component\Catalog\Normalizer\Structured\CurrencyNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\CurrencyNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\DateTimeNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\DateTimeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\FamilyNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\FamilyNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\FileNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\FileNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\GroupNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\ProxyGroupNormalizer`. Remove `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`, `Symfony\Component\Serializer\Normalizer\NormalizerInterface` and `Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer` from the constructor and add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as first and second parameters.
- Move `Pim\Component\Catalog\Normalizer\Structured\GroupTypeNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\GroupTypeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\LocaleNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\LocaleNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\MetricNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\Product\MetricNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ProductAssociationsNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\Product\AssociationsNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ProductNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ProductPriceNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\Product\PriceNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ProductPropertiesNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ProductValueNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\Product\ProductValueNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\ProductValuesNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\Product\ProductValuesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Structured\TranslationNormalizer` to `Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer`
- Move `Pim\Bundle\CommentBundle\Normalizer\Structured\CommentNormalizer` to `Pim\Bundle\CommentBundle\Normalizer\Standard\CommentNormalizer` and remove `Akeneo\Component\Localization\Presenter\PresenterInterface` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` from constructor.
- Rename service `pim_serializer.normalizer.association_type` to `pim_catalog.normalizer.standard.association_type`
- Rename service `pim_serializer.normalizer.attribute` to `pim_catalog.normalizer.standard.attribute`
- Rename service `pim_serializer.normalizer.attribute_group` to `pim_catalog.normalizer.standard.attribute_group`
- Rename service `pim_serializer.normalizer.attribute_option` to `pim_catalog.normalizer.standard.attribute_option`
- Rename service `pim_serializer.normalizer.category` to `pim_catalog.normalizer.standard.category`
- Rename service `pim_serializer.normalizer.channel` to `pim_catalog.normalizer.standard.channel`
- Rename service `pim_serializer.normalizer.datetime` to `pim_catalog.normalizer.standard.datetime`
- Rename service `pim_serializer.normalizer.family` to `pim_catalog.normalizer.standard.family`
- Rename service `pim_serializer.normalizer.group` to `pim_catalog.normalizer.standard.proxy_group`
- Rename service `pim_serializer.normalizer.product` to `pim_catalog.normalizer.standard.product`
- Rename service `pim_serializer.normalizer.product_properties` to `pim_catalog.normalizer.standard.product.properties`
- Rename service `pim_serializer.normalizer.product_associations` to `pim_catalog.normalizer.standard.product.associations`
- Rename service `pim_serializer.normalizer.product_values` to `pim_catalog.normalizer.standard.product.product_values`
- Rename service `pim_serializer.normalizer.product_value` to `pim_catalog.normalizer.standard.product.product_value`
- Rename service `pim_serializer.normalizer.product_price` to `pim_catalog.normalizer.standard.product.price`
- Rename service `pim_serializer.normalizer.metric` to `pim_catalog.normalizer.standard.product.metric`
- Rename service `pim_serializer.normalizer.file` to `pim_catalog.normalizer.standard.file`
- Rename service `pim_serializer.normalizer.currency` to `pim_catalog.normalizer.standard.currency`
- Rename service `pim_serializer.normalizer.group_type` to `pim_catalog.normalizer.standard.group_type`
- Rename service `pim_serializer.normalizer.locale` to `pim_catalog.normalizer.standard.locale`
- Rename service `pim_serializer.normalizer.label_translation` to `pim_catalog.normalizer.standard.translation`
- Rename service `pim_serializer.normalizer.comment` to `pim_comment.normalizer.standard.comment`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController` to add `Pim\Component\Enrich\Converter\ConverterInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\VariantGroupController` to add `Pim\Component\Enrich\Converter\ConverterInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to remove the tenth argument `tmpStorageDir` and add `Pim\Component\Enrich\Converter\ConverterInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\GroupNormalizer` to add `Pim\Component\Enrich\Converter\ConverterInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add `Pim\Component\Enrich\Converter\ConverterInterface`
- Change route from `pim_user_user_rest_get` to `pim_user_user_rest_get_current`. Route `pim_user_user_rest_get` now fetch a user the given username.
