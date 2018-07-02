<?php

declare(strict_types=1);

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
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;

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
    public function execute(): void
    {
        $this->initSecurityContext($this->stepExecution);

        $jobParameters = $this->stepExecution->getJobParameters();
        $productDrafts = $this->productDraftRepository->findByIds($jobParameters->get('productDraftIds'));
        $productModelDrafts = $this->productModelDraftRepository->findByIds($jobParameters->get('productModelDraftIds'));
        $context = ['comment' => $jobParameters->get('comment')];

        if (null !== $productDrafts) {
            $this->processDrafts($productDrafts, $context);
        }

        if (null !== $productModelDrafts) {
            $this->processDrafts($productModelDrafts, $context);
        }
    }

    /**
     * Skip or refuse given $drafts depending on permission
     *
     * @param mixed $drafts
     * @param array $context
     */
    protected function processDrafts($drafts, array $context): void
    {
        foreach ($drafts as $draft) {
            if ($this->permissionHelper->canEditOneChangeToReview($draft)) {
                try {
                    $this->refuseDraft($draft, $context);
                    $this->stepExecution->incrementSummaryInfo('refused');
                } catch (DraftNotReviewableException $e) {
                    $this->skipWithWarning(
                        $this->stepExecution,
                        self::TASKLET_NAME,
                        $e->getMessage(),
                        ($prev = $e->getPrevious()) ? ['%error%' => $prev->getMessage()] : [],
                        $draft->getEntityWithValue()
                    );
                }
            } else {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    self::ERROR_CANNOT_EDIT_ATTR,
                    [],
                    $draft->getEntityWithValue()
                );
            }
        }
    }

    /**
     * @param EntityWithValuesDraftInterface $draft
     * @param array                          $context
     *
     * @throws DraftNotReviewableException
     */
    protected function refuseDraft(EntityWithValuesDraftInterface $draft, array $context): void
    {
        if (EntityWithValuesDraftInterface::READY !== $draft->getStatus()) {
            throw new DraftNotReviewableException(self::ERROR_DRAFT_NOT_READY);
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $draft->getEntityWithValue())) {
            throw new DraftNotReviewableException(self::ERROR_NOT_PRODUCT_OWNER);
        }

        if ($draft instanceof ProductDraft) {
            $this->productDraftManager->refuse($draft, $context);
        } elseif ($draft instanceof ProductModelDraft) {
            $this->productModelDraftManager->refuse($draft, $context);
        }
    }
}
