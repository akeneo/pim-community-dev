<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowItemDataSerializerInterface;

/**
 * Performs serialization and deserialization of WorkflowItem data
 */
class WorkflowItemSerializeSubscriber implements EventSubscriber
{
    /**
     * @var WorkflowItemDataSerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param WorkflowItemDataSerializerInterface $serializer
     */
    public function __construct(WorkflowItemDataSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'preFlush',
            'postLoad'
        );
    }

    /**
     * Before flush serializes all WorkflowItem's data
     *
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($this->isSupported($entity)) {
                $this->serializeWorkflowItemData($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($this->isSupported($entity)) {
                $this->serializeWorkflowItemData($entity);
            }
        }
    }

    /**
     * After WorkflowItem loaded, de-serialize WorkflowItem
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($this->isSupported($args->getEntity($entity))) {
            $this->deserializeWorkflowItemData($entity);
        }
    }

    /**
     * Serialize data of WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     */
    protected function serializeWorkflowItemData(WorkflowItem $workflowItem)
    {
        $workflowItem->setSerializedData($this->serializer->serialize($workflowItem->getData()));
    }

    /**
     * Deserialize data of WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     */
    protected function deserializeWorkflowItemData(WorkflowItem $workflowItem)
    {
        $workflowItem->setData($this->serializer->deserialize($workflowItem->getSerializedData()));
    }

    /**
     * @param $entity
     * @return bool
     */
    protected function isSupported($entity)
    {
        return $entity instanceof WorkflowItem;
    }
}
