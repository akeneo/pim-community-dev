<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Connector\Tasklet;

use PimEnterprise\Bundle\SecurityBundle\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Security\Attributes as WorkflowAttributes;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Tasklet for product drafts mass approval.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class ApproveTasklet extends AbstractReviewTasklet
{
    /** @staticvar string */
    const TASKLET_NAME = 'approve';

    /**
     * {@inheritdoc}
     */
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        $productDrafts = $this->draftRepository->findByIds($configuration['draftIds']);
        foreach ($productDrafts as $productDraft) {
            try {
                $this->approveDraft($productDraft, $configuration['comment']);
                $this->stepExecution->incrementSummaryInfo('approved');
            } catch (DraftNotReviewableException $e) {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    $e->getMessage(),
                    ($prev = $e->getPrevious()) ? ['%error%' => $prev->getMessage()] : [],
                    $productDraft
                );
            }
        }
    }

    /**
     * Approve a draft
     *
     * @param ProductDraftInterface $productDraft
     * @param string|null           $comment
     *
     * @throws DraftNotReviewableException If draft cannot be approved
     */
    protected function approveDraft(ProductDraftInterface $productDraft, $comment)
    {
        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new DraftNotReviewableException(self::ERROR_DRAFT_NOT_READY);
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getProduct())) {
            throw new DraftNotReviewableException(self::ERROR_NOT_PRODUCT_OWNER);
        }

        if (!$this->authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft)) {
            throw new DraftNotReviewableException(self::ERROR_CANNOT_EDIT_ATTR);
        }

        try {
            $this->productDraftManager->approve($productDraft, ['comment' => $comment]);
        } catch (ValidatorException $e) {
            throw new DraftNotReviewableException(self::ERROR_INVALID_DRAFT, 0, $e);
        }
    }
}
