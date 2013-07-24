<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowDefinitionRepository;
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
     * @param string $workflowName
     * @return Workflow
     * @throws WorkflowNotFoundException
     */
    public function getWorkflow($workflowName)
    {
        if (!$this->workflowByName[$workflowName]) {
            $workflowDefinition = $this->findWorkflowDefinition($workflowName);
            if (!$workflowDefinition) {
                throw new WorkflowNotFoundException($workflowName);
            }
            return $this->getAssembledWorkflow($workflowDefinition);
        }
        return $this->workflowByName[$workflowName];
    }

    /**
     * Get Workflow by WorkflowDefinition
     *
     * @param WorkflowDefinition $workflowDefinition
     * @return Workflow
     */
    protected function getAssembledWorkflow(WorkflowDefinition $workflowDefinition)
    {
        $workflowName = $workflowDefinition->getName();
        if (!$this->workflowByName[$workflowName]) {
            $workflow = $this->assembleWorkflow($workflowDefinition);
            $this->workflowByName[$workflowName] = $workflow;
        }
        return $this->workflowByName[$workflowName];
    }

    /**
     * Get list of Workflows that are applicable to entity
     *
     * @param object $entity
     * @return Workflow[]
     */
    public function getWorkflowsByEntity($entity)
    {
        $result = array();
        $workflowDefinitions = $this->getWorkflowDefinitionRepository()->findWorkflowDefinitionsByEntity($entity);

        foreach ($workflowDefinitions as $workflowDefinition) {
            $result[$workflowDefinition->getName()] = $this->getAssembledWorkflow($workflowDefinition);
        }
        return $result;
    }

    /**
     * Find WorkflowDefinition
     *
     * @param string $name
     * @return WorkflowDefinition|null
     */
    protected function findWorkflowDefinition($name)
    {
        return $this->getWorkflowDefinitionRepository()->find($name);
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

    /**
     * @return WorkflowDefinitionRepository
     */
    protected function getWorkflowDefinitionRepository()
    {
        return $this->managerRegistry->getRepository('OroWorkflowBundle:WorkflowDefinition');
    }
}
