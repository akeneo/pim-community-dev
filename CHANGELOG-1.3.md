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

