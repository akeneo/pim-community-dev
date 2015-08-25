# 1.4.x

# 1.4.0-BETA2 (2015-08-17)

## BC Breaks
- Media related classes `PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia`, `PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMediaInterface` and `PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\MediaPublisher` have been removed
- Change constructor of  `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductWriter` to remove argument `Pim\Bundle\CatalogBundle\Manager\MediaManager`
- Change constructor of `PimEnterprise/Bundle/MassEditAction/Operation/EditCommonAttributes` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Akeneo\Component\FileStorage\RawFileRawFileStorerInterface`
- Change constructor of `Akeneo\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- `dispatchAction`, `showAction`, `showAttributeAction` and `draftsAction` have been removed from the `PimEnterprise\Bundle\EnrichBundle\Controller\ProductController`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\RowActionsConfigurator`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft\GridHelper` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductDraft\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductHistory\GridHelper` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductHistory\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\ContextConfigurator` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\GridHelper` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\PublishedProduct\GridHelper` to `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\PublishedProduct\GridHelper`

## Bug fixes
PIM-4443: Exporting a product with an attribute with a numeric code gives an error, full numeric codes for entities are now forbidden except for products

# 1.4.0-alpha (2015-07-31)

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
- Add argument `Symfony\Component\Serializer\SerializerInterface` and `Pim\Bundle\CatalogBundle\Manager\LocaleManager` in constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization\RuleDefinitionProcessor`
- Change constructor of `PimEnterprise\Bundle\UserBundle\Context\UserContext`, replace `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, add `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager` has been changed

# 1.3.x

# 1.3.22 (2015-08-25)

## Bug fixes
- PIM-4612: Error on Quick Export (MongoDB)

# 1.3.21 (2015-08-17)

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

