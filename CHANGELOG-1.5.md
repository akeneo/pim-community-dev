# 1.5.x

## Technical improvements

- PIM-4964: Use enable / disable import parameter only to create the product
- Family is not hardcoded anymore
- PIM-4743: Added the possibility to use optgroup in Oro ChoiceFilter
- PIM-4347: `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface` now extends `Doctrine\Common\Persistence\ObjectRepository`
- PIM-5067: Change the JUnit formatter of behats logs

## Bug fixes

## BC breaks

- Change constructor of `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder`. Add `Pim\Component\Localization\LocaleResolver` as the fourth argument.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor`. Add argument `Pim\Component\Localization\Localizer\LocalizerRegistryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`. Add argument `Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Handler\GroupHandler`. Add argument `Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber`. Add argument `Pim\Component\Localization\LocaleResolver`.
- Change constructor of `Pim\Bundle\UIBundle\Form\Type\NumberType`. Add arguments `Pim\Component\Localization\LocaleResolver` and `Pim\Component\Localization\Localizer\LocalizerInterface`.
- Add `Pim\Component\Localization\Localizer\LocalizedAttributeConverter` to `Pim\Component\Connector\Processor\Denormalization\ProductProcessor`
- Add an array `$decimalSeparators` to `Pim\Component\Connector\Reader\File\CsvProductReader`
- Column 'comment' has been added on the `pim_notification_notification` table.
- Remove OroEntityBundle
- Remove OroEntityConfigBundle
- Remove PimEntityBundle
- Move DoctrineOrmMappingsPass from Oro/EntityBundle to Akeneo/StorageUtilsBundle
- Remove OroDistributionBundle (explicitely define oro bundles routing, means oro/rounting.yml are not automaticaly loaded anymore, and remove useless twig config)
- Change constructor of `Pim\Bundle\TranslationBundle\Twig\TranslationsExtension`. Replace `Oro\Bundle\LocaleBundle\Model\LocaleSettings` by `Symfony\Component\HttpFoundation\RequestStack`.
- Removed `Pim\Bundle\UserBundle\EventListener\LocalListener` (use `Pim\Bundle\UserBundle\EventListener\LocaleListener` instead).
- Change constructor of `Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`. Add `Pim\Component\Localization\Provider\LocaleProviderInterface` as the first argument.
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
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\PriceProperty` and `Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\MetricProperty`. Add `Pim\Component\Localization\Formatter\FormatterInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor`. Add `array` of available decimal separators and `array` of available date formats.
- Change constructor of `Pim\Component\Localization\Normalizer\ProductValueNormalizer`. Add `Pim\Component\Localization\Localizer\LocalizerRegistryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\Normalization\VariantGroupProcessor`. Add `array` of available decimal separators and `array` of available date formats as third and fourth parameter.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`. Add `Pim\Component\Localization\Provider\DateFormatProviderInterface` as third parameter.
- Change constructor of `Pim\Bundle\CatalogBundle\Factory\FamilyFactory`. Add family classname as last parameter.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController`. Add family classname as last parameter.
- Change `Pim\Bundle\EnrichBundle\Controller\FamilyController` methods parameters for `editAction`, `removeAction`, `historyAction` and `addAttributesAction` changing Family by integer (id).
- Change parameters of `renderStatefulGrid` of `Pim\Bundle\DataGridBundle\Resources\views\macros.html.twig` array `defaultView` has been added.
- Change constructor of `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator`. Unused datagrid view repository has been removed.
- Change constructor of `Pim\Bundle\UserBundle\Form\Type\UserType`. Added EventDispatcher as last parameter.
- Remove class `Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager`
- Remove class `Pim\Bundle\CatalogBundle\Manager\AssociationManager`
- Remove deprecated method valueExists from `Pim\Bundle\CatalogBundle\Manager\ProductManager`
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder` to inject ProductRepositoryInterface an AttributeRepositoryInterface
- Added method `getAttributeCodesByGroup` to the `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`
- Changed constructor of `Pim\Bundle\TransformBundle\Normalizer\Structured\AttributeGroupNormalizer`, made AttributeRepository mandatory