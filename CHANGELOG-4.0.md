# 4.0.x

## Improvements

- DAPI-26: adds the enrichment progress in the widget's project drop down
- DAPI-46: As a contributor, I would like to see my teammate progress on a project dashboard
- PIM-8665: The SAML diagnostic logs are now stored in a dedicated table instead of a local file. This removes the burden of sharing the log directory in a multi-front setup.

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
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\ProductNormalizer` to add 
    `Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculator` and `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface`
- As SSO Log are now in a dedicated table, the following classes are unused and have been removed:
    - Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchive
    - Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\FlySystemLogHandler
- Change `Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface` interface to add `executeWithIdentifiers(array $productIdentifiers): array;` new method
- Remove class `Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Indexing\Normalizer\ProductSubscriptionNormalizer`
- Remove class `Akeneo\Pim\Permission\Bundle\Normalizer\InternalApi\IncompleteValuesNormalizer`
- Remove interface `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductCompletenessInterface` and its implementation `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductCompleteness`
- Update `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductSaver` to remove:
    - `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface` and
    - `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface`
- Update `Akeneo\Pim\Permission\Bundle\MassEdit\Writer\ProductAndProductModelWriter` to remove:
    - `Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface`
    - `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
    - `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
    - `string $jobName`
- Change interface `Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface` to remove the second parameter `$author` of the method `process` 
- Change constructor of `Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\ProposalUpsert` to add `Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Factory\FranklinUserDraftSourceFactory $draftSourceFactory`
- Change interface `Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface` to replace the parameter `string $username` of the method `build` by `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource $draftSource`
- Change interface `Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\EntityWithValuesDraftFactory` to replace the parameter `string $username` of the method `createEntityWithValueDraft` by `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource $draftSource`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver\DelegatingProductSaver` to add `Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver\DelegatingProductModelSaver` to add `Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query\DraftAuthors` to replace `Doctrine\ORM\EntityManagerInterface` by `Doctrine\DBAL\Connection`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager` to add `Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor\Denormalization\ProductDraftProcessor` to add `Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor\Denormalization\ProductModelDraftProcessor` to add `Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Widget\ProposalWidget` to remove `Akeneo\UserManagement\Bundle\Manager\UserManager $userManager`
- Remove class `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\IndexProductsSubscriber',
    replaced by `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnSave\ComputePublishedProductsSubscriber`
    and `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnDelete\ComputePublishedProductsSubscriber`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftChangesExtension` to remove `Akeneo\Pim\Structure\Component\Factory\AttributeFactory`
- Rename `supportsChange` method to `supports` in
  - `Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter\ReferenceEntityCollectionValuePresenter`
  - `Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter\ReferenceEntityValuePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\BooleanPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DatePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DefaultPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\MetricPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\NumberPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionsPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PricesPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\ReferenceDataCollectionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\ReferenceDataPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TextPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TextareaPresenter`
- Change constructor to remove `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` from
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AbstractProductValuePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AssetsCollectionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DatePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\FilePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\MetricPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\NumberPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionsPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PricesPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\AbstractReferenceDataPresenter`
- Update `supports` signature to `(string $attributeType, string $referenceDataName)` on
  - `Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter\ReferenceEntityCollectionValuePresenter`
  - `Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter\ReferenceEntityValuePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AssetsCollectionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\BooleanPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DatePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DefaultPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\FilePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ImagePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\MetricPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\NumberPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionsPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PricesPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\AbstractReferenceDataPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\ReferenceDataCollectionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\ReferenceDataPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TextPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TextareaPresenter`
- Update `present` first argument to `mixed $formerData` on
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AbstractProductValuePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AssetsCollectionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\FilePresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\OptionsPresenter`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface`
  - `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PricesPresenter`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\EditAttributesProcessor` to
    - add `Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface` (as `productEmptyValuesFilter`)
    - add `Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface` (as `productModelEmptyValuesFilter`)
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi` to
    - remove `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface`
    - add `Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\FillMissingPublishedProductValues`
- Change Constructor of `Akeneo\Pim\Permission\Component\Filter\GrantedProductAttributeFilter` to
    - remove `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository`, `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $localeRepository`
        and `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
    - add `Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface`, `use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser`
        and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` 
- Change Constructor of `Akeneo\Pim\Permission\Component\Filter\NotGrantedValuesFilter` to
    - remove `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository`, `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $localeRepository`
        and `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
    - add `Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface`, `use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser`
        and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change Constructor of `Akeneo\Pim\Permission\Component\Merger\NotGrantedValuesMerger` to
    - remove `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository`, `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $localeRepository`
        and `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
    - add `Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface`, `use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser`
        and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`


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
- Update `pimee_teamwork_assistant.controller.project_completeness_controller` to remove `@security.token_storage` as dependency
- Update `pimee_catalog_rule.applier.product.saver` to remove `@pim_catalog.saver.product_model_descendants` as dependency
