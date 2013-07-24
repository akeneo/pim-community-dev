<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;

class WorkflowRegistry
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var WorkflowAssembler
     */
    protected $workflowAssembler;

    /**
     * @var Workflow[]
     */
    protected $workflowByName;

    public function __construct(ManagerRegistry $managerRegistry, WorkflowAssembler $workflowAssembler)
    {
        $this->managerRegistry = $managerRegistry;
        $this->workflowAssembler = $workflowAssembler;
    }

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
     * Find WorkflowDefinition
     *
     * @param string $name
     * @return WorkflowDefinition|null
     */
    protected function findWorkflowDefinition($name)
    {
        return $this->managerRegistry->getRepository('OroWorkflowBundle:WorkflowDefinition')->find($name);
    }

    /**
     * Assembles Workflow by WorkflowDefinition
     *
     * @param WorkflowDefinition $workflowDefinition
     * @return Workflow
     */
    protected function assembleWorkflow(WorkflowDefinition $workflowDefinition)
    {
        return $this->workflowAssembler->assemble($workflowDefinition);
    }
}
