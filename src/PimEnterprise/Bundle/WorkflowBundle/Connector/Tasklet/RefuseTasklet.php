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

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

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
                $this->refuseDraft($productDraft, $configuration['comment']);
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
     * @param string|null           $comment
     *
     * @throws DraftNotReviewableException If draft cannot be refused
     */
    protected function refuseDraft(ProductDraftInterface $productDraft, $comment)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productDraft->getProduct())) {
            throw new DraftNotReviewableException(self::ERROR_NOT_PRODUCT_OWNER);
        }

        if (!$this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
            throw new DraftNotReviewableException(self::ERROR_CANNOT_EDIT_ATTR);
        }

        $this->productDraftManager->refuse($productDraft, ['comment' => $comment]);
    }
}
