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
            if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft->getProduct())) {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    'not_product_owner',
                    [],
                    $productDraft
                );
                continue;
            }

            if (!$this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    'cannot_edit_attributes',
                    [],
                    $productDraft
                );
                continue;
            }

            $this->productDraftManager->refuse($productDraft);
            $this->stepExecution->incrementSummaryInfo('refused');
        }
    }
}
