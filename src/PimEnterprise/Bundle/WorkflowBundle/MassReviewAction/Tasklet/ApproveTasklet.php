<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    const TASKLET_NAME = 'approve';

    /**
     * {@inheritdoc}
     */
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        $productDrafts = $this->productDraftRepository->findByIds($configuration['draftIds']);
        foreach ($productDrafts as $productDraft) {
            if (ProductDraft::READY !== $productDraft->getStatus()) {
                $this->skipWithWarning($this->stepExecution, self::TASKLET_NAME, 'draft_not_ready', [], $productDraft);
                continue;
            }

            if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft->getProduct())) {
                $this->skipWithWarning($this->stepExecution, self::TASKLET_NAME, 'not_product_owner', [], $productDraft);
                continue;
            }

            if (!$this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
                $this->skipWithWarning($this->stepExecution, self::TASKLET_NAME, 'cannot_edit_attributes', [], $productDraft);
                continue;
            }

            try {
                $this->productDraftManager->approve($productDraft);
                $this->stepExecution->incrementSummaryInfo('approved');
            } catch (ValidatorException $e) {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    'invalid_draft',
                    ['%error%' => $e->getMessage()],
                    $productDraft
                );
            }
        }
    }
}
