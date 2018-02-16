# 2.0.x

## Bug fixes

- PIM-7170: Fix media files unnecessarily generated during quick export

# 2.0.15 (2018-02-01)

# 2.0.14 (2018-02-01)

## Bug fixes

- PIM-7131: Fix mass add product values when number of products selected is greater than batch size
- PIM-7144: Fix translated label of attribute groups in the product edit form

# 2.0.13 (2018-01-23)

## Bug fixes

- PIM-7111: Fix display bug on variant axis completeness
- PIM-6908: Fix cancel button on unsaved changes dialog
- PIM-6913: Fix incorrect product completeness percentage
- PIM-7057: Fix import families by adding a dedicated step regarding the computing of product models data

## BC Breaks

- Changes the constructor of `\Pim\Bundle\CatalogBundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber` to add `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`

# 2.0.12 (2018-01-12)

## Bug fixes

- PIM-7102: Fix product categories and groups being lost when attaching a product to a product model
- PIM-6874: Fix select attribute groups from PEF when there are more than 25
- PIM-7086: Fix enable loading message in system configuration
- API-567: Fix validation of product-models on API
- PIM-7085: Fix translation missing
- PIM-6965: Show short view|project name in the grid
- PIM-7083: fix access to product edit form if no right to view default locale
- PIM-7082: remove double user menu on product import edit form
- PIM-7084: fix attribute suppression
- PIM-6355: Fix the count by categories on the product grid
- PIM-7105: Fix un-index variant product on deletion

## Improvements

- PIM-7103: Improve product datagrid performance

## Better manage products with variants!

- API-516: be able to add a parent to a product via API
- API-566: "updated" filter works on product variant if its product models were updated

## BC Breaks

- Changes the constructor of `Pim\Bundle\ApiBundle\Controller\ProductController` to add `Pim\Component\Catalog\EntityWithFamilyVariant\AddParent`
- Changes the service `pim_enrich.doctrine.counter.category_product` first argument to a `@pim_catalog.query.product_query_builder_factory`

# 2.0.11 (2018-01-05)

## Bug fixes

- API-568: Forbid the use of an non existing attribute in product creation and update
- API-544: Forbid the use of an non existing attribute in product models creation and update
- PIM-7062: Allow to delete attributes contained in a family variant
- PIM-6944: Fix delta export for variant products
- PIM-7070: Fix sequential edit when selecting multiple product models
- PIM-6812: Change the message when delete an attribute as variant axis
- PIM-7048: Fix cascade persist issue during import of families with variant
- PIM-7049: Fix random order of attribute options
- PIM-7080: Fix memory leak on product export
- PIM-6955: Fix delete user
- PIM-7065: Fix versioning when attribute codes are numerics.
- PIM-7087: Fix completeness normalization when channel code is numeric.
- PIM-6968: Fix mass delete product

## Improvements

- PIM-7079: Improve indexation performance of ValueCollection when deleting values

## BC breaks

- Changes the constructor of  `Pim\Component\Catalog\ProductModel\Filter\ProductAttributeFilter` add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Changes the constructor of  `Pim\Component\Catalog\ProductModel\Filter\ProductModelAttributeFilter` add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Changes the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeController` add `Doctrine\ORM\EntityManagerInterface` and `Symfony\Component\Translation\TranslatorInterface`
- Changes the constructor of `Pim\Bundle\CatalogBundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber` add `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`
- Changes the constructor of `Pim/Bundle/DataGridBundle/Extension/MassAction/Handler/DeleteProductsMassActionHandler` to add `Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface`
- Changes the constructor of `Pim\Bundle\ApiBundle\Controller\MediaFileController` to add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Changes the constructor of `Pim\Bundle\ApiBundle\Controller\MediaFileController` to add `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
- Changes the constructor of `Pim\Bundle\ApiBundle\Controller\MediaFileController` to add `Akeneo\Component\StorageUtils\Saver\SaverInterface`

## Better manage products with variants!

- API-543: update product model with media file via API

# 2.0.10 (2017-12-22)

## Bug fixes

- PIM-7032: Fix close button when clicking on "Compare/Translate" option on the product edit form
- PIM-7063: Add validation for AttributeGroup - cannot remove AttributeGroup containing attributes and cannot remove AttributGroup "other"

# 2.0.9 (2017-12-15)

## Bug fixes

- PIM-7037: Allows code to be an integer on product model import
- PIM-7011: XLSX Options Import - Simple select attribute option cannot be updated for options with numeric codes
- PIM-7039: Association grid, scopable attributes used as labels do not appear
- PIM-6980: Missing labels for attribute prevent you from creating a variant family
- PIM-7030: Not allow empty Metric value as axis for variant products

## Better manage products with variants!

- PIM-6341: Allow cascade deletion of product models via the grid and PEF
- PIM-6357: Adds mass edit of attributes for product and product models

## BC breaks

- MySQL table constraints and elasticsearch indexes have changed. Please execute the pending migrations using the `doctrine:migrations:migrate` console command.

# 2.0.8 (2017-12-07)

## Bug fixes

- PIM-7035: fix reset login page style and error 500 thrown after submitting form
- PIM-7045: fix memory leak in step `Compute product model descendants` for product model import
- PIM-6958: fix loading a product with a reference data that is not available (simpleselect or multiselect)

## Better manage products with variants!

- PIM-6349: Adds mass edit to add products to an existing product model
- PIM-6791: Change a product in a variant product by import

## Update jobs

IMPORTANT: In order to use the new mass edit, please execute `bin/console akeneo:batch:create-job internal add_to_existing_product_model mass_edit add_to_existing_product_model '{}' 'Add products to an existing product model' --env=prod`

## BC breaks

- Changes the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductModelController` to add `Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface`
- Changes the constructor of `Akeneo\Bundle\ElasticsearchBundle\Cursor\CursorFactory` to add `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface` and remove `Doctrine\Common\Persistence\ObjectManager` and string `$entityClassName`
- Changes the constructor of `Akeneo\Bundle\ElasticsearchBundle\Cursor\FromSizeCursorFactory` to add `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface` and remove `Doctrine\Common\Persistence\ObjectManager` and string `$entityClassName`
- Changes the constructor of `Akeneo\Bundle\ElasticsearchBundle\Cursor\SearchAfterSizeCursorFactory` to add `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface` and remove `Doctrine\Common\Persistence\ObjectManager` and string `$entityClassName`
- Deletes `Pim\Component\Catalog\Repository\ProductRepositoryInterface::getAssociatedProductIds()`
- Changes the constructor of `Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxesValuesValidator` to remove `Doctrine\ORM\EntityManagerInterface`


# 2.0.7 (2017-11-23)

## Better manage products with variants!

- PIM-6567: Add edition capabilities to family variants from the UI (distribution of the attributes)
- PIM-6460: Preventing from deleting attributes used as axis from the family and remove the deleted attributes from the family variants
- PIM-6986: Change the image in add variant modal
- API-400: Update partially a family variant with the API
- API-401: Update partially a list of family variants with the API
- PIM-6357: Show the right count when selecting product and product models on mass edit

## Bug fixes

- PIM-6489: fix the sort of attributes in attribute groups
- PIM-6997: fixes product model indexing CLI command slowness
- PIM-6959: fix getting the product label according to the scope if needed

## Improvements

- IM-825: allow concurrent AJAX requests by closing the session in a listener
- PIM-6838: Display completeness panel after Attributes in the PEF
- PIM-6891: On the grid, execute the ES query only once, not twice
- PIM-6967: Allow category panels to be resized
- PIM-6585: Add help center link in menu
- PIM-6833: Aligns technical requirements with documentation
- PIM-6992: Keep category panel open
- PIM-6791: Change a product in a variant product by import

## BC breaks

- New data has been indexed in Elasticsearch. Please re-index the products and product models by launching the commands `pim:product:index --all -e prod` and `pim:product-model:index --all -e prod`.
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\FamilyVariantController` to add `Pim\Bundle\ApiBundle\Stream\StreamResourceResponse`.
- Replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Connector\Processor\Denormalization\Product\AddParent` and `Pim\Component\Connector\Processor\Denormalization\Product\FindProductToImport` in `Pim\Component\Connector\Processor\Denormalization\ProductProcessor`
- Change method signature from `Pim\Component\Catalog\Model\ProductInterface::setAssociations(array $associations)` to `Pim\Component\Catalog\Model\ProductInterface::setAssociations(Collection $associations)`

# 2.0.6 (2017-11-03)

## Better manage products with variants!

- PIM-6354: Adds product models during quick exports.
- PIM-6449: Adds a sub product model to a product model.
- PIM-6450: Adds a variant product to a product model.

## Bug fixes

- PIM-6948: Use search after method for products and product models indexing instead of offset limit
- PIM-6922: Fix sort order on attribute groups
- PIM-6880: Remove the old variation asset icon
- PIM-6914: Default UI locale for a new user is en_US but fix display of saved UI locale for user

## Improvements

- TIP-824: Increase CLI products indexing performance by 20%
- IMP-6932: Fix product model actions on products grid

## BC breaks

- Rename `Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductQuickExport` to `ProductAndProductModelQuickExport`
- Rename `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductProcessor` to `ProductAndProductModelProcessor`
- Updates quick export configurations to remove `filePath` and add `filePathProduct` and `filePathProductModel`.
- Adds `Pim\Component\Catalog\Repository\ProductRepositoryInterface.php::searchAfter()` and `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface::searchAfter()` methods
- Deletes `Pim\Component\Catalog\Repository\ProductRepositoryInterface.php::findAllWithOffsetAndSize()` and `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface::findRootProductModelsWithOffsetAndSize()` methods.
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface`.

## Update jobs

IMPORTANT: In order to use the new quick exports, please execute `bin/console doctrine:migrations:migrate` to migrate your configurations.
IMPORTANT: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.
- Add the job instance `compute_family_variant_structure_changes`: `bin/console akeneo:batch:create-job "internal" "compute_family_variant_structure_changes" "compute_family_variant_structure_changes" "compute_family_variant_structure_changes" '{"family_variant_codes":["null"]}' "Compute family variant structure changes" --env=prod`

# 2.0.5 (2017-10-26)

## Bug fixes

- GITHUB-7035: Change class alias for proper LocaleType form parent indication, cheers @mkilmanas!
- PIM-6567: Fix attributes filter to not remove axes
- API-411: Fix error 500 when product model has no values
- API-408: Fix too many error messages
- API-407: Fix too many error messages when trying to create a product model that extends a product model with a parent
- PIM-6933: Fix menu display in case of acl restriction
- PIM-6923: Fix search on all grids when returning on it
- PIM-6878: Fix attribute creation popin not extensible

# Improvements
 - TIP-819: 3x indexing performance on command by not waiting for index refresh (Product, ProductModel and PublishedProduct indexing commands)

## Better manage products with variants!

- PIM-6773: Add the missing required attributes filter in the product model edit form
- PIM-6806: Update product completenesses whenever the attribute requirements of a family are updated
- PIM-6492: search products with variants according to the completeness
- PIM-6337: Create a product model from the UI
- API-405: Update partially a list of product models

## BC breaks

- `Refresh::disabled()` rename to `Refresh::disable()`, to make it homogeneous with `Refresh::enable()` and `Refresh::waitFor()`
- Change the constructor of `Pim\Component\Catalog\Completeness\CompletenessCalculator`. Remove `Pim\Component\Catalog\Factory\ValueFactory` and both `Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface`. Add `Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory` and `Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory`.
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`.
- Move `Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\CompletenessFilter` to `Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\CompletenessFilter`
- Move `Pim\Bundle\FilterBundle\Filter\Product\CompletenessFilter` to `Pim\Bundle\FilterBundle\Filter\CompletenessFilter`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductModelController` to add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface` and `Symfony\Component\Serializer\Normalizer\NormalizerInterface`

## New jobs
IMPORTANT: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.
- Add the job instance `compute_completeness_of_products_family`: `bin/console akeneo:batch:create-job "internal" "compute_completeness_of_products_family" "compute_completeness_of_products_family" "compute_completeness_of_products_family" '{"family_code":"null"}' "compute completeness of products family" --env=prod`

# 2.0.4 (2017-10-19)

# 2.0.3 (2017-10-19)

## Bug fixes

- PIM-6898: Fixes some data can break ES index and crashes new products indexing
- PIM-6918: Fix error when deleteing boolean attribute linked to a published product
- PIM-5817: move datepicker above field instead of under

## Better manage products with variants!

- API-381: Create family variant via API.
- API-399: Create a product model via API.
- PIM-6903: Adds compare/translate functionality for product models
- API-404: Update partially a single product model via API
- PIM-6892: Forbids users to unselect categories of parent product models
- PIM-6896: Remove the button restore displayed on product models
- PIM-6891: Keep the tab context between product and product model forms

## Better UI\UX!

- PIM-6667: Update loading mask design
- PIM-6504: Update action icons on datagrids
- PIM-6848: Fix design on export builder fields
- PIM-6868: CSS glitches compilation
- PIM-6909: Replace 'products' by 'results' in products indexes

## BC breaks

- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to add `Pim\Component\Enrich\Query\AscendantCategoriesInterface`

# 2.0.2 (2017-10-12)

## Tech improvements

- TIP-808: Add version strategy for js and css assets, no more need to ask final users to refresh their browser cache when applying a new patch!
- PRE_SAVE and POST_SAVE events dispatched by instances of BaseSaver now include an "is_new" argument indicating if entities are being inserted or updated.
- TIP-813: Move attribute form fields to make them generic
- PIM-6589: Add new template for confirmation modals

## Bug Fixes

- PIM-6865: Fix ACL on import profile page
- PIM-6876: Escape u001f character to workaround a mysql bug
- TIP-810: Add Symfony command to reset the ES indexes
- TIP-809: Prevents ES from using the scoring system and bypass the max_clause_count limit.
- PIM-6872: Fix PQB sorters with Elasticsearch
- PIM-6859: Fix missing attribute values in PDF
- PIM-6894: Allow any special characters in password field
- PIM-6821: Options "Edit attributes" and "classify the product" not working on the Product grid

## Better UI\UX!

- PIM-6584: Update main menu order
- API-398: As Mary, I want to only see my launched exports/imports
- API-397: As Mary, I want to only see my launched jobs in the dashboard
- API-389: As Mary, I want to only see my launched jobs in the process tracker
- PIM-6881: Fix common attributes design
- PIM-6851: Fix completeness panel in case of a big number of channels
- PIM-6895: Improve performances on products datagrid
- PIM-6539: Update cross icons with new design
- PIM-6776: Missing translations for page titles

## Better manage products with variants!

- PIM-6343: Classify product models via the product form in the tab "categories"
- PIM-6327: Create a family variant from the UI (without distribution of the attributes)
- PIM-6857: Display a family variant from the UI
- PIM-6346: Add history on product model form
- PIM-6863: Hide "Variant" meta in non variant products
- PIM-6816: Manage validation error messages for product models
- PIM-6893: Fix cannot create a variant product if the axes combination already exist
- API-394: Warn API user if they try to use `variant_group` field on product POST/PATCH
- API-395: Get list of product models via API
- API-373: Update a single variant product via API
- API-376: Update a list of variant products via API

## BC breaks

- Throw exception when trying to create or update a product with the `variant_group` field through the API, now you have to use `parent` field [please see the link below](http://api.akeneo.com/documentation/products-with-variants.html)
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` to add `Oro\Bundle\SecurityBundle\SecurityFacade`, an acl and a template
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeGroupController` to add `Symfony\Component\EventDispatcher\EventDispatcherInterface` and `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to add `Symfony\Component\EventDispatcher\EventDispatcherInterface` and `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository` to add `Pim\Component\Catalog\Repository\VariantProductRepositoryInterface`
- Change the constructor of `Pim\Component\Catalog\ProductModel\Filter` to add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Move `Pim\Component\Connector\Processor\Denormalization\AttributeFilter\AttributeFilterInterface` to `Pim\Component\Catalog\ProductModel\Filter\AttributeFilter\AttributeFilterInterface`
- Move `Pim\Component\Connector\Processor\Denormalization\AttributeFilter\ProductAttributeFilter` to `Pim\Component\Catalog\ProductModel\Filter\AttributeFilter\ProductAttributeFilter`
- Move `Pim\Component\Connector\Processor\Denormalization\AttributeFilter\ProductModelAttributeFilter` to `Pim\Component\Catalog\ProductModel\Filter\AttributeFilter\ProductModelAttributeFilter`
- Rename `Pim\Component\Catalog\Validator\Constraints\SiblingUniqueVariantAxes` into `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxis`
- Rename service `pim_catalog.validator.constraint.sibling_unique_variant_axes` into `pim_catalog.validator.constraint.unique_variant_axes`
- Rename class parameter `pim_catalog.validator.constraint.sibling_unique_variant_axes.class` into `pim_catalog.validator.constraint.unique_variant_axes.class`
- Replace the class parameter of the service `pim_catalog.repository.variant_product` with `pim_catalog.repository.variant_product.class`
- Add method `getCodesIfExist` to `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Rename `Pim\Bundle\EnrichBundle\Controller\Rest\ValueController` to `Pim\Bundle\EnrichBundle\Controller\Rest\ValuesController`
- Remove method `Pim\Component\Catalog\Repository\ProductRepositoryInterface::setProductQueryBuilderFactory()`
- Remove method `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository::setReferenceDataRegistry()`

# 2.0.1 (2017-10-05)

## Bug Fixes

- PIM-6446: fix variant family code uniqueness
- GITHUB-6866: Fix the QueryProductCommand, cheers @LeoBenoist!
- API-393: Add prefix "akeneo:batch" to the command "publish-job-to-queue"
- PIM-6843: Update delete buttons in category, user, role and group pages
- PIM-6861: Correctly display a product model if it has no product children
- PIM-6862: Save products on "pim:completeness:calculate" command
- PIM-6866: Fix PQB sorter when attribute is not localizable and/or not scopable
- PIM-6348: Display a red label in the variant navigation if no variant product is complete
- PIM-6451: Now display variant axes coming from parent as "Variant Axis" on the product edit form
- PIM-6847: Fix variant product history
- PIM-6867: Fix validation of variant product, now it's impossible to have a root product model as parent if there are 2 levels of variation
- PIM-6816: Add validation error messages on product model edit form

## Tech improvements

- GITHUB-6639: Fix Job throwing exception, cheers @dnd-tyler!
- GITHUB-6824: Update gitignore for web-server-bundle, cheers @xElysioN!
- API-377: Get a single product model via API
- API-379: Get a single family variant via API
- API-380: Get a list of family variants via API
- API-369: Get a list of variant products
- API-370: Get a single variant product
- API-371: Delete single variant product
- API-372: Create a variant product

## Better manage products with variants!

- PIM-6343: Classify product models by import and export product models with their categories
- PIM-6356: Display the image of the 1st variant product created in the grid and on the PEF for product models
- PIM-6856: List family variants created by import in a new tab "variants" in the family
- PIM-6797: Automatically add "unique value" and identifier attributes at the last variant product level in family variants

## Better UI\UX!

- TIP-807: Improve menu to pass parameters for routes, cheers @MarieMinasyan!
- PIM-6839: Fix the design for large titles
- PIM-6595: Add missing breadcrumb or user navigation on every page
- PIM-6841: Add custom pictures for entities creation
- PIM-6832: Fix the column category display when category node is expanded
- PIM-6835: CSS Glitch compilation
- PIM-6853: Remove the checkboxes from the attributes grids
- PIM-6534: Move the user status to context dropzone
- PIM-6537: Wrong display of Role / Permission
- PIM-6618: Edit attribute options icons

## BC breaks

- Change constructor of `Pim\Bundle\DataGridBundle\Normalizer\ProductModelNormalizer` to add `Pim\Component\Catalog\ProductModel\ImageAsLabel`
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer` to add `Pim\Component\Catalog\ProductModel\ImageAsLabel`
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to add `Pim\Component\Catalog\ProductModel\ImageAsLabel`
- Rename `Pim\Component\Catalog\Validator\Constraints\SiblingUniqueVariantAxesValidator` to `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxisValidator`
- PIM-6446: fix variant family code uniqueness, beware, this changes the MySQL table

## Remove dead code (Variant Group Feature)

- Rename `Pim\Bundle\EnrichBundle\Controller\Rest\ProductTemplateController` to `Pim\Bundle\EnrichBundle\Controller\Rest\ValueController`
- Remove class `Pim\Bundle\VersioningBundle\Normalizer\Flat\ProxyGroupNormalizer`
- Remove class `Pim\Component\Catalog\Normalizer\Standard\ProxyGroupNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\Command\CleanProductTemplateCommand`
- Remove class `Pim\Bundle\CatalogBundle\Command\CopyVariantGroupValuesCommand`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductTemplateRepository`
- Remove class `Pim\Bundle\CatalogBundle\Entity\ProductTemplate`
- Remove class `Pim\Bundle\CatalogBundle\EventSubscriber\ComputeProductTemplateRawValuesSubscriber`
- Remove class `Pim\Bundle\CatalogBundle\EventSubscriber\LoadProductTemplateValuesSubscriber`
- Remove class `Pim\Bundle\CatalogBundle\EventSubscriber\ProductTemplateAttributeSubscriber`
- Remove class `Pim\Component\Catalog\Builder\ProductTemplateBuilder`
- Remove class `Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface`
- Remove class `Pim\Component\Catalog\Factory\ProductTemplateFactory`
- Remove class `Pim\Component\Catalog\Manager\ProductTemplateApplier`
- Remove class `Pim\Component\Catalog\Manager\ProductTemplateApplierInterface`
- Remove class `Pim\Component\Catalog\Manager\ProductTemplateApplierInterface`
- Remove class `Pim\Component\Catalog\Manager\ProductTemplateMediaManager`
- Remove class `Pim\Component\Catalog\Model\ProductTemplateInterface`
- Remove class `Pim\Component\Catalog\Repository\ProductTemplateRepositoryInterface`
- Remove class `Pim\Component\Catalog\Updater\ProductTemplateUpdater`
- Remove class `Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface`
- Remove class `Pim\Component\Catalog\Validator\Constraints\UniqueVariantGroup`
- Remove class `Pim\Component\Catalog\Validator\Constraints\VariantGroupValues`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\VariantGroupProductDatasource`
- Remove class `Pim\Bundle\VersioningBundle\Normalizer\Flat\VariantGroupNormalizer`
- Remove class `Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer`
- Remove class `Pim\Component\Catalog\Updater\Setter\VariantGroupFieldSetter`
- Remove class `Pim\Component\Catalog\Updater\VariantGroupUpdater`
- Remove class `Pim\Component\Catalog\Validator\Constraints\HasVariantAxes`
- Remove class `Pim\Component\Catalog\Validator\Constraints\HasVariantAxesValidator`
- Remove class `Pim\Component\Catalog\Validator\Constraints\UniqueVariantGroupType`
- Remove class `Pim\Component\Catalog\Validator\Constraints\UniqueVariantGroupTypeValidator`
- Remove class `Pim\Component\Catalog\Validator\Constraints\UniqueVariantGroupValidator`
- Remove class `Pim\Component\Catalog\Validator\Constraints\VariantGroupAxis`
- Remove class `Pim\Component\Catalog\Validator\Constraints\VariantGroupAxisValidator`
- Remove class `Pim\Component\Catalog\Validator\Constraints\VariantGroupValuesValidator`
- Remove class `Pim\Component\Catalog\Manager\VariantGroupAttributesResolver`
- Remove method `getTypeByGroup` from `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
- Remove method `getAllGroupsExceptVariantQB` from `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
- Remove `$isVariant` to `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface::findTypeIds()`
- Remove method `addAxisAttribute` from `Pim\Component\Catalog\Model\GroupInterface`
- Remove method `removeAxisAttribute` from `Pim\Component\Catalog\Model\GroupInterface`
- Remove method `getAxisAttributes` from `Pim\Component\Catalog\Model\GroupInterface`
- Remove method `setAxisAttributes` from `Pim\Component\Catalog\Model\GroupInterface`
- Remove method `getProductTemplate` from `Pim\Component\Catalog\Model\GroupInterface`
- Remove method `setProductTemplate` from `Pim\Component\Catalog\Model\GroupInterface`
- Remove method `isVariant` from `Pim\Component\Catalog\Model\GroupTypeInterface`
- Remove method `setVariant` from `Pim\Component\Catalog\Model\GroupTypeInterface`
- Remove method `getVariantGroup` from `Pim\Component\Catalog\Model\ProductInterface`
- Remove method `hasAttributeInVariantGroup` from `Pim\Component\Catalog\Model\ProductInterface`
- Remove method `findAllForVariantGroup` from `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove method `getEligibleProductsForVariantGroup` from `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove method `findProductIdsForVariantGroup` from `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Change `Pim\Component\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface::convert()` signature, it return `Pim\Component\Connector\ArrayConverter\FlatToStandard\ConvertedField` instead of an array

# 2.0.0 (2017-09-28)

# 2.0.0-BETA1 (2017-09-28)

## Better manage products with variants!

- PIM-6560: Product model - As Julia, I would like to have the "complete variant products" on a product model
- PIM-6451: Variant product - As Julia, I would like to enrich a variant product

# 2.0.0-ALPHA1 (2017-09-25)

## Better manage products with variants!

- PIM-6330: Family variant - As Julia I would like to import a new variant of a family
- PIM-6331: Family variant - As Julia, I would like to export a family variant
- PIM-6333: Import - As Julia, I would like to import products models from my ERP
- PIM-6335: Import - As Julia, I would like to import variant products from my ERP
- PIM-6336: Export - As Julia, I would like to export variant products
- PIM-6338: Product model - As Julia I would like to enrich a product model
- PIM-6347: Variant product - As Julia, I would like to have a global completeness on a variant product
- PIM-6348: Variant product - As Julia, I would like to navigate to my product model and variant products
- PIM-6352: Grid - As Julia, I would like to display the products models in the grid
- PIM-6353: Grid - As Julia, I would like to search products with variants on custom attributes
- PIM-6360: Bulk actions - As Julia, I would like to edit some products models sequentially
- PIM-6441: Fixtures catalog
- PIM-6442: Extract values behavior from product
- PIM-6444: No variant group - Remove the entry menu + the screens
- PIM-6455: Family variant - As Julia, I would like to update a family variant by import
- PIM-6669: Product model - As Julia I would like to enrich a sub product model
- PIM-6674: No bulk actions managed with products models
- PIM-6732: No variant group - Remove the bulk operation "Add to a variant group"
- PIM-6734: No variant group - Remove the CE permissions for variant groups
- PIM-6735: No variant group - Remove the variant groups exports and imports
- PIM-6737: Export - As Julia, I would like to export products models
- PIM-6741: Product model - Add product model code in the PEF header
- PIM-6742: Import - As Julia, I would like to update products models by import from my ERP
- PIM-6743: Import - As Julia I would like to update variant products from my ERP
- PIM-6768: Grid - ID column with "product model code" or "product identifier"
- PIM-6793: Grid - Different display for products models
- PIM-6801: Settings - As Julia I would like to create an attribute with the code "parent"
- PIM-6818: No mass delete with product models

## Better UI\UX!

- PIM-6288: Update flash messages design
- PIM-6289: Update JSTree design
- PIM-6294: Update switch design
- PIM-6374: Add columns for product navigation
- PIM-6391: Update comments design
- PIM-6403: Update panels design to use dropdown selectors
- PIM-6404: Update buttons design
- PIM-6409: Update all the title containers design
- PIM-6290: Update the main navigation design
- PIM-6397: Enable Search filter on all grids
- PIM-6406: Update job profile show page to include last executions
- TIP-764: Mass edit has been redone
- PIM-6412: GRID Change product information -  As Julia, I would like to use the mass edit with a new UI
- PIM-6474: [IMP] Main menu does not display the "highlighted" elements
- PIM-6486: [IMP] There are remaining borders in some tabs
- PIM-6495: PEF HEADER- As Julia, I would like to enrich product in a brand new UI
- PIM-6505: GRID - As Julia I would like to switch to another working context
- PIM-6506: PEF ATTRIBUTE- As Julia, I would like to enrich product in a brand new UI
- PIM-6507: PEF COMPLETENESS - As Julia, I would like to display product completeness in a brand new UI
- PIM-6508: PEF CATEGORY - As Julia, I would like to enrich product in a brand new UI
- PIM-6513: [IMP] Display no result screen with the grid header
- PIM-6517: GRID FILTERS - As Julia, I would like to Manage filters on the product grid
- PIM-6521: GRID EXPORT - As Julia, I would like to quick export products from the grid
- PIM-6524: GRID VIEW - As Julia, I would like to create a view
- PIM-6533: [IMP] Export and Import icons are mixed in the menu
- PIM-6535: [IMP] Import / Export: display the Profile's name in purple
- PIM-6545: ASSET GRID- As Julia, I would like to display the assets in a brand new grid
- PIM-6551: PEF ASSOCIATION - As Julia, I would like to view Association in a new UI
- PIM-6552: PEF ASSOCIATION  - As Julia, I would like to add/remove an association
- PIM-6553: GRID VIEW - As Julia I would like to update a view
- PIM-6554: PUBLISHED Products GRID - As Julia, I would like to check the published products
- PIM-6555: GRID VIEW - As Julia, I would like to display a view list
- PIM-6556: PEF COMPARE - As Julia, I would like to copy product information from a locale/ or a channel
- PIM-6570: [IMP] PEF - Completeness is not updated after a single Save
- PIM-6574: GRID CATEGORY - As Julia, I would like to display the Category panel in the product grid
- PIM-6575: GRID VIEW - As Julia, I would like to delete a view
- PIM-6576: GRID PRODUCT - As Julia, I would like to create a product in a brand new UI
- PIM-6577: PEF - As Julia I would like to add an option to a simple or multiselect attribute with a new design
- PIM-6578: PEF - As Julia I would like to view the product's version content in a new UI
- PIM-6579: *** PEF HIGHLIGHT - As Julia, I would like to see at a glance empty required attributes
- PIM-6580: PEF COMMENT - As Julia, I would like to add a comment on a product
- PIM-6581: PEF COMMENT - As Julia, I would like to reply to a comment
- PIM-6582: PEF Restore - As Julia, I would like to restore a product version in a new UI
- PIM-6587: GRID - As Julia, I would like to switch from View to Project
- PIM-6606: IMPORT EXPORT - As Julia, I would like to search for a profile in a new designed drop-down
- PIM-6607: IMPORT - As Julia I would like to upload a file
- PIM-6608: IMPORT EXPORT - As Julia I would like to edit an import or export profile properties & settings in a new UI
- PIM-6613: [IMP] Settings Attributes - Attribute's name isn't display in purple
- PIM-6614: [IMP] Settings Attributes - type and group dropdowns don't have the right design
- PIM-6619: ATTRIBUTE CREATION - As Julia, I would like to create an attribute
- PIM-6631: GROUP TYPE CREATION - As Julia, I would like to create a group type
- PIM-6640: CHANNEL SEARCH - As Julia, I would like to look for a channel using its label
- PIM-6643: [IMP] wrong design when you log out
- PIM-6645: PEF SEQ EDIT- As Julia, I would like to edit sequentially products
- PIM-6653: FAMILY EDIT - As Julia, I would like to mass edit families
- PIM-6658: PEF COMPLETENESS & FILTER
- PIM-6694: Add UX Design
- PIM-6746: PEF/ Grid - As Julia, I would like to keep the working context in the header
- PIM-6749: PEF - As Mary, I would like to send a comment with my proposal in a new UI
- PIM-6751: PEF - As Mary, I would like to click on a new designed button to send my proposal
- PIM-6753: COMMON - As Julia I would like "empty" screen to be displayed in a new UI
- PIM-6755: COMMON - As Julia I would like "no result" screen to be displayed in a new UI
- PIM-6758: PEF - As Julia, I would like to see the missing required attributes in the PEF header
- PIM-6778: PEF HEADER - As Julia, I would like to view the identifier in the PEF header

## New API endpoints

- API-347: [Migration script] Label is now mandatory when generating clientId via cmd line
- API-351: As Peter/Philip, I want that API connection revocation takes effect instantly
- API-324: Label is now mandatory when generating client Id via command line
- API-335: Refactor the ProductController in ApiBundle
- API-312: Add UI to manage (create/revoke) API connections
- API-218: Get the list of measure families with their respecting units
- API-252: Get a single measure family
- API-161: Update partially a list of channels
- API-205: Update partially a single channel
- API-159: Update partially a list of association types
- API-184: Get a single association type
- API-160: Get a list of association types
- API-207: Update partially a single association type
- API-206: Create a new association type
- API-204: Create a new channel
- API-202: Create a new attribute group
- API-182: Get a single currency
- API-165: Get a list of currencies
- API-163: Update partially a list of attribute groups
- API-203: Update partially a single attribute group
- API-183: Get a single attribute group
- API-166: Get a list of attribute groups

## Other Functional Improvements

- API-324: Convert label option to mandatory argument in command `pim:oauth-server:create-client`
- TIP-718: Update group types form
- PIM-6291: Adds attribute used as the main picture in the UI for each family (attribute_as_image)
- GITHUB-4877: Update some tooltips messages of the export builder, Cheers @Milie44!
- GITHUB-5949: Fix the deletion of a job instance (import\export) from the job edit page, cheers @BatsaxIV !
- PIM-6531: Have English language as default language for new users
- PIM-6761: Add filter on required attributes

## Technical improvements

- TIP-711: Rework job execution reporting page with the new PEF architecture
- TIP-724: Refactoring of the 'Settings\Association types' index screen using 'pim\common\grid'
- TIP-725: Generalization of the refactoring made in the TIP-724 for all screen containing a simple grid
- TIP-734: Menu and index page is now using the new PEF architecture
- GITHUB-6174: Show a loading mask during the file upload in the import jobs
- TIP-730: Reworking of the creation popin for basic entities
- TIP-732: Rework the attribute form using the PEF architecture
- PIM-6448: Product model CRUD API
- TIP-747: Migrate to Symfony 3.3
- PIM-6740: Separe installation state (installed) from config file
- API-359: Move notified user of a job into the configuration parameters of the job
- TIP-733: Replace `oro/datafilter-builder` with `filters-list` and `filters-button`
- PIM-6645: removed all backend related sequential edit classes. It's now managed in the frontend.
- API-331: As a devops, I should be able to configure where files generated by jobs are archived
- API-334: Execution jobs should have all the information to be launched in background
- API-362: Push job executions into a queue that will be consumed by a daemon
- API-359: Configure notification user of a job in the raw_parameters instead of user column

## Remove MongoDB product storage

- Remove container parameter `pim_catalog_product_storage_driver`

- Remove model `src/Pim/Bundle/CatalogBundle/Resources/config/model/doctrine/Association.mongodb.yml`
- Remove model `src/Pim/Bundle/CatalogBundle/Resources/config/model/doctrine/Completeness.mongodb.yml`
- Remove model `src/Pim/Bundle/CatalogBundle/Resources/config/model/doctrine/Product.mongodb.yml`
- Remove model `src/Pim/Bundle/VersioningBundle/Resources/config/model/doctrine/Version.mongodb.yml`

- Remove constants `DOCTRINE_ORM` and `DOCTRINE_MONGODB_ODM` from `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension`

- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry`
- Remove service `akeneo_storage_utils.doctrine.smart_manager_registry`

- Remove repository `Akeneo\Bundle\ClassificationBundle\Doctrine\Mongo\Repository\AbstractItemCategoryRepository`
- Remove repository `Pim\Bundle\ApiBundle\Doctrine\MongoDBODM\Repository\ProductRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\CompletenessRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductCategoryRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductMassActionRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductValueCounterRepository`
- Remove repository `Pim\Bundle\CatalogBundle\spec\Doctrine\MongoDBODM\Repository\CompletenessRepositorySpec`
- Remove repository `Pim\Bundle\CatalogBundle\spec\Doctrine\MongoDBODM\Repository\ProductRepositorySpec`
- Remove repository `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\VersionRepository`

- Remove event listener `Akeneo\Bundle\StorageUtilsBundle\EventListener\MongoDBODM\ResolveTargetEntityListener`
- Remove event listener `Pim\Bundle\DataGridBundle\EventListener\MongoDB\ConfigureHistoryGridListener`
- Remove event subscriber `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber`
- Remove event subscriber `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EnsureIndexesSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\ProductRelatedEntityRemovalSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\RemoveOutdatedProductsFromAssociationsSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\SetNormalizedProductDataSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\SetProductsSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\TimestampableSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\UpdateNormalizedProductDataSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\ORM\InjectProductReferenceSubscriber`
- Remove event subscriber `Pim\Bundle\VersioningBundle\EventSubscriber\MongoDBODM\AddProductVersionSubscriber`

- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Collections\ReferencedCollectionFactory`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Collections\ReferencedCollection`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor\CursorFactory`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor\Cursor`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\MappingsOverrideConfigurator`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Types\Entities`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Types\Entity`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory`
- Remove class `Pim\Bundle\CatalogBundle\Command\CleanMongoDBCommand`
- Remove class `Pim\Bundle\CatalogBundle\Command\MongoDBIndexCreatorCommand`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractAttributeFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractFieldFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BooleanFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateTimeFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\FamilyFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MediaFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\NumberFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionsFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\ProductIdFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\StringFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexCreator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexPurger`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\AbstractQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\AttributeAsLabelUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\AttributeDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\ChannelDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\FamilyDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\FamilyLabelUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionCodeUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionValueUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\NormalizedDataQueryGeneratorInterface`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\OptionCodeUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\OptionDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\OptionValueUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver\ProductSaver`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\BaseSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\CompletenessSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\FamilySorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\InGroupSorter`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\AssociationNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\DateTimeNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\GenericNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\MetricNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductPriceNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductValueNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\VersionNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\AttributeOptionNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\CompletenessNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\DateTimeNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\FamilyNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\FileNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\GroupNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\MetricNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\ProductNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\ProductPriceNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\ProductValueNormalizer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\AssociatedProductHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\ObjectHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\ObjectIdHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\AssociationTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\CompletenessTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\DateTimeTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FamilyTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\GroupsTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\ProductHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\OptionsTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer`
- Remove class `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\ResolverPass`
- Remove class `Pim\Bundle\DataGridBundle\Extension\Pager\MongoDbOdm\Pager`
- Remove class `Pim\Bundle\DataGridBundle\Extension\Sorter\MongoDbOdm\FieldSorter`
- Remove class `Pim\Bundle\FilterBundle\Datasource\MongoDbOdm\OdmFilterDatasourceAdapter`
- Remove class `Pim\Bundle\FilterBundle\Datasource\MongoDbOdm\OdmFilterProductDatasourceAdapter`
- Remove class `Pim\Bundle\ReferenceDataBundle\DataGrid\Datasource\ResultRecord\MongoDbOdm\Product\ReferenceDataTransformer`
- Remove class `Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Filter\ReferenceDataFilter`
- Remove class `Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Sorter\ReferenceDataSorter`
- Remove class `Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer\Document\ReferenceDataNormalizer`
- Remove class `Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer\NormalizedData\ReferenceDataNormalizer`
- Remove class `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\BulkVersionBuilder`
- Remove class `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\Saver\BulkVersionSaver`
- Remove class `Pim\Bundle\VersioningBundle\UpdateGuesser\MongoDBODM\ContainsProductsUpdateGuesser`
- Remove class `upgrades/UpgradeHelper.php`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\AbstractAttributeFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\AbstractFieldFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\AbstractFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BooleanFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\CompletenessFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateTimeFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\FamilyFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\GroupsFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\IdentifierFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MediaFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\NumberFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionsFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\PriceFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\ProductIdFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\CompletenessJoin`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\BaseSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\CompletenessSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\FamilySorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\InGroupSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\IsAssociatedSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\MetricSorter`
- Remove class `ReferenceDataBundle\Doctrine\ORM\Filter\ReferenceDataFilter`
- Remove class `ReferenceDataBundle\Doctrine\ORM\Sorter\ReferenceDataSorter`

- Change the constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectCodeResolver` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Pim\Bundle\CommentBundle\Controller\CommentController` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Pim\Bundle\VersioningBundle\UpdateGuesser\VariantGroupUpdateGuesser` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Pim\Component\Catalog\Repository\GroupRepositoryInterface` and to remove the `$groupClass` argument
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolver` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Oro\Bundle\SecurityBundle\Acl\Extension\EntityClassResolver` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Symfony\Bridge\Doctrine\RegistryInterface\RegistryInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\ConfigureSortersListener` to remove `Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver` to remove `Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver`

## Remove variant groups

- Remove methods `countVariantGroupAxis`, `getAllGroupsExceptVariant`, ``, `getAllVariantGroups`, `hasAttribute` and `getVariantGroupByProductTemplate` from `Pim\Component\Catalog\Repository\GroupRepositoryInterface`
- Remove method `hasAttributeInVariantGroup` from `Pim\Component\Catalog\Repository\ProductRepositoryInterface`

- Remove class `Pim\Component\Connector\ArrayConverter\FlatToStandard\VariantGroup`
- Remove class `Pim\Component\Connector\ArrayConverter\StandardToFlat\VariantGroup`
- Remove class `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\VariantGroupCsvExport`
- Remove class `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\VariantGroupXlsxExport`
- Remove class `Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\VariantGroupCsvImport`
- Remove class `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\VariantGroupCsvExport`
- Remove class `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\VariantGroupXlsxExport`
- Remove class `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\VariantGroupCsvImport`
- Remove class `Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\VariantGroupXlsxImport`
- Remove class `Pim\Component\Connector\Processor\Denormalization\VariantGroupProcessor`
- Remove class `Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor`
- Remove class `Pim\Component\Connector\Reader\Database\VariantGroupReader`
- Remove class `Pim\Component\Connector\Reader\File\Csv\VariantGroupReader`
- Remove class `Pim\Component\Connector\Writer\Database\VariantGroupWriter`
- Remove class `Pim\Component\Connector\Writer\File\CSV\VariantGroupWriter`
- Remove class `Pim\Component\Connector\Writer\File\XLSX\VariantGroupWriter`
- Remove class `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductToVariantGroupProcessor`
- Remove class `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredVariantGroupProductReader`
- Remove class `Pim\Bundle\EnrichBundle\Controller\Rest\VariantGroupAttributeController`
- Remove class `Pim\Bundle\EnrichBundle\Controller\Rest\VariantGroupController`
- Remove class `Pim\Bundle\EnrichBundle\Filter\VariantGroupEditDataFilter`
- Remove class `Pim\Bundle\EnrichBundle\Form\Subscriber\AddVariantGroupAxesSubscriber`
- Remove class `Pim\Bundle\EnrichBundle\Form\Type\VariantGroupType`
- Remove class `Pim\Bundle\VersioningBundle\UpdateGuesser\VariantGroupUpdateGuesser`

- Rename class `Pim\Bundle\EnrichBundle\Normalizer\VariantGroupNormalizer` into `Pim\Bundle\EnrichBundle\Normalizer\GroupNormalizer`

- Remove service `pim_connector.array_converter.flat_to_standard.variant_group` and class parameter `pim_connector.array_converter.flat_to_standard.variant_group.class`
- Remove service `pim_connector.array_converter.standard_to_flat.variant_group` and class parameter `pim_connector.array_converter.standard_to_flat.variant_group.class`
- Remove service `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_csv_export` and class parameter `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_csv_export.class`
- Remove service `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_xlsx_export` and class parameter `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_xlsx_export.class`
- Remove service `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_csv_import` and class parameter `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_csv_import.class`
- Remove service `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_xlsx_import` and class parameter `pim_connector.job.job_parameters.constraint_collection_provider.variant_group_xlsx_import.class`
- Remove service `pim_connector.job.job_parameters.default_values_provider.variant_group_csv_export` and class parameter `pim_connector.job.job_parameters.default_values_provider.variant_group_csv_export.class`
- Remove service `pim_connector.job.job_parameters.default_values_provider.variant_group_xlsx_export` and class parameter `pim_connector.job.job_parameters.default_values_provider.variant_group_xlsx_export.class`
- Remove service `pim_connector.job.job_parameters.default_values_provider.variant_group_csv_import` and class parameter `pim_connector.job.job_parameters.default_values_provider.variant_group_csv_import.class`
- Remove service `pim_connector.job.job_parameters.default_values_provider.variant_group_xlsx_import` and class parameter `pim_connector.job.job_parameters.default_values_provider.variant_group_xlsx_import.class`
- Remove service `pim_connector.processor.denormalization.variant_group` and class parameter `pim_connector.processor.denormalization.variant_group.class`
- Remove service `pim_connector.processor.normalization.variant_group` and class parameter `pim_connector.processor.normalization.variant_group.class`
- Remove service `pim_connector.reader.database.variant_group` and class parameter `pim_connector.reader.database.variant_group.class`
- Remove service `pim_connector.reader.file.csv_variant_group` and class parameter `pim_connector.reader.file.csv_variant_group.class`
- Remove service `pim_connector.writer.database.variant_group` and class parameter `pim_connector.writer.database.variant_group.class`
- Remove service `pim_connector.writer.file.csv_variant_group` and class parameter `pim_connector.writer.file.csv_variant_group.class`
- Remove service `pim_connector.writer.file.xlsx_variant_group` and class parameter `pim_connector.writer.file.xlsx_variant_group.class`
- Remove service `pim_enrich.connector.processor.mass_edit.product.add_to_variant_group` and class parameter `pim_enrich.connector.processor.mass_edit.product.add_to_variant_group.class`
- Remove service `pim_enrich.connector.reader.mass_edit.variant_group_product` and class parameter `pim_enrich.connector.reader.mass_edit.variant_group_product.class`
- Remove service `pim_enrich.controller.rest.variant_group` and class parameter `pim_enrich.controller.rest.variant_group.class`
- Remove service `pim_enrich.controller.rest.variant_group_attribute` and class parameter `pim_enrich.controller.rest.variant_group_attribute.class`
- Remove service `pim_enrich.filter.variant_group_edit_data` and class parameter `pim_enrich.filter.variant_group_edit_data.class`
- Remove service `pim_enrich.form.subscriber.add_variant_group_axes` and class parameter `pim_enrich.form.subscriber.add_variant_group_axes.class`
- Remove service `pim_enrich.form.type.variant_group` and class parameter `pim_enrich.form.type.variant_group.class`
- Remove service `pim_versioning.update_guesser.variant_group` and class parameter `pim_versioning.update_guesser.variant_group.class`

- Remove service `pim_connector.reader.file.xlsx_variant_group`
- Remove service `pim_connector.job.csv_variant_group_export`
- Remove service `pim_connector.job.xlsx_variant_group_export`
- Remove service `pim_connector.job.csv_variant_group_import`
- Remove service `pim_connector.job.xlsx_variant_group_import`
- Remove service `pim_connector.step.csv_variant_group.import`
- Remove service `pim_connector.step.xlsx_variant_group.import`
- Remove service `pim_connector.step.csv_variant_group.export`
- Remove service `pim_connector.step.xlsx_variant_group.export`
- Remove service `pim_enrich.form.variant_group`
- Remove service `pim_enrich.form.handler.variant_group`
- Remove service `pim_enrich.job.add_to_variant_group`
- Remove service `pim_enrich.normalizer.variant_group_violation`
- Remove service `pim_enrich.step.add_to_variant_group.mass_edit`
- Remove service `pim_installer.job_parameters.constraints.variant_group_csv_import`
- Remove service `pim_installer.job_parameters.defaults.variant_group_csv_import`
- Remove service `pim_installer.job.fixtures_variant_group_csv`

- Rename service `pim_enrich.provider.structure_version.variant_group` into `pim_enrich.provider.structure_version.group`

- Remove parameter `pim_installer.job_name.fixtures_variant_group_csv`

- Rename class parameter `pim_enrich.normalizer.variant_group_violation.class` into `pim_enrich.normalizer.group_violation.class`

## BC breaks

### Doctrine mapping

- PIM-6448: `Pim\Component\Catalog\Model\AbstractProduct` becomes a Doctrine mapped superclass

### Classes

- Remove class `Pim\Bundle\EnrichBundle\Form\Type\AttributeRequirementType`
- PIM-6442: Rename `Pim\Bundle\VersioningBundle\Normalizer\Flat\AbstractProductValueDataNormalizer` to `Pim\Bundle\VersioningBundle\Normalizer\Flat\AbstractValueDataNormalizer`
- PIM-6442: Rename `Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductValueNormalizer` to `Pim\Bundle\VersioningBundle\Normalizer\Flat\ValueNormalizer`
- PIM-6442: Rename `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker` to `Pim\Component\Catalog\Completeness\Checker\ValueCompleteChecker`
- PIM-6442: Rename `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to `Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\DateProductValueFactory` to `Pim\Component\Catalog\Factory\Value\DateValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\MediaProductValueFactory` to `Pim\Component\Catalog\Factory\Value\MediaValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\MetricProductValueFactory` to `Pim\Component\Catalog\Factory\Value\MetricValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\OptionProductValueFactory` to `Pim\Component\Catalog\Factory\Value\OptionValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\OptionsProductValueFactory` to `Pim\Component\Catalog\Factory\Value\OptionsValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\PriceCollectionProductValueFactory` to `Pim\Component\Catalog\Factory\Value\PriceCollectionValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface` to `Pim\Component\Catalog\Factory\Value\ValueFactoryInterface`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\ScalarProductValueFactory` to `Pim\Component\Catalog\Factory\Value\ScalarValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValueCollectionFactory` to `Pim\Component\Catalog\Factory\ProductValueCollectionFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValueFactory` to `Pim\Component\Catalog\Factory\ValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\DateProductValue` to `Pim\Component\Catalog\Value\DateValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\MediaProductValue` to `Pim\Component\Catalog\Value\MediaValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\MetricProductValue` to `Pim\Component\Catalog\Value\MetricValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\OptionProductValue` to `Pim\Component\Catalog\Value\OptionValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\OptionsProductValue` to `Pim\Component\Catalog\Value\OptionsValue`
- PIM-6442: Rename `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductValue` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Value`
- PIM-6442: Rename `Pim\Component\Enrich\Converter\EnrichToStandard\ProductValueConverter` to `Pim\Component\Enrich\Converter\EnrichToStandard\ValueConverter`
- PIM-6442: Rename `Pim\Component\Enrich\Converter\StandardToEnrich\ProductValueConverter` to `Pim\Component\Enrich\Converter\StandardToEnrich\ValueConverter`
- PIM-6442: Rename `Pim\Component\ReferenceData\Factory\ProductValue\ReferenceDataCollectionProductValueFactory` to `Pim\Component\ReferenceData\Factory\Value\ReferenceDataCollectionValueFactory`
- PIM-6442: Rename `Pim\Component\ReferenceData\Factory\ProductValue\ReferenceDataProductValueFactory` to `Pim\Component\ReferenceData\Factory\Value\ReferenceDataValueFactory`
- PIM-6442: Rename `Pim\Component\ReferenceData\ProductValue\ReferenceDataCollectionProductValue` to `Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue`
- PIM-6442: Rename `Pim\Component\ReferenceData\ProductValue\ReferenceDataProductValue` to `Pim\Component\ReferenceData\Value\ReferenceDataValue`
- PIM-6442: Rename `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductValueValueFactoryPass` to `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterValueFactoryPass`
- TIP-764: Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface` and all inherited classes
- PIM-6740: Remove `Pim\Bundle\InstallerBundle\Persister\YamlPersister`
- PIM-6333: Rename `Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface` to `Pim\Component\Catalog\Comparator\Filter\FilterInterface`
- PIM-6333: Rename `Pim\Component\Catalog\Comparator\Filter\ProductFilter` to `Pim\Component\Catalog\Comparator\Filter\EntityWithValuesFilter`
- PIM-6333: Rename `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductDelocalized` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\EntityWithValuesDelocalized`
- PIM-6732: Remove `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductToVariantGroupProcessor`
- PIM-6732: Remove `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredVariantGroupProductReader`
- PIM-6732: Remove `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- PIM-6732: Remove `Pim\Component\Enrich\Converter\MassOperationConverter`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Controller\SequentialEditController`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Manager\SequentialEditManager`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Helper\SortHelper`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Factory\SequentialEditFactory`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Entity\SequentialEdit`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Entity\Repository\SequentialEditRepository`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\ParamConverter\ConfigurableParamConverter`
- PIM-6645: Remove `Pim\Bundle\EnrichBundle\Normalizer\SequentialEditNormalizer`

### Constructors

- Change the constructor of `Pim\Component\Catalog\Comparator\Filter\ProductFilter` to add `Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface`
- Change the constructor of `Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Oro\Bundle\DataGridBundle\Datagrid\RequestParameters` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\GroupColumnsConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractor` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\AddParametersToProductGridListener` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Component\Catalog\Updater\AssociationTypeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\ChannelUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\ChannelController` to add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`,
 `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`, `Symfony\Component\Validator\Validator\ValidatorInterface`,`Symfony\Component\Routing\RouterInterface`,
  `Pim\Bundle\ApiBundle\Stream\StreamResourceResponse` and `Akeneo\Component\StorageUtils\Saver\SaverInterface` before last parameter
- Change the constructor of `Pim\Component\Connector\Writer\Database\ProductWriter` to replace `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` by `Akeneo\Component\StorageUtils\Cache\CacheClearerInterface`.
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeGroupUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\JobTrackerController` to add `Oro\Bundle\SecurityBundle\SecurityFacade` and add an associative array
- Change the constructor of `Pim\Component\Catalog\Manager\CompletenessManager` to remove the completeness class.
- Change the constructor of `Pim\Component\Catalog\Updater\FamilyUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `logDir` (string)
- Change the constructor of `Pim\Bundle\EnrichBundle\Twig\AttributeExtension` to remove `pim_enrich.attribute_icons`
- Remove OroNotificationBundle
- Remove createAction from `Pim\Bundle\EnrichBundle/Controller/AssociationTypeController.php`
- Remove `Pim\Bundle\EnrichBundle\Controller\FamilyController.php`
- Remove `Pim\Bundle\EnrichBundle\Controller\VariantGroupController.php`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeGroupController` to add `Oro\Bundle\SecurityBundle\SecurityFacade`, `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`, `Symfony\Component\Validator\ValidatorInterface`, `Akeneo\Component\StorageUtils\Saver\SaverInterface`, `Akeneo\Component\StorageUtils\Remover\RemoverInterface`, `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements` to remove `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` and remove `Pim\Component\Catalog\Factory\AttributeRequirementFactory`
- Change the constructor of `Pim\Bundle\ApiBundle\EventSubscriber\CheckHeadersRequestSubscriber` to add `Pim\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionValueUpdatedQueryGenerator` to add `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\AttributeOptionNormalizer`
- Change the constructor of `Pim\Component\Catalog\Model\AbstractMetric` to replace `id` by `family`, `unit`, `data`, `baseUnit` and `baseData` (strings)
- Change the constructor of `Pim\Component\Catalog\Factory\MetricFactory` to add `Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter` and `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\MetricDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Converter\MetricConverter` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\PricesDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface` and replace `"Pim\Component\Catalog\Model\ProductPrice"` `Pim\Component\Catalog\Factory\PriceFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder` to remove `Pim\Component\Catalog\Validator\AttributeValidatorHelper`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder` to remove `Pim\Component\Catalog\Validator\AttributeValidatorHelper`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AttributeCopier` and `Pim\Component\Catalog\Updater\Copier\MetricAttributeCopier` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as third argument
- Change the constructor of `Pim\Component\Catalog\Manager\ProductTemplateMediaManager` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` by `Pim\Component\Catalog\Factory\ProductValueFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover` to replace `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` by `Pim\Component\Catalog\Factory\ProductValueFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover` to add `Pim\Component\Catalog\Factory\ProductValueFactory` as third argument
- Change the constructor of `Pim\Component\Catalog\Model\AbstractProductValue` to add `Pim\Component\Catalog\Model\AttributeInterface`, `channel` (string), `locale` (string), `data` (mixed)
- Change the constructor of `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\PricesDenormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as third parameter
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\FamilyFilter` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\GroupsFilter` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductTemplateBuilder` to remove first argument `Symfony\Component\Serializer\Normalizer\NormalizerInterface`, second argument `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`, and last argument `%pim_catalog.entity.product.class%`
- Change the constructor of `Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer` to remove `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\ProductTemplateUpdater` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as second argument
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController` to remove
    `Pim\Component\Catalog\Manager\CompletenessManager`,
    `Doctrine\Common\Persistence\ObjectManager`,
    `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`,
    `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface` and
    `$storageDriver`, and add `Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface`
- Change the constructor of `Pim\Component\Connector\Writer\File\ProductColumnSorter` to replace `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` by `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepositoryInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\VariantGroupUpdater` to replace `Pim\Component\Catalog\BuilderProductBuilderInterface` and `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
    by `Pim\Component\Catalog\Factory\ProductValueFactory`, `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface` and `Akeneo\Component\FileStorage\File\FileStorerInterface`
- Change the constructor of `Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor` to remove `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\Extension\Sorter\Product\ValueSorter` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\ProductDatasource` to remove `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to add `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\ProductUpdater` to add a `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface` as the 3rd argument.
- Change the constructor of `Pim\Component\Catalog\Converter\MetricConverter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\AbstractAttributeAdder` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Setter\AbstractAttributeSetter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Setter\AttributeSetter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\MetricAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\ReferenceData\Updater\Copier\ReferenceDataAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\ReferenceData\Updater\Copier\ReferenceDataCollectionAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeController`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer` to add `Pim\Bundle\VersioningBundle\Manager\VersionManager`, `Symfony\Component\Serializer\Normalizer\NormalizerInterface`, `Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface`, `Akeneo\Component\Localization\Localizer\LocalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to add `uploadTmpDir` (string)
- Change the constructor of `Pim\Component\Connector\Processor\Denormalization\ProductProcessor` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface` as the 3rd argument (variant product builder).
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to add `uploadTmpDir` (string)
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\ProductController` to remove `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` and to add `Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface` and `Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to replace `Pim\Component\Catalog\Manager\AttributeValuesResolver` by `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface`
- Change the constructor of `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver` to replace `Pim\Component\Catalog\Manager\AttributeValuesResolver` by `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\EntityWithValuesBuilder` to replace `Pim\Component\Catalog\Manager\AttributeValuesResolver` by `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface`
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\LocaleController` to add `Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add `Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to remove `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface` and `Pim\Component\Catalog\Factory\ValueFactory`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductTemplateBuilder` to add `Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- Change the constructor of `Pim\Component\Connector\Processor\Normalization\ProductProcessor` to remove `Pim\Component\Catalog\Builder\ProductBuilderInterface` and add `Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductProcessor` to replace `Pim\Component\Catalog\Builder\ProductBuilder` by `Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` to add `Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `Akeneo\Component\Batch\Job\JobParametersValidator`
- Change the constructor of `Pim\Bundle\ConnectorBundle\Launcher\AuthenticatedJobLauncher` to add `Akeneo\Component\Batch\Job\JobParametersValidator`
- Change the constructor of `Pim\Bundle\AnalyticsBundle\DataCollector\VersionDataCollector` to replace `string` by `Pim\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager` - Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to add `uploadTmpDir` (string)
- Change the constructor of `Pim\Component\Connector\Processor\Denormalization\ProductProcessor` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface` as the 3rd argument (variant product builder).
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to add `uploadTmpDir` (string)
- Change the constructor of `Pim\Component\Connector\Processor\Denormalization\ProductProcessor` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface` as the 3rd argument (variant product builder).
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` to remove
    `Symfony\Component\Routing\RouterInterface`,
    `Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface`,
    `Symfony\Component\Form\FormFactoryInterface`,
    `Pim\Bundle\UserBundle\Context\UserContext`,
    `Oro\Bundle\SecurityBundle\SecurityFacade`,
    `Pim\Bundle\EnrichBundle\Manager\SequentialEditManager`,

### Methods

- Change `Pim\Component\Catalog\Model\FamilyInterface` to add `setAttributeAsImage` and `getAttributeAsImage`
- Remove method `addMissingProductValues` of `Pim\Component\Catalog\Builder\ProductBuilderInterface` (this method is now handled by `Pim\Component\Catalog\ValuesFiller\ProductValuesFiller::fillMissingValues`)
- Remove method `getFamily` of `Pim\Component\Catalog\Model\ProductInterface`
- PIM-6732: Remove `AddProductToVariantGroupProcessor` from `Pim\Component\Catalog\Repository\ProductRepositoryInterface`

### Type hint

- Add type hint `Akeneo\Component\Batch\Model\JobExecution` to the return of the function `launch` of `Akeneo\Bundle\BatchBundle\Launcher`
- Add type hint `array` to the return of the function `resolveEligibleValues` of `Pim\Component\Catalog\Manager\AttributeValuesResolver`

### Configuration
- PIM-6740: Remove `installed` parameter from parameters.yml.dist , parameters_test.yml.dist and config.yml

### Others

- Add method `findCategoriesItem` to `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`
- Add method `getAssociatedProductIds` to `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove useless method `applyFilterByIds` of `Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface`
- Remove useless method `getLocalesQB` of `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`
- Remove useless method `findTypeIds` of `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
- Remove useless methods `getChoicesByType`, `countVariantGroups`, `getVariantGroupsByIds`, `getAllVariantGroupIds` and `getVariantGroupsByAttributeIds` of `Pim\Component\Catalog\Repository\GroupRepositoryInterface`
- Remove useless method `findAttributeIdsFromFamilies` of `Pim\Component\Catalog\Repository\FamilyRepositoryInterface`
- Change visibility from public to protected of `getActivatedCurrenciesQB` method of `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`
- Remove useless methods `findAllWithTranslations` and `getAttributeGroupsFromAttributeCodes` of `Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface`
- Remove useless method `countForAssociationType` of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface`
- Remove useless methods `countChildren` and `search` of `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Remove useless methods `buildByChannelAndCompleteness`, `setAttributeRepository` and `getObjectManager`  of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove useless methods `findWithGroups` and `getNonIdentifierAttributes` of `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Remove OroNotificationBundle
- Extract and rename method `valueExists` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface` into `Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface`::`uniqueDataExistsInAnotherProduct`.
- Remove methods `searchAfterOffset`, `searchAfterIdentifier` and `count` of `Pim\Component\Api\Repository\ProductRepositoryInterface`
- Extract methods `schedule*` of `Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface` into a `Pim\Component\Catalog\Completeness\CompletenessRemoverInterface`. Methods `schedule`, `scheduleForFamily` and `scheduleForChannelAndLocale` have been renamed respectively `removeForProduct`, `removeForFamily` and `removeForChannelAndLocale`.
- Remove method `findOneById` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`.
- Move class `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\DummyFilter` to `Pim\Bundle\EnrichBundle\ProductQueryBuilder\Filter\DummyFilter` as this filter is just for UI concerns
- Rename class `Pim\Component\Catalog\Completeness\Checker\ChainedProductValueCompleteChecker`  to `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker`
- Change the method `isComplete` of `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to make `Pim\Component\Catalog\Model\ChannelInterface` and `Pim\Component\Catalog\Model\LocaleInterface` mandatory.
- Change the method `supportsValue` of `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to add `Pim\Component\Catalog\Model\ChannelInterface` and `Pim\Component\Catalog\Model\LocaleInterface`.
- Remove class `Pim\Component\Catalog\Completeness\Checker\EmptyChecker`
- Remove classes `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AbstractEntityDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AssociationDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\CategoryDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\FamilyDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\GroupDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AssociationDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValueDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValuesDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\BaseValueDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionsDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\PricesDenormalizer`
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\MetricDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\DateTimeDenormalizer` and `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\FileDenormalizer`
- Remove service parameters: `pim_serializer.denormalizer.flat.family.class`, `pim_serializer.denormalizer.flat.category.class`, `pim_serializer.denormalizer.flat.group.class`, `pim_serializer.denormalizer.flat.association.class`,
    `pim_serializer.denormalizer.flat.product_value.class`, `pim_serializer.denormalizer.flat.product_values.class`, `pim_serializer.denormalizer.flat.base_value.class`, `pim_serializer.denormalizer.flat.attribute_option.class`,
    `pim_serializer.denormalizer.flat.attribute_options.class`, `pim_serializer.denormalizer.flat.prices.class`, `pim_serializer.denormalizer.flat.metric.class`, `pim_serializer.denormalizer.flat.datetime.class`
    and `pim_serializer.denormalizer.flat.file.class`
- Remove method `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Rename method `getEligibleProductIdsForVariantGroup` to `getEligibleProductsForVariantGroup` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`. And returns a `Akeneo\Component\StorageUtils\Cursor\CursorInterface`.
- Remove methods `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove class `Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser.php`
- Remove service and parameter: `pim_pim_versioning.update_guesser.product_value` and `pim_versioning.update_guesser.product_value.class`
- Add method `setValues` and `setIdentifier` to `Pim\Component\Catalog\Model\ProductInterface`
- Remove method `setNormalizedData` from `Pim\Component\Catalog\Model\ProductInterface`
- Change method `fetchAll` of `Pim\Component\Connector\Processor\BulkMediaFetcher` to use a `Pim\Component\Catalog\Model\ProductValueCollectionInterface` instead of an `Doctrine\Common\Collections\ArrayCollection`
- Remove method `markIndexedValuesOutdated` from `Pim\Component\Catalog\Model\ProductInterface` and `Pim\Component\Catalog\Model\AbstractProduct`
- Remove classes `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\MetricBaseValuesSubscriber` and `Pim\Bundle\CatalogBundle\EventSubscriber\ORM\MetricBaseValuesSubscriber`
- Remove service `pim_catalog.event_subscriber.metric_base_values`
- Remove method `setId`, `getId`, `setValue`, `getValue`, `setBaseUnit`, `setUnit`, `setBaseData`, `setData` and `setFamily` from `Pim\Component\Catalog\Model\MetricInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\MetricInterface`
- Add a new argument `$amount` (string) to `addPriceForCurrency` method of `Pim\Component\Catalog\Builder\ProductBuilderInterface`
- Remove methods `setId`, `getId`, `setValue`, `getValue`, `setCurrency` and `setData` from `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add a new argument `$data` to `addProductValue` method of `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `createProductValue`, `addProductValue`, `addPriceForCurrencyWithData` and `removePricesNotInCurrency` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove classes `Pim\Component\Catalog\Updater\Setter\TextAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\MetricAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\BooleanAttributeSetter`,
    `Pim\Component\Catalog\Updater\Setter\DateAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\NumberAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\SimpleSelectAttributeSetter`,
    `Pim\Component\Catalog\Updater\Setter\MultiSelectAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\PriceCollectionAttributeSetter`, `Pim\Component\ReferenceData\Updater\Setter\ReferenceDataSetter`,
    `Pim\Component\ReferenceData\Updater\Setter\ReferenceDataCollectionSetter`
- Add `Pim\Component\Catalog\Updater\Setter\AttributeSetter`
- Remove classes `Pim\Component\Catalog\Updater\Copier\SimpleSelectAttributeCopier`, `Pim\Component\Catalog\Updater\Copier\MultiSelectAttributeCopier` and `Pim\Component\Catalog\Updater\Copier\PriceCollectionAttributeCopier`
- Rename class `Pim\Component\Catalog\Updater\Copier\BaseAttributeCopier` in `Pim\Component\Catalog\Updater\Copier\AttributeCopier`
- Remove methods `addPriceForCurrency` and `addMissingPrices` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `getId`, `setId`, `getProduct`, `getEntity`, `setProduct`, `setEntity`, `addOption`, `addPrice`, `removePrice`, `RemoveOption`, `addData` and `isRemovable` from `Pim\Component\Catalog\Model\ProductValueInterface` and `Pim\Component\Catalog\Model\AbstractProductValue`
- Remove methods `setData`, `setText`, `setDecimal`, `setOptions`, `setOption`, `setPrices`, `setPrice`, `setBoolean`, `setVarchar`, `setMedia`, `setMetric`, `setScope`, `setLocale`, `setDate` and `setDatetime` from `Pim\Component\Catalog\Model\ProductValueInterface`
    and make them protected in `Pim\Component\Catalog\Model\AbstractProductValue`
- Remove useless class `Pim\Component\Catalog\Validator\ConstraintGuesser\IdentifierGuesser`
- Remove useless service and parameter `pim_catalog.validator.constraint_guesser.identifier` and `pim_catalog.validator.constraint_guesser.identifier.class`
- Remove third argument `$locale` from `addAttributes` method of `Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface`
- Make protected the method `setValues` in `Pim\Component\Catalog\Updater\VariantGroupUpdater`
- Add method `getId` and remove `setMissingCount`, `setChannel`, `setLocale`, `setProduct`, `setRequiredCount` from `Pim\Component\Catalog\Model\CompletenessInterface` and `Pim\Component\Catalog\Model\AbstractCompleteness`
- Remove useless classes `Pim\Bundle\EnrichBundle\Controller\CompletenessController`
- Remove useless service `pim_enrich.controller.completeness` and parameter `pim_enrich.controller.completeness.class`
- Remove class `Pim\Bundle\EnrichBundle\Controller\Rest\CompletenessController`
- Remove service `pim_enrich.controller.rest.completeness` and parameter `pim_enrich.controller.rest.completeness.class`
- Add method `findCodesByIdentifiers` in `Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface`
- Add method `findCodesByIdentifiers` in `Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface`
- Remove class `Pim\Bundle\DataGridBundle\EventListener\AddParametersToVariantProductGridListener`
- Remove methods `createVariantGroupDatagridQueryBuilder` and `createGroupDatagridQueryBuilder` from `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository`
- Extract and rename method `valueExists` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface` into `Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface`::`uniqueDataExistsInAnotherProduct`.
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm\ProductHydrator`
- Remove services `pim_datagrid.datasource.result_record.hydrator.product` and `pim_datagrid.datasource.result_record.hydrator.associated_product`
    and parameters `pim_datagrid.datasource.result_record.hydrator.product.class` and `pim_datagrid.datasource.result_record.hydrator.associated_product.class`
- Remove all standard denormalizers classes `Pim\Component\Catalog\Denormalizer\Standard\*` and services `pim_catalog.denormalizer.standard.*`
- Add argument `Pim\Component\Catalog\Model\ProductInterface` to `addValue` method of `Pim\Component\Catalog\Validator\UniqueValueSet`
- Remove OroNavigationBundle
- Remove OroNotificationBundle
- Remove `Pim\Bundle\EnrichBundle\Controller\FamilyController.php`
- Remove unused `Pim\Component\Catalog\Manager\AttributeGroupManager`
- Remove unused `Pim\Bundle\CatalogBundle\ProductQueryUtility`
- Split `Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer` in two. The original service name (`pim_enrich.normalizer.attribute`) points now to `Pim\Bundle\EnrichBundle\Normalizer\VersionedAttributeNormalizer`
    The arguments of the old normalizer are now divided between both normalizers, also `Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer` is injected into `Pim\Bundle\EnrichBundle\Normalizer\VersionedAttributeNormalizer`.
- PIM-6740: Remove service `pim_installer.yaml_persister`
- PIM-6740: Add exception `Pim\Bundle\InstallerBundle\Exception\UnavailableCreationTimeException`
- PIM-6228: remove escape parameter from csv imports.

### Methods

- Remove `attributeIcon` method from `Pim\Bundle\EnrichBundle\Twig\AttributeExtension`
- Remove the `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` from `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationRepository`
- Rename `BackendType::TEXT = 'text'` to `BackendType::TEXTEAREA = 'textarea'` and `BackendType::VARCHAR = 'varchar'` to `BackendType::TEXT = 'text'` from `Pim\Component\Catalog\AttributeTypes`
- Remove methods `addAttributeToProduct` and `addOrReplaceProductValue` from `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
    These methods are now in `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface` and have been renamed to `addAttribute` and `addOrReplaceValue`.
    For both methods, the `Pim\Component\Catalog\Model\ProductInterface` has been replaced by `Pim\Component\Catalog\Model\EntityWithValuesInterface`.
- Remove methods `getRawValues`, `setRawValues`, `getValues`, `setValues`, `getValue`, `addValue`, `removeValue`, `getAttributes`, `hasAttribute` and `getUsedAttributeCodes` from `Pim\Component\Catalog\Model\ProductInterface`.
    These methods are now in the `Pim\Component\Catalog\Model\EntityWithValuesInterface`.
- Change method `convert` of `Pim\Component\Catalog\Converter\MetricConverter` to use `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface` instead of a `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
- Change method `addAttributeData` of `Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface` to use a `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`.
- Change method `copyAttributeData` of `Pim\Component\Catalog\Updater\Copier\AttributeCopierInterface` to use 2 `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of 2 `Pim\Component\Catalog\Model\ProductInterface`.
- Change method `removeAttributeData` of `Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface` to use a `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`.
- Change method `setAttributeData` of `Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface` to use a `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`.
- Rename class `pim_catalog.factory.product_value_collection.class` to `pim_catalog.factory.value_collection.class`
- Rename class `pim_catalog.factory.product_value.class` to `pim_catalog.factory.value.class`
- Rename class `pim_catalog.factory.product_value.scalar.class` to `pim_catalog.factory.value.scalar.class`
- Rename class `pim_catalog.factory.product_value.metric.class` to `pim_catalog.factory.value.metric.class`
- Rename class `pim_catalog.factory.product_value.price_collection.class` to `pim_catalog.factory.value.price_collection.class`
- Rename class `pim_catalog.factory.product_value.option.class` to `pim_catalog.factory.value.option.class`
- Rename class `pim_catalog.factory.product_value.options.class` to `pim_catalog.factory.value.options.class`
- Rename class `pim_catalog.factory.product_value.media.class` to `pim_catalog.factory.value.media.class`
- Rename class `pim_catalog.factory.product_value.date.class` to `pim_catalog.factory.value.date.class`
- Rename class `pim_serializer.normalizer.flat.product_value.class` to `pim_serializer.normalizer.flat.value.class`
- Rename class `pim_catalog.entity.product_value.scalar.class` to `pim_catalog.entity.value.scalar.class`
- Rename class `pim_catalog.entity.product_value.media.class` to `pim_catalog.entity.value.media.class`
- Rename class `pim_catalog.entity.product_value.metric.class` to `pim_catalog.entity.value.metric.class`
- Rename class `pim_catalog.entity.product_value.option.class` to `pim_catalog.entity.value.option.class`
- Rename class `pim_catalog.entity.product_value.options.class` to `pim_catalog.entity.value.options.class`
- Rename class `pim_catalog.entity.product_value.date.class` to `pim_catalog.entity.value.date.class`
- Rename class `pim_catalog.entity.product_value.price_collection.class` to `pim_catalog.entity.value.price_collection.class`
- Rename class `pim_enrich.converter.standard_to_enrich.product_value.class` to `pim_enrich.converter.standard_to_enrich.value.class`
- Rename class `pim_enrich.converter.enrich_to_standard.product_value.class` to `pim_enrich.converter.enrich_to_standard.value.class`
- Rename class `pim_reference_data.factory.product_value.reference_data.class` to `pim_reference_data.factory.value.reference_data.class`
- Rename class `pim_reference_data.factory.product_value.reference_data_collection.class` to `pim_reference_data.factory.value.reference_data_collection.class`
- Rename class `pim_reference_data.product_value.reference_data.class` to `pim_reference_data.value.reference_data.class`
- Rename class `pim_reference_data.product_value.reference_data_collection.class` to `pim_reference_data.value.reference_data_collection.class`
- Rename service `pim_catalog.factory.product_value` to `pim_catalog.factory.value`
- Rename service `pim_catalog.factory.product_value_collection` to `pim_catalog.factory.value_collection`
- Rename service `pim_catalog.factory.product_value.text` to `pim_catalog.factory.value.text`
- Rename service `pim_catalog.factory.product_value.textarea` to `pim_catalog.factory.value.textarea`
- Rename service `pim_catalog.factory.product_value.number` to `pim_catalog.factory.value.number`
- Rename service `pim_catalog.factory.product_value.boolean` to `pim_catalog.factory.value.boolean`
- Rename service `pim_catalog.factory.product_value.identifier` to `pim_catalog.factory.value.identifier`
- Rename service `pim_catalog.factory.product_value.metric` to `pim_catalog.factory.value.metric`
- Rename service `pim_catalog.factory.product_value.price_collection` to `pim_catalog.factory.value.price_collection`
- Rename service `pim_catalog.factory.product_value.option` to `pim_catalog.factory.value.option`
- Rename service `pim_catalog.factory.product_value.options` to `pim_catalog.factory.value.options`
- Rename service `pim_catalog.factory.product_value.file` to `pim_catalog.factory.value.file`
- Rename service `pim_catalog.factory.product_value.image` to `pim_catalog.factory.value.image`
- Rename service `pim_catalog.factory.product_value.date` to `pim_catalog.factory.value.date`
- Rename service `pim_catalog.model.product_value.interface` to `pim_catalog.model.value.interface`
- Rename service `pim_versioning.serializer.normalizer.flat.product_value` to `pim_versioning.serializer.normalizer.flat.value`
- Remove interface `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeInterface`, attribute type classes must now implement directly `Pim\Component\Catalog\AttributeTypeInterface`
- Remove class `Pim\Bundle\EnrichBundle\Controller\AttributeController`
- Remove service `pim_enrich.controller.attribute` and parameter `pim_enrich.controller.attribute.class`
- Remove several UI related classes for attributes: `Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber`, `Pim\Bundle\EnrichBundle\Form\Type\AttributeProperty\AvailableLocalesType`, `Pim\Bundle\EnrichBundle\Form\Type\AttributeProperty\OptionsType`, `Pim\Bundle\EnrichBundle\Form\Type\AttributeType`
- Remove services `pim_enrich.form.subscriber.attribute`, `pim_enrich.form.type.attribute`, `pim_enrich.form.type.available_locales`, `pim_enrich.form.type.options`, `pim_enrich.form.attribute`, `pim_enrich.form.handler.attribute`
- Add subscriber to lock/unlock batch job commands thanks to @bOnepain
- Rename class `pim_connector.array_converter.flat_to_standard.product_delocalized.class` to `pim_connector.array_converter.flat_to_standard.entity_with_values_delocalized.class`

## Requirements

- GITHUB-5937: Remove the need to have mcrypt installed

## Bug Fixes

- GITHUB-6101: Fix Summernote (WYSIWYG) style
- GITHUB-6337: Write invalid items process fails when it encounters a Date field in xlsx files thanks to @pablollorens!
- GITHUB-6657: Fix mapping for Product and ProductUniqueData
