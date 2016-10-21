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
