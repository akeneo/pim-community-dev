# 1.6.x

## Functional improvements

- PIM-5592: The product grid keeps the page number when you go back to it
- PIM-5096: Introduce the XLSX quick export
- PIM-5593: The context is now kept in the associations tab of the product edit form
- PIM-5099: The catalog structure can now be exported in XLSX format (families, attributes, attribute options, association types and categories)
- PIM-5097: The catalog structure can now be imported in XLSX format (families, attributes, attribute options, association types and categories)
- PIM-5657: It is now possible to add custom tabs within the job profile and edit pages
- PIM-5427: Add possibility to filter by families for product export
- PIM-5427: It is now possible to filter by families for product export
- PIM-5145: It is now possible to filter product exports by locale
- PIM-5426: It is now possible to filter product exports by completeness
- PIM-5761: The channel no more contains any color information as it was not used anymore in the UI.

## Scalability improvements

- PIM-5542: Optimize the Family normalization

## Technical improvements

- PIM-5589: Introduce a channels, attribute groups, group types, locales and currencies import using the new import system introduced in v1.4
- PIM-5589: Introduce a SimpleFactoryInterface to create simple entities
- PIM-5594: Panel state is now stored in the session storage
- PIM-5645: Bath jobs configuration files can now also be loaded when contained in a folder named 'batch_jobs'. Introduces the new Akeneo Product XLSX Connector
- TIP-342: be able to launch mass edit processes without having to previously store a JobConfiguration and only rely on dynamic configuration
- PIM-5577: The completeness is now calculated every time a product is saved, ie during mass edit, product import and on edit/save of variant groups.
- Call validation in the controller when adding/removing attributes to the family.
- Simplify installation process and the loading of catalogs in Behat by using the import system and `akeneo:batch:job` commands.
- PIM-5653: When using the Product Query Builder, it is now possible to filter on completeness without specifying a locale. Products with a matching completeness for at least one of the locales of the scope will be selected.
- PIM-5653: Introduce a new storage-agnostic Product Reader using the PQB
- PIM-5742: Schedule completeness for ORM is now performed directly through SQL
- Integrates the AkeneoMeasureBundle in our main repository
- TIP-245: Add datetime filters in the Product Query Builder, allowing to select products on "created at" and "updated at" fields.
- PIM-5657: Introduce a `JobTemplateProvider` that holds the job template codes to use for creating, showing, editing job profiles. The provider uses configuration files in order to retrieve overridden templates for specific job names

## BC breaks

- Change constructor of `Pim\Component\Connector\Reader\ProductReader`. Add `Akeneo\Component\Batch\Job\JobRepositoryInterface`.
- Add method `getLastJobExecution` to interface `Akeneo\Component\Batch\Job\JobRepositoryInterface`
- Remove properties editTemplate, showTemplate from `src\Akeneo\Component\Batch\Job\Job`.
- Remove methods setShowTemplate, setEditTemplate from `src\Akeneo\Component\Batch\Job\Job`.
- Change constructor of `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`. Add `Akeneo\Bundle\BatchBundle\Connector\JobTemplateProviderInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\CsvWriter` . Add parameter `Pim\Component\Connector\Writer\File\ColumnSorterInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\CsvProductWriter` . Add parameter `Pim\Component\Connector\Writer\File\ColumnSorterInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\CsvVariantGroupWriter` . Add parameter `Pim\Component\Connector\Writer\File\ColumnSorterInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\XlsxSimpleWriter` . Add `Pim\Component\Connector\Writer\File\ColumnSorterInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\XlsxProductWriter` . Add `Pim\Component\Connector\Writer\File\ColumnSorterInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\XlsxVariantGroupWriter` . Add `Pim\Component\Connector\Writer\File\ColumnSorterInterface`
- Remove method `setAvailableLocales` in `Pim\Component\Catalog\Model\AttributeInterface` and `Pim\Component\Catalog\Model\AbstractAttribute`
- `Pim\Component\Connector\Writer\File\FlatItemBuffer` implements `\Countable`
- `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter` does not implement `Pim\Component\Catalog\Query\Filter\FieldFilterInterface`
- `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter` does not implement `Pim\Component\Catalog\Query\Filter\FieldFilterInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter`. Remove the third parameter `supportedFields`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter`. Remove the third parameter `supportedFields`
- Remove `Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager`
- Remove methods `getTreesQB` and `getAllChildrenQueryBuilder` in `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Remove method `getItemIdsInCategory` in `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`
- Replace all parameters in `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface::getItemsCountInCategory()` by `array $categoryIds`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController`. Remove `Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ChannelType`. Add `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface`
- Change constructor of `Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`. Add `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface`
- Remove deprecated methods `getProductCountByTree` and `getProductsCountInCategory` in `Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController`. Add Symfony validator.
- Change constructor of `Pim\Component\Connector\Reader\File\CsvReader`. Add `Pim\Component\Connector\Reader\File\FileIteratorFactory`.
- Move `Pim\Component\Connector\Reader\File\CsvProductReader` to `Pim\Component\Connector\Reader\File\Product\CsvProductReader`
- Change constructor of `Pim\Component\Connector\Reader\File\Product\CsvProductReader`. Remove `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`. Add `Pim\Component\Connector\Reader\File\FileIteratorFactory` and `Pim\Component\Connector\Reader\File\Product\MediaPathTransformer` 
- Change constructor of `Pim\Bundle\NotificationBundle\Controller\NotificationController`. Remove deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and add `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface`.
- Change constructor of `Pim\Bundle\NotificationBundle\EventSubscriber\JobExecutionNotifier`. Remove deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and add `Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry` and `Pim\Bundle\NotificationBundle\NotifierInterface`.
- Change constructor of `Pim\Bundle\NotificationBundle\Twig\NotificationExtension`. Replace deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` by `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FileController`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`. 
- Change constructor of `Pim\Bundle\EnrichBundle\Imagine\Loader\FlysystemLoader`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`. 
- Change constructor of `Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`. 
- Change constructor of `Pim\Component\Connector\Writer\File\FileExporter`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`. 
- Move `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry` to `Pim\Component\Catalog\AttributeTypeRegistry`
- Move `Pim\Bundle\CatalogBundle\Factory\AttributeFactory` to `Pim\Component\Catalog\Factory\AttributeFactory`
- Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface` twice.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeOptionController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface` and `Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionNormalizer`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`.
- Move `Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory` to `Pim\Component\Catalog\Factory\AttributeRequirementFactory`
- Move `Pim\Bundle\CatalogBundle\Factory\GroupFactory` to `Pim\Component\Catalog\Factory\GroupFactory`
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
- Remove method `getCategoryIds` in `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
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
- Remove argument $em from constructor of `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and inject `Akeneo\Component\StorageUtils\Saver\SaverInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface`. Replace `Doctrine\ORM\EntityRepository` by `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface` and remove `Pim\Bundle\NotificationBundle\Factory\NotificationFactory`.
- Rename createFamily to create in the `Pim\Bundle\CatalogBundle\Factory\FamilyFactory`
- Remove createUser from the `Oro\Bundle\UserBundle\Entity\UserManager`. You can now use the SimpleFactory to create new users
- Remove `Pim\Component\Catalog\Factory\ChannelFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Remove `Akeneo\Component\Classification\Factory\CategoryFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Remove `Pim\Bundle\CatalogBundle\Factory\AssociationTypeFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Remove `Pim\Component\Connector\Processor\Denormalization\AssociationTypeProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove `Pim\Component\Connector\Processor\Denormalization\AttributeGroupProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove `Pim\Component\Connector\Processor\Denormalization\CategoryProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove `Pim\Component\Connector\Processor\Denormalization\FamilyProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove `Pim\Component\Connector\Processor\Denormalization\ChannelProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Invert the two first arguments or the constructor of `Pim\Component\Connector\Processor\Denormalization\AttributeProcessor`
- `Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface` now extends `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Connector\Connector`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Entity\FieldMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Entity\ItemMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Job\BatchException`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Transform\Mapping\FieldMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Transform\Mapping\ItemMapping`
- Removed deprecated class `Pim\Bundle\CatalogBundle\Manager\ChannelManager`.
- Remove the extend of the `Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController` and `Pim\Bundle\EnrichBundle\AbstractController\AbstractController`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\ProductProcessor` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\ODMProductReader` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\ORMProductReader` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Validator\Constraints\ChannelValidator` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\CompletenessRepository` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ChannelRepository` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Factory\FamilyFactory` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\ChannelManager` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor` replace `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CompletenessController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeRequirementsSubscriber` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ProductTemplateType` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Twig\ChannelExtension` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadUserData` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\UserBundle\EventSubscriber\UserPreferencesSubscriber` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Repository\ChannelRepositoryInterface` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Rename `Pim\Component\Catalog\Repository\ChannelRepositoryInterface::getChannelChoices` to `Pim\Component\Catalog\Repository\ChannelRepositoryInterface::getLabelsIndexedByCode`
- Change constructor of `Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository` to inject two more arguments `%akeneo_batch.entity.job_instance.class%` and `%pim_import_export.repository.job_instance.class%`
- Move namespace `Pim\Bundle\TransformBundle\DependencyInjection\Normalizer\Flat` to `Pim\Component\Connector\Normalizer`
- Move namespace `Pim\Bundle\TransformBundle\DependencyInjection\Denormalizer\Flat` to `Pim\Component\Connector\Denormalizer`
- Move namespace `Pim\Bundle\TransformBundle\DependencyInjection\Normalizer\Structured` to `Pim\Component\Catalog\Normalizer`
- Move namespace `Pim\Bundle\TransformBundle\DependencyInjection\Denormalizer\Structured` to `Pim\Component\Catalog\Denormalizer`
- Move and rename class `Pim\Bundle\TransformBundle\DependencyInjection\Compiler\SerializerPass` to `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass`
- Move class `Pim\Bundle\TransformBundle\Cache\CacheClearer` to `Pim\Bundle\BaseConnectorBundle\Cache\CacheClearer`
- Move class `Pim\Bundle\TransformBundle\Cache\DoctrineCache` to `Pim\Bundle\BaseConnectorBundle\Cache\DoctrineCache`
- Move class `Pim\Bundle\TransformBundle\Converter\MetricConverter` to `Pim\Bundle\BaseConnectorBundle\Converter\MetricConverter`
- Remove namespace `Pim\Bundle\BaseConnectorBundle\Exception`
- Remove `TransformBundle`
- Change constructor of `Pim\Component\Catalog\Updater\GroupUpdater` and `Pim\Component\Catalog\Updater\VariantGroupUpdater`, add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Change constructor of `Akeneo\Bundle\BatchBundle\Job\Pim\Bundle\TransformBundle\Normalizer\Structured\FamilyNormalizer` to inject two more dependendies `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` and `Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface`
- Move class `Pim\Bundle\BaseConnectorBundle\Processor\Normalization\VariantGroupProcessor` to `Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor`
- Remove class `Pim\Bundle\ConnectorBundle\JobLauncher\SimpleJobLauncher`  which overrides `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` we now always use `@akeneo_batch.launcher.simple_job_launcher` and not anymore `@pim_connector.launcher.simple_job_launcher`
- Remove parameter `Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface` from constructors of 
    `Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family\SetAttributeRequirements`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductToVariantGroupProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductValueProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\UpdateProductValueProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredFamilyReader`
    `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductReader`
- Remove class `Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface`
- Remove class `Pim\Component\Connector\Factory\JobConfigurationFactory`
- Remove class `Pim\Component\Connector\Model\JobConfiguration`
- Remove class `Pim\Component\Connector\Model\JobConfigurationInterface`
- Removed the `recalculate` and `schedule` option from the `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver` and `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver`
- Remove methods `setConfig` and `getConfig` from `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface`
- Change the method `launch` of `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface`, `$configuration` is now an array and not a string anymore
- Remove deprecated method `setName` from `Akeneo\Component\Batch\Job\Job`
- Remove deprecated classes `Pim\Bundle\BaseConnectorBundle\Step\ValidatorStep` and `Pim\Bundle\BaseConnectorBundle\Validator\Step\CharsetValidator`
- Remove methods `setEventDispatcher` and `setJobRepository` from `Akeneo\Component\Batch\Job\Job`
- Add mandatory arguments `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface` in constructor of `Akeneo\Component\Batch\Job\Job`
- Remove methods `setEventDispatcher`, `setJobRepository` and `setName` from `Akeneo\Component\Batch\Step\AbstractStep`
- Add mandatory arguments `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface` in constructor of `Akeneo\Component\Batch\Step\AbstractStep`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Reader\DummyReader`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Validator\Import\SkipImportValidator`
- Remove `Pim\Bundle\InstallerBundle\Command\LoadDataFixturesDoctrineCommand`, `Pim\Bundle\InstallerBundle\Command\LoadFixturesCommand`
- Remove `Pim\Bundle\InstallerBundle\DataFixtures\*`
- Remove `Pim\Bundle\InstallerBundle\FixtureLoader\*`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter`, add `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`
- Remove `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Remove `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\GroupController`
    replace `Pim\Bundle\CatalogBundle\Manager\GroupManager` by `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\VariantGroupController`
    replace `Pim\Bundle\CatalogBundle\Manager\GroupManager` by `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
    add `Pim\Bundle\UserBundle\Context\UserContext`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`
    replace `Pim\Bundle\CatalogBundle\Manager\CategoryManager` by `Pim\Component\Catalog\Repository\CategoryRepositoryInterface`
    remove the parameter `$categoryClass`
    remove method `getTrees()`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType`
    replace `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
    remove `Pim\Bundle\UserBundle\Context\UserContext`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ChannelType`
    replace `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ProductEditType`
    replace `Pim\Component\Catalog\Repository\FamilyRepositoryInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
- Change constructor of `Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`
    replace `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` remove `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Remove interface `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface` (replace by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`)
- Remove class `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Remove class `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Remove method `findAllAxis` from `Pim\Component\Catalog\RepositoryAttributeGroupRepositoryInterface`
- Remove method `getChoices` from `Pim\Component\Catalog\GroupRepositoryInterface`
- Remove method `getAvailableAttributesAsLabelChoice` from `Pim\Component\Catalog\AttributeRepositoryInterface`
- Rename method `findAllAxis`in `findAvailableAxes` from `Pim\Component\Catalog\AttributeRepositoryInterface`
- Rename method `findAllAxisQB` in `findAllAxesQB` from `Pim\Component\Catalog\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`, add `Symfony\Component\Security\Core\User\UserProviderInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Context option `filter_type` of `Pim\Component\Connector\Normalizer\Flat\ProductNormalizer` changed to `filter_types` and now accepts an array of filter names instead of just one filter name
- Context option `filter_type` of `Pim\Component\Catalog\Normalizer\Structured\ProductNormalizer` changed to `filter_types` and  now accepts an array of filter names instead of just one filter name
- Remove methods `getConfigurationFields()`, `getConfiguration()` and `setConfiguration()` from `Akeneo\Component\Batch\Item\AbstractConfigurableStepElement`
- Remove methods `getConfiguration()` and `setConfiguration()` from `Akeneo\Component\Batch\Job\Job`
- Add argument `Akeneo\Component\Batch\Job\JobParameters` in method `createJobExecution()` of `Akeneo\Component\Batch\Job\JobRepositoryInterface`
- Remove methods `getConfiguration()`, `setConfiguration()` and `getConfigurableStepElements()` from `Akeneo\Component\Batch\Step\StepInterface`
- Remove methods `getConfiguration()`, `setConfiguration()` and `getConfigurableStepElements()` from `Akeneo\Component\Batch\Step\AbstractStep`
- Remove methods `getConfiguration()`, `setConfiguration()` from `Akeneo\Component\Batch\Step\ItemStep`
- Injects `Symfony\Component\DependencyInjection\ContainerInterface` in constructor of `Akeneo\Component\Batch\Updater\JobInstanceUpdater`, `Pim\Bundle\BaseConnectorBundle\Archiver\ArchivableFileWriterArchiver`, `Pim\Bundle\BaseConnectorBundle\Archiver\FileReaderArchiver`, `Pim\Bundle\BaseConnectorBundle\Archiver\FileWriterArchiver`, `Pim\Component\Connector\Processor\Denormalization\JobInstanceProcessor` (avoid a cricular reference due to ConnectorRegistry, should be fixed with TIP-418 by re-working the way we build Jobs)
- Remove argument array $configuration from `Pim\Component\Connector\Step\TaskletInterface::execute()`, we can access to the JobParameters from the StepExecution $stepExecution
- Change constructor of `Pim\Component\Catalog\Updater\AttributeUpdater`, remove `Pim\Component\ReferenceData\ConfigurationRegistryInterface` and the list of reference data types
- Move class `Pim\Component\Catalog\Normalizer\Structured\ReferenceDataNormalizer` to `Pim\Component\ReferenceData\Normalizer\Structured\ReferenceDataNormalizer`
- Move class `Pim\Component\Connector\Normalizer\Flat\ReferenceDataNormalizer` to `Pim\Component\ReferenceData\Normalizer\Flat\ReferenceDataNormalizer`
- Move class `Pim\Component\Catalog\Denormalizer\Structured\ProductValue\ReferenceDataDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Structured\ProductValue\ReferenceDataDenormalizer`
- Move class `Pim\Component\Catalog\Denormalizer\Structured\ProductValue\ReferenceDataCollectionDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Structured\ProductValue\ReferenceDataCollectionDenormalizer`
- Move class `Pim\Component\Connector\Denormalizer\Flat\ProductValue\ReferenceDataDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue\ReferenceDataDenormalizer`
- Move class `Pim\Component\Connector\Denormalizer\Flat\ProductValue\ReferenceDataCollectionDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue\ReferenceDataCollectionDenormalizer`
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`, add `Symfony\Component\Security\Core\User\UserProviderInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Context option `filter_type` of `Pim\Component\Connector\Normalizer\Flat\ProductNormalizer` changed to `filter_types` and now accepts an array of filter names instead of just one filter name
- Context option `filter_type` of `Pim\Component\Catalog\Normalizer\Structured\ProductNormalizer` changed to `filter_types` and  now accepts an array of filter names instead of just one filter name
- Move class `Pim\Component\Catalog\Normalizer\Structured\JobInstanceNormalizer` to `Akeneo\Component\Batch\Normalizer\Structured\JobInstanceNormalizer`
- Change constructor of `Pim\Component\Catalog\Factory\AttributeRequirementFactory` to inject `%pim_catalog.entity.attribute_requirement.class%`
- Change constructor of `Pim\Component\Catalog\Localization\Presenter\MetricPresenter`, replace argument `Symfony\Component\Translation\TranslatorInterface` by `Akeneo\Component\Localization\TranslatorProxy`
- Change constructor of `Pim\Component\Catalog\Builder\ProductTemplateBuilder`, remove argument `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`
- Add `$locale` argument to method `addAttributes` in `Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\VariantGroupAttributeController`, add `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` argument
- Change constructor of `Pim\Component\Catalog\Denormalizer\Structured\ProductValuesDenormalizer`, remove argument `%pim_catalog.entity.attribute.class%` and
    replace argument `Doctrine\Common\Persistence\ManagerRegistry` by `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridProvider`, add `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\JobExecutionNormalizer`, add `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\StepExecutionNormalizer`, add `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType`, add `Akeneo\Component\Batch\Job\JobParametersFactory` and `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` arguments
- Change constructor of `Akeneo\Component\Batch\Model\Warning` to remove the $name argument, we also remove related getter/setter
- Remove getName() from `Akeneo\Component\Batch\Item\AbstractConfigurableStepElement`
- Remove $name argument from addWarning method of `Akeneo\Component\Batch\Model\StepExecution`
- Remove `Pim\Bundle\EnrichBundle\Provider\ColorsProvider`
- Remove `Pim\Bundle\EnrichBundle\Twig\ChannelExtension`
- Remove twig functions `channel_color` and `channel_font_color`
- Remove property color from the model `Pim\Bundle\CatalogBundle\Entity\Channel` and interface `Pim\Component\Catalog\Model\ChannelInterface`
