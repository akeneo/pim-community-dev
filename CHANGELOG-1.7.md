# 1.7.0-ALPHA1 (2017-02-23)

## Team Work Assistant

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

## Technical improvements

- Update spec to be independent from actual developer timezone
- GITHUB-5455: Redo channel's form `Asset transformations` tab to fit new form implementation based on internal REST API

## BC breaks

### Bundles

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
