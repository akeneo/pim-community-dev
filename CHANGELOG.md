# master

## Bug fixes

- PIM-10950: Fix wrongly removed code filter on attribute page
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
- PIM-10936: Fix an issue where completeness could not be saved after migrating to UUIDs
- PIM-10938: Fix getNextObject when use clicks on variant during sequential edit
- PIM-10948: Fix number value comparison
- PIM-10955: Temporary rollback of PIM-10916 causing performance issue on categories API
- PIM-10959: Fix API response when trying to associate a product model to itself in a 2-way association
- PIM-10951: Fix grid search with special characters
- PIM-10916: Fix with_position results on get categories Rest API endpoint
- PIM-10961: Use React component for product grid locale switcher
- PIM-10932: Fix data in NumberValueFactory if data contains a white space
- PIM-10814: Wysiwyg now supports languages that use right-to-left (rtl) scripts

## Improvements

- PIM-10782: Optimize get completeness SQL query

## New features

## BC Breaks
