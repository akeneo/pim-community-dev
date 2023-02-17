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
- PIM-10778: Add limit to the number of options to display on the attribute option page
- PIM-10828: Fix search bars don't take into account special characters
- PIM-10844: Filter empty attribute option labels

## Improvements

- PIM-10782: Optimize get completeness SQL query

## New features

## BC Breaks
