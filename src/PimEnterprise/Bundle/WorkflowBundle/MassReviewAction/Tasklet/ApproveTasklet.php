<?php

namespace PimEnterprise\Bundle\WorkflowBundle\MassReviewAction\Tasklet;

use Pim\Bundle\BaseConnectorBundle\Step\TaskletInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Tasklet for product drafts mass approval.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class ApproveTasklet extends AbstractReviewTasklet
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        $productDrafts = $this->productDraftRepository->findByIds($configuration['draftIds']);
        foreach ($productDrafts as $productDraft) {
            if (ProductDraft::READY !== $productDraft->getStatus()) {
                $this->skipWithWarning($this->stepExecution, 'draft_not_ready', [], $productDraft);
                continue;
            }

            if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft->getProduct())) {
                $this->skipWithWarning($this->stepExecution, 'not_product_owner', [], $productDraft);
                continue;
            }

            if (!$this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
                $this->skipWithWarning($this->stepExecution, 'cannot_edit_attributes', [], $productDraft);
                continue;
            }

            try {
                $this->productDraftManager->approve($productDraft);
                $this->stepExecution->incrementSummaryInfo('approved');
            } catch (ValidatorException $e) {
                $this->skipWithWarning(
                    $this->stepExecution,
                    'invalid_draft',
                    ['%error%' => $e->getMessage()],
                    $productDraft
                );
            }
        }
    }
}
