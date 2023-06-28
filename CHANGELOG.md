# master

## Bug fixes

- PIM-11041: Fix null has attribute group code broke the pef
- PIM-10966: Fix error when toggling between tabs on an export profile editing page
- PIM-10972: Fix shifting data during import
- PIM-10950: Fix wrongly removed code filter on attribute page
- PIM-11057: Fix ScalarValue::__toString does not cast wrapped boolean value correctly
- PIM-10960: Fix no violation raised when importing variant with already existing siblings
- PIM-10789: Fix password is displayed in SFTP and Amazon S3 form and encrypted password is displayed on history
- PIM-10779: Fix lowercase on get attribute group code for dqi activation
- PIM-10791: Fix product and product model completeness compute on attribute removal
- PIM-10768: Fix update list status_code response
- PIM-10784: Fix API error 500 when filtering for identifier with null value
- PIM-10785: Fix case-insensitive patch product model
- PIM-10808: Fix Error message on the family modification when an attribute is as required=0
- PIM-10802: Fix wysiwyg-field add link event
- PIM-10796: Fix system-information endpoint with wrong answers
- PIM-10806: Allow to delete product's attributes with type 'identifier' and ensure grey cross is displayed
- PIM-10825: Fix transfer to external storage does not give enough information
- PIM-10823: Fix cannot import price and measurement with comma as decimal separator with value not saved as string in import file
- PIM-10843: Rework get association query to avoid group concat max lenght limit
- PIM-10835: Fix command publish-job-to-queue does not work
- PIM-10855: Fix products and models with empty attribute values are counted by CountItemsWithAttributeValueAction
- PIM-10778: Add limit to the number of options to display on the attribute option page
- PIM-10828: Fix search bars don't take into account special characters
- PIM-10858: Fix password is set on sftp storage using private key
- PIM-10844: Filter empty attribute option labels
- PIM-10831: Fix severe performance issues with the association product and product model picker
- PIM-10849: Fix sorting datagrid on completeness when the selected locale is not supported by the channel
- PIM-10853: Fix type checking in SaveFamilyVariantOnFamilyUpdate bulk action
- PIM-10829: Fix case-sensitive locale on translatable business objects
- PIM-10840: Fix attribute update date on attribute options change above 10000 options
- PIM-10793: Add a command to delete expired tokens
- PIM-10840: Fix attribute update date on attribute options change above 10000 options
- PIM-10868: Fix checkboxes on category trees
- PIM-10832: Fix compute completeness job after removing an attribute from a family
- PIM-10820: Partially revert [PIM-10350] to fix case sensitivity on options import
- PIM-10856: Prevent the creation of a useless PHP session when a new token is created in the API
- PIM-10870: Fix display of permissions when empty
- PIM-10860: [SLA] Announcements aren't shown
- PIM-10908: Add zdd migration to add an index on akeneo_file_storage_file_info
- PIM-10745: Fix history display for product's quantified association
- PIM-10874: fix labels api type consistency
- PIM-10877: Fix sequential edit not working if grid is sorted by quality score
- PIM-10923: File import a file with non printable characters returns an error 500
- PIM-10876: Does not save empty ('') labels and don't show null labels on API REST
- PIM-10894: Allow research user by email as username.
- PIM-10888: Disable adding an item to an association of its parent already contains the same item.
- PIM-10925: The search by code is missing on the attribute page
- PIM-10906: Use user timezone to display dates in history grid
- PIM-10915: Fix attribute with numeric code throw 500 error on product history
- PIM-10889: Update Category updated date after setting a labels and show category filtered by updated date on API REST
- PIM-10919: Fix Cleaning Products with removed attributes using identifiers instead of uuids
- PIM-10911: Add user-agent when sending an event
- PIM-10885: Use React shared component for locale selector in product form locale switcher
- PIM-10941: Fix unitary attribute group deletion
- PIM-10916: Fix external categories endpoint with_position always return 1
- PIM-10887: Prevent channel creation on validation error during import
- PIM-10929: Add limit on get product history
- PIM-10940 : Add command to remove orphan categories
- PIM-10936: Fix an issue where completeness could not be saved after migrating to UUIDs
- PIM-10938: Fix getNextObject when use clicks on variant during sequential edit
- PIM-10948: Fix number value comparison
- PIM-10955: Temporary rollback of PIM-10916 causing performance issue on categories API
- PIM-10959: Fix API response when trying to associate a product model to itself in a 2-way association
- PIM-10951: Fix grid search with special characters
- PIM-10916: Fix with_position results on get categories Rest API endpoint
- PIM-10961: Use React component for product grid locale switcher
- PIM-10909: Refactor command to remove non-existing products and models from ES index
- PIM-10932: Fix data in NumberValueFactory if data contains a white space
- PIM-10814: Wysiwyg now supports languages that use right-to-left (rtl) scripts
- PIM-10956: Fix deletion of category with enriched category template
- PIM-10914: Add title and ellipsis for long labels on attribute select
- PIM-10967: Fix inconsistency on DQI completeness recommendation
- PIM-10639: Prevent users to change his password without providing its current password
- PIM-10958: Fix attribute option position after clicking on "done"
- PIM-10976: Fix variant product counter on Product Model Edit Form for variant products without identifier
- PIM-10983: Error HTTP 500 when adding a custom app
- PIM-10980: Fix pagination update when applying filters on product association grid
- PIM-10977 : Prevent api users to log in to the PIM via the UI
- PIM-11001: Fix code filter on attributes grid with special character
- PIM-11002: Fix bad context locale used in DQI dashboard families widget
- PIM-10997: Update error message when trying to delete role with linked users or connections
- PIM-11003: Fix scrolling on Product edit form attributes
- PIM-10982 : Fix flag emoji rendering on windows
- PIM-10931: Add possibility to filter job execution to purge by status and job_instance_code
- PIM-11013 : Fix edit user profile without password changes
- PIM-11012 : Add helper if selected UI language is not sufficiently supported
- PIM-11024 : Add an error message when trying to modify the code of an option attribute [External API]
- PIM-11040 : Sku is disabled according to the rights
- PIM-11023 : Fix product search containing underscores
- PIM-11016 : Improve the clean-removed-attributes command
- PIM-11050 : On "locale" DIV, missing the indication of the country
- PIM-11039: Fix export with duplicated labels
- PIM-10869: Image upload fields now only accept images
- PIM-11063: Fix validation of generated identifiers
- PIM-11066: PIM-11066: Fix Missing Values Adder for scopable + localizable + locale specific attributes
- PIM-11018: Fix view on Connected App permissions if manage or not
- PIM-11075: Fix import attribute option with numeric code

## Improvements

- PIM-10782: Optimize get completeness SQL query

## New features

## BC Breaks
