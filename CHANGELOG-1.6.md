# 1.6.x

## Functional improvements

- PIM-5592: The product grid keeps the page number when you go back to it
- PIM-5096: Introduce the XLSX quick export
- PIM-5593: The context is now kept in the associations tab of the product edit form

## Scalability improvements

- PIM-5542: Optimize the Family normalization

## Technical improvements

- PIM-5589: Introduce a channels, attribute groups, group types, locales and currencies import using the new import system introduced in v1.4
- PIM-5589: Introduce a SimpleFactoryInterface to create simple entities
- PIM-5594: Panel state is now stored in the session storage
- PIM-5645: Bath jobs configuration files can now also be loaded when contained in a folder named 'batch_jobs'. Introduces the new Akeneo XLSX Connector
- TIP-342: be able to launch mass edit processes without having to previously store a JobConfiguration and only rely on dynamic configuration

## BC breaks

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
- Remove argument $em from constructor of `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and inject `Akeneo\Component\StorageUtils\Saver\SaverInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface`. Replace `Doctrine\ORM\EntityRepository` by `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface` and remove `Pim\Bundle\NotificationBundle\Factory\NotificationFactory`.
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
- Remove methods `setConfig` and `getConfig` from `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface`
- Remove deprecated method `setName` from `Akeneo\Component\Batch\Job\Job`
- Remove methods `setEventDispatcher` and `setJobRepository` from `Akeneo\Component\Batch\Job\Job`
- Add mandatory arguments `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface` in constructor of `Akeneo\Component\Batch\Job\Job`
- Remove deprecated classes `Pim\Bundle\BaseConnectorBundle\Step\ValidatorStep` and `Pim\Bundle\BaseConnectorBundle\Validator\Step\CharsetValidator`
- Remove methods `setEventDispatcher`, `setJobRepository` and `setName` from `Akeneo\Component\Batch\Step\AbstractStep`
- Add mandatory arguments `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface` in constructor of `Akeneo\Component\Batch\Step\AbstractStep`
