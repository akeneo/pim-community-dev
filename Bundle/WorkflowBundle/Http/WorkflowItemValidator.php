<?php

namespace Oro\Bundle\WorkflowBundle\Http;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class WorkflowItemValidator
{
    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @param WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param WorkflowItem $workflowItem
     * @throws NotFoundHttpException
     */
    public function validate(WorkflowItem $workflowItem)
    {
        if (!$this->workflowManager->isAllManagedEntitiesSpecified($workflowItem)) {
            throw new NotFoundHttpException('Managed entities for workflow item not found');
        }
    }
}
