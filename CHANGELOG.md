# 1.4.x

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

# 1.3.16 (2015-06-08)

## Bug fixes
- PIM-4312: Fix price indexes on MongoDB
- PIM-4112: Not translated Error message when wrong format import

# 1.3.15 (2015-06-05)

## Bug fixes
- PIM-4308: MongoDB indexes are removed on schema update
- PIM-4314: Added missing translation keys

# 1.3.14 (2015-06-03)

## Bug fixes
- PIM-4227: fix BC break introduced in 1.3.13
- PIM-4309: Fix bug processing media with a non existing media

# 1.3.13 (2015-05-29)

## Bug fixes
- PIM-4223: Fix grid sorting order initialization (changed to be consistent with Platform behavior)
- PIM-4227: Disable product versionning on category update (never used and very slow)

# 1.3.12 (2015-05-22)

## Bug fixes
- PIM-4182: Fix product values normalization when decimals are not allowed
- PIM-4203: fix mass edit of families after sorting by label
- PIM-4208: Fix js memory leak on a product edit form with scopable attributes

# 1.3.11 (2015-05-13)

## Bug fixes
- PIM-4044: Fix pressing Enter on a Product grid filter makes the page "unclickable"
- PIM-4146: Fix the delete confirmation message that was not translated
- PIM-4176: Fix context not keeped after product saving

# 1.3.10 (2015-05-05)

## Bug fixes
- PIM-4113: Initialize cursors on first item during creation
- PIM-4122: Preserve variant structure during an import containing empty values from a template

# 1.3.9 (2015-04-21)

## Bug fixes
- PIM-4082: Fix error translation keys when creating a job

# 1.3.8 (2015-04-14)

## Bug fixes
- PIM-4045: Fix completeness computation with behat
- PIM-4047: Missing translation key for a number value which should not be decimal in edit form
- PIM-3848: fix completeness not well calculated after attribute requirements deletion
- PIM-4050: Fix float val in range number error message

## Technical improvements
- rollback the visibility of the ProductRepository::buildByScope from protected to public (as in 1.2) to ensure connectors compatibility

# 1.3.7 (2015-04-03)

## Bug fixes
- PIM-3961: Fix inconsistencies in unique value constraint validator
- PIM-3416: Fix less / more than date filter
- PIM-4019: option code is properly displayed during option deletion in attribute edit form

# 1.3.6 (2015-04-01)

## Bug fixes
- PIM-2401: Association grid, add the Is associated sorter (MongoDB impl)
- PIM-3926: Set explicit message for 403 error
- PIM-3938: Querying products with PQB and using Sorters will not return an ordered Cursor
- PIM-3956: Fix user can add an attribute in a group even if he does not have the permission
- PIM-3965: Fix groups without labels are not displayed in the product grid cell
- PIM-3971: Cache results for Select2 on product edit form
- PIM-4017: Fix save an empty media attribute with variant group
- PIM-3931: Remove db query from CsvReader constructor

## BC breaks
- Change the constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber` to include `Oro\Bundle\SecurityBundle\SecurityFacade`and `Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface`

# 1.3.5 (2015-03-19)

## Bug fixes
- PIM-2874: Fix bad title on failed submit
- PIM-3836: Fix translations of a custom job label instance
- PIM-3909: Keep channel filter between product datagrid and edit form
- PIM-3925: Do not show system menu if no item allowed

# 1.3.4 (2015-03-11)

## Bug fixes
- PIM-3806: Delete an attribute from a product template
- PIM-3843: Product deletion raise an exception
- PIM-3786: Attribute type should not be blank for import
- PIM-3437: Fix applying datagrid views and columns when not using hash navigation
- PIM-3817: Fix error when mass editing after refreshing the grid
- PIM-3849, PIM-3880: Fix bad completeness scope on mass edit actions

## BC breaks
- Change the constructor of `Pim/Bundle/CatalogBundle/Doctrine/Common/Remover/AttributeRemover` to accept `Pim/Bundle/CatalogBundle/Builder/ProductTemplateBuilder` as the fourth argument and accept `Pim/Bundle/CatalogBundle/Entity/Repository/ProductTemplateRepository` as the fifth argument

# 1.3.3 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.3.2 (2015-02-27)

## Bug fixes
- PIM-3665: Remove media even if file not on filesystem
- PIM-3834: add missing cascade detach product -> associations, product -> completenesses
- PIM-3820: Attribute option translation not well handled on import
- PIM-3762: Fix the bug on image not well displayed on pdf export
- PIM-3307: Fix filter dropdown rendering

# 1.3.1 (2015-02-24)

## Bug fixes
- PIM-3775: Fix variant group import from an archive
- PIM-3783: Fix issue with Rest API and MediaNormalizer
- PIM-3791: Fix fatal error on MongoDB mass pending persister
- PIM-3757: Fix bugs on product query filter on multiple filter applied at once

# 1.3.0 - "Hare Force" (2015-02-12)

# 1.3.0-RC3 (2015-02-12)

## Technical improvements
- PIM-3482: clean composer.json

# 1.3.0-RC2 (2015-02-12)

## Bug fixes
- PIM-1235: Fix information message when trying to delete a category tree used by a channel
- PIM-3068: Darken navigation arrows in product grid
- PIM-2094: Regroup attributes validation properties in a subpanel
- PIM-3700: Fix comment display on long words
- PIM-2103: Display a loading when deleting a category tree
- PIM-3394: Improve forgotten password screen
- PIM-3398: Translate units on metric fields on product edit form
- PIM-3575: Sort csv column in a determinist way (alphabetically) on export
- PIM-3752: Fixed the hard coded entry `Select Job` on import/export creation
- PIM-3736: Fix wrong count of products in Variant group view
- PIM-3628: Fixed products not being versioned when modifing a metric, price or media value
- PIM-3753: Fix completeness filter

## BC breaks
- Change the constructor of `Pim/Bundle/CatalogBundle/Doctrine/Common/Remover/AttributeRemover` to accept `Pim/Bundle/CatalogBundle/Builder/ProductTemplateBuilder` as the fourth argument and accept `Pim/Bundle/CatalogBundle/Entity/Repository/ProductTemplateRepository` as the fifth argument
- Add a TranslatorInterface argument in MetricType::__construct
- Change of constructor of `Pim/Bundle/CommentBundle/Form/Type/CommentType` to accept `Pim\Bundle\CommentBundle\Entity` as a string for the third argument
- Added new constructor to `Pim/Bundle/DataGridBundle/Form/Type/DatagridViewType` to accept `Pim\Bundle\DataGridBundle\Entity\DataGridView` as a string for the first argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AssociationType` to accept `Pim\Bundle\CatalogBundle\Model\Product` as a string for the third argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AssociationType` to accept `Pim\Bundle\CatalogBundle\Entity\AssociationType` as a string for the fourth
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AssociationType` to accept `Pim\Bundle\CatalogBundle\Entity\Group` as a string for the fifth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AssociationType` to accept `Pim\Bundle\CatalogBundle\Model\Association` as a string for the sixth argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/AssociationTypeType` to accept `Pim\Bundle\CatalogBundle\Model\AssociationType` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/AttributeGroupType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeGroup` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/AttributeOptionCreateType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeOption` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/AttributeOptionType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeOption` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/AttributeOptionValueType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/AttributeRequirementType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeRequirement` as a string for the first argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AttributeType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeTranslation` as a string for the third argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AttributeType` to accept `Pim\Bundle\CatalogBundle\Entity\Attribute` as a string for the fourth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AttributeType` to accept `Pim\Bundle\CatalogBundle\Entity\AttributeGroup` as a string for the fifth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AvailableAttributesType` to accept `Pim\Bundle\CatalogBundle\Entity\Attribute` as a string for the third argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/AvailableAttributesType` to accept `Pim\Bundle\CatalogBundle\Model\AvailableAttribute` as a string for the fourth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/CategoryType` to accept `Pim\Bundle\CatalogBundle\Entity\Category` as a string for the first argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/CategoryType` to accept `Pim\Bundle\CatalogBundle\Entity\CategoryTranslation` as a string for the second argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/ChannelType` to accept `Pim\Bundle\CatalogBundle\Entity\Category` as a string for the fourth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/ChannelType` to accept `Pim\Bundle\CatalogBundle\Entity\Channel` as a string for the fifth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/FamilyType` to accept `Pim\Bundle\CatalogBundle\Entity\Attribute` as a string for the fourth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/FamilyType` to accept `Pim\Bundle\CatalogBundle\Entity\Family` as a string for the fifth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/GroupType` to accept `Pim\Bundle\CatalogBundle\Entity\Attribute` as a string for the second argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/GroupType` to accept `Pim\Bundle\CatalogBundle\Entity\Group` as a string for the third argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/GroupTypeType` to accept `Pim\Bundle\CatalogBundle\Entity\GroupType` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/ImageType` to accept `Pim\Bundle\CatalogBundle\Entity\ProductMedia` as a string for the fist argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/AddToGroupsType` to accept `Pim\Bundle\CatalogBundle\Entity\Group` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/AddToGroupsType` to accept `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups` as a string for the second argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/AddToVariantGroupType` to accept `Pim\Bundle\CatalogBundle\Entity\Group` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/AddToVariantGroupType` to accept `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup` as a second for the third argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/ChangeFamilyType` to accept `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/ChangeStatusType` to accept `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus` as a string for the first argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/ClassifyType` to accept ` Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify` as a string for the second argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/EditCommonAttributesType` to accept `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` as a string for the fifth argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MassEditAction/SetAttributeRequirementsType` to accept `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MediaType` to accept `Pim\Bundle\CatalogBundle\Model\ProductMedia` as a string for the first argument
- Added new constructor to `Pim/Bundle/EnrichBundle/Form/Type/MetricType` to accept `Pim\Bundle\CatalogBundle\Model\Metric` as a string for the first argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Form/Type/PriceType` to accept `Pim\Bundle\CatalogBundle\Model\Price` as a string for the first argument
- Change the constructor of `Pim/Bundle/ImportExportBundle/Form/Type/JobInstanceType` to accept `Symfony\Component\Translation\TranslatorInterface` as for the second argument
- Change the constructor of `Pim/Bundle/BaseConnectorBundle/Writer/Doctrine/ProductWriter` to accept `Pim\Bundle\CatalogBundle\Manager\MediaManager` as for the first argument instead of `Pim\Bundle\CatalogBundle\Manager\ProductManager`
- Change the constructor of `Pim/Bundle/CatalogBundle/Manager/AssociationTypeManager` to remove the $eventDispatcher argument from the constructor
- Change the constructor of `Pim/Bundle/CatalogBundle/Manager/AttributeManager` to remove the $eventDispatcher argument from the constructor
- Change the constructor of `Pim/Bundle/CatalogBundle/Manager/CategoryManager` to remove the $eventDispatcher argument from the constructor
- Change the constructor of `Pim/Bundle/CatalogBundle/Manager/GroupManager` to remove the $eventDispatcher argument from the constructor
- Change the constructor of `Pim/Bundle/CatalogBundle/Manager/FamilyManager` to remove the $eventDispatcher argument from the constructor
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/AttributeGroupController` to accept `Akeneo\Component\StorageUtils\Remover\RemoverInterface` and `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` as for the fourteenth and fifteenth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/CategoryTreeController` to accept `Akeneo\Component\StorageUtils\Remover\RemoverInterface` and `Akeneo\Component\StorageUtils\Saver\SaverInterface` as for the fourteenth and fifteenth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/FamilyController` to accept `Akeneo\Component\StorageUtils\Remover\RemoverInterface` and `Akeneo\Component\StorageUtils\Saver\SaverInterface` as for the fourteenth and fifteenth argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/GroupController` to accept `Akeneo\Component\StorageUtils\Remover\RemoverInterface` as for the fourteenth  argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/ProductController` to accept `Akeneo\Component\StorageUtils\Remover\RemoverInterface` as for the fourteenth  argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/VariantGroupController` to accept `Akeneo\Component\StorageUtils\Remover\RemoverInterface` as for the fourteenth  argument
- Change the constructor of `Pim/Bundle/EnrichBundle/Controller/VariantGroupController` and remove `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface`

# 1.3.0-RC1 (2015-02-03)

## Features
- Export a product as PDF
- Add a widget in the navigation bar to display notifications when import/export jobs finish
- Add the sequential edit for a selected list of products
- Add comments on a product
- Load dashboard widgets asynchronously and allow to refresh the data
- Add filters for image and file attributes
- Add values to variant group and be able to apply them on products belonging to the variant group
- Remove deprecated attribute property *Usable as a grid column* because all attributes are now useable as columns
- Refactor of the attribute options screen to handle more than 100 options (AJAX)
- Load all product grid filters asynchronously
- Improve the UI of the datagrid column configuration popin
- Enhance the display of permissions in the role permissions edit form
- Better display on batch warnings
- Redesign of the loading box
- Add an information message when there is no common attribute in the mass-edit
- Add ACL on entity history
- Add a notice in manage attribute groups and manage categories
- Re-design select all options in grid filters
- Display symbol and not code for currencies in the grid
- Enhance the product edit form header on small resolutions (1024)

## Technical improvements
- Provide a cleaner ProductQueryBuilder API to ease the selection of products
- Provide a ProductUpdater API to mass update products
- Introduce the 'pim_validator' service to be able to validate products and cascade on values with dynamic constraints
- Introduce commands to ease developer's life (`pim:product:query`, `pim:product:query-help`, `pim:product:update`, `pim:product:validate`)
- Add flat / csv denormalizers for product data
- Remove the fixed mysql socket location
- Switch to stability stable
- Base template has been moved from `app/Resources/views` to `PimEnrichBundle/Resources/views`
- Make classes of `Pim\Bundle\CatalogBundle\Model` consistent with the interfaces
- Move filter transformation to CatalogBundle
- Re-work `Pim\Bundle\ImportExportBundle\Controller\JobProfileController` to make it more readable
- Re-work the `Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilder` to provide a clear and extensible API to query products
- Normalize the managers by introducing 4 interfaces, `Akeneo\Component\Persistence\SaverInterface`, `Akeneo\Component\Persistence\BulkSaverInterface`, `Akeneo\Component\Persistence\RemoverInterface` and `Pim\Component\Persistence\BulkRemoverInterface`
- Add a view manager to help integrators to override and add elements to the UI (tabs, buttons, etc)
- Add a check on passed values in ORM filters
- Add a requirement regarding the need of the `exec()` function (for job executions)
- Use `Pim\Bundle\CatalogBundle\Model\ProductInterface` instead of `Pim\Bundle\CatalogBundle\Model\AbstractProduct`
- Use `Pim\Bundle\CatalogBundle\Model\ProductValueInterface` instead of `Pim\Bundle\CatalogBundle\Model\AbstractProductValue`
- Use `Pim\Bundle\CatalogBundle\Model\ProductPriceInterface` instead of `Pim\Bundle\CatalogBundle\Model\AbstractProductPrice`
- Use `Pim\Bundle\CatalogBundle\Model\ProductMediaInterface` instead of `Pim\Bundle\CatalogBundle\Model\AbstractMetric`
- Use `Pim\Bundle\CatalogBundle\Model\AttributeInterface` instead of `Pim\Bundle\CatalogBundle\Model\AbstractAttribute`
- Use `Pim\Bundle\CatalogBundle\Model\CompletenessInterface` instead of `Pim\Bundle\CatalogBundle\Model\AbstractCompleteness`
- Allow to generate many versions in a single request
- Introduce `Pim\Bundle\CatalogBundle\Model\GroupInterface` instead of `Pim\Bundle\CatalogBundle\Entity\Group`
- Use `Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface` instead of `Pim\Bundle\CatalogBundle\Entity\AttributeOption`
- Use `Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface` instead of `Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue`
- Use `Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface` instead of `Pim\Bundle\CatalogBundle\Entity\AttributeRequirement`
- Use `Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface` instead of `Pim\Bundle\CatalogBundle\Entity\AssociationType`
- Use `Pim\Bundle\CatalogBundle\Model\GroupTypeInterface` instead of `Pim\Bundle\CatalogBundle\Entity\GroupType`
- Use `Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface` instead of `Pim\Bundle\CatalogBundle\Entity\AttributeGroup`
- Use `Pim\Bundle\CatalogBundle\Model\ChannelInterface` instead of `Pim\Bundle\CatalogBundle\Entity\Channel`
- Use `Pim\Bundle\CatalogBundle\Model\CurrencyInterface` instead of `Pim\Bundle\CatalogBundle\Entity\Currency`
- Removed `icecat_demo` from fixtures

## BC breaks
- Rename `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineOrmTargetEntitiesPass` to `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelsPass`
- Rename `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineOrmTargetEntitiesPass` to `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelsPass`
- Rename `Pim\Bundle\UIBundle\Form\Transformer\IntegerTransformer` to `Pim\Bundle\UIBundle\Form\Transformer\NumberTransformer`
- Remove useless applySorterByAttribute, applySorterByField from Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository
- Introduce ``Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface`
- Change visibility of `Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilder::addAttributeFilter`, `Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilder::addFieldFilter` from public to protected
- Change visibility of `Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilder::addAttributeSorter`, `Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilder::addFieldSorter` from public to protected
- Remove `ProductManager` from `ProductFilterUtility::__construct` argument
- Remove `ProductFilterUtility::getAttribute()`
- Two new methods have been added to `Pim\Bundle\DashboardBundle\Widget\WidgetInterface`: `getAlias` and `getData`
- Constructor of `Pim\Bundle\DashboardBundle\Controller\WidgetController` has been changed (most dependencies have been removed)
- Method `Pim\Bundle\DashboardBundle\Controller\WidgetController::showAction()` has been removed in favor of `listAction` to render all widgets and `dataAction` to provide widget data
- Constructors of `Pim\Bundle\DashboardBundle\Widget\CompletenessWidget` and `Pim\Bundle\DashboardBundle\Widget\LastOperationsWidget` have been changed
- `Pim\Bundle\DashboardBundle\Widget\Registry:add()` now accepts the widget (`WidgetInterface`) as the first argument and position as the second
- Remove CatalogContext argument from ProductQueryBuilder::__construct
- Remove ProductRepository from Datagrid Sorters __construct
- Remove deprecated ProductRepositoryInterface::getProductQueryBuilder
- Replace setProductQueryBuilder by setProductQueryFactory and add a getObjectManager in ProductRepositoryInterface
- Add a ProductQueryFactoryInterface argument in ProductDatasource::__construct
- Add a $productOrmAdapterClass argument in DatasourceAdapterResolver::__construct
- Remove is_default, translatable from attributeOption mapping
- Remove AttributeOption::setDefault, AttributeOption::isDefault
- Remove AttributeInterface::getDefaultOptions
- Remove $optionClass and $optionValueClass arguments from the AttributeManager::__construct
- Remove createAttributeOption, createAttributeOptionValue, getAttributeOptionClass from the attributeManager (now in the attributeOptionManager)
- Add a $attributeOptionManager argument in AttributeController::__construct
- Remove MediaManager argument from CsvProductWriter::__construct
- Update CsvProductWriter::copyMedia argument to replace AbstractProductMedia by an array
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor`. `Doctrine\Common\Persistence\ManagerRegistry` is used as fourth argument and is mandatory now. The data class is the fifth argument.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\*` and `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\*` to remove the CatalogContext
- Remove ProductRepositoryInterface::findOneBy (still a native support for ORM)
- Add ProductRepositoryInterface::findOneByIdentifier and findOneById
- Remove ProductRepositoryInterface::buildByScope
- Remove ProductRepositoryInterface::findByExistingFamily
- Remove ProductRepositoryInterface::findAllByAttributes
- Move CatalogBundle/Doctrine/ORM/CompletenessJoin and CatalogBundle/Doctrine/ORM/ValueJoin to CatalogBundle/Doctrine/ORM/Join
- Move CatalogBundle/Doctrine/ORM/CriteriaCondition to CatalogBundle/Doctrine/ORM/Condition
- Remove the 'defaultValue' property of attributes and `Pim/Bundle/CatalogBundle/Model/AttributeInterface::setDefaultValue()` and `getDefaultValue()`
- Refactor `Pim\Bundle\EnrichBundle\Controller\SequentialEditController`
- Remove the `Pim\Bundle\CatalogBundle\Doctrine\(ORM|MongoDBODM)\Filter\BaseFilter` to use proper dedicated filters
- The parameter `category_id` for the route `pim_enrich_product_listcategories` has been renamed to `categoryId`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\File\CsvProductReader`. Now `FieldNameBuilder`, channel, locale and currency entity classes are mandatory.
- AttributeTypeRegistry replaces AttributeTypeFactory, changed constructors for AttributeManager, ProductValueFormFactory, AddAttributeTypeRelatedFieldsSubscriber
- Drop Pim\Bundle\CatalogBundle\Doctrine\EntityRepository, ORM repositories now extends Doctrine\ORM\EntityRepository, no more access to buildAll(), build() and buildOne()
- Replace AssociationTypeRepository::buildMissingAssociationTypes by AssociationTypeRepository::findMissingAssociationTypes
- Replace AttributeGroupRepository::buildAllWithTranslations by AttributeGroupRepository::findAllWithTranslations
- Replace GroupTypeRepository::buildAll by GroupTypeRepository::getAllGroupsExceptVariantQB
- In AttributeGroupHandler::_construct, replace ObjectManager argument by AttributeGroupManager
- Remove unused ProductManager::removeAll() method
- Add an ObjectManager argument in DatagridViewManager::__construct
- Change of constructor of `Pim\Bundle\EnrichBundle\Form\Handler\ChannelHandler` to accept `Pim\Bundle\CatalogBundle\Manager\ChannelManager` as third argument
- Change of constructor of `Pim\Bundle\EnrichBundle\Form\Handler\FamilyHandler` to accept `Pim\Bundle\CatalogBundle\Manager\FamilyManager` as third argument
- Change of constructor of `Pim\Bundle\EnrichBundle\Form\Handler\GroupHandler` to accept `Pim\Bundle\CatalogBundle\Manager\GroupManager` as third argument and `Pim\Bundle\CatalogBundle\Manager\ProductManager` as fourth argument
- Change of constructor of `Pim\Bundle\CatalogBundle\Manager\FamilyManager` to accept `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` as sixth argument
- Change of constructor of `Pim\Bundle\CatalogBundle\Manager\ChannelManager` to accept `Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository` as second argument and `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` as third argument
- Use `Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface` in constructors of AssociationTypeController, AttributeController, AttributeGroupController, ChannelController, FamilyController, GroupController, GroupTypeController
- Change of constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController` to remove `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` argument
- Remove ObjectManager first argument of `Pim\Bundle\EnrichBundle\Builder\ProductBuilder` constructor and delete method removeAttributeFromProduct
- Change of constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator` to accept `Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository` as third argument to replace `Pim\Bundle\CatalogBundle\Manager\ChannelManager` argument
- Method `Pim\Bundle\CatalogBundle\Entity\Category::addProduct()`, `Pim\Bundle\CatalogBundle\Entity\Category::removeProduct()`, `Pim\Bundle\CatalogBundle\Entity\Category::setProducts()` have been removed.
- We now use uniqid() to generate filename prefix (on media attributes)
- Change of constructor of `Pim\Bundle\EnrichBundle\Controller\ChannelController` to add a `RemoverInterface` as last argument
- Change of constructor of `Pim\Bundle\EnrichBundle\Controller\GroupTypeController.php` to add a `RemoverInterface` as last argument
- `ProductPersister` and `BasePersister` has been replaced by `ProductSaver` in CatalogBundle
- Add methods `execute()`, `getQueryBuilder()`, `setQueryBuilder()` in `ProductQueryBuilderInterface`
- Add `MediaFactory` and `ObjectManager` arguments in MediaManager contructor
- Change of constructor `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to remove arguments `Pim\Bundle\CatalogBundle\Builder\ProductBuilder` and  `Pim\Bundle\CatalogBundle\Factory\MetricFactory`. `Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface` is expected as second argument and `Symfony\Component\Serializer\Normalizer\NormalizerInterface` is expected as last but one.
- Enabled field in normalized data is now a boolean in mongodb. You can migrate your database with the script located at `./upgrades/1.2-1.3/mongodb/migrate_statuses.php`
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\ProductManager` to accept a `Pim\Component\Resource\Model\SaverInterface` as second argument. Add a `Pim\Component\Resource\Model\BulkSaverInterface` as third argument
- FieldNameBuilder constructor now expects $channelClass and $localeClass FQCN
- The Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeUpdateGuesser has been removed
- IndexCreator constructor now expects a LoggerInterface as last argument
- Add methods isLocaleSpecific and getLocaleSpecificCodes in AttributeInterface
- AssociationTransformer constructor now expects a $associationTypeClass as last argument
- Inject the GroupFactory as las constructor argument in GroupController and VariantGroupController
- (Akeneo storage) The following constants have been moved:
  * `DOCTRINE_ORM` and `DOCTRINE_MONGODB_ODM` from `Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension` are now located in `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension`
  * `DOCTRINE_MONGODB`, `ODM_ENTITIES_TYPE` and `ODM_ENTITY_TYPE` from `Pim\Bundle\CatalogBundle\PimCatalogBundle` are now located in `Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle`
- (Akeneo storage) The container parameter `pim_catalog.storage_driver` has been deleted
- (Akeneo storage) The following services have been renamed:
  * `pim_catalog.event_subscriber.resolve_target_repository` has been renamed to `akeneo_storage_utils.event_subscriber.resolve_target_repository`
  * `pim_catalog.doctrine.smart_manager_registry` has been renamed to `akeneo_storage_utils.doctrine.smart_manager_registry`
  * `pim_catalog.doctrine.table_name_builder` has been renamed to `akeneo_storage_utils.doctrine.table_name_builder`
  * `pim_catalog.factory.referenced_collection` has been renamed to `akeneo_storage_utils.factory.referenced_collection`
  * `pim_catalog.event_subscriber.mongodb.resolve_target_repositories` has been renamed to `akeneo_storage_utils.event_subscriber.mongodb.resolve_target_repository`
  * `pim_catalog.event_subscriber.mongodb.entities_type` has been renamed to `akeneo_storage_utils.event_subscriber.mongodb.entities_type`
  * `pim_catalog.event_subscriber.mongodb.entity_type` has been renamed to `akeneo_storage_utils.event_subscriber.mongodb.entity_type`
  * `pim_catalog.mongodb.mongo_objects_factory` has been renamed to `akeneo_storage_utils.mongodb.mongo_objects_factory`
- (Akeneo storage) The following classes have been renamed or moved:
  * `Pim\Bundle\CatalogBundle\MongoDB\MongoObjectsFactory` becomes `Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory`
  * `Pim\Bundle\CatalogBundle\MongoDB\Type\Entities` becomes `Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entities`
  * `Pim\Bundle\CatalogBundle\MongoDB\Type\Entity` becomes `Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entity`
  * `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelsPass` becomes `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass`
  * `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoriesPass` becomes `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass`
  * `Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\ReferencedCollection`
  * `Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\ReferencedCollectionFactory`
  * `Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry`
  * `Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder`
  * `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber` becomes `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber`
  * `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber` becomes `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber`
  * `Pim\Bundle\CatalogBundle\EventSubscriber\ResolveTargetRepositorySubscriber` becomes `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\ResolveTargetRepositorySubscriber`
- ProductBuilder now takes `Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository`, `Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository`, `Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository` and not anymore Managers
- constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operator\ProductMassEditOperator` to remove ProductManager
- following constructors have been changed to add `Akeneo\Component\Persistence\BulkSaverInterface` as argument:
  * `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus`
  * `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`
  * `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`
  * `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily`
  * `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`
- removeAttributesAction and addAttributesAction have been move from `Pim\Bundle\EnrichBundle\Controller\ProductController` to a dedicated `Pim\Bundle\EnrichBundle\Controller\ProductAttributeController`
- constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` has been updated and now receives `Akeneo\Component\Persistence\SaverInterface`, `Pim\Bundle\CatalogBundle\Manager\MediaManager` and `Pim\Bundle\EnrichBundle\Manager\SequentialEditManager` as extra arguments
- the method execute() of `Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface` now return a `Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface`
- Added a new parameter in `src/Pim/Bundle/CatalogBundle/Manager/MediaManager` that gives the uploaded directory
- constructor of `Pim\Bundle\EnrichBundle\Form\View\ProductFormView` has been updated and now receives `Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterRegistry`
- constructor of `Pim\Bundle\TransformBundle\Transformer\ProductTransformer` has been updated and now receives `Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface`
- You cannot add product to multiple variant group anymore
- constructor of `Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository` to add ProductTemplateUpdaterInterface and Validator interface
- rename buildProductValueForm to createProductValueForm in `Pim\Bundle\EnrichBundle\Form\Factory\ProductValueFormFactory`
- The method `setContext` for the class `src/Pim/Bundle/VersioningBundle/Manager/VersionManager` has been moved to `src/Pim/Bundle/VersioningBundle/Manager/VersionContext` and renamed setContextInfo
- The method `getContext` for the class `src/Pim/Bundle/VersioningBundle/Manager/VersionManager` has been moved to `src/Pim/Bundle/VersioningBundle/Manager/VersionContext` and renamed getContextInfo
- constructor of `Pim/Bundle/CatalogBundle/Doctrine/Common/Saver/GroupSaver` has been updated and now receives `Pim\Bundle\VersioningBundle\Manager\VersionContext` instead of `Pim\Bundle\VersioningBundle\Manager\VersionManager`
- constructor of `Pim/Bundle/VersioningBundle/Doctrine/AbstractPendingMassPersister` has been updated and now receives `Pim\Bundle\VersioningBundle\Manager\VersionContext`
- constructor of `Pim/Bundle/VersioningBundle/Doctrine/ORM/PendingMassPersister` has been updated and now receives `Pim\Bundle\VersioningBundle\Manager\VersionContext`
- constructor of `Pim/Bundle/VersioningBundle/EventSubscriber/AddVersionSubscriber` has been updated and now receives `Pim\Bundle\VersioningBundle\Manager\VersionContext`
- constructor of `src/Pim/Bundle/VersioningBundle/EventSubscriber/MongoDBODM/AddProductVersionSubscriber.php` has been updated and now receives `Pim\Bundle\VersioningBundle\Manager\VersionContext`
- constructor of `src/Pim/Bundle/CatalogBundle/Manager/GroupManager` has been updated and now receives `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface`
- Added `getProductsByGroup` method in `Pim/Bundle/CatalogBundle/Repository/ProductRepositoryInterface`

## Bug fixes
- PIM-3332: Fix incompatibility with overriden category due to usage of ParamConverter in ProductController
- PIM-3069: Fix image file prefixes not well generated on product creation (import and fixtures)
- PIM-3548: Do not use the absolute file path of a media
- PIM-3730: Fix variant group link on product edit page
- PIM-3632: Correctly show scopable attribute icons on scope change
- PIM-3583: Fix the bad parsed filter value with spaces

# 1.2.35 (2015-05-29)

## Bug fixes
- PIM-4227: Disable product versionning on category update (never used and very slow)

# 1.2.34 (2015-05-27)

## Bug fixes
- PIM-4223: Fix grid sorting order initialization (changed to be consistent with Platform behavior)

# 1.2.33 (2015-03-16)

# 1.2.32 (2015-03-11)

## Bug fixes
- PIM-3786: Attribute type should not be blank for import
- PIM-3437: Fix applying datagrid views and columns when not using hash navigation
- PIM-3844: Create popin keeps state in memory

# 1.2.31 (2015-03-06)

# 1.2.30 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.2.29 (2015-02-24)

# 1.2.28 (2015-02-20)

## Bug fixes
- PIM-3790: Fix WYSIWYG on folded scopable elements
- PIM-3785: Can not export Products/Published due to null medias

# 1.2.27 (2015-02-13)

## Bug fixes
- PIM-3779: Fix multiple WYSIWYG on same textarea element

# 1.2.26 (2015-02-12)

## Bug fixes
- PIM-3761: Fix WYSIWYG onClick behaviour, event correctly bind
- PIM-3632 : Correctly show scopable attribute icons on scope change

# 1.2.25 (2015-02-04)

## Bug fixes
- PIM-3718: load tinymce only on textarea click

# 1.2.24 (2015-01-28)

## Bug fixes
- PIM-3712: Fix installation issue related to the tag of gedmo/doctrine-extensions v2.3.11, we freeze to v2.3.10

# 1.2.23 (2015-01-23)

## Bug fixes
- PIM-3664: Fix product media stacktrace regression on missing media on filesystem during an export
- PIM-3677: Fix `Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection` saving problem

# 1.2.22 (2015-01-21)
- Crowdin Updated translations

# 1.2.21 (2015-01-16)

## Bug fixes
- PIM-3615: Context of the grid not applied in product form for an attribute type Date
- PIM-3638: Fix doctrine/cache 1.3.1 to fix Oro FilesystemCache issue

# 1.2.20 (2015-01-14)

## Bug fixes
- PIM-3603 Trigger saving wysiwyg editor contents when submitting product form manually

# 1.2.19 (2015-01-09)

## Bug fixes
- PIM-3556: Fix memory leak on versionning
- PIM-3548: Do not rely on the absolute file path of a media

# 1.2.18 (2014-12-23)

## Bug fixes
- PIM-3533: Fix wrong keys being generated for empty price attributes in normalized product snapshots
- PIM-3558: Fix order of options for multiselect attribute in product versionning and csv product export

## BC breaks
- PIM-3558: in the exported product csv file, we apply the sort order defined by the user to sort the options of a multiselect

# 1.2.17 (2014-12-19)
- PIM-3550: force the version of "doctrine/annotations" to "v1.2.1" to avoid the BC Break introduced with v1.2.2

# 1.2.16 (2014-12-17)

## Bug fixes
- PIM-3447: Enforce max database length limit on identifier, text and textarea attribute values
- PIM-3471: Add an error log when the max number of indexes is reached for the mongo product collection (MongoResultException is raised since Mongo 2.6.*)
- PIM-3369: Check on import if the couple channel/local exist
- PIM-3368: Add association type check on association import
- PIM-3377: Add a check if the specific locale exists on imports, and skip unused attribute column for locale specific on exports
- PIM-3458: When creating an attribute group, automatically set the sort order to the last one
- PIM-3420: Remove update guessers on attributes and attributes option to fix the versionning memory leak

## BC breaks
- PIM-3368: Add AssociationType class argument to the `Pim\Bundle\TransformBundle\Transformer\AssociationTransformer` constructor

## Improvements
- PIM-3448: Add the method `getAttributeGroupsFromAttributeCodes` in the `Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository`

# 1.2.15 (2014-12-10)

## Bug fixes
- PIM-3473: Fix date picker year range selection over next year
- PIM-3475: Fix attribute options sort order in import/export

## BC breaks
- Export of attribute options in CSV now include a sort_order column

# 1.2.14 (2014-12-03)

## Bug fixes
- PIM-3443: Fix prices not exported in quick export
- PIM-3446: Fix import export history with large amount of errors

# 1.2.13 (2014-11-26)

## Bug fixes
- PIM-3406: Fix boolean filter on Mongo implementation
- PIM-3430: Fix doctrine issue on prices when skip an item during the product import
- PIM-3358: Fix sprintf issue in an exception which prevents doctrine writer to deal with anything else than an object
- PIM-3326: Fix mongo filters with multiples values and empty on MongoDB
- PIM-3426: Fix common attributes edition on multi selects
- PIM-3434: Fix bug in product media manager when file does not exist on the filesystem
- PIM-3436: Fix WYSIWYG field on product edit form (load them asynchronously)
- PIM-3372: Add an error message when the locale is disabled during product import
- PIM-3370: Add an error message when the channel doesnt exist during product import
- PIM-3374: Add an error message when a channel is provided for a global attribute
- PIM-3375: Add an error message when a locale is provided for a global attribute
- PIM-3376: Add an error message when a channel and a locale are provided for a global attribute
- PIM-3393: Don't show the update view button for non-owners

# 1.2.12 (2014-11-13)

## Bug fixes
- PIM-3298: Fix issue with locale specific property of an attribute when edit and mass edit
- PIM-3229: Fix values for simple and multi select attributes with missing translations not being displayed in the grid
- PIM-3309: Fix check on product value uniqueness
- PIM-3288: Fix memory leak on product import (avoid to hydrate all products of a category when we add a category to a product)
- PIM-3354: Fix parameter alias in ORM ProductCategoryRepository

# 1.2.11 (2014-10-31)

## Bug fixes
- PIM-3308: Fix regression on unclassified filter
- PIM-3311: Fix creation of products with missing identifier during imports
- PIM-3312: Fix CSV import of product values with invalid channel, locale or currency

# 1.2.10 (2014-10-24)

## Bug fixes
- PIM-3221: Fix the possibility to update attributes on variant groups during import
- PIM-3283: Fix issue on the password reset
- PIM-3209: Fix issue on the extension validation during import
- PIM-3234: Fix performance issue on category filter

# 1.2.9 (2014-10-17)

## Bug fixes
- PIM-3254: Fix issue with inactive locales in exports
- PIM-3217: Fix missing filter groups in grid filter selector when two attribute groups have the same sort orders
- PIM-3281: Fix mass edit issue on localizable values, it uses user locale instead of selected locale
- PIM-3248: Fix completeness not being correctly calculated after removing a required attribute from a family
- PIM-3279: Fix performance issue with big group sets
- PIM-3266: Fix the flush of skipped items during an import that uses the `Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor`. All your custom processors that uses the `TransformmerProcessor` should now inject the `Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry` to fix this issue too.
- PIM-3282: Fix the grid filters that can be set as json in the request

## BC breaks
- Two new arguments have been added to Pim\Bundle\FilterBundle\Filter\Product\GroupsFilter: `userContext` and `groupClass`

# 1.2.8 (2014-10-10)

## Bug fixes
- Fix memory leak in CSV quick export
- Fix memory leak when product with medias are exported in CSV
- Cannot display correctly all variant groups on grid

## Improvements
- avoid hydrating duplicate categories when applying category filter in product grid

# 1.2.7 (2014-10-01)

## Bug fixes
- Fix no warning message when leaving a product form after a submit with errors
- Stabilize composer.json (minimum-stability: stable) and fix monolog version issue

# 1.2.6 (2014-09-26)

## Bug fixes
- Fix installer fail on requirements when you change the archive and uploads folder
- Fix display of multi-byte characters in long form labels that are truncated
- Incorrect date display between export/import widget and job execution page and job history
- Fix archiver bug with yml imports
- Fix missing product versioning data when a category, attribute or attribute option linked to a product is removed

## BC breaks
- Added supports method in Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface
- Two new methods have been added to Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface: `findAllWithAttribute` and `findAllWithAttributeOption`
- Constructor of Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeOptionUpdateGuesser has been changed

## Improvements
- Add images in icecat_demo_dev installer fixtures
- Add sorter to the grid state

# 1.2.5 (2014-09-19)

## Bug fixes
- File that contains non UTF-8 characters can not be imported anymore
- Mimetype check on file import has been removed
- Incorrect written number after csv export

## Improvements
- Fixtures stop if warnings are encountered
- Errors and warnings for fixtures are displayed

# 1.2.4 (2014-09-11)

## Bug fixes
- Fixed job profile controller doing a global flush after launching job execution

# 1.2.3 (2014-09-08)

## Bug fixes
- association fixtures

# 1.2.2 (2014-09-05)

## Improvements
- CacheClearer splits into two services, one for Product and one for other entities

## Bug fixes
- association import with MongoDB fixes

# 1.2.1 (2014-09-03)

## Bug fixes
- large memory leak fixed for non product import (association, product group, attribute, categories, etc...)
- new associations were created at each import

## BC breaks
- protected postWrite method not called anymore from BaseConnectorBundle\\Writer\\Doctrine\\Writer.
 If you need it, override the write method, call the parent and add your code after.
- constructor of Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\Writer has changed
- Pim\Bundle\TransformBundle\Cache\ProductCacheClearer has been renamed Pim\Bundle\TransformBundle\Cache\CacheClearer

# 1.2.0 (2014-08-28)

## Improvements

## Bug fixes
- Fix a bug on entity `Pim/Bundle/CatalogBundle/Model/AbstractProduct`

# 1.2.0-RC4

## Improvements
- Java dependency has been removed
- Add locale fallback to en_US

## Bug fixes
- Sort exported categories by tree and order inside the tree
- Return to the family index page after cancelling family mass edit instead of product index
- Fixed an error when all families are edited without any applied filters
- Fixed a bug that allowed to mass edit only 10 families
- Fixed category order in the categories tab of products
- Incomplete archives no longer appear as downloadable in the export execution details page
- Fixed Cascade delete on associations for MongoDB impl
- Fixed a bug on normalization of decimal attributes for MongoDB impl
- Fixed the 'Is associated' filter in the product association grids
- Fixed a bug where special characters were not well handled in product grid filter
- Fixed unique value validation for date attributes during import
- Fixed apply filter on channel tree on MongoDB implementation
- Fixed a bug on ProductCsvWriter
- Fixed a bug that causes product associations to be stored twice in MongoDB implementation

## BC breaks
- Replace ACLs `pim_enrich_family_add_atribute` and `pim_enrich_family_remove_atribute` with `pim_enrich_family_edit_attributes`. This ACL also enforces rights to edit attribute requirements.
- Changed JobExecutionArchivist to archive files generated by export before it is marked as completed
- JS and CSS are not minified anymore. We advise to use server side compression for bandwidth savings.

# 1.2.0-RC3

## Improvements
- Killed export process are now detected and displayed as failed
- CsvWriter can write files for any type of entity

## Bug fixes
- Fixed Mass edit on a never fulfilled price attribute
- Fix TinyMCE WYSIWYG editor generating 'fake' history due to html reformatting
- Fixed flat product normalizer and filtered values (with many filters)
- Make sure that the file path of export profiles is writable before allowing to execute
- Fixed bug with scopable boolean value not being saved
- Use `pim_number` form type to replace the use of `number` and fix issue with javascript validation on numbers with different formats

## BC breaks
- Remove `task` option from install command
- JobExecutionController now require the Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager.
- InvalidItemsCsvArchiver is not injected in the constructors of ProductCsvReader and ProductReader
- CsvProductWriter should be used instead of CsvWriter for products
- Remove `pim_serializer.normalizer.get_set_method` defined as fallback normalizer

# 1.2.0-RC2

## Improvements
- Create a metric factory
- Improve UI for defining role permissions
- Throw exception on install command if fixture directory not found
- Setup `pim_catalog.storage_driver` in `pim_parameters.yml` instead of `config.yml`
- Load PIM configuration via the import of the file `pim.yml` instead of a preprend configuration
- Externalize non local PIM parameters in `pim_parameters.yml`
- Replace buttons by icons to manage datagrid views
- Add post create event on enrich part when an attribute group is created

## Bug fixes
- The message 'there are unsaved changes' is missing in the Role edit form
- Display a file attribute attribute as column in product grid displays Array
- History tab crashes when product imported without real time versioning
- Creating an attribute with the code "id" should be forbidden
- Switch not well displayed on other locales than en_US
- Associations are now well saved on product import

## BC breaks
- Remove backendStorage property on attribute entities
- Inject MetricFactory in `Pim\Bundle\CatalogBundle\AttributeType\MetricType`, `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttribute` and `Pim\Bundle\TransformBundle\Transformer\Property\MetricTransformer` instead of metric entity class parameter
- MongoDB: Media are now part of the product as embedded document and not in an external collection. A migration script is provided. See the UPGRADE file.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\FamilyType` to add `DisableFamilyFieldsSubscriber` as third argument and `AddAttributeAsLabelSubscriber` as fourth argument
- Rename pim_catalog.datasource.smart and pim_catalog.datasource.product respectively by pim_datagrid.datasource.smart and pim_datagrid.datasource.product
- Add method setMassActionRepository and remove the MassActionRepositoryInterface from constructor
- Introduce a dedicated pim_webservice.serializer to handle REST API
- Rename ACL `pim_enrich_family_edit` to `pim_enrich_family_edit_properties`. This ACL now only check the access to the properties tab.
- Rename ACL `pim_enrich_product_edit` to `pim_enrich_product_edit_attributes`. This ACL now only check the access to the attributes tab.
- Added ProductCacheClearer to share cache clearing between product writers

# 1.2.0-RC1

## Features
- Add an option to automatically sort the choices of simple and multi select attributes
- Add a mass family edition operation to allow adding or changing attribute requirements on many families at once
- Allow filtering by empty values for attributes (text, textarea, number, date, simple and multiselect, prices and metrics) and for family property
- Add an option to filter products by a list of identifier values
- Don't allow editing the default datagrid view
- Add a enable/disable row action in product grid

## Improvements
- Group datagrid filters by attribute groups
- Ease the adding of new filters and sorters in ProductQueryBuilder
- All grids can now benefit from the multistep mass edition wizard (this was reserved to the the product grid before)
- Ease the adding of subscribers in ProductEditType, JobInstanceType and AttributeGroupType with addEventSubscriber methods
- Introduce a ProductValueFormFactory which dispatch a EnrichEvents::CREATE_PRODUCT_VALUE_FORM to ease the product value form customization
- MongoDB completeness calculation performances
- Introduce Abstract models for Association, Media, Metric, Price, Completeness to ease the overriding/re-using of theses classes
- Allow to override of a repository avoiding to redefine the entity mapping
- Introduce a datagrid choice filter that loads attribute option choices based on the search query to enhance performance with a large number of attribute options
- Apply "Remove product" permission to hide mass delete and delete row action
- Change "launch" button by "view" on job profile datagrids
- Create a `JobInstanceRepository`
- Automatic creation and purge of indexes for MongoDB
- Dispatch event before rendering the product edit template
- Fixed asymetric enable product button
- Remove qb definition from job profile grid configs
- Create repositories for JobInstance and JobExecution
- Create manager for JobInstance
- Clean LastOperationsWidget architecture
- New readers for export improve memory usage loading small batches of products instead of all products in same time
- Update BatchBundle to 0.1.6 in order to get updated summary information during the execution of the process (and not only at the end)
- Allow values 'true', 'false', 'yes' and 'no' to be converted into boolean during import
- Create a job instance factory to create job instances
- Allow to add hidden row actions in grids
- Make optional the generation of missing completenesses in product reader
- Update install to be able to define email address/name used for system emailing
- Update BatchBundle version to get a better support of exceptions in logs and provide the new command akeneo:batch:list-jobs
- Faster MongoDB product writer (around 10x times faster than current one)
- Dispatch events on show/edit/execute/remove job profile actions
- Dispatch events on view/download job execution actions
- Allow to install custom user roles and groups from installer fixtures
- Display the code of import/export profiles on the edit and show views
- Related entities' edition and deletion doesn't reload all the products' normalized data
- Inject event dispatcher inside AbstractController
- Csv reader and Yaml readers are now reseted between each steps
- Dispatch events when removing some entities
- Add method remove in Category, Group, Attribute, Association type and family managers.
- Call manager's method remove from these entity controllers
- Remove the count of products by category in the context of the management of the categories (perf)
- Define attribute type classes as parameters
- Products on which mass edit operation is not performed are also ignored from operation finalize method
- Create specific serializer service for versioning

## Bug fixes
- Replaced usage of Symfony process to launch background job with a simple exec, more reliable on a heavily loaded environment
- Added missing translation keys for "manage filters", "all", "records", etc
- Images import from fixtures now works
- Fixed versions not being properly generated when real-time versioning is disabled (in imports/exports)
- Deleted completeness when a locale of a channel is deleted
- Displayed flags in the completenesses grid
- Fixed a memory leak on product import when using MongoDB
- Fixed a bug with image upload on product with a "\" or "/" in their sku
- Fixed a bug that silently failed when uploading file that does not comply with server configuration
- Fixed a bug when display image thumbnail in the product grid with MongoDB support
- Fixed a bug with timestampable listener which doesn't change the updated date of a product
- Fixed a bug with numeric validation and decimal allowed property (number, metric, price attribute types)
- Attribute Options code validation now more precise on uniqueness (equality instead of similarity)
- Fixed a bug with repository resolver on ODM implementation
- Fixed a bug on mass edit when we use a completeness filter to select products
- Removed the import CSV mimetype validation which is unreliable
- Product completeness in MongoDB is not lost anymore in the grid
- Upload on a job with a custom step (non ItemStep) doesn't crash anymore
- Memory leak fixed in pim:version:refresh command
- Fixed a bug when try to remove the family of a product
- Wrong date conversion fixes on grid and form

## BC breaks
- Remove FlexibleEntityBundle
- Remove CategoryWriter and use the generic doctrine writer instead
- Remove entity argument from FiltersConfigurator constructor
- Rely on CatalogBundle/Version and not anymore on CatalogBundle/PimCatalogBundle to get the current version of the PIM
- The Pim\Bundle\EnrichBundle\MassEditAction namespace has been renamed to Pim\Bundle\EnrichBundle\MassEditAction\Operation
- Mass edit operator has been moved to an Operator sub-namespace
- Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditActionInterface has been renamed Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface
- Changed the HydratorInterface::hydrate() method signature
- Avoid to store null values in Product::normalizedData (MongoDB support)
- Remove redundant 'getActiveCodeChoices' method in CurrencyManager (use CurrencyManager::getActiveCodes())
- Remove AbstractAttributeType::buildValueFormType, change visibility of prepareValueFormName, prepareValueFormAlias, prepareValueFormOptions, prepareValueFormConstraints, prepareValueFormData to public
- Remove `MetricBaseValuesSubscriber` and create one for MongoDB and another one for ORM
- Create `OptionFilter`, `OptionsFilter` for ORM and MongoDB implementations
- InstallerBundle/LoaderInterface has been changed to pass ProductManager to manage media (loading images from fixtures)
- Refactor VersioningBundle - a lot of API changes, add MongoDB support.
- Remove the Doctrine registry dependency from `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` and use only the family repository
- Remove the Doctrine registry dependency from `Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator` and use only the entity manager
- Add a new method `scheduleForChannelAndLocale` to `Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface`
- Add a dependency to the completeness manager on `Pim\Bundle\EnrichBundle\Form\Handler\ChannelHandler`
- Add a dependency to the channel repository on `Pim\Bundle\CatalogBundle\Manager\CompletenessManager`
- Remove deprecated ConfigureGroupProductGridListener and add parameter in method ConfiguratorInterface::configure(DatagridConfiguration $configuration)
- Category and CategoryRepository no longer extend AbstractSegment and SegmentRepository, previously inherited methods are now in these classes
- Change constructor of ProductExportController to remove CurrencyManager and AssociationTypeManager args
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` and `Pim\Bundle\ImportExportController\JobProfileController` (inject event dispatcher)
- Add parameters to load datagrids in job profiles index twig templates
- Remove WidgetRepository to replace it by `Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository`
- Inject `Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager` into LastOperationsWidget
- Remove injection of WidgetRepository from LastOperationsWidget
- Inject JobInstanceFactory inside `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`
- Remove duplicate pim_catalog.entity.job_instance.class parameter, we must now use akeneo_batch.entity.job_instance.class
- Inject EventDispatcher inside AbstractController
- Add missing getEntity() method in product value interface
- Add methods inside CategoryInterface
- Inject `Symfony\Component\EventDispatcher\EventDispatcherInterface` inside Attribute, AssociationType, Category, Family and Group managers
- Inject `Pim\Bundle\CatalogBundle\Manager\FamilyManager` in `Pim\Bundle\EnrichBundle\Controller\FamilyController`
- Inject `Doctrine\Common\Persistence\ObjectManager` in `Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager`
- Inject `Doctrine\Common\Persistence\ObjectManager` in `Pim\Bundle\CatalogBundle\Manager\FamilyManager`
- Inject group and group types classes in `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Inject `Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager` in `Pim\Bundle\EnrichBundle\Controller\AssociationTypeController`
- Inject `Pim\Bundle\CatalogBundle\Manager\FamilyManager` in `Pim\Bundle\EnrichBundle\Controller\FamilyController`
- Inject SecurityFacade inside `Pim\Bundle\EnrichBundle\Controller\CategoryController`
- Each dashboard widget has to define its full template, nothing is rendered automatically
- Delete `Pim\Bundle\DataGridBundle\Extension\Filter\MongoDBFilterExtension`, `Pim\Bundle\DataGridBundle\Extension\Filter\OrmFilterExtension`, `Pim\Bundle\DataGridBundle\Extension\Filter\ProductFilterExtension`
- Rename `Pim\Bundle\DataGridBundle\Extension\Filter\AbstractFilterExtension` to `Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension` which expects a `Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver\` as third argument for its constructor
- Rename constant `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddFilterTypesPass::FILTER_ORM_EXTENSION_ID` to `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddFilterTypesPass::FILTER_EXTENSION_ID`
- Delete `Pim\Bundle\DataGridBundle\Extension\Sorter\MongoDBSorterExtension`, `Pim\Bundle\DataGridBundle\Extension\Sorter\OrmSorterExtension`, `Pim\Bundle\DataGridBundle\Extension\Sorter\ProductSorterExtension`
- Rename `Pim\Bundle\DataGridBundle\Extension\Sorter\AbstractSorterExtension` to `Pim\Bundle\DataGridBundle\Extension\Sorter\SorterExtension`
- Rename constant `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddSortersPass::SORTER_ORM_EXTENSION_ID` to `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddSortersPass::SORTER_EXTENSION_ID`
- Delete service `pim_datagrid.extension.filter.mongodb_filter`
- Delete service `pim_datagrid.extension.filter.product_filter`
- Rename service `pim_datagrid.extension.filter.orm_filter` to `pim_datagrid.extension.filter`
- Delete service `pim_datagrid.extension.sorter.mongodb_sorter`
- Rename service `pim_datagrid.extension.sorter.orm_sorter` to `pim_datagrid.extension.sorter`
- Delete `Pim\Bundle\DataGridBundle\Extension\Pager\MongoDBPagerExtension`,`Pim\Bundle\DataGridBundle\Extension\Pager\OrmPagerExtension` and `Pim\Bundle\DataGridBundle\Extension\Pager\ProductPagerExtension`
- Rename `Pim\Bundle\DataGridBundle\Extension\Pagerr\AbstractPagerExtension` to `Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension` which expects a `PagerResolver` as first argument
- Delete service `pim_datagrid.extension.pager.mongodb_pager`
- Delete service `pim_datagrid.extension.pager.product_pager`
- Rename service `pim_datagrid.extension.pager.orm_pager` to `pim_datagrid.extension.pager`
- Replace `Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource` by `Pim\Bundle\DataGridBundle\Datasource\Datasource`
- Replace service `pim_datagrid.datasource.orm` by `pim_datagrid.datasource.default`
- Delete `Pim\Bundle\DataGridBundle\Datasource\MongoDB\MongoDBDatasource`
- Delete service `pim_datagrid.datasource.mongodb`
- Remove the flush parameter from Pim\Bundle\CatalogBundle\Doctrine\MongoDB\CompletenessGenerator::generateMissingForProduct(), as it was not used properly anymore (completeness are directly pushed to MongoDB without using ODM)
- Rename countForAttribute to countVariantGroupAxis in GroupRepository
- Remove locale-specific rights
- Upgraded to 0.2.* version of akeneo/batch-bundle
- Rename `Pim\Bundle\TransformBundle\DependencyInjection\Compiler\ReplacePimSerializerArgumentsPass` by `Pim\Bundle\Transform\DependencyInjection\Compiler\SerializerPass` and change construct parameters
- AddVersionListener and VersionBuilder use new `pim_versioning.serializer` service
- In InGroupFilter and IsAssociatedFilter constructors, replace the RequestParameters argument by a RequestParametersExtractorInterface
- Change constructor of `Pim\Bundle\DataGridBundle\Controller\ProductExportController` to inject the product repository `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface` as fourth argument
- Rename Pim\CatalogBundle\Model\Media to Pim\CatalogBundle\Model\ProductMedia to reflect the coupling between product media and product value and allow the future introduction of generic media
- Add a $metricClass argument in MetricTransformer constructor
- Add a $mediaClass argument in MediaTransformer constructor
- Add a $metricClass argument in MetricType constructor
- Change the arguments of ProductBuilder to pass classes (product, value, price) as an array
- Change the arguments of EditCommonAttributes to pass classes (metric, media, price) as an array
- Remove not used parameter `pim_import_export.entity.export.class`
- Remove file `Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler\ResolveDoctrineOrmTargetEntitiesPass`
- Replace the filter config parent_type by ftype
- Rename CatalogBundle, VersioningBundle, UserBundle listeners to subscribers
- Change constructor of `Pim\Bundle\DataGridBundle\Manager\DatagridViewManager` to inject the datagrid view repository as first argument (instead of the manager)
- Rename service `pim_catalog.validator.attribute_constraint_guesser` by `pim_catalog.validator.constraint_guesser.chained_attribute`

# 1.1.0 - "Rabbit Punch" (2014-04-16)

## Features
- Implement creating, updating, applying and removing datagrid views
- MongoDB storage support

## Improvements
- Allow to add many quick export on datagrids
- Optimize products mass deletion
- Improve get product REST API
- Improve entity history context display for entities updated during import jobs
- Add a 'properties' field to the Attribute entity to allow easily adding new attribute type dependent properties
- Introduced custom ODM types to map document to one or many entities
- Define specific route and configuration for datagrid quick exports
- Add a parameter to ProductManager::save() and ProductManager::saveAll() to allow saving products without completeness recalculation
- Dispatch event pre/post handler for each mass action
- Enhance the error message displayed when a related entity doesn't exist during an import (for instance we import products and a family doesn't exist)
- Default product datagrid sorting is done descending on updated property

## Bug fixes
- Fixed the verbose option always used in the install command
- Fixed issue on attribute option removal
- Fixed identifier is required attribute
- Fixed get common attributes with common values
- Fixed completeness not removed from changed family
- Fixed Product ORM mapping: activated orphanRemoval of values
- Fixed family import fixtures, we now throw an exception if attribute used as requirement not exists
- Fixed the CSV import of attribute options which can fail due to missing columns when options are not 100% translated
- Fixed the CSV import of attribute option to throw exception when the attribute is not known
- Fixed the CSV export of attributes to avoid to export the virtual group 'Other'
- Prevent considering 0 as a null value when importing metric data
- Ensured the attribute option validation when edit an option
- Fixed the product CSV export when a metric attribute is exported without unit
- Fixed the missed 'there are unsaved changes' message when I delete an option
- Ensured the ability to change the user catalog locale from user fixtures
- Fixed mass delete and pagination
- Fixed the CSV import of family when an attribute does not exist
- Fixed the CSV import of option when an attribute does not exist
- Fixed the erroneous message on completeness tab to display "not yet calculated" instead of "locale non associated to this channel"
- Fixed the 'null' displayed after a dynamic option creation
- Fixed the quick Export to be able to export all the products
- Ensured that we're able to configure the email to use in monolog handler
- Fixed the missing translation keys
- Fixed the route exception for less/address in prod.log
- Fixed the categories tree get cut off on a long list on categiry management
- Fixed the deletion of an attribute option
- Remove the deprecated fallback property in locale and in locales.yml file
- Avoid to recalculate the completeness when I add some products to one or more group with the mass-edit wizard
- Fixed the unique attributes validation during product CSV imports
- Fixed the exception on file_get_content if the image doesn't exist anymore
- Ensure the required property for an identifier when importing attributes
- Fixed the error message when the family is not known when importing products
- Removed useless ```app/entities``` directory

## BC breaks
- Add an argument HydratorInterface in ProductDatasource constructor (MongoDBODM support)
- Add an argument $adapterClass (string for FQCN) in ProductFilterExtension and OrmFilterExtension constructors (MongoDBODM support)
- Remove deprecated fallback property of Locale entity
- Add a generateProductCompletenesses method on CompletenessGeneratorInterface, to generate completeness for one product
- Add setCompletenesses and getCompletenesses method on ProductInterface and Product class
- Add methods getProductQueryBuilder, deleteProductIds methods in ProductRepositoryInterface
- Remove methods setLocale/getLocale, setScope/getScope, setConfiguration/getConfiguration from ProductRepositoryInterface
- Remove methods setLocale/getLocale, setScope/getScope from ProductManager
- Move findAllByAttributes and findOneByWithValues from FlexibleEntityRepositoryInterface to ProductRepositoryInterface
- Move setFlexibleQueryBuilder, findAllByAttributes, findOneByWithValues, getFlexibleQueryBuilder, addJoinToValueTables, findAllByAttributesQB from FlexibleEntityRepository to ProductRepository (ORM)
- Move FilterBundle/Filter/ScopeFilter.php, ProductCompletenessFilter.php, ProductGroupsFilter.php, CategoryFilter.php -> FilterBundle/Filter/Product/ScopeFilter.php, CompletenessFilter, GroupsFilter.php, CategoryFilter.php
- Move FilterBundle/Resources/public/js/datafilter/filter/scope-filter.js, category-filter.js -> FilterBundle/Resources/public/js/datafilter/filter/product_scope-filter.js, product_category-filter.js
- Move FilterBundle/Filter/Flexible/FilterUtility.php -> Filter/ProductFilterUtility.php, remove the flexibleEntityName argument of applyFlexibleFilter, rename applyFlexibleFilter to applyFilterByAttribute
- ProductValueNonBlank renamed to ProductValueComplete
- Remove the AclHelper $aclHelper argument from the DataGridBundle/Extension/Pager/Orm/Pager.php constructor
- Moved CustomEntityBundle to its own repository
- Move `FlexibleEntityBundle/Doctrine/*` -> `CatalogBundle/Doctrine/ORM/*`, rename `FlexibleQueryBuilder*` to `ProductQueryBuilder*`, specialize the implementation and pass the CatalogContext as constructor argument
- Changes in the implementation of storing datagrid state - adding 'pim/datagrid/state-listener' to the datagrid configuration is no longer required, instead, the grid should be rendered with dataGrid.renderStatefulGrid()
- Move `FilterBundle/Filter/Flexible/*` -> `FilterBundle/Filter/ProductValue/*`
- Remove unused FilterBundle/Filter/ProductValue/EntityFilter
- Replace FlexibleManager by ProductManager in ContextConfigurator constructor arguments
- Replace tag `pim_flexibleentity.attributetype` by `pim_catalog.attribute_type`
- Replace service `@pim_flexibleentity.validator.attribute_constraint_guesser` by `@pim_catalog.validator.attribute_constraint_guesser`
- Replace the use of FlexibleValueInterface by ProductValueInterface in AttributeTypeInterface and AbstractAttributeType
- Update ProductValueInterface, add getData, setData and getAttribute methods
- Move `DataGridBundle/Extension/Formatter/Property/*` to `DataGridBundle\Extension\Formatter\Property\ProductValue\*`
- Use CatalogContext and not ProductManager as constructor argument in AddParametersToProductGridListener
- Move mass export in specific controller
- Add an affectsCompleteness method to MassEditActionInterface to indicate whether performing the mass action requires recalculating the product completeness
- Remove DeleteMassActionHandler, replaced by ProductDeleteMassActionHandler
- Change product REST API data and url format
- Remove incomplete REST API for getting multiple products
- Remove Router dependency from json ProductNormalizer
- Replace RegistryInterface with ManagerRegistry in controllers - retrieving the ObjectManager from the AbstractController now requires passing the class name (AbstractDoctrineController::getManagerForClass())
- Change Completeness Manager and Repository function names to something more coherent (generateMissingForxxx)
- Move `DataGridBundle/Extension/Sorter\Orm\FlexibleFieldSorter` to `DataGridBundle/Extension/Sorter/Product/ValueSorter`
- Move `DataGridBundle/Extension/Sorter/Orm/FlexibleFieldSorter` to `DataGridBundle/Extension/Sorter/Product/ValueSorter`
- Move `DataGridBundle/Extension/Selector/Orm/*` to `DataGridBundle/Extension/Selector/Orm/Product` and `DataGridBundle/Extension/Selector/Orm/ProductValue`
- ProductRepository does not extend anymore FlexibleEntityRepository, getFlexibleConfig/setFlexibleConfig have been replaced by getConfiguration/setConfiguration
- Change mass action route for products and create own controller for these mass actions
- Add a MassActionHandlerRegistry for mass action handlers services (works with handler alias)
- Rename ProductDeleteMassActionHandler to DeleteMassActionHandler
- Create MassActionHandlerInterface instead of using OroPlatform one
- Change MassActionDispatcher::dispatch parameters
- Replace `@pim_datagrid.datasource.product.result_record.hydrator` by `@pim_datagrid.datasource.result_record.hydrator.product` and same for class parameter
- Move mass action handlers to its own `Handler` directory
- Create PimDatasourceInterface extending OroDatasourceInterface
- Use PimVersioningBundle:Version for all entity audits instead of OroDataAuditBundle:Audit, replace AuditManager with VersionManager, drop AuditBuilder and refactor listeners that create object versions
- Redefine DeleteMassAction, EditMassAction and ExportMassAction
- Remove data_identifier property defined on datagrid.yml for mass actions
- Rename parameter $queryBuilder as $qb in HydratorInterface
- Add findFamilyCommonAttributeIds and findValuesCommonAttributeIds methods to ProductRepository interface
- Remove queryBuilder property from MassEditActionController and remove $request from each action
- Remove queryBuilder from methods initialize and perform in AbstractMassEditAction and children
- Add setProductsToMassEdit and getProductsToMassEdit in AbstractMassEditAction
- Remove EntityManager property from AddToGroups mass edit action and directly inject GroupRepository
- Remove ProductManager property from Classify mass edit action
- Remove method getProductIdsFromQB from EditCommonAttributes mass edit action
- Remove ProductRepository::findFamilyCommonAttributes() and ProductRepository::findValuesCommonAttributeIds() to replace them by ProductRepository::findCommonAttributeIds()
- Disable global search feature
- Remove the 'searchable' property of AbstractAttribute
- Move ProductRepository::getIdentifier() to attribute repository
- Move CatalogBundle\Entity\Repository\ProductRepository to CatalogBundle\Doctrine\ORM
- Move CatalogBundle\Entity\Repository\AssociationRepository to CatalogBundle\Doctrine\ORM
- Move CatalogBundle\Model\ProductRepositoryInterface to CatalogBundle\Repository
- Move CatalogBundle\Model\AssociationRepositoryInterface to CatalogBundle\Repository
- Move CatalogBundle\Model\CompletenessRepositoryInterface to CatalogBundle\Repository
- EditCommonAttributes class needs the ProductBuilder and ProductMassActionManager now
- Move prepareDBALQuery from ProductRepository to QueryBuilderUtility
- Add a ProductCategoryManager and move here the methods getProductsCountInCategory, getProductIdsInCategory from the ProductManager
- Renamed service writer ids `pim_base_connector.writer.orm.*` -> `pim_base_connector.writer.doctrine.*`
- Replace `@security.context` by `@pim_user.context.user` in `ContextConfigurator`
- Delete the attribute virtual group and the `getVirtualGroup` method of the class `Pim\Bundle\CatalogBundle\Model\AbstractAttribute`
- Render the attribute group mandatory for the creation and the edition of an attribute

# 1.0.2
## Bug Fixes
- Removed hardcoded attribute table from ORM/CompletenessGenerator.php
- Fixed of ProductValue's attributes' exclusion on completeness's computation

# 1.0.1
## Bug Fixes
- Removed hardcoded Attribute from ChainedAttributeConstraintGuesser
- Removed hardcoded Attribute from ValidMetricValidator

# 1.0.0 - "Hare We Go" (2014-03-06)

## Features
- Uservoice integration
- Add a last operations widget on the dashboard
- Add new user as fixtures
- Auto-refresh job execution report page
- Add a checkbox to select all visible rows in entity association grids
- Add colors to channels and use them in scopable field labels to make them more compact

## Improvements
- Load choices for grid filters asynchronously
- Allow adding/removing attributes to mass edit attributes view without a page reload
- Propagate -v option to subcommands of install command
- Fix the versions of dependencies in composer.json
- Undisplay unwanted searchable elements in quick search
- Add icons for category and product in search view
- Prevent hydrating all attributes in the available attributes addition form
- Prevent hydrating all families in the product edition form
- Import conversion units for channels
- Product grid loading performance by hydrating as array and introduce selector extension
- Add a screen to select the attribute type before creating an attribute
- Create check-requirements, assets and database/fixtures commands and simplify install one
- Make documentation tested linking it to our behat scenarios

## Bug fixes
- Fixed non-updated values being displayed in the the audit history
- Fixed attribute group form state not being saved
- Do not display Id as an eligible attribute as label
- Fixed select field missing for scopable simple/multi select attributes in the product form
- Restored missing attributes translation
- Fixed the display of scopable metric attributes in the product edit form
- Fixed regression with resolve target entity on Category entity
- Fixed datepicker in date attribute form
- Fixed unwanted fields appearing in attribute creation form if server-side validation errors are present
- Fixed 500 response when submitting import launch form without selecting file to upload
- Fixed $field_catalogLocale linked bugs
- Fixed scopable value order in product form
- Restored unique variant axis constraint when saving product
- Fixed missing breadcrumbs for edit views
- Fixed lost hashnav when creating an attribute group
- Fixed a bug that prevented saving unchecked checkbox value in product edit form
- Fixed recovered attributes on mass edit action
- Fixed a bug with tooltips sometimes not appearing due to a conflict between bootstrap and jquery tooltip plugins

## BC breaks
- Remove the date type property of Attribute and simplify the pim_catalog_date attribute type to support date only (not date/datetime/time)
- Remove unnecessary AttributeManagerInterface and AttributeInterface in favor of AbstractAttribute
- Rename findByWithSortedAttribute to findOneByWithValues, add pre-select attributes and related translations to reduce number of lazy loaded queries when edit a product
- Rename findByWithAttributes to findAllByAttributes
- MeasureBundle has been moved from the BAP to an external repository (akeneo/measure-bundle).
- BatchBundle has been moved from the BAP to an external repository (akeneo/batch-bundle).
- Remove magic setter access to value (ex: $product->setDescription()), as it has multiple conceptual and
technical flaws (attribute codes are data, not a freeze structure, needed to maintain an full attribute cache in product
that made the entity too smart for its own good and created performances problem)
- Remove Product::createValue(). Can be replaced by calling ProductManager::createFlexibleValue() and setting attribute, scope and locale on the created value.
- Product datagrid, hydrate rows as arrays (in place of objects) to reduce the loading time
- Datagrid configuration, remove [flexible_entity] config to avoid to define the used entity twice
- Rename and move src/Pim/Bundle/EnrichBundle/Resources/views/Completeness/_datagridCompleteness.html.twig => DataGridBundle/Resources/views/Property/completeness.html.twig
- Delete classes ConfigureAssociationProductGridListener and AssociationProductColumnsConfigurator, we now use ConfigureFlexibleGridListener to configure product association grid
- Delete the HideColumnsListener, the ColumnConfigurator is now able to add only columns configured by the user
- Rename CompletenessFilter to ProductCompletenessFilter to be consistent, move also the related js file
- Changed signature of ProductRepository::getEligibleProductIds()
- Changed signature of GroupType::__construct()
- Changed signature of AssociationType::__construct()
- Removed AttributeRepository::findallWithGroups()
- Rename grid_extensions.yml, grid_actions.yml, grid_listeners.yml, grid_attribute_types.yml to extensions.yml, actions.yml, event_listeners.yml, attribute_types.yml

# 1.0.0-rc-1 - "Tortoise Beats Hare" (2014-02-06)

## Features
- Completenesses over channels and locales widget
- New command to install the PIM
- Price attributes can be scopable
- Popin to configure product datagrid columns

## Improvements
- Add missing translations
- New grid implementation
- Grids performances
- Quick export of selected products in the grid
- Status column in the product grid
- Thumbnail in product grid for attribute of type image

## Bug fixes
- Bug #658: Export all activated translations even if no value has been set
- Bug PIM-1892: Prevented the form subscriber to remove form fields if not valid
- Downgrade ICU lib to be compatible with RedHat 6 and CentOS 6
- Fix an issue with excessive url length when mass editing many products
- Products grid loaded twice the first time the screen is displayed
- The first tree is not displayed in the mass edit wizard
- When no group type exist, it's not possible to add Variant Group
- Job validation is applied twice (create import/export)
- Validation messages not visible in job creation popin (create import/export)
- Lose hashnav when I create a tree
- Fix completeness calculation on icecat demo dev data
- Application crash on some product validation fail
- In create product popin, no way to search for family (in select 2 field)
- Attribute export in csv shift columns instead of putting blank values
- Error with field_catalogLocale on first load
- Missing translations in page titles
- When adding a new option from product form, the new option is not in the select
- Category edit and page title not updated

## BC breaks
- Change some translation message keys
- Remove GridBundle, add a new DataGridBundle (based on OroPlatform changes)
- Change filters implementations in FilterBundle
- Update all PIM grids to use the new implementation (extensions for filter, sorter, pager, custom datasource, custom cell formatters)
- Rename TranslatableInterface and TranslatableListener by Localizable one in FlexibleEntityBundle
- Rename translatable attribute property by localizable
- FlexibleQueryBuilder has been rewritten to prepare the MongoDB support (add filters and sorters in FlexibleEntityBundle/Doctrine/ORM)
- FlexibleQueryBuilder is injected to ProductRepository
- ProductRepository is injected in ProductManager
- Remove deprecated flexible entity config which is now builded by flexible manager itself (use doctrine meta)
- Move controllers, forms, routing and views from CatalogBundle to EnrichBundle (rename routes, forms, acls, services)
- Introduce a BaseConnectorBundle and move readers, processors, writers, archivers and related configuration from ImportExportBundle
- Introduce a TransformBundle and move cache, converters, encoders, normalizers, transformers and related configuration from ImportExportBundle
- Renaming of services of ImportExport that have been moved (pim_transform_* and pim_base_connector_*)
- Move functionality related to user preferences from LocaleManager and ChannelManager to a dedicated UserContext
- Remove AbstractFlexibleValue::isMatching() method

# 1.0.0-beta-4 - "The Abominable Snow Rabbit" (2014-01-08)

## Features
- Import product associations (CSV)
- New translation mode : Compare and copy values within a product edit form
- Convert metric values into the conversion unit selected for the channel during export
- Allow filtering and sorting by metric values
- Allow to go back to the grid or create another product after saving one
- Add products to many groups through mass edit wizard
- Attribute options fixture
- Product associations fixture
- Fixtures can be in CSV (all fixtures except users and currencies)
- Fixture files can be imported through a command (all fixtures except users and currencies)
- Add quick create popin for jobs
- Add a WYSIWYG editor for TextArea attributes

## Improvements
- Improve the user experience for family management
- Update import / export detail view by adding a summary
- Improve installer to provide different data set (minimal or dev)
- Use a form extension to apply select2 only on specified fields
- Add real time versioning option in product import
- Merge the configuration of import/export job steps in the first tab of the edit view
- Implement save of base unit and data for metric entity
- Metric values are now exported in two distinct columns (value and unit)
- Metric values can now be imported through two distinct columns ([examples](https://github.com/akeneo/pim-community-dev/blob/42371c0d6c70801a4a23a7aa8cf87e18f417c4a8/features/import/import_products.feature#L170-L198))
- Ajaxify the completeness tab of product edit form
- Change the channel switcher and collapse/expand modes on product edit view
- Add a loading mask when loading quick creation form
- Allow to switch configuration between ORM and ODM
- Update OroPlatform from beta-1 to beta-5
- Move Batch Form Types to ImportExport bundle and refactor them to be able to configure any kind of job
- Don't display several UI elements when users don't have the corresponding rights
- Use aliases for subforms, no more manual instanciation to enhance extensibility
- Product prices can now be imported with a single column per currency

## Bug fixes
- Missing pending versionable entities
- Product edit form fails with memory limit for products contained in large groups
- When I delete a filter price or metric and add it again, the filter is not applied
- Translate metric units in select field
- Values of attributes with the type Number are displayed with .0000 on product edit
- Reduce metric field width
- Sort by metric value in product datagrid
- Constraint of unicity for products of a variant group
- When reimporting a product, history for this product shows Create instead of Update
- The completness calculation takes a lot of time after importing in IcecatDemo
- Apply select2 only on needed fields
- Inverse unit and data position for metric form field
- Unwanted popin when try to leave attribute edit view
- Display bug on channel selector with long labels
- Versioning is not called after import
- I can select a root of a tree in the mass-edit wizard
- Products with no completeness do not show in the grid when selecting All products
- Exporting products with an empty file attribute value fails
- The count of Write when I export products is wrong
- Attributes are created even with minimal install
- Error on disallowed decimal on price are not displayed at the right place
- Initial state of completeness filter is wrong
- Search should take account of ACLs
- Oro mapping issue with search item on beta-1
- Locale selector in the product header is sometimes too short
- Allow to remove a translation setting it to empty
- Completeness doesn't take into account currencies of channels

## BC breaks
- Change AbstractAttribute getters that return a boolean value to use the 'is' prefix instead of 'get'. The affected getters are 'getScopable', 'getTranslatable', 'getRequired', 'getUnique'.
- Product, ProductValue, Media and ProductPrice have switched from Pim\Bundle\CatalogBundle\Entity namespace to the Pim\Bundle\CatalogBundle\Model namespace, to pave the way for the MongoDB implementation
- AbstractEntityFlexible getValue method now returns null in place of false when there is now value related to attribute + locale + scope
- Completeness and Product are not linked any more via a Doctrine relationship. We are cutting the links between Product and other entities in order to pave the way to the ability to switch between MongoDB and ORM while using the same API (apart from Product repository).
- Same thing than above for Category
- Relation between Family and Product has been removed from Family side
- Remove PimDataAuditBundle
- Remove PimDemoBundle
- Move product metric in catalog bundle
- Change jobs.yml to batch_jobs.yml and change expected format to add services and parameters
- Rename getStorageManager in flexible manager and change related references
- Rename AttributeTypeManager to AttributeManager and change related references, move createAttribute, createAttributeOption, createAttributeOptionValue from ProductManager to AttributeManager
- Introduce AttributeManagerInterface and remove references to concrete class
- Change attribute type configuration, refactor the attribute type compiler pass and attribute type factory
- Remove getAttributeOptionValueRepository, getFlexibleValueRepository from FlexibleManager
- Attribute fixtures format has changed
- Product associations import/export format has changed.
- Rename Association to AssociationType and all properties/methods linked to this class.
- Rename ProductAssociation to Association
- Rename ProductAttribute to Attribute

# 1.0.0-beta-3 - "Hare Conditioned" (2013-12-04)

## Features
- History of changes for groups and variant groups
- History of changes for import / export profiles
- History of changes for channels
- Allow creating new options for simple select and multiselect attributes directly from the product edit form
- Add a default tree per user
- Introduce command "pim:completeness:calculate" size argument to manage number of completenesses to calculate
- Switching tree to see sub-categories products count and allow filtering on it
- Group types management
- Import/Export product groups (CSV)
- Import/Export associations (CSV)
- Export product associations (CSV)
- Import/Export attributes (CSV)
- Import/Export attribute options (CSV)
- Upload and import an archive (CSV and medias)
- Download an archive containing the exported products along with media
- Add the column "enabled" in the CSV file for products import/export and for versioning

## Improvements
- Export media into separated sub directories
- Separate product groups and variants management
- Display number of created/updated products during import
- Speed up completeness calculation
- Display the "has product" filter by default in the product grid of group edit view
- Display currency label in currencies datagrid
- Disable changing the code of all configuration-related entities
- Merge the directory and filename of export profiles into a single file path property

## Bug fixes
- Mass delete products
- Fix some issues with import ACL translations (issues#484)
- Add a message when trying to delete an attribute used by one or more variant groups instead of throwing an error
- Selection of products in mass edit
- Versioning of installed entities (from demo bundle)
- For csv export of products, only export values related to selected channel and related locales
- Fix locale activation/deactivation based on locales used by channels
- Fix issue with 100 products csv import

## BC breaks
- Command "pim:product:completeness-calculator" has been replaced into "pim:completeness:calculate"
- Refactor in ImportExport bundle for Readers, Writers and Processors

# 1.0.0-beta-2 - "Hold the Lion, Please" (2013-10-29)

## Features
- Manage variant groups
- CRUD actions on groups
- Manage association between groups and products
- CRUD actions on association entities
- Link products with associations
- Import medias from a CSV file containing name of files
- Export medias from a CSV file
- Apply rights on locales for users
- Do mass classification of products
- Define price attribute type with localizable property

## Improvements
- Upgrade to BAP Beta 1
- Homogenize title/label/name entity properties using label
- Mass actions respects ACL
- Improve Import/Export profile view
- Hide access to shortcut to everyone
- Number, date and datetime attributes can be defined as unique values
- Use server timezone instead of UTC timezone for datagrids
- Make upload widget work on FireFox
- Display skipped data errors on job report

## Bug fixes
- Fix sorting channels by categories
- Bug #324 : Translate group label, attribute label and values on locale switching
- Number of products in categories are not updated after deleting products
- Fix dashboard link to create import/export profile
- Fix price format different between import and enrich
- Fix channel datagrid result count
- Fix end date which is updated for all jobs
