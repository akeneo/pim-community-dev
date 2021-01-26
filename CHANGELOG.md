
# master

## Bug fixes

- PIM-9595: Avoid 403 error when launching import with no view rights on import details
- PIM-9622: Fix query that can generate a MySQL memory allocation error
- PIM-9630: Fix SQL sort buffer size issue when the catalog has a very large number of categories
- PIM-9636: Fix add posibility to contextualize translation when create a variant depending on number of axes
- PIM-9631: fix attribute groups not displayed in family due to JS error
- PIM-9649: Fix PDF product renderer disregarding permissions on Attribute groups
## New features

## Improvements

# Technical Improvements

## Classes

## BC Breaks

- CPM-101: Remove twig/extensions dependency (abandoned)
- CPM-100: replace deprecated `Symfony\Component\Translation\TranslatorInterface` by `Symfony\Contracts\Translation\TranslatorInterface`
- CPM-100: replace deprecated `Symfony\Component\HttpKernel\Event\GetResponseEvent` by `Symfony\Component\HttpKernel\Event\RequestEvent`

### Codebase

- Change constructor of `Oro\Bundle\PimDataGridBundle\Controller\DatagridController` to remove `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating`
- Change constructor of `Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType` to remove `Symfony\Component\Translation\TranslatorInterface $translator`
- Change constructor of `Oro\Bundle\PimFilterBundle\Filter\ProductValue\MetricFilter` to remove `Symfony\Component\Translation\TranslatorInterface $translator`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VersionNormalizer` to add `Symfony\Contracts\Translation\LocaleAwareInterface\LocaleAwareInterface $localeAware`
- Change constructor of `Akeneo\UserManagement\Bundle\EventListener\LocaleSubscriber` to:
    - remove `Symfony\Component\Translation\TranslatorInterface $translator`
    - add  `Symfony\Contracts\Translation\LocaleAwareInterface\LocaleAwareInterface $localeAware`

### CLI commands

### Services

