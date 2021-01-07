<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base remover, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseRemover implements RemoverInterface, BulkRemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $removedClass;

    /**
     * @param ObjectManager                    $objectManager
     * @param EventDispatcherInterface         $eventDispatcher
     * @param string                           $removedClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $removedClass
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->removedClass = $removedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        $this->validateObject($object);

        $objectId = $object->getId();

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(new RemoveEvent($object, $objectId, $options), StorageEvents::PRE_REMOVE);

        $this->objectManager->remove($object);
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(new RemoveEvent($object, $objectId, $options), StorageEvents::POST_REMOVE);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new RemoveEvent($objects, null), StorageEvents::PRE_REMOVE_ALL);

        $removedObjects = [];
        foreach ($objects as $object) {
            $this->validateObject($object);
            $removedObjects[$object->getId()] = $object;

            $this->eventDispatcher->dispatch(new RemoveEvent($object, $object->getId(), $options), StorageEvents::PRE_REMOVE);

            $this->objectManager->remove($object);
        }

        $this->objectManager->flush();

        foreach ($removedObjects as $id => $object) {
            $this->eventDispatcher->dispatch(new RemoveEvent($object, $id, $options), StorageEvents::POST_REMOVE);
        }

        $this->eventDispatcher->dispatch(
            new RemoveEvent($objects, array_keys($removedObjects)),
            StorageEvents::POST_REMOVE_ALL
        );
    }

    /**
     * @param $object
     */
    protected function validateObject($object)
    {
        if (!$object instanceof $this->removedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->removedClass,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
