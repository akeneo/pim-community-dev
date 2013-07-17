<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * Workflow item
 *
 * @ORM\Table(name="oro_workflow_item")
 * @ORM\Entity(repositoryClass="Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 */
class WorkflowItem
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Name of WorkflowDefinition
     *
     * @var string
     *
     * @ORM\Column(name="workflow_name", type="string", length=255)
     */
    protected $workflowName;

    /**
     * Name of current Step
     *
     * @var string
     *
     * @ORM\Column(name="current_step", type="string", length=255)
     */
    protected $currentStepName;

    /**
     * @var string
     *
     * @ORM\Column(name="closed", type="boolean")
     */
    protected $closed;

    /**
     * Entities relatd to this WorkflowItems
     *
     * @var Collection
     *
     * @ORM\OneToMany(
     *  targetEntity="WorkflowItemEntity",
     *  mappedBy="workflowItem",
     *  cascade={"persist", "remove"},
     *  orphanRemoval=true
     * )
     */
    protected $entities;

    /**
     * Plain data of WorkflowItem
     *
     * @var string
     *
     * @ORM\Column(type="blob")
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entities = new ArrayCollection();
        $this->closed = false;
    }

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
     * @param int $id
     * @return WorkflowItem
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set workflowName
     *
     * @param string $workflowName
     * @return WorkflowItem
     */
    public function setWorkflowName($workflowName)
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    /**
     * Get workflowName
     *
     * @return string
     */
    public function getWorkflowName()
    {
        return $this->workflowName;
    }

    /**
     * Set current Step name
     *
     * @param string $currentStep
     * @return WorkflowItem
     */
    public function setCurrentStepName($currentStep)
    {
        $this->currentStepName = $currentStep;

        return $this;
    }

    /**
     * Get current Step name
     *
     * @return string
     */
    public function getCurrentStepName()
    {
        return $this->currentStepName;
    }

    /**
     * Set closed
     *
     * @param boolean $closed
     * @return WorkflowItem
     */
    public function setClosed($closed)
    {
        $this->closed = (bool)$closed;

        return $this;
    }

    /**
     * Is closed
     *
     * @return string
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Set entities
     *
     * @param \Traversable|array $entities
     * @return WorkflowItem
     * @throws \InvalidArgumentException If $entities is not an array or Collection
     */
    public function setEntities($entities)
    {
        if (!$entities instanceof Collection && !is_array($entities)) {
            throw new \InvalidArgumentException(
                '$entities must be an instance of Doctrine\Common\Collections\Collection or an array'
            );
        }

        $this->entities->clear();
        foreach ($entities as $entity) {
            $this->addEntity($entity);
        }

        return $this;
    }

    /**
     * Get entities
     *
     * @return Collection|WorkflowItemEntity[]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Add WorkflowItemEntity
     *
     * @param WorkflowItemEntity $entity
     * @return WorkflowItem
     */
    public function addEntity(WorkflowItemEntity $entity)
    {
        if (!$this->getEntities()->contains($entity)) {
            $this->getEntities()->add($entity);
            $entity->setWorkflowItem($this);
        }

        return $this;
    }

    /**
     * Remove WorkflowItemEntity
     *
     * @param WorkflowItemEntity $entity
     * @return WorkflowItem
     */
    public function removeEntity(WorkflowItemEntity $entity)
    {
        if ($this->getEntities()->contains($entity)) {
            $this->getEntities()->removeElement($entity);
        }

        return $this;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return WorkflowItem
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
