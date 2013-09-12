<?php

namespace Oro\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Serializer;

use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowDataSerializer extends Serializer implements WorkflowAwareSerializer
{
    /**
     * @var string
     */
    protected $workflowName;

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @param WorkflowRegistry $workflowRegistry
     */
    public function setWorkflowRegistry(WorkflowRegistry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @return Workflow
     */
    public function getWorkflow()
    {
        return $this->workflowRegistry->getWorkflow($this->getWorkflowName());
    }

    /**
     * @param string $workflowName
     */
    public function setWorkflowName($workflowName)
    {
        $this->workflowName = $workflowName;
    }

    /**
     * @return string
     */
    public function getWorkflowName()
    {
        return $this->workflowName;
    }
}
