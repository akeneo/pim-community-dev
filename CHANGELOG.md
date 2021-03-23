
# master

## Bug fixes

- PIM-9678: The time counter is still running despite the job failed
- PIM-9672: Error 500 on the API when inputing [null] on an array
- PIM-9595: Avoid 403 error when launching import with no view rights on import details
- PIM-9622: Fix query that can generate a MySQL memory allocation error
- PIM-9630: Fix SQL sort buffer size issue when the catalog has a very large number of categories
- PIM-9636: Fix add posibility to contextualize translation when create a variant depending on number of axes
- PIM-9631: fix attribute groups not displayed in family due to JS error
- PIM-9649: Fix PDF product renderer disregarding permissions on Attribute groups
- PIM-9650: Add translation key for mass delete action.
- PIM-9642: Refresh product image when switching channel or locale
- PIM-9667: Prevent import of duplicate options in multiselect attributes
- PIM-9658: Add missing backend permission checks
- PIM 9657: Make open filters close when opening a new one.
- PIM-9671: Provide a data quality insight status context for attribute groups
- PIM-9670: Fix attribute filter "Group" issue when several attribute groups have the same label
- PIM-9664: Display Ziggy as asset image when the preview cannot be generated
- PIM-9681: Fix criteria selector closing behavior on the product grid filters
- PIM-9686: Fix memory leak during "set_attribute_requirements" job
- PIM-9690: Fix job remaining in stopping status forever
- PIM-9700: Add batch-size option in index products command and index product-models command
- PIM-9701: Fix role deletion when a user do not have any role
- PIM-9699: Fix clicking detail on last operation return 404 on import and export jobs
- API-1483: Fix the test button of the Event Subscription
- PLG-63: Fix product-grid grouped variant filter dropdown
- PIM-9718: Decimals attribute values with no separators are well formatted
- PIM-9727: Add missing query params to hatoas links
- API-9698: Refresh ES index after creating a product from the UI in order to well send product created event to event subscriptions
- PIM-9711: Check that a category root isn't linked to a user or a channel before moving it to a sub-category
- PIM-9730: Fix category tree initialization in the PEF when switching tabs
- PIM-9679: Clean existing text attribute values removing linebreaks
- PIM-9758: Fix bad replacement for line breaks introduced in PIM-9658
- PIM-9743: Add the "change input" event so that the SKU/code doesn't disappear when doing copy/paste 
- PIM-9759: Fix step name translation for product models csv import
- PIM-9740: Prevent to delete a channel used in a product export job

## New features

- DAPI-1443: Add possibility to export products depending on their Quality Score
- DAPI-1480: Add possibility to filter products on Quality Score through the API

## Improvements

# Technical Improvements

- PIM-9648: Mitigate DDoS risk on API auth endpoint by rejecting too large content

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

