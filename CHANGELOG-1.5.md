# 1.5.x

## Technical improvements

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
