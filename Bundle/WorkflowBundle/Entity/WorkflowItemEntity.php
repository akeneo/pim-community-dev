<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workflow item relation with custom entity
 *
 * @ORM\Table(name="oro_workflow_item_entity")
 * @ORM\Entity
 */
class WorkflowItemEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var WorkflowItem
     *
     * @ORM\ManyToOne(targetEntity="WorkflowItem", inversedBy="entities")
     */
    protected $workflowItem;

    /**
     * Step name
     *
     * @var string
     *
     * @ORM\Column(name="step", type="string", length=255)
     */
    protected $stepName;

    /**
     * Full class name of related entity
     *
     * @var string
     *
     * @ORM\Column(name="entity_class", type="string", length=255)
     */
    protected $entityClass;

    /**
     * Id of related entity
     *
     * @var string
     *
     * @ORM\Column(name="entity_id", type="string", length=255)
     */
    protected $entityId;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set step name
     *
     * @param string $step
     * @return WorkflowItemEntity
     */
    public function setStepName($step)
    {
        $this->stepName = $step;

        return $this;
    }

    /**
     * Get step name
     *
     * @return string
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * Set entity class
     *
     * @param string $entityClass
     * @return WorkflowItemEntity
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entity class
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set entity id
     *
     * @param string $entityId
     * @return WorkflowItemEntity
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entity id
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     * @return WorkflowItemEntity
     */
    public function setWorkflowItem(WorkflowItem $workflowItem)
    {
        $this->workflowItem = $workflowItem;
    }

    /**
     * Get WorkflowItem
     *
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }
}
