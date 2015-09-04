# 1.4.x

# 1.4.0-RC1 (2015-09-04)

## Technical improvements

- Rename FileStorage classes and services: File (file information stored in database) => FileInfo, RawFile (physical file on the disk) => File
- Change namespace of Classification component and bundle from Pim to Akeneo

# 1.4.0-BETA3 (2015-09-02)

## BC breaks
- Change the constructor of `Pim\Bundle\UserBundle\Context\UserContext`. Takes `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`, `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`, `Symfony\Component\HttpFoundation\RequestStack`, `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface` and a `$defaultLocale` string
- Remove deprecated `AbstractDoctrineController` parent to `Pim\Bundle\EnrichBundle\Controller\CategoryTreeControlle`. Now it extends `Symfony\Bundle\FrameworkBundle\Controller\Controller`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CategoryTreeController`, added `$rawConfiguration` as the last argument. Removed `Symfony\Component\HttpFoundation\Request`, `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface`, `Symfony\Component\Routing\RouterInterface`, `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Symfony\Component\Form\FormFactoryInterface`, `Symfony\Component\Validator\Validator\ValidatorInterface`, `Symfony\Component\Translation\TranslatorInterface`, `Doctrine\Common\Persistence\ManagerRegistry` and `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Rename service `pim_enrich.controller.category_tree` to `pim_enrich.controller.category_tree.product`
- Change constructor of `src/Pim/Bundle/EnrichBundle/Twig/CategoryExtension` to remove `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` and `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`. Added `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterRegistryInterface`
- Add `$getChildrenTreeByParentId` to `getChildrenTreeByParentId` of `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\GroupColumnsConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\GroupColumnsConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\SortersConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractor` to `Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractor`
- Move `Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface` to `Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface`

# 1.4.0-BETA2 (2015-08-17)

## Bug fixes
- PIM-4443: Exporting a product with an attribute with a numeric code gives an error, full numeric codes for entities are now forbidden except for products

## BC breaks
- Move `Pim\Bundle\ImportExportBundle\Factory\JobInstanceFactory` to `Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory`
- Media classes `Pim\Bundle\CatalogBundle\Model\ProductMedia`, `Pim\Bundle\CatalogBundle\Model\AbstractProductMedia` and `Pim\Bundle\CatalogBundle\Model\ProductMediaInterface` have been removed
- Media denormalizers `Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\MediaDenormalizer`, `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MediaDenormalizer` have been removed
- Media normalizers `Pim\Bundle\TransformBundle\Normalizer\Structured\MediaNormalizer`, `Pim\Bundle\TransformBundle\Normalizer\Flat\MediaNormalizer` have been removed
- Media related classes `Pim\Component\Catalog\Comparator\Attribute\MediaComparator` and `Pim\Bundle\EnrichBundle\Controller\MediaController` have been removed
- Class `Pim\Bundle\BaseConnectorBundle\Writer\File\ProductWriter` has been removed
- Change constructor of `Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter` to remove `Pim\Bundle\CatalogBundle\Manager\MediaManager`, `Pim\Bundle\CatalogBundle\Factory\MediaFactory` and the upload directory parameter and to add `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface`, `Akeneo\Component\FileStorage\File\FileStorerInterface` and `League\Flysystem\MountManager`
- Change constructor of `Pim\Component\Catalog\Updater\Setter\MediaAttributeCopier` to remove `Pim\Bundle\CatalogBundle\Manager\MediaManager` and `Pim\Bundle\CatalogBundle\Factory\MediaFactory` and to add `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface`, `Akeneo\Component\FileStorage\File\FileStorerInterface` and `League\Flysystem\MountManager`
- Change constructor of `Pim\Bundle\TransformBundle\Transformer\Property\MediaTransformer` to remove media class parameter and to add `Akeneo\Component\FileStorage\File\FileStorerInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor` to remove the upload directory parameter and to add the media attributes types
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Writer\File\CsvProductWriter` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Pim\Component\Connector\Writer\File\FileExporterInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Writer\File\CsvVariantGroupWriter` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Pim\Component\Connector\Writer\File\FileExporterInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Archiver\ArchivableFileWriterArchiver` to remove the archive directory parameter
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver` to remove the archive directory parameter
- Change method `createZip` of `Pim\Bundle\BaseConnectorBundle\Filesystem\ZipFilesystemFactory` to return a `League\Flysystem\Filesystem`
- Change method `getArchive` of `Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface` to return a `resource`
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Akeneo\Component\FileStorage\File\FileStorerInterface`
- Remove method `generateFilenamePrefix` of `Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Akeneo\Component\FileStorage\File\FileStorerInterface` and to remove the upload directory parameter
- Change constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\AttributeSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ChannelSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\FamilySaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\GroupSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- `updateAction` has been removed from the `Pim\Bundle\EnrichBundle\Controller\ProductController`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` to remove product remover `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\ProductRemover`
- Moved the `removeAction` from `Pim\Bundle\EnrichBundle\Controller\ProductController` to `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`

# 1.4.0-BETA1 (2015-07-31)

## Features

## Technical improvements
- Revamp the Readers, Processors and Writers to import data, make them more simple and re-useable
- Use DEFERRED_EXPLICIT as Doctrine changeTrackingPolicy (for all models)
- Continue to group persist()/flush() to the dedicated layer (SaverInterface) to avoid to have them everywhere in the stack
- Category filter is separated from other datagrid filters for performance concerns (parallel ajax requests)
- Use MySQL as a non blocking session storage
- Handle Doctrine mapping overrides smoothly (no more need to copy/paste the full mapping of an entity or a document)
- Product edit form revamp to handle lot of attributes, locales and channels per product
- Re-work the import/export engine by introducing a new Connector (component+bundle), the old one is deprecated but still useable
- Re-work the installer to use the new import engine (remove the yml format for fixtures)
- Remove the yml category and association fixtures
- Migrate to Symfony 2.7

## Bug fixes
- PIM-3874: clicking a category gives an error with only "list categories" permission
- PIM-3771: Create version when modifying variant group attribute
- PIM-2743: keep page per view on datagrids
- PIM-3758: Hide the category tree on products grid if user do not have the right to list categories
- PIM-3929: Categories with circular references are skipped in processor during import
- PIM-4024: Fix for metric and price denormalizer
- PIM-4314: Added missing translation keys
- PIM-4112: Not translated Error message when wrong format import
- PMI-4032: Fix wrong error message when deleting used attribute option by a published product

## BC breaks
- Change the constructor of `Pim\Bundle\UserBundle\Context\UserContext`, `Pim\Bundle\UserBundle\Form\Type\UserType`, `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, `Pim\Bundle\EnrichBundle\Controller\JobExecutionController`, `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`, `Pim\Bundle\EnrichBundle\Controller\VariantGroupController` and `Pim\Bundle\EnrichBundle\EventListener\UserContextListener`. Replace `Symfony\Component\Security\Core\SecurityContext` by `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, added `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change interface `Symfony\Component\Validator\ValidatorInterface` to `Symfony\Component\Validator\Validator\ValidatorInterface`
- Change interface `Symfony\Component\OptionsResolver\OptionsResolverInterface` to `Symfony\Component\OptionsResolver\OptionsResolver`
- Change interface `Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase` to `Symfony\Component\Form\Test\TypeTestCase`
- Change interface `Symfony\Component\Form\Extension\Core\View\ChoiceView` to `Symfony\Component\Form\ChoiceList\View\ChoiceView`
- Change interface `Symfony\Component\Validator\MetadataFactoryInterface` to `Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface`
- Change interface `Symfony\Component\Validator\ExecutionContextInterface` to `Symfony\Component\Validator\Context\ExecutionContextInterface`
- Remove bundle StofDoctrineExtensionsBundle
- Symfony events `FormEvents::POST_BIND` and `FormEvents::BIND` have been replaced by `FormEvents::POST_SUBMIT` and `FormEvents::SUBMIT` in `Pim\Bundle\TranslationBundle\Form\Subscriber`
- Rename methods `bind()` and `postBind()` by `submit()` and `postSubmit()` in `Pim\Bundle\TranslationBundle\Form\Subscriber`
- Rename method `setDefaultOptions()` to `configureOptions()` in all form types
- Service `pim_catalog.validator.product` calls now `Symfony\Component\Validator\Validator\RecursiveValidator`, take the `pim_catalog.validator.context.factory` service as the first argument and remove the fourth and fifth argument
- Add `Symfony\Component\HttpFoundation\RequestStack` as the fifth argument in `Pim\Bundle\UserBundle\Context\UserContext`, `$defaultLocale` become the sixth argument and `Symfony\Component\HttpFoundation\Request` is no longer called
- Remove connections `report_source` and `report_target` from dbal in `app/config/config.yml`
- `normalize` method of `Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValueNormalizer` returns an array with a "data" key instead of "value" key
- `Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\VariantGroupWriter` and `Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\VariantGroupProcessor` are deprecated
- Change the constructor of `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, removed `Pim\Bundle\VersioningBundle\Manager\VersionManager`
- Rename method `onKernelRequest` to `findUsername` on `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`
- Change the constructor of `Pim\Bundle\VersioningBundle\Manager\VersionManager`, added `Symfony\Component\EventDispatcher\EventDispatcherInterface` as the last argument
- Change the constructor of `Pim/Bundle/TransformBundle/Denormalizer/Structured/ProductValuesDenormalizer`, removed `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`, added `Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry` as the second argument and `pim_catalog.entity.attribute.class` as the last argument
- Change the constructor of `Pim/Bundle/TransformBundle/Normalizer/Structured/GroupNormalizer`, added `Symfony\Component\Serializer\Normalizer\DenormalizerInterface` as the last argument
- Change the constructor of `Pim/Bundle/CatalogBundle/Doctrine/Common/Remover/AttributeRemover` to accept `Pim/Bundle/CatalogBundle/Builder/ProductTemplateBuilder` as the fourth argument and accept `Pim/Bundle/CatalogBundle/Entity/Repository/ProductTemplateRepository` as the fifth argument
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/CompletenessRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductCategoryRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductMassActionRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/AssociationRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AssociationTypeRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeGroupRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeOptionRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/CategoryRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/ChannelRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/CompletenessRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/CurrencyRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/FamilyRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/GroupRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/GroupTypeRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/LocaleRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductCategoryRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductMassActionRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductRepository.php
- Remove Pim/Bundle/CatalogBundle/ProductManager::createProductValue, saveProduct, saveAllProducts
- Add AttributeRepositoryInterface, FamilyRepositoryInterface, AssociationTypeRepositoryInterface and EventDispatcherInterface as arguments of the constructor of Pim/Bundle/CatalogBundle/Builder/ProductBuilder
- Remove ProductManager and add AttributeRepositoryInterface in arguments of the constructor of Pim/Bundle/CatalogBundle/Factory/FamilyFactory
- Remove ProductManager, add ProductBuilderInterface, ProductRepositoryInterface, $productClass and $productValueClass in arguments of the constructor of Pim/Bundle/TransformBundle/Transformer/ProductTransformer
- Remove ProductManager, add AttributeRepositoryInterface in arguments of the constructor of Pim/Bundle/CatalogBundle/Validator/Constraints/SingleIdentifierAttributeValidator
- Move Pim/Bundle/CatalogBundle/Updater/Setter/AbstractValueSetter.php → Pim/Component/Catalog/Updater/Setter/AbstractAttributeSetter
- Remove AttributeRepositoryInterface argument from constructor of Pim/Component/Catalog/Updater/Setter/SetterRegistryInterface, remove method get(
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/BooleanValueSetter -> Pim/Component/Catalog/Updater/Setter/BooleanAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/DateValueSetter -> Pim/Component/Catalog/Updater/Setter/DateAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/MediaValueSetter -> Pim/Component/Catalog/Updater/Setter/MediaAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/MetricValueSetter -> Pim/Component/Catalog/Updater/Setter/MetricAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/MultiSelectValueSetter -> Pim/Component/Catalog/Updater/Setter/MultiSelectAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/NumberValueSetter -> Pim/Component/Catalog/Updater/Setter/NumberAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/PriceCollectionValueSetter -> Pim/Component/Catalog/Updater/Setter/PriceCollectionAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/SimpleSelectValueSetter -> Pim/Component/Catalog/Updater/Setter/SimpleSelectAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/TextValueSetter -> Pim/Component/Catalog/Updater/Setter/TextAttributeSetter
- Remove setValue and supports from Pim/Component/Catalog/Updater/Setter/SetterInterface
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/CopierInterface -> Pim/Component/Catalog/Updater/Copier/AttributeCopierInterface
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/AbstractValueCopier -> src/Pim/Component/Catalog/Updater/Copier/AbstractAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/BaseValueCopier -> src/Pim/Component/Catalog/Updater/Copier/BaseAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/MediaValueCopier -> src/Pim/Component/Catalog/Updater/Copier/MediaAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/MetricValueCopier -> src/Pim/Component/Catalog/Updater/Copier/MetricAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/MultiSelectValueCopier -> src/Pim/Component/Catalog/Updater/Copier/MultiSelectAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/PriceCollectionValueCopier -> src/Pim/Component/Catalog/Updater/Copier/PriceCollectionAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/SimpleSelectValueCopier -> src/Pim/Component/Catalog/Updater/Copier/SimpleSelectAttributeCopier
- Remove MediaManager from constructor of Pim\Bundle\CatalogBundle\Manager\ProductManager
- Remove deprecated handleMedia() and handleAllMedia() from Pim\Bundle\CatalogBundle\Manager\ProductManager
- Replace argument ProductManager by MediaManager in constructor of Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB\ProductWriter
- Remove deprecated Pim/Bundle/BaseConnectorBundle/Reader/ORM/CursorReader
- Remove deprecated Pim/Bundle/BaseConnectorBundle/Reader/Doctrine/BulkProductReader and Pim/Bundle/BaseConnectorBundle/Reader/Doctrine/ObsoleteProductReader
- Remove deprecated Pim/Bundle/CatalogBundle/Repository/ReferableEntityRepositoryInterface and Pim/Bundle/CatalogBundle/Doctrine/ReferableEntityRepository
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/AssociationTypeManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/AttributeManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/CategoryManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/FamilyManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/GroupManager
- Change arguments of Pim/Bundle/EnrichBundle/Controller/AssociationController to use AssociationTypeRepositoryInterface, ProductRepositoryInterface, ProductBuilderInterface, EngineInterface
- Remove arguments ChannelRepositoryInterface, LocaleRepositoryInterface, add argument AttributeValuesResolver in Pim/Bundle/CatalogBundle/Builder/ProductBuilder constructor
- Remove arguments DenormalizerInterface, ValidatorInterface, ObjectDetacherInterface, $class from the constructor of Pim/Bundle/BaseConnectorBundle/Processor/Denormalization/AbstractProcessor
- Add methods `getReferenceDataName` and `setReferenceDataName` to Pim\Bundle\CatalogBundle\Model\AttributeInterface.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\MassEditActionController`, removed `Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry`, `Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher`, `$massEditLimit`, added `Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface`, `Pim\Bundle\ConnectorBundle\JobLauncher\SimpleJobLauncher`, `Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository`, `Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry`, `Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface`, `Pim\Bundle\EnrichBundle\MassEditAction\MassEditFormResolver`
- Remove `Pim\Bundle\EnrichBundle\Form\Subscriber\MassEditAction\AddSelectedOperationSubscriber`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToGroupsType`, added `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` as first argument
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToVariantGroupType`, added `Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface` as first argument and `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` as second argument
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`, added `Pim\Bundle\CatalogBundle\Manager\CategoryManager` as second argument
- Rename `Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType\MassEditOperatorType` -> `Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditAction`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, removed `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` and `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, updated method `setGroups` to accept `Doctrine\Common\Collections\ArrayCollection`, removed method `getWarningMessages`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Remove constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup`, removed method `setObjectsToMassEdit`, `perform`, `getWarningMessages`, `getValidVariantGroups`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily` now imlements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily`, removed method `affectsCompleteness`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Remove constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`, removed method `getTrees`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, removed `Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface`, `Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager`, `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`, added `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`, `Pim\Bundle\CatalogBundle\Manager\MediaManager`, `Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager` and `$uploadDir`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, removed method `affectsCompleteness`, `perform`, `getCommonAttributes`, `getWarningMessages`
- Change interface `Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface`, removed method `getFormType`, `getFormOptions`, `initialize`, `perform`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\FamilyMassEditOperation`
- `Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\ProductWriter` now implements `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` instead of `Pim\Bundle\TransformBundle\Cache\CacheClearer`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\ProductMassEditOperator`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\FamilyMassEditOperation`
- Completely refactor the `\Pim\Bundle\DataGridBundle\Controller\ProductExportController`. It implement nothing instead of `\Pim\Bundle\DataGridBundle\Controller\ExportController`. Now it launched job in backend end.
- Remove `Pim\Bundle\EnrichBundle\DependencyInjection\Compiler\RegisterMassEditOperatorsPass`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\FamilyMassEditOperator`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\MassEditOperatorInterface`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\CategoryRepository` → `Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository`
- Move `Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface` → `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Remove Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface replaced by Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface
- Remove Pim\Bundle\TransformBundle\Normalizer\Filter\FilterableNormalizerInterface
- Remove `Pim\Bundle\EnrichBundle\Controller\ProductAttributeController`
- ProductTemplateUpdater now takes ProductPropertyUpdaterInterface as argument and not anymore ProductUpdaterInterface
- Remove fixtures_product_yml and fixtures_association_yml from the InstallerBundle, csv format is now mandatory for products
- ProductUpdater takes ValidatorInterface as second argument
- Rename `Pim\Bundle\TransformBundle\Builder\FieldBuilder` to `Pim\Component\Connector\ArrayConverter\Flat\AttributeColumnInfoExtractor`
- Method `createAttribute` of Pim/Bundle/CatalogBundle/Manager/AttributeManager.php is now deprecated use `AttributeFactory::createAttribute` instead
- Constructor of `Pim\Bundle\EnrichBundle\Controller\ChannelController` now takes a BulkSaver as last argument (to save locales)
- Constructor of `Pim\Bundle\CatalogBundle\Manager\AttributeManager` has been changed
- Constructor of `Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager` has been changed
- Constructor of `Pim\Bundle\CommentBundle\Manager\CommentManager` has been changed
- Constructor of `Pim\Bundle\DatagridBundle\Manager\DatagridViewManager` has been changed
- Constructor of `Pim\Bundle\EnrichBundle\Manager\SequentialEditManager` has been changed
- Depreciate and change constructor of `Pim\Bundle\TransformBundle\Builder\FieldNameBuilder`
- Replace the argument ProductManager by ProductRepositoryInterface in the constructor of `Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantAxisValidator`
- Add an argument BulkSaverInterface in the constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\CategoryRemover`
- Constructor of `Pim\Bundle/CatalogBundle/Manager/CompletenessManager` : removed dependency on `Symfony\Component\Validator\ValidatorInterface`
- Constructor of `Pim\Bundle/CatalogBundle/Manager/CompletenessManager` : added dependency on `Pim\Component\Catalog\Completeness\Checker\ChainedProductValueCompleteChecker`
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\CategoryManager`, added `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` and `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` as third and fourth argument.
- Change `Pim\Bundle\CatalogBundle\Manager\CategoryManager`, updated method `getEntityRepository` to return return a `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CategoryTreeController`, added `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` and `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` as last arguments
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController`, added `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` as last argument
- Move `Pim\Bundle\FilterBundle\Filter\Product\CategoryFilter` to `Pim\Bundle\FilterBundle\Filter\CategoryFilter`
- Change constructor of `Pim\Bundle\FilterBundle\Filter\CategoryFilter`, last argument is now a `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter`, second argument is now a `Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface`
- Remove the option 'flush_only_object' from `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver`, `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver`, `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseRemover`
- Add an argument `Pim/Bundle/VersioningBundle/Factory/VersionFactory` in the constructor of `Pim/Bundle/VersioningBundle/Builder/VersionBuilder`
- Add an argument `Symfony\Component\EventDispatcher\EventDispatcher` in the constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\ORM\CategoryReader`, argument is now a `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\UserBundle\Context\UserContext`, replace `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, add `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Constructor of `Pim\Bundle\CatalogBundle\Manager\CategoryManager` has been changed

