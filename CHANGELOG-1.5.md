# 1.5.x

## Technical improvements

- PIM-4964: Use enable / disable import parameter only to create the product

## Bug fixes

## BC breaks

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
- Change constructor of `Pim\Component\Localization\Normalizer\ProductValueNormalizer`. Add `Pim\Component\Localization\Localizer\LocalizerInterface` and `Pim\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\Normalization\VariantGroupProcessor`. Add `array` of available decimal separators and `array` of available date formats as third and fourth parameter.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`. Add `Pim\Component\Localization\Provider\DateFormatProviderInterface` as third parameter.
