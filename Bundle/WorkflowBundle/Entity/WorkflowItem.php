<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\WorkflowResult;

use JMS\Serializer\Annotation as Serializer;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * Workflow item
 *
 * @ORM\Table(
 *      name="oro_workflow_item",
 *      indexes={
 *          @ORM\Index(name="oro_workflow_item_workflow_name_idx", columns={"workflow_name"})
 *      }
 *  )
 * @ORM\Entity(repositoryClass="Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Serializer\ExclusionPolicy("all")
 */
class WorkflowItem
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     */
    protected $id;

    /**
     * Name of WorkflowDefinition
     *
     * @var string
     *
     * @ORM\Column(name="workflow_name", type="string", length=255)
     * @Serializer\Expose()
     */
    protected $workflowName;

    /**
     * Name of current Step
     *
     * @var string
     *
     * @ORM\Column(name="current_step", type="string", length=255)
     * @Serializer\Expose()
     */
    protected $currentStepName;

    /**
     * @var string
     *
     * @ORM\Column(name="closed", type="boolean")
     * @Serializer\Expose()
     */
    protected $closed;

    /**
     * Entities related to this WorkflowItems
     *
     * @var Collection|WorkflowBindEntity[]
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
     * Corresponding Workflow Definition
     *
     * @var WorkflowDefinition
     *
     * @ORM\ManyToOne(targetEntity="WorkflowDefinition")
     * @ORM\JoinColumn(name="workflow_name", referencedColumnName="name", onDelete="CASCADE")
     */
    protected $definition;

    /**
     * Related transition records
     *
     * @var Collection|WorkflowTransitionRecord[]
     *
     * @ORM\OneToMany(
     *  targetEntity="WorkflowTransitionRecord",
     *  mappedBy="workflowItem",
     *  cascade={"persist", "remove"},
     *  orphanRemoval=true
     * )
     */
    protected $transitionRecords;

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
     * @var WorkflowResult
     *
     * @Serializer\Expose()
     */
    protected $result;

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
        $this->transitionRecords = new ArrayCollection();
        $this->closed = false;
        $this->data = new WorkflowData();
        $this->result = new WorkflowResult();
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
     * Synchronize current bind entities with the list of actual bind entities, removes entities that are outdated and
     * adds new entities.
     *
     * @param WorkflowBindEntity[] $actualBindEntities
     * @return bool
     */
    public function syncBindEntities(array $actualBindEntities)
    {
        $hasChanges = false;

        // Remove connections with WorkflowBindEntity that are outdated
        /** @var $bindEntity WorkflowBindEntity */
        foreach ($this->getBindEntities() as $bindEntity) {
            $isActual = false;
            foreach ($actualBindEntities as $actualBindEntity) {
                if ($actualBindEntity->hasSameEntity($bindEntity)) {
                    $isActual = true;
                    break;
                }
            }
            if (!$isActual) {
                $hasChanges = true;
                $this->removeBindEntity($bindEntity);
            }
        }

        // Add WorkflowBindEntity that are missing entities
        foreach ($actualBindEntities as $bindEntity) {
            if (!$this->hasBindEntity($bindEntity)) {
                $this->addBindEntity($bindEntity);
                $hasChanges = true;
            }
        }

        return $hasChanges;
    }

    /**
     * Set workflow definition
     *
     * @param WorkflowDefinition $definition
     * @return WorkflowItem
     */
    public function setDefinition(WorkflowDefinition $definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get workflow definition
     *
     * @return WorkflowDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
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
     * @return WorkflowResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Collection|WorkflowTransitionRecord[]
     */
    public function getTransitionRecords()
    {
        return $this->transitionRecords;
    }

    /**
     * @param WorkflowTransitionRecord $transitionRecord
     * @return WorkflowItem
     */
    public function addTransitionRecord(WorkflowTransitionRecord $transitionRecord)
    {
        $transitionRecord->setWorkflowItem($this);
        $this->transitionRecords->add($transitionRecord);

        return $this;
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
