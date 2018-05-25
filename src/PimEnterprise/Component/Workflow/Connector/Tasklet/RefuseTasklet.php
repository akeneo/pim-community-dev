<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Connector\Tasklet;

use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Component\Workflow\Exception\DraftNotReviewableException;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

/**
 * Tasklet for product drafts mass refusal.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class RefuseTasklet extends AbstractReviewTasklet
{
    /** @staticvar string */
    const TASKLET_NAME = 'refuse';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->initSecurityContext($this->stepExecution);

        $jobParameters = $this->stepExecution->getJobParameters();
        $productDrafts = $this->draftRepository->findByIds($jobParameters->get('draftIds'));
        $context = ['comment' => $jobParameters->get('comment')];

        $this->processDrafts($productDrafts, $context);
    }

    /**
     * Skip or refuse given $productDrafts depending on permission
     *
     * @param mixed $productDrafts
     * @param array $context
     */
    protected function processDrafts($productDrafts, array $context)
    {
        foreach ($productDrafts as $productDraft) {
            if ($this->permissionHelper->canEditOneChangeToReview($productDraft)) {
                try {
                    $this->refuseDraft($productDraft, $context);
                    $this->stepExecution->incrementSummaryInfo('refused');
                } catch (DraftNotReviewableException $e) {
                    $this->skipWithWarning(
                        $this->stepExecution,
                        self::TASKLET_NAME,
                        $e->getMessage(),
                        [],
                        $productDraft->getEntityWithValue()
                    );
                }
            } else {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    self::ERROR_CANNOT_EDIT_ATTR,
                    [],
                    $productDraft->getEntityWithValue()
                );
            }
        }
    }

    /**
     * Refuse a draft
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $context
     *
     * @throws DraftNotReviewableException
     */
    protected function refuseDraft(EntityWithValuesDraftInterface $productDraft, array $context)
    {
        if (EntityWithValuesDraftInterface::READY !== $productDraft->getStatus()) {
            throw new DraftNotReviewableException(self::ERROR_DRAFT_NOT_READY);
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getEntityWithValue())) {
            throw new DraftNotReviewableException(self::ERROR_NOT_PRODUCT_OWNER);
        }

        $this->productDraftManager->refuse($productDraft, $context);
    }
}
