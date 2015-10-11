# 1.5.x

## Technical improvements

## Bug fixes

## BC breaks

- Column 'comment' has been added on the `pim_notification_notification` table.
- Change constructor of `Pim\Bundle\TranslationBundle\Twig\TranslationsExtension`.
  Replace `Oro\Bundle\LocaleBundle\Model\LocaleSettings` by `Symfony\Component\HttpFoundation\RequestStack`.
- Change constructor of `Pim\Bundle\UserBundle\EventSubscriber\LocalSubscriber`.
  Rename by `Pim\Bundle\UserBundle\EventSubscriber\LocaleSubscriber` and remove `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage`
- Remove OroEntityBundle
- Remove OroEntityConfigBundle
- Remove PimEntityBundle
- Move DoctrineOrmMappingsPass from Oro/EntityBundle to Akeneo/StorageUtilsBundle
- Remove OroDistributionBundle (explicitely define oro bundles routing, means oro/rounting.yml are not automaticaly loaded anymore, and remove useless twig config)
