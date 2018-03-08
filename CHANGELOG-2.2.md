# 2.2.0-ALPHA2 (2018-03-07)

## BC breaks

### Constructors

- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassApproveActionHandler` to add `Akeneo\Bundle\ElasticsearchBundle\Cursor\CursorFactoryInterface` as new argument.
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassRefuseActionHandler` to add `Akeneo\Bundle\ElasticsearchBundle\Cursor\CursorFactoryInterface` as new argument.
- AOB-2: Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Security\Elasticsearch\ProductQueryBuilderFactory` to add `$accessLevel` as new argument.
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` to remove `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal`
         to remove `PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface`,
                   `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Symfony\Component\HttpFoundation\RequestStack`,`PimEnterprise\Bundle\WorkflowBundle\Provider\ProductDraftGrantedAttributeProvider`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductDraftHydrator` to add `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Normalizer\ProductProposalNormalizer`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProposalGridListener` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassApproveActionHandler` to remove `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassRefuseActionHandler` to remove `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface`
- AOB-2: `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` now implements `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface`
- AOB-2: `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` now implements `Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface`
- AOB-2: `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` now implements `Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface`
- AOB-2: `PimEnterprise\Component\Workflow\Model\ProductDraftInterface` now implements `Pim\Component\Catalog\Model\EntityWithValuesInterface`
- AOB-2: `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` now implements `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`

# 2.2.0-ALPHA1 (2018-02-21)

## Improve Julia's experience

- PIM-7165: Execute rules after a product model import
- PIM-7125: As Peter, I would like to use rules to unclassify products from a tree
- PIM-7186: Don't apply a rule if its action field is an attribute not present in the family

## BC breaks

### Constructors

- PIM-7125: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\RemoverActionApplier` to add `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- PIM-7125: Change the constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Twig\RuleExtension` to add `Symfony\Component\Translation\TranslatorInterface`

# 2.2.0-ALPHA0 (2018-02-13)

## Improve Julia's experience

- PIM-6367: Apply rules on products models values
- PIM-7166: Display on product model edit form that an attribute can be updated by a rule

## BC breaks

### Constructors

- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver` to add `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\SetterActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\AdderActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\RemoverActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\CopierActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`

### Services and parameters

- PIM-6367: Remove argument `pim_catalog.resolver.attribute_values_with_permissions` from service `pimee_workflow.builder.published_product`
- PIM-6367: Rename service `pimee_enrich.query.product_and_product_model_query_builder_factory_with_permissions`
    into `pimee_catalog.query.product_and_product_model_query_builder_factory_with_permissions`
- PIM-6367: Rename service `pimee_enrich.query.product_and_product_model_query_builder_factory.with_permissions_and_product_and_product_model_cursor`
    into `pimee_catalog.query.product_and_product_model_query_builder_factory.with_permissions_and_product_and_product_model_cursor`
