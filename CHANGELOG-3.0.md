# 3.0

## BC Breaks

- Move namespace `PimEnterprise\Bundle\VersioningBundle\UpdateGuesser` to `PimEnterprise\Bundle\SecurityBundle\UpdateGuesser`
- Move `PimEnterprise\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` to `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\SkipVersionSubscriber`
- Move namespace `PimEnterprise\Bundle\VersioningBundle\Purger` to `PimEnterprise\Bundle\WorkflowBundle\Purger`

## Technical improvement

- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component 


## BC Breaks

- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Router\ProxyProductRouter` to `PimEnterprise\Bundle\WorkflowBundle\Router\ProxyProductRouter`
- Move `PimEnterprise\Component\Api\Normalizer\ProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\ProductNormalizer`
