# 3.0

## Technical improvement

- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component 
- PAV3-4: Regroup PAM Classes

## BC breaks

- Move namespace `PimEnterprise\Bundle\VersioningBundle` to `PimEnterprise\Bundle\RevertBundle`
- Move namespace `PimEnterprise\Bundle\VersioningBundle\UpdateGuesser` to `PimEnterprise\Bundle\SecurityBundle\UpdateGuesser`
- Move `PimEnterprise\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` to `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\SkipVersionSubscriber`
- Move namespace `PimEnterprise\Bundle\VersioningBundle\Purger` to `PimEnterprise\Bundle\WorkflowBundle\Purger`
- Move all classes from `PimEnterprise\Bundle\ApiBundle\Normalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi`
- Move all classes from `PimEnterprise\Bundle\EnrichBundle\Controller\Rest` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest`
- Move all classes from `PimEnterprise\Bundle\FilterBundle\Filter\Tag` to `PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter`
- Rename `PimEnterprise\Bundle\EnrichBundle\Controller\Rest\ChannelController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest\AssetTransformationController`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\AssetNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\InternalApi\AssetNormalizer`
- Move `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\AssetCategoryAccessSubscriber` to `PimEnterprise\Bundle\ProductAssetBundle\Security\AssetCategoryAccessSubscriber`
- Move `PimEnterprise\Bundle\SecurityBundle\Normalizer\Flat\AssetCategoryNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Flat\AssetCategoryNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Router\ProxyProductRouter` to `PimEnterprise\Bundle\WorkflowBundle\Router\ProxyProductRouter`
- Move `PimEnterprise\Component\Api\Normalizer\ProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\ProductNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository\AssetRepository` to `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository\ExternalApi\AssetRepository`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetCategoryController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\ExternalApi\AssetCategoryController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\ExternalApi\AssetController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetReferenceController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\ExternalApi\AssetReferenceController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetTagController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\ExternalApi\AssetTagController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetVariationController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\ExternalApi\AssetVariationController`
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
- Move `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\ProductDraftController`
- Move `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\PublishedProductController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\PublishedProductController`
- Move `PimEnterprise\Component\Api\Normalizer\PublishedProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\PublishedProductNormalizer`
- Remove `PimEnterprise\Component\Api\Repository\PublishedProductRepositoryInterface`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\PublishedProduct\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\PublishedProduct\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductHistoryHydrator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ResultRecord\PublishedProductHistoryHydrator`
