<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;
use Oro\Bundle\WorkflowBundle\Model\DoctrineHelper;

/**
 * Runs binding of workflow items with entities
 */
class EntityBinder
{
    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param WorkflowRegistry $workflowRegistry
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(WorkflowRegistry $workflowRegistry, DoctrineHelper $doctrineHelper)
    {
        $this->workflowRegistry  = $workflowRegistry;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Bind entities to workflow item
     *
     * @param WorkflowItem $workflowItem
     * @return bool Returns true if new entities were bound
     */
    public function bindEntities(WorkflowItem $workflowItem)
    {
        $workflowData = $workflowItem->getData();
        if (!$workflowData->isModified()) {
            return false;
        }

        $workflow = $this->workflowRegistry->getWorkflow($workflowItem->getWorkflowName());
        $bindAttributeNames = $workflow->getAttributeManager()->getBindEntityAttributeNames();
        $entitiesToBind = $workflowData->getValues($bindAttributeNames);

        return $workflowItem->syncBindEntities($this->createBindEntities($entitiesToBind));
    }

    /**
     * @param array $entitiesToBind
     * @return WorkflowBindEntity[]
     */
    protected function createBindEntities(array $entitiesToBind)
    {
        $result = array();
        foreach ($entitiesToBind as $entity) {
            if ($entity) {
                $bindEntity = new WorkflowBindEntity();
                $bindEntity->setEntityClass($this->doctrineHelper->getEntityClass($entity));
                $bindEntity->setEntityId($this->doctrineHelper->getEntityIdentifier($entity));

                $result[] = $bindEntity;
            }
        }
        return $result;
    }
}
