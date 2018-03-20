# 3.0

## Technical improvement

- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component 


## BC Breaks

- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Router\ProxyProductRouter` to `PimEnterprise\Bundle\WorkflowBundle\Router\ProxyProductRouter`
- Move `PimEnterprise\Component\Api\Normalizer\ProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\ProductNormalizer`