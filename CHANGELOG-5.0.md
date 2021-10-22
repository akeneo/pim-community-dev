# 5.0.x

## Bug fixes

- PIM-10040: Fix longtext types instead of json type in old catalogs

# 5.0.51 (2021-10-18)

# 5.0.50 (2021-10-11)

# 5.0.49 (2021-10-11)

## Bug fixes

- PIM-10099: Fix "upload assets" button being displayed in asset families with a media link as main media when the user doesn't have the permission to create an asset (backport #PIM-10088)

# 5.0.48 (2021-09-21)

# 5.0.47 (2021-09-20)

## Bug fixes

- PIM-10060: Impossible to edit products in a new tab/window from a right click on the product grid
- PIM-10073: [Backport PIM-9671] DQI de-activation on attribute group is not fully taken into account

# 5.0.46 (2021-09-03)

# 5.0.45 (2021-08-27)

# 5.0.44 (2021-08-16)

## Bug fixes

- PIM-9975: Skip DQI evaluation for texts coming from Word to fix timeout and performance issues during product save
- PIM-10012: Fix rule grid mass actions when using the label filter
- PIM-10023: [BACKPORT PIM-9937] Rule engine - Fix remove action for reference entity collection and asset collection attributes

# 5.0.43 (2021-07-26)

# 5.0.42 (2021-07-20)

# 5.0.41 (2021-07-19)

## Bug fixes

- PIM-9971: [Backport] Add associations in published product normalizer

## Improvements

- PIM-9969: Add queue to lazy load Reference Entity Records thumbnails

## Bug fixes

- PIM-9972: Half the selected published product are unpublished through mass action

# 5.0.40 (2021-07-09)

# 5.0.39 (2021-07-06)

## Bug fixes

- PIM-9942: Fix parsing of locale and channel codes in asset mass upload form

# 5.0.38 (2021-07-02)

## Bug fixes

- PIM-9945: Fix the number of displayed elements in the locale grid

# 5.0.37 (2021-07-01)

## Bug fixes

- PIM-9926: API - Display an error when setting an asset collection value with an invalid data format

# 5.0.36 (2021-06-25)

# 5.0.35 (2021-06-22)

# 5.0.34 (2021-06-22)

# 5.0.33 (2021-06-18)

## Bug fixes

- PIM-9919: Breadcrumb for Reference Entities now redirects to the record list
- PIM-9915: Permissions tabs are missing for Assets and Reference Entity Records import profiles
- PIM-9918: Concatenate rule does not keep anymore the trailing zeros on a decimal number

# 5.0.32 (2021-06-16)

# 5.0.31 (2021-06-10)

## Bug fixes

- PIM-9876: Fix purge of products old scores in Data Quality Insights
- PIM-9884: Fix cannot create rule to make calculation with scopable price attributes
- PIM-9883: Fix TIFF to jpeg conversion.

# 5.0.30 (2021-06-04)

## Bug fixes

- PIM-9895: [Backport] PIM-9707: ES Max query size and add test for the ElasticSearch client chunked bulk index

# 5.0.29 (2021-05-31)

# 5.0.28 (2021-05-28)

# 5.0.27 (2021-05-26)

## Bug fixes

- PIM-9841: Improve dashboard performance: introduce data cache (through pim:volume:aggregate) for number of values for asset query and number of values per record

# 5.0.26 (2021-05-21)

# 5.0.25 (2021-05-19)

## Bug fixes

- PIM-9865: [Backport] PIM-9771: Export to PDF doesn't export Image

# 5.0.24 (2021-05-07)

## Bug fixes

- PIM-9846: [Backport] PIM-9822: Asset Manager - Error 500 after deleting a filterable asset family attribute

# 5.0.23 (2021-05-05)

## Bug fixes

- API-1557: Published products do not produce Pim events anymore
- PIM-9845: Ensure that filters on published products take into account the scope & the locale

# 5.0.22 (2021-04-27)

## Bug fixes

- PIM-9830: Upgrade phpseclib dependency following security issue CVE-2021-30130

# 5.0.21 (2021-04-23)

PIM-9823: [Backport] PIM-9734: Fix URI too long for rules on smart attributes

# 5.0.20 (2021-04-22)

## Bug fixes

- PIM-9815: Fix asset and record imports in XLSX when sone cells contain only numeric characters

# 5.0.19 (2021-04-20)

## Bug fixes

- PIM-9814: Fix infinite loop when using get all assets API endpoint (Backport PIM-9702)
- OB-752: Fix 5.0 memcached package issue

# 5.0.18 (2021-04-15)

## Bug fixes

- PIM-9811: [Backport] PIM-9705: Replace rules codes list by a simple link to the attribute edit form on the PEF and the assets

# 5.0.17 (2021-04-15)

## Bug fixes

- PIM-9810: Add missing status filter for published products

# 5.0.16 (2021-04-08)

## Bug fixes

- PIM-9793: Fix custom rule import
- PIM-9795: [Standard Kernel] Do not load Messenger configuration from EE if it exists in project

# 5.0.15 (2021-04-06)

# 5.0.14 (2021-04-01)

## Bug fixes

- PIM-9751: Fix Standard Kernel for dev environment

# 5.0.13 (2021-03-29)

# 5.0.12 (2021-03-26)

# 5.0.11 (2021-03-24)

# 5.0.10 (2021-03-23)

# 5.0.9 (2021-03-19)

## Bug fixes

- PIM-9747: Preview of URL containing cyrilic characters crashed the page on the Asset Manager

# 5.0.8 (2021-03-17)

# 5.0.7 (2021-03-09)

# 5.0.6 (2021-03-09)

# 5.0.5 (2021-02-19)

## Improvements

- BH-351: use Debian nodejs v12 in docker-compose

# 5.0.4 (2021-02-02)

# 5.0.3 (2021-01-29)

# 5.0.2 (2021-01-29)

## Bug fixes

- PIM-9629: Fix filtering issue on product value "identifier" via the API for published products
- PIM-9584: Fix case insensitive links between products and assets

# 5.0.1 (2021-01-08)

# 5.0.0 (2020-12-31)

## Bug fixes

- PIM-9560: Reference entities are blocked after using the filter.
- PIM-9332: Bump resource's memory limits for flexibility environments
- PIM-9388: Fix product link rules for scopable/localizable asset collection attributes
- PIM-9389: Unfriendly page title in create rule page.
- PIM-9376: Duplicate button appears under variant products.
- PIM-9226: Fix error on channel deletion after migration from v3.2.
- Fixes memory leak when indexing product models with a lot of product models in the same family (see https://github.com/akeneo/pim-community-dev/pull/11742)
- PIM-9109: Fix SSO not working behind reverse proxy.
- PIM-9133: Fix product and product model save when the user has no permission on some attribute groups
- PIM-9149: Fix compare/translate on product
- DAPI-947: The evaluation of the title formatting criterion should be apply only on text attributes that are localizable and are defined as main title
- PIM-9138: Rules import not working with asset manager
- PIM-9196: Allow the search on label and code on the rules grid
- PIM-9197: Fix the rule execution when attribute code is not in lower case
- PIM-9239: Fix proposal datagrid when there is a product model proposal with an empty value suggestion
- PIM-9202: Fix Asset Manager / Product link rules not working with multiple consumers
- PIM-9261: Fix API assets pagination
- PIM-9270: Fix assets family product-link-rule definition
- PIM-9295: Fix error when applying an "Add groups" action to a product model
- PIM-9309: Update mekras/php-speller dependency to fix Swedish spelling issues
- PIM-9316: Fix url encoding of media links in asset edit form
- PIM-9318: Add created_at & updated_at fields in RefEntity record table
- PIM-9334: Add error during rule import when a condition value contains null value
- PIM-9324: Fix cannot save product when simple reference entity linked to this product is deleted
- PIM-9243: Creation and update dates are not displayed on the asset page
- PIM-9362: Fix missing "System information" translations for asset analytics
- PIM-9363: Fix API error 500 when import a picture with an incorrect extension
- PIM-9370: Fixes page freezing with a big number of attribute options
- PIM-9404: Fix incorrect cast of numeric attribute option codes
- PIM-9393: Add error message on job instance when permissions edit is empty
- PIM-9400: Fix asset linked products not refreshing when switching locale
- PIM-9412: Keep asset collection order when sort order is the same
- PIM-9392: Prevent generating asset thumbnail when file is too large
- PIM-9411: Fix TWA project widget searching on all contributors
- PIM-9372: Fix media-link thumbnail re-generation
- PIM-9444: Fix locking issue on attribute table for retrieving the attribute options that need to be evaluated
- PIM-9441: Fix errors when importing a product proposal with several changes on the same attribute
- PIM-9457: Remove option tab on attributes setting page when attribute is not simple/multi select
- PIM-9454: Fix scalar value type check in PQB filters
- PIM-9460: Fix performance issue on export
- PIM-9458: Fix proposal creation when a user does not have permissions on attributes
- PIM-9489: Fix exception wrongly thrown when merging not granted values
- PIM-9503: Ignore permissions when executing rules in a job step
- PIM-9504: Fix the selection of the category tree when a user creates a rule
- PIM-9500bis: Add "asset_manager_link_assets_to_products" and "asset_manager_compute_transformations" type and job translations.
- PIM-9529: Fix translation in rule engine edit page
- PIM-9536: Fix unexpected behaviors on drag&drop in rules edit page (calculate and concatenate actions)
- PIM-9528: Fix asset code changed into lower case in create asset/upload asset UI
- PIM-9537: Fix importing reference entities with wrong code fails the import
- PIM-9541: Fix API users shown in Project contributors search
- PIM-9545: Fix possible memory leak in large import jobs
- PIM-9564: Use original filename instead of data for MediaFile attributes in naming convention
- PIM-9543: Print PDF content with Han (Chinese, Japanese, Korean) characters
- PIM-9589: Fix attribute edit form in Asset Manager
- Fix the display page of reference entity's record import jobs in XLSX
- PIM-9512: Fix asset navigation panel is not kept close
- PIM-9579: Fix the duplication of a product when missing rights on values
- PIM-9574: Fix product duplication to throw an error when the identifier of the duplicated product is not valid
- PIM-9593: Prevent job "compute project calculation" from appearing in the process tracker and in the dashboard
- PIM-9594: Fix project completeness when at least 1 attribute group have the "All" group in its permissions but the project's locale permissions does not
- PIM-9600: Fix rules that use string concatenation with measurement ending by zero
- PIM-9606: Fix rule execution job status when stopping the job during the last rule
- PIM-9625: Order of the assets is not kept

## Improvements

- DAPI-834: Data quality - As Julia, when I'm overing the dashboard, I'd like to see the medium grade for a given column.
- DAPI-697: Data quality - As Julia, when I'm on the DQI page, I want to click the attributes that need improvements and land on the PEF.
- DAPI-830: Add more supported languages for data quality text checking
- DAPI-806: Improve criteria evaluations performance
- DAPI-943: Do not use prepare statement anymore
- DAPI-739: Add coefficients by criterion to the calculation of the axes rates
- DAPI-635: Add spellcheck on WYSIWG editors
- DAPI-798: Allow spelling suggestions after a title formatter check
- DAPI-895: As Julia, I'd like spell-check to be available for Norwegian
- DAPI-749: Improve Dashboard rates purge
- DAPI-863: Evaluate the applicability of the criterion title formatting as soon as possible
- RUL-20: Rule engine - As Julia, I would like to copy values from/to different attribute types
- RUL-49: Rule engine - As Peter, I would like to clear attribute values, associations, categories and groups
- RUL-77: Rule engine - As Peter, I would like to add labels to my rules
- CLOUD-1959: Use cloud-deployer 2.2 and terraform 0.12.25
- MET-207: Asset Manager - As Peter, I would like to manually re-execute naming conventions
- RUL-271: Rule engine - As Peter, I'd like to add a condition on a relative date for created/updated fields
- PIM-9506: Make "image" the default media type for media link asset attributes
- PIM-9452: Add a command to update ES max field limit on Serenity upgrade (Helm hook)
- PIM-9562: default asset preview and thumbnail
- PIM-9558: Remove the word "Damn" from the PIM

## New features

- DAPI-854: Data quality - Variant products are also evaluated
- RUL-17: Rule engine - Add the concatenate action type to concatenate some attribute values into a single attribute value
- RUL-28: Rule engine - As Peter, I'd like to calculate attribute values
- AOB-277: Add an acl to allow a role member to view all job executions in last job execution grids, job tracker and last operations widget.
- RAC-54: Add a new type of associations: Association with quantity
- RAC-123: Add possibility to export product/product model with labels instead of code

## BC Breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet\ImpactedProductCountTasklet` to change last argument from `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` to `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\AdderActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\CopierActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\SetterActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\RemoverActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change return type of `Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface` from `void` to `array`
- Change return type of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater` from `void` to `array`
- Add method `getType()` to `Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface` interface
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Bundle\Twig\RuleExtension` to add `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Remove class `Akeneo\Pim\Automation\RuleEngine\Bundle\Normalizer\RuleDefinitionNormalizer`
- Change constructor of `\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor` to
  - remove `Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface`
  - add `Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver\DelegatingProductSaver` to
  - remove `Symfony\Component\EventDispatcher\EventDispatcherInterface` and `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer`
  - add `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface` (twice) and `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface`
- Change constructor of `\Akeneo\Pim\Permission\Component\Merger\MergeDataOnProduct` to add `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface`
- Change interface `Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface` to add `isEnabled` and `setEnabled` methods
- Change interface `Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface` to rename `findAllOrderedByPriority` method by `findEnabledOrderedByPriority`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi\GetCategoriesController` to replace `ObjectFilterInterface $objectFilter` by `CollectionFilterInterface $collectionFilter`
- Change constructor of `Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context\AssociationContext` to replace `IdentifiableObjectRepositoryInterface $productRepository` by `ProductRepositoryInterface $productRepository`
- Remove `Akeneo\Pim\Enrichment\AssetManager\Bundle\Analytics\CountAssetFamilies` class
- Remove `Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Analytics\CountReferenceEntities` class
- Change `Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery::getGrantedItemIds()` method to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change `Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface::getGrantedProductIdentifiers()` method to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change `Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface::getGrantedProductIdentifiers()` method to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change `Akeneo\Pim\Permission\Bundle\Entity\Query\ProductCategoryAccessQuery::getGrantedProductIdentifiers()` method to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change `Akeneo\Pim\Permission\Bundle\Entity\Query\ProductModelCategoryAccessQuery::getGrantedProductIdentifiers()` method to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository` to use `Akeneo\UserManagement\Component\Model\UserInterface` instead of `Symfony\Component\Security\Core\User\UserInterface`. It affects the following public and protected methods:
  - `getGrantedChildrenIds()`
  - `getGrantedChildrenCodes()`
  - `getGrantedCategoryIds()`
  - `isOwner()`
  - `areAllCategoryCodesGranted()`
  - `isCategoriesGranted()`
  - `getGrantedChildrenQB()`
- Change `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository::getGrantedUserGroupsForEntityWithValues()` to replace `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface $entityWithValues` parameter by `Akeneo\Tool\Component\Classification\CategoryAwareInterface $entity`
- Change `Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager::isUserGranted()` to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change `Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager::isUserGranted()` to replace `Symfony\Component\Security\Core\User\UserInterface $user` parameter by `Akeneo\UserManagement\Component\Model\UserInterface $user`
- Change constructor `Akeneo\Pim\Permission\Bundle\Voter\CategoryVoter` to replace `mixed $className` parameter by `string $className`
- Change constructor `Akeneo\Pim\Permission\Component\Updater\Setter\GrantedCategoryFieldSetter` to replace `ObjectRepository $categoryAccessRepository` parameter by `CategoryAccessRepository $categoryAccessRepository`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\UserRepositoryInterface::isProjectContributor()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\UserRepository::isProjectContributor()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification\ProjectNotifierInterface::notifyUser()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectCreatedNotifier::notifyUser()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectDueDateReminderNotifier::notifyUser()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectFinishedNotifier::notifyUser()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectFinishedNotifier::findApprovableByUser()` to use `Akeneo\UserManagement\Component\Model\UserInterface $user` as parameter instead of `Symfony\Component\Security\Core\User\UserInterface $user`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager` to add `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $publishedProductBulkSaver`
- Change `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft` to remove `getAttributes()` method
- Change `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft` to remove `getAttributes()` method
- Change `Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface::findUserEntityWithValuesDraft()` to return `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface` instead of `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface`
- Change constructor `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener\EnsureUserCanBeDeletedSubscriber` to make the parameter `IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery` not null
- Update `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct` to:
  - remove the `setFamilyId()`, `setProductModel()` and `getProductModel()` methods
  - remove the `$categoryIds` public property and the `$familyId`, `$groupIds` and `$productModel` protected properties
  - remove the `getAssociationForType()`, `getAssociationForTypeCode()` and `setAssociations()` public methods
- Rename `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository::isCategoriesGranted` to `isCategoryIdsGranted`
