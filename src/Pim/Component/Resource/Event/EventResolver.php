<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\ResourceInterface;
use Pim\Component\Resource\ResourceSetInterface;

/**
 * Retrieve the event linked to a resource or a set of resources.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventResolver
{
    /** @var EventTypeRegistry */
    protected $registry;

    /** @var string */
    protected $eventClass;

    /**
     * @param EventTypeRegistry $registry
     * @param string            $eventClass
     */
    public function __construct(EventTypeRegistry $registry, $eventClass = 'Pim\Component\Resource\Event\ResourceEvent')
    {
        $this->registry = $registry;
        $this->eventClass = $eventClass;
    }

    /**
     * Create the event suitable for the resource.
     *
     * @param ResourceInterface|ResourceSetInterface $resource
     *
     * @return ResourceEventInterface
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($resource)
    {
        if (!$resource instanceof ResourceInterface && !$resource instanceof ResourceSetInterface) {
            throw new \InvalidArgumentException(
                'Resource should be an instance of "ResourceInterface" or "ResourceSetInterface".'
            );
        }

        $resourceClass = $this->getResourceClass($resource);
        $eventClass = $this->getEventClassForResource($resource);

        /** @var ResourceEventInterface $event */
        $event = new $eventClass($resourceClass);
        $event->setSubject($resource);

        return $event;
    }

    /**
     * Get the class of the event that should be created according to the given resource.
     *
     * @param ResourceInterface|ResourceSetInterface $resource
     *
     * @return string
     */
    private function getEventClassForResource($resource)
    {
        $resourceClass = $this->getResourceClass($resource);
        $eventClass = $this->eventClass;

        if (null === $resourceClass) {
            // It can be null if the resource set is empty. In that case, let's return the default event class.
            return $eventClass ;
        }

        foreach ($this->registry->getEventTypes() as $eventType) {
            if ($resourceClass === $eventType->getSubjectClass()) {
                $eventClass = get_class($eventType);
                break;
            }
        }

        return $eventClass;
    }

    /**
     * Return the resource class or null in case of an empty resource set.
     *
     * @param ResourceInterface|ResourceSetInterface $resource
     *
     * @return null|string
     */
    private function getResourceClass($resource)
    {
        if ($resource instanceof ResourceSetInterface) {
            return $resource->getResourceClass();
        }

        return get_class($resource);
    }
}
