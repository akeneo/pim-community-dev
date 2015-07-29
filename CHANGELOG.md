# 1.4.x

## Technical improvements
- Product edit form revamp
- Mass approve or reject proposals
- Use DEFERRED_EXPLICIT as Doctrine changeTrackingPolicy (for all models)
- Continue to group persist()/flush() to the dedicated layer (SaverInterface) to avoid to have them everywhere in the stack
- Replaced `attribute_options.yml` by `attribute_options.csv`

## Bug fixes
- PIM-3933: Add missing translation keys for published products
- PIM-3296: Better display of options changes in Proposals
- PIM-4311: Create indexes on PublishedProduct

## BC Breaks
- Change the constructor of `Pim\Bundle\UserBundle\Context\UserContext`, `Pim\Bundle\UserBundle\Form\Type\UserType`, `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, `Pim\Bundle\EnrichBundle\Controller\JobExecutionController`, `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`, `Pim\Bundle\EnrichBundle\Controller\VariantGroupController`, `Pim\Bundle\EnrichBundle\EventListener\UserContextListener`, `PimEnterprise\Bundle\EnrichBundle\Normalizer\ProductNormalizer`, `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController`, `PimEnterprise\Bundle\CatalogBundle\Manager\ProductMassActionManager`, `PimEnterprise\Bundle\DataGridBundle\EventListener\AddPermissionsToGridListener`, `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder`, `PimEnterprise\Bundle\FilterBundle\Filter\Product\PermissionFilter`, `PimEnterprise\Bundle\ImportExportBundle\Manager\JobExecutionManager`, `PimEnterprise\Bundle\WorkflowBundle\Controller\ProductDraftController` and `PimEnterprise\Bundle\WorkflowBundle\Controller\PublishedProductController`. Replaced `Symfony\Component\Security\Core\SecurityContext` by `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, `PimEnterprise\Bundle\EnrichBundle\Normalizer\ProductNormalizer`, `PimEnterprise\Bundle\UserBundle\Context\UserContext`, `PimEnterprise\Bundle\WorkflowBundle\Controller\ProductDraftController` and `PimEnterprise\Bundle\WorkflowBundle\Controller\PublishedProductController`. Added `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager`, `PimEnterprise\Bundle\CatalogBundle\Manager\ProductCategoryManager`, `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget`, `PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator`, `PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft\GridHelper`, `PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductHistory\GridHelper`, `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\GridHelper`, `PimEnterprise\Bundle\DataGridBundle\Datagrid\PublishedProduct\GridHelper`, `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureJobProfileGridListener`, `PimEnterprise\Bundle\DataGridBundle\Manager\DatagridViewManager`, `PimEnterprise\Bundle\EnrichBundle\Form\View\ProductFormView`, `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, `PimEnterprise\Bundle\FilterBundle\Filter\Product\CategoryFilter`, `PimEnterprise\Bundle\PdfGeneratorBundle\Controller\ProductController`, `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich\DisableProductValueFieldSubscriber`, `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich\JobPermissionsSubscriber`, `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich\ProductSubscriber`, `PimEnterprise\Bundle\SecurityBundle\Form\Subscriber\RemoveProductValueSubscriber`, `PimEnterprise\Bundle\TransformBundle\Normalizer\Filter\GrantedAttributeNormalizerFilter`, `PimEnterprise\Bundle\WebServiceBundle\Handler\Rest\ProductHandler`, `PimEnterprise\Bundle\WorkflowBundle\Controller\PublishedProductRestController`, `PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\DelegatingProductSaver`, `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\ReplaceProductUpdatedFlashMessageSubscriber` and `PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper`. Replaced `Symfony\Component\Security\Core\SecurityContextInterface` by `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\ProductCategoryManager`, `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget` and `PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\DelegatingProductSaver`. Added `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change interface `Symfony\Component\Validator\ValidatorInterface` to `Symfony\Component\Validator\Validator\ValidatorInterface`
- Change interface `Symfony\Component\OptionsResolver\OptionsResolverInterface` to `Symfony\Component\OptionsResolver\OptionsResolver`
- Change interface `Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase` to `Symfony\Component\Form\Test\TypeTestCase`
- Change interface `Symfony\Component\Form\Extension\Core\View\ChoiceView` to `Symfony\Component\Form\ChoiceList\View\ChoiceView`
- Change interface `Symfony\Component\Validator\MetadataFactoryInterface` to `Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface`
- Change interface `Symfony\Component\Validator\ExecutionContextInterface` to `Symfony\Component\Validator\Context\ExecutionContextInterface`
- Remove bundle StofDoctrineExtensionsBundle
- Symfony events `FormEvents::POST_BIND` and `FormEvents::BIND` have been replaced by `FormEvents::POST_SUBMIT` and `FormEvents::SUBMIT` in `Pim\Bundle\TranslationBundle\Form\Subscriber`
- Rename methods `bind()` and `postBind()` by `submit()` and `postSubmit()` in `Pim\Bundle\TranslationBundle\Form\Subscriber`
- Rename method `setDefaultOptions()` to `configureOptions()` in all form types
- Service `pim_catalog.validator.product` calls now `Symfony\Component\Validator\Validator\RecursiveValidator`, take the `pim_catalog.validator.context.factory` service as the first argument and remove the fourth and fifth argument
- Add `Symfony\Component\HttpFoundation\RequestStack` as the fifth argument in `Pim\Bundle\UserBundle\Context\UserContext`, `$defaultLocale` become the sixth argument and `Symfony\Component\HttpFoundation\Request` is no longer called
- Remove connections `report_source` and `report_target` from dbal in `app/config/config.yml`
- Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\GridHelper`, added `Symfony\Component\Security\Core\SecurityContextInterface` argument
- Remove `PimEnterprise\Bundle\WorkflowBundle\Comparator\DateComparator`
- Rename `PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface` to `PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface`. First argument of method `supportsComparison` is replaced by a string $type
- `PimEnterprise\Bundle\WorkflowBundle\Comparator\ChainedComparator` is replaced by `PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorRegistry`
- Move Comparators from `PimEnterprise/Bundle/WorkflowBundle/Comparator` to `Pim/Component/Catalog/Comparator`
- Replace tag `pimee_workflow.comparator` by `pimee_workflow.attribute.comparator`
- Remove `PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\ProductDraftSaver`
- Remove `PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent`
- Remove `PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvents`
- Remove `PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent`
- Remove `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ChangeSet\MetadataSubscriber`
- Remove `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\MarkInProgressSubscriber`
- Remove `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\PrepareProductDraftChangesSubscriber`
- Remove `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\PrepareUploadingMediaSubscriber`
- Remove `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\UpdateProductDraftStatusSubscriber`
- Change the constructor of `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager`, removed the first argument $registry, added `Akeneo\Component\StorageUtils\Saver\SaverInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface` as the latest arguments
- Remove second argument of `supports` method of `PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface`
- Move PimEnterprise/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductMassActionRepository
- Move PimEnterprise/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductMassActionRepository
- Move PimEnterprise/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeRepository
- Move PimEnterprise/Bundle/WorkflowBundle/Doctrine/MongoDBODM/{ → Repository}/ProductDraftRepository
- Move PimEnterprise/Bundle/WorkflowBundle/Doctrine/MongoDBODM/{ → Repository}/PublishedProductRepository
- Move PimEnterprise/Bundle/WorkflowBundle/Doctrine/ORM/{ → Repository}/ProductDraftRepository
- Move PimEnterprise/Bundle/WorkflowBundle/Doctrine/ORM/{ → Repository}/PublishedAssociationRepository
- Move PimEnterprise/Bundle/WorkflowBundle/Doctrine/ORM/{ → Repository}/PublishedProductRepository
- Add ProductBuilderInterface argument of the constructor of PimEnterprise/Bundle/CatalogRuleBundle/Validator/Constraints/ProductRule/ValueActionValidator
- Replace UserManager argument by ProductDraftRepositoryInterface in `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\GridHelper`
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Controller\ProductDraftController`
- Add `findByIds` method to `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface`
- Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager`, added `Pim\Component\Classification\Repository\CategoryRepositoryInterface` and `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` as last arguments
- Add a `getGrantedCategoryCodes` method in `PimEnterprise/Bundle/SecurityBundle/Entity/Repository/CategoryAccessRepository`
- ProductsUpdater takes now ProductPropertySetterInterface and ProductPropertyCopierInterface as arguments and not anymore ProductUpdaterInterface
- ValueActionValidator takes now ProductPropertySetterInterface and ProductPropertyCopierInterface as arguments and not anymore ProductUpdaterInterface
- Replace argument ObjectManager by SaverInterface and RemoverInterface in `PimEnterprise\Bundle\DataGridBundle\Manager\DatagridViewManager` constructor
- Rename `PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess` to `PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess`
- In `PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface`, rename methods `setEditProducts` to `setEditItems`, `isEditProducts` to `isEditItems`, `setViewProducts` to `setViewItems`, `isViewProducts` to `isViewItems`, `setOwnProducts` to `setOwnItems` and `isOwnProducts` to `isOwnItems`
- `PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface` now handles `Pim\Component\Classification\Model\CategoryInterface` instead of `Pim\Bundle\CatalogBundle\Model\CategoryInterface`
- Rename class `pimee_security.entity.category_access.class` to `pimee_security.entity.product_category_access.class`
- Add argument `Symfony\Component\Security\Core\SecurityContextInterface` in constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\GridHelper`
- Remove the option 'flush_only_object' from `Akeneo/Bundle/RuleEngineBundle/Doctrine/Common/Saver/RuleDefinitionSaver`

# 1.3.x

# 1.3.18 (2015-07-09)

# 1.3.17 (2015-07-07)

## Bug fixes
- PIM-4494: Fix loading page when family has been sorted
- Fixed missing parameter in mapping

# 1.3.16 (2015-06-08)

# 1.3.15 (2015-06-05)

# 1.3.14 (2015-06-03)

## Bug fixes
- PIM-4227: fix BC break introduced in 1.3.13

# 1.3.13 (2015-05-29)

## Bug fixes
- PIM-4223: Fix grid sorting order initialization (changed to be consistent with Platform behavior)
- PIM-4227: Disable product versionning on category update (never used and very slow)

# 1.3.12 (2015-05-22)

# 1.3.9 (2015-04-21)

# 1.3.7 (2015-04-03)

## Bug fixes
- PIM-3961: Fix issue with validation of products when apply a rule

# 1.3.6 (2015-04-01)

## Bug fixes
- PIM-3935: Fix "working copy value" tooltip is always empty
- PIM-3957: Unable to publish a product already published

# 1.3.5 (2015-03-19)

## Bug fixes
- PIM-3925: do not show system menu if no item allowed

# 1.3.4 (2015-03-11)

## Bug fixes
- PIM-3814: "Affected by rules" info on attribute doesn't disappear
- PIM-3820: Fix attribute options translation which were not displayed

# 1.3.3 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.3.2 (2015-02-27)

## Bug fixes
- PIM-3834: Fix issue, new products are created during the appliance of rules (paginator + cache clearer)
- PIM-3820: Attribute option translation not well handled on import

## BC breaks
- Change constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier`, ObjectDetacherInterface replaces CacheClearer and $ruleDefinitionClass arguments

# 1.3.1 (2015-02-24)

## Bug fixes
- PIM-3760: Fix reverting previous product versions with file/image attributes
- PIM-3784: Generate the completeness on selected products before to mass-publish
- PIM-3765: Do not allow the revert of a product if it has a variant group

## BC breaks
- Update constructor of `PimEnterprise/Bundle/WorkflowBundle/Publisher/Product/ProductPublisher` to add a `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` $completenessManager argument
- Update constructor of `PimEnterprise/Bundle/VersioningBundle/Reverter/ProductReverter` to add a `Symfony\Component\Translation\TranslatorInterface` $translator argument

# 1.3.0 - "Strawberry" (2015-02-12)

## Bug fixes
- PIM-3760: Fix reverting previous product versions with file/image attributes

# 1.3.0-RC2 (2015-02-12)

## Bug fixes
- PIM-3617: Fix scope selection hidden by notification alert on product edit
- PIM-3526: Fix author in the "proposals to review" widget

## BC breaks
- Change constructor of `PimEnterprise/Bundle/DashboardBundle/Widget/ProposalWidget`, UserContext argument replaced by `Oro\Bundle\UserBundle\Entity\UserManager`
- Change constructor of `PimEnterprise/Bundle/EnrichBundle/Form/Type/AvailableAttributesType`, hard coded classes are now passed in parameters
- Change constructor of `PimEnterprise/Bundle/EnrichBundle/Form/Type/MassEditAction/EditCommonAttributesType`, hard coded classes are now passed in parameters
- Change constructor of `PimEnterprise/Bundle/VersioningBundle/Reverter/ProductReverter` to use `Akeneo\Component\StorageUtils\Saver\SaverInterface` and not `Pim\Bundle\CatalogBundle\Manager\ProductManager`

# 1.3.0-RC1 (2015-02-03)

## Community Edition changes
- Based on CE 1.3.x, see [changelog](https://github.com/akeneo/pim-community-dev/blob/master/CHANGELOG.md)

## Features
- Rules engine and smart attributes
- List of proposals to ease validation
- Add a date filter in the proposal grid

## Technical improvements
- remove the fixed mysql socket location
- switch to minimum-stability:stable in composer.json
- base template has been moved from `app/Resources/views` to `PimEnrichBundle/Resources/views`
- remove BaseFilter usage
- add a view manager to help integrators to override and add elements to the UI (tab, buttons, etc)
- Use ProductInterface and not AbstractProduct
- Use ProductValueInterface and not AbstractProductValue
- Use ProductPriceInterface and not AbstractProductPrice
- Use ProductMediaInterface and not AbstractMetric
- Use AttributeInterface and not AbstractAttribute
- Use CompletenessInterface and not AbstractCompleteness
- Introduce a `PimEnterprise\Bundle\WorkflowBundle\Saver\ProductDraftSaver` and a `PimEnterprise\Bundle\WorkflowBundle\Saver\DelegatingProductSaver` with corresponding services to allow to save working copy, product draft or save both depending on permissions
- Add a command to run a rule or all rules in database
- Add a rule view on attribute edit page
- Removed `icecat_demo` from fixtures
- Add a form view updater registry to update the attributes form views dynamically

## BC breaks
- Remove service `pimee_workflow.repository.product_draft_ownership`. Now, `pimee_workflow.repository.product_draft` should be used instead.
- Move method `findApprovableByUser` from `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface` to `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface`.
- Remove interface `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface`
- Rename `PimEnterprise\Bundle\DashboardBundle\Widget\ProductDraftWidget` to `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget`, replace the first constructor argument with `Symfony\Component\Security\Core\SecurityContextInterface` and remove the third argument
- Refactored `PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionsDenormalizer.php`, replaced the inheritance of `AttributeOptionDenormalizer` by `AbstractValueDenormalizer`
- Add `Doctrine\Common\Persistence\ObjectManager` as third argument of `PimEnterprise\Bundle\DataGridBundle\Manager` constructor
- Change of constructor `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to remove arguments `Pim\Bundle\CatalogBundle\Builder\ProductBuilder` and  `Pim\Bundle\CatalogBundle\Factory\MetricFactory`. `Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface` is expected as second argument and `Symfony\Component\Serializer\Normalizer\NormalizerInterface` is expected as seventh argument.
- Add a requirement regarding the need of the exec() function (for job executions)
- `PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler\ResolveDoctrineOrmTargetEntitiesPass` has been renamed to `ResolveDoctrineTargetModelsPass`
- Remove the override of `PimEnterprise\Bundle\CatalogBundle\Manager\MediaManager`
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Saver\ProductDraftSaver`. `Doctrine\Common\Persistence\ObjectManager` is now expected as first argument.
- Move the versioning denormalizers to CE PimTransformBundle, namespaces are changed but the services alias are kept
- change constructor `PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft\GridHelper` which now expects a SecurityContextInterface
- rename src/PimEnterprise/Bundle/SecurityBundle/Voter/ProductDraftOwnershipVoter.php to ProductDraftVoter
- changes in constructor of `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager`, ProductManager argument replaced by `Akeneo\Component\Persistence\SaverInterface` and `Pim\Bundle\CatalogBundle\Manager\MediaManager` added as last argument
- Drop the `bypass_product_draft` from product saving options
- Replace the `ProductDraftPersister` by different savers
- Remove the overrides `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily`, `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus`
- Update constructor of `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Classify` to add a `Akeneo\Component\Persistence\BulkSaverInterface` $productSaver argument
- Update constructor of `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to add a `Akeneo\Component\Persistence\BulkSaverInterface` $productSaver argument
- Update constructor of `PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType` to add a `string` $dataClass argument
- Update constructor of `PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction\PublishType` to add a `string` $dataClass argument
- Update constructor of `PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction\UnpublishType` to add a `string` $dataClass argument
- Change of constructor of `Pim/Bundle/ImportExportBundle/Form/Type/JobInstanceType` to accept `Symfony\Component\Translation\TranslatorInterface` as for the second argument

## Bug fixes
- PIM-3300: Fixed bug on revert of a multiselect attribute options
- Remove the `is_default` from fixtures for attribute options
- PIM-3548: Do not rely on the absolute file path of a media

# 1.0.x

# 1.0.29 (2015-07-06)
- Update community-edition dependency to 1.2.36 version.

# 1.0.28 (2015-05-29)

## Bug fixes
- PIM-4227: Disable product versionning on category update (never used and very slow)

# 1.0.27 (2015-05-27)

## Bug fixes
- PIM-4223: Fix grid sorting order initialization (changed to be consistent with Platform behavior)

# 1.0.26 (2015-03-16)

## Bug fixes
- PIM-3883: Mass publish does not work on Mongo
- PIM-3898: Quick export does not work on published products

# 1.0.25 (2015-03-11)
- Update community-edition dependency to 1.2.32 version.

# 1.0.24 (2015-03-06)
- Update community-edition dependency to 1.2.31 version.

## Bug fixes
- PIM-3658: Mass publish time are exponential and not linear

# 1.0.23 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.0.22 (2015-02-24)

## Bug fixes
- PIM-3808: Fix the export of published products

# 1.0.21 (2015-02-20)
- Update community-edition dependency to 1.2.28 version.

# 1.0.20 (2015-02-13)
- Update community-edition dependency to 1.2.27 version.

# 1.0.19 (2015-02-12)
- Update community-edition dependency to 1.2.26 version.

# 1.0.18 (2015-02-04)

## Bug fixes
- PIM-3717: Fix handling draft save with special attributes

# 1.0.17 (2015-01-28)

## Bug fixes
- PIM-3712: Fix installation issue related to the tag of gedmo/doctrine-extensions v2.3.11, we freeze to v2.3.10

# 1.0.16 (2015-01-23)

## Bug fixes
- PIM-3664: Fix product media stacktrace regression on missing media on filesystem during an export

# 1.0.15 (2015-01-21)

## Bug fixes
- PIM-3636: Fix the failing publish of associations when mass publish products

# 1.0.14 (2015-01-16)

## Bug fixes
- PIM-3615: Context of the grid not applied in product form for an attribute type date

# 1.0.13 (2015-01-14)

## Bug fixes
- PIM-3603: Trigger saving wysiwyg editor contents when submitting product form manually

# 1.0.12 (2015-01-14)

# 1.0.11 (2015-01-09)

## Bug fixes
- PIM-3548: Do not rely on the absolute file path of a media

# 1.0.10 (2014-12-23)

## Bug fixes
- PIM-3533: Fix wrong keys being generated for empty price attributes in normalized product snapshots to fix the revert of product versions

# 1.0.9 (2014-12-17)

## Improvements
- PIM-3475: Add sort order in options attribute to sample catalog

## Bug fixes
- PIM-3448: Avoid to allow to approve/refuse/delete a proposal if the user can't edit all values of this proposal

# 1.0.8 (2014-12-10)

## Bug fixes
- PIM-3449: Fix performance problem on on grid with many categories

# 1.0.7 (2014-12-03)

## Bug fixes
- PIM-3444: Fix the enabled flag not published

# 1.0.6 (2014-11-26)

## Bug fixes
- PIM-3302: Published product page is not well displayed with project via the standard edition. To fix this bug, add `bundles/pimenterpriseui/css/pimee.less` to your stylesheets in `app/Resources/views/base.html.twig`
- PIM-3385: Fix deleting attributes not linked to published products

# 1.0.5 (2014-11-13)

## Bug fixes
- PIM-3331: Fix draft creation when saving values for newly added attributes for the first time
- PIM-3351: Test data value in PricesDenormalizer to avoid creating empty ProductPrices
- PIM-3301: Test data value in DateTimeDenormalizer to avoid reverted date to be set on current day
- PIM-3354: Fix category filter on multi-positioned product on unclassified

# 1.0.4 (2014-10-31)

## Bug fixes
- PIM-3300: Fixed bug on revert of a multi-select attribute options

# 1.0.3 (2014-10-24)

## Bug fixes
- PIM-3206: Removing the group "ALL" from categories' permissions after a clean installation.
- PIM-3234: Fix performance issue on granted category filter

# 1.0.2 (2014-10-10)

## Bug fixes
- Fix installer fail on requirements when you change the archive and uploads folder
- Fixed icecat-demo-dev fixtures
- Setup relationships of published products with interfaces in order to ease overriding
- Fixed problem on view rights on attribute groups which were not displayed on family configuration
- Fixed a bug with wysiwyg values disappearing from drafts when saved without modifications
- Fixes products count by tree performances problem with lots of categories
- Stabilize composer.json (minimum-stability: stable) and fix monolog version issue

## Improvements

## BC breaks

# 1.0.1 (2014-09-10)

## Bug fixes

## Improvements

## BC breaks

# 1.0.0 - "Dandelion" (2014-08-29)

## Improvements

## Bug fixes
- Fixed read only mode of products page
- Fixed a regression on product draft submission

## BC breaks

# 1.0.0-RC3 (2014-08-27)

## Improvements
- Java dependency has been removed
- Add fallback to en_US locale
- Application is partially translated in French

## Bug fixes
- Fixed disabled file input appearing as if it were enabled
- Fixed product draft validation
- Don't allow users without corresponding rights to edit entity permissions
- Imported categories now have the permissions of their parent

## BC breaks
- Change constructor of `PimEnterprise/Bundle/EnrichBundle/Form/Subscriber/AttributeGroupPermissionsSubscriber`, `PimEnterprise/Bundle/EnrichBundle/Form/Subscriber/CategoryPermissionsSubscriber` and `src/PimEnterprise/Bundle/EnrichBundle/Form/Subscriber/LocalePermissionsSubscriber` to inject `Oro\Bundle\SecurityBundle\SecurityFacade` as the second argument
- JS and CSS are not minified anymore. We advise to use server side compression for bandwidth savings.
- Change constructor of `PimEnterprise/Bundle/SecurityBundle/Manager/CategoryAccessManager` to add the user group class as fourth argument
- Methods `setAccess` of `PimEnterprise/Bundle/SecurityBundle/Manager/CategoryAccessManager` now has a last parameter to handle the flush

# 1.0.0-RC2 (2014-08-14)

## Improvements
- Apply permissions on REST API
- Change labels of permissions in category, locale, attribute group and import/export profiles
- Load locales in installer with default permissions
- Replace the grey background with a border for fields in product view
- New CSS for publish/unpublish/send for approval buttons
- Display completeness in the product and published product view page
- Add confirmation before publishing or unpublishing a product
- Implement automatic inherited permissions selection for all entities (javascript)
- Add missing page titles on product and published product view
- Add default user group "All" in permission when create a tree, locale, attribute group
- Don't allow users to classify products they don't own
- Fix product count in mass publish/unpublish operations

## Bug fixes
- Fix extraction of category ids from DatagridView filters to properly filter views the user can apply
- Fix the number of proposals displayed in the proposal widget
- Take into account permission on 'Edit working copy' button

## BC breaks
- Put media back into published product (as now in product)
