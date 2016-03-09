# 1.6.x

## Technical improvements

- PIM-5589: introduce a channels, attribute groups, group types and currencies import using the new import system introduced in v1.4
- PIM-5589: introduce a channels and attribute groups import using the new import system introduced in v1.4
- PIM-5594: Panel state is now stored in the session storage

## BC breaks

- Move `Pim\Bundle\CatalogBundle\Query\Filter\DumperInterface` to `Pim\Bundle\CatalogBundle\Command\DumperInterface`
- Move `Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterDumper` to `Pim\Bundle\CatalogBundle\Command\ProductQueryHelp\AttributeFilterDumper`
- Move `Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterDumper` to `Pim\Bundle\CatalogBundle\Command\ProductQueryHelp\FieldFilterDumper`
- Move namespace `Pim\Bundle\CatalogBundle\Query` to `Pim\Component\Catalog\Query`
- Move namespace `Pim\Bundle\CatalogBundle\Exception` to `Pim\Component\Catalog\Exception`
- Move `Pim\Bundle\CatalogBundle\Event\ProductEvents` to `Pim\Component\Catalog\ProductEvents`
- Move namespace `Pim\Bundle\CatalogBundle\Repository` to `Pim\Component\Catalog\Repository`
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\CurrencyManager`. Please use the service `pim_catalog.repository.currency` instead of `@pim_catalog.manager.currency`.
- Change constructor of `Pim\Bundle\CatalogBundle\AttributeType\PriceCollectionType`. Remove `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\PriceFilter`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Validator\Constraints\CurrencyValidator`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Bundle\FilterBundle\Form\Type\Filter\PriceFilterType`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Move namespace `Pim\Bundle\CatalogBundle\Validator` to `Pim\Component\Catalog\Validator`
- Move `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes` to `Pim\Component\Catalog\AttributeTypes`
- Method `getCategoryIds` of `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` has been removed
- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- Installer fixtures now support csv format for attribute groups setup and not anymore the yml format
- Installer fixtures now support csv format for group types setup and not anymore the yml format
- Add `Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator` as last parameter of
    `Pim\Component\Connector\ArrayConverter\Flat\AssociationTypeStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeGroupStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeOptionStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\CategoryStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\ChannelStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\FamilyStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\GroupStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter,`
    `Pim\Component\Connector\ArrayConverter\Flat\VariantGroupStandardConverter` and
    `Pim\Component\Connector\ArrayConverter\Structured\AttributeOptionStandardConverter`
- Remove deprecated argument $propertyCopier from constructor of `Pim\Component\Catalog\Updater\ProductUpdater` and allow to inject supported fields
- Remove argument $em from constructor of `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and inject `Akeneo\Component\StorageUtils\Saver\SaverInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface`
