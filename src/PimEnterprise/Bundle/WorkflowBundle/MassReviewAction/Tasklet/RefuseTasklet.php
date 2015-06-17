<?php

namespace PimEnterprise\Bundle\WorkflowBundle\MassReviewAction\Tasklet;

use Pim\Bundle\BaseConnectorBundle\Step\TaskletInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Tasklet for product drafts mass refusal.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class RefuseTasklet extends AbstractReviewTasklet
{
    const TASKLET_NAME = 'refuse';

    /**
     * {@inheritdoc}
     */
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        $productDrafts = $this->productDraftRepository->findByIds($configuration['draftIds']);
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
