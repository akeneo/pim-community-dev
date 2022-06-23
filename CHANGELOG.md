# master

## Bug fixes

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
