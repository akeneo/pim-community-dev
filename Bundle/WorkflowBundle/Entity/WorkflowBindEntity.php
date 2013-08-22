<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workflow item relation with custom entity
 *
 * @ORM\Table(name="oro_workflow_bind_entity")
 * @ORM\Entity
 */
class WorkflowBindEntity
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
     * Set id
     *
     * @param integer $id
     * @return WorkflowBindEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set entity class
     *
     * @param string $entityClass
     * @return WorkflowBindEntity
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
     * @return WorkflowBindEntity
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
     * @return WorkflowBindEntity
     */
    public function setWorkflowItem(WorkflowItem $workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
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

    /**
     * Is current object is bind to same entity as other object
     *
     * @param WorkflowBindEntity $other
     * @return bool
     */
    public function hasSameEntity(WorkflowBindEntity $other)
    {
        return
            $this->entityId && $this->entityClass &&
            $this->entityClass  == $other->entityClass && $this->entityId == $other->entityId;
    }
}
