# 3.0.x

# 3.0.55 (2019-11-22)

# 3.0.54 (2019-11-18)

# 3.0.53 (2019-11-12)

# 3.0.52 (2019-11-08)

## Bug fixes

- PIM-8945: Fix unclear error message on product updater
- PIM-8944: Add permission check on product variant creation button

# 3.0.51 (2019-10-30)

# 3.0.50 (2019-10-28)

# 3.0.49 (2019-10-24)

# 3.0.48 (2019-10-23)

# 3.0.47 (2019-10-21)

# 3.0.46 (2019-10-16)

# 3.0.45 (2019-10-04)

# 3.0.44 (2019-10-02)

## Bug fixes

- PIM-8769: Fix 'SKU' filter disappearing from the filter options
- PIM-8818: Fix project completeness for locale specific attributes

# 3.0.43 (2019-09-24)

# 3.0.42 (2019-09-13)

## Bug fixes

- PIM-8741: Fix asset update via API for attribute having one value per channel or one value per locale

# 3.0.41 (2019-09-05)

## Bug fixes

- PIM-8719: Update Mink Selenium driver
- PIM-8715: Use the same page size than the underlying cursor when applying product rules

# 3.0.40 (2019-09-02)

## Bug fixes

- PIM-8588: Fix proposal diff for reference entity

# 3.0.39 (2019-08-28)

## Bug fixes

- PIM-8702: Fix sso configuration page reload after toggling
- PIM-8704: Redirect bad credentials to login form when SSO is activated
- PIM-8678: Fix the Project Completeness Calculations when families are updated

# 3.0.38 (2019-08-20)

## Bug fixes

- PIM-8667: Fix grid filters for numeric attribute codes

# 3.0.37 (2019-08-13)

## Bug fixes

- PIM-8356: Fix margin on Mass publish action

# 3.0.36 (2019-08-08)

# 3.0.35 (2019-08-05)

## Bug fixes

- PIM-8592: Fix incorrect count on sequential edit

# 3.0.34 (2019-07-24)

# 3.0.33 (2019-07-24)

# 3.0.32 (2019-07-19)

# 3.0.31 (2019-07-16)

# 3.0.30 (2019-07-05)

# 3.0.29 (2019-07-04)

# 3.0.28 (2019-07-02)

# 3.0.27 (2019-06-27)

# 3.0.26 (2019-06-21)

## Bug fixes

- AOB-556: Fix SSO SP certificate expiration date

# 3.0.25 (2019-06-18)

# 3.0.24 (2019-06-17)

# 3.0.23 (2019-06-11)

# 3.0.22 (2019-06-04)

## Bug fixes

- PIM-8389: Fix asset end of use display when no date is set
- PIM-8388: Normalize reference entity values in product model index

# 3.0.21 (2019-05-27)

# 3.0.20 (2019-05-24)

## Improvements

- PIM-8360: Ref Entities / Onboarder - Add a query in public API to fetch all records with their localized labels

## Bug fixes

- PIM-6869: Fix bad label on the asset grid category tree
- PIM-8362: Fix wording on the proposals action buttons
- PIM-8308: Fix translation file name for the ReferenceEntity validation messages
- PIM-8364: Fix proposals modals display

# 3.0.19 (2019-05-21)

## Bug fixes

- PIM-8314: Add missing translation
- PIM-8358: Fix inconsistent label for the product save button

# 3.0.18 (2019-05-15)

## Improvements

- PIM-8307: Create public API services/events for records synchronisation with Onboarder

## Bug fixes

- PIM-8339: Add missing translations
- DAPI-226: Do not create a subscription if there's already one for the same identifiers

# 3.0.17 (2019-05-10)

## Bug fixes

- PIM-8328: Fix flag display on reference entities locales with multiple underscore
- DAPI-234: Fix query that retrieves the users to notify for missing mapping

# 3.0.16 (2019-05-06)

## Bug fixes

- PIM-8316: Fix product-grid filter on reference entity attributes
- DAPI-235: Franklin Insights - Display a better error message when a PIM attribute is mapped multiple times
- DAPI-239: Franklin Insights - Display backoffice error messages when identifiers mapping fails
- DAPI-224: Franklin Insights - Fix mapping of Franklin attribute codes with dot character inside

## Improvements

- PIM-8215: Displaying a record won't display "dead links" to any deleted records in its values
- DAPI-212: Change command name to fetch products from Franklin Insights

# 3.0.15 (2019-04-30)

## Bug fixes

- DAPI-225: Fix attribute search in Franklin-Insights settings
- PIM-8300: Display description in record grid without HTML tags
- DAPI-213: Fix missing translation keys

## BC Breaks

- Changed constructor of `Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydrator`. Added `Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlRecordsExists` and `Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindRecordLinkValueKeys` as last arguments.
- Changed constructor of `Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent`. Added `Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode` and `Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier` as last arguments.
- Changed constructor of `Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent`. Added `Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier` as first argument.

# 3.0.14 (2019-04-19)

## Bug fixes

- PIM-8294: Allow for empty-looking strings like '0' in TextData
- PIM-8295: fetch all records that exist in SqlRecordsExists

## Improvements

- DAPI-212: Add command to run the Fetch products job

# 3.0.13 (2019-04-15)

## Bug fixes

- PIM-8277: Fix Reference Entities- Localisable attributes not refreshed in the record after changing the locale
- PIM-8293: Fix image normalization for assets with missing references

# 3.0.12 (2019-04-09)

## Bug fixes

- PIM-8233: Display the label instead of the code for reference entity axes in the product edit form

# 3.0.11 (2019-04-02)

## Bug fixes

- PIM-8263: inject correct presenter for `ProjectDueDateReminderNotifier`
- PIM-8252: Fix ACL not applied on some other actions menus
- PIM-8239: Display html tags in reference entity records grid

# 3.0.10 (2019-03-28)

## Bug fixes

- PIM-8244: fix PDF with assets generation
- PXD-94: Fix Asset transformations tab design
- PXD-101: Fix SSO page fields width


# 3.0.9 (2019-03-26)

## Bug fixes

- PXD-9: Fix Revert popin design
- PXD-79: Fix help messages design on SSO page
- PXD-83: Fix Proposals page design

# 3.0.8 (2019-03-20)

## Bug fixes

- PIM-8226: inject correct service to present dates
- PIM-8213: Fix no message when remove a reference entity linked to a product attribute
- PIM-8229: fix reference data option value zero value.

# 3.0.7 (2019-03-13)

## Bug fixes

- PIM-8211: Fix DI for pimee_product_asset.controller.asset_variation

## Improvements

- PIM-8159: Adds a command to be able to refresh records completenesses

# 3.0.6 (2019-03-08)

## Bug fixes

- PIM-8154: Fix validation rule dropdown overlapped with the sticky footer
- PIM-8049: Fix font size list of the rich text editor behind the next field
- PIM-8014: Fix the display of duplicate category trees in the product grid
- PIM-8189: Fix wrong edition being displayed on standard edition
- PIM-8194: Reference entity record values sanitizing
- PIM-8186: Fix variant axis with attribute of type Reference Entity link

## Improvements

- PIM-7863: Add reference entity metrics in the catalog volume monitoring as well as system info
- PIM-7933: Display the number of records and results for an entity in the grid
- AOB-337: Display the expiration date of the certificate on the SSO config page

# 3.0.5 (2019-02-25)

# 3.0.4 (2019-02-20)

## Bug fixes

- PIM-8017: Fix PDF generation
- PIM-8000: Accept links property when creating or updating a reference entity record with the API
- PIM-8023: Fix to center the title in the Assets mass upload modal

# 3.0.3 (2019-02-18)

## Bug fixes

- APAI-550: Add ACL checks on the backend part
- APAI-561: Silently filter unknown/unexisting attribute options on attribute options (Franklin Insights)
- PIM-8130: Fixed the product grid filter for attribute with type "Reference Entity" (multi & simple link)
- PIM-8138: Fix 401 error on password reset page

## Improvements

- APAI-471: Add warning on attribute deletion (Franklin Insights)
- APAI-494: Notify Julia when she has new attributes "pending" for mapping (Franklin Insights)
- APAI-581: Update the identifiers mapping when an attribute that belongs to the identifiers mapping is deleted (Franklin Insights)
- APAI-517: Add end-to-end scenario on identifiers mapping (Franklin Insights)
- APAI-518: Add end-to-end scenario on attributes mapping (Franklin Insights)
- APAI-464: add end to end scenario on bulk subscription (Franklin Insights)
- APAI-519: add end to end scenario on attribute options mapping (Franklin Insights)
- PIM-8052: Display the record labels in the product grid instead of the code

# 3.0.2 (2019-02-13)

## Bug fixes

- PIM-7985: Fix locale permissions on Reference Entities
- PIM-8012: Fix json-schema validation for the image attribute property `"max_file_size"` (API)
- PIM-8011: Some attribute properties are nullable (API)
- PIM-7999: Fix upsert record type attribute (API)
- PIM-8000: Accept `_links` property in patch endpoints (API)
- PIM-7998: Add validation on the immutable attribute properties (API)
- PIM-8015: Add validation for record attribute creation (API)
- PIM-8027: Fix create reference entity without labels (API)
- Fixed the "dot" menu on Reference Entity screen that was not visible if left panel was folded
- PIM-8032: Fix the image deletion with the keyboard
- PIM-8043: Fix to keep the Save and Cancel button always on the right during the edition of a reference entity attribute
- PIM-8046: Fix issue for reference entity attributes with a numeric code
- PIM-8037: allow to click on record row even if it has not attribute

## Improvements

- PIM-8040: The code should be check when upserting an attribute option (API)
- AOB-351: Add a user provider used when sso is activated

# 3.0.1 (2019-02-06)

- Name Enterprise version "Rose"

# 3.0.0 (2019-02-06)

## Manage reference entities

- PIM-7380: List reference entities

## Technical improvement

- Set PHP 7.2 as minimal required version
- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component
- PAV3-4: Regroup PAM Classes
- Composer use Packagist to retrieve pim-community-dev
- Uses centralized community edition technical requirements
- TIP-879: Uses utf8mb4 as encoding for MySQL instead of the less complete utf8
- TIP-883: In order to have a clean and independant product aggregate, ProductValue only provides attribute code and no more direct attribute access.
- TIP-889: Improve product export performance by computing headers at the end
- TIP-1041: Adds support for Elasticsearch 6, puts Published products in their own index

## BC breaks
- Change constructor of `Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer`. Add argument `Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface`
- Two new parameters must be defined for published products: `published_product_index_name` and `published_product_and_product_model_index_name`
- All product flat writers (CSV, XLSX for products and published products) now have two new arguments of type `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface` and `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface`. There two new services now managed the generation of headers for flat product file at export time.
- Remove the service `pimee_workflow.twig.extension.group_product_values`
- Remove the service `pimee_workflow.helper.sort_product_values`
- Remove the second constructor arguments of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct`
- Replace the `Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface` by `Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder` in `Akeneo\Pim\Permission\Bundle\Controller\Ui\ProductController`
- Replace argument `@pim_catalog.builder.product` by `@pim_catalog.association.missing_association_adder` in service `pim_catalog.updater.setter.association_field`
- Remove argument `@pim_catalog.association.missing_association_adder` from service `pimee_workflow.builder.published_product`
- Remove `Akeneo\Pim\Permission\Bundle\Form\Type\ProductGridFilterChoiceType`
- Remove bundle `PimEnterpriseUserBundle`
- The service `pim_catalog.repository.cached_attribute`, of type `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`, has been added to the construtor of the following classes:
  - `Akeneo/Pim/Asset/Component/Completeness/Checker/AssetCollectionCompleteChecker`
  - `Akeneo/Pim/Permission/Bundle/Filter/ProductValueAttributeGroupRightFilter`
  - `Akeneo/Pim/Permission/Bundle/Filter/ProductValueLocaleRightFilter`
  - `Akeneo/Pim/Permission/Bundle/Pdf/ProductPdfRenderer`
  - `Akeneo/Pim/Permission/Component/Filter/NotGrantedValuesFilter`
  - `Akeneo/Pim/WorkOrganization/TeamworkAssistant/Component/Calculator/AttributeGroupCompletenessCalculator`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Helper/FilterProductValuesHelper`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/AbstractProductValuePresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/AssetsCollectionPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/DatePresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/FilePresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/MetricPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/NumberPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/OptionPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/OptionsPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/PricesPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Presenter/ReferenceData/AbstractReferenceDataPresenter`
  - `Akeneo/Pim/WorkOrganization/Workflow/Bundle/Twig/SortProductValuesHelper`
  - `Akeneo/Pim/WorkOrganization/Workflow/Component/Normalizer/Indexing/LabelNormalizer`
  - `Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer`

- Move `PimEnterprise\Bundle\EnrichBundle\Twig\AttributeExtension` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\AttributeExtension`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\ProductModelNormalizer`
- Move `Akeneo\Asset\Bundle\Doctrine\ORM\Query\GrantedCategoryItemsCounter` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\Query\GrantedCategoryItemsCounter`
- Move `PimEnterprise\Bundle\EnrichBundle\Form\Type\LocaleType` to `Akeneo\Pim\Permission\Bundle\Form\Type\LocaleType`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository` to `Akeneo\Pim\Permission\Bundle\Entity\Repository\JobExecutionRepository`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobInstanceRepository` to `Akeneo\Pim\Permission\Bundle\Entity\Repository\JobInstanceRepository`
- Move `PimEnterprise\Bundle\EnrichBundle\Repository\AttributeRepositoryInterface` to `Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeRepositoryInterface`
- Move `PimEnterprise\Bundle\EnrichBundle\Repository\AttributeSearchableRepository` to `Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeSearchableRepository`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeRepository` to `Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeRepository`
- Move `PimEnterprise\Bundle\EnrichBundle\Controller\ProductController` to `Akeneo\Pim\Permission\Bundle\Controller\Ui\ProductController`
- Move `PimEnterprise\Bundle\EnrichBundle\Controller\ProductModelController` to `Akeneo\Pim\Permission\Bundle\Controller\Ui\ProductModelController`
- Move `PimEnterprise\Bundle\EnrichBundle\Controller\LocaleController` to `Akeneo\Pim\Permission\Bundle\Controller\Ui\LocaleController`
- Move `PimEnterprise\Bundle\EnrichBundle\Controller\CategoryTreeController` to `Akeneo\Pim\Permission\Bundle\Controller\Ui\CategoryTreeController`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\RuleRelationNormalizer` to `Akeneo\Pim\Automation\RuleEngine\Bundle\Normalizer\RuleRelationNormalizer`
- Move `PimEnterprise\Bundle\EnrichBundle\Controller\RuleRelationController` to `Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi\RuleRelationController`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\ImageNormalizer` to `Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer`
- Replace `%akeneo_rule_engine.normalizer.rule_definition.class%` by `Akeneo\Pim\Automation\RuleEngine\Bundle\Normalizer\RuleDefinitionNormalizer`
- Replace `%pimee_product_asset.doctrine.counter.granted_category_items.class%` by `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\Query\GrantedCategoryItemsCounter`
- MySQL charset for Akeneo is now utf8mb4, instead of the flawed utf8. If you have custom table, you can convert them with `ALTER TABLE my_custom_table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`. For Akeneo native tables, the migration scripts applies the conversion.
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\Filter\FilterExtension` to `Akeneo\Pim\Permission\Bundle\Datagrid\Extension\Filter\FilterExtension`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassActionEvents`
- Move `PimEnterprise\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\OroToPimGridFilterAdapter`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\RuleImpactedProductCountActionHandler` to `Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\MassAction\RuleImpactedProductCountActionHandler`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductHistory\GridHelper` to `Akeneo\Pim\WorkOrganization\ProductRevert\Datagrid\Configuration\ProductHistory\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` to `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Configuration\Product\FiltersConfigurator`
- Move `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\CompletenessRemover` to `Akeneo\Pim\Enrichment\Asset\Bundle\Doctrine\ORM\CompletenessRemover`
- Move `PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface` to `Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface`
- Move `PimEnterprise\Component\ProductAsset\Completeness\Checker\AssetCollectionCompleteChecker` to `Akeneo\Pim\Enrichment\Asset\Component\Completeness\Checker\AssetCollectionCompleteChecker`
- Move `PimEnterprise\Component\ProductAsset\Comparator\Attribute\AssetCollectionComparator` to `Akeneo\Pim\Enrichment\Asset\Component\Comparator\Attribute\AssetCollectionComparator`
- Move `PimEnterprise\Component\Catalog\Manager\AttributeValuesResolver` to `Akeneo\Pim\Permission\Component\Manager\AttributeValuesResolver`
- Move `PimEnterprise\Component\Catalog\ProductModel\Filter\GrantedProductAttributeFilter` to `Akeneo\Pim\Permission\Component\Filter\GrantedProductAttributeFilter`
- Move `PimEnterprise\Component\Catalog\Updater\Adder\AssetCollectionAdder` to `Akeneo\Pim\Enrichment\Asset\Component\Updater\Adder\AssetCollectionAdder`
- Move `PimEnterprise\Component\Catalog\\AssetCollectionValueFactory` to `Akeneo\Pim\Enrichment\Asset\Component\AssetCollectionValueFactory`
- Move namespace `PimEnterprise\Component\Catalog\Security` to `PimEnterprise\Component\Security`
- Change constructor of `PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedAssociationFieldSetter`. Add arguments `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface`, `Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery` two times and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`.
- Change constructor of `PimEnterprise\Component\Catalog\Security\Merger\NotGrantedAssociatedProductMerger`. Add arguments `Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery` two times and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`.
- Change constructor of `PimEnterprise\Component\Catalog\Security\Filter\NotGrantedAssociatedProductFilter`. Add arguments `Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery` two times and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`.
- Move namespace `PimEnterprise\Bundle\VersioningBundle\UpdateGuesser` to `Akeneo\Pim\Permission\Bundle\UpdateGuesser`
- Move `PimEnterprise\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` to `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\SkipVersionSubscriber`
- Move namespace `PimEnterprise\Bundle\VersioningBundle\Purger` to `PimEnterprise\Bundle\WorkflowBundle\Purger`
- Move all classes from `PimEnterprise\Bundle\ApiBundle\Normalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi`
- Move all classes from `PimEnterprise\Bundle\EnrichBundle\Controller\Rest` to `Akeneo\Asset\Bundle\Controller\Rest`
- Move all classes from `PimEnterprise\Bundle\FilterBundle\Filter\Tag` to `Akeneo\Asset\Bundle\Datagrid\Filter`
- Rename `PimEnterprise\Bundle\EnrichBundle\Controller\Rest\ChannelController` to `Akeneo\Asset\Bundle\Controller\Rest\AssetTransformationController`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\AssetNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\InternalApi\AssetNormalizer`
- Move `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\AssetCategoryAccessSubscriber` to `Akeneo\Asset\Bundle\Security\AssetCategoryAccessSubscriber`
- Move `PimEnterprise\Bundle\SecurityBundle\Normalizer\Flat\AssetCategoryNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Flat\AssetCategoryNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Router\ProxyProductRouter` to `PimEnterprise\Bundle\WorkflowBundle\Router\ProxyProductRouter`
- Move `PimEnterprise\Component\Api\Normalizer\ProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\ProductNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository\AssetRepository` to `Akeneo\Asset\Bundle\Doctrine\ORM\Repository\ExternalApi\AssetRepository`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetCategoryController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetCategoryController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetReferenceController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetReferenceController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetTagController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetTagController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetVariationController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetVariationController`
- Move `PimEnterprise\Bundle\ApiBundle\Normalizer\AssetReferenceNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi\AssetReferenceNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Normalizer\AssetVariationNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi\AssetVariationNormalizer`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductDraft\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\ProductDraft\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\Proposal\ContextConfigurator`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\Proposal\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ProductProposalDatasource` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ProductProposalDatasource`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductDraftHydrator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ResultRecord\ProductDraftHydrator`
- Move `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProposalGridListener` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\ConfigureProposalGridListener`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft\AttributeChoiceFilter` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\AttributeChoiceFilter`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft\AuthorFilter` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\AuthorFilter`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft\ChoiceFilter` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\ChoiceFilter`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraftFilterUtility` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\ProductDraftFilterUtility`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassApproveActionHandler` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\MassAction\Handler\MassApproveActionHandler`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassRefuseActionHandler` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\MassAction\Handler\MassRefuseActionHandler`
- Move `PimEnterprise\Bundle\SecurityBundle\Voter\ProductDraftVoter` to `PimEnterprise\Bundle\WorkflowBundle\Security\ProductDraftVoter`
- Move `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget` to `PimEnterprise\Bundle\WorkflowBundle\Widget\ProposalWidget`
- Move `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget` to `PimEnterprise\Bundle\WorkflowBundle\Widget\ProposalWidget`
- Move `PimEnterprise\Component\Api\Updater\AssetUpdater` to `PimEnterprise\Component\ProductAsset\Updater\ExternalApi\AssetUpdater`
- Move `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi\ProductDraftController`
- Move `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\PublishedProductController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\PublishedProductController`
- Move `PimEnterprise\Component\Api\Normalizer\PublishedProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\PublishedProductNormalizer`
- Remove `PimEnterprise\Component\Api\Repository\PublishedProductRepositoryInterface`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\PublishedProduct\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\PublishedProduct\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductHistoryHydrator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ResultRecord\PublishedProductHistoryHydrator`
- Move `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\AbstractProductPublisherTasklet` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\AbstractProductPublisherTasklet`
- Move `PimEnterprise\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\MassPublish` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\JobParameters\ConstraintCollection`
- Move `PimEnterprise\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider\MassPublish` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\JobParameters\DefaultValues`
- Move `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\PublishProductTasklet` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\PublishProductTasklet`
- Move `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\UnpublishProductTasklet` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\UnpublishProductTasklet`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to `PimEnterprise\Bundle\WorkflowBundle\Normalizer\ProductNormalizer`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\VersionNormalizer` to `PimEnterprise\Bundle\WorkflowBundle\Versioning\VersionNormalizer`
- Remove `PimEnterprise\Bundle\ApiBundle\DependencyInjection\Configuration`
- Remove `PimEnterprise\Bundle\ApiBundle\DependencyInjection\PimEnterpriseApiExtension`
- Change the constructor of `Akeneo\Pim\Permission\Bundle\User\UserContext` to remove `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Move namespace `Akeneo\Bundle\FileMetadataBundle` to `Akeneo\Tool\Bundle\FileMetadataBundle`
- Move namespace `Akeneo\Bundle\FileTransformerBundle` to `Akeneo\Tool\Bundle\FileTransformerBundle`
- Move namespace `Akeneo\Bundle\RuleEngineBundle` to `Akeneo\Bundle\Tool\RuleEngineBundle`
- Move namespace `Akeneo\Component\FileMetadata` to `Akeneo\Tool\Component\FileMetadata`
- Move namespace `Akeneo\Component\FileTransformer` to `Akeneo\Tool\Component\FileTransformer`
- Move class `PimEnterprise\Component\ProductAsset\Remover\CategoryAssetRemover` to `Akeneo\Asset\Bundle\Doctrine\ORM\Remover\CategoryAssetRemover`
- Move class `PimEnterprise\Component\ProductAsset\Factory\NotificationFactory` to `Akeneo\Asset\Bundle\Notification\NotificationFactory`
- Move class `PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM\AssetEventSubscriber` to `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Asset\AssetEventSubscriber`
- Move class `PimEnterprise\Bundle\ProductAssetBundle\Workflow\Presenter\AssetsCollectionPresenter` to `PimEnterprise\Bundle\WorkflowBundle\Presenter\AssetsCollectionPresenter`
- Remove class `Akeneo\Asset\Bundle\TwigExtension\ImageExtension`
- Move namespace `PimEnterprise\Component\TeamworkAssistant` to `Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component`
- Move namespace `PimEnterprise\Bundle\TeamworkAssistantBundle` to `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle`
- Move namespace `PimEnterprise\Bundle\VersioningBundle` to `Akeneo\Pim\WorkOrganization\ProductRevert`
- Move namespace `PimEnterprise\Component\Workflow` to `Akeneo\Pim\WorkOrganization\Workflow\Component`
- Move namespace `PimEnterprise\Bundle\Workflow` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle`
- Move namespace `PimEnterprise\Bundle\CatalogRuleBundle` to `Akeneo\Pim\Automation\RuleEngine\Bundle`
- Move namespace `PimEnterprise\Component\CatalogRule` to `Akeneo\Pim\Automation\RuleEngine\Component`
- Move class `PimEnterprise\Bundle\ApiBundle\Controller\ProductModelDraftController` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi\ProductModelDraftController`
- Move class `PimEnterprise\Bundle\ApiBundle\Controller\ProductModelProposalController` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi\ProductModelProposalController`
- Move class `PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter\AbstractReferenceDataPresenter` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\AbstractReferenceDataPresenter`
- Move class `PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter\ReferenceDataCollectionPresenter` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\ReferenceDataCollectionPresenter`
- Move class `PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter\ReferenceDataPresenter` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData\ReferenceDataPresenter`
- Move class `PimEnterprise\Component\Api\Normalizer\ProductModelNormalizer` to `Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi\ProductModelNormalizer`
- Move class `PimEnterprise\Bundle\ReferenceDataBundle\Publisher\ReferenceDataPublisher` to ` Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\ReferenceDataPublisher`
- Remove class `PimEnterprise\Bundle\ApiBundle\PimEnterpriseApiBundle`
- Remove class `PimEnterprise\Bundle\ConnectorBundle\DependencyInjection\PimEnterpriseConnectorExtension`
- Remove class `PimEnterprise\Bundle\ConnectorBundle\PimEnterpriseConnectorBundle`
- Remove class `PimEnterprise\Bundle\ReferenceDataBundle\DependencyInjection\PimEnterpriseReferenceDataExtension`
- Remove class `PimEnterprise\Bundle\ReferenceDataBundle\PimEnterpriseReferenceDataBundle`
- Rename class `Akeneo\Asset\Bundle\PimEnterpriseProductAssetBundle` to `Akeneo\Asset\Bundle\AkeneoAssetBundle`
- Rename class `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\PimEnterpriseTeamworkAssistantBundle` to `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\AkeneoPimTeamworkAssistantBundle`
- Rename class `Akeneo\Pim\WorkOrganization\ProductRevert\PimEnterpriseRevertBundle` to `Akeneo\Pim\WorkOrganization\ProductRevert\AkeneoPimProductRevertBundle`
- Rename class `Akeneo\Pim\WorkOrganization\Workflow\Bundle\PimEnterpriseWorkflowBundle` to `Akeneo\Pim\WorkOrganization\Workflow\Bundle\AkeneoPimWorkflowBundle`
- Rename class `Akeneo\Pim\Automation\RuleEngine\Bundle\PimEnterpriseCatalogRuleBundle` to `Akeneo\Pim\Automation\RuleEngine\Bundle\AkeneoPimRuleEngineBundle`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber\RuleExecutionSubscriber`. Remove argument `Symfony\Component\Security\Core\User\ChainUserProvider`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier`. Remove arguments `Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface` and `Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface`. Add arguments `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface` and `integer`.
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleBuilder`. Remove argument `Symfony\Component\Validator\Validator\ValidatorInterface`.
- Change constructor of `Akeneo\Pim\Permission\Bundle\Filter\ProductValueLocaleRightFilter`. Add argument `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Move namespace `PimEnterprise\Bundle\ProductAssetBundle` to `Akeneo\Asset\Bundle`
- Move namespace `PimEnterprise\Component\ProductAsset` to `Akeneo\Asset\Component`
- Change constructor of `Akeneo\Asset\Component\Upload\MassUpload\MassUploadProcessor`. Replace argument `Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface` with `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`.
- Change `Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber\MassUploadAssetsSubscriber::massUploadAssets()` signature to replace `Symfony\Component\EventDispatcher\GenericEvent` by `Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Writer\ProductAndProductModelWriter`. Replace argument `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
    with  `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface`, `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`, and `string`.
- Change constructor of `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\FilterConverter`. Remove argument `Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter` and `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser`.
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Tasklet\AbstractReviewTasklet`. Remove arguments `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`and `Symfony\Component\Security\Core\User\UserProviderInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet\AbstractProductPublisherTasklet`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager`, `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` and `Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet\UnpublishProductTasklet`. Add argument `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet\PublishProductTasklet`. Add argument `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductModelRepository`. Add argument `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\AddAttributeValueProcessor`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\AddProductValueWithPermissionProcessor`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\EditAttributesProcessor`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\EditCommonAttributesProcessor`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\RemoveProductValueWithPermissionProcessor`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\MassEdit\Processor\UpdateProductValueWithPermissionProcessor`. Remove arguments `Akeneo\UserManagement\Bundle\Manager\UserManager` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change constructor of `Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset\AddTagsToAssetsProcessor`. Add argument `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change constructor of `Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset\ClassifyAssetsProcessor`. Add argument `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change constructor of `Akeneo\Asset\Bundle\Doctrine\Common\Saver\AssetReferenceSaver`. Remove argument `Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface`.
- Change constructor of `Akeneo\Asset\Bundle\Doctrine\Common/Saver/AssetSaver`. Remove argument `Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface`.
- Change constructor of `Akeneo\Asset\Bundle\Controller\ProductAssetController`. Add arguments `Akeneo\Asset\Component\Builder\ReferenceBuilderInterface` and `Akeneo\Asset\Component\Builder\VariationBuilderInterface`.

## Security

- Move `PimEnterprise\Bundle\ApiBundle\Security\AccessDeniedHandler` to `Akeneo\Pim\Permission\Bundle\Api\AccessDeniedHandler`
- Move `PimEnterprise\Bundle\ApiBundle\Checker\QueryParametersChecker` to `Akeneo\Pim\Permission\Bundle\Api\QueryParametersChecker`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AbstractAuthorizationFilter\DatagridViewFilter` to `Akeneo\Pim\Permission\Bundle\Filter\AbstractAuthorizationFilter\DatagridViewFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager\ProductController` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager\ProductController`
- Move `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager\ProductModelController` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager\ProductModelController`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeViewRightFilter\AttributeRepository` to `Akeneo\Pim\Permission\Bundle\Filter\AttributeViewRightFilter\AttributeRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\JobInstanceEditRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\JobInstanceEditRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AbstractAuthorizationFilter` to `Akeneo\Pim\Permission\Bundle\Filter\AbstractAuthorizationFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeEditRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\AttributeEditRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeGroupViewRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\AttributeGroupViewRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeViewRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\AttributeViewRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\LocaleEditRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\LocaleEditRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\LocaleViewRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\LocaleViewRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductAndProductModelDeleteRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\ProductAndProductModelDeleteRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductRightEditFilter` to `Akeneo\Pim\Permission\Bundle\Filter\ProductRightEditFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductRightViewFilter` to `Akeneo\Pim\Permission\Bundle\Filter\ProductRightViewFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductValueAttributeGroupRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\ProductValueAttributeGroupRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductValueLocaleRightFilter` to `Akeneo\Pim\Permission\Bundle\Filter\ProductValueLocaleRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Attribute\AttributeRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductMassActionRepository` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductMassActionRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductModelRepository` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductModelRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Security\Elasticsearch\ProductQueryBuilderFactory` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Security\Doctrine\Common\Saver\FilteredEntitySaver` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\FilteredEntitySaver`
- Move `PimEnterprise\Bundle\DataGridBundle\Filter\DatagridViewFilter` to `Akeneo\Pim\Permission\Bundle\Datagrid\DatagridViewFilter`
- Move `PimEnterprise\Bundle\DataGridBundle\Manager\DatagridViewManager` to `Akeneo\Pim\Permission\Bundle\Datagrid\DatagridViewManager`
- Move `PimEnterprise\Bundle\DataGridBundle\EventListener\AddPermissionsToGridListener` to `Akeneo\Pim\Permission\Bundle\Datagrid\EventListener\AddPermissionsToGridListener`
- Move `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener` to `Akeneo\Pim\Permission\Bundle\Datagrid\EventListener\ConfigureProductGridListener`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder` to `Akeneo\Pim\Permission\Bundle\Datagrid\MassAction\ProductFieldsBuilder`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\RowActionsConfigurator` to `Akeneo\Pim\Permission\Bundle\Datagrid\Product\RowActionsConfigurator`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\Counter\GrantedCategoryItemsCounter` to `Akeneo\Asset\Bundle\Doctrine\ORM\Query\GrantedCategoryItemsCounter`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\Counter\GrantedCategoryProductsCounter` to `Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\Query\GrantedCategoryProductsCounter`
- Move `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\ProductCategoryAccessSubscriber` to `Akeneo\Pim\Permission\Bundle\Datagrid\EventListener\ProductCategoryAccessSubscriber`
- Move `PimEnterprise\Bundle\EnrichBundle\EventSubscriber\SavePermissionsSubscriber` to `Akeneo\Pim\Permission\Bundle\EventSubscriber\SavePermissionsSubscriber`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Filter` to `Akeneo\Pim\Permission\Bundle\Filter`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Form\Subscriber` to `Akeneo\Pim\Permission\Bundle\Form\EventListener`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Provider\Form` to `Akeneo\Pim\Permission\Bundle\Form\Provider`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Form\Type` to `Akeneo\Pim\Permission\Bundle\Form\Type`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product` to `Akeneo\Pim\Permission\Bundle\MassEdit\Processor`
- Move `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter` to `Akeneo\Pim\Permission\Bundle\MassEdit\Writer\ProductAndProductModelWriter`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer` to `Akeneo\Pim\Permission\Bundle\Normalizer\InternalApi\IncompleteValuesNormalizer`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\Product\PermissionFilter` to `Akeneo\Pim\Permission\Bundle\Datagrid\Filter\PermissionFilter`
- Move `PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber\JobProfilePermissionsSubscriber` to `Akeneo\Pim\Permission\Bundle\Form\EventListener\JobProfilePermissionsSubscriber`
- Move `PimEnterprise\Bundle\ImportExportBundle\Form\Type\JobProfilePermissionsType` to `Akeneo\Pim\Permission\Bundle\Form\Type\JobProfilePermissionsType`
- Move `PimEnterprise\Bundle\ImportExportBundle\Manager\JobExecutionManager` to `Akeneo\Pim\Permission\Bundle\Manager\JobExecutionManager`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter` to `PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\Filter\ProjectCompletenessFilter`
- Move `PimEnterprise\Bundle\UIBundle\Controller\AjaxOptionController` to `Akeneo\Asset\Bundle\Controller\Rest\AjaxOptionController`
- Move `PimEnterprise\Bundle\PdfGeneratorBundle\Twig\ImageExtension` to `Akeneo\Asset\Bundle\TwigExtension\ImageExtension`
- Move `PimEnterprise\Bundle\SecurityBundle\Controller\PermissionRestController` to `Akeneo\Pim\Permission\Bundle\Controller\InternalApi\PermissionRestController`
- Move `PimEnterprise\Bundle\PdfGeneratorBundle\Controller\ProductController` to `Akeneo\Pim\Permission\Bundle\Controller\ProductController`
- Move `PimEnterprise\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer` to `Akeneo\Pim\Permission\Bundle\Pdf\ProductPdfRenderer`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\PublishedProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\InternalApi\PublishedProductNormalizer`
- Move namespace `PimEnterprise\Bundle\SecurityBundle` to `Akeneo\Pim\Permission\Bundle`
- Move namespace `PimEnterprise\Component\Security` to `Akeneo\Pim\Automation\RuleEngine\Component`

## Removed classes

- Remove `PimEnterprise\Bundle\FilterBundle\PimEnterpriseFilterBundle`
- Remove `PimEnterprise\Bundle\ImportExportBundle\PimEnterpriseImportExportBundle`
- Remove `PimEnterprise\Bundle\FilterBundle\DependencyInjection\PimEnterpriseFilterExtension`
- Remove `PimEnterprise\Bundle\ImportExportBundle\DependencyInjection\PimEnterpriseImportExportExtension`
- Remove `PimEnterprise\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductAndProductModelReader`
- Remove `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductWriter`
- Remove `PimEnterprise\Bundle\EnrichBundle\Form\Type\AvailableAttributesType`
- Remove `PimEnterprise\Bundle\PdfGeneratorBundle\PimEnterprisePdfGeneratorBundle`
- Remove `PimEnterprise\Bundle\PdfGeneratorBundle\DependencyInjection\PimEnterprisePdfGeneratorExtension`
- Remove `PimEnterprise\Bundle\UIBundle\DependencyInjection\PimEnterpriseUIExtension`
- Remove `PimEnterprise\Component\Catalog\Updater\AttributeUpdater`
- Remove `PimEnterprise\Bundle\EnrichBundle\Normalizer\AttributeNormalizer`
- Remove `PimEnterprise\Component\Catalog\Normalizer\Standard\AttributeNormalizer`
- Remove `PimEnterprise\Bundle\UIBundle\PimEnterpriseUIBundle`
- Remove `PimEnterprise\Bundle\DashboardBundle\PimEnterpriseDashboardBundle`
