# 4.0.x

# Technical Improvements

## Classes

- Add `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer`. This should be used instead of `ProductIndexer`.
- Add `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\ProductProposalAndProductModelProposalQueryBuilder`
- Add `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublishedProductQueryBuilder`

## BC breaks

### Elasticsearch

`published_product_and_published_product_model` is removed as it was useless.

### Doctrine mapping

- The entity `Akeneo\Pim\Enrichment\Component\Product\Model\Completeness` is is no more an association of the `PublishedProduct` entity.
  The methods `getCompletenesses` and `setCompletenesses` of a `PublishedProduct` are removed.
  If you want to access the Completeness of published products, please use the dedicated class `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses`.

### Codebase

- Remove service `pimee_workflow.manager.completeness`
- Remove service `pimee_workflow.completeness.calculator`
- Remove service `pimee_workflow.completeness.generator`
- All the table names used by the TeamWork Assistant are now hardcoded. 
- Remove `published_product_and_published_product_model` ES index. To search on published products, use `PublishedProductQueryBuilder`
- Remove `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\QueryProductProposalCommand`
- Update `Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber\RefreshIndexesBeforeRuleSelectionSubscriber` to remove `$productClient` and `$productModelClient`.
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\MediaFilter` to add `Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper` as the second argument.
- Update `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\IndexProductsSubscriber` to use `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer`.
- Remove `Akeneo\Pim\Permission\Bundle\Datagrid\MassAction\ProductFieldsBuilder`
- Rename `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\SkipVersionSubscriber` in `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\SkipVersionListener`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\PublishedProductNormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Remove the following class and the corresponding command:
     - `Akeneo\Asset\Bundle\Command\GenerateVariationFilesFromReferenceCommand`
     - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\ApproveProposalCommand  `
     - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\CreateDraftCommand`
     - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\PublishProductCommand `
     - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\QueryPublishedProductCommand`
     - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\SendDraftForApprovalCommand`
     - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\UnpublishProductCommand `
     - `Akeneo\Platform\Bundle\InstallerBundle\Command\GiveAllRightsToAllUsersCommand`
     - `Akeneo\Platform\Bundle\InstallerBundle\Command\GiveBackendProcessesRightsToAllUsersCommand`
- Remove class `Akeneo\Pim\Enrichment\Asset\Bundle\Doctrine\ORM\CompletenessRemover`
- Remove interface `Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface` 
- Remove methods `getCompletenesses` and `setCompletenesses` from `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct`
- Remove class `Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product\CompletenessPublisher`

### CLI commands

The following CLI commands have been deleted:
- pim:installer:grant-backend-processes-accesses
- pim:installer:grant-user-accesses
- pim:product:unpublish
- pim:draft:send-for-approval
- pim:published-product:query
- pim:product:publish
- pim:draft:create
- pim:proposal:approve
- pim:asset:generate-variation-files-from-reference
 
### Services

- Remove `pimee_workflow.query.select_category_codes_by_product_grid_filters`
- Remove `pimee_workflow.query.is_user_owner_on_all_categories`
- Remove `akeneo_elasticsearch.client.published_product_and_product_model`.
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`.
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_not_including_sub_categorie` to use `akeneo_elasticsearch.client.product_and_product_model`.
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`.
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`.
- Update `pim_catalog.factory.product_cursor_without_permission` to use `akeneo_elasticsearch.client.product_and_product_model`.
- Remove decoration from `pimee_catalog.query.product_and_product_model_query_builder_factory_with_permissions` and add parameters:
    - `pim_catalog.repository.attribute`
    - `pim_catalog.query.filter.product_and_product_model_registry`
    - `pim_catalog.query.sorter.registry`
    - `pim_catalog.query.product_query_builder_resolver`
- Remove `pimee_workflow.factory.product_proposal_cursor`
- Remove `pimee_workflow.factory.product_proposal_from_size_cursor`
- Update `pim_datagrid.extension.mass_action.handler.mass_refuse` to use `pimee_workflow.factory.product_and_product_model_proposal_cursor` as the second argument
- Update `pim_catalog.elasticsearch.published_product_indexer` to replace `pim_catalog.elasticsearch.indexer.product.class` with `pimee_workflow.elasticsearch.indexer.published_product.class`
- Update `pimee_workflow.event_subscriber.published_product.check_removal` to replace `pimee_workflow.doctrine.query.published_product_query_builder_factory` with `pimee_workflow.doctrine.query.published_product_query_builder_factory.without_permission`
- Update `pimee_workflow.doctrine.query.published_product_query_builder_factory` to use `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublishedProductQueryBuilder` and `pimee_workflow.query.filter.published_product_registry`
- Update `pimee_workflow.doctrine.query.published_product_query_builder_from_size_factory` to use `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublishedProductQueryBuilder` and `pimee_workflow.query.filter.published_product_registry`
- Update `pimee_workflow.doctrine.query.published_product_query_builder_search_after_size_factory` to use `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublishedProductQueryBuilder` and `pimee_workflow.query.filter.published_product_registry`
- Remove `pimee_workflow.query.product_proposal_query_builder_factory`
- Update `pimee_workflow.doctrine.query.proposal_product_and_product_model_query_builder_from_size_factory` to use `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\ProductProposalAndProductModelProposalQueryBuilder`
