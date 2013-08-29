<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;
use Oro\Bundle\WorkflowBundle\Model\MetadataManager;

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
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * @param WorkflowRegistry $workflowRegistry
     * @param MetadataManager $metadataManager
     */
    public function __construct(WorkflowRegistry $workflowRegistry, MetadataManager $metadataManager)
    {
        $this->workflowRegistry  = $workflowRegistry;
        $this->metadataManager = $metadataManager;
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
        $bindAttributeNames = $workflow->getBindEntityAttributeNames();
        $entitiesToBind = $workflowData->getValues($bindAttributeNames);

        $counter = 0;

        foreach ($entitiesToBind as $entity) {
            if ($entity && $this->bindEntity($workflowItem, $entity)) {
                $counter++;
            }
        }

        return $counter > 0;
    }

    /**
     * Binds entity to WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     * @param mixed $entity
     * @return bool Returns true if at least one entity was bound
     */
    protected function bindEntity(WorkflowItem $workflowItem, $entity)
    {
        $bindEntity = new WorkflowBindEntity();
        $bindEntity->setEntityClass($this->metadataManager->getEntityClass($entity));
        $bindEntity->setEntityId($this->metadataManager->getEntityIdentifier($entity));

        if (!$workflowItem->hasBindEntity($bindEntity)) {
            $workflowItem->addBindEntity($bindEntity);
            return true;
        }

        return false;
    }
}
