<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;

/**
 * Performs serialization and deserialization of WorkflowItem data
 */
class WorkflowDataSerializeSubscriber implements EventSubscriber
{
    /**
     * @var WorkflowAwareSerializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * Constructor
     *
     * @param WorkflowAwareSerializer $serializer
     */
    public function __construct(WorkflowAwareSerializer $serializer)
    {
        $this->serializer = $serializer;
    }


    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            // @codingStandardsIgnoreStart
            Events::onFlush,
            Events::postLoad
            // @codingStandardsIgnoreEnd
        );
    }

    /**
     * Before flush serializes all WorkflowItem's data
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($this->isSupported($entity)) {
                $this->serialize($entity, $uow);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($this->isSupported($entity)) {
                $this->serialize($entity, $uow);
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
            $this->deserialize($entity);
        }
    }

    /**
     * Serialize data of WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     * @param UnitOfWork $uow
     */
    protected function serialize(WorkflowItem $workflowItem, UnitOfWork $uow)
    {
        if ($workflowItem->getData()->isModified()) {
            $oldValue = $workflowItem->getSerializedData();

            $this->serializer->setWorkflowName($workflowItem->getWorkflowName());
            $serializedData = $this->serializer->serialize($workflowItem->getData(), $this->format);
            $workflowItem->setSerializedData($serializedData);

            $uow->propertyChanged($workflowItem, 'serializedData', $oldValue, $serializedData);
        }
    }

    /**
     * Deserialize data of WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     */
    protected function deserialize(WorkflowItem $workflowItem)
    {
        // Pass serializer into $workflowItem to make lazy loading of workflow item data.
        $workflowItem->setSerializer($this->serializer, $this->format);
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
