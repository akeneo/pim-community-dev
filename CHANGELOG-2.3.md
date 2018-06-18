# 2.3.x

## Better manage products with variants

- AOB-58: Change signature of `PimEnterprise\Component\Workflow\Connector\Processor\Denormalization\ProductDraftProcessor` constructor to add the `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- AOB-139: Make Teamwork assistant project filtering work with product models.

## Improve Julia's experience

- PIM-7405: As Julia, I would like to order the assets linked to the products in the asset collection in the product form.
- PIM-7397: Add asset collection preview on the product edit form
- PIM-7407: As Julia, I would like to upload assets linked to products directly from the Product Form in the asset collection.

## BC Breaks

- AOB-139: Change constructor of `PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`.
- AOB-139: Rename method `PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface::findProductIds` to `findProductIdentifiers`.
- PIM-7407: Remove class parameters `pimee_product_asset.upload_context.class`, `pimee_product_asset.upload_checker.class`, `pimee_product_asset.upload_importer.class`, `pimee_product_asset.mass_upload_processor.class` and `pimee_product_asset.tasklet.class`
- PIM-7407: Move class `PimEnterprise\Component\ProductAsset\Upload\MassUploadProcessor` to `PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadProcessor`
- PIM-7407: Change the constructor of `PimEnterprise\Component\ProductAsset\Upload\Processor\\MassUploadProcessor` to  remove
    `PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException\UploadCheckerInterface`,
    `PimEnterprise\Component\ProductAsset\Factory\AssetFactory`,
    `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface`,
    `PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface`,
    `Akeneo\Component\FileStorage\File\FileStorerInterface`,
    and `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`,
    and add `PimEnterprise\Component\ProductAsset\Upload\MassUpload\BuildAsset` and `PimEnterprise\Component\ProductAsset\Upload\MassUpload\RetrieveAssetGenerationErrors` as new arguments.
- PIM-7407: Rename service `pimee_product_asset.mass_upload.tasklet` into `pimee_product_asset.tasklet.mass_upload`

## Improve Julia's experience

- PIM-7446: As Julia, if I mass upload an asset which has the same name than another asset in the PIM, I would like it to be well created.

# 2.3.0-ALPHA2 (2018-06-07)

## BC Breaks

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

- PIM-7285: Change the behavior for the mass publish if product models selected so children variant products are published.
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import.

## Bug fixes
- PIM-7219: Prevent users from creating asset collection attributes that are locale specific.

## BC Breaks

- Change signature of `Pim\Component\Component\Catalog\ProductBuilder` constructor to add the `Pim\Component\Catalog\Association\MissingAssociationAdder`
- `PimEnterprise\Component\Workflow\Model\PublishedProductInterface` now implements `Pim\Component\Catalog\Model\AssociationAwareInterface`
- Service definition change: `pim_catalog.updater.product_without_permission` and `pim_catalog.updater.product`. Added `pim_catalog.association.filter.parent_associations` dependency.
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager` into `PimEnterprise\Bundle\WorkflowBundle\Manager\EntityWithValuesDraftManager`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectProductForProductDraftSubscriber` into `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectEntityWithValuesForProductDraftSubscriber`

## New jobs

Be sure to run the following command `bin/console pim:installer:grant-backend-processes-accesses --env=prod` to add missing job profile accesses.
