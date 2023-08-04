# 7.0.x

# 7.0.23 (2023-08-04)

# 7.0.22 (2023-07-11)

# 7.0.21 (2023-07-11)

# 7.0.20 (2023-06-29)

# 7.0.19 (2023-06-27)

## New features

- GRF-872 [Backport GRF-669]: Add 4 field columns (properties) to the user export job profile

# 7.0.18 (2023-06-22)

## Bug fixes

- PIM-11047: Be able to customize local storage root without breaking import/export

# 7.0.17 (2023-05-24)

# 7.0.16 (2023-05-24)

## Bug fixes

- PIM-11000: Fallback on filePath when storage type is undefined

# 7.0.15 (2023-05-22)

# 7.0.14 (2023-05-02)

# 7.0.13 (2023-04-19)

# 7.0.12 (2023-04-18)

# 7.0.11 (2023-03-21)

# 7.0.10 (2023-02-28)

- RAB-1356: [Backport PIM-10840]: Fix attribute update date on attribute options change above 10000 options
- RAB-1356: [Backport PIM-10853]: Fix type checking in SaveFamilyVariantOnFamilyUpdate bulk action
- RAB-1356: [Backport PIM-10849]: Fix sorting datagrid on completeness when the selected locale is not supported by the channel
- RAB-1356: [Backport PIM-10831]: Fix severe performance issues with the association product and product model picker
- RAB-1356: [Backport PIM-10844]: Filter empty attribute option labels
- RAB-1356: [Backport PIM-10778]: Add limit to the number of options to display on the attribute option page
- RAB-1356: [Backport PIM-10835]: Fix command publish-job-to-queue does not work
- RAB-1356: [Backport PIM-10823]: Fix cannot import price and measurement with comma as decimal separator with value not saved as string in import file
- RAB-1356: [Backport PIM-10825]: Fix transfer to external storage does not give enough information
- RAB-1356: [Backport PIM-10858]: Fix password is set on sftp storage using private key

# 7.0.9 (2023-02-20)

# 7.0.8 (2023-02-13)

## Bug fixes

- PIM-1318 [Backport PIM-10789]: Fix password is displayed in SFTP and Amazon S3 form and encrypted password is displayed on history
- PIM-1318 [Backport PIM-10791]: Fix product and product model completeness compute on attribute removal
- PIM-1318 [Backport PIM-10768]: Fix update list status_code response
- PIM-1318 [Backport PIM-10779]: Fix lowercase on get attribute group code for dqi activation
- PIM-1318 [Backport PIM-10784]: Fix API error 500 when filtering for identifier with null value
- PIM-1318 [Backport PIM-10785]: Fix case-insensitive patch product model
- PIM-1318 [Backport PIM-10808]: Fix Error message on the family modification when an attribute is as required=0
- PIM-1318 [Backport PIM-10802]: Fix wysiwyg-field add link event
- PIM-10997: Update error message when trying to delete role with linked users or connections

# 7.0.7 (2023-02-13)

# 7.0.6 (2023-02-07)

# 7.0.5 (2023-02-07)

# 7.0.4 (2023-02-06)

# 7.0.3 (2023-02-03)

# 7.0.2 (2023-01-13)

# 7.0.1 (2023-01-12)

# 7.0.0 (2023-01-05)

## Bug fixes

- PIM-10607: Only request /announcements when the panel is open
- PIM-10515: Fix 'add associations' button visibility for quantified associations & category permissions
- PIM-10487: Fix import of very tiny measurement values (e.g. 0.000075 GRAM)
- PIM-10215: Fixed last operation widget job type translation key
- PIM-10233: Fix the saved value by an empty wysiwyg
- PIM-10232: Fix "A new entity is found through the relationship" errors in jobs
- PIM-10240: Fix error 500 on the API when inputting data:null for an existing price
- PIM-10241: Fix user account disabled can connect regression
- PIM-10264: Optimize variant product ratio query
- PIM-10248: Fix NOT BETWEEN filter does not work on products and product models (created and updated property)
- PIM-10259: Fix Arabic text being reversed in product PDF exports
- PIM-10277: Do not allow disabled user to login
- PIM-10292: Fix error 500 when role page contain a validation errors
- PIM-10268: SKU filter is always shown in the product grid
- PIM-10295: Fixed product categories disappearing on adjacent tab locale switch
- PIM-10330: Fix wrong error message while importing boolean attribute value
- PIM-10331: Fix error when using an association with quantities having an numeric code
- PIM-10346: Fix spellcheck badge not displayed on attribute options
- PIM-10336: Fix product Export edition in error if no locale selected
- PIM-10345: Fix issue when importing product model with an attribute constituted of only digits
- PIM-10334: Fix error on the clean-removed-attributes
- PIM-10350: Updating a product with an attribute option code in a simple or a multi select and a different code case than the original one is well handled.
- PIM-10362: Fix attribute type "number" gets modified in history when import with same value
- PIM-10372: Fix letter case issue when importing channels
- PIM-10396: Fix DQI "Enrichment" suggestion does not take into account Table attribute
- PIM-10389: Export channel currencies for a non-scopable price attribute instead of all enabled currencies
- PIM-10398: Fix category validator to prevent break-lines
- PIM-10409: Allow creating a measurement value with case insensitive unit code
- PIM-10411: Fix non numeric metric value in imports
- PIM-10413: Patch connections routes order
- PIM-10377: Change Elastic Search field Limit for everyone
- PIM-10251: Fix locale on API call
- PIM-10421: Add missing translation key for delete button
- PIM-10418: Simple and multi select values not showing if not imported with the correct letter case
- PIM-10426: Fix empty array should be normalized as empty JSON object in Measurement Family API
- PIM-10420: Handle status resolving when job crashes due to external issue (mysql crashes for example)
- PIM-10416: Fix letter case issue when importing families
- PIM-10427: Fix display of boolean value in variant axis
- PIM-10435: Fix search_after requests with codes using uppercase accented characters
- PIM-10443: Search for system product grid filters in System > Users > Additional is now case insensitive
- PIM-10459: Fix product grid selection
- PIM-10447: Do not hydrate product/model in UniqueEntityValidator
- PIM-10467: Fix create and delete quickly product models via API create indexation issue
- PIM-10471: Do not generate 2 files when making a quick export of 1 type of products
- PIM-10475: Fix option existence validation for numeric option codes
- PIM-10483: Fix slow loading products when filtering by variants
- PIM-10484: Fix job filter on status being incoherent with job interrupted by demon crash
- PIM-10485: Fix Wrong category tree is displayed in channel settings if user has no right on the linked category tree
- PIM-10495: Fix product datagrid by increasing sort_buffer_size
- PIM-10499: Fix MySQL's out of sort memory errors on variant product and product model edit form
- PIM-10500: Fix API not returning quantified associations for products when association type code is numeric
- PIM-10503: Fix Wrong regex on channel deletion
- PIM-10514: Fix associations normalization for published products
- PIM-10516: Fix remove completeness job when deactivating and reactivating a locale
- PIM-10508: Fix attribute creation when label contains an '&' character
- PIM-10501: Fix identifier validation for product and product model imports to disallow line breaks
- PIM-10527: Fix associated groups grid
- PIM-10542: Fix int attribute code breaks ValueUserIntentFactoryRegistry
- PIM-10528: Fix escaped special characters in page titles
- PIM-10541: Fix SetTableValue userIntent to allow null data in enrichment Service Api
- PIM-10543: Fix selected categories sent to listCategories
- PIM-10561: Fix associationUserIntentFactory to cast int to string
- PIM-10557: Fix notifications not displayed for obsolete route parameters
- PIM-10530: Fix case issue when querying products with attribute options
- PIM-10572: Fix product publishing when associated to a published product with a 2-way association
- PIM-10569: Fix associate bulk action screen for quantified associations
- PIM-10574: Fix link to product page in quantified association row
- PIM-10548: Fix yaml reader does not display an error message when imported file does not contain the root level
- PIM-10571: Fix infinite scroll of attribute group selector in family edit form
- PIM-10584: Fix conversion for Volume Flow measurement units
- PIM-10581: Fix attribute option code in linked data returned as an integer instead of a string
- PIM-10529: Fix links on product grid
- PIM-10598: Fix "Cleaning removed attribute values" job failing if attribute is deleted during mass deletion of products
- PIM-10595: Fix not being able to add record with code "0" on a product
- PIM-10588: Add potentially missing `remove_completeness_for_channel_and_locale` job instance
- PIM-10620: Fix export product options values with label to be case insensitive with codes
- PIM-10606: Fix computeFamilyVariantStructureChange on attribute removal
- PIM-10624: Fix very slow query when counting variants for mass delete
- PIM-10648: Migrate all job conf which contains old user_to_notify param
- PIM-10566: Fix wrong namespace for categories in resource_name column in pim_versioning_version table
- PIM-10568: Fix error when running Version_7_0_20220629142647_dqi_update_pk_on_product_score during On-Premise/Flex to Serenity migration
- PIM-10646: Fix export with label from a select attribute containing uppercase in its code exports code and not labels
- PIM-10622: Fix save options labels when using the automatic correction in Firefox
- PIM-10634: Fix media filter values normalizer to be case insensitive
- PIM-10649: Fix wrong attributes displayed when selecting attribute group in export profile filter when there is too many attributes selected
- PIM-10658: Fix database product reader
- PIM-10576: Fix product model mass edit acl check
- PIM-10633: Fix no DQI dashboard average rankings if code case changed
- PIM-10667: Fix product import when measurement contains line break
- PIM-10714: Fix family codes are not well-saved in export filters
- PIM-10669: Fix the attribute list does not update if we don't scroll
- PIM-10655: Fix format of empty completeness in API
- PIM-10644: Fix identifier format check on multiple product update
- PIM-10673: Fix media URL port display for Events API
- PIM-10686: Fix percentage of inaccurate completeness in the activity dashboard
- PIM-10659: Fix associated products in grid are now sorted using their uuids
- PIM-10718: Fix categories with empty labels throw 500 error
- PIM-10725: Fix get family variant case sensitive
- PIM-10720: Fix price versioning normalizer to round numbers
- PIM-10751: Avoid error 500 and print a violation when user try to save measurement value with space
- PIM-10724: Fix textarea template so that first break line is not considered as break in html
- PIM-10716: Fix uuids in quantified association revert version
- PIM-10734: Fix failing product export profiles with "[object Object]" family filter since last weekly upgrade
- PIM-10730: Fix mass actions for quantified associations rendering
- PIM:10739: Fix find families controller access.
- PIM:10741: Fix diff indexation of product models
- PIM:10743: Fix HTTP 500 on measurement PATCH without unit
- PIM-10753: Fix HTTP 500 in the API when patching product metric with a mathematical notation
- PIM:10744: Fix product import with a quantified association column is missing
- PIM-10750: Fix category code validation to allow '0'

## Improvements

- PIM-10293: add batch-size option to pim:completness:calculate command
- PIM-10229: Enforce strict samesite policy for session cookies
- Improvement: Use Debian Bullseye (v11) in Dockerfiles for akeneo/pim-php-dev:master
- BH-1159: Refactor BatchCommand to use execution ID without batch code
- BH-1159: Add tenant ID for batch processing
- BH-1159: Use available JobMessage class for denormalization

## New features

## BC Breaks

- BH-1159: Add `JobInterface::getJobRepository` method
