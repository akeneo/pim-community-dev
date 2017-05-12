# 1.5.x

## Bug fixes

- PIM-6395: Fix MongoDB query built to fetch products in a LazyCollection
- PIM:6405: display channel's label instead of channel's code in the completeness panel of the PEF

# 1.5.21 (2017-04-28)

## Bug fixes

- #5941: Fix memory leak when importing variant groups, cheers @nei!

# 1.5.20 (2017-02-09)

# 1.5.19 (2017-02-06)

## Bug fixes

- PIM-6146: Fix product import after associated product deletion (MongoDB)
- PIM-6140: Fix 'equals to' filters on job tracker page

# 1.5.18 (2017-02-01)

## Bug fixes

- PIM-6038: Fix product imports that do not change the product update date correctly (mongodb)
- PIM-6042: Successfully import product associations without removing already existing associations when option "compare values" is set to true
- PIM-6110: Saving a product value clears the saved associations

# 1.5.17 (2017-01-18)

## Bug fixes

- PIM-5854: On MySQL, the family code is not displayed if the family label is empty
- PIM-6107: On MongoDb, the family code is not displayed if the family label is empty
- PIM-6064: Fix a grid issue with attribute named ID 
- PIM-6092: Always allow to create new option on select2

## Technical improvements

- PIM-6064: ProductQueryBuilder addFilter is now case sensitive.

# 1.5.16 (2016-12-28)

## Bug fixes

- PIM-6019: Successfully import products without removing already existing associations when option "compare values" is disabled
- PIM-6034: Fix date format on fixtures
- PIM-6039: Call validation on family mass edit

# 1.5.15 (2016-12-13)

## Bug fixes

- PIM-6026: Fix an error on family mass edit that occurs when working on more families than the mass edit batch size
- PIM-5991: Focus on wysiwig field when clicking on the product edit form completeness link
- PIM-6022: Fix shell injection in mass-edit form

# 1.5.14 (2016-12-01)

## Bug fixes

- PIM-5990: Fix persist order causing issue on variant group import with associated products
- PIM-5989: Fix attribute options on localizable and scopable attributes simple select
- PIM-6013: Fix attribute options on localizable and scopable attributes simple select and multi select
- PIM-6002: Fix characters escapment with usage of quote in attribute option
- PIM-5997: Restrict to 25 characters the role label
- PIM-5862: Fix product grid display on a custom user view

# 1.5.13 (2016-11-18)

## Bug fixes

- PIM-6005: Boost product export performances by loading less products at the same time
- PIM-5995: Fix issue with product count on group save
- PIM-6006: Fix small memory leak when iterating over products cursor

# 1.5.12 (2016-11-04)

## Bug fixes

- PIM-5995: Fix issue with locale specific attributes added to variant groups

# 1.5.11 (2016-10-31)

## Bug fixes

- PIM-5993: Fix value display issues with simple/multi select attributes
- GITHUB-5119: Remove console.log from renderExtension method of `src/Pim/Bundle/EnrichBundle/Resources/public/js/product/form.js` Cheers @a2xchip!
- PIM-5995: Fix issue with localizable and scopable attributes added to variant groups

# 1.5.10 (2016-10-14)

## Bug fixes

- PIM-5984: Optimization on product import when iterating on each existing attributes
- PIM-5985: Fix the import of localizable and scopable variant group attributes (backport of PIM-5915)
- PIM-5973: Fix association between products with new association types

# 1.5.9 (2016-09-27)

## Bug fixes

- PIM-5777: Fix attribute refresh when locale change in Product Edit Form
- TIP-307: Fix issues with Mongo 2.6

# 1.5.8 (2016-08-25)

## Bug fixes

- PIM-5767: Issue with filter "in list" when SKU contains dashes (-)
- PIM-5896: Fix reference data name validation when creating a reference data simple/multi-select attribute

## Technical improvements

- Add a duplicated product values cleaning command

# 1.5.7 (2016-07-19)

# 1.5.6 (2016-07-06)

## Bug fixes

- PIM-5864: "Attribute used as label" not displayed after being changed in the Family

# 1.5.5 (2016-06-16)

- PIM-5711: Don't create empty attribute translations if attributes are imported with empty labels
- PIM-5829: Fix an issue with wrong parameters order in FamilyUpdater
- PIM-5828: Correctly render product SKU in history pin bar
- PIM-5844: Fix export with medias when identifier contains slash
- PIM-4346: Fix datetimepicker on grid filters
- PIM-5851: Display product state changes when classifying/declassifying a product

# 1.5.4 (2016-06-01)

## Bug fixes

- PIM-5710: Fix thumbnail display after file upload in the product edit form
- PIM-5811: Fix family export with multiple locales activated
- PIM-5726: Fix number of product displayed on Product Grid when category panel is withdrawn
- PIM-5801: Fix save in product edit form when attribute code is only numeric
- PIM-5802: Keep data previously filled in select2 filter
- PIM-5824: Fix memory leak on products export
- PIM-5712: Keep reference in "akeneo_file_storage_file_info" table after removing a media attribute from a product

# 1.5.3 (2016-05-13)

## Bug fixes

- PIM-5666: Fix product value saving with value '0'
- PIM-5728: Fix bug in price and metric datagrid filters
- PIM-5727: Fix permission issue on add-attribute extension on edit common attributes step
- PIM-5754: Add labels to simple and multi select fields
- PIM-5763: Add Boolean presenter for fields
- PIM-5622: Fix NOT IN operator behavior for ORM groups filter
- PIM-5756: Fix empty min and max numbers on attributes import
- PIM-5655: Hide dropdown menu after action

## Technical improvements

- PIM-5762: Removed unused category filters on product datagrids
- Upgrade "akeneo/measure-bundle" from "0.4.1" to "0.5.0", details in the release note https://github.com/akeneo/MeasureBundle/releases/tag/0.5.0

## Bug fixes

- PIM-5643: Fix default system locale was not fixed by the last PIM-5643 ticket, tagged in 1.5.2

# 1.5.2 (2016-04-25)

## Bug fixes

- PIM-5697: Fix import form when a file extension is not allowed
- PIM-5695: Do not format price with currency if data is null
- PIM-5643: Fix default system locale

# 1.5.1 (2016-03-09)

## Bug fixes

- Only UPGRADE-*.md files are now copied from the development version to the standard edition during `php composer.phar update`

# 1.5.0 (2016-03-08)

## Bug fixes

- PIM-5509: Fix grid date filters initialization

# 1.5.0-RC1 (2016-03-02)

## BC breaks

- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`. Update argument `Akeneo\Component\StorageUtils\Updater\PropertySetterInterface` to `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\VersionNormalizer`. Add `Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MetricDenormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\PricesDenormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValue\ProductValueNormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Normalizer\Flat\ProductValueNormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.

# 1.5.0-BETA1 (2016-02-22)

## Bug fixes

- PIM-5508: Variant group edition fix

## BC breaks

- Change constructor of `Pim\Bundle\CommentBundle\Normalizer\Structured\CommentNormalizer` to add `Pim\Component\Localization\Presenter\PresenterInterface` and `Pim\Component\Localization\LocaleResolver`
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\VersionNormalizer` to add `Pim\Component\Localization\Presenter\PresenterInterface`
- Change constructor of `Pim\Bundle\DashboardBundle\Widget\LastOperationsWidget` to add `Pim\Component\Localization\Presenter\PresenterInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Removed `Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController` and `Pim\Bundle\EnrichBundle\AbstractController\AbstractController`.
- Change constructor of `Pim\Bundle\EnrichBundle\Filter\ProductEditDataFilter` to add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface` and to remove `Oro\Bundle\SecurityBundle\SecurityFacade`, `Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface`, `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`, `Pim\Component\Catalog\Repository\LocaleRepositoryInterface` and `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController` to add `Pim\Bundle\CatalogBundle\Factory\AttributeFactory`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeOptionController` to add `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\TransformBundle\Transformer\AttributeTransformer` to remove `Pim\Bundle\CatalogBundle\Manager\AttributeManager` and add `Pim\Bundle\CatalogBundle\Factory\AttributeFactory`

# 1.5.0-ALPHA1 (2016-01-26)

## Technical improvements

- PIM-4964: Use enable / disable import parameter only to create the product
- Family is not hardcoded anymore
- PIM-4743: Added the possibility to use optgroup in Oro ChoiceFilter
- PIM-4347: `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface` now extends `Doctrine\Common\Persistence\ObjectRepository`
- PIM-5067: Change the JUnit formatter of behats logs
- PIM-5217: Create a Buffer component and new file writer implementations that use it
- PIM-4646: TinyMCE wysiwyg editor is replaced by Summernote in the mass-edit and variant group
- PIM-4999: jQuery UI datepicker is replaced by bootstrap datepicker in the mass-edit and variant group
- A new twig extension (StyleExtension) in UIBundle now provides a "highlight" string filter
- PIM-5450: MongoDb ODM bundle in dev requirements
- PIM-5380: It is now possible to group grid actions in dropdown by specifying it in the grid configuration (see quick export in the product grid for an example)
- PIM-5481: New command to analyze products CSV files to get stats

## Bug fixes

## BC breaks
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor` to add `Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface` and `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
- Service `oro_filter.form.type.date_range` is removed and replaced by `pim_filter.form.type.date_range`
- Service `oro_filter.form.type.datetime_range` is removed and replaced by `pim_filter.form.type.datetime_range`
- Delete class `Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager` its service `pim_catalog.manager.product_mass_action`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\EditCommonAttributesType` to keep only $dataclass
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`. Removed arguments `Pim\Bundle\CatalogBundle\Context\CatalogContext`, `Symfony\Component\Serializer\Normalizer\NormalizerInterface`, `Akeneo\Component\FileStorage\File\FileStorerInterface`, `Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager`. Added arguments `Pim\Component\Catalog\Localization\Localizer\LocalizerRegistry` and `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Service `oro_form.type.date` is removed and replaced by `pim_form.type.date` (alias `oro_date` is replaced by `pim_date`)
- Change constructor of `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder`. Add `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` as the fourth argument.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`. Add argument `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Handler\GroupHandler`. Add argument `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber`. Add argument `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Change constructor of `Pim\Bundle\UIBundle\Form\Type\NumberType`. Add arguments `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` and `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Component\Catalog\Localization\Localizer\LocalizedAttributeConverter`. Add argument `Pim\Component\Connector\Processor\Denormalization\ProductProcessor`
- Change constructor of `Pim\Component\Connector\Reader\File\CsvProductReader`. Add an array `$decimalSeparators`
- Column 'comment' has been added on the `pim_notification_notification` table.
- Remove OroEntityBundle
- Remove OroEntityConfigBundle
- Remove PimEntityBundle
- Move DoctrineOrmMappingsPass from Oro/EntityBundle to Akeneo/StorageUtilsBundle
- Remove OroDistributionBundle (explicitely define oro bundles routing, means oro/rounting.yml are not automaticaly loaded anymore, and remove useless twig config)
- Change constructor of `Pim\Bundle\TranslationBundle\Twig\TranslationsExtension`. Replace `Oro\Bundle\LocaleBundle\Model\LocaleSettings` by `Symfony\Component\HttpFoundation\RequestStack`.
- Removed `Pim\Bundle\UserBundle\EventListener\LocalListener` (use `Pim\Bundle\UserBundle\EventListener\LocaleListener` instead).
- Change constructor of `Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`. Add `Akeneo\Component\Localization\Provider\LocaleProviderInterface` as the first argument.
- Move `LocaleType` from `Oro\Bundle\LocalBundle\Form\Type` to `Pim\Bundle\LocalizationBundle\Form\Type`
- Move `UserType` from `Oro\Bundle\UserBundle\Form\Type` to `Pim\Bundle\UserBundle\Form\Type`
- Added Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface to the constructor of Pim\Component\Catalog\Updater\Remover\RemoverRegistry, Pim\Component\Catalog\Updater\Adder\AdderRegistry, Pim\Component\Catalog\Updater\Setter\SetterRegistry and Pim\Component\Catalog\Updater\Copier\CopierRegistry
- Added Pim\Bundle\CatalogBundle\Repository\AttributeRequirementRepositoryInterface to the constructor of Pim\Component\Catalog\Updater\FamilyUpdater
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\ProductProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\HeterogeneousProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\HomogeneousProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\Normalization\FamilyProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Helper\LocaleHelper`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ChannelType`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionNormalizer`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Deleted class `Pim\Bundle\CatalogBundle\Manager\LocaleManager` we should now use the `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\PriceProperty`. Add `Akeneo\Component\Localization\Presenter\PresenterInterface`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\MetricProperty`. Add `Akeneo\Component\Localization\Presenter\PresenterInterface`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\NumberProperty`. Add `Akeneo\Component\Localization\Presenter\PresenterInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor`. Add `array` of available decimal separators and `array` of available date formats.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\Normalization\VariantGroupProcessor`. Add `array` of available decimal separators and `array` of available date formats as third and fourth parameter.
- Change constructor of `Pim\Bundle\CatalogBundle\Factory\FamilyFactory`. Add family classname as last parameter.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController`. Add family classname as last parameter.
- Change `Pim\Bundle\EnrichBundle\Controller\FamilyController` methods parameters for `editAction`, `removeAction`, `historyAction` and `addAttributesAction` changing Family by integer (id).
- Change parameters of `renderStatefulGrid` of `Pim\Bundle\DataGridBundle\Resources\views\macros.html.twig` array `defaultView` has been added.
- Change constructor of `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator`. Unused datagrid view repository has been removed.
- Change constructor of `Pim\Bundle\UserBundle\Form\Type\UserType`. Added EventDispatcher as last parameter.
- Remove class `Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager`
- Remove class `Pim\Bundle\CatalogBundle\Manager\AssociationManager`
- Remove deprecated method valueExists from `Pim\Bundle\CatalogBundle\Manager\ProductManager`
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder` to inject ProductRepositoryInterface and AttributeRepositoryInterface
- Added method `getAttributeCodesByGroup` to the `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Changed constructor of `Pim\Bundle\TransformBundle\Normalizer\Structured\AttributeGroupNormalizer`, made AttributeRepository mandatory
- Remove deprecated method scheduleForChannel from `Pim\Bundle\CatalogBundle\Manager\CompletenessManager`
- Move `Pim\Bundle\BaseConnectorBundle\Writer\File\ArchivableWriterInterface` to `Pim\Component\Connector\Writer\File\ArchivableWriterInterface`
- `Pim\Component\Connector\Writer\File\YamlWriter` now inherits from `Pim\Component\Connector\Writer\File\AbstractFileWriter` therefore needs an instance of `Pim\Component\Connector\Writer\File\FilePathResolverInterface` as first parameter of the constructor
- Remove deprecated methods findAllWithTranslations, getIdToLabelOrderedBySortOrder, getAttributeGroupChoices from `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Remove deprecated methods findAllWithTranslations, getIdToLabelOrderedBySortOrder, getAttributeGroupChoices from `Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface`
- Remove deprecated methods getAvailableAttributesAsLabelChoice, getAttributeIds from `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Remove deprecated methods getAvailableAxis, getRepository, getGroupTypeRepository, getProductList, getAttributeRepository from `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\GroupManager` to pass `Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Remove deprecated method getAttributeOptionValueClass from `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager`
- Remove deprecated methods getActiveCurrencies, getCurrencies from `Pim\Bundle\CatalogBundle\Manager\CurrencyManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeGroupController` to inject `Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController` to inject `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistry` to inject `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` and `Oro\Bundle\SecurityBundle\SecurityFacade`
- Update schema of `Pim\Component\Catalog\Model\Metric`. Increase precision of data and baseData.
- Change constructor of `Pim\Component\Connector\Processor\Denormalization\ProductAssociationProcessor` to add `Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface`
- Rename `Pim\Bundle\CatalogBundle\Validator\Constraints\Numeric` to `Pim\Bundle\CatalogBundle\Validator\Constraints\IsNumeric` to fix PHP7 compatibility
- Rename `Pim\Bundle\CatalogBundle\Validator\Constraints\NumericValidator` to `Pim\Bundle\CatalogBundle\Validator\Constraints\IsNumericValidator` to fix PHP7 compatibility
- Rename `Pim\Bundle\CatalogBundle\Validator\Constraints\String` to `Pim\Bundle\CatalogBundle\Validator\Constraints\IsString` to fix PHP7 compatibility
- Rename `Pim\Bundle\CatalogBundle\Validator\Constraints\StringValidator` to `Pim\Bundle\CatalogBundle\Validator\Constraints\IsStringValidator` to fix PHP7 compatibility
