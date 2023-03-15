# master

## Bug fixes

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
- PIM-10745: Fix history display for product's quantified association
- PIM-10874: fix labels api type consistency
- PIM-10877: Fix sequential edit not working if grid is sorted by quality score
- PIM-10876: Does not save empty ('') labels and don't show null labels on API REST
# deploy tests
## Improvements

- PIM-10782: Optimize get completeness SQL query

## New features

## BC Breaks
