# master

## Bug fixes

- PIM-9946: Throw warning when product import contains two products with same identifiers
- PIM-9885: Associating a product to itself with a 2-way association returns an error 500
- PIM-9890: Creating Channels with numeric code breaks the PIM
- PIM-9748: Upgrade JQuery for security reasons
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
- PIM-9764: Fix DSM Card component to handle links properly
- PIM-9773: Fix unique variant axis validator considering 01 and 1 as equal
- PIM-9767: Fix minimum & maximum user password validation
- PIM-9765: Fix missing translation key in bulk actions when adding attributes values for some product
- PIM-9780: Fix completed import/export job notification broken link
- PIM-9783: Optimize batch query when compute completeness
- PIM-9783: Optimize SQL query when compute completeness
- PIM-9715: Prevent the deletion of an attribute used as a label by a family
- PIM-9781: Fix Category tree not refreshing when switching locale
- PIM-9779: Fix ACE order when loading ACLs
- PIM-9739: Fix connection users, users, channels having a link to a sub-category
- PIM-9763: Make sure that 2 users can each create a private view with the same name
- PIM-9798: Refresh completeness on product grid after family import
- PIM-9800: Fix event not sent issue when creating products or product models
- PIM-9809: Fix missing filters in the product grid for few UI locales with Firefox
- PIM-9807: Trigger warning when importing date as text attribute via XLSX files
- PIM-9801: Fix jobs that are still stuck in STARTED and STOPPING and create a command to avoid this again
- PIM-9771: Fix the image preview when exporting a product as pdf
- PIM-9829: Fix product grid crash when using a family filter on a deleted family
- PIM-9820: Fix the Error 500 on the product grid with the date filter
- PIM-9833: Fix null pointer exception on Product::getVariationLevel (CE contribution)
- PIM-9826: Display the system attribute filters with the UI locale on the user account settings
- PIM-9834: Fix MySQL error when trying to import new attribute options to attributes with a lot of options already
- PIM-9850: Fix broken section title in DQI dashboard
- PIM-9827: Fix HTTP 500 when using POST/PATCH with incorrect format
- PIM-9857: Fix Microgram & Microliter conversion operations
- PIM-9856: Fix children completeness query being too long
- PIM-9853: Make the word "product" translatable
- PIM-9741: Fix choice filter mask not closing when selecting with keyboard
- PIM-9806: Enable authentication temporary lock to protect against brute force attack
- PIM-9864: Fix 500 error when using DateTime filter with invalid value
- PIM-9869: Fix download log in job tracker is only available when log is located in the fpm server
- PIM-9777: Fix error message when trying to delete an attribute linked to an entity
- PIM-9873: Fix since last n day filter in product export
- PIM-9852: Fix exception during PRE_REMOVE on removeAll cause ES desynchronisation
- PIM-9876: Fix purge of products old scores in Data Quality Insights
- PIM-9881: Do not update a product value which was not modified
- PIM-9863: Remove temporisation and add unit tests for product model reindexation.
- PIM-9891: Fix missing sanity checks when computing enrichment status
- PIM-9886: Fix display of completeness in the PEF when the selected locale is deactivated
- PIM-9925: Fix roles that couldn't contain dashes in their codes
- PIM-9933: Fix delete category menu that stays displayed on screen
- PIM-9949: Fix category edit page to use catalog locale
- PIM-9950: Fix import product model fail instead of warning vs permission
- PIM-9948: Fix performance issue on product model import
- PIM-9966: Fix Settings page crashing when coming from the PEF
- PIM-9973: Fix Asset attribute media type dropdown being hidden
- PIM-9986: Fix error message returned by the backend not displayed when an error occured while deleting a category
- PIM-9942: Fix message on DQI dashboard in French UI locale
- PIM-9947: Display validation errors message in the UI when `compute_family_variant_structure_changes` job fails
- PIM-9987: Fix product grid count not accurate after specific SKU selection

## New features

- DAPI-1443: Add possibility to export products depending on their Quality Score
- DAPI-1480: Add possibility to filter products on Quality Score through the API

## Improvements

- PIM-9716: Autoselect last element of pasted list in choice filter
- PIM-9985: Improve channels sentence display in the settings menu

# Technical Improvements

- PIM-9648: Mitigate DDoS risk on API auth endpoint by rejecting too large content
- PIM-9697: Exported files streamer
- PIM-9719: Add the real "updated" values in ES for product and product models
- CPM-152: Use Symfony Messenger to handle job queue messages. Therefore the `akeneo_batch_job_execution_queue` table is removed.
  Depending on your environment, please check the associated `messenger.yml` to figure out how the messages are sent/received.
  The former command to launch job consumption is removed and replaced by:

```bash
bin/console messenger:consume ui_job import_export_job data_maintenance_job
```

- PIM-9929: Improve performances of attribute options list PATCH endpoint when Data Quality Insights is enabled

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
  - add `Symfony\Contracts\Translation\LocaleAwareInterface\LocaleAwareInterface $localeAware`

### CLI commands

### Services
