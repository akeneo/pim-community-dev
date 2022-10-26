# master

## Bug fixes

- PIM-10524: Removed AuthnContextClassRef from SAML Auth requests
- PIM-10431: SAML endpoint /saml/acs now returns HTTP status 405 when called using method GET (instead of 500)
- PIM-10498: Fix Reference Entity Multiple link to handle duplication properly on import
- PIM-10492: Fix Tailored Export filter on "identifier" when exporting Product Models
- PIM-10227: Fix filter not applied properly on the Asset manager grid
- PIM-10215: Fixed missing translations label in process tracker for mass edit on assets and records jobs
- PIM-10237: Order connector Assets with PHP to avoid memory issues
- PIM-10228: Fix Asset transformations job crashing when having cache resources exhausted
- PIM-10236: Fix duplicated mask returned by query for table attribute
- PIM-10238: Add permissions to view mass delete assets, mass edit assets and mass delete records job logs
- PIM-10249: Fix error message when importing YML rule containing a reference entity record with numeric value
- PIM-10255: Fix LocaleCodeContext initialization place for table condition line inside rules
- PIM-10269: Fix regression in asset-selector component
- PIM-10256: Fix MainImage as asset using React Component
- PIM-10259: Fix Arabic text being reversed in product PDF exports
- PIM-10284: Clean product values after reference entity removal
- PIM-10277: Reject disabled user when coming from SAML auth
- PIM-10285: Fix concurrent bulk publish issue
- PIM-10268: SKU filter is always shown in the product grid
- PIM-10304: Fix blank screen caused by AM components
- PIM-10306: Batch thumbnail http calls to avoid parralel generations of thumbails at the same time in AM
- PIM-10291: Fix table attribute edit blocked when locale recently deleted from channel
- PIM-10338: Fix the number of options to improve is not correctly refreshed
- PIM-10346: Fix spellcheck badge not displayed on attribute options
- PIM-10340: Fix uncaught exception when creating the same asset twice in parrallel
- PIM-10359: Fix missing locale parameter for TWA projects when redirecting from dashboard to product-grid
- PIM-10363: Fix add association rule updater
- PIM-10341: Fix unable to delete reference data multi select attribute
- PIM-10351: Improve error message for ancestor categories validation in process tracker
- PIM-10347: Use the Vimeo oEmbed API to generate thumbnail
- PIM-10380: Fix cannot add words with accent to the dictionary when words without are already there
- PIM-10367: Fix table attribute condition line handleChange values
- PIM-10381: allow words with dot in the dictionary
- PIM-10391: Fix mass records deletion launching n+1 "remove_non_existing_product_values" jobs
- PIM-10389: Export channel currencies for a non-scopable price attribute instead of all enabled currencies
- PIM-10394: Fix dictionary lookup to properly match uppercase
- PIM-10399: Fix media link internal value on initialValue change
- PIM-10402: Allow clearing a table measurement cell from the PEF
- PIM-10377: Change Elastic Search field Limit for everyone
- PIM-10384: Fix cannot build EE standard due to "can't resolve '@akeneo-pim-enterprise/onboarder'" error
- PIM-10417: Increase ImageMagick disk resource
- PIM-10429: Fix mismatch between Reference-entity records and their link in the PEF
- PIM-10408: Fix bad product version when importing a product table value with wrong option case
- PIM-10435: Fix search_after requests with codes using uppercase accented characters
- PIM-10440: Fix error adding records into a table attribute if the reference entity had uppercase characters in the code
- PIM-10447: Fix memory leak during compute_family_variant_structure_changes job
- PIM-10444: Fix thumbnail generation http queue and cancel request when leaving the page
- PIM-10463: Fix textarea stringifier for rich textarea on source and target
- PIM-10473: Fix record codes filter by comparing lowercase
- GRF-63: Fix calculation on TWA projects with completeness filter
- PIM-10479: Fix Rule engine concatenate action does not handle properly case-sensitivity for option codes
- PIM-10493: Fix Rule engine datagrid sorting
- PIM-10507: Fix product export with labels when a table attribute contains a measurement column
- PIM-10510: "Automatic link between assets and products" log download returns a 403
- PIM-10521: Fix product link on asset and reference entity pages
- PIM-10534: Fix identifier comparison is case-sensitive in RE front
- PIM-10511: Fix distant server taking to long to respond when reaching media link resources 
- PIM-10535: Fix 500 error in published product API when reaching the limit of offset pagination type
- PIM-10547: Fix issue with duplicated linked records in a product
- PIM-10573: Fix tailored export does not handle write into multiple file when batch size is a multiple of linesPerFile
- PIM-10546: Fix DQI is not calculated on product models with table attribute all rows required for completeness
- PIM-10548: Fix rule engine does not display an error message when imported file does not contain the root level
- PIM-10579: Fix Reference entity and asset normalizers and factory to handle disordered indexed arrays
- PIM-10605: Fix unsafe usage of empty() on strings in AM & RE
- PIM-10578: Fix the search on system filters in the rule engine UI
- PIM-10616: Fix asset import not handling properly duplicated code
- PIM-10592: Fix DQI score showing "In Progress" - data_quality_insights_evaluations returns an error
- PIM-10619: Avoiding checks on expiration dates of certificates when disabling SSO
- PIM-10621: Fix API returning an empty list instead of an empty object for published products
- PIM-10613: Fix no purge done on the DQI when deleting option for multi-select and simple-select attributes
- PIM-10627: Fix Job executions permissions for 'mass_edit','quick_export','mass_delete'
- PIM-10656: Fix asset collection sorting in PEF is not case-insensitive
- PIM-10658: Fix TE database product reader
- PIM-10645: Fix tailored imports removes the prefix zeros on data
- PIM-10665: Fix error when updating product models through API with non existing attributes as numbers
- PIM-10650: Fix white page when editing asset in a new tab
- PIM-10678: Prevent concurrent thumbnail generation of big asset images to exhaust server resources
- PIM-10628: Prevent importing values of readonly attributes in product draft import

## Improvements

- PIM-10229: Enforce lax same-site policy for session cookies
- PIM-10235: Add HTTP Headers

## New features

## BC Breaks
