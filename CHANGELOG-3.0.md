# 3.0.x

# 3.0.84 (2020-08-19)

# 3.0.83 (2020-08-04)

## Improvements

- PIM-9383: Fix underscore JS dependency

# 3.0.82 (2020-07-23)

## Bug fixes

- PIM-9282: Make calling attribute options via API case insensitive

# 3.0.81 (2020-07-22)

# 3.0.80 (2020-07-06)

## Bug fixes

- GITHUB-APD-124: Update help menu link to redirect to the right version on the help center

# 3.0.79 (2020-05-27)

# 3.0.78 (2020-05-13)

# 3.0.77 (2020-05-12)

## Bug fixes

- PIM-9242: Fix API product model list when filtering with SINCE LAST N DAYS operator 

# 3.0.76 (2020-04-27)

# 3.0.75 (2020-04-21)

# 3.0.74 (2020-04-17)

# 3.0.73 (2020-04-16)

## Bug fixes

- PIM-9179: Fix product grid freezing when using ENTER on filter
- PIM-9193: DatePresenter presents null if date can not be formatted

# 3.0.72 (2020-04-03)

- PIM-9169: Fix error message when deleting user that created a project

# 3.0.71 (2020-03-20)

## Enhancements

- PIM-9090: Improve performance of the variant navigation dropdown in the product model edit form

# 3.0.70 (2020-03-17)

## Bug fixes

- PIM-9140: Translate error message from backend in dialogs
- PIM-9146: Fix product grid choice-filter data fetching

# 3.0.69 (2020-03-11)

## Bug fixes

- PIM-9135: Currency is not set by default on price filter on datagrid
- PIM-9137: do not export headers if no data to export and attribute used as filters

# 3.0.68 (2020-02-19)

# 3.0.67 (2020-02-10)

## Enhancements

- PIM-8952: Freeze the first line and column of the attribute options tab

# 3.0.66 (2020-02-06)

# 3.0.65 (2020-02-03)

## Bug fixes

- PIM-9065: Allow more than 4 decimals for an attribute of type "number"

# 3.0.64 (2020-01-21)

## Bug fixes

- PIM-8163: Display messages when toggling curencies
- PIM-6160: Fix update of 'unique' property in attribute updater when updating both unique and type properties
- PIM-9061: Fix filters with numeric attribute codes for bulk actions

# 3.0.63 (2020-01-10)

## Bug fixes

- PIM-9051: Fix cropping on assets preview

# 3.0.62 (2020-01-03)

## Bug fixes

- PIM-9039: Delete export/import files with the job batch:purge-job-execution
- PIM-9035: Fix search in the textarea for the very long texts

# 3.0.61 (2019-12-19)

## Bug fixes

- PIM-9030: Fix date formatting for non UTC values
- PIM-9041: Fix performance issue on "Add to existing product model" mass action

# 3.0.60 (2019-12-13)

## Bug fixes

- PIM-9011: Fix display issue on read-only categories
- PIM-9023: Add login details on user profile page

# 3.0.59 (2019-12-09)

# 3.0.58 (2019-12-05)

## Bug fixes

- PIM-9007: Fix cancelling category selection in export profile

# 3.0.57 (2019-12-02)

## Enhancements
- PIM-9002: Allow command `pim:product:clean-removed-attributes` to work with --no-interaction parameter

# 3.0.56 (2019-11-27)

## Bug fixes

- PIM-8990: Create only the attributes requirements of the identifier attribute when a channel is created
- PIM-8997: Fix incorrect empty value stored for wysiwyg editor

# 3.0.55 (2019-11-22)

## Bug fixes

- PIM-8992: Do not allow to import attribute code with line-feed characters
- PIM-8991: Forbid user without "list users" permission to access other user data

# 3.0.54 (2019-11-18)

## Bug fixes

- PIM-8985: Fix label translation on family product selection

# 3.0.53 (2019-11-12)

# 3.0.52 (2019-11-08)

# 3.0.51 (2019-10-30)

## Bug fixes

- PIM-8924: Fix permission of sorting attribute groups
- PIM-8927: fix label translation on product model creation page
- PIM-8928: Fix permission for sorting attributes inside an attribute group

# 3.0.50 (2019-10-28)

# 3.0.49 (2019-10-24)

## Bug fixes

- GITHUB-10955: Remove database prefix in queries
- PIM-8903: Fix export job instances filters when a family is deleted

# 3.0.48 (2019-10-23)

## Bug fixes

AOB-691: Allow to show only specific operators in datagrid
AOB-691: Allow only default filters without calling a dynamic attributes query in datagrid

# 3.0.47 (2019-10-21)

## Bug fixes

- PIM-8586: Fix association type order in association tab
- PIM-8892: Fix product bulk actions on categories

# 3.0.46 (2019-10-16)

## Bug fixes

- PIM-8369: Remove flex from families dropdown css
- PIM-8585: fix incorrect displayed order in attributes groups

# 3.0.45 (2019-10-04)

## Bug fixes

- PIM-8331: Fix display of category tree in the product grid when user does not have access to its default tree
- PIM-8852: Locale specific and localized metric attribute is well displayed in product edit form.
- PIM-8865: Forbid category codes (add case insensitive option)

# 3.0.44 (2019-10-02)

# 3.0.43 (2019-09-24)

## Bug fixes

- PIM-8777: Forbid space in username at creation

# 3.0.42 (2019-09-13)

## Bug fixes

- PIM-8754: Fix completeness for locale specific attribute
- PIM-8756: Fix variant axis label normalization

# 3.0.41 (2019-09-05)

## Bug fixes

- PIM-8719: Update Mink Selenium driver
- PIM-8720: Revert product grid loading twice (PIM-6978)

# 3.0.40 (2019-09-02)

## Bug fixes

- PIM-8978: Fix the double loading of the product grid after login
- PIM-8710: New converter to flatten invalid import values
- PIM-8713: Fix category tree selector

# 3.0.39 (2019-08-28)

## Bug fixes

- PIM-8615: Fix issue with boolean attribute used as variant axis
- PIM-8678: Fill the updated property on Family entity update

## BC Breaks
- Change `Akeneo\Pim\Structure\Component\Mode\FamilyInterface` to extend `Akeneo\Tool\Component\Versioning\Model\TimestampableInterface`

# 3.0.38 (2019-08-20)

## Bug fixes

- PIM-8357: Fix styling of actions on history grid
- PIM-8438: Add an explicit error message when an attribute label is too long
- PIM-8629: Fix hiding of upload button on import profiles
- PIM-8667: Fix grid filters for numeric attribute codes
- PIM-6138: Fix display of remove icons on family grid and category tree when user does not have permission

# 3.0.37 (2019-08-13)

## Bug fixes

- PIM-8632: Fix empty column in the product datagrid when product model does not have any shared value
- PIM-8633: Fix query to get product models when root product model does not have any shared value
- PIM-8631: Fix column selector in the case of an attribute code as integer
- PIM-8313: Do not display already added attributes in the family attributes selector dropdown

# 3.0.36 (2019-08-08)

## Bug fixes

- PIM-8604: Fix typos in the word 'occurred'
- PIM-8387: Fix product export builder on simple/multi select attribute filters
- PIM-8321: Do not display navigation blocks when there is no links inside
- PIM-8623: Fix wysiwyg edit link modal on Firefox
- PIM-8628: Display label translations on configuration screens even you don't have "Allowed to view information" permission on a locale
- PIM-8624: Fix default product grid view selector in user profile when there is more than 20 views

# 3.0.35 (2019-08-05)

## Bug fixes

- PIM-8591: Fix product history author display
- PIM-8589: Fix add attribute to attribute group when no permission on group "Other"
- PIM-8445: Fix variant axes settings CSS style
- PIM-8614: Fix empty variant axes validation
- PIM-8611: Fix filters applied when a product model is excluded from a product-grid selection

# 3.0.34 (2019-07-24)

## Bug fixes

- PIM-8582: Fix wysiwyg edit link modal not visible

# 3.0.33 (2019-07-24)

## Bug fixes

- PIM-8564: fixes link dialog rendering for wysiwyg editors on product edition page

# 3.0.32 (2019-07-19)

## Bug fixes

- PIM-8555: Fix display of metric as variant axis in PEF
- PIM-8560: Fix maximum height of categories tree to be able to scroll

# 3.0.31 (2019-07-16)

# 3.0.30 (2019-07-05)

## Bug fixes

- PIM-8441: Fix badge positioning on PM dropdowns
- PIM-8354: Fix missing metric attributes on channel page

# 3.0.29 (2019-07-04)

# 3.0.28 (2019-07-02)

## Bug fixes

- PIM-7894: Fix metric and price filters design
- PIM-8447: Fix deformed images in dropdowns and grids
- PIM-8477: Fix rich text area link dialog
- PIM-8463: On import profiles, displays an explicit error message when file upload fails, for example when the file is too big

# 3.0.27 (2019-06-27)

## Bug fixes

- PIM-8413: Fix modal of category selection in product and product model exports

# 3.0.26 (2019-06-21)

## Bug fixes

- PIM-8383: Do not take products without family into account when filtering on empty values
- PIM-8460: Do not display Save button if user does not have ACL edit permissions on product models.

# 3.0.25 (2019-06-18)

# 3.0.24 (2019-06-17)

## Bug fixes

- PIM-8414: Fix the product variant breadcrumb size
- PIM-8427: Fix PDF export of product to expose all attributes
- PIM-8439: Fix mass edit translation for "remove from categories" operation
- PIM-8426: Fix user password validation

## Improvements

- PIM-8433: Save loading messages in the database and display them randomly on the main loading screen.

# 3.0.23 (2019-06-11)

## Bug fixes

- PIM-8412: Fix wrong display of too long attribute group labels when filtering in the datagrid
- PIM-8424: Fix memory leak executing "akeneo:batch:purge-job-execution" command

# 3.0.22 (2019-06-04)

## Bug fixes

- PIM-8315: Fix undefined attribute groups in family mass edit

# 3.0.21 (2019-05-27)

## Bug fixes

- PIM-7772: Fix translation in roles ACL
- PIM-8308: Fix missing translation for import and export profiles
- PIM-8375: Fix counter on grids when user selects all results then select all visible results

# 3.0.20 (2019-05-24)

## Bug fixes

- PIM-8257: Fix user grid filter set when creating a new user
- PIM-8366: Translate the placeholder in the quick search input
- PIM-8374: Fix timeout when launching the completeness purge command

# 3.0.19 (2019-05-21)

# Bug fixes

- PIM-8343: Use BaseRemover instead of ObjectManager to delete a user
- PIM-8340: Allow to delete a user who authored or replied to a comment

# 3.0.18 (2019-05-15)

# Bug fixes

- PIM-8013: Fix 401 redirection on non authorized page
- PIM-8242: Fix the search result rendering of the product grid filters when a filter is unselected

# Improvements

- PIM-8282: Fix error message when removing category tree linked to a channel

# 3.0.17 (2019-05-10)

# Bug fixes

- PIM-8283: Command `akeneo:batch:purge-job-execution` now works with option `--days=0`.
- PIM-8329: Add Serbian flag for CS region
- PIM-8254: Attributes, attribute groups, groups, group types and channels edit page are not accessible anymore
    and remove action is disabled from grid if they are not granted.

# Improvements

- AOB-472: Fix modal display when using illustration class
- AOB-479: Resource paths in less files are now absolute and are checked when executing the "oro:assetic:dump" command to avoid wrong path resolution by Assetic.

# 3.0.16 (2019-05-06)

# Bug fixes

- PIM-8312: Delete unique value row in `pim_catalog_product_unique_data` table when deleting unique value in product

# Improvements

- AOB-472: Add missing check template bootstrap modal
- PIM-8325: Apply permissions on quick export

# 3.0.15 (2019-04-30)

# Bug fixes

- PIM-8287: Fix horizontal scroll on history panel

# 3.0.14 (2019-04-19)

# Bug fixes

- PIM-8291: Use the UI locale in Completeness dashboard widget
- PIM-8285: allow reordering of some datagrid columns by forcing the presence of column.code if not provided by the backend

# 3.0.13 (2019-04-15)

# Bug fixes

- PIM-8286: Allow users to edit their own account even if they're not granted the `pim_user_user_edit` permission

# 3.0.12 (2019-04-09)

# Bug fixes

- PIM-8239: Set latest doctrine migration during fresh install to be consistent with database state
- PIM-8272: Fix "My account" product grid filter search on label
- PIM-8274: Fix misplaced button on imports/exports

## Enhancements

- PIM-8233: Extract attribute normalization in dedicated classes

# 3.0.11 (2019-04-02)

# Bug fixes

- PIM-8258: Fix missing translation for "copy none"
- PXD-98: Fix panel content size for filters selector column
- PIM-8252: Add ACL on the edit import/export profile button and grid buttons
- PIM-8267: Fix user's group delete translation
- PIM-8271: Fix import/export delete translation
- PIM-8264: Fix multiselect style
- PIM-8265: Fix blinking display selector on products page
- PIM-8259: add a max width and a title attribute to the label field in the product grid

# 3.0.10 (2019-03-28)

# Bug fixes

- PIM-8241: Do not reset filter display when adding a new filter
- PXD-91: Fix margins between search filter and grid
- PXD-92: Fix badges visibility
- PXD-93: Fix icon colors
- PXD-95: Fix the too small content zone in the 'Associate' bulk action

# 3.0.9 (2019-03-26)

# Bug fixes

- PIM-8196: Fix long channel labels on completeness widget
- PXD-89: Update illustrations with new colors
- PIM-8237: Fix ignored query params "options" on the internal API: search attribute
- PIM-8248: Fix load order of pim/model/attribute in requirejs
- PXD-10: Fix comment deletion Popin
- PXD-11: Fix Done button position on category tree
- PXD-12: Move view selector in the search box
- PXD-13: Fix disable filter cross on filters list
- PXD-15: Fix margins on filters column
- PXD-17: Fix grid margins
- PXD-19: Fix Forgot Password page design
- PXD-20: Fix version number design in history pages
- PXD-21: Fix Dashboard Last operation widget design
- PXD-22: Fix grid action buttons margin
- PXD-23: Fix border between user navigation and actions
- PXD-24: Fix grid bottom panel margins
- PXD-82: Fix stick header on horizontal scroll
- PXD-14: Add default placeholder for every select2 search input

# 3.0.8 (2019-03-20)

## Bug fixes

- PIM-8217: Fix missing translations on product grid (Filters, Done, Yes and No)
- PIM-8221: Fix missing translations on family attributes tab (Required, Not required)
- PIM-8128: Fix display of reset password confirmation message
- PIM-8223: Fix missing translation on family variant deletion
- PIM-8224: Fix missing translations in process tracker (Compute completeness, Compute family variant and Compute product model descendants)
- PIM-8136: Fix display order of datepicker
- PIM-8227: Fix disappearing columns when saving view columns

# 3.0.7 (2019-03-13)

# 3.0.6 (2019-03-08)

## Bug fixes

- PIM-8051: Fix display of product model images in the datagrid when "Attribute used as main picture" is at the variant attributes level one
- PIM-8168: accept MySql version with suffix in requirements check
- PIM-8152: accept spaces in kernel root dir when executing commands
- PIM-8178: Fix Attribute search on family edit page
- PIM-8189: Fix new patch availability display
- PIM-8195: Fix JS error on Catalog volume monitoring screen

# 3.0.5 (2019-02-25)

## Bug fixes

- PIM-8157: Fix issues on user edit (scroll and groups)
- PIM-8164: Always display cancel cross on popins
- PIM-8165: Increase font sizes
- PIM-8018: Move Confirm button on mass edit screen
- PIM-8167: register missing command "pim:reference-data:check"

# 3.0.4 (2019-02-20)

## Bug fixes

- PIM-8129: Fix default system locale
- PIM-8145: Fix design on variant navigation
- PIM-8144: Fix spaces on field labels and required note
- PIM-8143: Fix missing translations
- PIM-8147: Fix design issue on boolean fields
- PIM-8146: Fix centered alignment on drag & drop fields
- PIM-8156: Fix multiselect field alignment
- PIM-8153: Fix locale specific field to allow multiple locales
- PIM-8017: Fix PDF generation
- PIM-8060: Fix avatars migration 2.3 -> 3.0
- PIM-8135: Fix cursor paginator sequence
- PIM-8053: Fix avatar deletion and avatar update on dashboard page and user navigation
- PIM-8149: Fix design of the cross deletion in the Item Picker

# 3.0.3 (2019-02-18)

## Bug fixes

- PIM-8127: Be able to save and launch job even if filter values refer to deleted entities.

# 3.0.2 (2019-02-13)

## Bug fixes

- PIM-8020: Fix wrong count on missing required attributes in the completeness
- PIM-8028: Fix translations on boolean values
- PIM-8057: Fix error during "forgot password" process
- PIM-8019: Fix broken bulk product association modal

# 3.0.1 (2019-02-06)

- Name Community version "Super-Rabbit"

# 3.0.0 (2019-02-06)

## Technical improvement

- Set Elasticsearch 6.5.4 as a minimal requirement
- Set PHP 7.2 as minimal required version
- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component
- GITHUB-8451: Add basic compatibility for PHP 7.2  (Thanks [janmyszkier](https://github.com/janmyszkier)!)
- PIM-7371: Improve the performance to display the category tree in the product grid
- PIM-7506: Cache default views and columns on the product grid
- TIP-879: Uses utf8mb4 as encoding for MySQL instead of the less complete utf8
- Centralizes technical requirements checks to reuse them on standard edition
- TIP-883: In order to have a clean and independent product aggregate, ProductValue only provides attribute code and no more direct attribute access.
- PIM-7660: Improve performance of the product grid by using a dedicated read model
- PIM-7499: Improve the performance of the completeness widget in the dashboard
- PIM-7371: Improve the performance of the category tree in the product grid
- PIM-7839: Remove date of birth
- GITHUB-8234 & GITHUB-8383: Fix constraints on attribute code. Cheers @oliverde8 & @navneetbhardwaj!
- TIP-1018: Adds a script to check container services instantiability (bin/check-services-instantiability)
- GITHUB-9333: Fix the storage data collector to consider the port number on system information page. Cheers @nei!
- TIP-899: Improve product export performance by computing headers at the end
- PIM-7968: Make the user fetchable by id instead of username

## Enhancements

- TIP-832: Enable regional languages for UI
- TIP-898: Allow extension for user via a property named "properties", used on the EE by example

## BC breaks
- TIP-1018: Remove SOAP requirements (not used anymore) and WSSE bundle
- Remove service `@pim_enrich.provider.structure_version.attribute` in favor of `@pim_structure_version.provider.structure_version.attribute`
- Remove service `@pim_enrich.provider.structure_version.family_variant` in favor of `@pim_structure_version.provider.structure_version.family_variant`
- Remove service `@pim_enrich.provider.structure_version.group_type` in favor of `@pim_structure_version.provider.structure_version.group_type`
- Replace argument `Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface` by `Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder` in the constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AssociationFieldSetter`
- Replace argument `Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface` by `Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder` in the constructor of `Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder`
- Remove method `addMissingAssociations` from interface `Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface`
- Remove argument `Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder` from constructor of `Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder`
- Remove service `@pim_enrich.provider.structure_version.product` in favor of `@pim_structure_version.provider.structure_version.product`
- Remove service `@pim_enrich.provider.structure_version.group` in favor of `@pim_structure_version.provider.structure_version.group`
- Remove argument `@pim_catalog.association.missing_association_adder` from `pim_catalog.builder.product`
- Remove interface `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Remove service `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectIdResolver`
- Replace the `Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface` by `Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder` in `Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductController`
- Remove method `mergeAndFilterConversionUnits` from `Akeneo\Channel\Bundle\Controller\ExternalApi\ChannelController`
- Change constructor of `Akeneo\UserManagement\Component\Normalizer\UserNormalizer` to add an Array of `Symfony\Component\Serializer\Normalizer\NormalizerInterface` and a variadic of properties (designed for User)
- Change constructor of `Akeneo\UserManagement\Component\Updater\UserUpdater` to add a variadic of properties (designed for User)
- `AbstractValue->getAttribute()` has been replaced by `AbstractValue->getAttributeCode()`. You will need to inject the AttributeRepository in your service if you need to access the full Attribute object related to the provided attribute code.
- `AbstractValue->getLocale()` has been renamed to `AbstractValue->getLocaleCode()` to better represent its behaviour
- `AbstractValue->getScope()` has been renamed to `AbstractValue->getScopeCode()` to better represent its behaviour
- MySQL charset for Akeneo is now utf8mb4, instead of the flawed utf8. If you have custom table, you can convert them with `ALTER TABLE my_custom_table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`. For Akeneo native tables, the migration scripts apply the conversion.
- ProductValue objects must now be instantiated through the named constructor must be called to instantiate a new ProductValue. See `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue`, methods `value()`, `scopableValue()`, `localizablevalue()` and `scopableAndLocalizableValue()`
- The service `pim_catalog.repository.cached_attribute`, of type `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`, has been added to the construtor of the following classes:
  - `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer`
  - `Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleSpecificValueSubscriber`
  - `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer`
  - `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\MediaCompleteChecker`
  - `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\MetricCompleteChecker`
  - `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\PriceCompleteChecker`
  - `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteChecker`
  - `Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter`
  - `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer`
  - `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\EntityWithFamilyVariantNormalizer`
  - `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer`
  - `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ValueNormalizer`
  - `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntityValidator`
  - `Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping\ProductValueMetadataFactory`
  - `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\AbstractEntityWithFamilyValuesFiller`
  - `Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer`
  - `Oro\Bundle\PimDataGridBundle\Normalizer\Product\ReferenceDataCollectionNormalizer`
  - `Oro\Bundle\PimDataGridBundle\Normalizer\Product\ReferenceDataNormalizer`

- The service `pim_catalog.repository.cached_attribute_option`, of type `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`, has been added to the construtor of the following classes:
  - `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ValueNormalizer`
  - `Oro\Bundle\PimDataGridBundle\Normalizer\Product\OptionNormalizer`
  - `Oro\Bundle\PimDataGridBundle\Normalizer\Product\OptionsNormalizer`

- The service `pim_reference_data.repository_resolver`, of type `Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface`, has been added to the constructor of the following classes:
  - `Oro\Bundle\PimDataGridBundle\Normalizer\Product\ReferenceDataCollectionNormalizer`
  - `Oro\Bundle\PimDataGridBundle\Normalizer\Product\ReferenceDataNormalizer`

- In `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactory`, the attribute repository parameter is now defined as `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` instead of ``Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface`


- Change constructor of `Akeneo\UserManagement\Component\Updater\UserUpdater` to add `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
- Change constructor of `Akeneo\UserManagement\Component\Updater\UserUpdater` to add `Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface`
- Change constructor of `Akeneo\UserManagement\Component\Updater\UserUpdater` to add `Doctrine\Common\Persistence\ObjectRepository`
- Change constructor of `Akeneo\UserManagement\Component\Updater\UserUpdater` to add `Oro\Bundle\PimDataGridBundle\Entity\DatagridView`
- Change constructor of `Akeneo\UserManagement\Component\Normalizer\UserNormalizer` to add `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `Akeneo\UserManagement\Component\Normalizer\UserNormalizer` to add `Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface`
- Change constructor of `Akeneo\UserManagement\Component\Normalizer\UserNormalizer` to add `Oro\Bundle\SecurityBundle\SecurityFacade`
- Change constructor of `Akeneo\UserManagement\Component\Normalizer\UserNormalizer` to add `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\UserManagement\Component\Normalizer\UserNormalizer` to add `Symfony\Component\Serializer\Normalizer\DateTimeNormalizer`
- Add methods `setAvatar`, `getAvatar`, `setGroups` to `Akeneo\UserManagement\Component\Model\UserInterface`
- Remove `Akeneo\UserManagement\Bundle\Controller\UserController`
- Remove `Akeneo\UserManagement\Bundle\Form\Handler\AbstractUserHandler`
- Remove `Akeneo\UserManagement\Bundle\Form\Handler\UserHandler`
- Remove `Akeneo\UserManagement\Bundle\Form\Subscriber\ChangePasswordSubscriber`
- Remove `Akeneo\UserManagement\Bundle\Form\Subscriber\UserPreferencesSubscriber`
- Remove `Akeneo\UserManagement\Bundle\Form\Type\ChangePasswordType`
- Remove `Akeneo\UserManagement\Bundle\Form\Type\UserType`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Doctrine\Common\Persistence\ObjectManager`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Oro\Bundle\SecurityBundle\Annotation\AclAncestor`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Symfony\Component\HttpFoundation\Session\Session`
- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `Symfony\Component\Validator\Validator\ValidatorInterface`
- Change constructor of `Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider` to add `Akeneo\Channel\Component\Repository\LocaleRepositoryInterface` argument
- Remove `Akeneo\Platform\Bundle\UIBundle\Form\Type\ProductGridFilterChoiceType`
- Remove `Pim\Bundle\EnrichBundle\PimEnrichBundle`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\MeasuresController` to `Akeneo\Tool\Bundle\MeasureBundle\Controller\MeasuresController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\ApiClientController` to `Akeneo\UserManagement\Bundle\Controller\ApiClientController`
- Move `Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\TemporaryFileCleaner` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit\TemporaryFileCleaner`
- Move `Pim\Bundle\EnrichBundle\Connector\Step\MassEditStep` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Step\MassEditStep`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductAndProductModelQuickExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductAndProductModelQuickExport`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductMassEdit` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductMassEdit`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductQuickExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductQuickExport`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleMassEdit` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleMassEdit`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider\ProductAndProductModelMassDelete` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductAndProductModelMassDelete`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider\ProductMassEdit` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductMassEdit`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider\ProductQuickExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductQuickExport`
- Move `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider\SimpleMassEdit` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\SimpleMassEdit`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductAndProductModelProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\QuickExport\ProductAndProductModelProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductAndProductModelProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\QuickExport\ProductAndProductModelProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddAttributeValueProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddAttributeValueProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductValueProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddProductValueProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddToExistingProductModelProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddToExistingProductModelProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\ChangeParentProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\ChangeParentProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditAttributesProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditAttributesProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditCommonAttributesProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\RemoveProductValueProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\RemoveProductValueProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\UpdateProductValueProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\UpdateProductValueProcessor`
- Move `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family\SetAttributeRequirements` to `Akeneo\Pim\Structure\Component\Processor\MassEdit\SetAttributeRequirements`
- Move `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductAndProductModelReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductAndProductModelReader`
- Move `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductModelReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductModelReader`
- Move `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductReader`
- Move `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\ProductAndProductModelReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\ProductAndProductModelReader`
- Move `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredFamilyReader` to `Akeneo\Pim\Structure\Component\Reader\Database\MassEdit\FilteredFamilyReader`
- Move `Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit\ProductAndProductModelWriter`
- Move `Pim\Bundle\LocalizationBundle\Controller\FormatController` to `Oro\Bundle\ConfigBundle\Controller\Rest\FormatController`
- Move `Pim\Bundle\LocalizationBundle\Controller\LocaleController` to `Akeneo\Platform\Bundle\UIBundle\Controller\LocaleController`
- Move `Pim\Bundle\LocalizationBundle\Provider\UiLocaleProvider` to `Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider`
- Move `Pim\Bundle\LocalizationBundle\Form\DataTransformer\NumberLocalizerTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\NumberLocalizerTransformer`
- Move `Pim\Bundle\LocalizationBundle\Form\Type\LocaleType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\LocaleType`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\CategoryNormalizer` to `Akeneo\Pim\Enrichment\Component\Category\Normalizer\Versioning\CategoryNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\TranslationNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\GroupNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\GroupNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\ProductModelNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductModelNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\ValueNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ValueNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\DateTimeNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\DateTimeNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\FileNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\FileNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\MetricNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\MetricNormalizer`
- Move `Pim\Component\VersioningBundle\Normalizer\Flat\PriceNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\PriceNormalizer`
- Move `Pim\Bundle\LocalizationBundle\Twig\AttributeExtension` to `Akeneo\Platform\Bundle\UIBundle\Twig\AttributeExtension`
- Move `Pim\Bundle\LocalizationBundle\Twig\LocaleExtension` to `Akeneo\Platform\Bundle\UIBundle\Twig\LocaleExtension`
- Move `Pim\Bundle\ReferenceDataBundle\DataGrid\Extension\Sorter\ReferenceDataSorter` to `Oro\Bundle\PimDataGridBundle\Extension\Sorter\Produc\ReferenceDataSorter`
- Move `Pim\Bundle\ReferenceDataBundle\DataGrid\Normalizer\ReferenceDataCollectionNormalizer` to `Oro\Bundle\PimDataGridBundle\Normalizer\Product\ReferenceDataCollectionNormalizer`
- Move `Pim\Bundle\ReferenceDataBundle\DataGrid\Normalizer\ReferenceDataNormalizer` to `Oro\Bundle\PimDataGridBundle\Normalizer\Product\ReferenceDataNormalizer`
- Move `Pim\Bundle\ReferenceDataBundle\DataGrid\Filter\ReferenceDataFilter` to `Oro\Bundle\PimFilterBundle\Filter\ProductValue\ReferenceDataFilter`
- Move `Pim\Bundle\ReferenceDataBundle\DependencyInjection\Compiler\RegisterConfigurationsPass` to `Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterReferenceDataConfigurationsPass`
- Move `Pim\Bundle\ReferenceDataBundle\Enrich\Provider\EmptyValue\ReferenceDataEmptyValueProvider` to `Pim\Bundle\EnrichBundle\Provider\EmptyValue\ReferenceDataEmptyValueProvider`
- Move `Pim\Bundle\ReferenceDataBundle\Enrich\Provider\Field\ReferenceDataFieldProvider` to `Pim\Bundle\EnrichBundle\Provider\Field\ReferenceDataFieldProvider`
- Move `Pim\Bundle\ReferenceDataBundle\Enrich\Provider\Filter\ReferenceDataFilterProvider` to `Pim\Bundle\EnrichBundle\Provider\Filter\ReferenceDataFilterProvider`
- Move `Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver`
- Move `Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\RequirementChecker\ReferenceDataUniqueCodeChecker` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\ReferenceDataUniqueCodeChecker`
- Move `Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ReferenceDataRepository`
- Move `Pim\Bundle\ReferenceDataBundle\Elasticsearch\Filter\Attribute\ReferenceDataFilter` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\ReferenceDataFilter`
- Move `Pim\Bundle\ReferenceDataBundle\Controller\ConfigurationRestController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\ReferenceDataConfigurationRestController`
- Move `Pim\Bundle\ReferenceDataBundle\RequirementChecker\AbstractReferenceDataUniqueCodeChecker` to `Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\AbstractReferenceDataUniqueCodeChecker`
- Move `Pim\Bundle\ReferenceDataBundle\RequirementChecker\CheckerInterface` to `Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\CheckerInterface`
- Move `Pim\Bundle\ReferenceDataBundle\RequirementChecker\ReferenceDataInterfaceChecker` to `Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\ReferenceDataInterfaceChecker`
- Move `Pim\Bundle\ReferenceDataBundle\RequirementChecker\ReferenceDataNameChecker` to `Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\ReferenceDataNameChecker`
- Move `Pim\Bundle\ReferenceDataBundle\Normalizer\ReferenceDataConfigurationNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\ReferenceDataConfigurationNormalizer`
- Move `Pim\Bundle\ReferenceDataBundle\AttributeType\ReferenceDataSimpleSelectType` to `Akeneo\Pim\Structure\Component\AttributeType\ReferenceDataSimpleSelectType`
- Move `Pim\Bundle\ReferenceDataBundle\AttributeType\ReferenceDataMultiSelectType` to `Akeneo\Pim\Structure\Component\AttributeType\ReferenceDataMultiSelectType`
- Move `Pim\Component\ReferenceData\LabelRenderer` to `Akeneo\Pim\Enrichment\Component\Product\ReferenceData\LabelRenderer`
- Move `Pim\Component\ReferenceData\MethodNameGuesser` to `Akeneo\Pim\Enrichment\Component\Product\ReferenceData\MethodNameGuesser`
- Move `Pim\Component\ReferenceData\ConfigurationRegistry` to `Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistry`
- Move `Pim\Component\ReferenceData\ConfigurationRegistryInterface` to `Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface`
- Move `Pim\Component\ReferenceData\Normalizer\Indexing\ProductValue\ReferenceDataNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ReferenceDataNormalizer`
- Move `Pim\Component\ReferenceData\Normalizer\Indexing\ProductValue\ReferenceDataCollectionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ReferenceDataCollectionNormalizer`
- Move `Pim\Component\ReferenceData\Normalizer\Flat\ReferenceDataNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ReferenceDataNormalizer`
- Move `Pim\Component\ReferenceData\Updater\Copier\ReferenceDataAttributeCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\ReferenceDataAttributeCopier`
- Move `Pim\Component\ReferenceData\Updater\Copier\ReferenceDataCollectionAttributeCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\ReferenceDataCollectionAttributeCopier`
- Move `Pim\Component\ReferenceData\Model\ReferenceDataInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface`
- Move `Pim\Component\ReferenceData\Model\AbstractReferenceData` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractReferenceData`
- Move `Pim\Component\ReferenceData\Model\Configuration` to `Akeneo\Pim\Structure\Component\Model\ReferenceDataConfiguration`
- Move `Pim\Component\ReferenceData\Model\ConfigurationInterface` to `Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface`
- Move `Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue` to `Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue`
- Move `Pim\Component\ReferenceData\Value\ReferenceDataCollectionValueInterface` to `Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface`
- Move `Pim\Component\ReferenceData\Value\ReferenceDataValue` to `Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue`
- Move `Pim\Component\ReferenceData\Value\ReferenceDataValueInterface` to `Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface`
- Move `Pim\Component\ReferenceData\Factory\Value\ReferenceDataValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataValueFactory`
- Move `Pim\Component\ReferenceData\Factory\Value\ReferenceDataCollectionValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataCollectionValueFactory`
- Move `Pim\Component\Catalog\ProductEvents` to `Akeneo\Pim\Enrichment\Component\Product\ProductEvents`
- Move `Pim\Component\Catalog\FileStorage` to `Akeneo\Pim\Enrichment\Component\FileStorage`
- Move `Pim\Component\Catalog\AttributeTypes` to `Akeneo\Pim\Structure\Component\AttributeTypes`
- Move `Pim\Component\Catalog\AttributeTypeInterface` to `Akeneo\Pim\Structure\Component\AttributeTypeInterface`
- Move `Pim\Component\Catalog\Exception\AlreadyExistingAxisValueCombinationException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException`
- Move `Pim\Component\Catalog\Exception\InvalidArgumentException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException`
- Move `Pim\Component\Catalog\Exception\InvalidAttributeException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException`
- Move `Pim\Component\Catalog\Exception\InvalidDirectionException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException`
- Move `Pim\Component\Catalog\Exception\InvalidOperatorException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException`
- Move `Pim\Component\Catalog\Exception\InvalidOptionException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOptionException`
- Move `Pim\Component\Catalog\Exception\InvalidOptionsException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOptionsException`
- Move `Pim\Component\Catalog\Exception\MissingIdentifierException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\MissingIdentifierException`
- Move `Pim\Component\Catalog\Exception\ObjectNotFoundException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException`
- Move `Pim\Component\Catalog\Exception\ProductQueryException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\ProductQueryException`
- Move `Pim\Component\Catalog\Exception\UnsupportedFilterException` to `Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException`
- Move `Pim\Component\Catalog\Localization\Localizer\AttributeConverter` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverter`
- Move `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface`
- Move `Pim\Component\Catalog\Localization\Localizer\LocalizerRegistry` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistry`
- Move `Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistryInterface`
- Move `Pim\Component\Catalog\Localization\Localizer\MetricLocalizer` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer`
- Move `Pim\Component\Catalog\Localization\Localizer\PriceLocalizer` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\PriceLocalizer`
- Move `Pim\Component\Catalog\Localization\Presenter\MetricPresenter` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\MetricPresenter`
- Move `Pim\Component\Catalog\Localization\Presenter\PresenterRegistry` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistry`
- Move `Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface`
- Move `Pim\Component\Catalog\Localization\Presenter\PricesPresenter` to `Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PricesPresenter`
- Move `Pim\Component\Catalog\Localization\CategoryUpdater` to `Akeneo\Pim\Enrichment\Component\Category\CategoryUpdater`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\BooleanGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\BooleanGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\CurrencyGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\CurrencyGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\DateGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\DateGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\EmailGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\EmailGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\FileGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\FileGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\LengthGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\LengthGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\MetricGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\MetricGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\NotBlankGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\NotBlankGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\NotDecimalGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\NotDecimalGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\NumericGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\NumericGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\PriceCollectionGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\PriceCollectionGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\RangeGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\RangeGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\RegexGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\RegexGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\StringGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\StringGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\UniqueValueGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UniqueValueGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesser\UrlGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UrlGuesser`
- Move `Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntity` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity`
- Move `Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntityValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntityValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductModelEntity` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntity`
- Move `Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductModelEntityValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntityValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\Boolean` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean`
- Move `Pim\Component\Catalog\Validator\Constraints\BooleanValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\BooleanValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\Channel` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Channel`
- Move `Pim\Component\Catalog\Validator\Constraints\ChannelValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ChannelValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\Currency` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency`
- Move `Pim\Component\Catalog\Validator\Constraints\CurrencyValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\CurrencyValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\File` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File`
- Move `Pim\Component\Catalog\Validator\Constraints\FileExtension` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FileExtension`
- Move `Pim\Component\Catalog\Validator\Constraints\FileExtensionValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FileExtensionValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\FileValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FileValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxesValues` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ImmutableVariantAxesValues`
- Move `Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxesValuesValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ImmutableVariantAxesValuesValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\IsNumeric` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric`
- Move `Pim\Component\Catalog\Validator\Constraints\IsNumericValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumericValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\IsString` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString`
- Move `Pim\Component\Catalog\Validator\Constraints\IsStringValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsStringValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\LocalizableValue` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LocalizableValue`
- Move `Pim\Component\Catalog\Validator\Constraints\LocalizableValueValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LocalizableValueValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\NotDecimal` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal`
- Move `Pim\Component\Catalog\Validator\Constraints\NotDecimalValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimalValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\NotEmptyFamily` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily`
- Move `Pim\Component\Catalog\Validator\Constraints\NotEmptyFamilyValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamilyValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\NotEmptyVariantAxes` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyVariantAxes`
- Move `Pim\Component\Catalog\Validator\Constraints\NotEmptyVariantAxesValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyVariantAxesValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\OnlyExpectedAttributes` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\OnlyExpectedAttributes`
- Move `Pim\Component\Catalog\Validator\Constraints\OnlyExpectedAttributesValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\OnlyExpectedAttributesValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ProductModelPositionInTheVariantTree` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTree`
- Move `Pim\Component\Catalog\Validator\Constraints\ProductModelPositionInTheVariantTreeValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTreeValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\Range` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range`
- Move `Pim\Component\Catalog\Validator\Constraints\RangeValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\RangeValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\SameFamilyThanParent` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\SameFamilyThanParent`
- Move `Pim\Component\Catalog\Validator\Constraints\SameFamilyThanParentValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\SameFamilyThanParentValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ScopableValue` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ScopableValue`
- Move `Pim\Component\Catalog\Validator\Constraints\ScopableValueValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ScopableValueValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\UniqueValue` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue`
- Move `Pim\Component\Catalog\Validator\Constraints\UniqueValueValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValueValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxis` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxis`
- Move `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxisValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxisValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\VariantProductParent` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\VariantProductParent`
- Move `Pim\Component\Catalog\Validator\Constraints\VariantProductParentValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\VariantProductParentValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\WritableDirectory` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\WritableDirectory`
- Move `Pim\Component\Catalog\Validator\Constraints\WritableDirectoryValidator` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\WritableDirectoryValidator`
- Move `Pim\Component\Catalog\Validator\Mapping\ClassMetadataFactory` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping\ClassMetadataFactory`
- Move `Pim\Component\Catalog\Validator\Mapping\DelegatingClassMetadataFactory` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping\DelegatingClassMetadataFactory`
- Move `Pim\Component\Catalog\Validator\Mapping\ProductValueMetadataFactory` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping\ProductValueMetadataFactory`
- Move `Pim\Component\Catalog\Validator\AttributeConstraintGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeConstraintGuesser`
- Move `Pim\Component\Catalog\Validator\AttributeValidatorHelper` to `Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper`
- Move `Pim\Component\Catalog\Validator\ChainedAttributeConstraintGuesser` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ChainedAttributeConstraintGuesser`
- Move `Pim\Component\Catalog\Validator\ConstraintGuesserInterface` to `Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface`
- Move `Pim\Component\Catalog\Validator\UniqueAxesCombinationSet` to `Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueAxesCombinationSet`
- Move `Pim\Component\Catalog\Validator\UniqueValuesSet` to `Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet`
- Move `Pim\Component\Catalog\Validator\Constraints\ConversionUnits` to `Akeneo\Channel\Component\Validator\Constraint\ConversionUnits`
- Move `Pim\Component\Catalog\Validator\Constraints\ConversionUnitsValidator` to `Akeneo\Channel\Component\Validator\Constraint\ConversionUnitsValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\IsCurrencyActivated` to `Akeneo\Channel\Component\Validator\Constraint\IsCurrencyActivated`
- Move `Pim\Component\Catalog\Validator\Constraints\IsCurrencyActivatedValidator` to `Akeneo\Channel\Component\Validator\Constraint\IsCurrencyActivatedValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\IsRootCategory` to `Akeneo\Channel\Component\Validator\Constraint\IsRootCategory`
- Move `Pim\Component\Catalog\Validator\Constraints\IsRootCategoryValidator` to `Akeneo\Channel\Component\Validator\Constraint\IsRootCategoryValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidRegex` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidRegex`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidRegexValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidRegexValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidNumberRange` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidNumberRange`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidNumberRangeValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidNumberRangeValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidMetric` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidMetricValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetricValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidDateRange` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange`
- Move `Pim\Component\Catalog\Validator\Constraints\ValidDateRangeValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRangeValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\NullProperties` to `Akeneo\Pim\Structure\Component\Validator\Constraints\NullProperties`
- Move `Pim\Component\Catalog\Validator\Constraints\NullPropertiesValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\NullPropertiesValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\NotNullProperties` to `Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullProperties`
- Move `Pim\Component\Catalog\Validator\Constraints\NotNullPropertiesValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullPropertiesValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\IsReferenceDataConfigured` to `Akeneo\Pim\Structure\Component\Validator\Constraints\IsReferenceDataConfigured`
- Move `Pim\Component\Catalog\Validator\Constraints\IsReferenceDataConfiguredValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\IsReferenceDataConfiguredValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\IsIdentifierUsableAsGridFilter` to `Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilter`
- Move `Pim\Component\Catalog\Validator\Constraints\IsIdentifierUsableAsGridFilterValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilterValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyVariant` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyVariant`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyVariantValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyVariantValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\AttributeTypeForOption` to `Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeTypeForOption`
- Move `Pim\Component\Catalog\Validator\Constraints\AttributeTypeForOptionValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeTypeForOptionValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\Immutable` to `Akeneo\Tool\Component\StorageUtils\Validator\Constraints\Immutable`
- Move `Pim\Component\Catalog\Validator\Constraints\ImmutableValidator` to `Akeneo\Tool\Component\StorageUtils\Validator\Constraints\ImmutableValidator`
- Move `Pim\Component\Catalog\Model\AbstractMetric` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractMetric`
- Move `Pim\Component\Catalog\Model\AbstractProductPrice` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProductPrice`
- Move `Pim\Component\Catalog\Model\AbstractValue` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue`
- Move `Pim\Component\Catalog\Model\CommonAttributeCollection` to `Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection`
- Move `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface`
- Move `Pim\Component\Catalog\Model\EntityWithFamilyInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface`
- Move `Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface`
- Move `Pim\Component\Catalog\Model\EntityWithValuesInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface`
- Move `Pim\Component\Catalog\Model\Metric` to `Akeneo\Pim\Enrichment\Component\Product\Model\Metric`
- Move `Pim\Component\Catalog\Model\MetricInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface`
- Move `Pim\Component\Catalog\Model\PriceCollection` to `Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection`
- Move `Pim\Component\Catalog\Model\PriceCollectionInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface`
- Move `Pim\Component\Catalog\Model\ProductPrice` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice`
- Move `Pim\Component\Catalog\Model\ProductPriceInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface`
- Move `Pim\Component\Catalog\Model\ProductUniqueValueCollectionInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueValueCollectionInterface`
- Move `Pim\Component\Catalog\Model\ScopableInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ScopableInterface`
- Move `Pim\Component\Catalog\Model\TimestampableInterface` to `Akeneo\Tool\Component\Versioning\Model\TimestampableInterface`
- Move `Pim\Component\Catalog\Model\ValueCollection` to `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection`
- Move `Pim\Component\Catalog\Model\ValueCollectionInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface`
- Move `Pim\Component\Catalog\Model\VariantProductInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\VariantProductInterface`
- Move `Pim\Component\Catalog\Updater\Adder\AbstractAttributeAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AbstractAttributeAdder`
- Move `Pim\Component\Catalog\Updater\Adder\AbstractFieldAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AbstractFieldAdder`
- Move `Pim\Component\Catalog\Updater\Adder\AdderInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface`
- Move `Pim\Component\Catalog\Updater\Adder\AdderRegistry` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistry`
- Move `Pim\Component\Catalog\Updater\Adder\AdderRegistryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface`
- Move `Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder`
- Move `Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AttributeAdderInterface`
- Move `Pim\Component\Catalog\Updater\Adder\CategoryFieldAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\CategoryFieldAdder`
- Move `Pim\Component\Catalog\Updater\Adder\FieldAdderInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface`
- Move `Pim\Component\Catalog\Updater\Adder\GroupFieldAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\GroupFieldAdder`
- Move `Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\MultiSelectAttributeAdder`
- Move `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\PriceCollectionAttributeAdder`
- Move `Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AbstractAttributeCopier`
- Move `Pim\Component\Catalog\Updater\Copier\AttributeCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopier`
- Move `Pim\Component\Catalog\Updater\Copier\AttributeCopierInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface`
- Move `Pim\Component\Catalog\Updater\Copier\CopierInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface`
- Move `Pim\Component\Catalog\Updater\Copier\CopierRegistry` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistry`
- Move `Pim\Component\Catalog\Updater\Copier\CopierRegistryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface`
- Move `Pim\Component\Catalog\Updater\Copier\FieldCopierInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\FieldCopierInterface`
- Move `Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\MediaAttributeCopier`
- Move `Pim\Component\Catalog\Updater\Copier\MetricAttributeCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\MetricAttributeCopier`
- Move `Pim\Component\Catalog\Updater\Remover\AbstractAttributeRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AbstractAttributeRemover`
- Move `Pim\Component\Catalog\Updater\Remover\AbstractFieldRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AbstractFieldRemover`
- Move `Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface`
- Move `Pim\Component\Catalog\Updater\Remover\CategoryFieldRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\CategoryFieldRemover`
- Move `Pim\Component\Catalog\Updater\Remover\FieldRemoverInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface`
- Move `Pim\Component\Catalog\Updater\Remover\GroupFieldRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\GroupFieldRemover`
- Move `Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\MultiSelectAttributeRemover`
- Move `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\PriceCollectionAttributeRemover`
- Move `Pim\Component\Catalog\Updater\Remover\RemoverInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverInterface`
- Move `Pim\Component\Catalog\Updater\Remover\RemoverRegistry` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistry`
- Move `Pim\Component\Catalog\Updater\Remover\RemoverRegistryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface`
- Move `Pim\Component\Catalog\Updater\Setter\AbstractAttributeSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AbstractAttributeSetter`
- Move `Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AbstractFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AssociationFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\AttributeSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetter`
- Move `Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface`
- Move `Pim\Component\Catalog\Updater\Setter\CategoryFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\CategoryFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\EnabledFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\EnabledFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\FamilyFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FamilyFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\FieldSetterInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface`
- Move `Pim\Component\Catalog\Updater\Setter\GroupFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\GroupFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter`
- Move `Pim\Component\Catalog\Updater\Setter\ParentFieldSetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\ParentFieldSetter`
- Move `Pim\Component\Catalog\Updater\Setter\SetterInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface`
- Move `Pim\Component\Catalog\Updater\Setter\SetterRegistry` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistry`
- Move `Pim\Component\Catalog\Updater\Setter\SetterRegistryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface`
- Move `Pim\Component\Catalog\Updater\EntityWithValuesUpdater` to `Akeneo\Pim\Enrichment\Component\Product\Updater\EntityWithValuesUpdater`
- Move `Pim\Component\Catalog\Updater\GroupUpdater` to `Akeneo\Pim\Enrichment\Component\Product\Updater\GroupUpdater`
- Move `Pim\Component\Catalog\Updater\ProductModelUpdater` to `Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater`
- Move `Pim\Component\Catalog\Updater\ProductUpdater` to `Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater`
- Move `Pim\Component\Catalog\Updater\PropertyAdder` to `Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyAdder`
- Move `Pim\Component\Catalog\Updater\PropertyCopier` to `Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyCopier`
- Move `Pim\Component\Catalog\Updater\PropertyRemover` to `Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyRemover`
- Move `Pim\Component\Catalog\Updater\PropertySetter` to `Akeneo\Pim\Enrichment\Component\Product\Updater\PropertySetter`
- Move `Pim\Component\Catalog\Updater\Remover\FamilyVariantRemover` to `Akeneo\Pim\Structure\Component\Remover\FamilyVariantRemover`
- Move `Pim\Component\Catalog\Updater\Remover\FamilyRemover` to `Akeneo\Pim\Structure\Component\Remover\FamilyRemover`
- Move `Pim\Component\Catalog\Factory\Value\DateValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\DateValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\MediaValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\MediaValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\MetricValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\MetricValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\OptionsValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionsValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\OptionValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\PriceCollectionValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\PriceCollectionValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\ScalarValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ScalarValueFactory`
- Move `Pim\Component\Catalog\Factory\Value\ValueFactoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactoryInterface`
- Move `Pim\Component\Catalog\Factory\GroupFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\GroupFactory`
- Move `Pim\Component\Catalog\Factory\MetricFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory`
- Move `Pim\Component\Catalog\Factory\PriceFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\PriceFactory`
- Move `Pim\Component\Catalog\Factory\ProductUniqueDataFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\ProductUniqueDataFactory`
- Move `Pim\Component\Catalog\Factory\ValueCollectionFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactory`
- Move `Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface`
- Move `Pim\Component\Catalog\Factory\ValueFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory`
- Move `Pim\Component\Catalog\Manager\AttributeValuesResolver` to `Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolver`
- Move `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface` to `Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface`
- Move `Pim\Component\Catalog\Manager\CompletenessManager` to `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`
- Move `Pim\Component\Catalog\Normalizer\Indexing\CompletenessCollectionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\CompletenessCollectionNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\DateTimeNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\DateTimeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Product\PropertiesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\PropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelPropertiesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelPropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductPropertiesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductPropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\ProductModel\ProductModelNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\ProductModel\ProductModelPropertiesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelPropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\AbstractProductValueNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\BooleanNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\BooleanNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\DateNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\DateNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\DummyNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\DummyNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\MediaNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\MediaNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\MetricNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\MetricNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\NumberNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\NumberNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\OptionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\OptionNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\OptionsNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\OptionsNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\PriceCollectionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\PriceCollectionNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\TextAreaNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\TextAreaNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\TextNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\TextNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\Value\ValueCollectionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\CategoryNormalizer` to `Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard\CategoryNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\DateTimeNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\FileNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\FileNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\GroupNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\GroupNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\AssociationsNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\AssociationsNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\MetricNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\ParentsAssociationsNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ParentsAssociationsNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\PriceNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PriceNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\ProductValueNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\ProductValuesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValuesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\ProductModelNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductModelNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\DateTimeNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\DateTimeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\FileNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\FileNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\Product\AssociationsNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\AssociationsNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\Product\MetricNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\MetricNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\Product\PriceNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\PriceNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\Product\ProductValueNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValueNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\Product\ProductValuesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValuesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\Product\PropertiesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\PropertiesNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\ProductNormalizer`

- Register standard format normalizers into  `pim_standard_format_serializer` serializer and untagged them from `pim_serializer` serializer
- Register indexing normalizers into  `pim_indexing_serializer` serializer and untagged them from `pim_serializer` serializer
- Register datagrid normalizers into  `pim_datagrid_serializer` serializer and untagged them from `pim_serializer` serializer
- Register storage normalizers into  `pim_storage_serializer` serializer and untagged them from `pim_serializer` serializer
- Register external API normalizers into  `pim_external_api_serializer` serializer and untagged them from `pim_serializer` serializer

- Change constructor of `Pim\Component\Catalog\Updater\ProductUpdater`, remove `$supportedFields` argument

- Move `Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider` to `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider`
- Move `Pim\Component\Catalog\Repository\AssociationRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\CompletenessRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\CompletenessRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\EntityWithFamilyVariantRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\EntityWithFamilyVariantRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\GroupRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\ProductMassActionRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\ProductModelCategoryRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelCategoryRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\ProductRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\VariantProductRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface`

- Move namespace `Pim\Component\Catalog\Query` to `Akeneo\Pim\Enrichment\Component\Product\Query`
- Move namespace `Pim\Component\Catalog\Job` to `Akeneo\Pim\Enrichment\Component\Product\Job`
- Move namespace `Pim\Component\Catalog\Converter` to `Akeneo\Pim\Enrichment\Component\Product\Converter`
- Move namespace `Pim\Component\Catalog\Builder` to `Akeneo\Pim\Enrichment\Component\Product\Builder`
- Move namespace `Pim\Component\Catalog\Association` to `Akeneo\Pim\Enrichment\Component\Product\Association`
- Move namespace `Pim\Component\Catalog\Comparator` to `Akeneo\Pim\Enrichment\Component\Product\Comparator`
- Move namespace `Pim\Component\Catalog\EntityWithFamilyVariant` to `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant`
- Move namespace `Pim\Component\Catalog\EntityWithFamily` to `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily`
- Move namespace `Pim\Component\Catalog\ProductAndProductModel` to `Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel`
- Move namespace `Pim\Component\Catalog\ProductModel` to `Akeneo\Pim\Enrichment\Component\Product\ProductModel`
- Move namespace `Pim\Component\Catalog\Completeness` to `Akeneo\Pim\Enrichment\Component\Product\Completeness`
- Move namespace `Pim\Component\Catalog\ValuesFiller` to `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller`

- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\Localization\RegisterLocalizersPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization\RegisterLocalizersPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\Localization\RegisterPresentersPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization\RegisterPresentersPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterComparatorsPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterComparatorsPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterCompleteCheckerPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterCompleteCheckerPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterFilterPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterFilterPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductUpdaterPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductUpdaterPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterValueFactoryPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterValueFactoryPass`

- Move `Pim\Bundle\CatalogBundle\EventSubscriber/Category/CheckChannelsOnDeletionSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/Category/CheckChannelsOnDeletionSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/AddBooleanValuesToNewProductSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/AddBooleanValuesToNewProductSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/ComputeCompletenessOnFamilyUpdateSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/ComputeCompletenessOnFamilyUpdateSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/ComputeEntityRawValuesSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/ComputeEntityRawValuesSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/ComputeProductModelDescendantsSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/ComputeProductModelDescendantsSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/IndexProductModelCompleteDataSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/IndexProductModelCompleteDataSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/IndexProductModelsSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/IndexProductModelsSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/IndexProductsSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/IndexProductsSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/LoadEntityWithValuesSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/LoadEntityWithValuesSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/LocalizableSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/LocalizableSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/ResetUniqueValidationSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/ResetUniqueValidationSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/ScopableSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/ScopableSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber/TimestampableSubscriber` to `Akeneo/Pim/Enrichment/Bundle/EventSubscriber/TimestampableSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber\CreateAttributeRequirementSubscriber` to `Akeneo\Pim\Structure\Bundle\EventSubscriber\CreateAttributeRequirementSubscriber`

- Move `Pim\Bundle\CatalogBundle\Resolver\FQCNResolver` to `Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver`
- Move `Pim\Bundle\CatalogBundle\Context\CatalogContext` to `Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext`

- Move `Pim\Bundle\CatalogBundle\Filter\AbstractFilter` to `Akeneo\Pim\Enrichment\Bundle\Filter\AbstractFilter`
- Move `Pim\Bundle\CatalogBundle\Filter\ChainedFilter` to `Akeneo\Pim\Enrichment\Bundle\Filter\ChainedFilter`
- Move `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface` to `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface`
- Move `Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface` to `Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface`
- Move `Pim\Bundle\CatalogBundle\Filter\ProductValueChannelFilter` to `Akeneo\Pim\Enrichment\Bundle\Filter\ProductValueChannelFilter`
- Move `Pim\Bundle\CatalogBundle\Filter\ProductValueLocaleFilter` to `Akeneo\Pim\Enrichment\Bundle\Filter\ProductValueLocaleFilter`

- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\AttributeIsAFamilyVariantAxis` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\AttributeIsAFamilyVariantAxis`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\CompleteFilter` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CompleteFilter`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\CountEntityWithFamilyVariant` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountEntityWithFamilyVariant`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\CountProductsWithFamily` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountProductsWithFamily`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\VariantProductRatio` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\VariantProductRatio`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\AssociationRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\CompletenessRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\CompletenessRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\GroupRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\GroupRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductCategoryRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductCategoryRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductMassActionRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductMassActionRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductModelCategoryRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductModelCategoryRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductModelRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductModelRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductUniqueDataRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductUniqueDataRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\VariantProductRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\VariantProductRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessRemover` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\CompletenessRemover`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\QueryBuilderUtility`

- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectCodeResolver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectCodeResolver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectIdResolver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\GroupSaver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\GroupSaver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\GroupSavingOptionsResolver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\GroupSavingOptionsResolver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductModelDescendantsSaver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer`

- Move namespace `Pim\Bundle\CatalogBundle\Elasticsearch` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch`

- Move `Pim\Bundle\CatalogBundle\Command\Cleaner\WrongBooleanValuesOnVariantProductCleaner` to `Akeneo\Pim\Enrichment\Bundle\Command\Cleaner\WrongBooleanValuesOnVariantProductCleaner`
- Move `Pim\Bundle\CatalogBundle\Command\ProductQueryHelp\AttributeFilterDumper` to `Akeneo\Pim\Enrichment\Bundle\Command\ProductQueryHelp\AttributeFilterDumper`
- Move `Pim\Bundle\CatalogBundle\Command\ProductQueryHelp\FieldFilterDumper` to `Akeneo\Pim\Enrichment\Bundle\Command\ProductQueryHelp\FieldFilterDumper`
- Move `Pim\Bundle\CatalogBundle\Command\CalculateCompletenessCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\CalculateCompletenessCommand`
- Move `Pim\Bundle\CatalogBundle\Command\CleanRemovedAttributesFromProductAndProductModelCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\CleanRemovedAttributesFromProductAndProductModelCommand`
- Move `Pim\Bundle\CatalogBundle\Command\CreateProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\CreateProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\DumperInterface` to `Akeneo\Pim\Enrichment\Bundle\Command\DumperInterface`
- Move `Pim\Bundle\CatalogBundle\Command\GetProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\GetProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\IndexProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\IndexProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\IndexProductModelCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\IndexProductModelCommand`
- Move `Pim\Bundle\CatalogBundle\Command\PurgeCompletenessCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\PurgeCompletenessCommand`
- Move `Pim\Bundle\CatalogBundle\Command\PurgeProductsCompletenessCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\PurgeProductsCompletenessCommand`
- Move `Pim\Bundle\CatalogBundle\Command\QueryHelpProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\QueryHelpProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\QueryHelpProductModelCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\QueryHelpProductModelCommand`
- Move `Pim\Bundle\CatalogBundle\Command\QueryProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\QueryProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\RefreshProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\RefreshProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\RemoveCompletenessForChannelAndLocaleCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\RemoveCompletenessForChannelAndLocaleCommand`
- Move `Pim\Bundle\CatalogBundle\Command\RemoveProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\RemoveProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\RemoveWrongBooleanValuesOnVariantProductsBatchCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\RemoveWrongBooleanValuesOnVariantProductsBatchCommand`
- Move `Pim\Bundle\CatalogBundle\Command\RemoveWrongBooleanValuesOnVariantProductsCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\RemoveWrongBooleanValuesOnVariantProductsCommand`
- Move `Pim\Bundle\CatalogBundle\Command\UpdateProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\UpdateProductCommand`
- Move `Pim\Bundle\CatalogBundle\Command\ValidateObjectsCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\ValidateObjectsCommand`
- Move `Pim\Bundle\CatalogBundle\Command\ValidateProductCommand` to `Akeneo\Pim\Enrichment\Bundle\Command\ValidateProductCommand`

- Move `Pim\Bundle\CatalogBundle\EventSubscriber\AddUniqueAttributesToVariantProductAttributeSetSubscriber` to `Akeneo\Pim\Structure\Bundle\EventSubscriber\AddUniqueAttributesToVariantProductAttributeSetSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber\ComputeFamilyVariantStructureChangesSubscriber` to `Akeneo\Pim\Structure\Bundle\EventSubscriber\ComputeFamilyVariantStructureChangesSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber\RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber` to `Akeneo\Pim\Structure\Bundle\EventSubscriber\RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber` to `Akeneo\Pim\Structure\Bundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber`
- Move `Pim\Component\Catalog\Entity\GroupTranslation` to `Akeneo\Pim\Enrichment\Component\Category\Entity\GroupTranslation`
- Move `Pim\Component\Catalog\Model\GroupTranslationInterface` to `Akeneo\Pim\Enrichment\Component\Category\Model\GroupTranslationInterface`
- Move `Pim\Component\Catalog\Entity\Group` to `Akeneo\Pim\Enrichment\Component\Category\Entity\Group`
- Move `Pim\Component\Catalog\Model\GroupInterface` to `Akeneo\Pim\Enrichment\Component\Category\Model\GroupInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\Category` to `Akeneo\Pim\Enrichment\Component\Category\Model\Category`
- Move `Pim\Bundle\CatalogBundle\Entity\CategoryTranslation` to `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslation`
- Move `Pim\Component\Catalog\Model\CategoryTranslationInterface` to `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslationInterface`
- Move `Pim\Component\Catalog\Entity\Category` to `Akeneo\Pim\Enrichment\Component\Category\Model\Category`
- Move `Pim\Component\Catalog\Model\CategoryInterface` to `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface`
- Move `Pim\Component\Catalog\Model\AbstractAssociation` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation`
- Move `Pim\Component\Catalog\Model\AssociationInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface`
- Move `Pim\Component\Catalog\Model\ProductModelAssociation` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation`
- Move `Pim\Component\Catalog\Model\ProductModelAssociationInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface`
- Move `Pim\Component\Catalog\Model\ProductAssociation` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation`
- Move `Pim\Component\Catalog\Model\ProductAssociationInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociationInterface`
- Move `Pim\Component\Catalog\Model\ProductUniqueDataInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface`
- Move `Pim\Component\Catalog\Model\AbstractProductUniqueData` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProductUniqueData`
- Move `Pim\Component\Catalog\Model\ProductUniqueData` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueData`
- Move `Pim\Component\Catalog\Model\CompletenessInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface`
- Move `Pim\Component\Catalog\Model\AbstractCompleteness` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractCompleteness`
- Move `Pim\Component\Catalog\Model\Completeness` to `Akeneo\Pim\Enrichment\Component\Product\Model\Completeness`
- Move `Pim\Component\Catalog\Model\ValueInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface`
- Move `Pim\Component\Catalog\Model\ProductModel` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel`
- Move `Pim\Component\Catalog\Model\ProductModelInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface`
- Move `Pim\Component\Catalog\Model\Product` to `Akeneo\Pim\Enrichment\Component\Product\Model\Product`
- Move `Pim\Component\Catalog\Model\AbstractProduct` to `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct`
- Move `Pim\Component\Catalog\Model\ProductInterface` to `Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyVariantRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyVariantRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository`
- Move `Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\FamilyRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeGroupRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeGroupRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\FamilyVariantsByAttributeAxes` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FamilyVariantsByAttributeAxes`
- Move `Pim\Component\Catalog\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface` to `Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface`
- Move `Pim\Component\Catalog\FamilyVariant\AddUniqueAttributes` to `Akeneo\Pim\Structure\Component\FamilyVariant\AddUniqueAttributes`
- Move `Pim\Component\Catalog\Factory\FamilyFactory` to `Akeneo\Pim\Structure\Component\Factory\FamilyFactory`
- Move `Pim\Component\Catalog\Factory\AttributeRequirementFactory` to `Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory`
- Move `Pim\Component\Catalog\Factory\AttributeFactory` to `Akeneo\Pim\Structure\Component\Factory\AttributeFactory`
- Move `Pim\Component\Catalog\Validator\Constraints\ActivatedLocale` to `Akeneo\Channel\Component\Validator\Constraint\ActivatedLocale`
- Move `Pim\Component\Catalog\Validator\Constraints\Locale` to `Akeneo\Channel\Component\Validator\Constraint\Locale`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\LocaleRepository` to `Akeneo\Channel\Bundle\Doctrine\Repository\LocaleRepository`
- Move `Pim\Component\Catalog\Repository\LocaleRepositoryInterface` to `Akeneo\Channel\Component\Repository\LocaleRepositoryInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\Locale` to `Akeneo\Channel\Component\Model\Locale`
- Move `Pim\Component\Catalog\Model\LocaleInterface` to `Akeneo\Channel\Component\Model\LocaleInterface`
- Move `Pim\Bundle\UserBundle\Entity\UserInterface` to `Akeneo\UserManagement\Component\Model\UserInterface`
- Move `Pim\Bundle\UserBundle\Entity\User` to `Akeneo\UserManagement\Component\Model\User`
- Move `Oro\Bundle\UserBundle\Entity\Group` to `Akeneo\UserManagement\Component\Model\Group`
- Move `Oro\Bundle\UserBundle\Entity\Role` to `Akeneo\UserManagement\Component\Model\Role`
- Move `Oro\Bundle\UserBundle\Entity\UserManager` to `Akeneo\UserManagement\Bundle\Manager\UserManager`
- Move `Oro\Bundle\UserBundle\OroUserEvents` to `Akeneo\UserManagement\Component\UserEvents`
- Move `Pim\Bundle\UserBundle\Bundle\Controller\UserGroupRestController` to `Akeneo\UserManagement\Bundle\Controller\Rest\UserGroupController`
- Move `Pim\Bundle\UserBundle\Bundle\Controller\SecurityRestController` to `Akeneo\UserManagement\Bundle\Controller\Rest\SecurityController`
- Move `Pim\Bundle\UserBundle\Bundle\Controller\UserRestController` to `Akeneo\UserManagement\Bundle\Controller\Rest\UserController`
- Move all classes from `Oro\Bundle\UserBundle\Controller` to `Akeneo\UserManagement\Bundle\Controller`
- Move all classes from `Oro\Bundle\UserBundle\EventListener` to `Akeneo\UserManagement\Bundle\EventListener`
- Move all classes from `Oro\Bundle\UserBundle\Form\EventListener` to `Akeneo\UserManagement\Bundle\Form\Subscriber`
- Move all classes from `Oro\Bundle\UserBundle\Entity\Repository` to `Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository`
- Move `Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface` to `Akeneo\UserManagement\Component\EntityUploadedImageInterface`
- Move `Oro\Bundle\UserBundle\Entity\EventListener\UploadedImageSubscriber` to `Akeneo\UserManagement\Bundle\EventSubscriber\UploadedImageSubscriber`
- Move `Oro\Bundle\UserBundle\Form\Handler\AbstractUserHandler` to `Akeneo\UserManagement\Bundle\Form\Handler\AbstractUserHandler`
- Move `Oro\Bundle\UserBundle\Form\Handler\GroupHandler` to `Akeneo\UserManagement\Bundle\Form\Handler\GroupHandler`
- Move `Oro\Bundle\UserBundle\Form\Type\GroupApiType` to `Akeneo\UserManagement\Bundle\Form\Type\GroupApiType`
- Move `Oro\Bundle\UserBundle\Form\Type\GroupType` to `Akeneo\UserManagement\Bundle\Form\Type\GroupType`
- Move `Oro\Bundle\UserBundle\Form\Type\ResetType` to `Akeneo\UserManagement\Bundle\Form\Type\ResetType`
- Move `Oro\Bundle\UserBundle\Security\UserProvider` to `Akeneo\UserManagement\Bundle\Security\UserProvider`
- Move `Pim\Bundle\UserBundle` to `Akeneo\UserManagement\Bundle`
- Move `Pim\Component\User` to `Akeneo\UserManagement\Component`
- Merge `Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler` with `Akeneo\UserManagement\Bundle\Form\Handler\AclRoleHandler`
- Merge `Oro\Bundle\UserBundle\Form\Handler\ResetHandler` with `Akeneo\UserManagement\Bundle\Form\Handler\ResetHandler`
- Merge `Oro\Bundle\UserBundle\Form\Handler\UserHandler` with `Akeneo\UserManagement\Bundle\Form\Handler\UserHandler`
- Merge `Oro\Bundle\UserBundle\Form\Type\AclRoleType` with `Akeneo\UserManagement\Bundle\Form\Type\AclRoleType`
- Merge `Oro\Bundle\UserBundle\Form\Type\RoleApiType` with `Akeneo\UserManagement\Bundle\Form\Type\RoleApiType`
- Merge `Oro\Bundle\UserBundle\Entity\UserManager` with `Akeneo\UserManagement\Bundle\Manager\UserManager`

- Remove `Oro\Bundle\UserBundle\OroUserBundle`
- Remove `Oro\Bundle\UserBundle\DependencyInjection`
- Remove `Oro\Bundle\UserBundle\Form\Type\ChangePasswordType`
- Remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Remove `Pim\Component\Connector\Job\ComputeDataRelatedToFamilyVariantsTasklet`\
- Remove 2 service definitions `pim_connector.tasklet.csv_family.compute_data_related_to_family_variants` and `pim_connector.tasklet.xlsx_family.compute_data_related_to_family_variants`\
- Remove 2 job steps `pim_connector.step.csv_family.compute_data_related_to_family_variants` and `pim_connector.step.xlsx_family.compute_data_related_to_family_variants`
- Remove service definition `pim_enrich.mass_edit_action.operation_job_launcher`

- Change constructor of `Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridProvider`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument
- Change constructor of `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\JobExecutionNormalizer`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\StepExecutionNormalizer`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument
- Change constructor of `Akeneo\UserManagement\Bundle\Form\Type\UserType`, remove `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` and `Akeneo\UserManagement\Bundle\Form\Subscriber\UserSubscriber` argument
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductModelDescendantsSaver` to add `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` and a `batchSize` parameter.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeCompletenessOnFamilyUpdateSubscriber` to add `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\FindAttributesForFamily` argument
- Change constructor of `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeOptionSearchableRepository`, add `$attributeRepository` argument
- Change constructor of `Pim\Component\Catalog\Job\ComputeCompletenessOfProductsFamilyTasklet`, replace `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` by `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change constructor of `Pim\Component\Catalog\Job\ComputeProductModelsDescendantsTasklet`, add `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface` argument
- Change constructor of `Pim\Component\Connector\Job\ComputeDataRelatedToFamilyProductsTasklet`, add `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation`, `Symfony\Component\Validator\Validator\ValidatorInterface` and a batch size arguments.

- Move namespace `Pim\Component\Catalog\Value` to `Akeneo\Pim\Enrichment\Component\Product\Value`
- Move namespace `Pim\Component\Api` to `Akeneo\Tool\Component\Api`
- Move namespace `Pim\Bundle\ApiBundle` to `Akeneo\Tool\Bundle\ApiBundle`
- Move namespace `Pim\Component\Batch` to `Akeneo\Tool\Component\Batch`
- Move namespace `Pim\Bundle\BatchBundle` to `Akeneo\Tool\Bundle\BatchBundle`
- Move namespace `Pim\Component\BatchQueue` to `Akeneo\Tool\Component\BatchQueue`
- Move namespace `Pim\Bundle\BatchQueueBundle` to `Akeneo\Tool\Bundle\BatchQueueBundle`
- Move namespace `Pim\Component\StorageUtilsQueue` to `Akeneo\Tool\Component\StorageUtilsQueue`
- Move namespace `Pim\Bundle\StorageUtilsQueueBundle` to `Akeneo\Tool\Bundle\StorageUtilsQueueBundle`
- Move namespace `Pim\Bundle\ElasticsearchBundle` to `Akeneo\Tool\Bundle\ElasticsearchBundle`
- Move namespace `Pim\Component\Analytics` to `Akeneo\Tool\Component\Analytics`
- Move namespace `Pim\Component\Buffer` to `Akeneo\Tool\Component\Buffer`
- Move namespace `Pim\Component\Console` to `Akeneo\Tool\Component\Console`
- Move namespace `Pim\Component\Localization` to `Akeneo\Tool\Component\Localization`
- Move namespace `Pim\Component\Versioning` except Normalizers to `Akeneo\Tool\Component\Versioning`
- Move namespace `Pim\Bundle\MeasureBundle` to `Akeneo\Tool\Bundle\MeasureBundle`
- Move namespace `Pim\Component\FileStorage` to `Akeneo\Tool\Component\FileStorage`
- Move namespace `Pim\Bundle\FileStorageBundle` to `Akeneo\Tool\Bundle\FileStorageBundle`
- Move namespace `Pim\Component\Classification` to `Akeneo\Tool\Component\Classification`
- Move namespace `Pim\Bundle\ClassificationBundle` to `Akeneo\Tool\Bundle\ClassificationBundle`
- Move namespace `Pim\Bundle\BufferBundle` to `Akeneo\Tool\Bundle\BufferBundle`
- Move `Pim\Bundle\ApiBundle\Controller\ChannelController` to `Akeneo\Channel\Bundle\Controller\ExternalApi\ChannelController`
- Move `Pim\Bundle\ApiBundle\Controller\ChannelController` to `Akeneo\Channel\Bundle\Controller\ExternalApi\ChannelController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\ChannelController` to `Akeneo\Channel\Bundle\Controller\InternalApi\ChannelController`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\ChannelRemover` to `Akeneo\Channel\Bundle\Doctrine\Remover\ChannelRemover`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ChannelRepository` to `Akeneo\Channel\Bundle\Doctrine\Repository\ChannelRepository`
- Move `Pim\Bundle\EnrichBundle\EventListener\Storage\ChannelLocaleSubscriber` to `Akeneo\Channel\Bundle\EventListener\ChannelLocaleSubscriber`
- Change constructor of `Akeneo\Channel\Bundle\EventListener\ChannelLocaleSubscriber`, remove `Pim\Component\Catalog\Completeness\CompletenessRemoverInterface` argument
- Move `Pim\Bundle\CatalogBundle\Entity\Channel` to `Akeneo\Channel\Component\Model\Channel`
- Move `Pim\Component\Catalog\Model\ChannelInterface` to `Akeneo\Channel\Component\Model\ChannelInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\ChannelTranslation` to `Akeneo\Channel\Component\Model\ChannelTranslation`
- Move `Pim\Component\Catalog\Model\ChannelTranslationInterface` to `Akeneo\Channel\Component\Model\ChannelTranslationInterface`
- Move `Akeneo\Tool\Component\Api\Normalizer\ChannelNormalizer` to `Akeneo\Channel\Component\Normalizer\ExternalApi\ChannelNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\ChannelNormalizer` to `Akeneo\Channel\Component\Normalizer\InternalApi\ChannelNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\ChannelNormalizer` to `Akeneo\Channel\Component\Normalizer\Standard\ChannelNormalizer`
- Move `Pim\Bundle\VersioningBundle\Normalizer\Flat\ChannelNormalizer` to `Akeneo\Channel\Component\Normalizer\Versioning\ChannelNormalizer`
- Move `Pim\Component\Catalog\Repository\ChannelRepositoryInterface` to `Akeneo\Channel\Component\Repository\ChannelRepositoryInterface`
- Move `Pim\Component\Catalog\Updater\ChannelUpdater` to `Akeneo\Channel\Component\Updater\ChannelUpdater`
- Move `Pim\Component\Catalog\Updater\LocaleUpdater` to `Akeneo\Channel\Component\Updater\LocaleUpdater`
- Move `Akeneo\Tool\Component\Api\Normalizer\LocaleNormalizer` to `Akeneo\Channel\Component\Normalizer\ExternalApi\LocaleNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\LocaleNormalizer` to `Akeneo\Channel\Component\Normalizer\InternalApi\LocaleNormalizer`
- Move `Pim\Bundle\VersioningBundle\Normalizer\Flat` to `Akeneo\Channel\Component\Normalizer\Versioning`
- Move `Pim\Component\Catalog\Model\CurrencyInterface` to `Akeneo\Channel\Component\Model\CurrencyInterface`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\CurrencyRepository` to `Akeneo\Channel\Bundle\Doctrine\Repository\CurrencyRepository`
- Move `Pim\Bundle\CatalogBundle\EventSubscriber\CurrencyDisablingSubscriber` to `Akeneo\Channel\Bundle\EventListener\CurrencyDisablingSubscriber`
- Move `Akeneo\Tool\Component\Api\Normalizer\CurrencyNormalizer` to `Akeneo\Tool\Component\Api\Normalizer\CurrencyNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\CurrencyNormalizer` to `Akeneo\Channel\Component\Normalizer\Standard\CurrencyNormalizer`
- Move `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface` to `Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface`
- Move `Pim\Component\Catalog\Updater\CurrencyUpdater` to `Akeneo\Channel\Component\Updater\CurrencyUpdater`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Channel` to `Akeneo\Channel\Component\ArrayConverter\FlatToStandard\Channel`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Locale` to `Akeneo\Channel\Component\ArrayConverter\FlatToStandard\Locale`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Currency` to `Akeneo\Channel\Component\ArrayConverter\FlatToStandard\Currency`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Channel` to `Akeneo\Channel\Component\ArrayConverter\StandardToFlat\Channel`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Locale` to `Akeneo\Channel\Component\ArrayConverter\StandardToFlat\Locale`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Currency` to `Akeneo\Channel\Component\ArrayConverter\StandardToFlat\Currency`
- Move `Pim\Component\Catalog\Exception\LinkedChannelException` to `Akeneo\Channel\Component\Exception\LinkedChannelException`
- Move `Pim\Component\Catalog\Model\ReferableInterface` to `Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface`
- Remove method `getChoiceValue` and `getChoiceLabel` from `Akeneo\Channel\Component\Model\Channel`
- Change the constructor of `Akeneo\Channel\Component\Normalizer\InternalApi\ChannelNormalizer` to replace `Pim\Bundle\VersioningBundle\Manager\VersionManager` by `Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface`
- Change the constructor of `Akeneo\UserManagement\Bundle\Context\UserContext` to remove `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Remove class `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilder`
- Remove class `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Remove class `Pim\Bundle\PdfGeneratorBundle\Twig\ImageExtension`
- Move `Pim\Bundle\CatalogBundle\Entity\Attribute` to `Akeneo\Pim\Structure\Component\Model\Attribute`
- Move `Pim\Component\Catalog\Model\AbstractAttribute` to `Akeneo\Pim\Structure\Component\Model\AbstractAttribute`
- Move `Pim\Component\Catalog\Model\AttributeInterface` to `Akeneo\Pim\Structure\Component\Model\AttributeInterface`
- Move `Pim\Component\Catalog\Model\AttributeOptionInterface` to `Akeneo\Pim\Structure\Component\Model\AttributeInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\AttributeOption` to `Akeneo\Pim\Structure\Component\Model\AttributeOption`
- Move `Pim\Component\Catalog\Model\FamilyInterface` to `Akeneo\Pim\Structure\Component\Model\FamilyInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\Family` to `Akeneo\Pim\Structure\Component\Model\Family`
- Move `Pim\Bundle\CatalogBundle\Entity\FamilyTranslation` to `Akeneo\Pim\Structure\Component\Model\FamilyTranslation`
- Move `Pim\Component\Catalog\Model\FamilyTranslationInterface` to `Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface`
- Move `Pim\Component\Catalog\Model\FamilyVariantInterface` to `Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface`
- Move `Pim\Component\Catalog\Model\FamilyVariant` to `Akeneo\Pim\Structure\Component\Model\FamilyVariant`
- Move `Pim\Component\Catalog\Model\FamilyVariantTranslation` to `Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslation`
- Move `Pim\Component\Catalog\Model\FamilyVariantTranslationInterface` to `Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslationInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\AttributeRequirement` to `Akeneo\Pim\Structure\Component\Model\AttributeRequirement`
- Move `Pim\Component\Catalog\Model\AttributeRequirementInterface` to `Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\AttributeGroup` to `Akeneo\Pim\Structure\Component\Model\AttributeGroup`
- Move `Pim\Component\Catalog\Model\AttributeGroupInterface` to `Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation` to `Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslation`
- Move `Pim\Component\Catalog\Model\AttributeGroupTranslationInterface` to `Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslationInterface`
- Move `Pim\Bundle\CatalogBundle\Entity\AttributeTranslation` to `Akeneo\Pim\Structure\Component\Model\AttributeTranslation`
- Move `Pim\Component\Catalog\Model\AttributeTranslationInterface` to `Akeneo\Pim\Structure\Component\Model\AttributeTranslationInterface`
- Move `Pim\Component\Catalog\Model\VariantAttributeSet` to `Akeneo\Pim\Structure\Component\Model\VariantAttributeSet`
- Move `Pim\Component\Catalog\Model\VariantAttributeSetInterface` to `Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface`
- Move `Pim\Component\Catalog\Updater\AttributeUpdater` to `Akeneo\Pim\Structure\Component\Updater\AttributeUpdater`
- Move `Pim\Component\Catalog\Updater\AttributeOptionUpdater` to `Akeneo\Pim\Structure\Component\Updater\AttributeOptionUpdater`
- Move `Pim\Component\Catalog\Updater\FamilyUpdater` to `Akeneo\Pim\Structure\Component\Updater\FamilyUpdater`
- Move `Akeneo\Tool\Component\Api\Updater\FamilyVariantUpdater` to `Akeneo\Pim\Structure\Component\Updater\ExternalApi\FamilyVariantUpdater`
- Move `Pim\Component\Catalog\Updater\FamilyVariantUpdater` to `Akeneo\Pim\Structure\Component\Updater\FamilyVariantUpdater`
- Move `Akeneo\Tool\Component\Api\Normalizer\AttributeGroupNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeGroupNormalizer`
- Move `Akeneo\Tool\Component\Api\Normalizer\AttributeNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeNormalizer`
- Move `Akeneo\Tool\Component\Api\Normalizer\AttributeOptionNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeOptionNormalizer`
- Move `Akeneo\Tool\Component\Api\Normalizer\FamilyNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\FamilyNormalizer`
- Move `Akeneo\Tool\Component\Api\Normalizer\FamilyVariantNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\FamilyVariantNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Indexing\FamilyNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Indexing\FamilyNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\AttributeGroupNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeGroupNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeOptionNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\FamilyNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\FamilyNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\FamilyVariantNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\FamilyVariantNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\AttributeGroupNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeGroupNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\AttributeNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\AttributeOptionNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeOptionNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\FamilyNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Standard\FamilyNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Standard\FamilyVariantNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Standard\FamilyVariantNormalizer`
- Move `Pim\Component\Catalog\Normalizer\Storage\AttributeOptionNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Storage\AttributeOptionNormalizer`
- Move `Pim\Bundle\VersioningBundle\Normalizer\Flat\AttributeGroupNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeGroupNormalizer`
- Move `Pim\Bundle\VersioningBundle\Normalizer\Flat\AttributeNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeNormalizer`
- Move `Pim\Bundle\VersioningBundle\Normalizer\Flat\AttributeOptionNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeOptionNormalizer`
- Move `Pim\Bundle\VersioningBundle\Normalizer\Flat\FamilyNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\Versioning\FamilyNormalizer`
- Move `Pim\Bundle\ApiBundle\Controller\AttributeController` to `Akeneo\Pim\Structure\Bundle\Controller\ExternalApi\AttributeController`
- Move `Pim\Bundle\ApiBundle\Controller\AttributeGroupController` to `Akeneo\Pim\Structure\Bundle\Controller\ExternalApi\AttributeGroupController`
- Move `Pim\Bundle\ApiBundle\Controller\AttributeOptionController` to `Akeneo\Pim\Structure\Bundle\Controller\ExternalApi\AttributeOptionController`
- Move `Pim\Bundle\ApiBundle\Controller\FamilyController` to `Akeneo\Pim\Structure\Bundle\Controller\ExternalApi\FamilyController`
- Move `Pim\Bundle\ApiBundle\Controller\FamilyVariantController` to `Akeneo\Pim\Structure\Bundle\Controller\ExternalApi\FamilyVariantController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\AttributeController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeGroupController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\AttributeGroupController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeOptionController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\AttributeOptionController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\FamilyController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\FamilyController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\FamilyVariantController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\FamilyVariantController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeTypeController` to `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\AttributeTypeController`
- Move `Pim\Component\Catalog\AttributeTypeRegistry` to `Akeneo\Pim\Structure\Component\AttributeTypeRegistry`
- Remove class `Pim\Bundle\EnrichBundle\Controller\FamilyController`
- Remove class `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeOptionController`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeOptionRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRequirementRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRequirementRepository`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\AttributeSaver` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver`
- Move `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\FamilySaver` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver`
- Move `Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface`
- Move `Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface`
- Move `Pim\Bundle\ApiBundle\Doctrine\ORM\Repository\AttributeRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\AttributeRepository`
- Move `Pim\Component\Api\Repository\AttributeRepositoryInterface` to `Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeGroupRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeGroupRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeOptionSearchableRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeOptionSearchableRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeSearchableRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeSearchableRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilyRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\FamilyRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilySearchableRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\FamilySearchableRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilyVariantRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\FamilyVariantRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilyVariantSearchableRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\FamilyVariantSearchableRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeGroupSearchableRepository` to `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeGroupSearchableRepository`
- Move namespace `Pim\Bundle\CatalogBundle\AttributeType` to `Akeneo\Pim\Structure\Component\AttributeType`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Attribute` to `Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\Attribute`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\FlatToStandard\AttributeGroup` to `Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\AttributeGroup`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\FlatToStandard\AttributeOption` to `Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\AttributeOption`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\FlatToStandard\Family` to `Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\Family`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\FlatToStandard\FamilyVariant` to `Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\FamilyVariant`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\FlatToStandard\Attribute` to `Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\Attribute`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\FlatToStandard\AttributeGroup` to `Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\AttributeGroup`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\FlatToStandard\AttributeOption` to `Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\AttributeOption`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\FlatToStandard\Family` to `Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\Family`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\FlatToStandard\FamilyVariant\FamilyVariant` to `Akeneo\Pim\Structure\Component\ArrayConverter\FamilyVariant\StandardToFlat\FamilyVariant`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\FlatToStandard\FamilyVariant\FieldSplitter` to `Akeneo\Pim\Structure\Component\ArrayConverter\FamilyVariant\StandardToFlat\FieldSplitter`
- Move `Pim\Component\Connector\Reader\Database\AttributeOptionReader` to `Akeneo\Pim\Structure\Component\Reader\Database\AttributeOptionReader`
- Move `Pim\Component\Connector\Writer\Database\AttributeGroupWriter` to `Akeneo\Pim\Structure\Component\Writer\Database\AttributeGroupWriter`
- Move `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeTypePass` to `Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass`
- Move `Pim\Component\Catalog\Manager\AttributeOptionsSorter` to `Akeneo\Pim\Structure\Component\Manager\AttributeOptionsSorter`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsImage` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsImage`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsImageValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsImageValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsLabel` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsLabel`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsLabelValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsLabelValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeUsedAsAxis` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxis`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeUsedAsAxisValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxisValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyRequirements` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyRequirements`
- Move `Pim\Component\Catalog\Validator\Constraints\FamilyRequirementsValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyRequirementsValidator`
- Move `Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxes` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxes`
- Move `Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxesValidator` to `Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxesValidator`
- Remove `Pim\Component\Catalog\Model\AttributeTypeTranslationInterface`
- Move namespace `Pim\Bundle\VersioningBundle` to `Akeneo\Tool\Bundle\VersioningBundle`
- Remove `Pim\Bundle\PdfGeneratorBundle\PimPdfGeneratorBundle`
- Remove `Pim\Bundle\PdfGeneratorBundle\DependencyInjection\PimPdfGeneratorExtension`
- Move `Pim\Bundle\PdfGeneratorBundle\DependencyInjection\Compiler\RegisterRendererPass` to `Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterRendererPass`
- Move `Pim\Bundle\PdfGeneratorBundle\Builder\DompdfBuilder` to `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\DompdfBuilder`
- Move `Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface` to `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface`
- Move `Pim\Bundle\PdfGeneratorBundle\Exception\RendererRequiredException` to `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Exception\RendererRequiredException`
- Move `Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer` to `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer`
- Move `Pim\Bundle\PdfGeneratorBundle\Renderer\RendererInterface` to `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererInterface`
- Move `Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry` to `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererRegistry`
- Remove `Pim\Bundle\CommentBundle\PimCommentBundle`
- Remove `Pim\Bundle\CommentBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass`
- Remove `Pim\Bundle\CommentBundle\DependencyInjection\PimCommentExtension`
- Move `Pim\Bundle\CommentBundle\Controller\CommentController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\CommentController`
- Move `Pim\Bundle\CommentBundle\Repository\CommentRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\CommentRepository`
- Move `Pim\Bundle\CommentBundle\Form\Type\CommentType` to `Akeneo\Pim\Enrichment\Bundle\Form\Type\CommentType`
- Move `Pim\Bundle\CommentBundle\Builder\CommentBuilder` to `Akeneo\Pim\Enrichment\Component\Comment\Builder\CommentBuilder`
- Move `Pim\Bundle\CommentBundle\Entity\Comment` to `Akeneo\Pim\Enrichment\Component\Comment\Model\Comment`
- Move `Pim\Bundle\CommentBundle\Model\CommentInterface` to `Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface`
- Move `Pim\Bundle\CommentBundle\Model\CommentSubjectInterface` to `Akeneo\Pim\Enrichment\Component\Comment\Model\CommentSubjectInterface`
- Move `Pim\Bundle\CommentBundle\Normalizer\Standard\CommentNormalizer` to `Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard\CommentNormalizer`
- Move `Pim\Bundle\CommentBundle\Repository\CommentRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Comment\Repository\CommentRepositoryInterface`
- Move `Akeneo\Tool\Bundle\ApiBundle\Controller\CurrencyController` to `Akeneo\Channel\Bundle\Controller\ExternalApi\CurrencyController`
- Move `Akeneo\Tool\Bundle\ApiBundle\Controller\CategoryController` to `Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\CategoryController`
- Move `Akeneo\Tool\Bundle\ApiBundle\Controller\ProductController` to `Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductController`
- Move `Akeneo\Tool\Bundle\ApiBundle\Controller\ProductModelController` to `Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductModelController`
- Move `Akeneo\Tool\Bundle\ApiBundle\Doctrine\ORM\Repository\ProductRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ExternalApi\ProductRepository`
- Move `Akeneo\Tool\Component\Api\Normalizer\CategoryNormalizer` to `Akeneo\Pim\Enrichment\Component\Category\Normalizer\ExternalApi\CategoryNormalizer`
- Move `Akeneo\Tool\Component\Api\Normalizer\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ProductNormalizer`
- Move `Akeneo\Tool\Component\Api\Normalizer\ProductModelNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ProductModelNormalizer`
- Move `Akeneo\Tool\Component\Api\Repository\ProductRepositoryInterface` to `Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface`
- Move `Akeneo\Tool\Component\Api\Updater\ProductModelUpdater` to `Akeneo\Pim\Enrichment\Component\Product\ExternalApi\Updater\ProductModelUpdater`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Category` to `Akeneo\Pim\Enrichment\Component\Category\Connector\ArrayConverter\FlatToStandard\Category`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Category` to `Akeneo\Pim\Enrichment\Component\Category\Connector\ArrayConverter\StandardToFlat\Category`
- Move `Pim\Component\Connector\Reader\Database\StandardToFlat\CategoryReader` to `Akeneo\Pim\Enrichment\Component\Category\Connector\Reader\Database\CategoryReader`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMerger`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMapper`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\ConvertedField` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\EntityWithValuesDelocalized` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\EntityWithValuesDelocalized`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Group` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Group`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Product`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductAssociation` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductAssociation`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldConverter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverter`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModelAssociation` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModelAssociation`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\Value` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Value`
- Move namespace `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Group` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Group`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Product` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\MediaConverter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\MediaConverter`
- Move namespace `Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\ProductLocalized` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\ProductLocalized`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\ProductModel` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\ProductModel`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductCsvExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductCsvExport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductCsvImport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductCsvImport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductModelCsvExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductModelCsvExport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductModelCsvImport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductModelCsvImport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductXlsxExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductXlsxExport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductCsvImport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductCsvImport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductModelCsvExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductModelCsvExport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductModelCsvImport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductModelCsvImport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductXlsxExport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductXlsxExport`
- Move `Pim\Component\Connector\Processor\Denormalization\Product\FindProductToImport` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport`
- Move `Pim\Component\Connector\Processor\Denormalization\ProductAssociationProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductAssociationProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\ProductModelAssociationProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelAssociationProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\ProductModelLoaderProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelLoaderProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\ProductModelProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\ProductProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor`
- Move `Pim\Component\Connector\Processor\Normalization\ProductProcessor` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor`
- Move namespace`Pim\Component\Connector\Analyzer` to `Akeneo\Pim\Enrichment\Component\Product\Connector`
- Move `Pim\Component\Connector\Writer\File\ProductColumnSorter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\ProductColumnSorter`
- Move `Pim\Component\Connector\Reader\Database\GroupReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\GroupReader`
- Move `Pim\Component\Connector\Reader\Database\ProductReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader`
- Move `Pim\Component\Connector\Reader\File\Csv\ProductAssociationReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductAssociationReader`
- Move `Pim\Component\Connector\Reader\File\Csv\ProductModelReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductModelReader`
- Move `Pim\Component\ConnectoAkeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard\Userr\Reader\File\Csv\ProductReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductReader`
- Move `Pim\Component\Connector\Reader\File\Xlsx\ProductAssociationReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx\ProductAssociationReader`
- Move `Pim\Component\Connector\Reader\File\Xlsx\ProductModelReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx\ProductModelReader`
- Move `Pim\Component\Connector\Reader\File\Xlsx\ProductReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx\ProductReader`
- Move `Pim\Component\Connector\Reader\File\Xlsx\ProductReader` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx\ProductReader`
- Move `Pim\Component\Connector\Writer\Database\ProductAssociationWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductAssociationWriter`
- Move `Pim\Component\Connector\Writer\Database\ProductModelDescendantsWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelDescendantsWriter`
- Move `Pim\Component\Connector\Writer\Database\ProductModelWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelWriter`
- Move `Pim\Component\Connector\Writer\Database\ProductWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductWriter`
- Move `Pim\Component\Connector\Writer\File\Csv\ProductModelWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductModelWriter`
- Move `Pim\Component\Connector\Writer\File\Csv\ProductWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductWriter`
- Move `Pim\Component\Connector\Writer\File\Xlsx\ProductModelWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductModelWriter`
- Move `Pim\Component\Connector\Writer\File\Xlsx\ProductWriter` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductWriter`
- Move namespace `Pim\Component\Connector\Validator\Constraints` to `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints`
- Move `Pim\Component\Connector\ArrayConverter\FlatToStandard\User` to `Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard\User`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\User` to `Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat\User`
- Move `Pim\Component\Connector\Job\ComputeDataRelatedToFamilyProductsTasklet` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeDataRelatedToFamilyProductsTasklet`
- Move `Pim\Component\Connector\Job\ComputeDataRelatedToFamilyRootProductModelsTasklet` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeDataRelatedToFamilyRootProductModelsTasklet`
- Move `Pim\Component\Connector\Job\ComputeDataRelatedToFamilySubProductModelsTasklet` to `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeDataRelatedToFamilySubProductModelsTasklet`
- Move `Pim\Component\Connector\Analyzer\AnalyzerInterface` to `Akeneo\Tool\Component\Connector\Analyzer\AnalyzerInterface`
- Move `Pim\Component\Connector\Archiver\AbstractInvalidItemWriter` to `Akeneo\Tool\Component\Connector\Archiver\AbstractInvalidItemWriter`
- Move `Pim\Component\Connector\Archiver\AbstractFilesystemArchiver` to `Akeneo\Tool\Component\Connector\Archiver\AbstractFilesystemArchiver`
- Move `Pim\Component\Connector\Archiver\ArchivableFileWriterArchiver` to `Akeneo\Tool\Component\Connector\Archiver\ArchivableFileWriterArchiver`
- Move `Pim\Component\Connector\Archiver\ArchiverInterface` to `Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface`
- Move `Pim\Component\Connector\Archiver\CsvInvalidItemWriter` to `Akeneo\Tool\Component\Connector\Archiver\CsvInvalidItemWriter`
- Move `Pim\Component\Connector\Archiver\FileReaderArchiver` to `Akeneo\Tool\Component\Connector\Archiver\FileReaderArchiver`
- Move `Pim\Component\Connector\Archiver\FileWriterArchiver` to `Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver`
- Move `Pim\Component\Connector\Archiver\XlsxInvalidItemWriter` to `Akeneo\Tool\Component\Connector\Archiver\XlsxInvalidItemWriter`
- Move `Pim\Component\Connector\Archiver\ZipFilesystemFactory` to `Akeneo\Tool\Component\Connector\Archiver\XlsxInvalidItemWriter`
- Move `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface` to `Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface`
- Move `Pim\Component\Connector\ArrayConverter\DummyConverter` to `Akeneo\Tool\Component\Connector\ArrayConverter\DummyConverter`
- Move `Pim\Component\Connector\ArrayConverter\FieldSplitter` to `Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter`
- Move `Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker` to `Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker`
- Move `Pim\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter` to `Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter`
- Move `Pim\Component\Connector\Encoder\CsvEncoder` to `Akeneo\Tool\Component\Connector\Encoder\CsvEncoder`
- Move `Pim\Component\Connector\Exception\ArrayConversionException` to `Akeneo\Tool\Component\Connector\Exception\ArrayConversionException`
- Move `Pim\Component\Connector\Exception\CharsetException` to `Akeneo\Tool\Component\Connector\Exception\CharsetException`
- Move `Pim\Component\Connector\Exception\DataArrayConversionException` to `Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException`
- Move `Pim\Component\Connector\Exception\InvalidItemFromViolationsException` to `Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException`
- Move `Pim\Component\Connector\Exception\StructureArrayConversionException` to `Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException`
- Move `Pim\Component\Connector\Item\CharsetValidator` to `Akeneo\Tool\Component\Connector\Item\CharsetValidator`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleCsvExport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleCsvExport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleCsvImport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleCsvImport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleXlsxExport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleXlsxExport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleXlsxImport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleXlsxImport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleYamlExport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleYamlExport`
- Move `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleYamlImport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleYamlImport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvImport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvImport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleXlsxExport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleXlsxExport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleXlsxImport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleXlsxImport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleYamlExport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleYamlExport`
- Move `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleYamlImport` to `Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleYamlImport`
- Move `Pim\Component\Connector\Processor\BulkMediaFetcher` to `Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher`
- Move `Pim\Component\Connector\Processor\Denormalization\AbstractProcessor` to `Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\JobInstanceProcessor` to `Akeneo\Tool\Component\Connector\Processor\Denormalization\JobInstanceProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\Processor` to `Akeneo\Tool\Component\Connector\Processor\Denormalization\Processor`
- Move `Pim\Component\Connector\Processor\DummyItemProcessor` to `Akeneo\Tool\Component\Connector\Processor\DummyItemProcessor`
- Move `Pim\Component\Connector\Processor\Denormalization\Processor` to `Akeneo\Tool\Component\Connector\Processor\Denormalization\Processor`
- Move `Pim\Component\Connector\Reader\Database\AbstractReader` to `Akeneo\Tool\Component\Connector\Reader\Database\AbstractReader`
- Move `Pim\Component\Connector\Reader\DummyItemReader` to `Akeneo\Tool\Component\Connector\Reader\DummyItemReader`
- Move `Pim\Component\Connector\Reader\File\ArrayReader` to `Akeneo\Tool\Component\Connector\Reader\File\ArrayReader`
- Move `Pim\Component\Connector\Reader\File\Csv\Reader` to `Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader`
- Move `Pim\Component\Connector\Reader\File\Xlsx\Reader` to `Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader`
- Move `Pim\Component\Connector\Reader\File\Yaml\Reader` to `Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader`
- Move `Pim\Component\Connector\Reader\File\FileIteratorFactory` to `Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory`
- Move `Pim\Component\Connector\Reader\File\FileIteratorInterface` to `Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface`
- Move `Pim\Component\Connector\Reader\File\MediaPathTransformer` to `Akeneo\Tool\Component\Connector\Reader\File\MediaPathTransformer`
- Move `Pim\Component\Connector\Reader\File\Yaml\Reader` to `Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader`
- Move `Pim\Component\Connector\Step\TaskletInterface` to `Akeneo\Tool\Component\Connector\Step\TaskletInterface`
- Move `Pim\Component\Connector\Step\TaskletStep` to `Akeneo\Tool\Component\Connector\Step\TaskletStep`
- Move `Pim\Component\Connector\Step\ValidatorStep` to `Akeneo\Tool\Component\Connector\Step\ValidatorStep`
- Move `Pim\Component\Connector\Writer\Database\Writer` to `Akeneo\Tool\Component\Connector\Writer\Database\Writer`
- Move `Pim\Component\Connector\Writer\DummyItemWriter` to `Akeneo\Tool\Component\Connector\Writer\DummyItemWriter`
- Move `Pim\Component\Connector\Writer\File\AbstractFileWriter` to `Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter`
- Move `Pim\Component\Connector\Writer\File\ArchivableWriterInterface` to `Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface`
- Move `Pim\Component\Connector\Writer\File\ColumnSorterInterface` to `Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface`
- Move `Pim\Component\Connector\Writer\File\DefaultColumnSorter` to `Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter`
- Move `Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface` to `Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface`
- Move `Pim\Component\Connector\Writer\File\FlatItemBuffer` to `Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer`
- Move `Pim\Component\Connector\Writer\File\FlatItemBufferFlusher` to `Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher`
- Move `Pim\Component\Connector\Writer\File\MediaExporterPathGenerator` to `Akeneo\Tool\Component\Connector\Writer\File\MediaExporterPathGenerator`
- Move `Pim\Component\Connector\Writer\File\Csv\Writer` to `Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer`
- Move `Pim\Component\Connector\Writer\File\Yaml\Writer` to `Akeneo\Tool\Component\Connector\Writer\File\Yaml\Writer`
- Move `Pim\Bundle\ConnectorBundle\Command\AnalyzeProductCsvCommand` to `Akeneo\Tool\Bundle\ConnectorBundle\Command\AnalyzeProductCsvCommand`
- Move `Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterArchiversPass` to `Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterArchiversPass`
- Move `Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterStandardToFlatConverterPass` to `Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterStandardToFlatConverterPass`
- Move `Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterStandardToFlatConverterPass` to `Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterStandardToFlatConverterPass`
- Move `Pim\Bundle\ConnectorBundle\DependencyInjection\PimConnectorExtension` to `Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\PimConnectorExtension`
- Move `Pim\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer` to `Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer`
- Move `Pim\Bundle\ConnectorBundle\EventListener\ClearBatchCacheSubscriber` to `Akeneo\Tool\Bundle\ConnectorBundle\EventListener\ClearBatchCacheSubscriber`
- Move `Pim\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector` to `Akeneo\Tool\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector`
- Move `Pim\Bundle\ConnectorBundle\EventListener\JobExecutionAuthenticator` to `Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionAuthenticator`
- Move `Pim\Bundle\ConnectorBundle\EventListener\ResetProcessedItemsBatchSubscriber` to `Akeneo\Tool\Bundle\ConnectorBundle\EventListener\ResetProcessedItemsBatchSubscriber`
- Move `Pim\Bundle\ConnectorBundle\PimConnectorBundle` to `Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle`
- Split `pim_connector.job.job_parameters.default_values_provider.simple_csv_import` into `akeneo_channel.job.job_parameters.default_values_provider.simple_csv_import`, `akeneo_pim_enrichment.job.job_parameters.default_values_provider.simple_csv_import` and `akeneo_pim_structure.job.job_parameters.default_values_provider.simple_csv_import`
- Move `Pim\Bundle\NotificationBundle` to `Akeneo\Platform\Bundle\NotificationBundle`
- Move `Pim\Bundle\DashboardBundle\Widget\CompletenessWidget` to `Akeneo\Pim\Enrichment\Bundle\Widget\CompletenessWidget`
- Move `Pim\Bundle\DashboardBundle\Controller\WidgetController` to `Akeneo\Platform\Bundle\DashboardBundle\Controller\WidgetController`
- Move `Pim\Bundle\DashboardBundle\DependencyInjection\Compiler\RegisterWidgetsPass` to `Akeneo\Platform\Bundle\DashboardBundle\DependencyInjection\Compiler\RegisterWidgetsPass`
- Move `Pim\Bundle\DashboardBundle\DependencyInjection\PimDashboardExtension` to `Akeneo\Platform\Bundle\DashboardBundle\DependencyInjection\PimDashboardExtension`
- Move `Pim\Bundle\DashboardBundle\PimDashboardBundle` to `Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle`
- Move `Pim\Bundle\DashboardBundle\Widget\LastOperationsWidget` to `Akeneo\Platform\Bundle\DashboardBundle\Widget\LastOperationsWidget`
- Move `Pim\Bundle\DashboardBundle\Widget\Registry` to `Akeneo\Platform\Bundle\DashboardBundle\Widget\Registry`
- Move `Pim\Bundle\DashboardBundle\Widget\WidgetInterface` to `Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface`
- Move `Pim\Bundle\UIBundle` to `Akeneo\Platform\Bundle\UIBundle`
- Remove class `Pim\Bundle\NavigationBundle\PimNavigationBundle`
- Move `Pim\Bundle\ImportExportBundle` to `Akeneo\Platform\Bundle\ImportExportBundle`
- Move `Pim\Bundle\InstallerBundle` to `Akeneo\Platform\Bundle\InstallerBundle`
- Move `Pim\Bundle\AnalyticsBundle` to `Akeneo\Platform\Bundle\AnalyticsBundle`
- Move `Pim\Bundle\CatalogVolumeMonitoringBundle` to `Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle`
- Move `Pim\Component\CatalogVolumeMonitoring` to `Akeneo\Platform\Component\CatalogVolumeMonitoring`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor`, remove `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface\EntityManagerClearerInterface` argument
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelDescendantsWriter`, remove `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface\EntityManagerClearerInterface` argument
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelWriter`, remove `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface\EntityManagerClearerInterface` argument
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter`, remove `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface\EntityManagerClearerInterface` argument
- Change constructor of `Akeneo\Tool\Bundle\BatchQueueBundle\Queue\DatabaseJobExecutionQueue`, remove `Doctrine\ORM\EntityManagerInterface` argument
- Rename `Pim\Bundle\FilterBundle\Filter\CompletenessFilter` to `Oro\Bundle\PimFilterBundle\Filter\ProductCompletenessFilter`
- Move `Pim\Bundle\PimDataGridBundle` to `Oro\Bundle\PimDataGridBundle`
- Move `Pim\Bundle\PimFilterBundle` to `Oro\Bundle\PimFilterBundle`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer`, add `IdentifiableObjectRepositoryInterface` argument
- Change constructor of `Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\AttributeDataCollector`, add `AverageMaxQuery` argument 3 times
- Change constructor of `Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\DBDataCollector`, add `AverageMaxQuery` argument
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter`, add `TokenStorageInterface`, `JobLauncherInterface`, `IdentifiableObjectRepositoryInterface` and `string` arguments
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`, add `AttributeFilterInterface` argument
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductModelController`, add `AttributeFilterInterface` argument
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer`, add `AttributeRepositoryInterface` and `UserContext` arguments
- Change constructor of `Pim\Bundle\EnrichBundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder`, add `ProductAndProductModelSearchAggregator` argument
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobExecutionController`, add `NormalizerInterface` argument
- Move `Pim\Bundle\EnrichBundle\Normalizer\CategoryNormalizer` to `Akeneo\Pim\Enrichment\Component\Category\Normalizer\InternalApi\CategoryNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\CollectionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CollectionNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\CompletenessCollectionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CompletenessCollectionNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\CompletenessNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CompletenessNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\EntityWithFamilyVariantNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\GroupNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\GroupNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\GroupViolationNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\GroupViolationNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\IncompleteValuesNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductModelNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\ProductViolationNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductViolationNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\VariantNavigationNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VariantNavigationNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\VersionNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VersionNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\ViolationNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ViolationNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionValueCollectionNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeOptionValueCollectionNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionValueNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeOptionValueNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\VersionedAttributeNormalizer` to `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\VersionedAttributeNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer` to `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer`
- Move `Pim\Bundle\EnrichBundle\Normalizer\DatagridViewNormalizer` to `Oro\Bundle\PimDataGridBundle\Normalizer\InternalApi\DatagridViewNormalizer`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\ChannelRepository` to `Akeneo\Channel\Bundle\Doctrine\Repository\InternalApi\ChannelRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\CurrencyRepository` to `Akeneo\Channel\Bundle\Doctrine\Repository\InternalApi\CurrencyRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\LocaleRepository` to ` Akeneo\Channel\Bundle\Doctrine\Repository\InternalApi\LocaleRepository`
- Move `Pim\Bundle\EnrichBundle\Cursor\SequentialEditProduct` to `Akeneo\Pim\Enrichment\Bundle\Cursor\SequentialEditProduct`
- Move `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounter` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounter`
- Move `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterInterface` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterInterface`
- Move `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterRegistry` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterRegistry`
- Move `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterRegistryInterface` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterRegistryInterface`
- Move `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryProductsCounter` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryProductsCounter`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\AscendantCategories` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\AscendantCategories`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\CountImpactedProducts` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountImpactedProducts`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\CategoryRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi\CategoryRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\GroupRepository` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi\GroupRepository`
- Move `Pim\Bundle\EnrichBundle\Elasticsearch\FromSizeCursor` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\FromSizeCursor`
- Move `Pim\Bundle\EnrichBundle\Elasticsearch\FromSizeCursorFactory` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\FromSizeCursorFactory`
- Move `Pim\Bundle\EnrichBundle\Elasticsearch\Sorter\InGroupSorter` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\InGroupSorter`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository` to `Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobInstanceRepository` to `Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobInstanceRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobTrackerRepository` to `Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobTrackerRepository`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\CountImpactedProducts\ItemsCounter` to `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountImpactedProducts\ItemsCounter`
- Move `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\ClientRepository` to `Oro\Bundle\PimDataGridBundle\Repository\ClientRepository`
- Remove `Pim\Bundle\EnrichBundle\Form\Handler\BaseHandler`
- Remove `Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface`
- Move `Pim\Bundle\EnrichBundle\Form\Handler\GroupHandler` to `Akeneo\Pim\Enrichment\Bundle\Form\Handler\GroupHandler`
- Move `Pim\Bundle\EnrichBundle\Form\Subscriber\BindAssociationTargetsSubscriber` to `Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\BindAssociationTargetsSubscriber`
- Move `Pim\Bundle\EnrichBundle\Form\Subscriber\FilterLocaleSpecificValueSubscriber` to `Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleSpecificValueSubscriber`
- Move `Pim\Bundle\EnrichBundle\Form\Subscriber\FilterLocaleValueSubscriber` to `Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleValueSubscriber`
- Move `Pim\Bundle\EnrichBundle\Form\Subscriber\FixArrayToStringListener` to `Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FixArrayToStringListener`
- Move `Pim\Bundle\EnrichBundle\Form\Type\CategoryType` to `Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\GroupType` to `Akeneo\Pim\Enrichment\Bundle\Form\Type\GroupType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\SelectFamilyType` to `Akeneo\Pim\Enrichment\Bundle\Form\Type\SelectFamilyType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\AssociationTypeType` to `Akeneo\Pim\Structure\Bundle\Form\Type\AssociationTypeType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType` to `Akeneo\Pim\Structure\Bundle\Form\Type\AvailableAttributesType`
- Move `Pim\Bundle\EnrichBundle\Form\Exception\FormException` to `Akeneo\Platform\Bundle\UIBundle\Form\Exception\FormException`
- Move `Pim\Bundle\EnrichBundle\Form\Factory\IdentifiableModelTransformerFactory` to `Akeneo\Platform\Bundle\UIBundle\Form\Factory\IdentifiableModelTransformerFactory`
- Move `Pim\Bundle\EnrichBundle\Form\Subscriber\AddTranslatableFieldSubscriber` to `Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\AddTranslatableFieldSubscriber`
- Move `Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber` to `Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber`
- Move `Pim\Bundle\EnrichBundle\Form\DataTransformer\ArrayToStringTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\ArrayToStringTransformer`
- Move `Pim\Bundle\EnrichBundle\Form\DataTransformer\EntitiesToIdsTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntitiesToIdsTransformer`
- Move `Pim\Bundle\EnrichBundle\Form\DataTransformer\EntityToIdTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntityToIdTransformer`
- Move `Pim\Bundle\EnrichBundle\Form\DataTransformer\EntityToIdentifierTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntityToIdentifierTransformer`
- Move `Pim\Bundle\EnrichBundle\Form\DataTransformer\IdentifiableModelTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\IdentifiableModelTransformer`
- Move `Pim\Bundle\EnrichBundle\Form\DataTransformer\StringToBooleanTransformer` to `Akeneo\Platform\Bundle\UIBundle\Form\Transformer\StringToBooleanTransformer`
- Move `Pim\Bundle\EnrichBundle\Form\Type\AsyncSelectType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\AsyncSelectType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\LocalizedCollectionType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\LocalizedCollectionType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\MediaType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\MediaType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\ObjectIdentifierType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\ProductGridFilterChoiceType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\ProductGridFilterChoiceType`
- Move `Pim\Bundle\EnrichBundle\Form\Type\UploadType` to `Akeneo\Platform\Bundle\UIBundle\Form\Type\UploadType`
- Move `Pim\Bundle\EnrichBundle\Provider\Form\ProductFormProvider` to `Akeneo\Pim\Enrichment\Bundle\Provider\Form\ProductFormProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Form\ProductModelFormProvider` to `Akeneo\Pim\Enrichment\Bundle\Provider\Form\ProductModelFormProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Form\JobInstanceFormProvider` to `Akeneo\Platform\Bundle\ImportExportBundle\Provider\Form\JobInstanceFormProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\EmptyValue\BaseEmptyValueProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\BaseEmptyValueProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueChainedProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueChainedProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface` to `Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface`
- Move `Pim\Bundle\EnrichBundle\Provider\Field\BaseFieldProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\Field\BaseFieldProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Field\FieldChainedProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldChainedProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface` to `Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface`
- Move `Pim\Bundle\EnrichBundle\Provider\Field\WysiwygFieldProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\Field\WysiwygFieldProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Filter\BaseFilterProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\Filter\BaseFilterProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Filter\FilterChainedProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterChainedProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface` to `Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface`
- Move `Pim\Bundle\EnrichBundle\Provider\Form\FormChainedProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormChainedProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface` to `Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface`
- Move `Pim\Bundle\EnrichBundle\Provider\Form\NoCompatibleFormProviderFoundException` to `Akeneo\Platform\Bundle\UIBundle\Provider\Form\NoCompatibleFormProviderFoundException`
- Move `Pim\Bundle\EnrichBundle\Provider\FormExtensionProvider` to `Akeneo\Platform\Bundle\UIBundle\Provider\FormExtensionProvider`
- Move `Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface` to `Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface`
- Remove `Pim\Bundle\EnrichBundle\Provider\Form\AttributeFormProvider`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\CurrencyController` to `Akeneo\Channel\Bundle\Controller\InternalApi\CurrencyController`
- Move `Pim\Bundle\EnrichBundle\Controller\CurrencyController` to `Akeneo\Channel\Bundle\Controller\UI\CurrencyController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\CategoryController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\CategoryController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\GroupController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\GroupController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\MassEditController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\MassEditController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\MediaController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\MediaController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\ProductCategoryController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductCategoryController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\ProductCommentController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductCommentController`
- Move `Pim\Bundle\PdfGeneratorBundle\Controller\ProductController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductPdfController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\SequentialEditController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\SequentialEditController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\ValuesController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ValuesController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\VersioningController` to `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\VersioningController`
- Move `Pim\Bundle\EnrichBundle\Controller\AbstractListCategoryController` to `Akeneo\Pim\Enrichment\Bundle\Controller\Ui\AbstractListCategoryController`
- Move `Pim\Bundle\EnrichBundle\Controller\FileController` to `Akeneo\Pim\Enrichment\Bundle\Controller\Ui\FileController`
- Move `Pim\Bundle\EnrichBundle\Controller\ProductModelController` to `Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductModelController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to `Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi\JobInstanceController`
- Move `Pim\Bundle\EnrichBundle\Controller\JobTrackerController` to `Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui\JobTrackerController`
- Move `Pim\Bundle\EnrichBundle\Controller\Rest\FormExtensionController` to `Akeneo\Platform\Bundle\UIBundle\Controller\InternalApi\FormExtensionController`
- Remove `Pim\Bundle\EnrichBundle\Controller\GroupController`
- Remove `Pim\Bundle\EnrichBundle\Controller\JobExecutionController`
- Remove `Pim\Bundle\EnrichBundle\Controller\ProductController`
- Move `Pim\Bundle\EnrichBundle\Twig\LocaleExtension` to `Akeneo\Channel\Bundle\Twig\LocaleExtension`
- Move `Pim\Bundle\EnrichBundle\Twig\CategoryExtension` to `Akeneo\Pim\Enrichment\Bundle\Twig\CategoryExtension`
- Move `Pim\Bundle\EnrichBundle\Twig\CategoryExtension` to `Akeneo\Pim\Structure\Bundle\Twig\CategoryExtension`
- Move `Pim\Bundle\EnrichBundle\Twig\ObjectClassExtension` to `Akeneo\Platform\Bundle\UIBundle\Twig\ObjectClassExtension`
- Move `Pim\Bundle\EnrichBundle\Twig\TranslationsExtension` to `Akeneo\Platform\Bundle\UIBundle\Twig\TranslationsExtension`
- Move `Pim\Bundle\EnrichBundle\Twig\VersionExtension` to `Akeneo\Platform\Bundle\UIBundle\Twig\VersionExtension`
- Move `Pim\Bundle\EnrichBundle\Twig\ViewElementExtension` to `Akeneo\Platform\Bundle\UIBundle\Twig\ViewElementExtension`
- Move `Pim\Bundle\EnrichBundle\Exception\MissingOptionException` to `Akeneo\Platform\Bundle\UIBundle\Exception\MissingOptionException`
- Remove `Pim\Bundle\EnrichBundle\Exception\DeleteException`
- Remove `Pim\Bundle\EnrichBundle\Event\CategoryEvents`
- Move `Pim\Bundle\EnrichBundle\File\DefaultImageProvider` to `Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProvider`
- Move `Pim\Bundle\EnrichBundle\File\DefaultImageProviderInterface` to `Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProviderInterface`
- Move `Pim\Bundle\EnrichBundle\File\FileTypeGuesser` to `Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesser`
- Move `Pim\Bundle\EnrichBundle\File\FileTypeGuesserInterface` to `Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesserInterface`
- Move `Pim\Bundle\EnrichBundle\File\FileTypes` to `Akeneo\Pim\Enrichment\Bundle\File\FileTypes`
- Move `Pim\Bundle\EnrichBundle\Event\AttributeGroupEvents` to `Akeneo\Pim\Structure\Bundle\Event\AttributeGroupEvents`
- Move `Pim\Bundle\EnrichBundle\Factory\MassEditNotificationFactory` to `Akeneo\Platform\Bundle\ImportExportBundle\Factory\MassEditNotificationFactory`
- Move `Pim\Bundle\EnrichBundle\Mailer\MailRecorder` to `Akeneo\Platform\Bundle\ImportExportBundle\Test\MailRecorder`
- Move `Pim\Bundle\EnrichBundle\Flash\Message` to `Akeneo\Platform\Bundle\UIBundle\Flash\Message`
- Move `Pim\Bundle\EnrichBundle\Filter\ProductEditDataFilter` to `Akeneo\Pim\Enrichment\Bundle\Filter\ProductEditDataFilter`
- Move `Pim\Bundle\EnrichBundle\Filter\ProductValuesEditDataFilter` to `Akeneo\Pim\Enrichment\Bundle\Filter\ProductValuesEditDataFilter`
- Move `Pim\Bundle\EnrichBundle\Imagine\Loader\FlysystemLoader` to `Akeneo\Platform\Bundle\UIBundle\Imagine\FlysystemLoader`
- Move `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` to `Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver`
- Move `Pim\Bundle\EnrichBundle\VersionStrategy\CacheBusterVersionStrategy` to `Akeneo\Platform\Bundle\UIBundle\VersionStrategy\CacheBusterVersionStrategy`
- Move namespace `Pim\Bundle\EnrichBundle\ViewElement` to `Akeneo\Platform\Bundle\UIBundle\ViewElement`
- Move `Pim\Bundle\EnrichBundle\Extension\Action\Actions\DeleteProductAction` to `Akeneo\Pim\Enrichment\Bundle\Extension\Action\DeleteProductAction`
- Move `Pim\Bundle\EnrichBundle\Extension\Action\Actions\EditInModalAction` to `Akeneo\Pim\Enrichment\Bundle\Extension\Action\EditInModalAction`
- Move `Pim\Bundle\EnrichBundle\Extension\Action\Actions\NavigateProductAndProductModelAction` to `Akeneo\Pim\Enrichment\Bundle\Extension\Action\NavigateProductAndProductModelAction`
- Move `Pim\Bundle\EnrichBundle\Extension\Action\Actions\ToggleProductAction` to `Akeneo\Pim\Enrichment\Bundle\Extension\Action\ToggleProductAction`
- Move `Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface` to `Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\BatchableOperationInterface`
- Move `Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperation` to `Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\MassEditOperation`
- Move `Pim\Bundle\EnrichBundle\MassEditAction\OperationJobLauncher` to `Akeneo\Pim\Enrichment\Bundle\MassEditAction\OperationJobLauncher`
- Move `Pim\Bundle\EnrichBundle\ProductQueryBuilder\Filter\DummyFilter` to `Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\Filter\DummyFilter`
- Move `Pim\Bundle\EnrichBundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder` to `Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder`
- Move `Pim\Bundle\EnrichBundle\StructureVersion\EventListener\StructureVersionUpdater` to `Akeneo\Pim\Enrichment\Bundle\StructureVersion\EventListener\StructureVersionUpdater`
- Move `Pim\Bundle\EnrichBundle\StructureVersion\EventListener\TableCreator` to `Akeneo\Pim\Enrichment\Bundle\StructureVersion\EventListener\TableCreator`
- Move `Pim\Bundle\EnrichBundle\StructureVersion\Provider\StructureVersion` to `Akeneo\Pim\Enrichment\Bundle\StructureVersion\Provider\StructureVersion`
- Move `Pim\Bundle\EnrichBundle\EventListener\AddLocaleListener` to `Akeneo\Platform\Bundle\UIBundle\EventListener\AddLocaleListener`
- Move `Pim\Bundle\EnrichBundle\EventListener\CloseSessionListener` to `Akeneo\Platform\Bundle\UIBundle\EventListener\AddLocaleListener`
- Move `Pim\Bundle\EnrichBundle\EventListener\ExceptionListener` to `Akeneo\Platform\Bundle\UIBundle\EventListener\ExceptionListener`
- Move `Pim\Bundle\EnrichBundle\EventListener\TranslateFlashMessagesSubscriber` to `Akeneo\Platform\Bundle\UIBundle\EventListener\TranslateFlashMessagesSubscriber`
- Move `Pim\Bundle\EnrichBundle\EventListener\UserContextListener` to `Akeneo\Platform\Bundle\UIBundle\EventListener\UserContextListener`
- Remove `Pim\Bundle\EnrichBundle\EventListener\RequestListener`
- Remove `Pim\Component\Enrich\Model\ChosableInterface`
- Move `Pim\Component\Enrich\Converter\ConverterInterface` to `Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface`
- Move `Pim\Component\Enrich\Converter\InternalApiToStandard\ValueConverter` to `Akeneo\Pim\Enrichment\Component\Product\Converter\InternalApiToStandard\ValueConverter`
- Move `Pim\Component\Enrich\Converter\StandardToInternalApi\ValueConverter` to `Akeneo\Pim\Enrichment\Component\Product\Converter\StandardToInternalApi\ValueConverter`
- Move `Pim\Component\Enrich\Converter\MassOperationConverter` to `Akeneo\Pim\Enrichment\Component\Product\Converter\MassOperationConverter`
- Move `Pim\Component\Enrich\Job\DeleteProductsAndProductModelsTasklet` to `Akeneo\Pim\Enrichment\Component\Product\Job\DeleteProductsAndProductModelsTasklet`
- Move `Pim\Component\Enrich\Query\AscendantCategoriesInterface` to `Akeneo\Pim\Enrichment\Component\Category\Query\AscendantCategoriesInterface`
- Move `Pim\Component\Enrich\Model\AvailableAttributes` to `Akeneo\Pim\Structure\Component\Model\AvailableAttributes`
- Move `Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface` to `Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface`
- Change method `create` of `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactoryInterface` To add a boolean parameter to determine if a unknown element of a collection must be ignored or not.
- Move `Akeneo\Pim\Enrichment\Component\Product\Query\Escaper\QueryString` to `Akeneo\Tool\Component\Elasticsearch\QueryString`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Factory\Value\PriceCollectionValueFactory`, add `Akeneo\Pim\Enrichment\Component\Product\Channel\Query\FindActivatedCurrenciesInterface` argument
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductWriter`, remove last argument `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Remove methods `generateMissingForProducts`, `generateMissingForChannel` and `generateMissing` from `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface`
    and `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator`
- Remove methods `generateMissingForProducts`, `generateMissingForChannel`, `generateMissing`, `schedule`, `bulkSchedule` and `scheduleForFamily` from `Akeneo\Pim\Enrichment\Component\Product\ManagerCompletenessManager`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Manager\`, remove arguments
    `Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface`,
    `Akeneo\Channel\Component\Repository\ChannelRepositoryInterface`,
    `Akeneo\Channel\Component\Repository\LocaleRepositoryInterface`,
    `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface`
`\ValueCompleteCheckerInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader`, remove arguments `Akeneo\Pim\Enrichment\Component\Product\CompletenessManager` and last argument `bool`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductReader`, remove arguments `Akeneo\Pim\Enrichment\Component\Product\CompletenessManager` and last argument `bool`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\ProductAndProductModelReader`, remove argument `Akeneo\Pim\Enrichment\Component\Product\CompletenessManager`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductAndProductModelReader`, remove arguments `Akeneo\Pim\Enrichment\Component\Product\CompletenessManager` and fifth argument `bool`
- Change constructor of `Oro\Bundle\PimFilterBundle\Filter\ProductValue\ChoiceFilter`, add argument `Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface` and remove fourth argument `string`
- Change constructor of `Oro\Bundle\PimFilterBundle\Filter\ProductValue\ReferenceDataFilter`, add argument `Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface`
- Change constructor of `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface\FamilySaver`, remove second argument `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FileValidator` to add an array of string (extension to mime type mapping)
- Add `pim_configuration` table. Don't forget to run the `doctrine:migrations:migrate` command.
- Remove methods `getBirthday` and `setBirthday` of `Akeneo\UserManagement\Component\Model\UserInterface` and `Akeneo\UserManagement\Component\Model\User`
- Change constructor of `Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\StorageDataCollector`, replace all arguments with `\Doctrine\DBAL\Connection`
