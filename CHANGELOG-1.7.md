# 1.7

## Bug Fixes

- #5062 Fixed unit conversion for ElectricCharge cheers @gplanchat!

## Functional improvements

- Change the loading message by a more humanized message to share our love.

## Technical improvements

- TIP-575: Rename FileIterator classes to FlatFileIterator and changes the reader/processor behavior to iterate over the item's position in the file instead of the item's line number in the file.

## BC breaks

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
