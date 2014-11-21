# 1.1.x (based on CE 1.3.x, see [changelog](https://github.com/akeneo/pim-community-dev/blob/master/CHANGELOG.md))

## Improvements
- Add a date filter in the proposal grid
- remove the fixed mysql socket location
- switch to minimum-stability:stable in composer.json
- base template has been moved from `app/Resources/views` to `PimEnrichBundle/Resources/views`
- remove BaseFilter usage
- add a view manager to help integrators to override and add elements to the UI (bat, buttons, etc)

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
- Remove the override of MediaManager
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Saver\ProductDraftSaver`. `Doctrine\Common\Persistence\ObjectManager` is now expected as first argument.

## Bug fixes
- PIM-3300: Fixed bug on revert of a multiselect attribute options
- Remove the `is_default` from fixtures for attribute options

# 1.0.x

## Bug fixes
- PIM-3444: Fix the enabled flag not published

# 1.0.6 (2014-11-26)

## Bug fixes
- PIM-3302: Published product page is not well displayed with project via the standard edition. To fix this bug, add `bundles/pimenterpriseui/css/pimee.less` to your stylesheets in `app/Resources/views/base.html.twig`

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
- Setup relationships of published products with interfaces in order to easy overriding

## BC breaks

# 1.0.1 (2014-09-10)

## Bug fixes
- Fixed problem on view rights on attribute groups which were not displayed on family configuration

## BC breaks

# 1.0.0 (2014-08-29) (based on CE 1.2.0, see [changelog](https://github.com/akeneo/pim-community-dev/blob/dd64effbe173f595e4afea08d0e80528fd441741/CHANGELOG.md))

## Improvements

## Bug fixes
- Fixed read only mode of products page
- Fixed a regression on product draft submission

## BC breaks

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

## BC breaks
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

## BC breaks
- Put media back into published product (as now in product)
