# 1.0.2
## Bug Fixes
- Removed hardcoded attribute table from ORM/CompletenessGenerator.php
- Fixed of ProductValue's attributes' exclusion on completeness's computation

# 1.0.1
## Bug Fixes
- Removed hardcoded Attribute from ChainedAttributeConstraintGuesser
- Removed hardcoded Attribute from ValidMetricValidator

# 1.0.0 - "Hare We Go" (2014-03-06)

## Features
- Uservoice integration
- Add a last operations widget on the dashboard
- Add new user as fixtures
- Auto-refresh job execution report page
- Add a checkbox to select all visible rows in entity association grids
- Add colors to channels and use them in scopable field labels to make them more compact

## Improvements
- Load choices for grid filters asynchronously
- Allow adding/removing attributes to mass edit attributes view without a page reload
- Propagate -v option to subcommands of install command
- Fix the versions of dependencies in composer.json
- Undisplay unwanted searchable elements in quick search
- Add icons for category and product in search view
- Prevent hydrating all attributes in the available attributes addition form
- Prevent hydrating all families in the product edition form
- Import conversion units for channels
- Product grid loading performance by hydrating as array and introduce selector extension
- Add a screen to select the attribute type before creating an attribute
- Create check-requirements, assets and database/fixtures commands and simplify install one
- Make documentation tested linking it to our behat scenarios

## Bug fixes
- Fixed non-updated values being displayed in the the audit history
- Fixed attribute group form state not being saved
- Do not display Id as an eligible attribute as label
- Fixed select field missing for scopable simple/multi select attributes in the product form
- Restored missing attributes translation
- Fixed the display of scopable metric attributes in the product edit form
- Fixed regression with resolve target entity on Category entity
- Fixed datepicker in date attribute form
- Fixed unwanted fields appearing in attribute creation form if server-side validation errors are present
- Fixed 500 response when submitting import launch form without selecting file to upload
- Fixed $field_catalogLocale linked bugs
- Fixed scopable value order in product form
- Restored unique variant axis constraint when saving product
- Fixed missing breadcrumbs for edit views
- Fixed lost hashnav when creating an attribute group
- Fixed a bug that prevented saving unchecked checkbox value in product edit form
- Fixed recovered attributes on mass edit action
- Fixed a bug with tooltips sometimes not appearing due to a conflict between bootstrap and jquery tooltip plugins

## BC breaks
- Remove the date type property of Attribute and simplify the pim_catalog_date attribute type to support date only (not date/datetime/time)
- Remove unnecessary AttributeManagerInterface and AttributeInterface in favor of AbstractAttribute
- Rename findByWithSortedAttribute to findOneByWithValues, add pre-select attributes and related translations to reduce number of lazy loaded queries when edit a product
- Rename findByWithAttributes to findAllByAttributes
- MeasureBundle has been moved from the BAP to an external repository (akeneo/measure-bundle).
- BatchBundle has been moved from the BAP to an external repository (akeneo/batch-bundle).
- Remove magic setter access to value (ex: $product->setDescription()), as it has multiple conceptual and
technical flaws (attribute codes are data, not a freeze structure, needed to maintain an full attribute cache in product
that made the entity too smart for its own good and created performances problem)
- Remove Product::createValue(). Can be replaced by calling ProductManager::createFlexibleValue() and setting attribute, scope and locale on the created value.
- Product datagrid, hydrate rows as arrays (in place of objects) to reduce the loading time
- Datagrid configuration, remove [flexible_entity] config to avoid to define the used entity twice
- Rename and move src/Pim/Bundle/EnrichBundle/Resources/views/Completeness/_datagridCompleteness.html.twig => DataGridBundle/Resources/views/Property/completeness.html.twig
- Delete classes ConfigureAssociationProductGridListener and AssociationProductColumnsConfigurator, we now use ConfigureFlexibleGridListener to configure product association grid
- Delete the HideColumnsListener, the ColumnConfigurator is now able to add only columns configured by the user
- Rename CompletenessFilter to ProductCompletenessFilter to be consistent, move also the related js file
- Changed signature of ProductRepository::getEligibleProductIds()
- Changed signature of GroupType::__construct()
- Changed signature of AssociationType::__construct()
- Removed AttributeRepository::findallWithGroups()
- Rename grid_extensions.yml, grid_actions.yml, grid_listeners.yml, grid_attribute_types.yml to extensions.yml, actions.yml, event_listeners.yml, attribute_types.yml

# 1.0.0-rc-1 - "Tortoise Beats Hare" (2014-02-06)

## Features
- Completenesses over channels and locales widget
- New command to install the PIM
- Price attributes can be scopable
- Popin to configure product datagrid columns

## Improvements
- Add missing translations
- New grid implementation
- Grids performances
- Quick export of selected products in the grid
- Status column in the product grid
- Thumbnail in product grid for attribute of type image

## Bug fixes
- Bug #658: Export all activated translations even if no value has been set
- Bug PIM-1892: Prevented the form subscriber to remove form fields if not valid
- Downgrade ICU lib to be compatible with RedHat 6 and CentOS 6
- Fix an issue with excessive url length when mass editing many products
- Products grid loaded twice the first time the screen is displayed
- The first tree is not displayed in the mass edit wizard
- When no group type exist, it's not possible to add Variant Group
- Job validation is applied twice (create import/export)
- Validation messages not visible in job creation popin (create import/export)
- Lose hashnav when I create a tree
- Fix completeness calculation on icecat demo dev data
- Application crash on some product validation fail
- In create product popin, no way to search for family (in select 2 field)
- Attribute export in csv shift columns instead of putting blank values
- Error with field_catalogLocale on first load
- Missing translations in page titles
- When adding a new option from product form, the new option is not in the select
- Category edit and page title not updated

## BC breaks
- Change some translation message keys
- Remove GridBundle, add a new DataGridBundle (based on OroPlatform changes)
- Change filters implementations in FilterBundle
- Update all PIM grids to use the new implementation (extensions for filter, sorter, pager, custom datasource, custom cell formatters)
- Rename TranslatableInterface and TranslatableListener by Localizable one in FlexibleEntityBundle
- Rename translatable attribute property by localizable
- FlexibleQueryBuilder has been rewritten to prepare the MongoDB support (add filters and sorters in FlexibleEntityBundle/Doctrine/ORM)
- FlexibleQueryBuilder is injected to ProductRepository
- ProductRepository is injected in ProductManager
- Remove deprecated flexible entity config which is now builded by flexible manager itself (use doctrine meta)
- Move controllers, forms, routing and views from CatalogBundle to EnrichBundle (rename routes, forms, acls, services)
- Introduce a BaseConnectorBundle and move readers, processors, writers, archivers and related configuration from ImportExportBundle
- Introduce a TransformBundle and move cache, converters, encoders, normalizers, transformers and related configuration from ImportExportBundle
- Renaming of services of ImportExport that have been moved (pim_transform_* and pim_base_connector_*)
- Move functionality related to user preferences from LocaleManager and ChannelManager to a dedicated UserContext
- Remove AbstractFlexibleValue::isMatching() method

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
