# 1.6.x

## Functional improvements

- PIM-5592: the product grid keeps the page number when you go back to it
- PIM-5096: introduces the XLSX quick export

## Technical improvements

- PIM-5589: introduce a channels, attribute groups, group types, locales and currencies import using the new import system introduced in v1.4
- PIM-5589: introduce a SimpleFactoryInterface to create simple entities
- PIM-5594: Panel state is now stored in the session storage

## BC breaks

- Move `Pim\Bundle\CatalogBundle\Factory\FamilyFactory` to `Pim\Component\Catalog\Factory\FamilyFactory`
- Move `Pim\Bundle\CatalogBundle\Factory\MetricFactory` to `Pim\Component\Catalog\Factory\MetricFactory`
- Move `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` to `Pim\Component\Catalog\Manager\CompletenessManager`
- Move `Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager` to `Pim\Component\Catalog\Manager\AttributeGroupManager`
- Move `Pim\Bundle\CatalogBundle\Manager\VariantGroupAttributesResolver` to `Pim\Component\Catalog\Manager\VariantGroupAttributesResolver`
- Move `Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplier` to `Pim\Component\Catalog\Manager\ProductTemplateApplier`
- Move `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder` to `Pim\Component\Catalog\Builder\ProductTemplateBuilder`
- Move `Pim\Bundle\CatalogBundle\Builder\ProductBuilder` to `Pim\Component\Catalog\Builder\ProductBuilder`
- Move `Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver` to `Pim\Component\Catalog\Manager\AttributeValuesResolver`
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\FamilyManager`.
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\ProductManager`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\DeleteProductsMassActionHandler`. Remove `Pim\Bundle\CatalogBundle\Manager\ProductManager`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\AttributeType`. Replace `Pim\Bundle\CatalogBundle\Manager\AttributeManager` by `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeManager`. Add `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry` and `Pim\Bundle\CatalogBundle\Factory\AttributeFactory`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeOptionController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeManager`. Add `Pim\Bundle\CatalogBundle\Manager\AttributeOptionsSorter` and `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`.
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\AttributeManager`.
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
- Installer fixtures now support csv format for locales setup and not anymore the yml format
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
- Rename createFamily to create in the `Pim\Bundle\CatalogBundle\Factory\FamilyFactory`
- Removed createUser from the `Oro\Bundle\UserBundle\Entity\UserManager`. You can now use the SimpleFactory to create new users
- Removed `Pim\Component\Catalog\Factory\ChannelFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Removed `Akeneo\Component\Classification\Factory\CategoryFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Removed `Pim\Bundle\CatalogBundle\Factory\AssociationTypeFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Removed `Pim\Component\Connector\Processor\Denormalization\AssociationTypeProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Removed `Pim\Component\Connector\Processor\Denormalization\AttributeGroupProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Removed `Pim\Component\Connector\Processor\Denormalization\CategoryProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Removed `Pim\Component\Connector\Processor\Denormalization\FamilyProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Removed `Pim\Component\Connector\Processor\Denormalization\ChannelProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Inverted the two first arguments or the constructor of `Pim\Component\Connector\Processor\Denormalization\AttributeProcessor`
- `Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface` now extends `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Connector\Connector`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Entity\FieldMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Entity\ItemMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Job\BatchException`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Transform\Mapping\FieldMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Transform\Mapping\ItemMapping`
- Remove argument $objectIdResolver from constructors of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\FamilyFilter` and `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter` 
- Removed deprecated class `Pim\Bundle\CatalogBundle\Manager\ChannelManager`.
- Remove the extend of the `Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController` and `Pim\Bundle\EnrichBundle\AbstractController\AbstractController`.
- Change constructor of `Pim/Bundle/BaseConnectorBundle/Processor/CsvSerializer/ProductProcessor` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/BaseConnectorBundle/Processor/ProductToFlatArrayProcessor` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/BaseConnectorBundle/Reader/Doctrine/ODMProductReader` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/BaseConnectorBundle/Reader/Doctrine/ORMProductReader` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/BaseConnectorBundle/Validator/Constraints/ChannelValidator` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/Repository/CompletenessRepository` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/CatalogBundle/Doctrine/ORM/Repository/ChannelRepository` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/CatalogBundle/Factory/FamilyFactory` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/CatalogBundle/Manager/ChannelManager` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/EnrichBundle/Connector/Processor/QuickExport/ProductToFlatArrayProcessor` replace `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/EnrichBundle/Controller/CompletenessController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/EnrichBundle/Controller/FamilyController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/EnrichBundle/Form/Subscriber/AddAttributeRequirementsSubscriber` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/EnrichBundle/Form/Type/ProductTemplateType` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/EnrichBundle/Twig/ChannelExtension` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/InstallerBundle/DataFixtures/ORM/LoadUserData` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Bundle/UserBundle/EventSubscriber/UserPreferencesSubscriber` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim/Component/Catalog/Repository/ChannelRepositoryInterface` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Rename `Pim\Component\Catalog\Repository\ChannelRepositoryInterface::getChannelChoices` to `Pim\Component\Catalog\Repository\ChannelRepositoryInterface::getLabelsIndexedByCode`
- Change constructor of `Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository` to inject two more arguments `%akeneo_batch.entity.job_instance.class%` and `%pim_import_export.repository.job_instance.class%`
