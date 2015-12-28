# 1.4.x

## Bug fixes
- PIM-5348: fix asset category tree bug on new tree
- PIM-5347: fix mongo database in case of attribute removal

# 1.4.14 (2015-12-17)

## BC Breaks
- Change constructor of `PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor` to add a `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
- Change constructor of `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to add
        `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`,
        `Symfony\Component\Validator\Validator\ValidatorInterface`,
        `Symfony\Component\Serializer\Normalizer\NormalizerInterface`

# 1.4.13 (2015-12-10)

# 1.4.12 (2015-12-03)

## Bug fixes
- PIM-5136: Fixed completeness of published products

# 1.4.12 (2015-12-03)

# 1.4.11 (2015-11-27)

## Bug fixes
- PIM-5147: do not apply permission restrictions on attribute groups for families

# 1.4.10 (2015-11-20)

# 1.4.9 (2015-11-12)

# 1.4.8 (2015-11-09)

## Bug fixes
- PIM-5044: Fix rule priority not taken into account in rule command
- PIM-5149: Problem to access to mass edit executions in process tracker

# 1.4.7 (2015-11-03)

## Bug fixes
- PIM-5079: Add batch jobs script for 1.3 to 1.4 migration

# 1.4.6 (2015-10-27)

## Bug fixes
- PIM-5055: Fix medias migration for removed medias in product values

# 1.4.5 (2015-10-23)

## Bug fixes
- PIM-4970: Asset mass upload - Do not remove uploaded asset if not deleted from server
- PIM-5028: Fix log levels for prod environment

# 1.4.4 (2015-10-19)

## Bug fixes
- SDS-91 : Published product value migration with MongoDB
- PIM-5017: Fix media migration with lots of files
- PIM-4971: Fix flash messages for asset edit form

# 1.4.3 (2015-10-09)

## Bug fixes
- PIM-4794: Add contextualized thumbnails support for assets collections in product grid
- PIM-4717: Use the raw filename to move the file to the temporary file folder
- PIM-4977: Revert PIM-4443 by re-allowing full numeric entity codes

# 1.4.2 (2015-10-01)

## Bug fixes
- PIM-4874: Fix mass refuse proposals
- PIM-4924: Fix missing indexes of the product value
- PIM-4927: Fix redirect to published products for back to grid action
- PIM-4914: Fixed Quick export file name
- PIM-4760: Fix error if quick export not well configured
- PIM-4880: Fix media not displayed in product PDF download
- PIM-4887: Fixed locales active status when removed from channels
- PIM-4911: Fix escaping of property with locale and scope
- PIM-4922: Fix media attribute preview
- PIM-4925: Fix dashboard patch information
- PIM-4936: Fixes performances problems and memory leak at import time
- PIM-4935: Fix inconsistent data on import using comparison optimisation

# 1.4.1 (2015-09-24)

## Bug fixes
- PIM-4911: Fix product edit form string escaping
- PIM-4778: Fix asset grid thumbnails for unknown types
- PIM-4895: Fix redirect after mass unpublish

# 1.4.0 (2015-09-23)

## BC breaks
- Removed function `countPublishedProductsForCategoryAndChildren` from `PublishedProductRepositoryInterface` and implementations in favor of `countPublishedProductsForCategory`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\AssociationTypeRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\AttributeOptionRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\AttributeRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\CategoryRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\FamilyRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\GroupRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\ProductRemover`
- Removed `Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\AttributeEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\AttributeOptionEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\CategoryEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\FamilyEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\GroupEvents`
- Removed event `pim_catalog.pre_remove.association_type` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.attribute` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.post_remove.attribute` use `akeneo.storage.post_remove` instead
- Removed event `pim_catalog.pre_remove.attribute_option` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.category` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.tree` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.family` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.group` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.product` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.post_remove.product` use `akeneo.storage.post_remove` instead
- Change constructor of `Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer`.
  Added `Liip\ImagineBundle\Imagine\Cache\CacheManager`, `Liip\ImagineBundle\Imagine\Data\DataManager` and `Liip\ImagineBundle\Imagine\Filter\FilterManager`

## Bug fixes
- PIM-4892: No way to assign a user when edit a role

# 1.4.0-RC1 (2015-09-04)

## Technical improvements

- Rename FileStorage classes and services: File (file information stored in database) => FileInfo, RawFile (physical file on the disk) => File
- Change namespace of Classification component and bundle from Pim to Akeneo

# 1.4.0-BETA3 (2015-09-02)

## Bug fixes
- PIM-4775: When I mass-edit products I can view only, proposals are created

# 1.4.0-BETA2 (2015-08-17)

## BC Breaks
- `imagemagick` is now a requirement of the PIM
- Change the constructor of `PimEnterprise\Bundle\UserBundle\Context\UserContext`. Takes `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`, `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`, `Symfony\Component\HttpFoundation\RequestStack`, `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`, a `$defaultLocale` string, `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` and `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository`
- Change the constructor of `PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter` to add a string `$className`
- Rename constants `VIEW_PRODUCTS` and `EDIT_PRODUCTS` of `PimEnterprise\Bundle\SecurityBundle\Attributes` to `VIEW_ITEMS` and `EDIT_ITEMS`
- Remove deprecated `AbstractDoctrineController` parent to `Pim\Bundle\EnrichBundle\Controller\CategoryTreeControlle`. Now it extends `Symfony\Bundle\FrameworkBundle\Controller\Controller`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CategoryTreeController`, added `$rawConfiguration`, `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` as the last argument. Removed `Symfony\Component\HttpFoundation\Request`, `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface`, `Symfony\Component\Routing\RouterInterface`, `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Symfony\Component\Form\FormFactoryInterface`, `Symfony\Component\Validator\Validator\ValidatorInterface`, `Symfony\Component\Translation\TranslatorInterface`, `Doctrine\Common\Persistence\ManagerRegistry` and `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Rename service `pim_enrich.controller.category_tree` to `pim_enrich.controller.category_tree.product`
- Change constructor of `Pim\Bundle\EnrichBundle/Twig/CategoryExtension` to remove `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` and `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`. Added `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterRegistryInterface`
- Add an array `$getChildrenTreeByParentId` to `getChildrenTreeByParentId` of `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Media related classes `PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia`, `PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMediaInterface` and `PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\MediaPublisher` have been removed
- Change constructor of  `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductWriter` to remove argument `Pim\Bundle\CatalogBundle\Manager\MediaManager`
- Change constructor of `PimEnterprise/Bundle/MassEditAction/Operation/EditCommonAttributes` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Akeneo\Component\FileStorage\RawFileRawFileStorerInterface`
- Change constructor of `Akeneo\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `PimEnterprise\Bundle\UserBundle\Context\UserContext` to add a string `$treeOptionKey`
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
- Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager`, added `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` and `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` as last arguments
- Add a `getGrantedCategoryCodes` method in `PimEnterprise/Bundle/SecurityBundle/Entity/Repository/CategoryAccessRepository`
- ProductsUpdater takes now ProductPropertySetterInterface and ProductPropertyCopierInterface as arguments and not anymore ProductUpdaterInterface
- ValueActionValidator takes now ProductPropertySetterInterface and ProductPropertyCopierInterface as arguments and not anymore ProductUpdaterInterface
- Replace argument ObjectManager by SaverInterface and RemoverInterface in `PimEnterprise\Bundle\DataGridBundle\Manager\DatagridViewManager` constructor
- Rename `PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess` to `PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess`
- In `PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface`, rename methods `setEditProducts` to `setEditItems`, `isEditProducts` to `isEditItems`, `setViewProducts` to `setViewItems`, `isViewProducts` to `isViewItems`, `setOwnProducts` to `setOwnItems` and `isOwnProducts` to `isOwnItems`
- `PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface` now handles `Akeneo\Component\Classification\Model\CategoryInterface` instead of `Pim\Bundle\CatalogBundle\Model\CategoryInterface`
- Rename class `pimee_security.entity.category_access.class` to `pimee_security.entity.product_category_access.class`
- Add argument `Symfony\Component\Security\Core\SecurityContextInterface` in constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal\GridHelper`
- Remove the option 'flush_only_object' from `Akeneo/Bundle/RuleEngineBundle/Doctrine/Common/Saver/RuleDefinitionSaver`
- Add argument `Symfony\Component\Serializer\SerializerInterface` and `Pim\Bundle\CatalogBundle\Manager\LocaleManager` in constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization\RuleDefinitionProcessor`
- Change constructor of `PimEnterprise\Bundle\UserBundle\Context\UserContext`, replace `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, add `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager` has been changed
