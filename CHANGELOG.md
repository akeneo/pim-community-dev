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
- PIM-10227: Fix filter not applied properly on the Asset manager grid

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
