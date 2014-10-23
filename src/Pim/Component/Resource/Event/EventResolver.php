<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\ResourceInterface;
use Pim\Component\Resource\ResourceSetInterface;

/**
 * Retrieve the event to linked to a resource or a set of resources.
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
     * @param ResourceInterface|ResourceSetInterface $resource
     *
     * @return ResourceEventInterface
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($resource)
    {
        if ($resource instanceof ResourceInterface) {
            $resourceClass = get_class($resource);
        } elseif ($resource instanceof ResourceSetInterface) {
            $resourceClass = get_class($resource[0]);
        } else {
            throw new \InvalidArgumentException(
                'Resource should be an instance of "ResourceInterface" or "ResourceSetInterface".'
            );
        }

        // TODO: argument for this
        $eventClass = $this->eventClass;
        foreach ($this->registry->getEventTypes() as $eventType) {
            if ($resourceClass === $eventType->getSubjectClass()) {
                $eventClass = get_class($eventType);
                break;
            }
        }

        /** @var ResourceEventInterface $event */
        $event = new $eventClass($resourceClass);
        $event->setSubject($resource);

        return $event;
    }
}
