# 1.5.x

## Technical improvements

## Bug fixes

## BC breaks

- Column 'comment' has been added on the `pim_notification_notification` table.
- Change constructor of `Pim\Bundle\TranslationBundle\Twig\TranslationsExtension`.
  Replace `Oro\Bundle\LocaleBundle\Model\LocaleSettings` by `Symfony\Component\HttpFoundation\RequestStack`.
- Change constructor of `Pim\Bundle\UserBundle\EventSubscriber\LocalSubscriber`.
  Replace `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage` by `Symfony\Component\HttpFoundation\RequestStack`
