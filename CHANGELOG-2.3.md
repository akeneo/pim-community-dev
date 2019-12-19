# 2.3.x

# 2.3.74 (2019-12-12)

# 2.3.73 (2019-12-06)

# 2.3.72 (2019-12-02)

# 2.3.71 (2019-11-25)

## Bug fixes

- PIM-8993: Fix mass publish product by removing validation

# 2.3.70 (2019-11-20)

# 2.3.69 (2019-10-24)

# 2.3.68 (2019-10-18)

# 2.3.67 (2019-10-14)

## Bug fixes

- PIM-7691: Users were able to edit their own roles
- PIM-8878: Force to refresh the Elasticsearch index for the proposals after partial rejecting or approving

# 2.3.66 (2019-10-09)

## Bug fixes

- PIM-7644: Fix 500 error on thumbnail of TIFF images

# 2.3.65 (2019-10-04)

## Bug fixes

- PIM-7274: Do not remove the values used by a proposal diff when the proposal is refused
- PIM-6276: Fix asset filter on product export builder

# 2.3.64 (2019-10-01)

# 2.3.63 (2019-09-23)

# 2.3.62 (2019-09-13)

# 2.3.61 (2019-09-10)

## Bug fixes

- PIM-8747: Do not fallback to reference in asset collection attribute when no variation exists for the channel/locale

# 2.3.60 (2019-09-05)

## Bug fixes

- PIM-7643: Fix closing asset collection preview when last item is removed

# 2.3.59 (2019-08-12)

## Bug fixes

- PIM-6942: Add margin to the "Send for approval" button

# 2.3.58 (2019-08-08)

## Bug fixes

- PIM-8302: Fix missing action to remove a product model draft
- PIM-7959: Fix association screen when associated products contain asset_collection

# 2.3.57 (2019-07-31)

## Bug fixes

- PIM-7850: Ignore empty parent for simple products in the proposal product import job

# 2.3.56 (2019-07-24)

## Bug fixes

- PIM-7667: Display draft icon for modified attributes in product model edit form

# 2.3.55 (2019-07-23)

## Bug fixes

- PIM-8565: Add ProjectCompleteness filter in sequential edit

# 2.3.54 (2019-07-19)

## Bug fixes

- PIM-8546: Fix asset fetch from PEF when uri too long

# 2.3.53 (2019-07-15)

# 2.3.52 (2019-07-02)

## Bug fixes

- PIM-8474: Fix user deletion when he belongs to only the group All and he is a project contributor
- PIM-8448: Send an explicit error message when removing a user linked to at least 1 TWA project

# 2.3.51 (2019-06-26)

# 2.3.50 (2019-06-24)

# 2.3.49 (2019-06-19)

## Bug fixes

- PIM-8444: Asset mass upload has been fixed when editing many products.

# 2.3.48 (2019-06-12)

# 2.3.47 (2019-06-03)

# 2.3.46 (2019-05-27)

# 2.3.45 (2019-05-23)

## Bug fixes

- PIM-7463: Fix display of the number of assets in the category tree

# 2.3.44 (2019-05-20)

# 2.3.43 (2019-05-14)

# 2.3.42 (2019-05-06)

# 2.3.41 (2019-05-02)

# 2.3.40 (2019-04-30)

## Bug fixes

- PIM-8298: Fix issue with rules' versioning and history display

# 2.3.39 (2019-04-23)

# 2.3.38 (2019-04-23)

# 2.3.37 (2019-04-15)

## Bug fixes

- PIM-8234: Fix performance issue on the command that generates missing asset variation files

# 2.3.36 (2019-04-02)

## Developer Experience Improvement

- PIM-8261: create a dedicated error message when variation not generated due to missing asset transformation on channel

## Bug fixes

- PIM-8262: Fix unused completeness removal on product save
- PIM-8243: Fix error when a product draft with a modified reference data is sent for approval
- PIM-7636: Fix the indexation and the calculation of the completeness during import product models with rules

# 2.3.35 (2019-03-26)

## Bug fixes

- PIM-8232: Escape quotes in flash messages twig
- PIM-8231: Product model drafts/proposals through API are well applied.
- PIM-8041: Use project locale when switching between projects

# 2.3.34 (2019-03-18)

## Bug fixes

- PIM-8187: When delete a product model / product the proposals linked in ES where not deleted
- PIM-8225: Show reference image on assets grid when there is no variation file info for the channel
- PIM-8041: Use project locale when switching between projects

# 2.3.33 (2019-03-13)

# 2.3.32 (2019-03-07)

## Bug fixes

- PIM-8188: Check mysql port in PimRequirements

## Improvements

- PIM-8150: compute the product completeness only on asset variation changes

## BC breaks

- PIM-8150: Change constructor of `src/PimEnterprise/Bundle/ApiBundle/Controller/AssetVariationController.php` to add the event dispatcher

# 2.3.31 (2019-02-28)

## Bug fixes

- PIM-8173: Remove limit of 20 assets displayed when uploading assets

# 2.3.30 (2019-02-21)

## Bug fixes

- PIM-8059: Optimize `compute_completeness_of_products_linked_to_assets` job.
The job does not remove completeness of product if the asset is not required by the family.

# 2.3.29 (2019-02-11)

## Bug fixes

- PIM-8021: Fix translations.
- PIM-8034: Fix a bug that prevents from deleting a channel when there is too many assets.

# 2.3.28 (2019-02-01)

## Bug fixes

- PIM-7970: Fix exception thrown when generating variations of assets with special chars in metadata.
- PIM-8035: Fix a memory leak on during the generation of missing asset variations.

# 2.3.27 (2019-01-29)

# 2.3.26 (2019-01-28)

## Bug fixes

- PIM-8006: Improve the generation of missing asset variation performances.
- PIM-7962: Fix the deletion of a user that is a contributor to a TWA project.
- Force the use of ip-regex at 2.1.0 version. Upper version needs nodejs >= 8 but we have to support nodejs >= 6.

# 2.3.25 (2019-01-17)

# 2.3.24 (2019-01-10)

## Bug fixes

- PIM-7964: Fix database migration

# 2.3.23 (2019-01-03)

## Bug fixes

- PIM-7899: Remove Date of Birth field
- PIM-7938: Cascade delete missing attributes for published product completeness


## Migrations

Please run the doctrine migrations command in order to update the DB schema: `bin/console doctrine:migrations:migrate --env=prod`

# 2.3.22 (2018-12-21)

## Bug fixes

- PIM-7924: Raise more information for error messages on command lines for asset variation generations
- PIM-7922: Fix commandline for asset variation generation by using by default the asset reference.
- PIM-7869: Fix asset invalid code when mass uploading an asset with a "." in the filename
- PIM-7910: Search parent filter is now case insensitive
- PIM-7928: Asset variations are well generate through the command `pim:asset:generate-variation-files-from-reference`.
- PIM-7929: Asset variations are well generate through the command `pim:asset:generate-missing-variation-files`.
- PIM-7931: When a new Channel or Locale is added, assets are well updated.

 ## Elasticsearch

 - Please re-index the products and product models by launching the commands `console akeneo:elasticsearch:reset-indexes -e prod` and `pim:product:index --all -e prod`.

# 2.3.21 (2018-12-07)

# 2.3.20 (2018-12-06)

# 2.3.19 (2018-12-03)

## Bug fixes

- PIM-7864: Move completeness reset to a job when an asset or asset reference is modified to avoid scalability issues

# 2.3.18 (2018-11-28)

# 2.3.17 (2018-11-15)

## Bug fixes

- PIM-7830: Fix server-side validation for Product Asset creation
- PIM-7838: Fix ACL on Mass edit for Asset category classify action

# 2.3.16 (2018-11-13)

## Bug fixes

- PIM-7852: Increase product import performances and fixes model association import when comparison is disabled
- PIM-7812: Fix command that generate missing asset variation files

# 2.3.15 (2018-11-06)

# 2.3.14 (2018-11-05)

## Bug fixes

- PIM-7764: Allows on PAM scale transformations from 0% (included) to 100% (included).

# 2.3.13 (2018-10-25)

## Bug fixes

- PIM-7756: Don't generate asset variation if no transformation supports the reference file type for a channel

# 2.3.12 (2018-10-17)

## Bug fixes

- PIM-7723: fix consistency between TWA widget numbers and the datagrid view
- PIM-7731: check for attribute as label not null in normalizers
- PIM-7734: fix published product datagrid status filter
- PIM-7733: Fix memory leak during mass publishing
- PIM-7742: fix expired assets notification
- PIM-7736: Fix memory leak during published products indexing

# 2.3.11 (2018-10-08)

## Bug fixes

- PIM-7672: Fix missing permission check on assets during a mass edit.
- PIM-7709: Execute all transformations of a channel even if one of them does not support the file to transform

# 2.3.10 (2018-10-01)

## Bug fixes

- PIM-7662: Fix migration script to index only the proposed product drafts

## Improvements

- PIM-7662: Add a command to index the product proposals into Elasticsearch

# 2.3.9 (2018-09-25)

## Bug fixes

- PIM-7654: Fix the mass classify when there are more assets than the batch size
- PIM-7651: Fix history generation of category accesses
- PIM-7649: Change draft author when the username corresponding to the author is modified

# 2.3.8 (2018-09-14)

# 2.3.7 (2018-09-11)

## Bug fixes

- PIM-7638: The edit profile button must not be shown if the user have no right to edit his own information
- PIM-7617: Disable Manage Assets button if user has no permission to show the asset categories
- PIM-7564: When a user have rights to edit but is not owner, the "save" label on the save button must be "save draft"

# 2.3.6 (2018-09-06)

## Bug fixes

- PIM-7611: Fix issue with download of assets
- PIM-7599: Fix an issue preventing the asset datagrid to load when having a huge number of asset categories

# 2.3.5 (2018-08-22)

## Bug fixes

- PIM-7509: Compute the completeness of variant products after a rule execution on models
- PIM-7575: Fix missing elements on product and product model edit pages in view mode
- PIM-7567: Fix permissions autofill after saving forms
- PIM-7563: Fix permissions on API filters
- PIM-7582: Fix memory leak on assets mass upload
- PIM-7579: Fix assets display in product edit form when they have full numeric codes, they don't disappear anymore after preview.
- PIM-7568: Fix the history of the attribute groups on permission update
- PIM-7587: Fix issue with preview of non-images assets

# 2.3.4 (2018-08-08)

## Bug fixes

- PIM-7558: Fix asset collection proposal behavior. The assets uploaded by an asset collection attribute were not sent for approval but directly persisted.
- PIM-7561: Fix the timeout on mass upload assets. We now delegate the import task to asynchronous job.
- PIM-7545: Prevent the DelegatingProductModelSaver to run the 'compute_product_model_descendant' on bulk save

# 2.3.3 (2018-08-01)

## Bug fixes

- PIM-7534: Fix proposal grid for product model changes have errors visible in debug mode.

# 2.3.2 (2018-07-24)

## Bug fixes

- PIM-7443: Fix product mass action permissions also apply on other grid mass actions
- PIM-7476: fix family select2 to have the right limit
- PIM-7516: fix metric default value on product edit form

# 2.3.1 (2018-07-04)

## Bug fixes

- PIM-7465: Fix a bug that prevents asset mass upload to work from product model edit form.

## BC breaks

- PIM-7465: Change constructor of ` PimEnterprise\Component\ProductAsset\Upload\MassUpload\EntityToAddAssetsInto` to replace first, integer argument by a string argument.
- PIM-7465: Change constructor of `PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetToEntityWithValues` to replace `Doctrine\Common\Persistence\ObjectRepository` by `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`.
- PIM-7465: Change signature of `PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetToEntityWithValues::add` to replace first, integer argument by a string argument.

# 2.3.0 (2018-06-25)

- PIM-7457: Revert PIM-7446, updating asset during mass upload is a needed functionality.

# 2.3.0-BETA1 (2018-06-21)

## Better manage products with variants

- AOB-58: As Mary, I would like to submit product model via import.
- AOB-57: As Mary, I would like to submit draft on product model via the API.
- AOB-139: Make Teamwork assistant project filtering work with product models.

## Monitor your catalog volume

- PIM-7209: As John, I want to be able to get info about my catalog volume.

## Improve the asset management

- PIM-7405: As Julia, I would like to order the assets linked to the products in the asset collection in the product form.
- PIM-7397: Add asset collection preview on the product edit form.
- PIM-7407: As Julia, I would like to upload assets linked to products directly from the Product Form in the asset collection.
- PIM-7446: As Julia, if I mass upload an asset which has the same name than another asset in the PIM, I would like it to be well created.

## Improve Julia's experience

- PIM-7347: Improve the calculation of the completeness for locale specific attributes.
- PIM-7345: Remove the "is empty" operator for sku.

## BC Breaks

- AOB-58: Change signature of `PimEnterprise\Component\Workflow\Connector\Processor\Denormalization\ProductDraftProcessor` constructor to add the `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- AOB-139: Change constructor of `PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`.
- AOB-139: Rename method `PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface::findProductIds` to `findProductIdentifiers`.
- PIM-7407: Remove unused class parameters `pimee_product_asset.upload_context.class`
- PIM-7407: Move class `PimEnterprise\Component\ProductAsset\Upload\MassUploadProcessor` to `PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadProcessor`
- PIM-7407: Change the constructor of `PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadProcessor` to  remove
    `PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException\UploadCheckerInterface`,
    `PimEnterprise\Component\ProductAsset\Factory\AssetFactory`,
    `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface`,
    `PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface`,
    `Akeneo\Component\FileStorage\File\FileStorerInterface`,
    and `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`,
    and add `PimEnterprise\Component\ProductAsset\Upload\MassUpload\AssetBuilder` and `PimEnterprise\Component\ProductAsset\Upload\MassUpload\RetrieveAssetGenerationErrors` as new arguments.

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
- AOB-62: As Mary, I would like to send for approval product models in the UI.

## Technical improvements

- Add a 'family_variant' filter in the Product Query Builder with operators 'IN', 'NOT IN', 'EMPTY' and 'NOT EMPTY'.
- PIM-7324: Rework structure version provider to better handle cache invalidation.

## BC Breaks

- Remove public constant `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxis::DUPLICATE_VALUE_IN_SIBLING`
- Change the method signature of `Pim\Component\Catalog\Validator\UniqueAxesCombinationSet::addCombination`, this method does not return anything anymore, but can throw `AlreadyExistingAxisValueCombinationException`
- Add method `generateMissingForProducts` to `Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface`
- Add a new public method `findProductModelsForFamilyVariant` to `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface`
- Change signature of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver` constructor to add `Akeneo\Component\StorageUtils\Indexer\IndexerInterface`
- AOB-62: `PimEnterprise\Component\Workflow\Model\ProductDraft` now implements `Pim\Component\Catalog\Model\EntityWithValuesInterface`
- AOB-62: Rename `PimEnterprise\Component\Workflow\Model\ProductDraftInterface` into `Pim\Component\Catalog\Model\EntityWithValuesInterface`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface` into `PimEnterprise\Bundle\WorkflowBundle\Builder\EntityWithValuesDraftBuilderInterface`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilder` into `PimEnterprise\Bundle\WorkflowBundle\Builder\EntityWithValuesDraftBuilder`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectProductForProductDraftSubscriber` into `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectEntityWithValuesForProductDraftSubscriber`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` into `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\EntityWithValuesDraftRepository`
- AOB-62: Rename service `pimee_workflow.datagrid.event_listener.inject_product_for_product_draft` into `pimee_workflow.datagrid.event_listener.inject_entity_with_values_for_product_draft`
- AOB-62: Rename service class parameter `pimee_workflow.datagrid.event_listener.inject_product_for_product_draft.class` into `pimee_workflow.datagrid.event_listener.inject_entity_with_values_for_product_draft.class`
- AOB-62: Rename service `pimee_workflow.applier.product_draft` into `pimee_workflow.applier.draftt`
- AOB-62: Rename service class parameter `pimee_workflow.applier.product_draft.class` into `pimee_workflow.applier.draft.class`
- AOB-62: Rename service class parameter `pimee_workflow.repository.product_draft.class` into `pimee_workflow.repository.entity_with_values_draft.class`
- AOB-62: Property `$product` has been renamed into `$entityWithValues` into `PimEnterprise\Component\Workflow\Model\ProductDraft`
- AOB-62: Column `product` has been renamed into `entityWithValues` on the `pimee_workflow_product_draft` table.
- AOB-62: Renamed `PimEnterprise\Component\Workflow\Event\ProductDraftEvents` into `PimEnterprise\Component\Workflow\Event\EntityWithValuesDraftEvents`.

# 2.3.0-ALPHA1 (2018-04-27)

## Better manage products with variants

- PIM-6795: As Julia, I would like to display only the current level attributes
- PIM-7284: Be able to bulk change the status of children products if product models are selected
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import
- PIM-7000: As Julia, I would like to manage associations for products models from the UI
- PIM-6991: As Julia, I would like to export product models associations
- PIM-7286: Be able to bulk add children products of product models to group
- PIM-7285: Change the behavior for the mass publish if product models selected so children variant products are published.
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import.

## Improve Julia's experience

- PIM-7294: As Julia, I don't want to remove a family with products

## Technical improvements

- Add typescript support

## Bug fixes

- PIM-7219: Prevent users from creating asset collection attributes that are locale specific.

## BC Breaks

- Remove methods `getAssociations`, `setAssociations`, `addAssociation`, `removeAssociation`, `getAssociationForType` and `getAssociationForTypeCode` from `Pim\Component\Catalog\Model\ProductInterface`. These methods are now in the `Pim\Component\Catalog\Model\EntityWithAssociationsInterface`.
- Change signature of `Pim\Component\Catalog\Builder\ProductBuilderInterface::addMissingAssociations` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface::findMissingAssociationTypes` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Model\AssociationInterface::setOwner` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel` constructor to add the `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver`
- Change signature of `Pim\Component\Component\Catalog\ProductBuilder` constructor to add the `Pim\Component\Catalog\Association\MissingAssociationAdder`
- `PimEnterprise\Component\Workflow\Model\PublishedProductInterface` now implements `Pim\Component\Catalog\Model\AssociationAwareInterface`
- Service definition change: `pim_catalog.updater.product_without_permission` and `pim_catalog.updater.product`. Added `pim_catalog.association.filter.parent_associations` dependency.
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager` into `PimEnterprise\Bundle\WorkflowBundle\Manager\EntityWithValuesDraftManager`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectProductForProductDraftSubscriber` into `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectEntityWithValuesForProductDraftSubscriber`

## New jobs

**IMPORTANT**: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.

- Add the job instance `add_to_group`: `bin/console akeneo:batch:create-job "Akeneo Mass Edit Connector" "add_to_group" "mass_delete" "add_to_group" '{}' "Mass add product to group" --env=prod`

Be sure to run the following command `bin/console pim:installer:grant-backend-processes-accesses --env=prod` to add missing job profile accesses.

## Migrations

Please run the doctrine migrations command in order to see the new catalog volume monitoring screen: `bin/console doctrine:migrations:migrate --env=prod`
