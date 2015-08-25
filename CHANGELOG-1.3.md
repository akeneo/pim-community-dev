# 1.3.22 (2015-08-25)

## Bug fixes
- PIM-4612: Error on Quick Export (MongoDB)
- PIM-4803: Drag & Drop is too long when I filter on attribute group

# 1.3.21 (2015-08-17)

## Bug fixes
- PIM-4753: Fix updated date issues for Versionable objects

# 1.3.20 (2015-08-14)

## Bug fixes
- PIM-4737: Fix a bug with the select2 cache.

# 1.3.19 (2015-08-13)

## Bug fixes
- PIM-4706: Product association import error with identifier containing comma or semicolon
- PIM-4748: performances issues with product display with 8 locales (attribute and attribute group translations)
- PIM-4444: performances issues with product display with 10 locales (attribute and attribute group translations)
- PIM-4756: Filter issue with the option "start with" "0" on the product grid
- PIM-4758: Fixed attribute filtering on method getFullProducts (Github issue #3028)

# 1.3.18 (2015-07-09)

## Bug fixes
- PIM-4528: Fix attribute field display on error during mass edit
- PIM-4535: Fix font problems on pdf generation: you can now set a custom font by setting the %pim_pdf_generator_font% parameter.

# 1.3.17 (2015-07-07)

## Bug fixes
- PIM-4494: Fix loading page when family has been sorted

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

# 1.3.3 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.3.2 (2015-02-27)

## Bug fixes
- PIM-3665: Remove media even if file not on filesystem
- PIM-3834: add missing cascade detach product -> associations, product -> completenesses
- PIM-3820: Attribute option translation not well handled on import
- PIM-3762: Fix the bug on image not well displayed on pdf export

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

