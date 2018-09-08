# 1.7.x

# 1.7.32 (2018-09-07)

# 1.7.31 (2018-08-22)

## Bug fixes

- PIM-7585: apply permissions on the column picker of the product datagrid

## Improvements

- PIM-7614: Reduce SQL queries and loading time on the product edit form

# 1.7.30 (2018-08-14)

# 1.7.29 (2018-08-01)

# 1.7.28 (2018-07-26)

# 1.7.27 (2018-07-24)

# 1.7.26 (2018-07-23)

## Bug fixes

- PIM-7526: Prevent warnings about the generation of asset variation from being shown after uploading a non-image file.

## BC breaks:

### AppKernel 

- Remove `Pim\Bundle\JsFormValidationBundle\PimJsFormValidationBundle` and `APY\JsFormValidationBundle\APYJsFormValidationBundle`

### Routing

- Remove routing from `APYJsFormValidationBundle` 

# 1.7.24 (2018-07-05)

## Bug fixes

- PIM-7464: Fix memory leak during rule execution.

# 1.7.23 (2018-06-25)

# 1.7.22 (2018-06-05)

# 1.7.21 (2018-04-23)

# 1.7.20 (2018-04-03)

# Improvements:

- PIM-7221: Improve the way the completeness is computed when updating an asset collection in MongoDB.

# 1.7.19 (2018-02-27)

# 1.7.18 (2018-02-22)

# 1.7.17 (2018-02-01)

# 1.7.16 (2018-01-11)

# 1.7.15 (2017-12-18)

## Bug fixes

- PIM-7054: Optimize completeness generator
- PIM-7056: Fix a bug that prevents to delete options on localizable multi and simple select attributes

# 1.7.14 (2017-11-21)

# 1.7.13 (2017-11-09)

## Bug fixes

- PIM-6961: Do not apply validation on the faked identifier when importing rules (backport of PIM-6930)

# 1.7.12 (2017-10-25)

# 1.7.11 (2017-10-16)

# 1.7.10 (2017-10-10)

# 1.7.9 (2017-09-27)

## Bug fixes

- PIM-6780: Fix locale permissions on minimal catalog
- PIM-6798: Fix the simple-select and multi-select copiers

# 1.7.8 (2017-08-22)

# 1.7.7 (2017-08-03)

## Bug fixes

- PIM-6471: Fix permission tab for XLSX import profiles.

# 1.7.6 (2017-06-30)

## Bug fixes

- PIM-6465: Fix loading mask issue on asset creation from UI.
- PIM-6530: Fix pim:installer:grant-backend-processes-accesses command 

# 1.7.5 (2017-06-02)

## Bug fixes

- PIM-6393: Context kept when creating category tree
- PIM-6470: Fix issue on Permission filter when added to the user default filters

# 1.7.4 (2017-05-10)

## Bug fixes

- PIM-6314: Deleting a published product raises an internal error

# 1.7.3 (2017-04-14)

## Bug fixes

- PIM-6315: Fix publish button still clickable after a product is published

# 1.7.2 (2017-04-07)

## Bug fixes

- PIM-6248: Fix notification message on project creation
- PIM-6308: Fix migration of teamwork assistant

# 1.7.1 (2017-03-23)

**Caution:** Due to a wrong doctrine migration in the previous version, you also need to re-run them `php app/console doctrine:migrations:migrate` for this patch.
**Do not run this command if you have already fixed the migration issue in the 1.7.0**

## Bug fixes

- PIM-6213: Remove ticks on published form.
- PIM-6251: Fix completeness not calculated during bulk action edit attributes.
- PIM-6245: Correctly switch to project channel when selecting a Teamwork Assistant project.
- PIM-6266: Fix doctrine migration to add Teamwork Assistant job rights.
- PIM-6205: Fix asset collection field rendering on view mode

# 1.7.0 (2017-03-14)

## Bug fixes

- PIM-6201: Add automatically the view permission to the user if this one has edit permission during fixtures import

## Technical improvements

- PIM-6204: Add analytics providers for Enterprise Edition

## Teamwork Assistant

- AMS-184: As a contributor or a project creator, I can see project's products by status

# 1.7.0-BETA2 (2017-03-06)

# 1.7.0-BETA1 (2017-03-02)

- PIM-5448: Filters are not applied after approval of a proposal

## Web API

- API-47: Use OAuth2 to authenticate users on the web API
- API-48: As Peter, I would like to generate client_id and secret keys for OAuth2
- API-63: As Peter, I would like to manage who can access to the web API
- API-18: As Julia, I would like to list and filter products
- API-9: As Julia, I would like to get/create/update/delete a product
- API-16: As Julia, I would like to list families
- API-23: As Julia, I would like to get/create/update a family
- API-15: As Julia, I would like to list attributes
- API-22: As Julia, I would like to get/create/update an attribute
- API-17: As Julia, I would like to list categories
- API-29: As Julia, I would like to get/create/update a category
- API-75: As Julia, I would like to list channels
- API-77: As Filips, I would like to discover all routes in the API

# 1.7.0-ALPHA1 (2017-02-23)

## Teamwork Assistant

- AMS-3: As Julia, I would like to create a project based on a selection of products
- AMS-8: As a contributor/ manager, I would like to followup projects on my dashboard - Front
- AMS-6: As a Project creator, I would like to edit a project
- AMS-10: As a project creator, I would like to add a label to a project
- AMS-11: As a project creator, I would like to add a description to a project
- AMS-12: As a project creator, I would like to add a due date to a project
- AMS-13: As a Project creator, I would like to delete a project
- AMS-16: As a contributor/ Project creator, I would like to be notified before the due date
- AMS-17: As a project creator/ contributor, I would like to be notified when a project is finished
- AMS-18: As a contributor, I would like to be notified on a project
- AMS-19: As a project creator, I would like to notify contributors only if they have attributes to fill in
- AMS-20: As a contributor, I would like to see a project in the view selector
- AMS-22: As a contributor I would like to select a project
- AMS-44: As a Project creator, I would like my project to be updated with catalog structure updates
- AMS-48: As a project creator, I would like due date to be required
- AMS-128: As Julia, I would like to stay on my working locale
- AMS-130: As Julia, I would like to stay on my working scope
- AMS-153: As a owner or contributor I want to see updated completeness each new day
- PIM-5999: Fix list tree permission in manage category page

## Functional improvements

## Technical improvements

- Update spec to be independent from actual developer timezone
- GITHUB-5455: Redo channel's form `Asset transformations` tab to fit new form implementation based on internal REST API

## BC breaks

### Bundles

- Remove `PimEnterprise\Bundle\WebServiceBundle\PimEnterpriseWebServiceBundle`

### Dependency Injection

- Rename service `pimee_serializer.normalizer.structured.attribute` to `pimee_catalog.normalizer.standard.attribute`
- Rename service `pim_product_asset.normalizer.structured.asset` to `pimee_product_asset.normalizer.standard.asset`
- Rename service `pim_product_asset.normalizer.structured.variation` to `pimee_product_asset.normalizer.standard.variation`
- Rename service `pim_product_asset.normalizer.structured.channel_configuration` to `pimee_product_asset.normalizer.standard.channel_configuration`
- Rename service `pim_product_asset.normalizer.flat.asset` to `pimee_product_asset.normalizer.flat.asset`

### Classes

- Remove class `PimEnterprise\Bundle\EnrichBundle\Controller\ChannelTransformationController`
- Remove class `PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater\DraftViewUpdater` and associated service `pimee_enrich.form.view.view_updater.draft`
- Remove class `PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater\SmartViewUpdater` and associated service `pimee_enrich.form.view.view_updater.smart`
- Move `PimEnterprise\Component\Catalog\Normalizer\Structured\AttributeNormalizer` to `PimEnterprise\Component\Catalog\Normalizer\Standard\AttributeNormalizer`
- Move `PimEnterprise\Component\ProductAsset\Normalizer\Structured\AssetNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Standard\AssetNormalizer`
- Move `PimEnterprise\Component\ProductAsset\Normalizer\Structured\ChannelConfigurationNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Standard\ChannelConfigurationNormalizer`
- Move `PimEnterprise\Component\ProductAsset\Normalizer\Structured\VariationNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Standard\VariationNormalizer`
- Move `Akeneo\Bundle\RuleEngineBundle\Normalizer\RuleNormalizer` to `Akeneo\Bundle\RuleEngineBundle\Normalizer\Standard\RuleNormalizer`
- Replace `Pim\Component\Catalog\Query\Filter\FieldFilterInterface` by `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface` in `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\AssetCategoryAccessSubscriber`
- Update classes and services to use the interface `Pim\Component\User\Model\GroupInterface`in place of `Oro\Bundle\UserBundle\Entity\Group`

### Methods

- Add `applyCategoriesFilter` to `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface`

### Constructors

- Change the constructor of `PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter\ProductAssetFilterUtility` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter`
- Change the constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Twig\RuleExtension` to add `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository`
- Change the constructor of `PimEnterprise\Bundle\WorkflowBundle\Presenter\FilePresenter` to add `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface`
- Change constructor of `PimEnterprise\Bundle\ProductAssetBundle\Workflow\Presenter\AssetsCollectionPresenter` to add `Symfony\Component\Routing\RouterInterface`.

### Configuration

- Remove `wsse_secured` firewall in security.yml
