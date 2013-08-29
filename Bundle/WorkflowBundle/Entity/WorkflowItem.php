<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

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
     * Entities related to this WorkflowItems
     *
     * @var Collection
     *
     * @ORM\OneToMany(
     *  targetEntity="WorkflowBindEntity",
     *  mappedBy="workflowItem",
     *  cascade={"persist", "remove"},
     *  orphanRemoval=true
     * )
     */
    protected $bindEntities;

    /**
     * @var \Datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Datetime $updated
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * Serialized data of WorkflowItem
     *
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    protected $serializedData;

    /**
     * @var WorkflowData
     */
    protected $data;

    /**
     * @var WorkflowAwareSerializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $serializeFormat;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bindEntities = new ArrayCollection();
        $this->closed = false;
        $this->data = new WorkflowData();
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
     * Get entities
     *
     * @return Collection
     */
    public function getBindEntities()
    {
        return $this->bindEntities;
    }

    /**
     * Add WorkflowBindEntity
     *
     * @param WorkflowBindEntity $entity
     * @return WorkflowItem
     */
    public function addBindEntity(WorkflowBindEntity $entity)
    {
        if (!$this->hasBindEntity($entity)) {
            $entity->setWorkflowItem($this);
            $this->getBindEntities()->add($entity);
        }

        return $this;
    }

    /**
     * Is entity already bind to WorkflowItem
     *
     * @param WorkflowBindEntity $originalEntity
     * @return bool
     */
    public function hasBindEntity(WorkflowBindEntity $originalEntity)
    {
        $bindEntities = $this->getBindEntities()->filter(
            function (WorkflowBindEntity $existedEntity) use ($originalEntity) {
                return $originalEntity->hasSameEntity($existedEntity);
            }
        );

        return $bindEntities->count() > 0;
    }

    /**
     * Remove WorkflowBindEntity
     *
     * @param WorkflowBindEntity $entity
     * @return WorkflowItem
     */
    public function removeBindEntity(WorkflowBindEntity $entity)
    {
        if ($this->getBindEntities()->contains($entity)) {
            $this->getBindEntities()->removeElement($entity);
        }

        return $this;
    }

    /**
     * Set serialized data.
     *
     * This method should be called only from WorkflowDataSerializeSubscriber.
     *
     * @param string $data
     * @return WorkflowItem
     */
    public function setSerializedData($data)
    {
        $this->serializedData = $data;

        return $this;
    }

    /**
     * Get serialized data.
     *
     * This method should be called only from WorkflowDataSerializeSubscriber.
     *
     * @return string $data
     */
    public function getSerializedData()
    {
        return $this->serializedData;
    }

    /**
     * Set data
     *
     * @param WorkflowData $data
     * @return WorkflowItem
     */
    public function setData(WorkflowData $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return WorkflowData
     * @throws WorkflowException If data cannot be deserialized
     */
    public function getData()
    {
        if (!$this->data) {
            if (!$this->serializedData) {
                $this->data = new WorkflowData();
            } elseif (!$this->serializer) {
                throw new WorkflowException('Cannot deserialize data of workflow item. Serializer is not available.');
            } else {
                $this->serializer->setWorkflowName($this->workflowName);
                $this->data = $this->serializer->deserialize(
                    $this->serializedData,
                    'Oro\Bundle\WorkflowBundle\Model\WorkflowData', // @TODO Make this class name configurable?
                    $this->serializeFormat
                );
            }
        }
        return $this->data;
    }

    /**
     * Set serializer.
     *
     * This method should be called only from WorkflowDataSerializeSubscriber.
     *
     * @param WorkflowAwareSerializer $serializer
     * @param string $format
     */
    public function setSerializer(WorkflowAwareSerializer $serializer, $format)
    {
        $this->serializer = $serializer;
        $this->serializeFormat = $format;
    }

    /**
     * Get created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Invoked before the entity is updated.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdated();
    }

    /**
     * Set updated property to actual Date
     */
    public function setUpdated()
    {
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
