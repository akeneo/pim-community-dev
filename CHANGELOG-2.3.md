# 2.3.0-ALPHA1 (2018-04-27)

- PIM-7219: Prevent users from creating asset collection attributes that are locale specific.
- PIM-7285: Change the behavior for the mass publish on product models so children variant products are published.
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import.

## BC Breaks

- Change signature of `Pim\Component\Component\Catalog\ProductBuilder` constructor to add the `Pim\Component\Catalog\Association\MissingAssociationAdder`
- `PimEnterprise\Component\Workflow\Model\PublishedProductInterface` now implements `Pim\Component\Catalog\Model\AssociationAwareInterface`
- Service definition change: `pim_catalog.updater.product_without_permission` and `pim_catalog.updater.product`. Added `pim_catalog.association.filter.parent_associations` dependency.
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager` into `PimEnterprise\Bundle\WorkflowBundle\Manager\EntityWithValuesDraftManager`
- AOB-62: Rename `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectProductForProductDraftSubscriber` into `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\InjectEntityWithValuesForProductDraftSubscriber`

## New jobs
Be sure to run the following command `bin/console pim:installer:grant-backend-processes-accesses --env=prod` to add missing job profile accesses.
