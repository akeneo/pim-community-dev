# 2.3.x

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
