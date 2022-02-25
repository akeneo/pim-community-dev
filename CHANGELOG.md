# master

## Bug fixes

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

## Improvements

- PIM-10229: Enforce lax same-site policy for session cookies
- PIM-10235: Add HTTP Headers

## New features

## BC Breaks
