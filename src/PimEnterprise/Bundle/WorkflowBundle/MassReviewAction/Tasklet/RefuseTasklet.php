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

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException;

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
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        $productDrafts = $this->draftRepository->findByIds($configuration['draftIds']);
        foreach ($productDrafts as $productDraft) {
            try {
                $this->refuseDraft($productDraft);
                $this->stepExecution->incrementSummaryInfo('refused');
            } catch (DraftNotReviewableException $e) {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    $e->getMessage(),
                    [],
                    $productDraft
                );
            }
        }
    }

    /**
     * Refuse a draft
     *
     * @param ProductDraftInterface $productDraft
     *
     * @throws DraftNotReviewableException If draft cannot be refused
     */
    protected function refuseDraft(ProductDraftInterface $productDraft)
    {
        if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft->getProduct())) {
            throw new DraftNotReviewableException(self::ERROR_NOT_PRODUCT_OWNER);
        }

        if (!$this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
            throw new DraftNotReviewableException(self::ERROR_CANNOT_EDIT_ATTR);
        }

        $this->productDraftManager->refuse($productDraft);
    }
}
