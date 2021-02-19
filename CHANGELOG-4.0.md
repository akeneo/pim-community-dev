# 4.0.x

# 4.0.95 (2021-02-19)

## Bug fixes

- PIM-9680: Fix filtering exports with reference entity attributes
- PIM-9695: Backport of PIM-9503, ignore permissions when executing rules in a job step
- PIM-9691: Fix filtering exports with assets collection attributes

# 4.0.94 (2021-02-17)

# 4.0.93 (2021-02-12)

- PIM-9674: Fix Flash message missing when editing and saving an asset attribute at the family level

# 4.0.92 (2021-02-11)

## Improvements

- PIM-9666: improve asset transformation computing process

# 4.0.91 (2021-02-09)

## Bug fixes

- PIM-9663: Fix PDF product renderer disregarding permissions on attribute groups (backport of PIM-9649)

# 4.0.90 (2021-02-02)

## Bug fixes

- PIM-9652: Fix the list of proposals in the activity's dashboard.

# 4.0.89 (2021-01-29)

# 4.0.88 (2021-01-28)

## Bug fixes
RAC-473: Fix slow COUNT query on asset manager

# 4.0.87 (2021-01-26)

# 4.0.86 (2021-01-22)

## Bug fixes

- PIM-9641: Return a proper error when the asset or record code are not well formatted in product update API

# 4.0.85 (2021-01-19)

## Improvements

- PIM-9624: Improve DQI evaluations purge

## Bug fixes

- PIM-9638: Fix security issue in Symfony < 4.4.13 (see https://symfony.com/blog/cve-2020-15094-prevent-rce-when-calling-untrusted-remote-with-cachinghttpclient)

# 4.0.84 (2021-01-14)

# 4.0.83 (2021-01-06)

# 4.0.82 (2021-01-05)

- AOB-1258: Fix array_map call with null value

# 4.0.81 (2020-12-23)

# 4.0.80 (2020-12-21)

## Bug fixes

- PIM-9604: Reference entities are blocked after using the filter.

# 4.0.79 (2020-12-18)

# 4.0.78 (2020-12-08)

# 4.0.77 (2020-12-07)

## Improvements

- PIM-9558: Remove the word "Damn" from the PIM

# 4.0.76 (2020-12-02)

## Bug fixes

- PIM-9563: Fix default asset preview on asset family grid when cannot generate thumbnail
- PIM-9557: Fix invalid impacted products count on published product grid

# 4.0.75 (2020-11-27)

## Bug fixes

- PIM-9559: Clean product & product models draft changes when after running clean removed attributes command

# 4.0.74 (2020-11-25)

# 4.0.73 (2020-11-23)

## Bug fixes

- PIM-9527: default asset preview and thumbnail

# 4.0.72 (2020-11-16)

# 4.0.71 (2020-11-12)

## Bug fixes

- PIM-9556: Fix proposal diff rendering for wysiwyg-enabled textarea attributes

# 4.0.70 (2020-11-09)

# 4.0.69 (2020-11-05)

## Bug fixes:

- PIM-9547: Fix SqlFindPropertyAccessibleAsset performance
- PIM-9546: [Backport] PIM-9528: Fix asset code changed into lower case in create asset/upload asset UI
- PIM-9530: [Backport] PIM-9316: Fix url encoding of media links in asset edit form

# 4.0.68 (2020-10-30)

## Bug fixes:

- PIM-9525: Fix memory leak during project calculation
- PIM-9534: Fix product link rules for scopable/localizable asset collection attributes

# 4.0.67 (2020-10-28)

# 4.0.66 (2020-10-23)

## Bug fixes:

- PIM-9509: SSO Identity Provider should accept URNs

## Bug fixes:

- PIM-9510: Fix asset collection attributes that couldn't be added in the "Add attributes values" mass action

# 4.0.65 (2020-10-19)

## Improvements

- PIM-9506-4.0: Make "image" the default media type for media link asset attributes

# 4.0.64 (2020-10-09)

## Bug fixes:

- PIM-9492-4.0: [Backport] PIM-9109: Fix SSO not working behind reverse proxy.

# 4.0.63 (2020-10-08)

# 4.0.62 (2020-10-07)

# 4.0.61 (2020-10-02)

## Bug fixes

- PIM-9488: Fix not used variable during webpack run

# 4.0.60 (2020-09-30)

- PIM-9467: Check for asset family existence is now case sensitive

# 4.0.59 (2020-09-23)

# 4.0.58 (2020-09-22)

## Bug fixes

- PIM-9455: Make total_fields limit of elasticsearch configurable
- PIM-9451: Filter out Asset Manager attribute labels of disabled locales 
- PIM-9435: Fix duplicated listener
 
# 4.0.57 (2020-09-15)

# 4.0.56 (2020-09-09)

## Bug fixes

- PIM-9436: Filter out Reference Entity & Asset Family labels of disabled locales
- PIM-9437: Fix ListGrantedRootCategoriesWithCountIncludingSubCategories sort buffer

# 4.0.55 (2020-09-03)

# 4.0.54 (2020-08-28)

## Bug fixes

- PIM-9418: Fix errors on proposals containing metric, price and asset collection attributes

# 4.0.53 (2020-08-27)

## Improvements

- DAPI-1201: Remove unused ES index configuration file 
- PIM-9315: Improve error message when you have no rights on a published product

## Bug fixes

- DAPI-1231: Fix possible ES conflicts when updating product rates
- PIM-9424: Fix add attributes values mass action on reference entity multiple links attributes

# 4.0.52 (2020-08-24)

# 4.0.51 (2020-08-21)

# 4.0.50 (2020-08-20)

## Bug fixes

- PIM-9288: Product completeness was not up to date after deletion of an option for required attribute
- PIM-9397: Dispatch missing pre_ready event during bulk actions when "Send for approval" is checked

# 4.0.49 (2020-08-13)

## Bug fixes:

- PIM-9401: Fix Elasticsearch filters with EMPTY operator

# 4.0.48 (2020-08-12)

## Bug fixes

- PIM-9387: Fix product model proposal with empty values

## Technical Improvements

- PIM-9381: add new asset clear thumbnail cache command 

# 4.0.47 (2020-08-07)

## Bug fixes
- PIM-9386: Notification reminder message not compliant
- PIM-9385: Fix fatal error: Out of Sort memory on Record query 

# 4.0.46 (2020-07-31)

## Bug fixes

- PIM-9382: Fix fatal error on rule execution

# 4.0.45 (2020-07-31)

## Bug fixes

- PIM-9375: API Asset - add an error message
- PIM-9373: The Compare feature filters are not displayed on the product model parent level

# 4.0.44 (2020-07-28)

## Bug fixes

- PIM-9374: Fix search by code on asset collection attribute

# 4.0.43 (2020-07-27)

## Bug fixes

- PIM-9304: Fix slow query used to retrieve products to evaluate

# 4.0.42 (2020-07-22)

## Bug fixes

- PIM-9365: Fix PDF previews' background being black
- PIM-9366: Add a warning message when importing a product with asset that doesn't belong to the linked asset family

# 4.0.41 (2020-07-20)

# 4.0.40 (2020-07-13)

## Bug fixes

- PIM-9351: Fix the thumbnail preview of transparent PDFs in the asset manager
- PIM-9350: Fix the thumbnail preview of any PDFs in the asset manager
- PIM-9355: Fix product link rules for assets targeting both product & product models

# 4.0.39 (2020-07-08)

# 4.0.38 (2020-07-07)

## Bug fixes

- PIM-9271: Fix the image preview on asset manager linked product

# 4.0.37 (2020-07-06)

## Bug fixes

- PIM-9329: Fix slow queries collect data on reference entity and asset manager
- PIM-9335: Fix fetch attribute to 100 instead of 20 by default in linked products
- PIM-9209: Fix broken proposal screen when submitting changes on an Asset Collection

# 4.0.36 (2020-06-30)

# 4.0.35 (2020-06-22)

## Bug fixes

- PIM-9303: Fix project recalculate
- PIM-9302: Fix ref entity multiple option attribute view crashing

# 4.0.34 (2020-06-17)

## Bug fixes

- PIM-9298: Disallow removal of ReferenceEntity records used as product variant axis
- PIM-9313: Remove limit of only 20 asset collection attributes in the product's assets tab

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
