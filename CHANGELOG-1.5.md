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
