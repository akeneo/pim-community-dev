<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Performs serialization and deserialization of WorkflowItem data
 */
class WorkflowItemSerializeSubscriber implements EventSubscriber
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
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
                $this->serialize($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($this->isSupported($entity)) {
                $this->serialize($entity);
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
     */
    protected function serialize(WorkflowItem $workflowItem)
    {
        // @TODO Get format ("json", "xml", ...) from WorkflowDefinition
        $serializedData = $this->serializer->serialize($workflowItem->getData(), $this->format);
        $workflowItem->setSerializedData($serializedData);
    }

    /**
     * Deserialize data of WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     */
    protected function deserialize(WorkflowItem $workflowItem)
    {
        // @TODO Get class name "Oro\Bundle\WorkflowBundle\Model\WorkflowData" from WorkflowDefinition
        // @TODO Get format ("json", "xml", ...) from WorkflowDefinition
        $data = $this->serializer->deserialize(
            $workflowItem->getSerializedData(),
            'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
            $this->format
        );
        $workflowItem->setData($data);
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
