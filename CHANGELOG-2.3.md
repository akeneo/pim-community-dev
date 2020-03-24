# 2.3.x

# 2.3.78 (2020-03-24)

# 2.3.77 (2020-01-22)

# 2.3.76 (2020-01-10)

## Bug fixes

- PIM-6386: Add exception when trying to update the same product value on an attribute twice, with the same scope and locale
- PIM-7971: Fix filters being incorrectly disabled in the attributes grid

# 2.3.75 (2020-01-03)

## Bug fixes

- PIM-7699: Fix multi-select filter design with a lot of filtered items

## Technical improvement

- Update composer dependencies to fix PhpSpec Prohecy version to 1.9.*

# 2.3.74 (2019-12-12)

- PIM-9017: Remove "Add an attribute to a product" ACL
- PIM-6427: Fix wrong label update on attribute through the API

# 2.3.73 (2019-12-06)

## Bug fixes

- PIM-9003: Fix product variant navigation when there is no completeness for a given channel
- PIM-6437: Remove empty or already added attribute groups from family edit screen

# 2.3.72 (2019-12-02)

## Bug fixes

- PIM-8987: Add --batch-size option to pim:versioning:purge command and add a workaround for the memory leak inside.

# 2.3.71 (2019-11-25)

# 2.3.70 (2019-11-20)

## Bug fixes

- PIM-7027: fix completeness visibility on product edit form
- PIM-8965: Fix misleading error messages due to all-caps formatting
- PIM-8954: Forbid user without "list users" permission to access other user data
- PIM-8989: fix attributes order in the list of attributes

# 2.3.69 (2019-10-24)

## Bug fixes

- PIM-7963: fix datepicker width not adapting to the dropdown
- GITHUB-10955: Remove database prefix in queries

# 2.3.68 (2019-10-18)

## Bug fixes

- PIM-6976: fix max_characters attribute field not being nullable

# 2.3.67 (2019-10-14)

## Bug fixes

- PIM-7332: show an error message when a number attribute field reaches the PHP_INT_MAX.
- PIM-7691: Users were able to edit their own roles

# 2.3.66 (2019-10-09)

# 2.3.65 (2019-10-04)

## Bug fixes

- PIM-7122: User can not delete an attribute used as conversion unit in a channel

# 2.3.64 (2019-10-01)

## Bug fixes

- PIM-6424: update exports when a locale is removed from a channel
- PIM-7891: Update exports when an attribute is deleted

# 2.3.63 (2019-09-23)


## Bug fixes

- PIM-8738: Fix memory leak executing "akeneo:batch:purge-job-execution" command
- PIM-8790: Fix non removed category filter in job instances
- PIM-8778: Fix error code when request is bad formatted in product API

# 2.3.62 (2019-09-13)

## Bug fixes

- PIM-8757: Use a stream to create export archive
- PIM-8751: Fix simple and multi select attributes history when creating a new option

# 2.3.61 (2019-09-10)

# 2.3.60 (2019-09-05)

## Improvement

- PIM-7127: Hide version from CSS call

# 2.3.59 (2019-08-12)

## Bug fixes

- PIM-7675: Fix file input style

# 2.3.58 (2019-08-08)

## Bug fixes

- PIM-8461: Do not display 'Compare/translate' if user has no permission to edit product attributes
- PIM-7583: Allow user to import custom locales without '_'
- PIM-8630: Fix revert action when it exists an association type with integer as code

# 2.3.57 (2019-07-31)

## Bug fixes

- PIM-7741: use the catalog locale when choosing a new parent product model
- TIP-1200: Use SQL queries instead of repositories in UniqueVariantAxisValidator
- PIM-7888: Fix creation of a product model / variant product with a boolean attribute as axis

# 2.3.56 (2019-07-24)

## Bug fixes

- PIM-8349: Fixes missing variable passed to pim_enrich.job.upload translation

## Improvement

- GITHUB-10438: Include less files in the main stylesheet by default

# 2.3.55 (2019-07-23)

## Bug fixes

- PIM-8484: Show flash message instead of deprecated error modal on deletion
- PIM-8570: Fix category tree display
- PIM-8572: Fix issue on category selection in product grid

# 2.3.54 (2019-07-19)

## Bug fixes

- PIM-8551: Replace 'div' by '/' in Measure

# 2.3.53 (2019-07-15)

## Bug fixes

- PIM-7935: Close variant select dropdowns on page navigation
- PIM-8476: Fix drag & drop on category trees

# 2.3.52 (2019-07-02)

## Bug fixes:

- PIM-8480: Remove the job execution message orphans after a job execution purge.

# 2.3.51 (2019-06-26)

## Improvement

- PIM-8449: AKENEO_PIM_URL configured in community edition

# 2.3.50 (2019-06-24)

## Bug fixes:

- PIM-8464: Fix the product variant breadcrumb size
- PIM-8462: Fix memory leak in `Pim\Component\Catalog\Job\ComputeFamilyVariantStructureChangesTasklet`.

## BC Break

- Change constructor of `src/Pim/Component/Catalog/Job/ComputeFamilyVariantStructureChangesTasklet.php`:
    added: `Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface` and `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
    removed: `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface` and `Pim\Component\Catalog\Repository\ProductRepositoryInterface`

## Improvement

- PIM-8469: Bump Symfony version to 3.4.28 to fix Intl issues.

# 2.3.49 (2019-06-19)

## Bug fixes

- GITHUB-9557: Fix yarn product dependencies requirements, cheers @AngelVazquezArroyo!
- PIM-8450: Fix assets search with underscore in the code

## Technical improvement

- PIM-8449: Improve job notification by adding a link to the job.

# 2.3.48 (2019-06-12)

## Bug fixes

- PIM-7942: Fix tooltip minimal width
- PIM-8417: Fix inconsistent behavior in WYSIWYG editor by disabling HTML prettify

# 2.3.47 (2019-06-03)

## Technical improvement

- PIM-8384: Improve queue to consume specific jobs

## Bug fixes

- PIM-8378: Fix DI for EntityWithFamilyVariantNormalizer injection
- PIM-8385: Fix timeout when launching the clean attributes command
- PIM-8381: Do not expose disabled locales in attribute options export

# 2.3.46 (2019-05-27)

## Bug fixes

- PIM-7772: Fix translation in roles ACL
- PIM-8308: Fix broken translation keys for import and export profiles
- PIM-8374: Fix timeout when launching the completeness purge command
- PIM-7596: Fix margins on datagrids under tabs
- PIM-6829: Fix mass edit enabled steps
- PIM-7321: Fix blinking grid elements in gallery mode

# 2.3.45 (2019-05-23)

## Bug fixes

- PIM-8351: Fix entity overriding priority

# 2.3.44 (2019-05-20)

## Bug fixes

- PIM-8341: Use user locale for job execution normalization
- PIM-8347: Fix variant navigation display in case of long attribute labels
- PIM-8346: Hide the currency selector on the product grid price filters 'empty' and 'not empty'

## Improvement

- PIM-8342: Fix unused imports

# 2.3.43 (2019-05-14)

## Bug fixes

- PIM-8329: Add Serbian Flag for CS Region
- PIM-8276: Label or identifier search input is now limited to 255 characters.
- PIM-8322: Add a command to update elasticsearch mapping without having the need to reindex everything.
- PIM-8334: When a translation choice is not correct, it does not break the page anymore.
- PIM-8275: Set default attribute group filter to "All" when selecting attributes on product mass actions
- PIM-8305: Display the correct amount of products and product models deleted by the "Mass delete products" process.

# 2.3.42 (2019-05-06)

## Bug fixes

- PIM-8319: Prevent users from clicking several times on Import button during file upload.
- PIM-8323: Fix issue on attribute option removing
- PIM-8330: Backport : Allow installation folder to contain src | tests ... for webpack

# 2.3.41 (2019-05-02)

## Bug fixes

- PIM-8289: Fix search products on label or identifier for product variants ancestors

## Elasticsearch

- Please re-index the products and product models by launching the commands `console akeneo:elasticsearch:reset-indexes -e prod`, `pim:product:index --all -e prod` and `bin/console pim:product-model:index --all`.

## Improvement

- PIM-8318: Bump Symfony version to 3.4.26 to fix Intl issues.

# 2.3.40 (2019-04-30)

# 2.3.39 (2019-04-23)

# 2.3.38 (2019-04-23)

## Bug fixes

- PIM-8290: Fix flat to standard conversion of metrics, with unit filled and empty amount
- PIM-8288: keep locale-specific but non-localizable attribute values in the flat normalization to make them appear in the product changeset

# 2.3.37 (2019-04-15)

## Bug fixes

- PIM-8269: Do not create empty product values if it relies on an attribute which has been removed from family

# 2.3.36 (2019-04-02)

## Bug fixes

- PIM-8251: display variant family code in sidebar if no translated label
- PIM-8253: Fix unique attributes excluded in attributes search

# 2.3.35 (2019-03-26)

## Bug fixes

- PIM-8230: Show on hover information for a read-only text in product edit form
- PIM-8245: Fix the save-buttons extension (js) incorrectly resetting its internal state between calls.
- PIM-8176: `Nesting level too deep – recursive dependency?` for some custom reference_data attributes

# 2.3.34 (2019-03-18)

## Bug fixes

- PIM-8222: Fix product model issues when code contains `/` (create variant through UI and get product models via API)
- PIM-8187: Add the possibility to fetch descendant products and product models
- PIM-8214: Be able to save and launch job even if filter values refer to deleted entities.

## BC breaks

- PIM-8214: Remove validators `Pim\Component\Connector\Validator\Constraints\FilterDataValidator`, `Pim\Component\Connector\Validator\Constraints\ProductFilterData` and `Pim\Component\Connector\Validator\Constraints\ProductModelFilterData`

# 2.3.33 (2019-03-13)

## Bug fixes

- PIM-7966: Fix variant product order on variant product navigation in case of metric variations
- PIM-8177: Remove pages not accessible in case of product number higher than maximum ES window limit (10.000 by default) and add warning message on the last page
- PIM-8197: Use ZipArchive::addFile to avoid too much ram consumption

# 2.3.32 (2019-03-07)

## Improvement

- PIM-8175: add the possibility to filter on one or several index names when resetting ES indexes

# 2.3.31 (2019-02-28)

## Bug fixes

- PIM-8056: Remove bad ACL on the internal API end-point that get an association-type
- PIM-8162: use the catalog locale in product export builder
- PIM-8155: Fix bad ACL set on xlsx product export edit form

# 2.3.30 (2019-02-21)

## Bug fixes

- PIM-8134: Fix flickering on assets
- PIM-8131: Labels cannot be used for search in bulk actions
- PIM-7939: Fix PQB search when an attribute as label is on an ancestor.
  -> Not mandatory, you can re-index your products and product models to enjoy this fix with commands: `bin/console pim:product:index --all` and `bin/console pim:product-model:index --all`.

# 2.3.29 (2019-02-11)

## Bug fixes

- PIM-7943: Fix duplicate popin mask in family variant edit form.
- PIM-8010: Add missing job in minimal fixture
- PIM-8007: Content of sortable attribute options is now copyable.
- PIM-8008: Fix attributes sort order in PEF.
- PIM-8022: Fix the job status when using the batch command.
- PIM-8050: Fix ElasticSearch mappings loader

# 2.3.28 (2019-02-01)

# 2.3.27 (2019-01-29)

# 2.3.26 (2019-01-28)

## Bug fixes

- PIM-7967: Fix ACL for asset categories
- PIM-7969: fix special chars in PDF export
- Force the use of ip-regex at 2.1.0 version. Upper version needs nodejs >= 8 but we have to support nodejs >= 6.

# 2.3.25 (2019-01-17)

## Bug fixes

- PIM-7965: fix families patch endpoint when updating a family with a family variant
- PIM-7961: Fix localizable assets used as main image for family and added to product product model

# 2.3.24 (2019-01-10)

## Bug fixes

- PIM-7934: Fix translations of product model import
- PIM-7961: Fix localizable assets used as main image for family and added to product product model

# 2.3.23 (2019-01-03)

## Bug fixes

- PIM-7899: Remove Date of Birth field
- PIM-7926: Fix the parent property setter when "enabled comparison" is set to false in an import job definition

# 2.3.22 (2018-12-21)

## Bug fixes

- PIM-7892: Allow to filter on active catalog locale when adding an attribute to the product export filters
- PIM-7898: Fix tab navigation when the column is collapsed
- PIM-7866: Do not show delete icon on import/export profile if the user doesn't have the right to delete.
- PIM-7910: Search parent filter is now case insensitive
- PIM-7936: Missing breadcrumb when you create Attribute group or Channel

 ## Elasticsearch

 - Please re-index the products and product models by launching the commands `console akeneo:elasticsearch:reset-indexes -e prod` and `pim:product:index --all -e prod`.

# 2.3.21 (2018-12-07)

## Bug fixes

- PIM-7908: Fix variant family creation if an attribute doesn't have a translation for the current locale
- PIM-7901: Fix memory leak on "compute_family_variant_structure_changes" job

# 2.3.20 (2018-12-06)

## Bug fixes

- PIM-7897: Fix multiple calls to get all attribute groups in the PEF.

# 2.3.19 (2018-12-03)

# 2.3.18 (2018-11-28)

## Bug fixes

- PIM-7775: Security patch: check MIME type to be coherent with extension file. Saving products with incoherent file extension and MIME type is now forbidden.
- PIM-7865: Improve performances on Product model export
- PIM-7885: Allow "0" for non decimal metric value

# 2.3.17 (2018-11-15)

## Bug fixes

- PIM-7774: Fix refresh of grid date filter
- PIM-7778: Fix ACL on Catalog Volume Monitoring
- PIM-7773: Fix routing issues with product status toggle
- PIM-7776: Fix injection in the job's label in notification area
- PIM-7828: Fix ACL on System Info
- PIM-7824: Fix filter on view with attribute option deleted
- PIM-7852: Increase product import performances and fixes model association import when comparison is disabled
- PIM-7853: Fix unwanted automatic reload of the user page even if there were errors on the form
- PIM-7841: Allow users to set regional locales for UI (en_NZ, pt_PT and pt_BR)
- PIM-7791: Suppress warning on attribute option in case of case sensitive codes
- PIM-7831: Blacklist some characters in user form inputs in order to prevent from malicious injection

# 2.3.16 (2018-11-13)

## Bug fixes

- PIM-7823: Fix product gap between product grid and sequential edit when selecting `All` option
- PIM-7783: Fix constraint on attribute name
- PIM-7767: Remove option values label from attribute versioning
- PIM-7771: Fix refresh versioning command about duplicate version's rule.
- PIM-7813: Fix a bug that prevents to drag'n'drop an attribute group containing a lot of attributes in the variant family configuration screen.

# 2.3.15 (2018-11-06)

## Bug fixes

- PIM-7765: Replace `JSON_ARRAYAGG` by `GROUP_CONCAT`

# 2.3.14 (2018-11-05)

## Bug fixes

PIM-7810: Fix to mass delete products and product models

# 2.3.13 (2018-10-25)

## Bug fixes

- PIM-7759: Date range grid filters should be ignored when no value is set
- PIM-7758: Fix the product and product model deletion from the grid
- PIM-7765: Fix the loading of price values for disabled currencies

# 2.3.12 (2018-10-17)

## Bug fixes

- PIM-7674: fix Avatar image broken on dashboard
- PIM-7694: fix option null values crashing PDF
- PIM-7731: check for attribute as label not null in normalizers
- PIM-7740: bump summernote version to fix scroll glitches
- PIM-7746: Fix issue when an attribute code is numeric
- PIM-7727: parent filter search case insensitive
- PIM-7724: fix role label update and error displayed on permission save action
- PIM-7747: convert boolean strings in User converter

# 2.3.11 (2018-10-08)

## Bug fixes

- PIM-7676: Add code filter on attribute and family grid
- PIM-7673: Fix permissions on locales applied on channel settings page
- PIM-7664: ReferenceDataCollectionValueFactory can now ignore unknown reference data with an optionnal argument and not throw an exception.
- PIM-7672: Fix the mass edit controller to launch jobs with authentication.

# 2.3.10 (2018-10-01)

## Bug fixes

- PIM-7629: Fix category filter in product grid.
- PIM-7659: Fix search on the families to get all the results when they have same translations for many locales.
- PIM-7619: Fix search on groups for the variant products.
- PIM-7671: Fix associations tab cannot display more than 24 associated products/product models or 25 groups.
- PIM-7668: Fix issues with timezone in various screen, to always use current user timezone.
- PIM-7656: Fix a bug preventing a link insertion in WYSIWYG mass edit field.
- PIM-7670: Fix issue on SKU filters when changing context

# 2.3.9 (2018-09-25)

## Bug fixes

- PIM-7663: Fix API endpoint that list products updated since N days
- PIM-7658: Do not expose disabled locale
- PIM-7653: Fix product export builder when completeness should export products complete on at least one locale
- PIM-7650: Fix Values comparison. Allows to save a variant product with a metric as variant axe.
  - Please, for this fix, if you implemented `Pim\Component\Catalog\Model\AbstractValue` in specific code be warned that
    the `isEqual(ValueInterface $value)` method does not work due to a bug. Please, implement it in your own code for
    your specific business.
- PIM-7652: Fix concurrent edition with the parent of a product or a product model in the UI

# 2.3.8 (2018-09-14)

## Bug fixes

- PIM-7648: Fix preview of huge images in Product Edit Form
- PIM-7647: Fix completeness filter on the product export builder

# 2.3.7 (2018-09-11)

## Bug fixes

- PIM-7628: Fix the initialization of the product datagrid identifier filter.
- PIM-7594: Fix memory leak in `pim:versioning:purge` command
- PIM-7635: Fix elasticsearch config override
- PIM-7598: Fix locale change on reference data on simple and multi select
- PIM-7484: Search families and family variants regardless of the current locale

## BC breaks

- PIM-7594: Method `Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface::findPotentiallyPurgeableBy` returns now an CursorInterface

## Enhancements

- PIM-7612: Add the media/cache/{filter}/{path} route support in order to handle scalable frontend architecture for media content delivering

## Technical improvements

- PIM-7601: Update Symfony to 3.4.4

# 2.3.6 (2018-09-06)

## Enhancements

- PIM-7610: Add a command to create users

## Bug fixes

- PIM-7600: Change the default return value of ResetIndexesCommand to true to allow the --no-interaction parameter.
- PIM-7572: Cross to remove associations displayed at PV level whereas association is done at PM level
- PIM-7618: Hide the "Process tracker" link in the Dashboard if the user does not have the permission
- PIM-7626: Fix attribute groups order in the product grid's column configurator
- PIM-7631: Fix API filter product and product model on date with between operator
- PIM-7613: Fix translations of boolean attributes
- PIM-7609: Handle 'empty' and 'not empty' filter types in string filter

# 2.3.5 (2018-08-22)

## Bug fixes

- PIM-7580: Fixes the search on categories with product models
- PIM-7573: Fix "nesting level too deep" error during family import
- PIM-7562: Fix API filter product on status and groups
- PIM-7571: Fix job instance validation in case of attribute deletion
- PIM-7542: Fix completeness filter on edit product group page
- PIM-7589: Fix job `compute_product_models_descendants` launched too many times
- PIM-7587: Fix the preview generation configuration with imagine
- PIM-7414: Fix localisable assets used as main image for family and added to product, break the product form

## BC breaks

- PIM-7414: Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add `Pim\Bundle\CatalogBundle\Context\CatalogContext` as a new argument.

# 2.3.4 (2018-08-08)

## Bug fixes

- PIM-7537: Fix console error in password reset form
- PIM-7552: Fix SKU filter display bug on group grid
- PIM-7543: Forbid usage of 'label' code for attributes to prevent UI bugs
- PIM-7545: Redirect to login page when user is not authenticated anymore
- PIM-7569: Fix limit of 20 results for view selector

# 2.3.3 (2018-08-01)

## Bug fixes

- PIM-7529: Fix error when a tree is removed
- PIM-7536: Fix tool-tip error
- GITHUB-8550: Exclude more folders in typescript to improve build time
- GITHUB-8578: Fixed wrong labels displayed in the "active/inactive" filter of user grid" (Thanks [oliverde8](https://github.com/oliverde8)!)
- PIM-7551: Fix issue on product model import when using custom column headers
- PIM-7541: Fix issue on filtered search on created and updated product and product model properties. Date must be instanciated on server timezone.

# 2.3.2 (2018-07-24)

## Bug fixes

- PIM-7528: Fix Product and Product Model date attribute rendering in history panel, no timezone needed.
- PIM-7488: use catalog locale for attributes list in attribute groups
- PIM-7518: fix memory leak in channel/locale clean command
- PIM-7517: fix the product models export filter on identifier
- PIM-7476: fix family select2 to have the right limit
- PIM-7516: fix metric default value on product edit form

## Migration

**IMPORTANT** Please run the doctrine migrations command to fix the product models export profiles : `bin/console doctrine:migrations:migrate --env=prod`


# 2.3.1 (2018-07-04)

## Enhancements

- PIM-7465: Set form data entity into field context.

# 2.3.0 (2018-06-25)

# 2.3.0-BETA1 (2018-06-21)

## Monitor your catalog volume

- PIM-7209: As John, I want to be able to get info about my catalog volume.

## Improve Julia's experience

- PIM-7347: Improve the calculation of the completeness for locale specific attributes.
- PIM-7345: Remove the "is empty" operator for sku.

# 2.3.0-ALPHA2 (2018-06-07)

## Better manage products with variants

- PIM-6897: As Julia, I would like to update the family variant labels from the UI
- PIM-7302: As Julia, I am not able to delete an attribute option if it's use as variant axis.
- PIM-7330: Improve validation message in case of product model or variant product axis values duplication
- PIM-7326: Create a version when the parent of a variant product or a sub product model is changed.
- PIM-6784: Improve the product grid search with categories for products with variants
- PIM-7308: As Julia, I would like to bulk add product models associations if product models are selected.
- PIM-7293: As Julia, I would like to export variant products associations with their parent associations.
- PIM-7001: Don't display remove button on an association if it comes from inheritance
- PIM-7390: In the Product Edit Form, we now keep the context of attributes filter (missing, all, level specific...)
- PIM-7430: Mass associate generate new product version
- PIM-7298: As Julia, I would like to change the parent of a sub product model by import.
- PIM-6350: As Julia, I would like to change the parent of a variant product/sub product model from the UI.

## Technical improvements

- PIM-7302: Add a 'family_variant' filter in the Product Query Builder with operators 'IN', 'NOT IN', 'EMPTY' and 'NOT EMPTY'.
- PIM-7324: Rework structure version provider to better handle cache invalidation.

## BC Breaks

- Remove public constant `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxis::DUPLICATE_VALUE_IN_SIBLING`
- Change the method signature of `Pim\Component\Catalog\Validator\UniqueAxesCombinationSet::addCombination`, this method does not return anything anymore, but can throw `AlreadyExistingAxisValueCombinationException`
- Add method `generateMissingForProducts` to `Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface`
- Add a new public method `findProductModelsForFamilyVariant` to `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface`
- Change signature of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver` constructor to add `Akeneo\Component\StorageUtils\Indexer\IndexerInterface`

# 2.3.0-ALPHA1 (2018-04-27)

## Better manage products with variants

- PIM-6795: As Julia, I would like to display only the current level attributes
- PIM-7284: Be able to bulk change the status of children products if product models are selected
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import
- PIM-7000: As Julia, I would like to manage associations for products models from the UI
- PIM-6991: As Julia, I would like to export product models associations
- PIM-7286: Be able to bulk add children products of product models to group

## Improve Julia's experience

- PIM-7294: As Julia, I don't want to remove a family with products

## Technical improvements

- Add typescript support

## BC Breaks

- Remove methods `getAssociations`, `setAssociations`, `addAssociation`, `removeAssociation`, `getAssociationForType` and `getAssociationForTypeCode` from `Pim\Component\Catalog\Model\ProductInterface`. These methods are now in the `Pim\Component\Catalog\Model\EntityWithAssociationsInterface`.
- Change signature of `Pim\Component\Catalog\Builder\ProductBuilderInterface::addMissingAssociations` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface::findMissingAssociationTypes` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Model\AssociationInterface::setOwner` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel` constructor to add the `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver`
- Change signature of `Pim\Component\Component\Catalog\ProductBuilder` constructor to add the `Pim\Component\Catalog\Association\MissingAssociationAdder`
- `Pim\Component\Catalog\Model\ProductModelInterface` now implements `Pim\Component\Catalog\Model\EntityWithAssociationsInterface`

## New jobs

**IMPORTANT**: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.

- Add the job instance `add_to_group`: `bin/console akeneo:batch:create-job "Akeneo Mass Edit Connector" "add_to_group" "mass_delete" "add_to_group" '{}' "Mass add product to group" --env=prod`

## Migrations

Please run the doctrine migrations command in order to see the new catalog volume monitoring screen: `bin/console doctrine:migrations:migrate --env=prod`
