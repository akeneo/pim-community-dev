# 4.0.x

## Technical Improvements

- AOB-947: Add extension point on asset manager tab register

# 4.0.33 (2020-06-11)

## Technical Improvements

- PIM-9299: Temporary increase memory peak limit test to 55mb

## Bug fixes

- PIM-9297: Fixed redirection from assets to products with filter
- PIM-9296: Fix validation of product link rules format

# 4.0.32 (2020-06-08)

# 4.0.31 (2020-06-01)

## Bug fixes

- PIM-9276: Fix API assets pagination
- PIM-9281: Add title to asset navigation left menu

# 4.0.30 (2020-05-28)

## Technical Improvements

- PIM-9259: Add an optimize jpeg operation on asset's transformations

## Bug fixes

- PIM-9258: Fix linked products view in reference entities when an asset is set as main image.
- PIM-9263: Fix a bad refresh of select value on reference entity edit page

# 4.0.29 (2020-05-26)

# 4.0.28 (2020-05-20)

## Bug fixes

- PIM-9252: The asset manager is now accessible when the "Create asset family" ACL is disabled.

# 4.0.27 (2020-05-18)

# 4.0.26 (2020-05-15)

## Bug fixes

- PIM-9252: Fix TWA project unwanted deletion when a category not used as a filter was deleted

# 4.0.25 (2020-05-14)

## Bug fixes

- PIM-9241 - Target transformation was not empty when source attribute value was empty
- PIM-9233: Add missing preview of asset collection attribute in product grid

# 4.0.24 (2020-05-07)

## Bug fixes

- PIM-9237: Add missing scrollbar in asset navigation panel

# 4.0.23 (2020-05-06)

# 4.0.22 (2020-05-05)

## Bug fixes

- Fix typescript compilation error inside the DataQualityInsights context
- PIM-9181: Backport PIM-9133 to 4.0 (Fix product/product model save when the user has no permission on some attribute groups)

# 4.0.21 (2020-04-29)

## Bug fixes

- PIM-9178: Fix Asset Family deletion error message
- PIM-9211: Add indicator when uploading a media file in Asset Manager

# 4.0.20 (2020-04-27)

# 4.0.19 (2020-04-24)

## Technical Improvements

- PIM-9195: Enable preview of SVG files in the assets mass upload

## Bug fixes

- PIM-9192: Fix error being printed in the response of partial update of products API
- DAPI-961: change the dates display on daily chart axis to display relative dates

# 4.0.18 (2020-04-23)

## Technical Improvements

- PIM-9195: Add extra ImageMagick library to handle SVG files
- Limit Symfony version on 4.4.7 because of validation issues with 4.4.8

## Bug fixes

- PIM-9190: Mitigates deadlock on DQI

# 4.0.17 (2020-04-17)

## Improvements

- DAPI-531: Franklin Insights - Add loading indicator for multiple actions on mapping screens.

## Bug fixes

- PIM-9189: Fix the display of asset collection and reference entity link attributes in export product profile

# 4.0.16 (2020-04-08)

# 4.0.15 (2020-04-07)

## Bug fixes

- PIM-9162: Prevent the asset manager from computing transformations for unsupported file mime types

## Technical Improvements

- PIM-9174: PHP_IDE_CONFIG is not dependant from the PIM edition
- DAPI-948: Reduce the number of days of retention of the criteria evaluations

# 4.0.14 (2020-04-01)

## Bug fixes

- PIM-9155: Fix the creation of product link rules in the asset manager

# 4.0.13 (2020-03-30)

## Bug fixes

- PIM-9161: Fix performance issue on product normalization

# 4.0.12 (2020-03-24)

## Bug fixes

- PIM-9159: Fix filter by family and category on the DQI dashboard

# 4.0.11 (2020-03-20)

# 4.0.10 (2020-03-18)

- PIM-9153: Remove useless GLOB_BRACE flag from standard Kernel

# 4.0.9 (2020-03-16)

## Improvements

- DAPI-910: Do not insert evaluation criteria on post_save_all event, rely on product updated_at date

## Bug fixes

- MET-70: Add maintenance command to transform scopable asset manager attributes into non-scopable
- PIM-9121: Check Asset exists before importing it in a Product
- DAPI-900: Accept "ignored words" with any kind of letter from any language

# 4.0.8 (2020-03-05)

## Bug fixes

- PIM-9128: Fix asset manager transformation job failing on non supported file types
- PIM-9117: Fix Asset Manager naming convention validation message
- PIM-9116: Fix Asset Manager product link rules validation message
- PIM-9118: Fix Asset Manager asset deletion message

# 4.0.7 (2020-03-04)

## Improvements

- DAPI-810: Evaluate synchronous criteria on unitary product save
- DAPI-874: Call spellcheck only when clicking in a field 

# 4.0.6 (2020-02-19)

## Bug fixes

- PIM-9092: Fix search by code when there is no label defined for reference entity filter on the product grid

# 4.0.5 (2020-02-14)

## Bug fixes

- PIM-9100: fix the configuration of the controllers as public service for the API.

# 4.0.4 (2020-02-12)

# 4.0.3 (2020-02-05)

# 4.0.2 (2020-02-04)

## Bug fixes

- PIM-9070: Fix access to SSO configuration
- PIM-9072: Add missing Reference Entities translations

# 4.0.1 (2020-01-22)

# 4.0.0 (2020-01-15)

## New features

- AST-63: New Asset Manager to provide only one way to use assets in the PIM, regardless of their sources
- DAPI-585: Data quality - Display Enrichment and Consistency grades in the PEF and Product Grid
- DAPI-632: Data quality - Calculate spellcheck grade and make correction suggestions in the PEF
- DAPI-633: Data quality - Add a dashboard to track data quality evolution on full catalog, families and categories

## Improvements

- DAPI-437: Franklin Insights - Display Franklin key figures on Franklin’s added value to Julia’s catalog
- DAPI-467: Franklin Insights - Ease Franklin/PIM mapping via suggested mapping
- DAPI-26: adds the enrichment progress in the widget's project drop down
- DAPI-46: As a contributor, I would like to see my teammate progress on a project dashboard
- PIM-8665: The SAML diagnostic logs are now stored in a dedicated table instead of a local file. This removes the burden of sharing the log directory in a multi-front setup.
- PIM-6459: Users don't receive product draft notification if they don't have permission to validate the changes on its locale.
- PIM-8950: Add Content Security Policy

# Technical Improvements

## Classes

- Add `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer`. This should be used instead of `ProductIndexer`.
- Add `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\ProductProposalAndProductModelProposalQueryBuilder`
- Add `Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublishedProductQueryBuilder`

## BC breaks

### PHP Server
- Install the GNU Aspell spell-checker package: `aspell`
  Install the dictionaries for Aspell: `aspell-en`, `aspell-es`, `aspell-de`, `aspell-fr`
  Define the binary path for Aspell in the ENV variable: `ASPELL_BINARY_PATH`. (The default path is `aspell`)

### Storage configuration
- Removes the "%tmp_storage_dir%" parameter. Please use "sys_get_temp_dir()" in your code instead.
- Removes all the directories parameter. Please use the associated Flysystem filesystem in your code instead.

### Elasticsearch

`published_product_and_published_product_model` index is removed as it was useless.

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
    - add `Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface`, `Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser`
        and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` 
- Change Constructor of `Akeneo\Pim\Permission\Component\Filter\NotGrantedValuesFilter` to
    - remove `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository`, `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $localeRepository`
        and `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
    - add `Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface`, `Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser`
        and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change Constructor of `Akeneo\Pim\Permission\Component\Merger\NotGrantedValuesMerger` to
    - remove `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository`, `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $localeRepository`,
        `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` and `Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory`
    - add `Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface`, `Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser`
        and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator` to remove `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface` and `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`,
    and add `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator`. Also, protected method `findFilledAttributes()` was removed.        
- Remove class `Akeneo\Pim\Enrichment\Asset\Component\AssetCollectionValueFactory`
- Remove class `Akeneo\Pim\Enrichment\AssetManager\Component\Factory\AssetCollectionValueFactory`
- Update interface `src/Akeneo/UserManagement/Component/Model/UserInterface` and class `src/Akeneo/UserManagement/Component/Model/User`: add `defineAsApiUser` and `isApiUser` methods.


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
- pim:asset:send-expiration-notification
 
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
- Remove `akeneo_referenceentity.factory.product_value.reference_entity_collection`
- Remove `akeneo_referenceentity.factory.product_value.reference_entity`
