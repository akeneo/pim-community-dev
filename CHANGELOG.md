# 1.0.0-rc-1 -

## Features
- Created the completenesses over channels and locales widget

## Improvements
- Add some missing translations

## Bug fixes

## BC breaks
- Change some translation message keys

# 1.0.0-beta-4 - "The Abominable Snow Rabbit" (2014-01-08)

## Features
- Import product associations (CSV)
- New translation mode : Compare and copy values within a product edit form
- Convert metric values into the conversion unit selected for the channel during export
- Allow filtering and sorting by metric values
- Allow to go back to the grid or create another product after saving one
- Add products to many groups through mass edit wizard
- Attribute options fixture
- Product associations fixture
- Fixtures can be in CSV (all fixtures except users and currencies)
- Fixture files can be imported through a command (all fixtures except users and currencies)
- Add quick create popin for jobs
- Add a WYSIWYG editor for TextArea attributes

## Improvements
- Improve the user experience for family management
- Update import / export detail view by adding a summary
- Improve installer to provide different data set (minimal or dev)
- Use a form extension to apply select2 only on specified fields
- Add real time versioning option in product import
- Merge the configuration of import/export job steps in the first tab of the edit view
- Implement save of base unit and data for metric entity
- Metric values are now exported in two distinct columns (value and unit)
- Metric values can now be imported through two distinct columns ([examples](https://github.com/akeneo/pim-community-dev/blob/42371c0d6c70801a4a23a7aa8cf87e18f417c4a8/features/import/import_products.feature#L170-L198))
- Ajaxify the completeness tab of product edit form
- Change the channel switcher and collapse/expand modes on product edit view
- Add a loading mask when loading quick creation form
- Allow to switch configuration between ORM and ODM
- Update OroPlatform from beta-1 to beta-5
- Move Batch Form Types to ImportExport bundle and refactor them to be able to configure any kind of job
- Don't display several UI elements when users don't have the corresponding rights
- Use aliases for subforms, no more manual instanciation to enhance extensibility
- Product prices can now be imported with a single column per currency

## Bug fixes
- Missing pending versionable entities
- Product edit form fails with memory limit for products contained in large groups
- When I delete a filter price or metric and add it again, the filter is not applied
- Translate metric units in select field
- Values of attributes with the type Number are displayed with .0000 on product edit
- Reduce metric field width
- Sort by metric value in product datagrid
- Constraint of unicity for products of a variant group
- When reimporting a product, history for this product shows Create instead of Update
- The completness calculation takes a lot of time after importing in IcecatDemo
- Apply select2 only on needed fields
- Inverse unit and data position for metric form field
- Unwanted popin when try to leave attribute edit view
- Display bug on channel selector with long labels
- Versioning is not called after import
- I can select a root of a tree in the mass-edit wizard
- Products with no completeness do not show in the grid when selecting All products
- Exporting products with an empty file attribute value fails
- The count of Write when I export products is wrong
- Attributes are created even with minimal install
- Error on disallowed decimal on price are not displayed at the right place
- Initial state of completeness filter is wrong
- Search should take account of ACLs
- Oro mapping issue with search item on beta-1
- Locale selector in the product header is sometimes too short
- Allow to remove a translation setting it to empty
- Completeness doesn't take into account currencies of channels

## BC breaks
- Change AbstractAttribute getters that return a boolean value to use the 'is' prefix instead of 'get'. The affected getters are 'getScopable', 'getTranslatable', 'getRequired', 'getUnique'.
- Product, ProductValue, Media and ProductPrice have switched from Pim\Bundle\CatalogBundle\Entity namespace to the Pim\Bundle\CatalogBundle\Model namespace, to pave the way for the MongoDB implementation
- AbstractEntityFlexible getValue method now returns null in place of false when there is now value related to attribute + locale + scope
- Completeness and Product are not linked any more via a Doctrine relationship. We are cutting the links between Product and other entities in order to pave the way to the ability to switch between MongoDB and ORM while using the same API (apart from Product repository).
- Same thing than above for Category
- Relation between Family and Product has been removed from Family side
- Remove PimDataAuditBundle
- Remove PimDemoBundle
- Move product metric in catalog bundle
- Change jobs.yml to batch_jobs.yml and change expected format to add services and parameters
- Rename getStorageManager in flexible manager and change related references
- Rename AttributeTypeManager to AttributeManager and change related references, move createAttribute, createAttributeOption, createAttributeOptionValue from ProductManager to AttributeManager
- Introduce AttributeManagerInterface and remove references to concrete class
- Change attribute type configuration, refactor the attribute type compiler pass and attribute type factory
- Remove getAttributeOptionValueRepository, getFlexibleValueRepository from FlexibleManager
- Attribute fixtures format has changed
- Product associations import/export format has changed.
- Rename Association to AssociationType and all properties/methods linked to this class.
- Rename ProductAssociation to Association
- Rename ProductAttribute to Attribute

# 1.0.0-beta-3 - "Hare Conditioned" (2013-12-04)

## Features
- History of changes for groups and variant groups
- History of changes for import / export profiles
- History of changes for channels
- Allow creating new options for simple select and multiselect attributes directly from the product edit form
- Add a default tree per user
- Introduce command "pim:completeness:calculate" size argument to manage number of completenesses to calculate
- Switching tree to see sub-categories products count and allow filtering on it
- Group types management
- Import/Export product groups (CSV)
- Import/Export associations (CSV)
- Export product associations (CSV)
- Import/Export attributes (CSV)
- Import/Export attribute options (CSV)
- Upload and import an archive (CSV and medias)
- Download an archive containing the exported products along with media
- Add the column "enabled" in the CSV file for products import/export and for versioning

## Improvements
- Export media into separated sub directories
- Separate product groups and variants management
- Display number of created/updated products during import
- Speed up completeness calculation
- Display the "has product" filter by default in the product grid of group edit view
- Display currency label in currencies datagrid
- Disable changing the code of all configuration-related entities
- Merge the directory and filename of export profiles into a single file path property

## Bug fixes
- Mass delete products
- Fix some issues with import ACL translations (issues#484)
- Add a message when trying to delete an attribute used by one or more variant groups instead of throwing an error
- Selection of products in mass edit
- Versioning of installed entities (from demo bundle)
- For csv export of products, only export values related to selected channel and related locales
- Fix locale activation/deactivation based on locales used by channels
- Fix issue with 100 products csv import

## BC breaks
- Command "pim:product:completeness-calculator" has been replaced into "pim:completeness:calculate"
- Refactor in ImportExport bundle for Readers, Writers and Processors

# 1.0.0-beta-2 - "Hold the Lion, Please" (2013-10-29)

## Features
- Manage variant groups
- CRUD actions on groups
- Manage association between groups and products
- CRUD actions on association entities
- Link products with associations
- Import medias from a CSV file containing name of files
- Export medias from a CSV file
- Apply rights on locales for users
- Do mass classification of products
- Define price attribute type with localizable property

## Improvements
- Upgrade to BAP Beta 1
- Homogenize title/label/name entity properties using label
- Mass actions respects ACL
- Improve Import/Export profile view
- Hide access to shortcut to everyone
- Number, date and datetime attributes can be defined as unique values
- Use server timezone instead of UTC timezone for datagrids
- Make upload widget work on FireFox
- Display skipped data errors on job report

## Bug fixes
- Fix sorting channels by categories
- Bug #324 : Translate group label, attribute label and values on locale switching
- Number of products in categories are not updated after deleting products
- Fix dashboard link to create import/export profile
- Fix price format different between import and enrich
- Fix channel datagrid result count
- Fix end date which is updated for all jobs

