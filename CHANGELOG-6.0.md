# 6.0.x

# 6.0.57 (2022-12-07)

## Bug fixes

- PIM-10681: Fix project without ownership after user has been deleted.

# 6.0.56 (2022-11-28)

# 6.0.55 (2022-11-21)

## Bug fixes

- PIM-10732: Fix 'Export media' parameter in asset export profile is missing in 6.0

# 6.0.54 (2022-11-18)

# 6.0.53 (2022-11-10)

# 6.0.52 (2022-11-08)

# 6.0.51 (2022-11-03)

## Bug fixes

- PIM-10691: Change method type for categories related method in rule engine to support very large number of categories
- PIM-10699: Fix "All" is exported allong with the user groups on the user export

# 6.0.50 (2022-11-02)

# 6.0.49 (2022-10-31)

# 6.0.48 (2022-10-28)

## Bug fixes

- PIM-10654: [Backport PIM-10473] Fix non-existing RE filter for PEF is not case-insensitive

# 6.0.47 (2022-10-27)

# 6.0.46 (2022-10-20)

# 6.0.45 (2022-10-06)

## Bug fixes

- PIM-10657: [Backport] Remove RequestedAuthContext from SAML Auth requests
- PIM-10632: Fix detection of orphan attribute option spellcheck lines during orphan cleaning 

# 6.0.44 (2022-09-23)

## Bug fixes

- PIM-10435: [Backport] Fix search_after requests with codes using uppercase accented characters
- PIM-10632: [Backport] Fix no purge done on the DQI when deleting option for multi-select and simple-select attributes

# 6.0.43 (2022-09-20)

# 6.0.42 (2022-09-02)

# 6.0.41 (2022-08-29)

# 6.0.40 (2022-08-25)

# 6.0.39 (2022-08-23)

## Bug fixes

- PIM-10578: Fix the search on system filters in the rule engine UI
- PIM-10585: Error when downloading file attribute from the proposals screen

# 6.0.38 (2022-08-17)

# 6.0.37 (2022-07-27)

# 6.0.36 (2022-07-21)

# 6.0.35 (2022-07-12)

# 6.0.34 (2022-07-05)

# 6.0.33 (2022-07-01)

# 6.0.32 (2022-06-28)

# 6.0.31 (2022-06-14)

# 6.0.30 (2022-06-08)

# 6.0.29 (2022-05-25)

## Bug fixes

- PIM-10451: Fix reference schema in order to take into account the index on start_time
- PIM-10453: Fix reference schema in order to take into account the index on updated in pim_catalog_category table

# 6.0.29

- PIM-10450 remove DQI deprecated table pimee_data_quality_insights_dictionary_depr

# 6.0.28 (2022-05-13)

# 6.0.27 (2022-05-10)

## Improvements

- Hotfix: Use Node v14 in docker-compose files

# 6.0.26 (2022-04-29)

## Bug fixes

- PIM-10405: [Backport PIM-10399] Fix media link internal value on initialValue change
- PIM-10422: [Backport PIM-9688] Fix case of first letter in asset title of the asset edit page

# 6.0.25 (2022-04-14)

# 6.0.24 (2022-04-13)

## Bug fixes

- PIM-10393: Fix translations for job on table options removal

# 6.0.23 (2022-04-11)

# 6.0.22 (2022-04-08)

# 6.0.21 (2022-04-06)

## Bug fixes

- PIM-10311: [Backport PIM-10285] Fix concurrent bulk publish issue
- PIM-10388: Fix error after creating the first column in an empty table attribute

# 6.0.20 (2022-04-01)

# 6.0.19 (2022-03-28)

## Bug fixes

- PIM-10369: Fix deprecated migrations present during Standard-Edition upgrade

# 6.0.18 (2022-03-24)

# 6.0.17 (2022-03-24)

# 6.0.16 (2022-03-24)

## Bug fixes

- PIM-10325: [Backport PIM-10229] Enforce lax same-site policy for session cookies

# 6.0.15 (2022-03-23)

# 6.0.14 (2022-03-22)

- PIM-10317: [Backport PIM-10237] Order connector Assets with PHP to avoid memory issues

# 6.0.13 (2022-03-18)

# 6.0.12 (2022-03-17)

# 6.0.11 (2022-03-16)

# 6.0.10 (2022-03-14)

## Bug fixes

- BH-986: Fix standard build configuration for dev

# 6.0.9 (2022-03-14)

# 6.0.8 (2022-03-11)

## Bug fixes

- RAC-1221: [Backport PIM-10236] Fix duplicated mask returned by query for table attribute
- RAC-1222: [Backport PIM-10252] Tailored Export - asset collection: "main media" is not selectable
- RAC-1220: [Backport PIM-10238] Add permissions to view mass delete assets, mass edit assets and mass delete records job logs
- CPM-543: [Backport PIM-10249] Fix error message when importing YML rule containing a reference entity record with numeric value
- PIM-10305: Reject disabled user when coming from SAML auth
- OCT-25: [Backport OCT-19] fix attribute group permissions forms
- OCT-26: [Backport OCT-23] Locale permissions are removed from App when edited through settings menu
- OCT-27: [Backport OCT-24] Prevent non-default usergroups from losing permissions on categories
- PIM-10309: [Backport PIM-10291] Fix table attribute edit blocked when locale recently deleted from channel
- PIM-10310: [Backport PIM-10289] DQI purge don't purge pimee_dqi_attribute_locale_quality table
- PIM-10312: [Backport PIM-10284] Clean product values after reference entity removal
- PIM-10316: [Backport PIM-10255] Fix LocaleCodeContext initialization place for table condition line inside rules
- PIM-10326: [Bakport PIM-10228] Fix Asset transformations job crashing when having cache resources exhausted
- PIM-10318: [Backport PIM-10259] Fix Arabic text being reversed in product PDF exports
- PIM-10368: Fix deleted localizable or scopable values on drafts

# 6.0.7 (2022-02-25)

# 6.0.6 (2022-02-21)

# 6.0.5 (2022-02-17)

# 6.0.4 (2022-02-10)

# 6.0.3 (2022-02-09)

# 6.0.2 (2022-02-09)

# 6.0.1 (2022-02-07)

# 6.0.0 (2022-02-04)

## Bug fixes

- PIM-9955: Handle duplicated code asset in the import batch.
- AOB-1277: Fix issue when helm remove disk too fast on delete instance
- PIM-9742: Asset. Product link rules "Failed" without informations instead of sending Warning.
- PIM-9675: Api search_after on asset issue for Serenity clients
- PIM-9617: Configure clean_removed_attribute_job to be run on a single daemon
- PIM-9629: Fix filtering issue on product value "identifier" via the API for published products
- PIM-9640: Fix asset and record imports in XLSX when sone cells contain only numeric characters
- PIM-9646: Make the rule engine execution permission agnostic
- PIM-9649: Fix PDF product renderer disregarding permissions on Attribute groups
- PIM-9651: Concatenate rule does not keep anymore the trailing zeros on a decimal number
- PIM-9654: Allow single quote in DQI Word Dictionary
- PIM-9655: Fix multiple spellcheck calls on multi-select attributes
- PIM-9671: Do not process spell checking on attributes with data quality analysis disabled on group
- PIM-9659: Asset Manager - Fix missing warning message when changing page via the breadcrumb with unsaved changes.
- PIM-9676: Reference Entities - Fix missing warning message when changing page via the breadcrumb with unsaved changes.
- PIM-9668: Asset attribute - Fix Text area + Rich text editor modes turn text attributes into infinite extendable fields when not using spaces
- PIM-9664: Display Ziggy as asset image when the preview cannot be generated
- PIM-9702: Fix infinite loop when using get all assets API endpoint
- PIM-9720: Fix Asset cursor search after query
- PIM-9723: Fix Mysql memory size issue during ordering in SQL when getting category trees in product grid
- PIM-9722: Fix the increasing amount of requests when editing attribute with options
- PIM-9698: Fix product and product model deletion events when entity does not have any category
- PIM-9710: Fix rule execution job status stuck in STARTED or STOPPING
- PIM-9731: Fix proposals on boolean attributes when comparing an empty and a false value
- PIM-9756: Use PNG format for Asset transformations targets
- PIM-9760: Fix link assets to products job not translated in job tracker
- PIM-9693: Fix Asset creation modal failing when no label
- PIM-9755: Hide product model proposal changes that are already reviewed
- PIM-9764: Fix open Asset in new tab in Asset Manager library
- PIM-9767: Fix minimum & maximum user password validation
- PIM-9768: Handle error when trying to delete a product model with at least 1 variant already published
- PIM-9790: Fix memory leak on rules execution
- PIM-9788: Fix locales list on rule concatenate action
- PIM-9791: Fix memory issue on proposals widget
- PIM-9682: Link to proposal product model is completely wrong
- PIM-9800: Fix event not sent issue when creating products or product models
- PIM-9805: Fix the import of ref entity records with empty multiselect attributes
- PIM-9804: Published products does not produce Pim events anymore
- PIM-9813: Add missing translation key for unpublish bulk action
- PIM-9819: Fix overflow on ref entities tabs
- PIM-9771: Fix the image preview when exporting a product as pdf
- PIM-9822: Fix Error 500 after deleting a filterable asset family attribute
- PIM-9825: Fix Assets not being properly reindexed after bulk edit
- PIM-9835: Fix DQI loading in attribute edit page with a large amount of options
- PIM-9854: Fix message when deleting a user group used in a project
- PIM-9853: Make the word "product" translatable
- PIM-9872: Fix Columns button from published product grid when catalog contains many attributes
- PIM-9866: Creates a warning whenever a cell contains a date in a reference entity xls import
- PIM-9871: Fix Published Product Grid takes long time to load for high number of attribute usable in grid
- PIM-9874: Fix slow SQL query when many assets are linked to products when getting products through API
- PIM-9879: Fix notification link when a proposal on a product model was accepted or rejected
- PIM-9897: Simultaneous different mass publish jobs create Deadlock issue
- PIM-9912: Exception is not caught at the beginning of rules execution
- PIM-9704: Disable possibility to have media link with unauthorized protocol for security propose
- PIM-9913: Fix very long loading of the proposal page
- PIM-9927: Fix data_quality_insights_evaluations job when simple or multiselect product values are invalid
- PIM-9937: Rule engine - Fix remove action for reference entity collection and asset collection attributes
- PIM-9898: Assets: Fix "attribute as main media" and naming convention inconsistency
- PIM-9965: Fix Asset Family attribute saving notification displaying twice
- PIM-9963: Fix number of lines field missing on XLSX product export job
- PIM-9973: Fix Asset attribute media type dropdown being hidden
- PIM-9961: Remove useless "global settings" tab on asset and record XLSX imports (SAAS only)
- PIM-9974: [PIM-9972] Half the selected published product are unpublished through mass action
- PIM-9984: Fix image preview in asset manager in media link attributes
- PIM-9981: Fix permission to download logs of clean removed attribute values job
- PIM-9980: Prevent root category selection in Rule Engine set categories action
- PIM-9942: Fix message on DQI dashboard in French UI locale
- PIM-9983: Fix optimization of query to get info about projects
- PIM-10009: Fix error being printed in the response of partial update of product models API
- PIM-10020: Fix asset media link preview when the URI contains special characters
- PIM-10018: Fix asset code case in the product edit form
- PIM-9989: Fix record code of reference entity field is case-sensitive
- PIM-10021: Product link rule too long to be completed
- PIM-10026: Avoid session persistance for API
- PIM-10028: Fix the ETA time displayed when executing a rule with several actions
- PIM-10042: Fix infinite loop when using get all reference entity records API endpoint
- PIM-10043: Fix search not filtering family list on the asset export settings
- PIM-10052: Fix completeness display in Linked products
- PIM-10041: Change configuration to apply APP_ELASTICSEARCH_TOTAL_FIELDS_LIMIT to assets and references entities
- PIM-10035: Add LRU cached AssetFamilyExists query
- PIM-10034: Implement LRU cache for Asset Attributes to optimize performance
- PIM-10054: Check records are not used as variant axes before mass delete
- PIM-10038: Optimize Rule logger to be executed only depending of the log level
- PIM-10033: Implement LRU cache for Asset Families to optimize performance
- PIM-10055: Optimize publication of incoming published asssocication upon product publication
- PIM-10063: Fix product link rule execution to make the property case insensitive
- PIM-10059: Fix completeness display when user does not have permission on attribute group(s)
- PIM-10066: Product export in XLSX: option for "Export media" is missing
- PIM-10065: Asset textarea is not updated when the locale is changed
- PIM-10029: Fix missing filters on proposal grid page
- PIM-10068: Fix the Permissions tab disappeared for XLSX and CSV Published Products export profiles
- PIM-10072: Fix difference on the migration Serenity > Flexibility
- PIM-10083: Increase the limit of default values displayed in reference entities and assets filters in the product grid
- PIM-10089: Add missing validation check on rule import
- PIM-10088: Fix "upload assets" button being displayed in asset families with a media link as main media when the user doesn't have the permission to create an asset
- PIM-10093: Fix tailored export access when an attribute used as source is deleted
- PIM-10091: Add translation keys for the "Computation of asset transformations" job
- PIM-10097: Fix error message when checking removal of an entity related to a published product
- PIM-10109: Fix query to get reference entity records to be case insensitive
- PIM-10103: Fix JS error on attribute options when DQI is disabled
- PIM-10106: Fix Red underlines for spelling mistakes are moving
- PIM-10087: Fix storage errors HTTP code to return 500 instead of 422
- PIM-10048: fix memory leak in search product models by family variant query
- PIM-10115: Blacklist domains for asset media links
- PIM-10110: Enrich Service check logging upon exception and slow service check.
- PIM-10152: Fix Table attribute option modal not scrolling on label translations
- PIM-10120: Remove obsolete asset reference data
- PIM-10049: Fix thumbnail generation crashing with due to imagick segmentation fault
- PIM-10151: Fix category field in rule edit form
- PIM-10168: Fix Ref entity label and image attribute not being translated on the grid
- PIM-10143: Add a check for valid media links with redirects
- PIM-10169: Remove double warning sign when having validation errors on Record edit page
- PIM-10159: Fix table configuration update when a column is deleted and recreated with a different data type
- PIM-10170: Launch product link rules when creating asset by import
- PIM-10166: Prevent Asset Manager thumbnail generation on unsupported mime types
- PIM-10172: Fix creation of "id" option code in table attribute
- PIM-10173: Apply naming convention when importing an asset through flat file
- PIM-10161: Batch query to retrieve existing asset codes
- PIM-10176: Batch query to retrieve existing reference entity records
- PIM-10017: Improve logging in DQI spellcheck
- PIM-10175: Fix option labels display in asset bulk action
- PIM-10180: Fix PDF Asset thumbnail generation
- PIM-10183: Fix reference entity validation
- PIM-10149: Fix group product page OOM (remove group to products association)
- PIM-10186: No thumbnail generation for assets with filenames longer than 100 characters
- PIM-10190: Fix "Label" column shows the code in attribute rules tab
- PIM-10193: Fix incorrect asset in a product import file fail the import job with no warning in the process tracker
- PIM-10194: Fix pagination for list published products endpoint with search_after pagination type
- PIM-10195: Fix Asset Manager thumbnail generation on image/x-eps files
- PIM-10199: Fix occasional segmentation fault when generating thumbnails
- PIM-10203: Fix product variants are not all uncategorized after a mass "Remove from categories" action
- PIM-10211: Flatten image layers during thumbnail and preview transformations in AM
- PIM-10216: Fix PDF renderer in case of empty tables
- PIM-10221: Fix media link input caret jumping when manually typing a link

## Improvements

- PIM-9619: Improve error message when creating a new project with a name already used
- PLG-45: Activate SSO authentication from a command CLI
- RAC-509: Upgrade asset limit by asset family to 10 millions
- PIM-9777: Fix error message when trying to delete an attribute linked to an entity
- CPM-152: Use Symfony Messenger to handle job queue messages. Therefore the `akeneo_batch_job_execution_queue` table is removed.
  Depending on your environment, please check the associated `messenger.yml` to figure out how the messages are sent/received.
  The former command to launch job consumption is removed and replaced by:

```bash
bin/console messenger:consume ui_job import_export_job data_maintenance_job
```

## New features

- DAPI-1443: Add possibility to export products depending on their Quality Score
- RAC-475: Add Assets bulk delete feature
- RAC-479: Add Assets bulk edit feature
- RAC-501: Add Records bulk delete feature

## BC Breaks

- Replace `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` by `Twig\Environment` in all codebase
- Replace `Symfony\Component\Translation\TranslatorInterface` by `Symfony\Contracts\Translation\TranslatorInterface` in all codebase
- Change parameter of `UnknownUserExceptionListener::onKernelException()` method from `Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent` to ` Symfony\Component\HttpKernel\Event\ExceptionEvent`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ProductDraftController` to remove `Symfony\Component\Translation\TranslatorInterface $translator` parameter
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleSelector` to remove `ProductRepositoryInterface $repo`
- Remove class `Akeneo/AssetManager/back/Domain/Event/AssetFamilyAssetsDeletedEvent`
- Update `Akeneo/AssetManager/back/Domain/Repository/AssetIndexerInterface.php` to:
  - remove the `removeByAssetFamilyIdentifier` method
