<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;

class WorkflowRegistry
{
    /**
     * @var Workflow[]
     */
    protected $workflowByName;

    /**
     * Get Workflow by name
     *
     * @param string $name
     * @return Workflow
     * @throws WorkflowNotFoundException
     */
    public function getWorkflow($name)
    {
        if (!$this->workflowByName[$name]) {
            $workflowDefinition = $this->findWorkflowDefinition($name);
            if (!$workflowDefinition) {
                throw new WorkflowNotFoundException($name);
            }
            $workflow = $this->assembleWorkflow($workflowDefinition);
            $this->workflowByName[$name] = $workflow;
        }
        return $this->workflowByName[$name];
    }

    /**
     * @param string $name
     * @return WorkflowDefinition
     */
    protected function findWorkflowDefinition($name)
    {
        // @TODO Find WorkflowDefinition by name from repository
        return new WorkflowDefinition();
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return Workflow
     */
    protected function assembleWorkflow(WorkflowDefinition $workflowDefinition)
    {
        // @TODO Assemble workflow by name
        return new Workflow();
    }
}
